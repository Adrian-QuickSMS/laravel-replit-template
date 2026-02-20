<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\Invoice;
use App\Models\Billing\CreditNote;
use App\Models\Billing\Payment;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\FinancialAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class XeroService
{
    private string $baseUrl;
    private string $tenantId;

    public function __construct()
    {
        $this->baseUrl = config('services.xero.base_url') ?? 'https://api.xero.com/api.xro/2.0';
        $this->tenantId = config('services.xero.tenant_id') ?? '';
    }

    /**
     * Push an invoice to Xero.
     */
    public function pushInvoice(Invoice $invoice): void
    {
        $account = $invoice->account;
        $xeroContactId = $this->ensureXeroContact($account);

        $lineItems = $invoice->lineItems->map(fn($li) => [
            'Description' => $li->description,
            'Quantity' => $li->quantity,
            'UnitAmount' => $li->unit_price,
            'TaxType' => $this->mapTaxType($li->tax_rate),
            'AccountCode' => $this->mapRevenueAccountCode($li->product_type),
            'LineAmount' => $li->line_total,
        ])->toArray();

        $payload = [
            'Type' => 'ACCREC',
            'Contact' => ['ContactID' => $xeroContactId],
            'InvoiceNumber' => $invoice->invoice_number,
            'Reference' => $invoice->invoice_number,
            'Date' => $invoice->issued_date->format('Y-m-d'),
            'DueDate' => $invoice->due_date->format('Y-m-d'),
            'CurrencyCode' => $invoice->currency,
            'Status' => 'AUTHORISED',
            'LineItems' => $lineItems,
        ];

        $response = $this->makeRequest('POST', '/Invoices', $payload);

        if (isset($response['Invoices'][0])) {
            $xeroInvoice = $response['Invoices'][0];
            $invoice->update([
                'xero_invoice_id' => $xeroInvoice['InvoiceID'],
                'xero_invoice_number' => $xeroInvoice['InvoiceNumber'] ?? null,
                'status' => 'issued',
            ]);

            // For top-up invoices, also record payment in Xero
            if ($invoice->invoice_type === 'top_up') {
                $this->createXeroPayment($invoice, $xeroInvoice['InvoiceID']);
            }
        }
    }

    /**
     * Push a credit note to Xero.
     */
    public function pushCreditNote(CreditNote $creditNote): void
    {
        $account = $creditNote->account;
        $xeroContactId = $this->ensureXeroContact($account);

        $payload = [
            'Type' => 'ACCRECCREDIT',
            'Contact' => ['ContactID' => $xeroContactId],
            'CreditNoteNumber' => $creditNote->credit_note_number,
            'Date' => $creditNote->issued_date->format('Y-m-d'),
            'CurrencyCode' => $creditNote->currency,
            'Status' => 'AUTHORISED',
            'LineItems' => [[
                'Description' => $creditNote->reason,
                'Quantity' => 1,
                'UnitAmount' => $creditNote->subtotal,
                'AccountCode' => '200', // Revenue account
            ]],
        ];

        $response = $this->makeRequest('POST', '/CreditNotes', $payload);

        if (isset($response['CreditNotes'][0])) {
            $creditNote->update([
                'xero_credit_note_id' => $response['CreditNotes'][0]['CreditNoteID'],
                'status' => 'issued',
            ]);

            // Apply to original invoice if specified
            if ($creditNote->original_invoice_id) {
                $originalInvoice = Invoice::find($creditNote->original_invoice_id);
                if ($originalInvoice && $originalInvoice->xero_invoice_id) {
                    $this->allocateCreditNote(
                        $response['CreditNotes'][0]['CreditNoteID'],
                        $originalInvoice->xero_invoice_id,
                        $creditNote->total
                    );
                    $creditNote->update([
                        'applied_to_invoice_id' => $creditNote->original_invoice_id,
                        'status' => 'applied',
                    ]);
                }
            }
        }
    }

    /**
     * Handle Xero payment webhook (invoice marked as paid).
     */
    public function handlePaymentWebhook(array $payload): void
    {
        foreach ($payload['events'] ?? [] as $event) {
            if ($event['eventType'] !== 'UPDATE' || $event['eventCategory'] !== 'INVOICE') {
                continue;
            }

            $xeroInvoiceId = $event['resourceId'] ?? null;
            if (!$xeroInvoiceId) continue;

            // Fetch full invoice from Xero to get payment details
            $xeroInvoice = $this->makeRequest('GET', "/Invoices/{$xeroInvoiceId}");
            $invoiceData = $xeroInvoice['Invoices'][0] ?? null;
            if (!$invoiceData) continue;

            $invoice = Invoice::where('xero_invoice_id', $xeroInvoiceId)->first();
            if (!$invoice) {
                Log::warning('Xero webhook: invoice not found', ['xero_id' => $xeroInvoiceId]);
                continue;
            }

            $xeroStatus = $invoiceData['Status'] ?? '';
            if ($xeroStatus === 'PAID') {
                $this->processInvoicePaid($invoice, $invoiceData);
            }
        }
    }

    private function processInvoicePaid(Invoice $invoice, array $xeroData): void
    {
        DB::transaction(function () use ($invoice, $xeroData) {
            $amountPaid = $xeroData['AmountPaid'] ?? $invoice->total;

            // Record payment
            Payment::create([
                'account_id' => $invoice->account_id,
                'invoice_id' => $invoice->id,
                'payment_method' => 'bank_transfer',
                'xero_payment_id' => $xeroData['Payments'][0]['PaymentID'] ?? null,
                'currency' => $invoice->currency,
                'amount' => $amountPaid,
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);

            // Create ledger entry
            $this->ledgerService()->recordInvoicePayment(
                $invoice->account_id,
                (string)$amountPaid,
                $invoice->currency,
                "xero-payment-{$invoice->id}-" . now()->timestamp,
                $invoice->id
            );

            // Update invoice
            $invoice->update([
                'amount_paid' => $amountPaid,
                'amount_due' => '0',
                'status' => 'paid',
                'paid_date' => now()->toDateString(),
            ]);

            // Update account balance (reduce outstanding)
            $balance = AccountBalance::lockForAccount($invoice->account_id);
            $balance->total_outstanding = bcsub($balance->total_outstanding, (string)$amountPaid, 4);
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            // Check if account should be reactivated
            $account = $invoice->account;
            if ($account->status === 'suspended') {
                $hasOverdue = Invoice::where('account_id', $account->id)
                    ->overdue()
                    ->exists();

                if (!$hasOverdue) {
                    $account->update(['status' => 'active']);
                    Log::info('Account reactivated after payment', ['account_id' => $account->id]);
                }
            }

            FinancialAuditLog::record(
                'invoice_paid_via_xero', 'invoice', $invoice->id,
                ['status' => 'sent'], ['status' => 'paid', 'amount' => $amountPaid],
                null, 'webhook'
            );
        });
    }

    /**
     * Ensure a Xero contact exists for this account.
     */
    public function ensureXeroContact(Account $account): string
    {
        if ($account->xero_contact_id) {
            return $account->xero_contact_id;
        }

        $payload = [
            'Name' => $account->company_name,
            'EmailAddress' => $account->billing_email ?? $account->email,
            'TaxNumber' => $account->vat_number,
            'Addresses' => [[
                'AddressType' => 'POBOX',
                'AddressLine1' => $account->address_line1,
                'AddressLine2' => $account->address_line2,
                'City' => $account->city,
                'Region' => $account->county,
                'PostalCode' => $account->postcode,
                'Country' => $account->country,
            ]],
        ];

        $response = $this->makeRequest('POST', '/Contacts', $payload);

        if (isset($response['Contacts'][0]['ContactID'])) {
            $contactId = $response['Contacts'][0]['ContactID'];
            $account->update(['xero_contact_id' => $contactId]);
            return $contactId;
        }

        throw new \RuntimeException('Failed to create Xero contact for account ' . $account->id);
    }

    private function createXeroPayment(Invoice $invoice, string $xeroInvoiceId): void
    {
        $payload = [
            'Invoice' => ['InvoiceID' => $xeroInvoiceId],
            'Account' => ['Code' => config('services.xero.bank_account_code', '090')],
            'Date' => now()->format('Y-m-d'),
            'Amount' => $invoice->total,
            'Reference' => "Stripe: {$invoice->invoice_number}",
        ];

        $this->makeRequest('PUT', '/Payments', $payload);
    }

    private function allocateCreditNote(string $creditNoteId, string $invoiceId, string $amount): void
    {
        $payload = [
            'Allocations' => [[
                'Invoice' => ['InvoiceID' => $invoiceId],
                'Amount' => $amount,
                'Date' => now()->format('Y-m-d'),
            ]],
        ];

        $this->makeRequest('PUT', "/CreditNotes/{$creditNoteId}/Allocations", $payload);
    }

    private function mapTaxType(string $taxRate): string
    {
        return bccomp($taxRate, '0', 2) > 0 ? 'OUTPUT2' : 'NONE';
    }

    private function mapRevenueAccountCode(string $productType): string
    {
        return match ($productType) {
            'sms' => '200',
            'rcs_basic', 'rcs_single' => '201',
            'ai_query' => '202',
            'virtual_number_monthly', 'shortcode_monthly' => '203',
            'support', 'inbound_sms' => '204',
            default => '200',
        };
    }

    /**
     * Make an authenticated request to Xero API with rate limiting.
     */
    private function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        // Respect Xero rate limit: 60 calls/minute
        $this->waitForRateLimit();

        $token = $this->getAccessToken();

        $request = Http::withToken($token)
            ->withHeaders(['Xero-Tenant-Id' => $this->tenantId])
            ->acceptJson();

        $url = $this->baseUrl . $endpoint;

        $response = match (strtoupper($method)) {
            'GET' => $request->get($url),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            default => throw new \InvalidArgumentException("Unsupported method: {$method}"),
        };

        if ($response->failed()) {
            Log::error('Xero API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException("Xero API call failed: {$response->status()}");
        }

        return $response->json();
    }

    private function waitForRateLimit(): void
    {
        $key = 'xero_api_calls';
        $calls = Cache::get($key, 0);

        if ($calls >= 50) { // Leave headroom below 60 limit
            $ttl = Cache::get("{$key}_ttl", 60);
            sleep(max(1, $ttl));
        }

        Cache::increment($key);
        if ($calls === 0) {
            Cache::put("{$key}_ttl", 60, 60);
        }
    }

    private function getAccessToken(): string
    {
        // OAuth2 token management — use cached token, refresh if expired
        return Cache::remember('xero_access_token', 1700, function () {
            $response = Http::asForm()->post('https://identity.xero.com/connect/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => config('services.xero.refresh_token'),
                'client_id' => config('services.xero.client_id'),
                'client_secret' => config('services.xero.client_secret'),
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('Failed to refresh Xero token');
            }

            $data = $response->json();

            // Store new refresh token
            // In production, this should be stored securely (DB or secrets manager)
            Cache::put('xero_refresh_token', $data['refresh_token'], 5184000); // 60 days

            return $data['access_token'];
        });
    }

    private function ledgerService(): LedgerService
    {
        return app(LedgerService::class);
    }
}

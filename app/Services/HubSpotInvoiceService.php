<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotInvoiceService
{
    private string $baseUrl = 'https://api.hubapi.com/crm/v3/objects/invoices';
    private ?string $accessToken;

    public function __construct()
    {
        $this->accessToken = env('HUBSPOT_ACCESS_TOKEN');
    }

    public function isConfigured(): bool
    {
        return !empty($this->accessToken);
    }

    public function fetchInvoices(array $filters = []): array
    {
        if (!$this->isConfigured()) {
            Log::warning('HubSpot access token not configured - using mock invoice data');
            return $this->getMockInvoices($filters);
        }

        try {
            $properties = implode(',', [
                'hs_invoice_number',
                'hs_invoice_status',
                'hs_invoice_date',
                'hs_due_date',
                'hs_amount_billed',
                'hs_balance_due',
                'hs_tax',
                'hs_currency_code',
                'hs_pdf_download_link',
                'hs_billing_period_start_date',
                'hs_billing_period_end_date',
                'hs_payment_date',
                'hs_subtotal',
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl, [
                'properties' => $properties,
                'limit' => 100,
            ]);

            if ($response->failed()) {
                Log::error('HubSpot Invoice API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to fetch invoice data from HubSpot',
                    'invoices' => [],
                ];
            }

            $data = $response->json();
            Log::info('HubSpot invoices fetched successfully', [
                'invoice_count' => count($data['results'] ?? []),
                'timestamp' => now()->toIso8601String(),
            ]);

            $invoices = $this->mapInvoices($data['results'] ?? []);

            return [
                'success' => true,
                'invoices' => $invoices,
                'summary' => $this->calculateSummary($invoices),
            ];

        } catch (\Exception $e) {
            Log::error('HubSpot Invoice API exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Error connecting to invoice service: ' . $e->getMessage(),
                'invoices' => [],
            ];
        }
    }

    public function fetchInvoice(string $invoiceId): array
    {
        if (!$this->isConfigured()) {
            Log::warning('HubSpot access token not configured - using mock invoice data');
            return $this->getMockInvoiceDetail($invoiceId);
        }

        try {
            $properties = implode(',', [
                'hs_invoice_number',
                'hs_invoice_status',
                'hs_invoice_date',
                'hs_due_date',
                'hs_amount_billed',
                'hs_balance_due',
                'hs_tax',
                'hs_currency_code',
                'hs_pdf_download_link',
                'hs_billing_period_start_date',
                'hs_billing_period_end_date',
                'hs_payment_date',
                'hs_subtotal',
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/{$invoiceId}", [
                'properties' => $properties,
                'associations' => 'line_items',
            ]);

            if ($response->failed()) {
                Log::error('HubSpot Invoice detail API error', [
                    'invoice_id' => $invoiceId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to fetch invoice details',
                ];
            }

            $data = $response->json();
            $invoice = $this->mapSingleInvoice($data);

            $lineItems = $this->fetchLineItems($invoiceId);
            $invoice['lineItems'] = $lineItems;

            Log::info('HubSpot invoice detail fetched', [
                'invoice_id' => $invoiceId,
                'timestamp' => now()->toIso8601String(),
            ]);

            return [
                'success' => true,
                'invoice' => $invoice,
            ];

        } catch (\Exception $e) {
            Log::error('HubSpot Invoice detail exception', [
                'invoice_id' => $invoiceId,
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => 'Error fetching invoice details: ' . $e->getMessage(),
            ];
        }
    }

    private function fetchLineItems(string $invoiceId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/{$invoiceId}/associations/line_items");

            if ($response->failed()) {
                return [];
            }

            $associations = $response->json()['results'] ?? [];
            $lineItems = [];

            foreach ($associations as $assoc) {
                $lineItemId = $assoc['id'] ?? null;
                if ($lineItemId) {
                    $lineItem = $this->fetchLineItemDetail($lineItemId);
                    if ($lineItem) {
                        $lineItems[] = $lineItem;
                    }
                }
            }

            return $lineItems;

        } catch (\Exception $e) {
            Log::warning('Failed to fetch line items', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function fetchLineItemDetail(string $lineItemId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->get("https://api.hubapi.com/crm/v3/objects/line_items/{$lineItemId}", [
                'properties' => 'name,description,quantity,price,amount,hs_product_id',
            ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            $props = $data['properties'] ?? [];

            return [
                'id' => $data['id'],
                'name' => $props['name'] ?? 'Line Item',
                'description' => $props['description'] ?? '',
                'quantity' => (int) ($props['quantity'] ?? 1),
                'unitPrice' => (float) ($props['price'] ?? 0),
                'amount' => (float) ($props['amount'] ?? 0),
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    private function mapInvoices(array $hubspotInvoices): array
    {
        return array_map(function ($invoice) {
            return $this->mapSingleInvoice($invoice);
        }, $hubspotInvoices);
    }

    private function mapSingleInvoice(array $invoice): array
    {
        $props = $invoice['properties'] ?? [];
        
        $status = strtolower($props['hs_invoice_status'] ?? 'draft');
        $statusMap = [
            'open' => 'pending',
            'paid' => 'paid',
            'overdue' => 'overdue',
            'draft' => 'draft',
            'voided' => 'cancelled',
        ];
        $mappedStatus = $statusMap[$status] ?? $status;

        return [
            'id' => $invoice['id'],
            'invoiceNumber' => $props['hs_invoice_number'] ?? $invoice['id'],
            'status' => $mappedStatus,
            'issueDate' => $props['hs_invoice_date'] ?? null,
            'dueDate' => $props['hs_due_date'] ?? null,
            'paymentDate' => $props['hs_payment_date'] ?? null,
            'billingPeriodStart' => $props['hs_billing_period_start_date'] ?? null,
            'billingPeriodEnd' => $props['hs_billing_period_end_date'] ?? null,
            'subtotal' => (float) ($props['hs_subtotal'] ?? 0),
            'vat' => (float) ($props['hs_tax'] ?? 0),
            'total' => (float) ($props['hs_amount_billed'] ?? 0),
            'balanceDue' => (float) ($props['hs_balance_due'] ?? 0),
            'currency' => $props['hs_currency_code'] ?? 'GBP',
            'pdfUrl' => $props['hs_pdf_download_link'] ?? null,
        ];
    }

    private function calculateSummary(array $invoices): array
    {
        $totalInvoices = count($invoices);
        $paidAmount = 0;
        $pendingAmount = 0;
        $overdueAmount = 0;

        foreach ($invoices as $invoice) {
            switch ($invoice['status']) {
                case 'paid':
                    $paidAmount += $invoice['total'];
                    break;
                case 'pending':
                    $pendingAmount += $invoice['balanceDue'];
                    break;
                case 'overdue':
                    $overdueAmount += $invoice['balanceDue'];
                    break;
            }
        }

        return [
            'totalInvoices' => $totalInvoices,
            'paidAmount' => $paidAmount,
            'pendingAmount' => $pendingAmount,
            'overdueAmount' => $overdueAmount,
        ];
    }

    public function fetchAccountSummary(): array
    {
        $mockSummary = [
            'success' => true,
            'isMockData' => true,
            'billingMode' => 'prepaid',
            'currentBalance' => 2450.00,
            'creditLimit' => 5000.00,
            'availableCredit' => 7450.00,
            'accountStatus' => 'active',
            'currency' => 'GBP',
            'lastUpdated' => now()->toIso8601String(),
        ];

        if (!$this->isConfigured()) {
            Log::info('Account summary fetched (mock data)');
            return $mockSummary;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->get('https://api.hubapi.com/crm/v3/objects/companies', [
                'properties' => 'hs_account_balance,hs_credit_limit,hs_billing_mode,hs_account_status',
                'limit' => 1,
            ]);

            if ($response->failed()) {
                Log::warning('Failed to fetch account summary from HubSpot, using mock data');
                return $mockSummary;
            }

            $data = $response->json();
            $company = $data['results'][0] ?? null;

            if (!$company) {
                return $mockSummary;
            }

            $props = $company['properties'] ?? [];
            $currentBalance = (float) ($props['hs_account_balance'] ?? 2450.00);
            $creditLimit = (float) ($props['hs_credit_limit'] ?? 5000.00);
            $billingMode = strtolower($props['hs_billing_mode'] ?? 'prepaid');
            $accountStatus = strtolower($props['hs_account_status'] ?? 'active');

            $availableCredit = $currentBalance + $creditLimit;

            if ($availableCredit < 0) {
                $accountStatus = 'credit_hold';
            }

            return [
                'success' => true,
                'isMockData' => false,
                'billingMode' => $billingMode,
                'currentBalance' => $currentBalance,
                'creditLimit' => $creditLimit,
                'availableCredit' => $availableCredit,
                'accountStatus' => $accountStatus,
                'currency' => 'GBP',
                'lastUpdated' => now()->toIso8601String(),
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching account summary', ['error' => $e->getMessage()]);
            return $mockSummary;
        }
    }

    private function getMockInvoices(array $filters = []): array
    {
        $invoices = [
            [
                'id' => 'hs_inv_001',
                'invoiceNumber' => 'INV-2025-0012',
                'status' => 'paid',
                'issueDate' => '2025-01-02',
                'dueDate' => '2025-01-16',
                'paymentDate' => '2025-01-05',
                'billingPeriodStart' => '2025-01-01',
                'billingPeriodEnd' => '2025-01-31',
                'subtotal' => 2800.00,
                'vat' => 560.00,
                'total' => 3360.00,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_002',
                'invoiceNumber' => 'INV-2025-0011',
                'status' => 'paid',
                'issueDate' => '2024-12-15',
                'dueDate' => '2024-12-29',
                'paymentDate' => '2024-12-15',
                'billingPeriodStart' => '2024-12-01',
                'billingPeriodEnd' => '2024-12-31',
                'subtotal' => 99.99,
                'vat' => 20.00,
                'total' => 119.99,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_003',
                'invoiceNumber' => 'INV-2025-0010',
                'status' => 'paid',
                'issueDate' => '2024-12-01',
                'dueDate' => '2024-12-15',
                'paymentDate' => '2024-12-03',
                'billingPeriodStart' => null,
                'billingPeriodEnd' => null,
                'subtotal' => 750.00,
                'vat' => 150.00,
                'total' => 900.00,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_004',
                'invoiceNumber' => 'INV-2025-0009',
                'status' => 'paid',
                'issueDate' => '2024-11-20',
                'dueDate' => '2024-12-04',
                'paymentDate' => '2024-11-22',
                'billingPeriodStart' => '2024-11-20',
                'billingPeriodEnd' => '2025-11-19',
                'subtotal' => 120.00,
                'vat' => 24.00,
                'total' => 144.00,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_005',
                'invoiceNumber' => 'INV-2025-0008',
                'status' => 'issued',
                'issueDate' => '2024-12-20',
                'dueDate' => '2025-01-20',
                'paymentDate' => null,
                'billingPeriodStart' => '2024-12-01',
                'billingPeriodEnd' => '2024-12-31',
                'subtotal' => 1250.00,
                'vat' => 250.00,
                'total' => 1500.00,
                'balanceDue' => 1500.00,
                'currency' => 'GBP',
                'pdfUrl' => 'https://example.hubspot.com/invoice/hs_inv_005/pdf',
            ],
            [
                'id' => 'hs_inv_006',
                'invoiceNumber' => 'INV-2025-0007',
                'status' => 'overdue',
                'issueDate' => '2024-11-01',
                'dueDate' => '2024-12-01',
                'paymentDate' => null,
                'billingPeriodStart' => '2024-11-01',
                'billingPeriodEnd' => '2024-11-30',
                'subtotal' => 850.00,
                'vat' => 170.00,
                'total' => 1020.00,
                'balanceDue' => 1020.00,
                'currency' => 'GBP',
                'pdfUrl' => 'https://example.hubspot.com/invoice/hs_inv_006/pdf',
            ],
            [
                'id' => 'hs_inv_007',
                'invoiceNumber' => 'INV-2025-0006',
                'status' => 'paid',
                'issueDate' => '2024-10-15',
                'dueDate' => '2024-10-29',
                'paymentDate' => '2024-10-18',
                'billingPeriodStart' => null,
                'billingPeriodEnd' => null,
                'subtotal' => 300.00,
                'vat' => 60.00,
                'total' => 360.00,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_008',
                'invoiceNumber' => 'INV-2025-0005',
                'status' => 'paid',
                'issueDate' => '2024-10-01',
                'dueDate' => '2024-10-15',
                'paymentDate' => '2024-10-01',
                'billingPeriodStart' => '2024-10-01',
                'billingPeriodEnd' => '2024-10-31',
                'subtotal' => 99.99,
                'vat' => 20.00,
                'total' => 119.99,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_009',
                'invoiceNumber' => 'INV-2025-0004',
                'status' => 'paid',
                'issueDate' => '2024-09-20',
                'dueDate' => '2024-10-04',
                'paymentDate' => '2024-09-25',
                'billingPeriodStart' => '2024-09-01',
                'billingPeriodEnd' => '2024-09-30',
                'subtotal' => 37.50,
                'vat' => 7.50,
                'total' => 45.00,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_010',
                'invoiceNumber' => 'INV-2025-0003',
                'status' => 'paid',
                'issueDate' => '2024-09-01',
                'dueDate' => '2024-09-15',
                'paymentDate' => '2024-09-01',
                'billingPeriodStart' => '2024-09-01',
                'billingPeriodEnd' => '2024-09-30',
                'subtotal' => 99.99,
                'vat' => 20.00,
                'total' => 119.99,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_011',
                'invoiceNumber' => 'INV-2025-0002',
                'status' => 'paid',
                'issueDate' => '2024-08-15',
                'dueDate' => '2024-08-29',
                'paymentDate' => '2024-08-18',
                'billingPeriodStart' => null,
                'billingPeriodEnd' => null,
                'subtotal' => 4500.00,
                'vat' => 900.00,
                'total' => 5400.00,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
            [
                'id' => 'hs_inv_012',
                'invoiceNumber' => 'INV-2025-0001',
                'status' => 'paid',
                'issueDate' => '2024-08-01',
                'dueDate' => '2024-08-15',
                'paymentDate' => '2024-08-01',
                'billingPeriodStart' => '2024-08-01',
                'billingPeriodEnd' => '2024-08-31',
                'subtotal' => 99.99,
                'vat' => 20.00,
                'total' => 119.99,
                'balanceDue' => 0.00,
                'currency' => 'GBP',
                'pdfUrl' => null,
            ],
        ];

        return [
            'success' => true,
            'isMockData' => true,
            'invoices' => $invoices,
            'summary' => $this->calculateSummary($invoices),
        ];
    }

    private function getMockInvoiceDetail(string $invoiceId): array
    {
        $mockInvoices = $this->getMockInvoices()['invoices'];
        $invoice = collect($mockInvoices)->firstWhere('id', $invoiceId);

        if (!$invoice) {
            return [
                'success' => false,
                'error' => 'Invoice not found',
            ];
        }

        $lineItemsByInvoice = [
            'hs_inv_001' => [
                ['id' => 'li_001', 'name' => 'SMS Credits - Enterprise Tier', 'description' => '100,000 SMS @ £0.028/msg', 'quantity' => 100000, 'unitPrice' => 0.028, 'amount' => 2800.00],
            ],
            'hs_inv_002' => [
                ['id' => 'li_002', 'name' => 'Pro Platform Subscription', 'description' => 'December 2024', 'quantity' => 1, 'unitPrice' => 99.99, 'amount' => 99.99],
            ],
            'hs_inv_003' => [
                ['id' => 'li_003', 'name' => 'SMS Credits - Starter Tier', 'description' => '25,000 SMS @ £0.030/msg', 'quantity' => 25000, 'unitPrice' => 0.030, 'amount' => 750.00],
            ],
            'hs_inv_004' => [
                ['id' => 'li_004', 'name' => 'Virtual Mobile Number', 'description' => '+44 7700 900123 (12 months)', 'quantity' => 1, 'unitPrice' => 120.00, 'amount' => 120.00],
            ],
            'hs_inv_005' => [
                ['id' => 'li_005', 'name' => 'SMS Credits - Enterprise Tier', 'description' => '50,000 SMS @ £0.025/msg', 'quantity' => 50000, 'unitPrice' => 0.025, 'amount' => 1250.00],
            ],
        ];

        $invoice['lineItems'] = $lineItemsByInvoice[$invoiceId] ?? [
            ['id' => 'li_default', 'name' => 'Service Charge', 'description' => 'Monthly service', 'quantity' => 1, 'unitPrice' => $invoice['subtotal'], 'amount' => $invoice['subtotal']],
        ];

        return [
            'success' => true,
            'isMockData' => true,
            'invoice' => $invoice,
        ];
    }
}

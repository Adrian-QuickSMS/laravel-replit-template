<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\AccountBalance;
use App\Models\Account;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InvoiceApiController extends Controller
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function index(Request $request): JsonResponse
    {
        $accountId = auth()->user()?->tenant_id;

        if (!$accountId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $query = Invoice::where('account_id', $accountId)
            ->orderBy('issued_date', 'desc');

        if ($status = $request->input('status')) {
            if ($status === 'issued') {
                $query->where('status', 'sent');
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->input('search')) {
            $query->where('invoice_number', 'ilike', "%{$search}%");
        }

        if ($year = $request->input('billingYear')) {
            $query->whereYear('billing_period_start', $year);
        }

        if ($month = $request->input('billingMonth')) {
            $query->whereMonth('billing_period_start', $month);
        }

        $invoices = $query->get()->map(function (Invoice $inv) {
            return $this->formatInvoice($inv);
        });

        return response()->json([
            'success' => true,
            'invoices' => $invoices,
        ]);
    }

    public function show(string $invoiceId): JsonResponse
    {
        $accountId = auth()->user()?->tenant_id;

        if (!$accountId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $invoice = Invoice::with('lineItems')
            ->where('account_id', $accountId)
            ->where('id', $invoiceId)
            ->first();

        if (!$invoice) {
            return response()->json(['success' => false, 'error' => 'Invoice not found'], 404);
        }

        $formatted = $this->formatInvoice($invoice);
        $formatted['lineItems'] = $invoice->lineItems->map(function ($item) {
            return [
                'description' => $item->description,
                'quantity' => (int) $item->quantity,
                'unitPrice' => (float) $item->unit_price,
                'total' => (float) $item->line_total,
            ];
        });

        $this->logAudit('invoice_view', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice->invoice_number,
            'amount' => (float) $invoice->total,
            'currency' => $invoice->currency ?? 'GBP',
        ]);

        return response()->json([
            'success' => true,
            'invoice' => $formatted,
        ]);
    }

    public function downloadPdf(string $invoiceId): JsonResponse
    {
        $accountId = auth()->user()?->tenant_id;

        if (!$accountId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $invoice = Invoice::where('account_id', $accountId)
            ->where('id', $invoiceId)
            ->first();

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $pdfUrl = $invoice->xero_pdf_url;

        if (!$pdfUrl) {
            return response()->json([
                'error' => 'PDF not available for this invoice',
                'message' => 'The invoice PDF is being generated. Please try again in a few moments.',
            ], 404);
        }

        $this->logAudit('pdf_download', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice->invoice_number,
            'pdf_url' => $pdfUrl,
        ]);

        return response()->json([
            'success' => true,
            'pdfUrl' => $pdfUrl,
        ]);
    }

    public function accountSummary(): JsonResponse
    {
        $accountId = auth()->user()?->tenant_id;

        if (!$accountId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $account = Account::find($accountId);
        $balance = AccountBalance::where('account_id', $accountId)->first();

        $billingMode = 'prepaid';
        $accountStatus = 'active';

        if ($account) {
            $billingMode = $account->account_type === 'postpay' ? 'postpaid' : 'prepaid';
            $accountStatus = $account->status ?? 'active';
        }

        return response()->json([
            'success' => true,
            'billingMode' => $billingMode,
            'currentBalance' => (float) ($balance->balance ?? 0),
            'creditLimit' => (float) ($balance->credit_limit ?? 0),
            'availableCredit' => (float) ($balance->effective_available ?? 0),
            'accountStatus' => $accountStatus,
            'currency' => $balance->currency ?? 'GBP',
            'lastUpdated' => $balance?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
        ]);
    }

    public function createCheckoutSession(Request $request, string $invoiceId): JsonResponse
    {
        $accountId = auth()->user()?->tenant_id;

        if (!$accountId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $invoice = Invoice::where('account_id', $accountId)
            ->where('id', $invoiceId)
            ->first();

        if (!$invoice) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'reason' => 'Invoice not found',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invoice not found',
            ], 404);
        }

        $invoiceData = $this->formatInvoice($invoice);

        $allowedStatuses = ['issued', 'overdue', 'sent'];
        if (!in_array(strtolower($invoice->status), $allowedStatuses)) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice->invoice_number,
                'reason' => 'Invalid status: ' . $invoice->status,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'This invoice cannot be paid. Status: ' . $invoice->status,
            ], 400);
        }

        if ((float) $invoice->amount_due <= 0) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice->invoice_number,
                'reason' => 'No outstanding balance',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'This invoice has no outstanding balance.',
            ], 400);
        }

        $this->logAudit('payment_attempt', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice->invoice_number,
            'amount' => (float) $invoice->amount_due,
            'currency' => $invoice->currency ?? 'GBP',
            'status' => $invoice->status,
        ]);

        $result = $this->stripeService->createInvoicePaymentSession($invoiceData);

        if (!$result['success']) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice->invoice_number,
                'reason' => 'Stripe session creation failed',
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return response()->json($result, 500);
        }

        $this->logAudit('payment_session_created', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice->invoice_number,
            'amount' => (float) $invoice->amount_due,
            'currency' => $invoice->currency ?? 'GBP',
            'session_id' => $result['sessionId'] ?? null,
        ]);

        return response()->json($result);
    }

    private function formatInvoice(Invoice $invoice): array
    {
        $status = $invoice->status;
        if ($status === 'sent') {
            $status = 'issued';
        }
        if (in_array($invoice->status, ['sent', 'overdue']) && $invoice->due_date && $invoice->due_date->isPast()) {
            $status = 'overdue';
        }

        return [
            'id' => $invoice->id,
            'invoiceNumber' => $invoice->invoice_number,
            'billingPeriodStart' => $invoice->billing_period_start?->format('Y-m-d'),
            'billingPeriodEnd' => $invoice->billing_period_end?->format('Y-m-d'),
            'issueDate' => $invoice->issued_date?->format('Y-m-d'),
            'dueDate' => $invoice->due_date?->format('Y-m-d'),
            'status' => $status,
            'subtotal' => (float) $invoice->subtotal,
            'vat' => (float) $invoice->tax_amount,
            'total' => (float) $invoice->total,
            'balanceDue' => (float) $invoice->amount_due,
            'currency' => $invoice->currency ?? 'GBP',
            'pdfUrl' => $invoice->xero_pdf_url,
            'xeroInvoiceId' => $invoice->xero_invoice_id,
        ];
    }

    private function logAudit(string $action, array $data): void
    {
        $userId = $this->getCurrentUserId();

        Log::channel('single')->info('[AUDIT] ' . strtoupper($action), array_merge([
            'action' => $action,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $data));
    }

    private function getCurrentUserId(): string
    {
        return (string) (auth()->user()?->id ?? 'anonymous');
    }
}

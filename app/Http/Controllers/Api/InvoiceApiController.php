<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HubSpotInvoiceService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InvoiceApiController extends Controller
{
    private HubSpotInvoiceService $invoiceService;
    private StripeService $stripeService;

    public function __construct(HubSpotInvoiceService $invoiceService, StripeService $stripeService)
    {
        $this->invoiceService = $invoiceService;
        $this->stripeService = $stripeService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'dateRange' => $request->input('dateRange'),
            'type' => $request->input('type'),
            'search' => $request->input('search'),
        ];

        $result = $this->invoiceService->fetchInvoices($filters);

        return response()->json($result);
    }

    public function show(string $invoiceId): JsonResponse
    {
        $result = $this->invoiceService->fetchInvoice($invoiceId);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        $this->logAudit('invoice_view', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $result['invoice']['invoiceNumber'] ?? null,
            'amount' => $result['invoice']['total'] ?? null,
            'currency' => $result['invoice']['currency'] ?? 'GBP',
        ]);

        return response()->json($result);
    }

    public function downloadPdf(string $invoiceId): JsonResponse
    {
        $result = $this->invoiceService->fetchInvoice($invoiceId);

        if (!$result['success']) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $pdfUrl = $result['invoice']['pdfUrl'] ?? null;

        if (!$pdfUrl) {
            return response()->json([
                'error' => 'PDF not available for this invoice',
                'message' => 'The invoice PDF is being generated. Please try again in a few moments.',
            ], 404);
        }

        $this->logAudit('pdf_download', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $result['invoice']['invoiceNumber'] ?? null,
            'pdf_url' => $pdfUrl,
        ]);

        return response()->json([
            'success' => true,
            'pdfUrl' => $pdfUrl,
        ]);
    }

    public function accountSummary(): JsonResponse
    {
        $summary = $this->invoiceService->fetchAccountSummary();

        return response()->json($summary);
    }

    public function createCheckoutSession(Request $request, string $invoiceId): JsonResponse
    {
        $invoiceResult = $this->invoiceService->fetchInvoice($invoiceId);

        if (!$invoiceResult['success']) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'reason' => 'Invoice not found',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invoice not found',
            ], 404);
        }

        $invoice = $invoiceResult['invoice'];

        $allowedStatuses = ['issued', 'overdue'];
        if (!in_array(strtolower($invoice['status']), $allowedStatuses)) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice['invoiceNumber'] ?? null,
                'reason' => 'Invalid status: ' . $invoice['status'],
            ]);

            return response()->json([
                'success' => false,
                'error' => 'This invoice cannot be paid. Status: ' . $invoice['status'],
            ], 400);
        }

        if (($invoice['balanceDue'] ?? 0) <= 0) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice['invoiceNumber'] ?? null,
                'reason' => 'No outstanding balance',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'This invoice has no outstanding balance.',
            ], 400);
        }

        $this->logAudit('payment_attempt', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice['invoiceNumber'] ?? null,
            'amount' => $invoice['balanceDue'] ?? 0,
            'currency' => $invoice['currency'] ?? 'GBP',
            'status' => $invoice['status'],
        ]);

        $result = $this->stripeService->createInvoicePaymentSession($invoice);

        if (!$result['success']) {
            $this->logAudit('payment_attempt_failed', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice['invoiceNumber'] ?? null,
                'reason' => 'Stripe session creation failed',
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return response()->json($result, 500);
        }

        $this->logAudit('payment_session_created', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice['invoiceNumber'] ?? null,
            'amount' => $invoice['balanceDue'] ?? 0,
            'currency' => $invoice['currency'] ?? 'GBP',
            'session_id' => $result['sessionId'] ?? null,
            'is_mock' => $result['isMock'] ?? false,
        ]);

        return response()->json($result);
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
        return 'user_demo_001';
    }
}

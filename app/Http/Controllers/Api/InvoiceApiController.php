<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HubSpotInvoiceService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
            return response()->json([
                'success' => false,
                'error' => 'Invoice not found',
            ], 404);
        }

        $invoice = $invoiceResult['invoice'];

        $allowedStatuses = ['issued', 'overdue'];
        if (!in_array(strtolower($invoice['status']), $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'error' => 'This invoice cannot be paid. Status: ' . $invoice['status'],
            ], 400);
        }

        if (($invoice['balanceDue'] ?? 0) <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'This invoice has no outstanding balance.',
            ], 400);
        }

        $result = $this->stripeService->createInvoicePaymentSession($invoice);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}

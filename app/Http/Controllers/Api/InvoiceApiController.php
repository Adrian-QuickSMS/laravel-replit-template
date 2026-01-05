<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HubSpotInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceApiController extends Controller
{
    private HubSpotInvoiceService $invoiceService;

    public function __construct(HubSpotInvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
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

        // TODO: Implement actual Stripe Checkout Session creation
        // This requires STRIPE_SECRET_KEY to be configured
        // 
        // Example Stripe integration:
        // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        // $session = \Stripe\Checkout\Session::create([
        //     'payment_method_types' => ['card'],
        //     'line_items' => [[
        //         'price_data' => [
        //             'currency' => strtolower($invoice['currency'] ?? 'gbp'),
        //             'product_data' => [
        //                 'name' => 'Invoice ' . $invoice['invoiceNumber'],
        //                 'description' => 'Payment for invoice ' . $invoice['invoiceNumber'],
        //             ],
        //             'unit_amount' => (int) ($invoice['balanceDue'] * 100),
        //         ],
        //         'quantity' => 1,
        //     ]],
        //     'mode' => 'payment',
        //     'success_url' => route('reporting.invoices') . '?payment=success&invoice=' . $invoiceId,
        //     'cancel_url' => route('reporting.invoices') . '?payment=cancelled',
        //     'metadata' => [
        //         'invoice_id' => $invoiceId,
        //         'invoice_number' => $invoice['invoiceNumber'],
        //     ],
        // ]);
        // return response()->json([
        //     'success' => true,
        //     'checkoutUrl' => $session->url,
        //     'sessionId' => $session->id,
        // ]);

        \Illuminate\Support\Facades\Log::info('Invoice payment initiated', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice['invoiceNumber'],
            'amount' => $invoice['balanceDue'],
            'currency' => $invoice['currency'] ?? 'GBP',
        ]);

        $mockCheckoutUrl = route('reporting.invoices') . '?payment=success&invoice=' . $invoiceId;

        return response()->json([
            'success' => true,
            'checkoutUrl' => $mockCheckoutUrl,
            'message' => 'Mock checkout - Stripe integration pending',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Billing\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
    ) {}

    /**
     * GET /api/v1/invoices
     */
    public function index(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $perPage = min((int)$request->input('per_page', 25), 100);

        $invoices = $this->invoiceService->getInvoicesForAccount($accountId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/invoices/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoiceDetail($id);

        // Tenant isolation
        if ($invoice->account_id !== $request->user()->account_id) {
            return response()->json(['success' => false, 'error' => 'Not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * GET /api/v1/invoices/{id}/pdf
     * Redirect to Xero PDF URL.
     */
    public function pdf(Request $request, string $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoiceDetail($id);

        if ($invoice->account_id !== $request->user()->account_id) {
            return response()->json(['success' => false, 'error' => 'Not found.'], 404);
        }

        if (!$invoice->xero_pdf_url) {
            return response()->json(['success' => false, 'error' => 'PDF not yet available.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['pdf_url' => $invoice->xero_pdf_url],
        ]);
    }
}

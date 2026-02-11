<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Account Activation Controller
 *
 * Handles the 5-section account activation process:
 * 1. Sign Up Details (completed during signup)
 * 2. Company Information
 * 3. Support & Operations
 * 4. Contract Signatory
 * 5. Billing, VAT and Tax Information
 *
 * All routes require authentication
 * Only owners and admins can update activation details
 */
class AccountActivationController extends Controller
{
    /**
     * Get account activation status and progress
     *
     * GET /api/account/activation/status
     */
    public function getStatus(Request $request)
    {
        try {
            $user = $request->user();
            $account = $user->account;

            // Refresh completion flags
            $account->updateActivationStatus();

            return response()->json([
                'status' => 'success',
                'data' => $account->getActivationProgress()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get activation status error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Update Company Information (Section 2)
     *
     * PUT /api/account/activation/company-info
     */
    public function updateCompanyInfo(Request $request)
    {
        try {
            $user = $request->user();

            // Only owners and admins can update
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only account owners and admins can update activation details.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                // Mandatory fields
                'company_type' => 'required|in:uk_limited,sole_trader,government_nhs,other',
                'business_sector' => 'required|string|in:' . implode(',', array_keys(config('business_sectors.sectors'))),
                'website' => ['required', 'url', 'regex:/^https:\/\//'],
                'address_line1' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'postcode' => 'required|string|max:20',
                'country' => 'required|string|size:2',

                // Conditional: Company number mandatory for UK Limited
                'company_number' => 'required_if:company_type,uk_limited|nullable|string|size:8',

                // Optional fields
                'trading_name' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'county' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $account = $user->account;
            $account->update($request->only([
                'company_type',
                'trading_name',
                'business_sector',
                'website',
                'company_number',
                'address_line1',
                'address_line2',
                'city',
                'county',
                'postcode',
                'country',
            ]));

            // Update activation status
            $account->updateActivationStatus();

            return response()->json([
                'status' => 'success',
                'message' => 'Company information updated successfully',
                'data' => $account->toPortalArray()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update company info error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Update Support & Operations Contacts (Section 3)
     *
     * PUT /api/account/activation/support-operations
     */
    public function updateSupportOperations(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                // Support Contact (mandatory)
                'support_contact_name' => 'required|string|max:100',
                'support_contact_email' => 'required|email|max:255',
                'support_contact_phone' => 'required|string|max:20',

                // Operations Contact (mandatory)
                'operations_contact_name' => 'required|string|max:100',
                'operations_contact_email' => 'required|email|max:255',
                'operations_contact_phone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $account = $user->account;
            $account->update($request->only([
                'support_contact_name',
                'support_contact_email',
                'support_contact_phone',
                'operations_contact_name',
                'operations_contact_email',
                'operations_contact_phone',
            ]));

            // Update activation status
            $account->updateActivationStatus();

            return response()->json([
                'status' => 'success',
                'message' => 'Support and operations contacts updated successfully',
                'data' => $account->toPortalArray()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update support operations error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Update Contract Signatory (Section 4)
     *
     * PUT /api/account/activation/contract-signatory
     */
    public function updateContractSignatory(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'signatory_name' => 'required|string|max:100',
                'signatory_title' => 'required|string|max:100',
                'signatory_email' => 'required|email|max:255',
                'contract_agreed' => 'required|accepted',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $account = $user->account;

            // Get contract version from config
            $contractVersion = config('consent.versions.terms', '1.0');

            $account->update([
                'signatory_name' => $request->signatory_name,
                'signatory_title' => $request->signatory_title,
                'signatory_email' => $request->signatory_email,
                'contract_agreed' => true,
                'contract_signed_at' => now(),
                'contract_signed_ip' => $request->ip(),
                'contract_version' => $contractVersion,
            ]);

            // Update activation status
            $account->updateActivationStatus();

            // Log contract signing
            \Log::info('Contract signed', [
                'account_id' => $account->id,
                'account_number' => $account->account_number,
                'signatory_name' => $request->signatory_name,
                'signatory_email' => $request->signatory_email,
                'ip_address' => $request->ip(),
                'contract_version' => $contractVersion,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Contract signatory details recorded successfully',
                'data' => $account->toPortalArray()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update contract signatory error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Update Billing, VAT and Tax Information (Section 5)
     *
     * PUT /api/account/activation/billing-vat
     */
    public function updateBillingVat(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                // Billing Contact (optional)
                'billing_contact_name' => 'nullable|string|max:100',
                'billing_contact_phone' => 'nullable|string|max:20',

                // Billing Address
                'billing_address_same_as_registered' => 'required|boolean',
                'billing_address_line1' => 'required_if:billing_address_same_as_registered,false|nullable|string|max:255',
                'billing_address_line2' => 'nullable|string|max:255',
                'billing_city' => 'required_if:billing_address_same_as_registered,false|nullable|string|max:100',
                'billing_county' => 'nullable|string|max:100',
                'billing_postcode' => 'required_if:billing_address_same_as_registered,false|nullable|string|max:20',
                'billing_country' => 'required_if:billing_address_same_as_registered,false|nullable|string|size:2',

                // VAT
                'vat_registered' => 'required|boolean',
                'vat_number' => 'required_if:vat_registered,true|nullable|string|max:50',

                // Tax
                'tax_id' => 'nullable|string|max:50',
                'tax_country' => 'nullable|string|size:2',

                // Payment Terms
                'purchase_order_required' => 'required|boolean',
                'payment_terms' => 'required|in:immediate,net_7,net_14,net_30,net_60',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $account = $user->account;
            $account->update($request->only([
                'billing_contact_name',
                'billing_contact_phone',
                'billing_address_same_as_registered',
                'billing_address_line1',
                'billing_address_line2',
                'billing_city',
                'billing_county',
                'billing_postcode',
                'billing_country',
                'vat_registered',
                'vat_number',
                'tax_id',
                'tax_country',
                'purchase_order_required',
                'payment_terms',
            ]));

            // Update activation status
            $account->updateActivationStatus();

            return response()->json([
                'status' => 'success',
                'message' => 'Billing and tax information updated successfully',
                'data' => $account->toPortalArray()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update billing VAT error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Complete activation and go live
     *
     * POST /api/account/activation/complete
     */
    public function completeActivation(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->isOwner()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only the account owner can complete activation'
                ], 403);
            }

            $account = $user->account;

            // Refresh completion status
            $account->updateActivationStatus();

            // Check if all sections are complete
            if (!$account->activation_complete) {
                $progress = $account->getActivationProgress();
                $incomplete = array_filter($progress['sections'], fn($s) => !$s['complete']);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot complete activation. Some sections are still incomplete.',
                    'incomplete_sections' => array_values($incomplete)
                ], 400);
            }

            // Already activated
            if ($account->account_type !== 'trial') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is already activated'
                ], 400);
            }

            // Change account type from trial to prepay (going live)
            $account->update([
                'account_type' => 'prepay',
                'activated_by' => $user->id,
            ]);

            // AccountObserver will handle:
            // - Expiring trial credits
            // - Logging activation event

            \Log::info('Account activated and went live', [
                'account_id' => $account->id,
                'account_number' => $account->account_number,
                'activated_by' => $user->id,
                'activated_by_email' => $user->email,
            ]);

            // TODO: Additional activation tasks
            // - Send welcome email
            // - Enable live sending
            // - Notify sales team
            // - Sync to HubSpot

            return response()->json([
                'status' => 'success',
                'message' => 'Account activated successfully. You can now send live messages!',
                'data' => $account->toPortalArray()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Complete activation error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Get available business sectors
     *
     * GET /api/account/activation/business-sectors
     */
    public function getBusinessSectors(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => config('business_sectors.sectors')
        ], 200);
    }
}

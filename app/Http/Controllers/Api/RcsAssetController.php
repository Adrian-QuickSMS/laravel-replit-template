<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RcsAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RcsAssetController extends Controller
{
    private RcsAssetService $assetService;

    public function __construct(RcsAssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function processUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'edit_params' => 'nullable|array',
            'edit_params.zoom' => 'nullable|numeric|min:100|max:300',
            'edit_params.crop_position' => 'nullable|string|in:center,top,bottom,left,right,top-left,top-right,bottom-left,bottom-right',
            'edit_params.orientation' => 'nullable|string|in:vertical_short,vertical_tall,horizontal',
            'draft_session' => 'nullable|string|max:64',
        ]);

        try {
            $result = $this->assetService->processFromUrl(
                $request->input('url'),
                $request->input('edit_params', []),
                $request->input('draft_session'),
                $request->user()?->id
            );

            Log::info('[AUDIT] RCS asset created from URL', [
                'uuid' => $result['asset']['uuid'],
                'source_url' => $request->input('url'),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('RCS asset processing failed', [
                'url' => $request->input('url'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process image. Please try again.',
            ], 500);
        }
    }

    public function processUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif|max:1024',
            'edit_params' => 'nullable|array',
            'edit_params.zoom' => 'nullable|numeric|min:100|max:300',
            'edit_params.crop_position' => 'nullable|string|in:center,top,bottom,left,right,top-left,top-right,bottom-left,bottom-right',
            'edit_params.orientation' => 'nullable|string|in:vertical_short,vertical_tall,horizontal',
            'draft_session' => 'nullable|string|max:64',
        ]);

        try {
            $result = $this->assetService->processFromUpload(
                $request->file('file'),
                $request->input('edit_params', []),
                $request->input('draft_session'),
                $request->user()?->id
            );

            Log::info('[AUDIT] RCS asset created from upload', [
                'uuid' => $result['asset']['uuid'],
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('RCS asset upload processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process image. Please try again.',
            ], 500);
        }
    }

    public function updateAsset(Request $request, string $uuid)
    {
        $request->validate([
            'edit_params' => 'required|array',
            'edit_params.zoom' => 'nullable|numeric|min:100|max:300',
            'edit_params.crop_position' => 'nullable|string|in:center,top,bottom,left,right,top-left,top-right,bottom-left,bottom-right',
            'edit_params.orientation' => 'nullable|string|in:vertical_short,vertical_tall,horizontal',
        ]);

        try {
            $result = $this->assetService->updateAsset(
                $uuid,
                $request->input('edit_params')
            );

            Log::info('[AUDIT] RCS asset updated', [
                'uuid' => $uuid,
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return response()->json($result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Asset not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('RCS asset update failed', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update image. Please try again.',
            ], 500);
        }
    }

    public function finalizeAsset(Request $request, string $uuid)
    {
        try {
            $asset = $this->assetService->finalizeAsset($uuid);

            Log::info('[AUDIT] RCS asset finalized', [
                'uuid' => $uuid,
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'asset' => [
                    'uuid' => $asset->uuid,
                    'public_url' => $asset->public_url,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Asset not found.',
            ], 404);
        }
    }
}

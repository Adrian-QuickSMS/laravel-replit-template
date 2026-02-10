<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RcsAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
            'edit_params.orientation' => 'nullable|string|in:vertical_short,vertical_medium,vertical_tall,horizontal',
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
            'edit_params.orientation' => 'nullable|string|in:vertical_short,vertical_medium,vertical_tall,horizontal',
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
            'edit_params.orientation' => 'nullable|string|in:vertical_short,vertical_medium,vertical_tall,horizontal',
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

    public function proxyImage(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        if (!preg_match('/^https?:\/\//i', $url)) {
            return response()->json(['error' => 'Invalid URL'], 422);
        }

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';

        if (empty($host)) {
            return response()->json(['error' => 'Invalid URL'], 422);
        }

        $resolvedIps = gethostbynamel($host);
        if ($resolvedIps === false) {
            return response()->json(['error' => 'Could not resolve hostname'], 422);
        }

        foreach ($resolvedIps as $ip) {
            if (
                filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
            ) {
                return response()->json(['error' => 'URL points to a restricted network address'], 403);
            }
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        try {
            $response = Http::timeout(10)
                ->withoutRedirecting()
                ->withHeaders([
                    'User-Agent' => 'QuickSMS-RCS/1.0',
                    'Accept' => 'image/*',
                ])
                ->get($url);

            if ($response->status() >= 300 && $response->status() < 400) {
                return response()->json(['error' => 'Image URL redirects are not supported. Please use the final image URL.'], 422);
            }

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch image'], 502);
            }

            $contentType = $response->header('Content-Type');
            $mimeBase = $contentType ? strtolower(explode(';', $contentType)[0]) : '';
            if (!in_array($mimeBase, $allowedMimes)) {
                return response()->json(['error' => 'Not a valid image file'], 422);
            }

            $body = $response->body();
            $maxSize = 5 * 1024 * 1024;
            if (strlen($body) > $maxSize) {
                return response()->json(['error' => 'Image exceeds 5 MB limit'], 413);
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->buffer($body);
            if (!in_array($detectedMime, $allowedMimes)) {
                return response()->json(['error' => 'File content is not a valid image'], 422);
            }

            $base64 = base64_encode($body);
            $dataUrl = 'data:' . $detectedMime . ';base64,' . $base64;

            return response()->json([
                'success' => true,
                'dataUrl' => $dataUrl,
                'contentType' => $detectedMime,
                'size' => strlen($body),
            ]);
        } catch (\Exception $e) {
            Log::error('RCS image proxy failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to proxy image'], 500);
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

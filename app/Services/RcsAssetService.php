<?php

namespace App\Services;

use App\Models\RcsAsset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class RcsAssetService
{
    private const MAX_FILE_SIZE = 250 * 1024;
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const DISK = 'rcs-assets';
    
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function processFromUrl(
        string $url,
        array $editParams = [],
        ?string $draftSession = null,
        ?int $userId = null
    ): array {
        $imageData = $this->fetchImage($url);
        
        $processedImage = $this->applyEdits($imageData['content'], $editParams);
        
        $asset = $this->saveAsset($processedImage, [
            'source_type' => 'url',
            'source_url' => $url,
            'edit_params' => $editParams,
            'draft_session' => $draftSession,
            'user_id' => $userId,
        ]);

        return [
            'success' => true,
            'asset' => [
                'uuid' => $asset->uuid,
                'public_url' => $asset->public_url,
                'width' => $asset->width,
                'height' => $asset->height,
                'file_size' => $asset->file_size,
                'mime_type' => $asset->mime_type,
            ],
        ];
    }

    public function processFromUpload(
        $file,
        array $editParams = [],
        ?string $draftSession = null,
        ?int $userId = null
    ): array {
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException('Unsupported file type. Only JPEG, PNG, and GIF are allowed.');
        }

        $content = file_get_contents($file->getRealPath());
        
        $processedImage = $this->applyEdits($content, $editParams);
        
        $asset = $this->saveAsset($processedImage, [
            'source_type' => 'upload',
            'source_url' => null,
            'edit_params' => $editParams,
            'draft_session' => $draftSession,
            'user_id' => $userId,
        ]);

        return [
            'success' => true,
            'asset' => [
                'uuid' => $asset->uuid,
                'public_url' => $asset->public_url,
                'width' => $asset->width,
                'height' => $asset->height,
                'file_size' => $asset->file_size,
                'mime_type' => $asset->mime_type,
            ],
        ];
    }

    public function updateAsset(
        string $uuid,
        array $editParams,
        ?string $imageContent = null
    ): array {
        $asset = RcsAsset::where('uuid', $uuid)->firstOrFail();
        
        if ($imageContent === null) {
            $imageContent = Storage::disk(self::DISK)->get($asset->storage_path);
        }
        
        $processedImage = $this->applyEdits($imageContent, $editParams);
        
        Storage::disk(self::DISK)->delete($asset->storage_path);
        
        $filename = $this->generateFilename($processedImage['mime_type']);
        Storage::disk(self::DISK)->put($filename, $processedImage['content']);
        
        $asset->update([
            'storage_path' => $filename,
            'public_url' => Storage::disk(self::DISK)->url($filename),
            'mime_type' => $processedImage['mime_type'],
            'file_size' => $processedImage['size'],
            'width' => $processedImage['width'],
            'height' => $processedImage['height'],
            'edit_params' => $editParams,
        ]);

        return [
            'success' => true,
            'asset' => [
                'uuid' => $asset->uuid,
                'public_url' => $asset->public_url,
                'width' => $asset->width,
                'height' => $asset->height,
                'file_size' => $asset->file_size,
                'mime_type' => $asset->mime_type,
            ],
        ];
    }

    public function finalizeAsset(string $uuid): RcsAsset
    {
        $asset = RcsAsset::where('uuid', $uuid)->firstOrFail();
        $asset->update(['is_draft' => false, 'draft_session' => null]);
        return $asset;
    }

    private function validateAndResolveUrl(string $url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format.');
        }

        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');
        
        if (!in_array($scheme, ['http', 'https'])) {
            throw new \InvalidArgumentException('Only HTTP and HTTPS URLs are allowed.');
        }

        $host = $parsed['host'] ?? '';
        
        if (empty($host)) {
            throw new \InvalidArgumentException('Invalid URL: no host specified.');
        }
        
        $host = trim($host, '[]');
        $resolvedIp = null;
        
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->validateIpAddress($host);
            $resolvedIp = $host;
        } else {
            $resolvedIp = $this->resolveAndValidateHost($host);
        }
        
        return [
            'url' => $url,
            'host' => $host,
            'resolved_ip' => $resolvedIp,
            'port' => $parsed['port'] ?? ($scheme === 'https' ? 443 : 80),
        ];
    }

    private function resolveAndValidateHost(string $host): string
    {
        $aRecords = dns_get_record($host, DNS_A);
        $aaaaRecords = dns_get_record($host, DNS_AAAA);
        
        $allIps = [];
        
        if ($aRecords) {
            foreach ($aRecords as $record) {
                if (isset($record['ip'])) {
                    $allIps[] = $record['ip'];
                }
            }
        }
        
        if ($aaaaRecords) {
            foreach ($aaaaRecords as $record) {
                if (isset($record['ipv6'])) {
                    $allIps[] = $record['ipv6'];
                }
            }
        }
        
        if (empty($allIps)) {
            throw new \InvalidArgumentException('Unable to resolve hostname.');
        }
        
        foreach ($allIps as $ip) {
            $this->validateIpAddress($ip);
        }
        
        return $allIps[0];
    }

    private function validateIpAddress(string $ip): void
    {
        $blockedPatterns = [
            '127.',
            '10.',
            '192.168.',
            '169.254.',
            '0.',
            '::1',
            'fc',
            'fd',
            'fe80:',
            '::ffff:127.',
            '::ffff:10.',
            '::ffff:192.168.',
            '::ffff:172.',
        ];
        
        foreach ($blockedPatterns as $pattern) {
            if (str_starts_with(strtolower($ip), strtolower($pattern))) {
                throw new \InvalidArgumentException('Access to private or local networks is not allowed.');
            }
        }
        
        if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) {
            throw new \InvalidArgumentException('Access to private or local networks is not allowed.');
        }
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new \InvalidArgumentException('URL resolves to a private or reserved IP address.');
        }
    }

    private function fetchImage(string $url): array
    {
        $resolved = $this->validateAndResolveUrl($url);
        
        $response = Http::timeout(10)
            ->withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_RESOLVE => [
                        $resolved['host'] . ':' . $resolved['port'] . ':' . $resolved['resolved_ip']
                    ],
                    CURLOPT_FOLLOWLOCATION => false,
                ],
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch image from URL.');
        }

        $contentType = $response->header('Content-Type') ?? '';
        $mimeType = explode(';', $contentType)[0];
        
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException('Unsupported file type. Only JPEG, PNG, and GIF are allowed.');
        }

        $content = $response->body();
        
        if (strlen($content) > self::MAX_FILE_SIZE * 4) {
            throw new \InvalidArgumentException('Source file is too large to process.');
        }

        return [
            'content' => $content,
            'mime_type' => $mimeType,
        ];
    }

    private function applyEdits(string $imageContent, array $editParams): array
    {
        $image = $this->imageManager->read($imageContent);
        
        $zoom = floatval($editParams['zoom'] ?? 100) / 100;
        $cropPosition = $editParams['crop_position'] ?? 'center';
        $orientation = $editParams['orientation'] ?? 'vertical_short';
        
        $originalWidth = $image->width();
        $originalHeight = $image->height();
        $targetRatio = $this->getTargetRatio($orientation);
        
        if ($zoom < 1 && $targetRatio) {
            $canvasWidth = max(800, $originalWidth);
            $canvasHeight = (int) ($canvasWidth / $targetRatio);
            
            $scaledWidth = (int) ($originalWidth * $zoom);
            $scaledHeight = (int) ($originalHeight * $zoom);
            
            $image->resize($scaledWidth, $scaledHeight);
            
            $canvas = $this->imageManager->create($canvasWidth, $canvasHeight)->fill('f5f5f5');
            
            $x = $this->getPlacementX($canvasWidth, $scaledWidth, $cropPosition);
            $y = $this->getPlacementY($canvasHeight, $scaledHeight, $cropPosition);
            
            $canvas->place($image, 'top-left', $x, $y);
            $image = $canvas;
        } elseif ($zoom > 1) {
            $newWidth = (int) ($originalWidth * $zoom);
            $newHeight = (int) ($originalHeight * $zoom);
            $image->resize($newWidth, $newHeight);
            
            if ($targetRatio) {
                $currentRatio = $image->width() / $image->height();
                
                if (abs($currentRatio - $targetRatio) > 0.01) {
                    $cropWidth = $image->width();
                    $cropHeight = $image->height();
                    
                    if ($currentRatio > $targetRatio) {
                        $cropWidth = (int) ($image->height() * $targetRatio);
                    } else {
                        $cropHeight = (int) ($image->width() / $targetRatio);
                    }
                    
                    $x = $this->getCropX($image->width(), $cropWidth, $cropPosition);
                    $y = $this->getCropY($image->height(), $cropHeight, $cropPosition);
                    
                    $image->crop($cropWidth, $cropHeight, $x, $y);
                }
            }
        } elseif ($targetRatio) {
            $currentRatio = $image->width() / $image->height();
            
            if (abs($currentRatio - $targetRatio) > 0.01) {
                $cropWidth = $image->width();
                $cropHeight = $image->height();
                
                if ($currentRatio > $targetRatio) {
                    $cropWidth = (int) ($image->height() * $targetRatio);
                } else {
                    $cropHeight = (int) ($image->width() / $targetRatio);
                }
                
                $x = $this->getCropX($image->width(), $cropWidth, $cropPosition);
                $y = $this->getCropY($image->height(), $cropHeight, $cropPosition);
                
                $image->crop($cropWidth, $cropHeight, $x, $y);
            }
        }
        
        $mimeType = 'image/jpeg';
        $quality = 85;
        
        do {
            $encoded = $image->toJpeg($quality);
            $content = (string) $encoded;
            
            if (strlen($content) <= self::MAX_FILE_SIZE) {
                break;
            }
            
            $quality -= 5;
            
            if ($quality < 30) {
                $scale = sqrt(self::MAX_FILE_SIZE / strlen($content));
                $image->scale(width: (int) ($image->width() * $scale));
                $encoded = $image->toJpeg(60);
                $content = (string) $encoded;
                break;
            }
        } while ($quality >= 30);

        if (strlen($content) > self::MAX_FILE_SIZE) {
            throw new \RuntimeException('Unable to compress image to meet size requirements.');
        }

        $finalImage = $this->imageManager->read($content);

        return [
            'content' => $content,
            'mime_type' => $mimeType,
            'size' => strlen($content),
            'width' => $finalImage->width(),
            'height' => $finalImage->height(),
        ];
    }

    private function getTargetRatio(string $orientation): ?float
    {
        return match ($orientation) {
            'vertical_short' => 4 / 5,
            'vertical_tall' => 9 / 16,
            'horizontal' => 16 / 9,
            default => null,
        };
    }

    private function getCropX(int $imageWidth, int $cropWidth, string $position): int
    {
        return match ($position) {
            'left', 'top-left', 'bottom-left' => 0,
            'right', 'top-right', 'bottom-right' => $imageWidth - $cropWidth,
            default => (int) (($imageWidth - $cropWidth) / 2),
        };
    }

    private function getCropY(int $imageHeight, int $cropHeight, string $position): int
    {
        return match ($position) {
            'top', 'top-left', 'top-right' => 0,
            'bottom', 'bottom-left', 'bottom-right' => $imageHeight - $cropHeight,
            default => (int) (($imageHeight - $cropHeight) / 2),
        };
    }

    private function getPlacementX(int $canvasWidth, int $imageWidth, string $position): int
    {
        return match ($position) {
            'left', 'top-left', 'bottom-left' => 0,
            'right', 'top-right', 'bottom-right' => $canvasWidth - $imageWidth,
            default => (int) (($canvasWidth - $imageWidth) / 2),
        };
    }

    private function getPlacementY(int $canvasHeight, int $imageHeight, string $position): int
    {
        return match ($position) {
            'top', 'top-left', 'top-right' => 0,
            'bottom', 'bottom-left', 'bottom-right' => $canvasHeight - $imageHeight,
            default => (int) (($canvasHeight - $imageHeight) / 2),
        };
    }

    private function generateFilename(string $mimeType): string
    {
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        
        $date = date('Y/m');
        $uuid = Str::uuid();
        
        return "{$date}/{$uuid}.{$extension}";
    }

    private function saveAsset(array $processedImage, array $metadata): RcsAsset
    {
        $filename = $this->generateFilename($processedImage['mime_type']);
        
        Storage::disk(self::DISK)->put($filename, $processedImage['content']);
        
        $publicUrl = url('/storage/rcs-assets/' . $filename);
        
        return RcsAsset::create([
            'user_id' => $metadata['user_id'],
            'source_type' => $metadata['source_type'],
            'source_url' => $metadata['source_url'],
            'storage_path' => $filename,
            'public_url' => $publicUrl,
            'mime_type' => $processedImage['mime_type'],
            'file_size' => $processedImage['size'],
            'width' => $processedImage['width'],
            'height' => $processedImage['height'],
            'edit_params' => $metadata['edit_params'],
            'is_draft' => true,
            'draft_session' => $metadata['draft_session'],
        ]);
    }
}

<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Media Upload Helper
 *
 * Centralized, optimized file upload handling for CREAMS system
 *
 * Features:
 * - Automatic image optimization (resize, compress)
 * - Consistent file naming
 * - Old file cleanup
 * - Validation
 * - Multiple storage types (avatar, document, asset, etc.)
 *
 * @package App\Helpers
 */
class MediaUploadHelper
{
    /**
     * Storage configurations for different media types
     */
    const STORAGE_CONFIGS = [
        'avatar' => [
            'directory' => 'avatars',
            'max_size' => 2048, // 2MB in KB
            'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_width' => 500,
            'max_height' => 500,
            'quality' => 85,
            'optimize' => true
        ],
        'trainee_avatar' => [
            'directory' => 'trainee_avatars',
            'max_size' => 2048,
            'allowed_mimes' => ['jpg', 'jpeg', 'png'],
            'max_width' => 300,
            'max_height' => 300,
            'quality' => 80,
            'optimize' => true
        ],
        'asset_image' => [
            'directory' => 'assets',
            'max_size' => 5120, // 5MB
            'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_width' => 1200,
            'max_height' => 1200,
            'quality' => 85,
            'optimize' => true
        ],
        'document' => [
            'directory' => 'documents',
            'max_size' => 10240, // 10MB
            'allowed_mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'optimize' => false
        ],
        'letter_template' => [
            'directory' => 'template_images',
            'max_size' => 5120,
            'allowed_mimes' => ['jpg', 'jpeg', 'png'],
            'max_width' => 800,
            'max_height' => 1200,
            'quality' => 90,
            'optimize' => true
        ]
    ];

    /**
     * Upload and optimize a file
     *
     * @param UploadedFile $file The uploaded file
     * @param string $type Storage type (avatar, document, etc.)
     * @param string|null $identifier Unique identifier (user ID, trainee ID, etc.)
     * @param string|null $oldFile Path to old file to delete
     * @return string|false Stored file path or false on failure
     */
    public static function upload(
        UploadedFile $file,
        string $type = 'avatar',
        ?string $identifier = null,
        ?string $oldFile = null
    ) {
        try {
            // Get configuration
            $config = self::STORAGE_CONFIGS[$type] ?? self::STORAGE_CONFIGS['avatar'];

            // Validate file
            if (!self::validateFile($file, $config)) {
                return false;
            }

            // Generate filename
            $filename = self::generateFileName($file, $type, $identifier);

            // Get storage directory
            $directory = $config['directory'];

            // Ensure directory exists
            self::ensureDirectoryExists($directory);

            // Process and store file
            if ($config['optimize'] && self::isImage($file)) {
                // Optimize and store image
                $path = self::optimizeAndStoreImage($file, $directory, $filename, $config);
            } else {
                // Store file as-is
                $path = $file->storeAs($directory, $filename, 'public');
            }

            // Delete old file if provided
            if ($oldFile) {
                self::deleteFile($oldFile);
            }

            Log::info('File uploaded successfully', [
                'type' => $type,
                'filename' => $filename,
                'size' => $file->getSize(),
                'identifier' => $identifier
            ]);

            return $filename;

        } catch (Exception $e) {
            Log::error('File upload failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @param array $config
     * @return bool
     */
    private static function validateFile(UploadedFile $file, array $config): bool
    {
        // Check file size
        if ($file->getSize() > ($config['max_size'] * 1024)) {
            Log::warning('File size exceeds limit', [
                'size' => $file->getSize(),
                'limit' => $config['max_size'] * 1024
            ]);
            return false;
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $config['allowed_mimes'])) {
            Log::warning('Invalid file type', [
                'extension' => $extension,
                'allowed' => $config['allowed_mimes']
            ]);
            return false;
        }

        // Check if file is valid
        if (!$file->isValid()) {
            Log::warning('Invalid file uploaded');
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @param string $type
     * @param string|null $identifier
     * @return string
     */
    private static function generateFileName(
        UploadedFile $file,
        string $type,
        ?string $identifier = null
    ): string {
        $extension = strtolower($file->getClientOriginalExtension());
        $prefix = $identifier ? "{$type}_{$identifier}" : $type;
        $hash = Str::random(10);
        $timestamp = time();

        return "{$prefix}_{$timestamp}_{$hash}.{$extension}";
    }

    /**
     * Optimize and store image
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $filename
     * @param array $config
     * @return string
     */
    private static function optimizeAndStoreImage(
        UploadedFile $file,
        string $directory,
        string $filename,
        array $config
    ): string {
        // Use GD library for image optimization (built-in PHP)
        $imagePath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        // Load image based on type
        $image = self::loadImage($imagePath, $extension);
        if (!$image) {
            // Fallback to normal storage if image loading fails
            return $file->storeAs($directory, $filename, 'public');
        }

        // Get original dimensions
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // Calculate new dimensions
        $maxWidth = $config['max_width'] ?? 1200;
        $maxHeight = $config['max_height'] ?? 1200;

        list($newWidth, $newHeight) = self::calculateDimensions(
            $originalWidth,
            $originalHeight,
            $maxWidth,
            $maxHeight
        );

        // Create new image with optimized dimensions
        $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG/GIF
        if (in_array($extension, ['png', 'gif', 'webp'])) {
            imagealphablending($optimizedImage, false);
            imagesavealpha($optimizedImage, true);
            $transparent = imagecolorallocatealpha($optimizedImage, 255, 255, 255, 127);
            imagefilledrectangle($optimizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize image
        imagecopyresampled(
            $optimizedImage,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        // Save optimized image
        $storagePath = storage_path("app/public/{$directory}");
        self::ensureDirectoryExists($directory);

        $fullPath = "{$storagePath}/{$filename}";
        $quality = $config['quality'] ?? 85;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($optimizedImage, $fullPath, $quality);
                break;
            case 'png':
                // PNG quality is 0-9 (inverse of JPEG)
                $pngQuality = (int) ((100 - $quality) / 11);
                imagepng($optimizedImage, $fullPath, $pngQuality);
                break;
            case 'gif':
                imagegif($optimizedImage, $fullPath);
                break;
            case 'webp':
                imagewebp($optimizedImage, $fullPath, $quality);
                break;
        }

        // Free memory
        imagedestroy($image);
        imagedestroy($optimizedImage);

        return "{$directory}/{$filename}";
    }

    /**
     * Load image based on type
     *
     * @param string $path
     * @param string $extension
     * @return resource|false
     */
    private static function loadImage(string $path, string $extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return @imagecreatefromjpeg($path);
            case 'png':
                return @imagecreatefrompng($path);
            case 'gif':
                return @imagecreatefromgif($path);
            case 'webp':
                return @imagecreatefromwebp($path);
            default:
                return false;
        }
    }

    /**
     * Calculate new dimensions maintaining aspect ratio
     *
     * @param int $originalWidth
     * @param int $originalHeight
     * @param int $maxWidth
     * @param int $maxHeight
     * @return array [newWidth, newHeight]
     */
    private static function calculateDimensions(
        int $originalWidth,
        int $originalHeight,
        int $maxWidth,
        int $maxHeight
    ): array {
        // If image is smaller than max dimensions, don't upscale
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return [$originalWidth, $originalHeight];
        }

        // Calculate aspect ratio
        $ratio = $originalWidth / $originalHeight;

        if ($originalWidth > $originalHeight) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($maxWidth / $ratio);
        } else {
            $newHeight = $maxHeight;
            $newWidth = (int) ($maxHeight * $ratio);
        }

        return [$newWidth, $newHeight];
    }

    /**
     * Check if file is an image
     *
     * @param UploadedFile $file
     * @return bool
     */
    private static function isImage(UploadedFile $file): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, $imageExtensions);
    }

    /**
     * Delete a file
     *
     * @param string $filePath Relative path from storage/app/public
     * @return bool
     */
    public static function deleteFile(string $filePath): bool
    {
        try {
            // Handle both full paths and relative paths
            $relativePath = str_replace('storage/', '', $filePath);
            $relativePath = str_replace('public/', '', $relativePath);

            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
                Log::info('Old file deleted', ['path' => $relativePath]);
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::warning('Failed to delete file', [
                'path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Ensure directory exists
     *
     * @param string $directory
     * @return void
     */
    private static function ensureDirectoryExists(string $directory): void
    {
        $fullPath = storage_path("app/public/{$directory}");
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }

    /**
     * Get file URL
     *
     * @param string|null $filename
     * @param string $type
     * @param string|null $default Default image if file doesn't exist
     * @return string
     */
    public static function getUrl(?string $filename, string $type = 'avatar', ?string $default = null): string
    {
        if (!$filename) {
            return $default ?? asset('images/default-avatar.png');
        }

        $config = self::STORAGE_CONFIGS[$type] ?? self::STORAGE_CONFIGS['avatar'];
        $directory = $config['directory'];

        // Check if file exists
        if (Storage::disk('public')->exists("{$directory}/{$filename}")) {
            return asset("storage/{$directory}/{$filename}");
        }

        // Also check if filename already includes directory
        if (Storage::disk('public')->exists($filename)) {
            return asset("storage/{$filename}");
        }

        return $default ?? asset('images/default-avatar.png');
    }

    /**
     * Get file size in human-readable format
     *
     * @param string $filename
     * @param string $type
     * @return string
     */
    public static function getFileSize(string $filename, string $type = 'avatar'): string
    {
        $config = self::STORAGE_CONFIGS[$type] ?? self::STORAGE_CONFIGS['avatar'];
        $directory = $config['directory'];

        $path = "{$directory}/{$filename}";
        if (!Storage::disk('public')->exists($path)) {
            return 'Unknown';
        }

        $bytes = Storage::disk('public')->size($path);
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}

<?php
namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Centralizes item-photo storage.
 *
 * When Cloudinary is configured (CLOUDINARY_* env), uploads go to Cloudinary
 * and we store the returned secure URL. Otherwise we fall back to the local
 * "public" disk exactly like before, so local development keeps working with
 * no extra setup.
 *
 * The stored value is therefore either:
 *   - a full https URL  (Cloudinary), or
 *   - a relative path   (local disk, e.g. "lost-items/abc.jpg")
 *
 * Use the photo_url accessor on the models to render either form correctly.
 */
class ImageUploadService
{
    public static function enabled(): bool
    {
        return (bool) config('services.cloudinary.cloud_name')
            && (bool) config('services.cloudinary.api_key')
            && (bool) config('services.cloudinary.api_secret');
    }

    /**
     * Store an uploaded image and return the value to persist in the DB.
     *
     * @param  string  $folder  logical folder, e.g. "lost-items" / "found-items"
     * @return string|null
     */
    public static function store(UploadedFile $file, string $folder): ?string
    {
        if (self::enabled()) {
            try {
                $result = self::cloudinary()->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'folder'        => 'mak-lost-found/' . $folder,
                        'resource_type' => 'image',
                    ]
                );

                return $result['secure_url'] ?? null;
            } catch (\Throwable $e) {
                // Don't lose the report if Cloudinary hiccups — fall back to local.
                Log::error('Cloudinary upload failed, falling back to local disk: ' . $e->getMessage());
            }
        }

        return $file->store($folder, 'public');
    }

    /**
     * Delete a previously stored image (best-effort).
     */
    public static function delete(?string $value): void
    {
        if (! $value) {
            return;
        }

        // Cloudinary-hosted (full URL): derive the public_id and destroy it.
        if (str_starts_with($value, 'http')) {
            if (! self::enabled()) {
                return;
            }
            try {
                $publicId = self::publicIdFromUrl($value);
                if ($publicId) {
                    self::cloudinary()->uploadApi()->destroy($publicId);
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudinary delete failed: ' . $e->getMessage());
            }
            return;
        }

        // Local disk path.
        Storage::disk('public')->delete($value);
    }

    private static function cloudinary(): Cloudinary
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key'    => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => ['secure' => true],
        ]);
    }

    /**
     * Extract the Cloudinary public_id (incl. folder, excl. extension) from a
     * secure URL like:
     *   https://res.cloudinary.com/<cloud>/image/upload/v123/mak-lost-found/lost-items/abc.jpg
     */
    private static function publicIdFromUrl(string $url): ?string
    {
        if (! preg_match('#/upload/(?:v\d+/)?(.+)$#', $url, $m)) {
            return null;
        }
        // Strip the file extension.
        return preg_replace('/\.[a-zA-Z0-9]+$/', '', $m[1]);
    }
}

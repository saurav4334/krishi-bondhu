<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves a stored image path to a working URL on any host (localhost / shared
 * cPanel), supporting every format we might have in the DB:
 *
 *   - full URL            → https://cdn/x.jpg          (returned as-is)
 *   - public-disk path    → equipment/x.jpg            (served via /media route,
 *                                                        NO storage symlink needed)
 *   - public folder path  → uploads/equipment/x.jpg    (served directly)
 *   - missing / empty     → images/no-product.png placeholder
 *
 * The /media route streams the file through PHP, so images work even when
 * `php artisan storage:link` is unavailable on the host.
 */
class Media
{
    public static function url(?string $path): string
    {
        $placeholder = asset('images/no-product.png');

        if (empty(trim((string) $path))) {
            return $placeholder;
        }

        $p = ltrim(str_replace('\\', '/', trim($path)), '/');

        // 1) Already a full URL.
        if (preg_match('#^https?://#i', $p)) {
            return $p;
        }

        // Normalise away any leading public/ , storage/ or public/storage/ prefix.
        $rel = preg_replace('#^public/storage/#', '', $p);
        $rel = preg_replace('#^storage/#', '', $rel);
        $rel = preg_replace('#^public/#', '', $rel);

        // 2) File on the "public" storage disk (storage/app/public/<rel>) — stream
        //    it via the /media route so it works without a storage symlink.
        if (Storage::disk('public')->exists($rel)) {
            return url('media/' . $rel);
        }

        // 3) Physical file inside the public/ folder (e.g. uploads/equipment/x.jpg).
        if (file_exists(public_path($rel))) {
            return asset($rel);
        }

        // 4) Nothing on disk — placeholder.
        return $placeholder;
    }
}

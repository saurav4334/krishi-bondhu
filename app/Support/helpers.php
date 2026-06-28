<?php

use App\Support\Media;

if (! function_exists('image_url')) {
    /**
     * Global image-URL resolver for Blade. Returns a working URL on any host
     * (localhost / shared cPanel) for every path format we store:
     *   - full URL (https://…)            → returned as-is
     *   - public/images path              → asset(...)
     *   - uploads/… (public folder)       → asset(...)
     *   - storage/app/public path         → served via /media (no symlink)
     *   - missing / empty                 → $fallback placeholder
     *
     * Usage in Blade:
     *   <img src="{{ image_url($post->image, 'images/news/default.jpg') }}"
     *        onerror="this.onerror=null; this.src='{{ asset('images/news/default.jpg') }}';">
     */
    function image_url(?string $path, string $fallback = 'images/default.png'): string
    {
        return Media::url($path, $fallback);
    }
}

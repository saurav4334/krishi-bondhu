<?php

use App\Support\Media;

if (! function_exists('safe_image_url')) {
    /**
     * Canonical safe image-URL resolver. Returns a working URL on any host for
     * every stored format:
     *   - full https URL                  → as-is
     *   - public/images/… or images/…     → asset(...)
     *   - uploads/…                       → asset(...)
     *   - storage/… (public disk)         → /media route (no symlink needed)
     *   - null / missing file             → $fallback placeholder
     *
     * Blade: {{ safe_image_url($item->image, 'images/equipment/default.jpg') }}
     */
    function safe_image_url(?string $path, string $fallback = 'images/default.png'): string
    {
        return \App\Support\Media::url($path, $fallback);
    }
}

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

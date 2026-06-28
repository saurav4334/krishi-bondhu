<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Streams files from the "public" storage disk (storage/app/public) directly
 * through PHP. This makes uploaded images work on shared hosting even when the
 * `public/storage` symlink is missing or disabled — no symlink dependency.
 */
class MediaController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        // Block path traversal; only serve files that exist on the public disk.
        abort_if(str_contains($path, '..'), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        $headers = ['Cache-Control' => 'public, max-age=604800']; // 7-day browser cache

        // Set the MIME explicitly (so it works even without the fileinfo extension).
        $mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (isset($mimes[$ext])) {
            $headers['Content-Type'] = $mimes[$ext];
        }

        return response()->file(Storage::disk('public')->path($path), $headers);
    }
}

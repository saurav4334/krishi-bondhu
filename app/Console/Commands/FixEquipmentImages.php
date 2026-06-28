<?php

namespace App\Console\Commands;

use App\Models\EquipmentProduct;
use App\Models\EquipmentProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Repairs existing equipment image records:
 *  - Normalizes stored paths to the public-disk relative form (e.g. strips a
 *    leading "storage/", "public/" or "/") so equipment/x.jpg is consistent.
 *  - Nulls out thumbnail paths whose file no longer exists (so the placeholder
 *    shows instead of a broken icon).
 *
 * Idempotent and shared-cPanel safe. Run: php artisan equipment:fix-images
 */
class FixEquipmentImages extends Command
{
    protected $signature = 'equipment:fix-images {--dry : Show changes without saving}';

    protected $description = 'Normalize equipment image paths and clear missing thumbnails';

    public function handle(): int
    {
        $dry = $this->option('dry');
        $normalized = 0;
        $cleared = 0;

        foreach (EquipmentProduct::whereNotNull('image')->cursor() as $product) {
            $fixed = $this->normalize($product->image);

            if ($fixed !== $product->image) {
                $this->line("product #{$product->id}: [{$product->image}] -> [{$fixed}]");
                if (! $dry) {
                    $product->update(['image' => $fixed]);
                }
                $normalized++;
            }

            if ($fixed && ! Storage::disk('public')->exists($fixed)) {
                $this->warn("product #{$product->id}: missing file [{$fixed}] -> NULL (placeholder)");
                if (! $dry) {
                    $product->update(['image' => null]);
                }
                $cleared++;
            }
        }

        foreach (EquipmentProductImage::cursor() as $img) {
            $fixed = $this->normalize($img->image);
            if ($fixed !== $img->image) {
                if (! $dry) {
                    $img->update(['image' => $fixed]);
                }
                $normalized++;
            }
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Normalized {$normalized} path(s); cleared {$cleared} missing thumbnail(s).");

        return self::SUCCESS;
    }

    /** Strip leading slash, "public/" and "storage/" so paths are disk-relative. */
    private function normalize(?string $path): ?string
    {
        if (empty($path)) {
            return $path;
        }

        $p = ltrim(str_replace('\\', '/', $path), '/');
        $p = preg_replace('#^public/#', '', $p);
        $p = preg_replace('#^storage/#', '', $p);

        return $p;
    }
}

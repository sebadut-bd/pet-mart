<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Housekeeping for listing photos (scheduled daily in routes/console.php):
 *  1. Permanently removes listings deleted more than --days ago, with their photo files.
 *  2. Removes uploaded photos that were never attached to a listing or profile (older than 24 hours).
 */
class PruneListings extends Command
{
    protected $signature = 'listings:prune {--days=30 : Keep soft-deleted listings this many days} {--dry-run : Only report what would be removed}';

    protected $description = 'Remove old deleted listings and unused uploaded photos';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dry = (bool) $this->option('dry-run');

        $removedListings = 0;
        Product::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays((int) $this->option('days')))
            ->with('images')
            ->chunkById(100, function ($products) use ($disk, $dry, &$removedListings) {
                foreach ($products as $product) {
                    $files = $product->images->pluck('image_path')->filter(fn ($p) => ! preg_match('#^https?://#', $p));
                    if (! $dry) {
                        $disk->delete($files->all());
                        $product->forceDelete();
                    }
                    $removedListings++;
                }
            });

        $referenced = ProductImage::pluck('image_path')
            ->merge(User::whereNotNull('image')->pluck('image'))
            ->flip();
        $cutoff = now()->subDay()->getTimestamp();
        $orphans = collect(array_merge($disk->allFiles('products'), $disk->allFiles('avatars')))
            ->reject(fn ($path) => isset($referenced[$path]))
            ->filter(fn ($path) => $disk->lastModified($path) < $cutoff)
            ->values();

        if (! $dry) {
            $disk->delete($orphans->all());
        }

        $this->info(($dry ? '[dry run] ' : '') . "Listings removed: {$removedListings}. Unused photos removed: {$orphans->count()}.");

        return self::SUCCESS;
    }
}

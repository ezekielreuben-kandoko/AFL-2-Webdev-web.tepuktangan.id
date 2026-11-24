<?php

namespace Tests\Unit;

use App\Models\ClientAsset;
use App\Support\ImageVariant;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientAssetCacheTest extends TestCase
{
    public function test_it_serves_the_locally_cached_thumbnail_when_available(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('drive/abc123-w600.jpg', 'image');

        $asset = new ClientAsset(['drive_file_id' => 'abc123', 'file_type' => 'photo']);

        $this->assertStringContainsString('storage/drive/abc123-w600.jpg', $asset->thumbnail_url);
    }

    public function test_it_falls_back_to_google_drive_when_the_thumbnail_is_not_cached(): void
    {
        Storage::fake('public');

        $asset = new ClientAsset(['drive_file_id' => 'abc123', 'file_type' => 'photo']);

        $this->assertSame('https://lh3.googleusercontent.com/d/abc123=w600', $asset->thumbnail_url);
    }

    public function test_it_serves_the_locally_cached_feature_image_when_available(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('drive/abc123-w1200.jpg', 'image');

        $asset = new ClientAsset(['drive_file_id' => 'abc123', 'file_type' => 'photo']);

        $this->assertStringContainsString('storage/drive/abc123-w1200.jpg', $asset->url);
    }

    public function test_it_falls_back_to_google_drive_for_uncached_feature_images(): void
    {
        Storage::fake('public');

        $asset = new ClientAsset(['drive_file_id' => 'abc123', 'file_type' => 'photo']);

        $this->assertSame('https://lh3.googleusercontent.com/d/abc123=w1200', $asset->url);
    }

    public function test_it_serves_the_local_thumbnail_variant_when_available(): void
    {
        Storage::fake('public');

        $source = 'gallery/client/photo.jpg';
        $variant = ImageVariant::path($source, ImageVariant::THUMBNAIL_WIDTH);

        Storage::disk('public')->put($source, 'source');
        Storage::disk('public')->put($variant, 'thumbnail');

        $asset = new ClientAsset(['file_path' => $source, 'file_type' => 'photo']);

        $this->assertStringContainsString('storage/'.$variant, $asset->thumbnail_url);
    }

    public function test_it_serves_the_local_feature_variant_when_available(): void
    {
        Storage::fake('public');

        $source = 'gallery/client/photo.jpg';
        $variant = ImageVariant::path($source, ImageVariant::FEATURE_WIDTH);

        Storage::disk('public')->put($source, 'source');
        Storage::disk('public')->put($variant, 'feature');

        $asset = new ClientAsset(['file_path' => $source, 'file_type' => 'photo']);

        $this->assertStringContainsString('storage/'.$variant, $asset->url);
    }

    public function test_it_falls_back_to_the_original_local_file_when_no_variant_exists(): void
    {
        Storage::fake('public');

        $source = 'gallery/client/photo.jpg';
        Storage::disk('public')->put($source, 'source');

        $asset = new ClientAsset(['file_path' => $source, 'file_type' => 'photo']);

        $this->assertStringContainsString('storage/'.$source, $asset->thumbnail_url);
        $this->assertStringContainsString('storage/'.$source, $asset->url);
    }
}

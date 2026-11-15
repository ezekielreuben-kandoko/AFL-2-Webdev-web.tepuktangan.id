<?php

namespace Tests\Unit;

use App\Models\ClientAsset;
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
}

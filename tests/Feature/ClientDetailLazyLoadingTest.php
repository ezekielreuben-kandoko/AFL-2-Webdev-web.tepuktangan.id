<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientDetailLazyLoadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_detail_defers_offscreen_photos_and_marks_them_for_reveal(): void
    {
        $client = Client::factory()->create(['client_type' => 'gallery']);

        ClientAsset::factory()->create([
            'client_id' => $client->id,
            'file_path' => 'gallery/client/feature.jpg',
            'file_type' => 'photo',
            'sort_order' => 1,
        ]);

        ClientAsset::factory()->create([
            'client_id' => $client->id,
            'file_path' => 'gallery/client/second.jpg',
            'file_type' => 'photo',
            'sort_order' => 2,
        ]);

        $this->get(route('gallery.detail', $client))
            ->assertOk()
            ->assertSee('class="client-detail__photo client-detail__photo--feature"', false)
            ->assertSee('class="client-detail__photo js-lazy-image"', false)
            ->assertSee('loading="eager"', false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('fetchpriority="low"', false)
            ->assertSee('data-src="'.asset('storage/gallery/client/second.jpg').'"', false);
    }
}

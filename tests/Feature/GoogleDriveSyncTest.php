<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleDriveSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_imports_photos_and_videos_from_multiple_sources(): void
    {
        config(['services.google.drive_api_key' => 'test-key']);

        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, 'folder-a')) {
                return Http::response([
                    'files' => [
                        ['id' => 'photo-a', 'name' => 'a.jpg', 'mimeType' => 'image/jpeg'],
                        ['id' => 'doc-a', 'name' => 'a.pdf', 'mimeType' => 'application/pdf'],
                    ],
                ]);
            }

            if (str_contains($url, 'folder-b')) {
                return Http::response([
                    'files' => [
                        ['id' => 'video-b', 'name' => 'b.mp4', 'mimeType' => 'video/mp4'],
                    ],
                ]);
            }

            return Http::response(['files' => []]);
        });

        $user = User::factory()->create();

        $client = Client::factory()->create();

        $client->driveSources()->createMany([
            ['url' => 'https://drive.google.com/drive/folders/folder-a', 'drive_id' => 'folder-a', 'link_type' => 'folder', 'sort_order' => 0],
            ['url' => 'https://drive.google.com/drive/folders/folder-b', 'drive_id' => 'folder-b', 'link_type' => 'folder', 'sort_order' => 1],
        ]);

        $response = $this->actingAs($user)->post(route('admin.clients.assets.sync', $client));

        $response->assertRedirect(route('admin.clients.assets', $client));

        $this->assertDatabaseHas('client_assets', [
            'client_id' => $client->id,
            'drive_file_id' => 'photo-a',
            'file_type' => 'photo',
            'source' => 'gdrive',
        ]);

        $this->assertDatabaseHas('client_assets', [
            'client_id' => $client->id,
            'drive_file_id' => 'video-b',
            'file_type' => 'video',
            'source' => 'gdrive',
        ]);

        $this->assertDatabaseMissing('client_assets', ['drive_file_id' => 'doc-a']);

        $client->refresh();

        $this->assertNotNull($client->drive_synced_at);
        $this->assertSame('photo-a', $client->cover_drive_file_id);
    }

    public function test_sync_supports_a_single_file_source(): void
    {
        config(['services.google.drive_api_key' => 'test-key']);

        Http::fake([
            'https://www.googleapis.com/drive/v3/files/*' => Http::response([
                'id' => 'single-video',
                'name' => 'clip.mp4',
                'mimeType' => 'video/mp4',
            ]),
        ]);

        $user = User::factory()->create();

        $client = Client::factory()->create();

        $client->driveSources()->create([
            'url' => 'https://drive.google.com/file/d/single-video/view',
            'drive_id' => 'single-video',
            'link_type' => 'file',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)->post(route('admin.clients.assets.sync', $client))
            ->assertRedirect(route('admin.clients.assets', $client));

        $this->assertDatabaseHas('client_assets', [
            'client_id' => $client->id,
            'drive_file_id' => 'single-video',
            'file_type' => 'video',
        ]);
    }

    public function test_sync_requires_an_api_key(): void
    {
        config(['services.google.drive_api_key' => null]);

        $user = User::factory()->create();

        $client = Client::factory()->create();

        $client->driveSources()->create([
            'url' => 'https://drive.google.com/drive/folders/folder-a',
            'drive_id' => 'folder-a',
            'link_type' => 'folder',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('admin.clients.assets.sync', $client));

        $response->assertRedirect(route('admin.clients.assets', $client));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('client_assets', 0);
    }

    public function test_admin_edit_page_shows_saved_drive_links(): void
    {
        $user = User::factory()->create();

        $client = Client::factory()->create(['client_type' => 'gallery']);

        $client->driveSources()->create([
            'url' => 'https://drive.google.com/drive/folders/folder-a',
            'drive_id' => 'folder-a',
            'link_type' => 'folder',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)->get(route('admin.clients.edit', $client))
            ->assertOk()
            ->assertSee('https://drive.google.com/drive/folders/folder-a');
    }

    public function test_public_detail_page_renders_drive_assets(): void
    {
        $client = Client::factory()->create(['client_type' => 'gallery']);

        $client->assets()->create([
            'source' => 'gdrive',
            'drive_file_id' => 'photo-x',
            'mime_type' => 'image/jpeg',
            'file_type' => 'photo',
            'sort_order' => 0,
        ]);

        $client->assets()->create([
            'source' => 'gdrive',
            'drive_file_id' => 'video-y',
            'mime_type' => 'video/mp4',
            'file_type' => 'video',
            'sort_order' => 1,
        ]);

        $this->get(route('gallery.detail', $client))
            ->assertOk()
            ->assertSee('https://lh3.googleusercontent.com/d/photo-x=w1200', false)
            ->assertSee('https://drive.google.com/file/d/video-y/preview', false);
    }

    public function test_admin_can_store_a_client_with_multiple_drive_links(): void
    {
        config(['services.google.drive_api_key' => 'test-key']);

        Http::fake([
            'https://www.googleapis.com/drive/v3/files*' => Http::response([
                'files' => [
                    ['id' => 'photo-a', 'name' => 'a.jpg', 'mimeType' => 'image/jpeg'],
                ],
            ]),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.clients.store'), [
            'client_name' => 'New Couple',
            'client_type' => 'gallery',
            'drive_links' => "https://drive.google.com/drive/folders/folder-a\nhttps://drive.google.com/drive/folders/folder-b",
        ])->assertRedirect(route('admin.clients.index'));

        $client = Client::where('client_name', 'New Couple')->firstOrFail();

        $this->assertSame(2, $client->driveSources()->count());
        $this->assertSame('photo-a', $client->cover_drive_file_id);
        $this->assertSame(1, $client->assets()->where('source', 'gdrive')->count());
    }

    public function test_invalid_drive_link_does_not_create_a_client(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.clients.store'), [
            'client_name' => 'Bad Link',
            'client_type' => 'gallery',
            'drive_links' => 'not-a-valid-link',
        ])->assertSessionHasErrors('drive_links');

        $this->assertDatabaseMissing('clients', ['client_name' => 'Bad Link']);
    }
}

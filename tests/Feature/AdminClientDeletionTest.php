<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_assets_page_renders_delete_form_with_browser_confirmation(): void
    {
        $user = User::factory()->create();

        $client = Client::factory()->create(['client_type' => 'gallery']);

        $this->actingAs($user)->get(route('admin.clients.assets', $client))
            ->assertOk()
            ->assertSee('action="'.route('admin.clients.destroy', $client).'"', false)
            ->assertSee('value="DELETE"', false)
            ->assertSee("confirm('Hapus klien ini? Aset terkait juga akan terhapus.')", false);
    }

    public function test_delete_request_removes_client_and_redirects_to_index(): void
    {
        $user = User::factory()->create();

        $client = Client::factory()->create(['client_type' => 'gallery']);

        $this->actingAs($user)->delete(route('admin.clients.destroy', $client))
            ->assertRedirect(route('admin.clients.index'))
            ->assertSessionHas('success', 'Klien berhasil dihapus.');

        $this->assertModelMissing($client);
    }
}

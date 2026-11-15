<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_serves_responsive_webp_images(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('assets/Home/opt/DIA00001-800.webp', false)
            ->assertSee('imagesizes="100vw"', false)
            ->assertDontSee('scroll-reveal', false);
    }
}

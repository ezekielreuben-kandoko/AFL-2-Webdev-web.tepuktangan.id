<?php

namespace Tests\Unit;

use App\Services\GoogleDriveService;
use PHPUnit\Framework\TestCase;

class GoogleDriveServiceTest extends TestCase
{
    public function test_it_parses_folder_links(): void
    {
        $service = new GoogleDriveService;

        $this->assertSame(
            ['id' => '1OIFAXLVo4k2WFUKM-sA5UMDOH6YcSY_8', 'type' => 'folder'],
            $service->parseLink('https://drive.google.com/drive/folders/1OIFAXLVo4k2WFUKM-sA5UMDOH6YcSY_8?usp=drive_link')
        );
    }

    public function test_it_parses_file_links(): void
    {
        $service = new GoogleDriveService;

        $this->assertSame(
            ['id' => '13z9c12AEDBRokGI5O4GfmWxqyTUQ68du', 'type' => 'file'],
            $service->parseLink('https://drive.google.com/file/d/13z9c12AEDBRokGI5O4GfmWxqyTUQ68du/view?usp=drive_link')
        );
    }

    public function test_it_parses_open_id_links(): void
    {
        $service = new GoogleDriveService;

        $this->assertSame(
            ['id' => '1NaHtNYVpRso5ApcCQyV-zn0gwjBZKOt5', 'type' => 'file'],
            $service->parseLink('https://drive.google.com/open?id=1NaHtNYVpRso5ApcCQyV-zn0gwjBZKOt5')
        );
    }

    public function test_it_rejects_invalid_links(): void
    {
        $service = new GoogleDriveService;

        $this->assertSame(['id' => null, 'type' => null], $service->parseLink('bukan link'));
        $this->assertSame(['id' => null, 'type' => null], $service->parseLink('not-a-valid-link'));
        $this->assertSame(['id' => null, 'type' => null], $service->parseLink(''));
    }
}

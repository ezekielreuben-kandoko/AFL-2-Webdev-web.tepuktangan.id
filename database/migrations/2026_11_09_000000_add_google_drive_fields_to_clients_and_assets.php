<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('drive_folder_id')->nullable()->after('cover_image');
            $table->string('drive_folder_url', 2048)->nullable()->after('drive_folder_id');
            $table->string('drive_link_type', 20)->nullable()->after('drive_folder_url');
            $table->string('cover_drive_file_id')->nullable()->after('drive_link_type');
            $table->timestamp('drive_synced_at')->nullable()->after('cover_drive_file_id');
        });

        Schema::table('client_assets', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
            $table->string('source', 20)->default('upload')->after('file_path');
            $table->string('drive_file_id')->nullable()->index()->after('source');
            $table->string('mime_type')->nullable()->after('drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_assets', function (Blueprint $table) {
            $table->dropIndex(['drive_file_id']);
            $table->dropColumn(['source', 'drive_file_id', 'mime_type']);
            $table->string('file_path')->nullable(false)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'drive_folder_id',
                'drive_folder_url',
                'drive_link_type',
                'cover_drive_file_id',
                'drive_synced_at',
            ]);
        });
    }
};

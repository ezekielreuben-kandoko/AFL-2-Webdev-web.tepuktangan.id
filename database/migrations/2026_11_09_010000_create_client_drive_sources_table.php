<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_drive_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('url', 2048);
            $table->string('drive_id')->index();
            $table->string('link_type', 20);
            $table->string('label')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::table('client_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('client_drive_source_id')->nullable()->index()->after('drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_assets', function (Blueprint $table) {
            $table->dropIndex(['client_drive_source_id']);
            $table->dropColumn('client_drive_source_id');
        });

        Schema::dropIfExists('client_drive_sources');
    }
};

<?php

namespace App\Models;

use App\Support\ImageVariant;
use Database\Factories\ClientAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClientAsset extends Model
{
    /** @use HasFactory<ClientAssetFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'file_path',
        'source',
        'drive_file_id',
        'client_drive_source_id',
        'mime_type',
        'file_type',
        'alt',
        'sort_order',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getUrlAttribute(): string
    {
        if ($this->drive_file_id) {
            return $this->file_type === 'video'
                ? $this->embed_url
                : ($this->cachedDriveImage(1200) ?? 'https://lh3.googleusercontent.com/d/'.$this->drive_file_id.'=w1200');
        }

        return $this->localVariantUrl(ImageVariant::FEATURE_WIDTH)
            ?? asset('storage/'.$this->file_path);
    }

    public function getEmbedUrlAttribute(): string
    {
        if ($this->drive_file_id) {
            return 'https://drive.google.com/file/d/'.$this->drive_file_id.'/preview';
        }

        return asset('storage/'.$this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->drive_file_id) {
            return $this->cachedDriveImage(600) ?? 'https://lh3.googleusercontent.com/d/'.$this->drive_file_id.'=w600';
        }

        return $this->localVariantUrl(ImageVariant::THUMBNAIL_WIDTH)
            ?? asset('storage/'.$this->file_path);
    }

    public function cachedDriveImage(int $width): ?string
    {
        if (! $this->drive_file_id) {
            return null;
        }

        $path = 'drive/'.$this->drive_file_id.'-w'.$width.'.jpg';

        return Storage::disk('public')->exists($path) ? asset('storage/'.$path) : null;
    }

    private function localVariantUrl(int $width): ?string
    {
        if ($this->drive_file_id || ! $this->file_path) {
            return null;
        }

        $path = ImageVariant::path($this->file_path, $width);

        return Storage::disk('public')->exists($path) ? asset('storage/'.$path) : null;
    }
}

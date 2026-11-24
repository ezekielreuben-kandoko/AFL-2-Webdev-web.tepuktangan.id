<?php

namespace App\Models;

use App\Support\ImageVariant;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'client_name',
        'cover_image',
        'cover_drive_file_id',
        'month_date',
        'client_type',
        'drive_folder_id',
        'drive_folder_url',
        'drive_link_type',
        'drive_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'drive_synced_at' => 'datetime',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ClientAsset::class);
    }

    public function driveSources(): HasMany
    {
        return $this->hasMany(ClientDriveSource::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ClientAsset::class)
            ->where('file_type', 'video')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ClientAsset::class)
            ->where('file_type', 'photo')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_drive_file_id) {
            $path = 'drive/'.$this->cover_drive_file_id.'-w800.jpg';

            if (Storage::disk('public')->exists($path)) {
                return asset('storage/'.$path);
            }

            return 'https://lh3.googleusercontent.com/d/'.$this->cover_drive_file_id.'=w800';
        }

        if (! $this->cover_image) {
            return null;
        }

        $variant = ImageVariant::path($this->cover_image, ImageVariant::COVER_WIDTH);

        if (Storage::disk('public')->exists($variant)) {
            return asset('storage/'.$variant);
        }

        if (Storage::disk('public')->exists($this->cover_image)) {
            return asset('storage/'.$this->cover_image);
        }

        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Media extends Model
{
    protected $table = 'media';

    protected $guarded = [];

    /** Public URL for the stored file. */
    public function getUrlAttribute(): string
    {
        return asset('uploads/'.ltrim($this->path, '/'));
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->kind === 'video';
    }

    public function getReadableSizeAttribute(): string
    {
        $bytes = (int) $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        return max(1, (int) round($bytes / 1024)).' KB';
    }

    /** Remove the file from disk when the row goes. */
    protected static function booted(): void
    {
        static::deleting(function (self $media) {
            $full = public_path('uploads/'.$media->path);

            if (File::exists($full)) {
                File::delete($full);
            }
        });
    }
}

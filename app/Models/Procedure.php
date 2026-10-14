<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Procédure interne : un fichier (document, image ou vidéo) sur le disque « public », dossier « procedures ».
 */
class Procedure extends Model
{
    public const DIRECTORY = 'procedures';

    public $fillable = ['name', 'media', 'created_at'];
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(fn (self $procedure) => $procedure->created_at ??= now());
    }

    public function path(): string
    {
        return Storage::disk('public')->path(self::DIRECTORY . '/' . $this->media);
    }

    public function url(): string
    {
        return Storage::disk('public')->url(self::DIRECTORY . '/' . $this->media);
    }

    public function exists(): bool
    {
        return $this->media && Storage::disk('public')->exists(self::DIRECTORY . '/' . $this->media);
    }

    /** image, video ou document */
    public function type(): string
    {
        return match (strtolower(pathinfo((string) $this->media, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'mp4', 'avi', 'wmv', 'webm' => 'video',
            default => 'document',
        };
    }

    public function download()
    {
        abort_unless($this->exists(), 404);

        return Storage::disk('public')->download(
            self::DIRECTORY . '/' . $this->media,
            str($this->name)->slug() . '.' . pathinfo($this->media, PATHINFO_EXTENSION)
        );
    }
}

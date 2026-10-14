<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

/**
 * Enregistre une pièce jointe de passager (passeport, titre de séjour) sur le disque « public ».
 * $fileName est le chemin public, ex. « storage/passagers/passports/12.jpg ».
 * Les grandes photos sont réduites pour garder des fichiers légers.
 */
trait FileUpload
{
    public function UploadFile(UploadedFile $file, string $fileName): void
    {
        $relative = preg_replace('#^/?storage/#', '', $fileName);
        $path = Storage::disk('public')->path($relative);
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            Storage::disk('public')->putFileAs(dirname($relative), $file, basename($relative));

            return;
        }

        $image = ImageManager::gd()->read($file->getRealPath());
        $largest = max($image->width(), $image->height());
        $divider = match (true) {
            $largest > 5000 => 7,
            $largest > 4000 => 6,
            $largest > 3000 => 5,
            $largest > 2000 => 4,
            $largest > 1000 => 3,
            default => 1,
        };
        if ($divider > 1) {
            $image->resize((int) ($image->width() / $divider), (int) ($image->height() / $divider));
        }
        $image->save($path);
    }
}

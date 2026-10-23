<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ImageData
{
    /** Crop to square, resize, return base64 JPEG data URI. */
    public static function toDataUri(UploadedFile $file, int $size = 400): string
    {
        $path = $file->getRealPath();
        $mime = $file->getMimeType();

        $src = match ($mime) {
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => @imagecreatefromjpeg($path),
        };

        if (!$src) {
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);

        $dst = imagecreatetruecolor($size, $size);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled(
            $dst, $src, 0, 0,
            intdiv($w - $side, 2), intdiv($h - $side, 2),
            $size, $size, $side, $side
        );

        ob_start();
        imagejpeg($dst, null, 82);
        $jpeg = ob_get_clean();

        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }
}
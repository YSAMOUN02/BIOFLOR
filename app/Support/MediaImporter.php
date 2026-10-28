<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Adds images and videos to the media library (public/uploads/library).
 * Images are resized to at most 1600 px and saved as WebP; videos are copied.
 * A file whose content is already in the library is not added again.
 */
class MediaImporter
{
    private const IMAGE = ['png', 'jpg', 'jpeg', 'webp'];
    private const VIDEO = ['mp4', 'webm', 'ogg'];

    /** @var list<string> */
    public array $log = [];

    /**
     * Import every image and video in a folder.
     *
     * @return array<string, Media> original file name => media row
     */
    public function importFolder(string $folder): array
    {
        if (! is_dir($folder)) {
            throw new RuntimeException("Folder not found: {$folder}");
        }

        $imported = [];

        foreach (glob($folder.'/*') as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (is_file($file) && in_array($ext, [...self::IMAGE, ...self::VIDEO], true)) {
                $imported[basename($file)] = $this->importFile($file, basename($file));
            }
        }

        return $imported;
    }

    /** Store an upload from the admin (section editor, media library). */
    public function importUpload(UploadedFile $file): Media
    {
        return $this->importFile($file->getRealPath(), $file->getClientOriginalName());
    }

    public function importFile(string $file, string $originalName): Media
    {
        $checksum = sha1_file($file);

        if ($existing = Media::where('checksum', $checksum)->first()) {
            $this->log[] = "  = {$originalName} (already in the library as {$existing->path})";

            return $existing;
        }

        $ext  = SafeUpload::extension($originalName, [...SafeUpload::IMAGES, ...SafeUpload::VIDEOS], 'uploads');
        $base = $this->baseName($originalName);
        $dir  = public_path('uploads/library');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (in_array($ext, self::VIDEO, true)) {
            $name = $this->unique($dir, $base, $ext);
            copy($file, $dir.'/'.$name);
            $media = $this->record($originalName, 'library/'.$name, 'video/'.$ext, 'video', $checksum);
        } else {
            [$name, $width, $height] = $this->storeImage($file, $ext, $dir, $base);
            $media = $this->record($originalName, 'library/'.$name, str_ends_with($name, '.webp') ? 'image/webp' : 'image/'.$ext, 'image', $checksum, $width, $height);
        }

        $this->log[] = "  + {$originalName} → {$media->path} ({$media->readable_size})";

        return $media;
    }

    /** "photo_gut health_first 1000 days.png" → "photo-gut-health-first-1000-days"; old brand names follow the current brand. */
    public function baseName(string $originalName): string
    {
        // "learn-big (1).png" is a downloaded copy of "learn-big.png"
        $name = preg_replace('/\s*\(\d+\)$/', '', pathinfo($originalName, PATHINFO_FILENAME));
        $slug = Str::slug($name) ?: 'file';

        return str_replace('enterol', Str::slug(brand()), substr($slug, 0, 80));
    }

    private function record(string $original, string $path, string $mime, string $kind, string $checksum, ?int $w = null, ?int $h = null): Media
    {
        return Media::create([
            'title'         => Str::of(pathinfo($original, PATHINFO_FILENAME))->replace(['_', '-'], ' ')->squish()->ucfirst(),
            'path'          => $path,
            'original_name' => $original,
            'mime'          => $mime,
            'size'          => filesize(public_path('uploads/'.$path)),
            'kind'          => $kind,
            'checksum'      => $checksum,
            'width'         => $w,
            'height'        => $h,
        ]);
    }

    private function unique(string $dir, string $base, string $ext): string
    {
        $name = $base.'.'.$ext;
        for ($i = 2; is_file($dir.'/'.$name); $i++) {
            $name = $base.'-'.$i.'.'.$ext;
        }

        return $name;
    }

    /** @return array{0: string, 1: int|null, 2: int|null} */
    private function storeImage(string $file, string $ext, string $dir, string $base): array
    {
        set_error_handler(fn () => true);   // ignore harmless libpng colour-profile warnings
        $src = match ($ext) {
            'png'         => imagecreatefrompng($file),
            'jpg', 'jpeg' => imagecreatefromjpeg($file),
            'webp'        => imagecreatefromwebp($file),
            default       => false,
        };
        restore_error_handler();

        if (! $src || ! function_exists('imagewebp')) {
            $name = $this->unique($dir, $base, $ext);
            copy($file, $dir.'/'.$name);
            if ($ext === 'svg') {
                SafeUpload::cleanSvg($dir.'/'.$name, 'uploads');
            }
            [$w, $h] = @getimagesize($dir.'/'.$name) ?: [null, null];

            return [$name, $w, $h];
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, 1600 / max($w, $h));
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $name = $this->unique($dir, $base, 'webp');
        // Logos have flat colours and sharp edges: keep them lossless.
        $quality = str_contains($base, 'logo') && defined('IMG_WEBP_LOSSLESS') ? IMG_WEBP_LOSSLESS : 84;
        imagewebp($dst, $dir.'/'.$name, $quality);

        return [$name, $nw, $nh];
    }
}

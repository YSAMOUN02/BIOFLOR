<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\SafeUpload;
use Illuminate\Http\Request;

/**
 * Upload library for artwork and video. Files land in public/uploads/library
 * so they are served directly, with no symlink step.
 */
class MediaController extends Controller
{
    public function index(Request $request)
    {
        $kind = $request->query('kind');

        return view('admin.media.index', [
            'items' => Media::when($kind, fn ($q) => $q->where('kind', $kind))
                ->latest()->paginate(24)->withQueryString(),
            'kind'  => $kind,
            'limit' => $this->uploadLimitMb(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'  => ['required', 'file', 'mimes:jpg,jpeg,png,webp,avif,svg,mp4,webm,ogg', 'max:'.($this->uploadLimitMb() * 1024)],
            'title' => ['nullable', 'string', 'max:190'],
        ], [
            'file.max'   => 'That file is larger than this server accepts ('.$this->uploadLimitMb().' MB).',
            'file.mimes' => 'Allowed types: JPG, PNG, WebP, AVIF, SVG, MP4, WebM, OGG.',
        ]);

        $file = $request->file('file');
        $size = $file->getSize();
        $mime = $file->getMimeType();
        $name = SafeUpload::move($file, public_path('uploads/library'), [...SafeUpload::IMAGES, ...SafeUpload::VIDEOS]);
        $kind = in_array(pathinfo($name, PATHINFO_EXTENSION), SafeUpload::VIDEOS, true) ? 'video' : 'image';

        Media::create([
            'title'         => $request->input('title') ?: pathinfo($name, PATHINFO_FILENAME),
            'path'          => 'library/'.$name,
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $mime,
            'size'          => $size,
            'kind'          => $kind,
        ]);

        return back()->with('status', ucfirst($kind).' uploaded. Copy its URL to use it in a section.');
    }

    public function destroy(Media $medium)
    {
        $medium->delete();   // the model removes the file from disk too

        return back()->with('status', 'File deleted.');
    }

    /** Smaller of PHP's two upload limits, in MB. */
    private function uploadLimitMb(): int
    {
        $toMb = function (string $value): int {
            $value = trim($value);
            $unit  = strtolower(substr($value, -1));
            $n     = (int) $value;

            return match ($unit) {
                'g'     => $n * 1024,
                'm'     => $n,
                'k'     => max(1, (int) ($n / 1024)),
                default => max(1, (int) ($n / 1048576)),
            };
        };

        return max(1, min($toMb(ini_get('upload_max_filesize')), $toMb(ini_get('post_max_size'))));
    }
}

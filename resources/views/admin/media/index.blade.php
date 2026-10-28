@extends('admin.layout')

@section('title', 'Media library')

@section('content')
    <div class="card">
        <div class="card__head">
            <h2>Upload</h2>
            <span class="card__spacer"></span>
            <span class="pill pill--off">Max {{ $limit }} MB per file</span>
        </div>

        <div class="card__body">
            <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
                  style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap">
                @csrf

                <div class="field" style="flex:1 1 260px">
                    <label for="file">File</label>
                    <input type="file" id="file" name="file"
                           accept="image/jpeg,image/png,image/webp,image/avif,image/svg+xml,video/mp4,video/webm,video/ogg" required>
                    <p class="help">Images: JPG, PNG, WebP, AVIF, SVG. Video: MP4, WebM, OGG.</p>
                    @error('file') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="field" style="flex:1 1 220px">
                    <label for="title">Title (optional)</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}">
                </div>

                <div class="field">
                    <button class="btn btn--primary" type="submit">Upload</button>
                </div>
            </form>

            <p class="help" style="margin-top:1rem">
                To use a video on the home page: upload the MP4 here, copy its URL, then open
                <a href="{{ route('admin.sections.index') }}">Home sections</a> &rarr; the
                <strong>Video spot</strong> section and paste it into <code>video_url</code>.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card__head">
            <h2>Library</h2>
            <span class="pill pill--off">{{ $items->total() }}</span>
            <span class="card__spacer"></span>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.media.index') }}"
               @if (! $kind) style="border-color:var(--primary)" @endif>All</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.media.index', ['kind' => 'image']) }}"
               @if ($kind === 'image') style="border-color:var(--primary)" @endif>Images</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.media.index', ['kind' => 'video']) }}"
               @if ($kind === 'video') style="border-color:var(--primary)" @endif>Video</a>
        </div>

        @if ($items->isEmpty())
            <p class="empty">Nothing uploaded yet.</p>
        @else
            <div class="card__body">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem">
                    @foreach ($items as $item)
                        <div style="border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff">
                            <div style="height:130px;background:#f4f6fa;display:grid;place-items:center;overflow:hidden">
                                @if ($item->is_video)
                                    <video src="{{ $item->url }}" style="max-height:130px;max-width:100%" muted preload="metadata"></video>
                                @else
                                    <img src="{{ $item->url }}" alt="{{ $item->title }}" style="max-height:130px;max-width:100%;object-fit:contain">
                                @endif
                            </div>

                            <div style="padding:.75rem">
                                <strong style="font-size:.875rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $item->title }}</strong>
                                <span style="font-size:.75rem;color:var(--muted)">{{ strtoupper($item->kind) }} &middot; {{ $item->readable_size }}</span>

                                <input type="text" readonly value="{{ $item->url }}"
                                       data-copy
                                       title="Click to copy"
                                       style="width:100%;margin-top:.5rem;font-size:.75rem;padding:.35rem .5rem;border:1px solid var(--line);border-radius:6px">

                                <form method="post" action="{{ route('admin.media.destroy', $item) }}"
                                      style="margin-top:.5rem" data-confirm="Delete this file? Anything referencing it will break.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn--danger btn--sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{ $items->links('admin.pagination') }}
        @endif
    </div>
@endsection

@extends('admin.layout')

@section('title', 'Page sections')

@section('content')
    <div class="card">
        <div class="card__head">
            <h2>Page composition</h2>
            <form method="get" class="search">
                <select name="page_key" data-autosubmit aria-label="Page">
                    @foreach ($pageKeys as $key)
                        <option value="{{ $key }}" {{ $key === $pageKey ? 'selected' : '' }}>{{ $pages[$key] ?? $key }}</option>
                    @endforeach
                </select>
            </form>
            <span class="card__spacer"></span>

            @php
                $preview = match ($pageKey) {
                    'home' => route('home'), 'our-products' => route('products'), 'faq' => route('faq'),
                    'pdp' => optional(\App\Models\Product::active()->first())->url ?? route('products'),
                    default => route('page', ['slug' => $pageKey]),
                };
            @endphp
            <a class="btn btn--ghost btn--sm" href="{{ $preview }}" target="_blank" rel="noopener">Preview &rarr;</a>
            <form method="get" action="{{ route('admin.sections.create') }}" class="search">
                <input type="hidden" name="page_key" value="{{ $pageKey }}">
                <select name="type" aria-label="Section type">
                    @foreach (\App\Http\Controllers\Admin\SectionController::TYPES as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn--primary btn--sm" type="submit">Add section</button>
            </form>
        </div>

        @if ($sections->isEmpty())
            <p class="empty">This page has no sections yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th style="width:90px">Order</th><th>Type</th><th>Heading</th><th>Visible</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $section)
                            <tr>
                                <td style="white-space:nowrap">
                                    <form method="post" action="{{ route('admin.sections.move', $section) }}" style="display:inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="Move up"
                                                {{ $loop->first ? 'disabled' : '' }}>&uarr;</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.sections.move', $section) }}" style="display:inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="Move down"
                                                {{ $loop->last ? 'disabled' : '' }}>&darr;</button>
                                    </form>
                                </td>

                                <td>
                                    <strong>{{ \App\Http\Controllers\Admin\SectionController::TYPES[$section->type] ?? $section->type }}</strong>
                                    <br><span style="color:var(--muted);font-size:.8125rem">{{ $section->type }}</span>
                                </td>

                                <td class="cell-truncate">{!! strip_tags((string) $section->get('title', '—')) !!}</td>

                                <td>
                                    <form method="post" action="{{ route('admin.sections.toggle', $section) }}">
                                        @csrf
                                        <button class="pill {{ $section->is_visible ? 'pill--on' : 'pill--off' }}"
                                                type="submit" style="border:0;cursor:pointer">
                                            {{ $section->is_visible ? 'Visible' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>

                                <td class="actions">
                                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.sections.edit', $section) }}">Edit</a>
                                    <form method="post" action="{{ route('admin.sections.destroy', $section) }}"
                                          style="display:inline" data-confirm="Delete this section?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn--danger btn--sm" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

{{-- One schema-driven form field. Expects $name, $field, $record. --}}
@php
    $type     = $field['type'] ?? 'text';
    $isEdit   = $record->exists;
    $readonly = $isEdit && ($field['readonly_on_edit'] ?? false);
    $raw      = array_key_exists('value', $field) ? $field['value'] : ($record->{$name} ?? null);

    if ($type === 'datetime' && $raw instanceof \DateTimeInterface) {
        $raw = $raw->format('Y-m-d\TH:i');
    }
    if ($type === 'json' && is_array($raw)) {
        $raw = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    if ($type === 'keyvalue' && is_array($raw)) {
        $raw = collect($raw)->map(fn ($label, $key) => $key.' = '.$label)->implode("\n");
    }

    $value = old($name, $raw);
@endphp

<div class="field {{ $type === 'checkbox' ? 'field--check' : '' }}">
    @if ($type === 'checkbox')
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" id="f-{{ $name }}" name="{{ $name }}" value="1" {{ $value ? 'checked' : '' }}>
        <label for="f-{{ $name }}">{{ $field['label'] }}</label>

    @elseif ($type === 'multicheck')
        <span class="field__legend">{{ $field['label'] }}</span>
        <input type="hidden" name="{{ $name }}[]" value="">
        <div class="checks">
            @foreach ($field['options'] as $optValue => $optLabel)
                <label class="checks__item">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $optValue }}"
                           {{ in_array((string) $optValue, array_map('strval', (array) $value), true) ? 'checked' : '' }}>
                    <span>{!! $optLabel !!}</span>
                </label>
            @endforeach
        </div>

    @else
        <label for="f-{{ $name }}">{{ $field['label'] }}</label>

        @if (in_array($type, ['textarea', 'richtext', 'json', 'keyvalue'], true))
            <textarea id="f-{{ $name }}" name="{{ $name }}"
                      rows="{{ $field['rows'] ?? ($type === 'json' ? 18 : 5) }}"
                      class="{{ in_array($type, ['richtext', 'json', 'keyvalue'], true) ? 'code' : '' }}">{{ $value }}</textarea>

        @elseif ($type === 'select')
            <select id="f-{{ $name }}" name="{{ $name }}">
                @foreach ($field['options'] as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ (string) $value === (string) $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                @endforeach
            </select>

        @elseif ($type === 'image' || $type === 'file')
            @php $current = $record->{$name}; @endphp
            @if ($current)
                <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.5rem">
                    @if ($type === 'image')
                        <img src="{{ media_url($current) }}" alt=""
                             style="height:90px;width:auto;border:1px solid var(--line);border-radius:8px;background:#fff">
                    @else
                        <code style="font-size:.8125rem">{{ $current }}</code>
                    @endif
                    <label style="font-weight:600;font-size:.8125rem;display:flex;gap:.4rem;align-items:center">
                        <input type="checkbox" name="{{ $name }}_remove" value="1" style="width:16px;height:16px">
                        Remove
                    </label>
                </div>
            @endif
            <input type="file" id="f-{{ $name }}" name="{{ $name }}" accept="{{ $field['accept'] ?? ($type === 'image' ? 'image/*' : '*/*') }}">

        @elseif ($type === 'media')
            {{-- Path picker over the media library; type to filter. --}}
            <div style="display:flex;gap:1rem;align-items:center">
                @if ($value)
                    <img src="{{ media_url($value) }}" alt="" style="width:96px;height:72px;object-fit:cover;border:1px solid var(--line);border-radius:8px">
                @endif
                <input type="text" id="f-{{ $name }}" name="{{ $name }}" value="{{ $value }}" list="dl-{{ $name }}" placeholder="library/… or img/…" style="flex:1">
            </div>
            <datalist id="dl-{{ $name }}">
                @foreach (\App\Models\Media::where('kind', $field['kind'] ?? 'image')->latest()->get(['title', 'path']) as $media)
                    <option value="{{ $media->path }}">{{ $media->title }}</option>
                @endforeach
            </datalist>

        @elseif ($type === 'color')
            <input type="color" id="f-{{ $name }}" name="{{ $name }}" value="{{ $value ?: '#001660' }}">

        @elseif ($type === 'datetime')
            <input type="datetime-local" id="f-{{ $name }}" name="{{ $name }}" value="{{ $value }}">

        @elseif (in_array($type, ['number', 'url', 'email', 'date'], true))
            <input type="{{ $type }}" id="f-{{ $name }}" name="{{ $name }}" value="{{ $value }}"
                   @if ($type === 'number') step="any" @endif {{ $readonly ? 'readonly' : '' }}>

        @else
            <input type="text" id="f-{{ $name }}" name="{{ $name }}" value="{{ $value }}" {{ $readonly ? 'readonly' : '' }}>
        @endif
    @endif

    @if (! empty($field['help']))
        <p class="help">{{ $field['help'] }}</p>
    @endif

    @error($name) <p class="error">{{ $message }}</p> @enderror
</div>

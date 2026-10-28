{{-- Input for one attribute value. Expects $attribute (ProductAttribute) and $stored (string|null). --}}
@php
    $name  = 'attributes['.$attribute->code.']';
    $id    = 'attr-'.$attribute->code;
    $value = old('attributes.'.$attribute->code, $stored);
    $type  = $attribute->type;
    $label = $attribute->name.($attribute->unit ? ' ('.$attribute->unit.')' : '');
@endphp

<div class="field {{ $type === 'boolean' ? 'field--check' : '' }}">
    @if ($type === 'boolean')
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked(in_array((string) $value, ['1', 'true', 'yes'], true))>
        <label for="{{ $id }}">{{ $label }}</label>

    @elseif ($type === 'multiselect')
        @php $chosen = is_array($value) ? $value : (json_decode((string) $value, true) ?: []); @endphp
        <span class="field__legend">{{ $label }}</span>
        <input type="hidden" name="{{ $name }}[]" value="">
        <div class="checks">
            @foreach ($attribute->options ?? [] as $optValue => $optLabel)
                <label class="checks__item"><input type="checkbox" name="{{ $name }}[]" value="{{ $optValue }}" @checked(in_array((string) $optValue, array_map('strval', $chosen), true))> {{ $optLabel }}</label>
            @endforeach
        </div>

    @else
        <label for="{{ $id }}">{{ $label }} <code class="attr-code">{{ $attribute->code }}</code></label>

        @if ($type === 'select')
            <select id="{{ $id }}" name="{{ $name }}">
                <option value="">—</option>
                @foreach ($attribute->options ?? [] as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
                @endforeach
            </select>
        @elseif (in_array($type, ['richtext', 'textarea', 'list'], true))
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $type === 'richtext' ? 5 : 3 }}" class="{{ $type === 'richtext' ? 'code' : '' }}">{{ $value }}</textarea>
        @elseif ($type === 'color')
            <input type="color" id="{{ $id }}" name="{{ $name }}" value="{{ $value ?: '#001660' }}">
        @elseif (in_array($type, ['number', 'url', 'date'], true))
            <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" @if ($type === 'number') step="any" @endif>
        @else
            <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endif

    @if ($attribute->help || $type === 'list')
        <p class="help">{{ $attribute->help ?: 'One item per line.' }}</p>
    @endif
    @if (! empty($fallback))
        <p class="help">English: {{ \Illuminate\Support\Str::limit(plain_text($fallback), 160) }}</p>
    @endif
</div>

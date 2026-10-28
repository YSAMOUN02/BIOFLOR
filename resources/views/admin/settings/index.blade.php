@extends('admin.layout')

@section('title', 'Settings')

@section('content')
    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="stack">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card__head">
                <h2>Language</h2>
                <div class="segmented" aria-label="Language">
                    @foreach ($locales as $locale)
                        <a class="segmented__btn" aria-selected="{{ ($lang?->id ?? ($locale->is_default ? $locale->id : null)) === $locale->id ? 'true' : 'false' }}"
                           href="{{ route('admin.settings.index') }}{{ $locale->is_default ? '' : '?lang='.$locale->url_prefix }}">{{ $locale->label }}</a>
                    @endforeach
                </div>
                <span class="card__spacer"></span>
                <p class="help">Text may use <code>{brand}</code>, replaced by the brand name everywhere on the site.</p>
            </div>
            @if ($lang)
                <div class="card__body">
                    <input type="hidden" name="lang" value="{{ $lang->url_prefix }}">
                    <p class="alert alert--info" style="margin:0">
                        Editing the <strong>{{ $lang->label }}</strong> text. Empty fields show the English value. Logos, links and switches are the same in every language.
                    </p>
                </div>
            @endif
        </div>

        @foreach ($groupNames as $group => $groupName)
            @continue(! isset($groups[$group]))
            <div class="card" id="group-{{ $group }}">
                <div class="card__head"><h2>{{ $groupName }}</h2></div>
                <div class="card__body form-grid" @if ($lang) lang="{{ $lang->url_prefix }}" @endif>
                    @foreach ($groups[$group] as $setting)
                        @php $field = str_replace('.', '__', $setting->key); @endphp

                        @if ($lang)
                            <div class="field">
                                <label for="s-{{ $field }}">{{ $setting->label }}</label>
                                @if (in_array($setting->type, ['textarea', 'richtext'], true))
                                    <textarea id="s-{{ $field }}" name="settings[{{ $field }}]" rows="3">{{ old('settings.'.$field, $setting->translated_value) }}</textarea>
                                @else
                                    <input type="text" id="s-{{ $field }}" name="settings[{{ $field }}]" value="{{ old('settings.'.$field, $setting->translated_value) }}">
                                @endif
                                @if (filled($setting->value))<p class="help">English: {{ \Illuminate\Support\Str::limit($setting->value, 200) }}</p>@endif
                            </div>
                            @continue
                        @endif

                        <div class="field {{ $setting->type === 'boolean' ? 'field--check' : '' }}">
                            @if ($setting->type === 'boolean')
                                <input type="hidden" name="settings[{{ $field }}]" value="0">
                                <input type="checkbox" id="s-{{ $field }}" name="settings[{{ $field }}]" value="1" @checked($setting->value === '1')>
                                <label for="s-{{ $field }}">{{ $setting->label }}</label>
                            @elseif ($setting->type === 'image')
                                <label for="s-{{ $field }}">{{ $setting->label }}</label>
                                @if ($setting->value)
                                    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.5rem">
                                        <img src="{{ media_url($setting->value) }}" alt="" style="height:56px;width:auto;padding:6px;border:1px solid var(--line);border-radius:8px;background:#9aa4b8">
                                        <label style="font-weight:600;font-size:.8125rem;display:flex;gap:.4rem;align-items:center">
                                            <input type="checkbox" name="remove[{{ $field }}]" value="1" style="width:16px;height:16px"> Remove
                                        </label>
                                    </div>
                                @endif
                                <input type="file" id="s-{{ $field }}" name="uploads[{{ $field }}]" accept="image/*">
                            @else
                                <label for="s-{{ $field }}">{{ $setting->label }}</label>
                                @if (in_array($setting->type, ['textarea', 'richtext'], true))
                                    <textarea id="s-{{ $field }}" name="settings[{{ $field }}]" rows="3">{{ old('settings.'.$field, $setting->value) }}</textarea>
                                @else
                                    <input type="{{ in_array($setting->type, ['url', 'email', 'number'], true) && $setting->value !== '#' ? $setting->type : 'text' }}"
                                           id="s-{{ $field }}" name="settings[{{ $field }}]" value="{{ old('settings.'.$field, $setting->value) }}">
                                @endif
                            @endif
                            @if ($setting->help)<p class="help">{{ $setting->help }}</p>@endif
                            <p class="help"><code>{{ $setting->key }}</code></p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="form-actions">
            <button class="btn btn--primary" type="submit">{{ $lang ? 'Save '.$lang->label.' text' : 'Save settings' }}</button>
            <a class="btn btn--ghost" href="{{ $lang ? url('/'.$lang->url_prefix.'/home') : route('home') }}" target="_blank" rel="noopener">View site &rarr;</a>
        </div>
    </form>
@endsection

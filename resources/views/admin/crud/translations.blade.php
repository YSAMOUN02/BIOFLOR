{{-- Per-language values for the record's translatable fields. Empty = the default-language value is used. --}}
@if (! empty($translationFields) && $translationLocales->isNotEmpty())
    <div class="card">
        <div class="card__head">
            <h2>Translations</h2>
            <span class="help">Leave a field empty to show the English text in that language.</span>
        </div>
        <div class="card__body stack">
            @foreach ($translationLocales as $locale)
                <details class="translation-panel" @if ($loop->first) open @endif>
                    <summary>{{ $locale->label }} <code>/{{ $locale->url_prefix }}</code></summary>
                    <div class="form-grid" lang="{{ $locale->url_prefix }}">
                        @foreach ($translationFields as $name => $field)
                            @php
                                $type   = $field['type'] ?? 'text';
                                $stored = $record->exists ? $record->translationFor($locale->url_prefix, $name) : null;
                                if ($type === 'keyvalue' && $stored) {
                                    $stored = collect(json_decode($stored, true) ?: [])->map(fn ($l, $k) => $k.' = '.$l)->implode("\n");
                                }
                                $value  = old('translations.'.$locale->url_prefix.'.'.$name, $stored);
                                $source = $record->exists ? $record->getRawOriginal($name) : '';
                                $id     = 't-'.$locale->url_prefix.'-'.$name;
                            @endphp
                            <div class="field">
                                <label for="{{ $id }}">{{ $field['label'] }} <span class="pill pill--off">{{ $locale->url_prefix }}</span></label>
                                @if (in_array($type, ['textarea', 'richtext', 'keyvalue'], true))
                                    <textarea id="{{ $id }}" name="translations[{{ $locale->url_prefix }}][{{ $name }}]" rows="{{ min($field['rows'] ?? 4, 10) }}"
                                              class="{{ $type === 'textarea' ? '' : 'code' }}">{{ $value }}</textarea>
                                @else
                                    <input type="text" id="{{ $id }}" name="translations[{{ $locale->url_prefix }}][{{ $name }}]" value="{{ $value }}">
                                @endif
                                @if (is_string($source) && $source !== '')
                                    <p class="help">English: {{ \Illuminate\Support\Str::limit(plain_text($source), 140) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>
    </div>
@endif

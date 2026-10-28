{{-- One item of a repeatable list (slide, card, figure…). --}}
<fieldset class="repeater__item js-repeater-item">
    <div class="repeater__bar">
        <span class="repeater__title">{{ $def['item'] ?? 'Item' }} <span class="js-repeater-number">{{ is_int($index) ? $index + 1 : '' }}</span></span>
        <span class="card__spacer"></span>
        <button class="btn btn--ghost btn--sm js-repeater-up" type="button" aria-label="Move up">&uarr;</button>
        <button class="btn btn--ghost btn--sm js-repeater-down" type="button" aria-label="Move down">&darr;</button>
        <button class="btn btn--danger btn--sm js-repeater-remove" type="button">Remove</button>
    </div>
    <div class="form-grid">
        @include('admin.sections.fields', [
            'schema'       => $def['fields'],
            'prefix'       => $name.'['.$index.']',
            'uploadPrefix' => $upload.'['.$index.']',
            'values'       => $item,
        ])
    </div>
</fieldset>

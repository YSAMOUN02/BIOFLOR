{{-- "See all references" toggle. $body is HTML; $id must be unique on the page. --}}
{{-- Stand-in text ("add the approved reference…") is not shown to visitors. --}}
@if (filled($body ?? null) && ! is_placeholder_text($body))
    <section class="references">
        <div class="container references__head">
            <button class="references__toggle js-collapse" type="button" aria-expanded="false" aria-controls="{{ $id }}">
                <span class="references__label h3" data-when="closed">{{ $show ?? __('See all references') }}</span>
                <span class="references__label h3" data-when="open">{{ $hide ?? __('Hide references') }}</span>
                <span class="references__arrow" aria-hidden="true">@include('partials.icon', ['name' => 'chevron-down', 'size' => 18, 'stroke' => 2])</span>
            </button>
        </div>
        <div class="references__panel" id="{{ $id }}" hidden>
            <div class="container references__body">{!! $body !!}</div>
        </div>
    </section>
@endif

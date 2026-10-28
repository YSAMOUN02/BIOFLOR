{{-- Q&A from the faqs table (admin → Q&A). Optional payload: group, numbered. --}}
@php
    $faqs = \App\Models\Faq::orderBy('sort_order')
        ->when($section->get('group'), fn ($q, $group) => $q->where('group', $group))
        ->get();
@endphp
@if ($faqs->isNotEmpty())
    <div class="faq-list {{ $section->get('numbered', true) ? 'faq-list--numbered' : '' }}">
        @foreach ($faqs as $i => $faq)
            @php $panel = $section->uid('answer-'.$faq->id); @endphp
            <div class="faq-list__item">
                <h2 class="faq-list__heading">
                    <button class="faq-list__btn js-collapse" type="button" aria-expanded="false" aria-controls="{{ $panel }}">
                        <span class="faq-list__inner">
                            @if ($section->get('numbered', true))<span class="faq-list__number" aria-hidden="true">{{ $i + 1 }}.</span>@endif
                            <span class="faq-list__question">{{ str_replace('{brand}', brand(), $faq->question) }}</span>
                            @include('partials.icon', ['name' => 'chevron-down', 'size' => 16, 'class' => 'faq-list__chevron'])
                        </span>
                    </button>
                </h2>
                <div class="faq-list__panel" id="{{ $panel }}" hidden>
                    <div class="faq-list__answer rich-text">{!! str_replace('{brand}', brand(), $faq->answer) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif

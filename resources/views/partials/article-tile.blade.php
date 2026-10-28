{{-- Article tile: image with topic tag, title, "Learn more" and date. --}}
@php $titleId = 'article-'.$article->id.'-'.($uid ?? 'tile'); @endphp
<div class="article-tile {{ $variant ?? '' }}">
    <div class="article-tile__top">
        <div class="article-tile__image">
            <img src="{{ $article->image_url }}"{{ img_size($article->image_url) }} alt="" loading="lazy">
            @if ($article->eyebrow)
                <span class="article-tile__tag">{{ __($article->eyebrow) }}</span>
            @endif
        </div>
        <h3 class="article-tile__title" id="{{ $titleId }}">{!! $article->title !!}</h3>
    </div>
    <div class="article-tile__footer">
        <a class="btn btn--outline" href="{{ $article->url }}" aria-describedby="{{ $titleId }}">{{ __('Learn more') }}</a>
        @if ($article->published_at)
            <span class="article-tile__date">{{ $article->published_at->locale(app()->getLocale())->isoFormat('LL') }}</span>
        @endif
    </div>
</div>

@php
    $currentLocale = app('storefront.locale');
    $activeLocale  = $globalLocales->firstWhere('url_prefix', $currentLocale);
    $otherLocales  = $globalLocales->where('url_prefix', '!=', $currentLocale);
    // Same page in the other locale: swap the first path segment.
    $switchUrl     = fn ($prefix) => url(preg_replace('#^/?[a-z]{2}(?=/|$)#', $prefix, request()->path()) ?: $prefix.'/home');
    $whereToBuy    = setting('header.where_to_buy', __('Where to buy'));
    $isHome        = request()->routeIs('home');
@endphp

<header class="site-header js-header" id="site-header">
    <div class="site-header__top">
        <div class="container site-header__row">
            <div class="site-header__start">
                <button class="icon-btn site-header__menu js-drawer-open" type="button"
                        aria-controls="site-drawer" aria-expanded="false" aria-label="{{ __('Open the menu') }}">
                    @include('partials.icon', ['name' => 'menu', 'size' => 24])
                </button>

                @if ($globalLocales->count() > 1)
                    <div class="locale js-locale">
                        <button class="locale__toggle" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="locale-menu">
                            @include('partials.icon', ['name' => 'globe', 'size' => 18])
                            <span>{{ $activeLocale->label ?? 'English' }}</span>
                            @include('partials.icon', ['name' => 'chevron-down', 'size' => 14, 'stroke' => 2.2, 'class' => 'locale__caret'])
                        </button>
                        <div class="locale__menu" id="locale-menu" hidden>
                            @foreach ($otherLocales as $locale)
                                <a class="locale__item" href="{{ $switchUrl($locale->url_prefix) }}"
                                   hreflang="{{ str_replace('_', '-', $locale->code) }}" lang="{{ str_replace('_', '-', $locale->code) }}">
                                    {{ $locale->label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <a class="site-header__logo" href="{{ route('home') }}" aria-label="{{ brand() }} — {{ __('home page') }}">
                @include('partials.logo', ['variant' => 'dark'])
            </a>

            <div class="site-header__end">
                <a class="btn btn--navy btn--sm site-header__cta" href="{{ route('where-to-buy') }}">
                    @include('partials.icon', ['name' => 'location', 'size' => 18])
                    <span>{{ $whereToBuy }}</span>
                </a>
                <button class="icon-btn site-header__search-mobile js-search-toggle" type="button"
                        aria-controls="search-panel" aria-expanded="false" aria-label="{{ __('Open search') }}">
                    @include('partials.icon', ['name' => 'search', 'size' => 24])
                </button>
            </div>
        </div>
    </div>

    <div class="site-header__nav">
        @if ($isHome)
            <h1 class="visually-hidden">{{ str_replace('{brand}', brand(), (string) setting('site.title', '{brand} | Official site')) }}</h1>
        @endif

        <div class="container site-header__nav-row">
            <nav class="site-nav" id="main-navigation" aria-label="{{ __('Main navigation') }}">
                <ul class="site-nav__list">
                    @foreach ($globalNav as $item)
                        @php $hasChildren = $item->children->isNotEmpty(); @endphp
                        <li class="site-nav__item {{ $hasChildren ? 'has-dropdown js-dropdown' : '' }}">
                            <a class="site-nav__link" href="{{ site_url($item->url) }}"
                               @if ($hasChildren) aria-haspopup="true" aria-expanded="false" @endif
                               @if (url()->current() === site_url($item->url)) aria-current="page" @endif>
                                {{ $item->label }}
                            </a>

                            @if ($hasChildren)
                                <div class="site-nav__dropdown">
                                    <ul>
                                        @foreach ($item->children as $child)
                                            <li><a class="site-nav__dropdown-link" href="{{ site_url($child->url) }}">{!! $child->label !!}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>

            <button class="icon-btn site-header__search js-search-toggle" type="button"
                    aria-controls="search-panel" aria-expanded="false" aria-label="{{ __('Open search') }}">
                @include('partials.icon', ['name' => 'search', 'size' => 20])
            </button>
        </div>
    </div>

    <div class="search-panel js-search-panel" id="search-panel" hidden>
        <div class="container">
            <form class="search-form" role="search" action="{{ route('search') }}" method="get">
                <label class="visually-hidden" for="site-search-field">{{ __('Search') }}</label>
                <input class="search-form__input js-search-field" type="search" id="site-search-field" name="q"
                       value="{{ request()->routeIs('search') && is_string(request('q')) ? request('q') : '' }}"
                       placeholder="{{ setting('header.search_placeholder', __('Search (keywords, etc.)')) }}"
                       autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list"
                       aria-controls="search-suggestions" data-suggest-url="{{ route('search.suggest') }}">
                <button class="icon-btn search-form__submit" type="submit" aria-label="{{ __('Submit search') }}">
                    @include('partials.icon', ['name' => 'search', 'size' => 22])
                </button>
                <button class="icon-btn search-form__close js-search-close" type="button" aria-label="{{ __('Close search') }}">
                    @include('partials.icon', ['name' => 'close', 'size' => 22])
                </button>
                <div class="search-suggestions js-suggestions" id="search-suggestions" role="listbox" hidden></div>
            </form>
        </div>
    </div>
</header>

{{-- Mobile menu --}}
<div class="drawer js-drawer" id="site-drawer" role="dialog" aria-modal="true" aria-label="{{ __('Menu') }}" hidden>
    <div class="drawer__head">
        <button class="drawer__back js-drawer-back" type="button" hidden>
            @include('partials.icon', ['name' => 'chevron-left', 'size' => 18])
            <span>{{ __('Back') }}</span>
        </button>
        <button class="icon-btn drawer__close js-drawer-close" type="button" aria-label="{{ __('Close the menu') }}">
            @include('partials.icon', ['name' => 'close', 'size' => 24])
        </button>
    </div>

    <div class="drawer__panel is-active" data-panel="root">
        @if ($otherLocales->isNotEmpty())
            <details class="drawer__locale">
                <summary>
                    @include('partials.icon', ['name' => 'globe', 'size' => 18])
                    <span>{{ $activeLocale->label ?? 'English' }}</span>
                </summary>
                @foreach ($otherLocales as $locale)
                    <a href="{{ $switchUrl($locale->url_prefix) }}" lang="{{ str_replace('_', '-', $locale->code) }}">{{ $locale->label }}</a>
                @endforeach
            </details>
        @endif

        <div class="drawer__cta">
            <a class="btn btn--navy btn--sm" href="{{ route('where-to-buy') }}">
                @include('partials.icon', ['name' => 'location', 'size' => 18])
                <span>{{ $whereToBuy }}</span>
            </a>
        </div>

        <ul class="drawer__menu">
            @foreach ($globalNav as $item)
                <li>
                    @if ($item->children->isNotEmpty())
                        <button class="drawer__link js-drawer-panel" type="button" data-target="nav-{{ $item->id }}">
                            <span>{{ $item->label }}</span>
                            @include('partials.icon', ['name' => 'chevron-right', 'size' => 18])
                        </button>
                    @else
                        <a class="drawer__link" href="{{ site_url($item->url) }}">{{ $item->label }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    @foreach ($globalNav->filter(fn ($item) => $item->children->isNotEmpty()) as $item)
        <div class="drawer__panel" data-panel="nav-{{ $item->id }}" hidden>
            <div class="drawer__intro">
                <span class="card-title">{{ $item->label }}</span>
                @if ($item->description)
                    <p>{{ str_replace('{brand}', brand(), $item->description) }}</p>
                @endif
                <a class="btn btn--pink" href="{{ site_url($item->url) }}">{{ $item->cta_label ?: __('See all') }}</a>
            </div>
            <ul class="drawer__menu">
                @foreach ($item->children as $child)
                    <li><a class="drawer__link" href="{{ site_url($child->url) }}">{!! $child->label !!}</a></li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
<div class="drawer-backdrop js-drawer-backdrop" hidden></div>

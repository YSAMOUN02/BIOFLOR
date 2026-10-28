@php
    $social = collect([
        'facebook'  => ['Facebook', setting('social.facebook')],
        'tiktok'    => ['TikTok', setting('social.tiktok')],
        'instagram' => ['Instagram', setting('social.instagram')],
        'youtube'   => ['YouTube', setting('social.youtube')],
    ])->filter(fn ($row) => filled($row[1]));

    $columns = [
        __('Gut health')     => ($footerArticles['gut-health'] ?? collect())->take(8),
        __('Our uniqueness') => ($footerArticles['our-uniqueness'] ?? collect())->take(6),
    ];
@endphp

<footer class="site-footer" id="footer-content">
    <div class="container">
        <div class="site-footer__top site-footer__divider">
            <a class="site-footer__logo" href="{{ route('home') }}" aria-label="{{ __('Go to the home page') }}">
                @include('partials.logo', ['variant' => 'light'])
            </a>

            @if ($social->isNotEmpty())
                <nav class="site-footer__social" aria-label="{{ setting('social.title', __('Stay tuned')) }}">
                    <span class="site-footer__social-title" aria-hidden="true">{{ setting('social.title', __('Stay tuned')) }}</span>
                    <ul>
                        @foreach ($social as $icon => [$label, $url])
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ __('Follow us on :network', ['network' => $label]) }}">
                                    @include('partials.icon', ['name' => $icon, 'size' => 32])
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>

        <nav class="site-footer__menu site-footer__divider" aria-label="{{ __('Footer menu') }}">
            <div class="site-footer__col">
                <span class="site-footer__col-title" aria-hidden="true">{{ __('Our products') }}</span>
                <ul aria-label="{{ __('Our products') }}">
                    @foreach ($footerProducts as $product)
                        <li><a href="{{ $product->url }}">{!! $product->name !!}</a></li>
                    @endforeach
                </ul>
            </div>

            @foreach ($columns as $title => $articles)
                <div class="site-footer__col">
                    <span class="site-footer__col-title" aria-hidden="true">{{ $title }}</span>
                    <ul aria-label="{{ $title }}">
                        @foreach ($articles as $article)
                            <li><a href="{{ $article->url }}">{!! $article->title !!}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div class="site-footer__col">
                <ul class="site-footer__main-links">
                    <li><a href="{{ route('page', ['slug' => 'our-news']) }}">{{ __('Our news') }}</a></li>
                    <li><a href="{{ route('page', ['slug' => 'about-us']) }}">{{ __('About us') }}</a></li>
                    <li><a href="{{ route('where-to-buy') }}">{{ setting('header.where_to_buy', __('Where to buy')) }}</a></li>
                    <li><a href="{{ route('contact') }}">{{ __('Contact us') }}</a></li>
                    <li><a href="{{ route('faq') }}">{{ __('Q&A') }}</a></li>
                </ul>
            </div>
        </nav>

        <div class="site-footer__legal site-footer__divider">
            <div class="site-footer__legal-col">
                <p><strong>{{ setting('distributor.name') }}</strong></p>
                <p>{{ setting('distributor.address1') }}</p>
                <p>{{ setting('distributor.address2') }}</p>
            </div>
            <div class="site-footer__legal-col">
                @if (setting('distributor.phone'))
                    <p><strong>{{ __('Tel') }}:</strong> {{ setting('distributor.phone') }}</p>
                @endif
                @if (setting('distributor.email'))
                    <p><strong>{{ __('Email') }}:</strong> <a class="is-underlined" href="mailto:{{ setting('distributor.email') }}">{{ setting('distributor.email') }}</a></p>
                @endif
                @if (setting('distributor.website'))
                    <p><strong>{{ __('Website') }}:</strong> <a class="is-underlined" href="{{ setting('distributor.website') }}" target="_blank" rel="noopener">{{ setting('distributor.website') }}</a></p>
                @endif
            </div>
        </div>

        <div class="site-footer__bar">
            <div class="site-footer__company">
                @if (setting('company.logo'))
                    <img src="{{ media_url(setting('company.logo')) }}" alt="{{ setting('company.name') }}" height="28">
                @else
                    <span class="site-footer__company-name">{{ mb_strtoupper((string) setting('company.name', '')) }}</span>
                @endif
            </div>
        </div>
    </div>
</footer>

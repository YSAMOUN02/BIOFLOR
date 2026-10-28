@extends('layouts.app')

@section('title', setting('header.where_to_buy', __('Where to buy')).' | '.brand())
@section('meta_description', $page ? str_replace('{brand}', brand(), (string) $page->meta_description) : '')

@section('content')
    <div class="container breadcrumb-row">
        @include('partials.breadcrumb', ['items' => [[setting('header.where_to_buy', __('Where to buy')), null]]])
    </div>

    {{-- Two lists: online shops, and local pharmacies (Admin → Where to buy, "Online shop" ticked or not). --}}
    <section class="container stores js-store-filter">
        <div class="stores__head">
            <div>
                <h1 class="h1-sm">{{ setting('header.where_to_buy', __('Where to buy')) }}</h1>
                <p class="text-intro">{{ __(':brand is available online and in local pharmacies.', ['brand' => brand()]) }}</p>
            </div>
            @if ($countries->count() > 1)
                <div class="field stores__filter">
                    <label class="field__label" for="store-country">{{ __('Country') }}</label>
                    <select class="field__input field__select js-store-country" id="store-country">
                        <option value="">{{ __('All countries') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <h2 class="stores__title h3">
            @include('partials.icon', ['name' => 'globe', 'size' => 28]) {{ __('Buy online') }}
        </h2>
        @if ($online->isNotEmpty())
            <ul class="stores__grid">
                @foreach ($online as $retailer)
                    <li class="store-card" data-country="{{ $retailer->country }}">
                        <p class="card-title">{{ $retailer->name }}</p>
                        @if ($retailer->city || $retailer->country)
                            <p class="store-card__meta">{{ collect([$retailer->city, $retailer->country])->filter()->implode(', ') }}</p>
                        @endif
                        @if ($retailer->url)
                            <a class="btn btn--outline" href="{{ $retailer->url }}" target="_blank" rel="noopener">
                                <span>{{ __('Visit the shop') }}</span>@include('partials.icon', ['name' => 'external', 'size' => 16])
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="stores__empty">{{ __('Online shops will be listed here soon.') }}</p>
        @endif

        <h2 class="stores__title h3">
            @include('partials.icon', ['name' => 'location', 'size' => 28]) {{ __('Local pharmacies') }}
        </h2>
        @if ($pharmacy->isNotEmpty())
            <ul class="stores__grid">
                @foreach ($pharmacy as $retailer)
                    @php $place = collect([$retailer->address, $retailer->city, $retailer->country])->filter()->implode(', '); @endphp
                    <li class="store-card" data-country="{{ $retailer->country }}">
                        <p class="card-title">{{ $retailer->name }}</p>
                        @if ($place)
                            <p class="store-card__meta">@include('partials.icon', ['name' => 'location', 'size' => 16]) {{ $place }}</p>
                        @endif
                        @if ($retailer->phone)
                            <p class="store-card__meta">@include('partials.icon', ['name' => 'phone', 'size' => 16]) <a href="tel:{{ preg_replace('/[^+\d]/', '', $retailer->phone) }}">{{ $retailer->phone }}</a></p>
                        @endif
                        <div class="store-card__actions">
                            @if ($place)
                                <a class="btn btn--outline" href="https://www.google.com/maps/search/?api=1&amp;query={{ rawurlencode($retailer->name.', '.$place) }}" target="_blank" rel="noopener">
                                    <span>{{ __('Get directions') }}</span>@include('partials.icon', ['name' => 'external', 'size' => 16])
                                </a>
                            @endif
                            @if ($retailer->url)
                                <a class="btn btn--outline" href="{{ $retailer->url }}" target="_blank" rel="noopener">
                                    <span>{{ __('Website') }}</span>@include('partials.icon', ['name' => 'external', 'size' => 16])
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="stores__empty">{{ __('Ask for :brand at your local pharmacy.', ['brand' => brand()]) }}</p>
        @endif

        <p class="empty-state js-store-empty" hidden>{{ __('No stores listed for this country yet.') }}</p>
    </section>
@endsection

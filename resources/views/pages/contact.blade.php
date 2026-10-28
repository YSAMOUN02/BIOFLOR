@extends('layouts.app')

@section('title', __('Contact us').' | '.brand())
@section('meta_description', $page ? str_replace('{brand}', brand(), (string) $page->meta_description) : '')
@section('hide_newsletter', '1')

@section('content')
    <section class="contact-header bg-navy is-dark">
        <div class="container contact-header__row">
            <span class="contact-header__icon" aria-hidden="true">@include('partials.icon', ['name' => 'mail', 'size' => 40])</span>
            <div>
                <h1 class="h1-sm">{{ __('Contact us') }}</h1>
                <p class="text-intro">{{ __('A question about our products? Send us a message and our team will get back to you.') }}</p>
            </div>
        </div>
    </section>

    <div class="container breadcrumb-row">
        @include('partials.breadcrumb', ['items' => [[__('Contact us'), null]]])
    </div>

    <div class="container contact">
        <div class="contact__form">
            @if (session('contact_status'))
                <div class="notice notice--success" role="status">{{ session('contact_status') }}</div>
            @endif

            <form method="post" action="{{ route('contact.send') }}" novalidate class="form-stack">
                @csrf
                <p class="field__help">{{ __('Fields marked with * are required.') }}</p>

                <div class="form-row">
                    <div class="field is-required">
                        <label class="field__label" for="contact-name">{{ __('Name') }}</label>
                        <input class="field__input @error('name') is-invalid @enderror" type="text" id="contact-name" name="name" value="{{ old('name') }}" required autocomplete="name" aria-describedby="contact-name-error">
                        <p class="field__error" id="contact-name-error">@error('name'){{ $message }}@enderror</p>
                    </div>
                    <div class="field is-required">
                        <label class="field__label" for="contact-email">{{ __('Email') }}</label>
                        <input class="field__input @error('email') is-invalid @enderror" type="email" id="contact-email" name="email" value="{{ old('email') }}" required autocomplete="email" aria-describedby="contact-email-help contact-email-error">
                        <p class="field__help" id="contact-email-help">{{ __('Expected format: name@domain.com') }}</p>
                        <p class="field__error" id="contact-email-error">@error('email'){{ $message }}@enderror</p>
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="contact-subject">{{ __('Subject') }}</label>
                    <select class="field__input field__select" id="contact-subject" name="subject">
                        <option value="">{{ __('General question') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->plain_name }}" @selected(old('subject') === $product->plain_name)>{{ $product->plain_name }}</option>
                        @endforeach
                        <option value="Where to buy" @selected(old('subject') === 'Where to buy')>{{ __('Where to buy') }}</option>
                    </select>
                </div>

                <div class="field is-required">
                    <label class="field__label" for="contact-message">{{ __('Message') }}</label>
                    <textarea class="field__input field__textarea @error('message') is-invalid @enderror" id="contact-message" name="message" rows="6" maxlength="4000" required aria-describedby="contact-message-error">{{ old('message') }}</textarea>
                    <p class="field__error" id="contact-message-error">@error('message'){{ $message }}@enderror</p>
                </div>

                <div class="check">
                    <input class="check__input @error('consent') is-invalid @enderror" type="checkbox" id="contact-consent" name="consent" value="1" required @checked(old('consent')) aria-describedby="contact-consent-error">
                    <label class="check__label" for="contact-consent">
                        {{ __('I agree that my details are used to answer my request.') }}
                        <a href="{{ route('page', ['slug' => 'data-protection']) }}">{{ __('Data protection policy') }}</a>.
                    </label>
                    <p class="field__error" id="contact-consent-error">@error('consent'){{ $message }}@enderror</p>
                </div>

                <p class="notice notice--info">{{ __('To report a side effect, please contact your doctor or pharmacist, or your national reporting system.') }}</p>

                <div><button class="btn btn--pink" type="submit">{{ __('Send') }}</button></div>
            </form>
        </div>

        <aside class="contact__aside">
            <div class="info-card bg-sky">
                <h2 class="h3">{{ setting('distributor.name') }}</h2>
                <p>{{ setting('distributor.address1') }}<br>{{ setting('distributor.address2') }}</p>
                @if (setting('distributor.phone'))
                    <p class="info-card__line">@include('partials.icon', ['name' => 'phone', 'size' => 18]) <span>{{ setting('distributor.phone') }}</span></p>
                @endif
                @if (setting('distributor.email'))
                    <p class="info-card__line">@include('partials.icon', ['name' => 'mail', 'size' => 18]) <a href="mailto:{{ setting('distributor.email') }}">{{ setting('distributor.email') }}</a></p>
                @endif
            </div>
            <div class="info-card bg-cream">
                <h2 class="h3">{{ __('Questions you might have') }}</h2>
                <p>{{ __('Many answers are already in our Q&A.') }}</p>
                <a class="btn btn--pink" href="{{ route('faq') }}">{{ __('See the Q&A') }}</a>
            </div>
        </aside>
    </div>
@endsection

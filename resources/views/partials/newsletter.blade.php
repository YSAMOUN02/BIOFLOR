@php
    $subscribed = session('newsletter_status') === 'subscribed';
    $consent    = view('partials.consent-text')->render();
@endphp

<section class="newsletter" id="newsletter" aria-labelledby="newsletter-title">
    <div class="container newsletter__row">
        <div class="newsletter__intro">
            <h2 id="newsletter-title">{{ setting('newsletter.title', __('Our newsletter')) }}</h2>
            <p class="text-intro">{{ str_replace('{brand}', brand(), (string) setting('newsletter.text')) }}</p>
        </div>

        <div class="newsletter__body">
            <div class="newsletter__success js-newsletter-success" role="alert" @unless ($subscribed) hidden @endunless>
                @include('partials.newsletter-success')
            </div>

            <form class="newsletter__form js-newsletter-form" method="post" action="{{ route('newsletter.subscribe') }}"
                  novalidate @if ($subscribed) hidden @endif
                  data-error="{{ __('Your request could not be completed. Please try again later.') }}">
                @csrf
                <input type="hidden" name="source" value="block">

                <div class="newsletter__names">
                    <div class="field is-required">
                        <label class="field__label" for="nl-first-name">{{ __('First name') }}</label>
                        <input class="field__input" type="text" id="nl-first-name" name="first_name" maxlength="40"
                               value="{{ old('first_name') }}" placeholder="{{ __('Enter your first name') }}" required
                               autocomplete="given-name" aria-describedby="nl-first-name-error">
                        <p class="field__error" id="nl-first-name-error">@error('first_name'){{ $message }}@enderror</p>
                    </div>
                    <div class="field is-required">
                        <label class="field__label" for="nl-last-name">{{ __('Last name') }}</label>
                        <input class="field__input" type="text" id="nl-last-name" name="last_name" maxlength="80"
                               value="{{ old('last_name') }}" placeholder="{{ __('Enter your last name') }}" required
                               autocomplete="family-name" aria-describedby="nl-last-name-error">
                        <p class="field__error" id="nl-last-name-error">@error('last_name'){{ $message }}@enderror</p>
                    </div>
                </div>

                <div class="field is-required">
                    <label class="field__label" for="nl-email">{{ __('Email') }}</label>
                    <input class="field__input" type="email" id="nl-email" name="email" maxlength="50"
                           value="{{ old('email') }}" placeholder="{{ __('Email') }}" required autocomplete="email"
                           aria-describedby="nl-email-help nl-email-error">
                    <p class="field__help" id="nl-email-help">{{ __('Expected format: name@domain.com') }}</p>
                    <p class="field__error" id="nl-email-error">@error('email'){{ $message }}@enderror</p>
                </div>

                <div class="check">
                    <input class="check__input" type="checkbox" id="nl-consent" name="consent" value="1" required
                           @checked(old('consent')) aria-describedby="nl-consent-error">
                    <label class="check__label" for="nl-consent">{!! $consent !!}</label>
                    <p class="field__error" id="nl-consent-error">@error('consent'){{ $message }}@enderror</p>
                </div>

                <p class="form-error js-form-error" role="alert" hidden></p>

                <button class="btn btn--pink" type="submit">{{ __('Subscribe') }}</button>
            </form>
        </div>
    </div>
</section>

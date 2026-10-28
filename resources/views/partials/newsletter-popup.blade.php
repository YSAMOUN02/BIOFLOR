{{-- Newsletter pop-in: opened by site.js once a visitor has viewed enough pages. --}}
@if (setting('newsletter.popup_enabled', '1') === '1' && ! request()->routeIs('contact'))
    <div class="modal nl-popup js-newsletter-popup" id="newsletter-popup" role="dialog" aria-modal="true"
         aria-labelledby="nl-popup-title" hidden
         data-after-pages="{{ config('storefront.newsletter_popup.after_pages') }}"
         data-snooze-days="{{ config('storefront.newsletter_popup.snooze_days') }}"
         data-subscribed-days="{{ config('storefront.newsletter_popup.subscribed_days') }}">
        <div class="modal__dialog nl-popup__dialog">
            <button class="icon-btn modal__close js-modal-close" type="button" aria-label="{{ __('Close dialog') }}">
                @include('partials.icon', ['name' => 'close', 'size' => 18, 'stroke' => 2])
            </button>

            <div class="nl-popup__media" aria-hidden="true">
                <img src="{{ media_url(setting('newsletter.popup_image')) ?? asset('img/placeholders/newsletter.svg') }}"{{ img_size(media_url(setting('newsletter.popup_image')) ?? asset('img/placeholders/newsletter.svg')) }} alt="" loading="lazy">
            </div>

            <div class="nl-popup__body">
                <h2 class="nl-popup__title" id="nl-popup-title">{{ setting('newsletter.popup_title', __('Newsletter')) }}</h2>

                <div class="js-newsletter-success" role="alert" hidden>
                    @include('partials.newsletter-success')
                </div>

                <form class="newsletter__form js-newsletter-form" method="post" action="{{ route('newsletter.subscribe') }}" novalidate
                      data-error="{{ __('Your request could not be completed. Please try again later.') }}">
                    @csrf
                    <input type="hidden" name="source" value="popup">

                    <div class="field is-required">
                        <label class="field__label" for="nlp-email">{{ __('Email') }}</label>
                        <input class="field__input" type="email" id="nlp-email" name="email" maxlength="50"
                               placeholder="{{ __('Email') }}" required autocomplete="email" aria-describedby="nlp-email-help nlp-email-error">
                        <p class="field__help" id="nlp-email-help">{{ __('Expected format: name@domain.com') }}</p>
                        <p class="field__error" id="nlp-email-error"></p>
                    </div>

                    <div class="check">
                        <input class="check__input" type="checkbox" id="nlp-consent" name="consent" value="1" required aria-describedby="nlp-consent-error">
                        <label class="check__label" for="nlp-consent">
                            @include('partials.consent-text')
                        </label>
                        <p class="field__error" id="nlp-consent-error"></p>
                    </div>

                    <p class="form-error js-form-error" role="alert" hidden></p>

                    <button class="btn btn--pink" type="submit">{{ __('Subscribe') }}</button>
                </form>
            </div>
        </div>
    </div>
@endif

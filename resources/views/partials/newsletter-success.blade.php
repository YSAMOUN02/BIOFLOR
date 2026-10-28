<div class="newsletter-success">
    <span class="newsletter-success__icon" aria-hidden="true">@include('partials.icon', ['name' => 'check', 'size' => 28, 'stroke' => 2])</span>
    <div>
        <p class="newsletter-success__title">{{ setting('newsletter.success_title') }}</p>
        <p>{{ str_replace('{brand}', brand(), (string) setting('newsletter.success_text')) }}</p>
    </div>
</div>

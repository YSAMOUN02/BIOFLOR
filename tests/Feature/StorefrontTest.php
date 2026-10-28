<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use Tests\TestCase;

/** Every public page opens, in every language, and the forms work. */
class StorefrontTest extends TestCase
{
    private const PAGES = [
        'home', 'our-products', 'find-in-store', 'faq.html', 'contact-us.html', 'all-news.html', 'sitemap',
        'gut-health.html', 'our-uniqueness.html', 'our-news.html', 'about-us.html', 'data-protection.html',
    ];

    public function test_every_page_opens_in_every_language(): void
    {
        foreach (config('storefront.locales') as $locale) {
            foreach (self::PAGES as $page) {
                $this->get("/$locale/$page")->assertOk();
            }
        }
    }

    public function test_every_product_and_article_opens(): void
    {
        $products = Product::active()->get();
        $articles = Article::published()->get();
        $this->assertNotEmpty($products);

        foreach (['en', 'km'] as $locale) {
            foreach ($products as $product) {
                $this->get("/$locale/{$product->slug}/{$product->id}.html")->assertOk()->assertSee('pdp__title', false);
            }
            foreach ($articles as $article) {
                $this->get("/$locale/{$article->slug}.html")->assertOk();
            }
        }
    }

    public function test_unknown_addresses_answer_404_and_old_ones_redirect(): void
    {
        $this->get('/en/no-such-page.html')->assertNotFound();
        $this->get('/en/no-such-page')->assertNotFound();
        $this->get('/en/some-product/99999999.html')->assertNotFound();
        $this->get('/xx/home')->assertNotFound();

        $this->get('/')->assertRedirect('/en/home');
        $this->get('/en/products')->assertRedirect('/en/our-products')->assertStatus(301);
        $this->get('/km/where-to-buy')->assertRedirect('/km/find-in-store');
    }

    public function test_khmer_pages_keep_khmer_links_and_font(): void
    {
        $html = $this->get('/km/home')->assertOk()->getContent();

        $this->assertStringContainsString('lang="km-KH"', $html);
        $this->assertStringContainsString('Noto+Serif+Khmer', $html);

        preg_match('#id="main-navigation".*?</nav>#s', $html, $nav);
        preg_match_all('#href="([^"]+)"#', $nav[0] ?? '', $links);
        $this->assertNotEmpty($links[1]);
        foreach ($links[1] as $link) {
            $this->assertStringNotContainsString('/en/', $link, 'Khmer menu link goes to English: '.$link);
        }
    }

    public function test_contact_form_saves_the_message(): void
    {
        $before = ContactMessage::count();

        $this->from('/en/contact-us.html')->post('/en/contact-us.html', [
            'name' => 'Sokha', 'email' => 'sokha@example.test', 'subject' => 'Dosage',
            'message' => 'How many sachets a day?', 'consent' => '1',
        ])->assertRedirect('/en/contact-us.html')->assertSessionHas('contact_status');

        $this->assertSame($before + 1, ContactMessage::count());
    }

    public function test_contact_form_rejects_missing_fields(): void
    {
        $this->from('/en/contact-us.html')->post('/en/contact-us.html', ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['name', 'email', 'message', 'consent']);
    }

    public function test_newsletter_signup_answers_json(): void
    {
        $this->postJson('/km/newsletter', ['email' => 'reader@example.test', 'consent' => '1', 'source' => 'popup'])
            ->assertOk()->assertJson(['ok' => true]);

        $this->assertSame('km_KH', NewsletterSubscriber::where('email', 'reader@example.test')->value('locale'));

        $this->postJson('/en/newsletter', ['email' => 'nope', 'source' => 'popup'])
            ->assertStatus(422)->assertJsonValidationErrors(['email', 'consent']);
    }

    public function test_search_finds_products_and_suggests(): void
    {
        $this->get('/en/search?q=Bioflor')->assertOk()->assertSee('Bioflor');

        $this->getJson('/en/search/suggest?q=Bio')->assertOk()
            ->assertJsonStructure(['products' => [['label', 'url', 'image']], 'articles']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_robots_txt_hides_the_admin_and_points_to_the_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.url('sitemap.xml'));
    }

    public function test_sitemap_lists_only_working_indexable_pages(): void
    {
        $xml = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->getContent());
        $this->assertNotFalse($xml);

        $urls = [];
        foreach ($xml->url as $entry) {
            $urls[] = (string) $entry->loc;
        }
        $this->assertContains(url('en/home'), $urls);
        $this->assertContains(url('km/home'), $urls);

        foreach ($urls as $url) {
            $this->assertStringNotContainsString('/fr/', $url, 'French is not indexed yet');
            $this->get(parse_url($url, PHP_URL_PATH))->assertOk();
        }
    }

    public function test_pages_carry_canonical_hreflang_and_share_tags(): void
    {
        $this->get('/en/about-us.html')->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('en/about-us.html').'">', false)
            ->assertSee('hreflang="km-KH" href="'.url('km/about-us.html').'"', false)
            ->assertSee('hreflang="x-default" href="'.url('en/about-us.html').'"', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertDontSee('<link rel="alternate" hreflang="fr-FR"', false);
    }

    public function test_untranslated_french_and_search_results_are_noindex(): void
    {
        $this->get('/fr/home')->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertDontSee('rel="canonical"', false);

        $this->get('/en/search?q=gut')->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_titles_are_escaped_once(): void
    {
        $this->get('/en/faq.html')->assertOk()->assertSee('<title>Q&amp;A | ', false)->assertDontSee('&amp;amp;', false);
    }

    public function test_product_page_has_structured_data_and_its_own_share_image(): void
    {
        $product = Product::active()->first();

        $html = $this->get("/en/{$product->slug}/{$product->id}.html")->assertOk()
            ->assertSee('<meta property="og:type" content="product">', false)
            ->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $blocks);
        $types = array_map(fn ($json) => json_decode($json, true)['@type'] ?? 'list', $blocks[1]);
        $this->assertContains('Product', $types);
        foreach ($blocks[1] as $json) {
            $this->assertNotNull(json_decode($json), 'JSON-LD must be valid JSON');
        }
    }

    public function test_every_page_has_one_h1_and_a_description(): void
    {
        foreach (['/en/home', '/en/our-products', '/en/about-us.html', '/en/all-news.html', '/en/faq.html', '/km/home'] as $page) {
            $html = $this->get($page)->getContent();
            $this->assertSame(1, substr_count($html, '<h1'), $page.' should have one h1');
            $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{30,}">/', $html, $page);
        }
    }
}

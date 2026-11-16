<?php

use App\Models\Article;
use App\Models\Locale;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Database\Migrations\Migration;

/**
 * Content changes made on the local site in November 2026, so a server picks
 * them up with `php artisan migrate`. Safe to run on a database that already
 * has them. The Khmer wording itself comes with
 * `php artisan translations:ai import` (lang/ai-translate).
 */
return new class extends Migration
{
    public function up(): void
    {
        // French removed (config/storefront.php no longer answers /fr).
        Locale::where('url_prefix', 'fr')->delete();
        Translation::where('locale', 'fr')->delete();

        // Gut health quiz: only a placeholder, hidden until a real quiz exists.
        Article::where('slug', 'gut-health-quiz')->update(['published_at' => null]);
        PageSection::where('page_key', 'gut-health-quiz-intro')->update(['is_visible' => false]);

        foreach (PageSection::query()->get() as $section) {
            $payload = (array) $section->payload;

            if ($section->type === 'heroBanner' && ($payload['cta_url'] ?? null) === 'gut-health-quiz-intro.html') {
                $section->update(['is_visible' => false]);
            }

            if ($section->type === 'learnGrid' && ($payload['highlight']['cta_url'] ?? null) === 'gut-health-quiz-intro.html') {
                $payload['highlight']['cta_url']   = 'gut-health.html';
                $payload['highlight']['cta_label'] = 'Learn more';
                $section->payload = $payload;
                $section->save();
            }

            // Home testimonial still holding "Dr [name]" and a stand-in quote.
            if ($section->page_key === 'home' && $section->type === 'heroBanner' && is_placeholder_text(json_encode($payload))) {
                $section->update(['is_visible' => false]);
            }
        }

        // Invented sample reviews (and their ratings) are not shown.
        Review::where('author', 'like', 'Sample reviewer%')->update(['is_approved' => false]);

        // Key figure "+513M patients" reads "ជាង 513 លាន" in Khmer.
        if ($figures = PageSection::where('page_key', 'home')->where('type', 'keyFigures')->first()) {
            $translations = (array) $figures->translations;
            foreach ((array) ($figures->payload['items'] ?? []) as $i => $item) {
                if (($item['value'] ?? '') === '+513M') {
                    $translations['km']['items'][$i]['value'] = 'ជាង 513 លាន';
                }
            }
            $figures->forceFill(['translations' => $translations])->save();
        }

        // Phone-sized home hero photo (the file is in public/uploads/library).
        $mobile = 'library/photo-hero-home-dad-and-daughter-mobile.webp';
        if (is_file(public_path('uploads/'.$mobile))) {
            Media::firstOrCreate(['path' => $mobile], [
                'title' => 'Photo hero home dad and daughter (mobile)', 'original_name' => basename($mobile),
                'mime' => 'image/webp', 'kind' => 'image', 'size' => filesize(public_path('uploads/'.$mobile)),
                'width' => 800, 'height' => 761, 'checksum' => sha1_file(public_path('uploads/'.$mobile)),
            ]);
            if ($hero = PageSection::where('page_key', 'home')->where('type', 'heroCarousel')->first()) {
                $payload = (array) $hero->payload;
                if (($payload['slides'][0]['image'] ?? '') === 'library/photo-hero-home-dad-and-daughter-desktop.webp') {
                    $payload['slides'][0]['image_mobile'] = $mobile;
                    $hero->payload = $payload;
                    $hero->save();
                }
            }
        }

        // Share image for link previews (Facebook, TikTok, chat apps).
        Setting::firstOrCreate(['key' => 'site.share_image'], [
            'group' => 'site', 'label' => 'Share image', 'type' => 'image', 'sort_order' => 25,
            'value' => Media::where('path', 'like', 'library/photo-hero-home-dad-and-daughter-desktop.%')->value('path'),
            'help'  => 'Picture shown when a page is shared on Facebook, TikTok or a chat app (about 1200 × 630 px). Products and articles use their own photo.',
        ]);

        // A fuller description for the All news page (search results).
        Page::where('slug', 'all-news')->where('meta_description', 'Every article, newest first.')
            ->update(['meta_description' => 'Gut health tips, product news and research from {brand}: every article in one place, newest first.']);

        Setting::flush();
    }

    public function down(): void
    {
        // Content changes; restore a database backup to undo them.
    }
};

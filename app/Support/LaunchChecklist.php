<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Locale;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductFaq;
use App\Models\Retailer;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * The numbered list of what is left before the site goes live (Admin →
 * Launch checklist). Most steps check themselves by looking for the
 * placeholder wording the seeders left in the content; the rest are ticked
 * by hand and stored as settings rows "checklist:{step id}" (hidden from the
 * Settings screen, which skips keys containing ":").
 */
class LaunchChecklist
{
    /** Placeholder wording left by the seeders (English). */
    private const EN = '/placeholder|replace this|add the approved reference|\[name\]/i';

    /** Placeholder wording left by TranslationSeeder (Khmer). */
    private const KM = '/អត្ថបទគំរូ|កន្លែងសម្រាប់ពាក្យ|សូមបន្ថែមឯកសារយោង|សូមជំនួស|\[ឈ្មោះ\]/u';

    /** Sample stores from RetailerSeeder. */
    private const SAMPLE_RETAILERS = [
        'Partner Pharmacy Online', 'HealthShop EU', 'Farmacia Directa', 'PharmaBox', 'MagnaPharm Head Quarter',
        'Central Pharmacy', 'Pharmacie du Centre', 'Apoteka Zdravlje', 'Farmacia San Marco',
    ];

    /** Product fields whose wording needs a Khmer version. */
    private const WORDED = ['pack_size', 'usage', 'dosage', 'precautions', 'storage', 'ingredients', 'how_to_use'];

    /** Legal pages linked from the site (terms, cookies and accessibility were taken out of the footer). */
    private const LEGAL_PAGES = [
        'data-protection' => 'Data protection',
    ];

    private string $km = 'km';

    /**
     * @return Collection<int, array{number: int, id: string, title: string, hint: ?string, done: bool,
     *     steps: array<int, array{id: string, label: string, detail: ?string, url: ?string, manual: bool, optional: bool, done: bool}>}>
     */
    public function tasks(): Collection
    {
        $ticked = Setting::where('key', 'like', 'checklist:%')->where('value', '1')->pluck('key')
            ->map(fn ($key) => substr($key, strlen('checklist:')))->flip();

        $tasks = [
            $this->aboutPage(),
            $this->productInformation(),
            $this->productQuestions(),
            $this->homePage(),
            $this->references(),
            $this->footerDetails(),
            $this->retailers(),
            $this->reviews(),
            $this->articles(),
            $this->legalPages(),
            $this->khmerReview(),
            $this->french(),
            $this->adminPassword(),
            $this->goLive(),
        ];

        return collect($tasks)->values()->map(function ($task, $i) use ($ticked) {
            $task['number'] = $i + 1;
            $task['steps']  = array_map(function ($step) use ($ticked) {
                if ($step['manual']) {
                    $step['done'] = isset($ticked[$step['id']]);
                }

                return $step;
            }, $task['steps']);
            $task['done'] = collect($task['steps'])->every(fn ($step) => $step['done'] || $step['optional']);

            return $task;
        });
    }

    /** Ids of the steps that are ticked by hand. */
    public function manualStepIds(): array
    {
        return $this->tasks()->flatMap(fn ($task) => $task['steps'])->where('manual', true)->pluck('id')->all();
    }

    // ------------------------------------------------------------- tasks

    private function aboutPage(): array
    {
        $sections = PageSection::where('page_key', 'about-us')->orderBy('sort_order')->get();
        $banners  = $sections->where('type', 'heroBanner')->values();
        $mission  = $sections->firstWhere('type', 'mosaic');
        $history  = $sections->firstWhere('type', 'timeline');
        $values   = $sections->firstWhere('type', 'iconGrid');

        $historyLeft = collect($history?->payload['items'] ?? [])
            ->filter(fn ($item) => $this->isPlaceholder($item) || preg_match('/^Milestone \d/i', (string) ($item['year'] ?? '')))
            ->count();

        // Khmer: every block needs Khmer text without placeholder wording.
        $khmerLeft = $sections->filter(function ($section) {
            $overlay = $section->translations[$this->km] ?? null;

            return ! $overlay || $this->isPlaceholder($overlay, self::KM) || str_contains(json_encode($overlay, JSON_UNESCAPED_UNICODE), 'ព្រឹត្តិការណ៍ទី');
        });

        return $this->task('about-page', 'Complete the About page', 'Tell visitors who brings Bioflor to Cambodia. Send the company profile and history to your developer if you prefer them to enter it.', [
            $this->manual('about-intro', 'Intro banner — write a one-line company introduction and check the photo', $this->sectionUrl($banners->get(0))),
            $this->auto('Our mission — replace the sample paragraph with your company profile', $mission && ! $this->isPlaceholder($mission->payload), $this->sectionUrl($mission)),
            $this->auto('Our history — fill in the milestones (year, title, short text)', $history && $historyLeft === 0, $this->sectionUrl($history),
                $historyLeft ? $historyLeft.' of '.count($history->payload['items'] ?? []).' milestones still sample text' : null),
            $this->manualOrHidden('about-research', 'Research banner — check the text fits your company, or hide the block', $banners->get(1)),
            $this->manualOrHidden('about-values', 'Our values — confirm or rewrite the four values, or hide the block', $values),
            $this->auto('Khmer version of every block', $khmerLeft->isEmpty(), $this->sectionUrl($khmerLeft->first(), $this->km),
                $khmerLeft->isNotEmpty() ? $khmerLeft->count().' block(s) still need Khmer text' : null),
            $this->manual('about-preview', 'Open the About page in English and Khmer and read it through', route('page', ['locale' => 'en', 'slug' => 'about-us'])),
        ]);
    }

    private function productInformation(): array
    {
        $localeCode = Locale::where('url_prefix', $this->km)->value('code');
        $names      = ProductAttribute::pluck('name', 'id');
        $references = ProductAttribute::where('code', 'references')->value('id');   // own task
        $steps      = [];

        $wording    = ProductAttribute::whereIn('code', self::WORDED)->pluck('id')->all();

        foreach (Product::orderBy('sort_order')->get() as $product) {
            $values = ProductAttributeValue::where('product_id', $product->id)->where('attribute_id', '!=', $references)->get();
            $translated = $values->where('locale', $localeCode)->keyBy('attribute_id');

            $english = $values->where('locale', '')->filter(fn ($v) => $this->isPlaceholder($v->value))->map(fn ($v) => $names[$v->attribute_id] ?? '?');

            // Khmer is missing, or still placeholder, for any worded field that has English.
            $khmer = $values->where('locale', '')->filter(fn ($v) => in_array($v->attribute_id, $wording) && filled($v->value))
                ->filter(fn ($v) => ! isset($translated[$v->attribute_id]) || $this->isPlaceholder($translated[$v->attribute_id]->value, self::KM))
                ->map(fn ($v) => $names[$v->attribute_id] ?? '?');

            $steps[] = $this->auto(plain_text($product->getRawOriginal('name')).' — English approved wording', $english->isEmpty(),
                route('admin.products.edit', $product->id), $english->isNotEmpty() ? 'Still sample text: '.$english->implode(', ') : null);
            $steps[] = $this->auto(plain_text($product->getRawOriginal('name')).' — Khmer approved wording', $khmer->isEmpty(),
                route('admin.products.edit', [$product->id, 'lang' => $this->km]), $khmer->isNotEmpty() ? 'Missing or sample text: '.$khmer->implode(', ') : null);
        }

        return $this->task('product-info', 'Enter the approved product information', 'Indications, dosage, storage, ingredients and how to use must match the wording approved for Cambodia (package leaflet / registration).', $steps);
    }

    private function productQuestions(): array
    {
        $faqs    = ProductFaq::all();
        $english = $faqs->filter(fn ($faq) => $this->isPlaceholder($faq->getRawOriginal('answer')))->count();
        $khmer   = $faqs->filter(fn ($faq) => blank($answer = $faq->translationFor($this->km, 'answer')) || $this->isPlaceholder($answer, self::KM))->count();

        return $this->task('product-faq', 'Answer the product questions', 'The questions and answers shown on each product page.', [
            $this->auto('English answers', $english === 0, route('admin.product-faqs.index'), $english ? $english.' answer(s) still sample text' : null),
            $this->auto('Khmer answers', $khmer === 0, route('admin.product-faqs.index'), $khmer ? $khmer.' answer(s) missing or still sample text' : null),
        ]);
    }

    private function homePage(): array
    {
        $sections    = PageSection::where('page_key', 'home')->orderBy('sort_order')->get();
        $testimonial = $sections->first(fn ($s) => $s->type === 'heroBanner' && ($s->payload['variant'] ?? '') === 'testimonial');

        return $this->task('home-page', 'Check the home page', null, [
            $this->auto('Testimonial — add a real, approved quote and the person\'s name (or hide the block)',
                ! $testimonial || ! $testimonial->is_visible || ! $this->isPlaceholder($testimonial->payload), $this->sectionUrl($testimonial)),
            $this->manual('home-figures', 'Key figures (studies, years, patients) — confirm the numbers and their references for Cambodia', $this->sectionUrl($sections->firstWhere('type', 'keyFigures'))),
            $this->manual('home-hero-claim', 'Hero photo shows "#1 probiotic drug worldwide" — get it approved or use a photo without the claim', $this->sectionUrl($sections->firstWhere('type', 'heroCarousel'))),
        ]);
    }

    private function references(): array
    {
        $sections = PageSection::where('type', 'collapsibleContent')->get();
        $pages    = $sections->filter(fn ($s) => $this->isPlaceholder($s->payload) || $this->isPlaceholder($s->translations[$this->km] ?? [], self::KM));

        $definition = ProductAttribute::where('code', 'references')->value('id');
        $products   = ProductAttributeValue::where('attribute_id', $definition)->get()
            ->filter(fn ($v) => $this->isPlaceholder($v->value) || $this->isPlaceholder($v->value, self::KM))->pluck('product_id')->unique();

        $articles = Article::all()->filter(fn ($a) => $this->isPlaceholder($a->getRawOriginal('references')))->count()
            + $this->translationsMatching(Article::class, 'references')->count();

        return $this->task('references', 'Add the references', 'The small numbers after a claim (¹ ² ³) point to the "See all references" list at the bottom of the page.', [
            $this->auto('Page references', $pages->isEmpty(), route('admin.sections.index', $pages->isNotEmpty() ? ['page_key' => $pages->first()->page_key] : []),
                $pages->isNotEmpty() ? 'Still sample text on: '.$pages->pluck('page_key')->unique()->implode(', ') : null),
            $this->auto('Product references', $products->isEmpty(), $products->isNotEmpty() ? route('admin.products.edit', $products->first()) : route('admin.products.index'),
                $products->isNotEmpty() ? $products->count().' product(s)' : null),
            $this->auto('Article references', $articles === 0, route('admin.articles.index'), $articles ? $articles.' article reference list(s) still sample text (English + Khmer)' : null),
        ]);
    }

    private function footerDetails(): array
    {
        $sample = fn (string $key) => str_contains((string) setting($key), 'example.com');

        return $this->task('footer', 'Finish the contact details in the footer', 'Settings → Footer & contact. An empty field is hidden on the site.', [
            $this->auto('Distributor email (still contact@example.com)', ! $sample('distributor.email'), route('admin.settings.index').'#group-footer'),
            $this->auto('Distributor website (still example.com)', ! $sample('distributor.website'), route('admin.settings.index').'#group-footer'),
            $this->auto('Manufacturer logo for the footer (optional)', filled(setting('company.logo')), route('admin.settings.index').'#group-brand', null, true),
            $this->auto('Instagram and YouTube links (optional — only when you have the accounts)', filled(setting('social.instagram')) && filled(setting('social.youtube')), route('admin.settings.index').'#group-footer', null, true),
        ]);
    }

    private function retailers(): array
    {
        $samples = Retailer::whereIn('name', self::SAMPLE_RETAILERS)->count();
        $real    = Retailer::whereNotIn('name', self::SAMPLE_RETAILERS)->count();

        return $this->task('retailers', 'List your shops on "Where to buy"', 'The page has two lists: "Buy online" (tick "Online shop" and give the website) and "Local pharmacies" (give the address; the site adds a map link).', [
            $this->auto('Add your online shops and local pharmacies', $real > 0, route('admin.retailers.create'), $real ? $real.' added' : null),
            $this->auto('Delete the sample stores', $samples === 0, route('admin.retailers.index'), $samples ? $samples.' sample store(s) left' : null),
        ]);
    }

    private function reviews(): array
    {
        $samples = Review::where('author', 'like', 'Sample reviewer%')->orWhere('body', 'like', 'Sample review%')->count();

        return $this->task('reviews', 'Remove the sample product reviews', 'Only publish real reviews, and check local rules on reviews for medicines.', [
            $this->auto('Delete the sample reviews', $samples === 0, route('admin.reviews.index'), $samples ? $samples.' sample review(s) left' : null),
        ]);
    }

    private function articles(): array
    {
        $unfinished = Article::where('body', 'like', '%Placeholder%')->get(['id', 'title']);
        $withKhmer  = Translation::where('translatable_type', (new Article)->getMorphClass())->where('locale', $this->km)
            ->where('field', 'body')->whereNotNull('value')->where('value', '!=', '')->pluck('translatable_id');
        $noKhmer    = Article::whereNotIn('id', $withKhmer)->count();

        return $this->task('articles', 'Finish the articles', 'Article titles and summaries are in Khmer; the full text is still English.', [
            $this->auto('Write the articles still marked "Placeholder"', $unfinished->isEmpty(),
                $unfinished->isNotEmpty() ? route('admin.articles.edit', $unfinished->first()->id) : route('admin.articles.index'),
                $unfinished->isNotEmpty() ? $unfinished->count().' article(s): '.$unfinished->pluck('title')->map(fn ($t) => plain_text($t))->take(3)->implode(' · ').($unfinished->count() > 3 ? ' …' : '') : null),
            $this->auto('Khmer text for each article', $noKhmer === 0, route('admin.articles.index'), $noKhmer ? $noKhmer.' article(s) without Khmer text' : null),
            $this->manual('articles-approved', 'Have the article texts checked for Cambodia (medical claims)', route('admin.articles.index')),
        ]);
    }

    private function legalPages(): array
    {
        $steps = [];

        foreach (self::LEGAL_PAGES as $slug => $title) {
            $page = Page::where('slug', $slug)->first();
            if (! $page) {
                continue;
            }

            $english = ! $this->isPlaceholder($page->getRawOriginal('body'));
            $khmer   = filled($page->translationFor($this->km, 'body')) && ! $this->isPlaceholder($page->translationFor($this->km, 'body'), self::KM);
            $missing = array_keys(array_filter(['English' => ! $english, 'Khmer' => ! $khmer]));

            $steps[] = $this->auto($title, $english && $khmer, route('admin.pages.edit', $page->id), $missing ? 'Missing: '.implode(' and ', $missing).' text' : null);
        }

        return $this->task('legal', 'Write the data protection page', 'The newsletter sign-up links to it. Use wording from your legal team, for Cambodian law.', $steps);
    }

    private function khmerReview(): array
    {
        return $this->task('khmer-review', 'Have the Khmer text checked by a native speaker', 'The Khmer text is a first draft. Check the menu, products, pages, Q&A, footer and buttons (button words are in lang/km.json — ask your developer).', [
            $this->manual('khmer-review', 'Khmer text reviewed and corrected', route('home', ['locale' => $this->km])),
        ]);
    }

    private function french(): array
    {
        $exists = Locale::where('url_prefix', 'fr')->exists();
        $count  = $exists ? Translation::where('locale', 'fr')->count() : 0;

        return $this->task('french', 'Decide about French', 'French is in the language menu but shows English text.', [
            $this->auto('Translate the site into French, or ask your developer to remove French', ! $exists || $count > 0, null),
        ]);
    }

    private function adminPassword(): array
    {
        $user = auth()->user();

        return $this->task('password', 'Change the admin password', 'The admin still uses the default password from setup.', [
            $this->auto('Set a new password', $user && ! Hash::check('password', $user->password), route('admin.account')),
        ]);
    }

    private function goLive(): array
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        return $this->task('go-live', 'Before going live (with your developer)', null, [
            $this->manual('brand-approval', 'Written approval from the brand owner to use the Bioflor name, logo, photos and video', null),
            $this->manual('regulatory', 'Site content approved for the Cambodian market (Ministry of Health rules for medicine information)', null),
            $this->auto('Real domain set (APP_URL in .env)', $host && ! in_array($host, ['localhost', '127.0.0.1'], true), null),
            $this->auto('Debug mode off (APP_DEBUG=false in .env)', ! config('app.debug'), null, 'With debug on, an error page shows code and file paths to visitors'),
            $this->auto('Site served over HTTPS (APP_URL starts with https://)', str_starts_with((string) config('app.url'), 'https://'), null),
            $this->auto('Login cookie sent over HTTPS only (SESSION_SECURE_COOKIE=true in .env)', (bool) config('session.secure'), null),
            $this->auto('PHP OPcache on (makes every page faster)', function_exists('opcache_get_status') && (bool) (@opcache_get_status(false)['opcache_enabled'] ?? false), null, 'Enable opcache in php.ini on the server'),
            $this->manual('docroot', 'The web server points at the public/ folder only, then run php artisan optimize', null),
            $this->manual('backup', 'Database backup and hosting in place', null),
        ]);
    }

    // ----------------------------------------------------------- helpers

    private function task(string $id, string $title, ?string $hint, array $steps): array
    {
        return ['id' => $id, 'title' => $title, 'hint' => $hint, 'steps' => $steps];
    }

    private function auto(string $label, bool $done, ?string $url, ?string $detail = null, bool $optional = false): array
    {
        return ['id' => '', 'label' => $label, 'detail' => $done ? null : $detail, 'url' => $url, 'manual' => false, 'optional' => $optional, 'done' => $done];
    }

    private function manual(string $id, string $label, ?string $url): array
    {
        return ['id' => $id, 'label' => $label, 'detail' => null, 'url' => $url, 'manual' => true, 'optional' => false, 'done' => false];
    }

    /** A block to confirm by hand, or nothing to do once it is hidden on the site. */
    private function manualOrHidden(string $id, string $label, ?PageSection $section): array
    {
        return $section && ! $section->is_visible
            ? $this->auto($label, true, $this->sectionUrl($section)) + ['note' => 'hidden on the site']
            : $this->manual($id, $label, $this->sectionUrl($section));
    }

    private function sectionUrl(?PageSection $section, ?string $lang = null): ?string
    {
        return $section ? route('admin.sections.edit', array_filter([$section->id, 'lang' => $lang])) : null;
    }

    /** True when text (or any string in an array) still carries placeholder wording. */
    private function isPlaceholder(mixed $value, string $pattern = self::EN): bool
    {
        $text = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $value;

        // image paths such as img/placeholders/… are not text
        $text = preg_replace('#img/placeholders/[^"\s]*#', '', $text);

        return (bool) preg_match($pattern, $text);
    }

    private function translationsMatching(string $model, string $field): Collection
    {
        return Translation::where('translatable_type', (new $model)->getMorphClass())
            ->where('locale', $this->km)->where('field', $field)->get()
            ->filter(fn ($t) => $this->isPlaceholder($t->value, self::KM));
    }
}

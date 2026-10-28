<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Locale;
use App\Models\Media;
use App\Models\PageSection;
use App\Support\MediaImporter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Page composer: add, reorder, show/hide and edit the typed components that
 * make up a page. Each type has a field schema (schema()), rendered as a form
 * with image/video upload or library pick and repeatable item lists; the
 * payload can still be edited as JSON for anything the form does not cover.
 */
class SectionController extends Controller
{
    /** Component types the front end can render (views under resources/views/sections). */
    public const TYPES = [
        'heroCarousel'       => 'Hero carousel',
        'keyFigures'         => 'Key figures band',
        'introBlock'         => 'Title + intro + button',
        'productTabs'        => 'Product range tabs',
        'featureBlock'       => 'Feature block (navy)',
        'iconCarousel'       => 'Indications carousel',
        'learnGrid'          => 'Highlight card + learn cards',
        'tvSpot'             => 'Video spot',
        'heroBanner'         => 'Banner (informative / testimonial / highlight)',
        'articleCarousel'    => 'Article carousel',
        'articleGrid'        => 'Article grid',
        'collapsibleContent' => 'References (collapsible)',
        'richText'           => 'Heading + rich text',
        'mosaic'             => 'Two columns (text + image)',
        'categoryBanner'     => 'Range page header',
        'faqList'            => 'Q&A list',
        'ctaBox'             => 'Call-to-action box',
        'timeline'           => 'Timeline',
        'iconGrid'           => 'Icon grid',
        'breadcrumbs'        => 'Breadcrumb',
        'topicLinks'         => 'News topic links',
    ];

    /** Pages built from sections, and their regions. */
    public const PAGES = [
        'home'                  => 'Home',
        'our-products'          => 'Our products (range page)',
        'pdp'                   => 'Product pages (regions: after_main, middle, bottom)',
        'gut-health'            => 'Gut health hub',
        'our-uniqueness'        => 'Our uniqueness hub',
        'our-news'              => 'Our news landing',
        'about-us'              => 'About us',
        'faq'                   => 'Q&A',
        'gut-health-quiz-intro' => 'Quiz intro',
    ];

    /** Starter payload per type, used when a section is created. */
    public const EXAMPLES = [
        'heroCarousel'       => ['interval' => 6000, 'slides' => [['title' => 'Slide title', 'text' => '<p>Short text.</p>', 'cta_label' => 'Explore now', 'cta_url' => 'our-products', 'image' => 'img/placeholders/hero-1.svg', 'image_mobile' => null, 'image_alt' => '']]],
        'keyFigures'         => ['items' => [['icon' => 'img/icons/studies.svg', 'value' => '100', 'label' => 'label', 'ref' => '1']]],
        'introBlock'         => ['title' => 'Title', 'text' => 'Intro text.', 'cta_label' => 'See all products', 'cta_url' => 'our-products'],
        'productTabs'        => ['category' => 'our-products', 'all_label' => 'All the products', 'layout' => 'carousel'],
        'featureBlock'       => ['title' => 'Title', 'text' => 'Intro.', 'image' => 'img/placeholders/feature.svg', 'item_title' => 'Item title', 'item_text' => 'Item text.', 'cta_label' => 'Learn more', 'cta_url' => 'gut-health.html'],
        'iconCarousel'       => ['title' => 'When can {brand} help?', 'items' => [['icon' => 'img/icons/diarrhea.svg', 'color' => 'magenta', 'title' => 'Title', 'url' => 'gut-health.html']]],
        'learnGrid'          => ['bubbles' => true, 'highlight' => ['title' => 'Title', 'cta_label' => 'Take the quiz', 'cta_url' => 'gut-health-quiz-intro.html', 'image' => 'img/placeholders/highlight.svg'], 'cards' => [['title' => 'Card', 'text' => 'Text.', 'cta_label' => 'Explore', 'cta_url' => 'gut-health.html', 'image' => 'img/placeholders/learn-1.svg']]],
        'tvSpot'             => ['image' => 'img/placeholders/tv-spot.svg', 'video_url' => '', 'legend' => 'Our TV spot', 'transcript_title' => 'Video text transcript', 'transcript' => '<p>Transcript.</p>'],
        'heroBanner'         => ['variant' => 'informative', 'align' => 'left', 'background' => 'secondary', 'full_width' => false, 'surtitle' => '', 'title' => 'Title', 'title_style' => '', 'text' => '<p>Text.</p>', 'cta_label' => 'Discover more', 'cta_url' => 'faq.html', 'image' => 'img/icons/checks.svg', 'image_shape' => 'blob'],
        'articleCarousel'    => ['style' => 'default', 'title' => 'Latest news', 'intro' => 'Intro.', 'cta_label' => 'Our latest news', 'cta_url' => 'all-news.html', 'category' => 'news', 'tag' => null, 'featured' => false, 'limit' => 6],
        'articleGrid'        => ['title' => 'Title', 'intro' => 'Intro.', 'category' => 'gut-health', 'slugs' => [], 'offset' => 0, 'limit' => 3],
        'collapsibleContent' => ['label_show' => 'See all references', 'label_hide' => 'Hide references', 'body' => '<p>(1) Reference.</p>'],
        'richText'           => ['title' => 'Title', 'title_tag' => 'h2', 'subtitle' => '', 'html' => '<p>Text.</p>', 'width' => 'md'],
        'mosaic'             => ['html' => '<h2>Title</h2><p>Text.</p>', 'image' => 'img/placeholders/learn-2.svg', 'image_side' => 'right', 'aside' => ''],
        'categoryBanner'     => ['title' => 'Our products', 'text' => 'Intro.', 'image' => 'img/placeholders/category-banner.svg'],
        'faqList'            => ['numbered' => true, 'group' => null],
        'ctaBox'             => ['background' => 'secondary', 'title' => 'Title', 'text' => 'Text.', 'buttons' => [['label' => 'Contact us', 'url' => 'contact-us.html', 'style' => 'pink']]],
        'timeline'           => ['background' => 'fourth', 'title' => 'Our history', 'items' => [['year' => '2000', 'title' => 'Milestone', 'text' => 'Text.']]],
        'iconGrid'           => ['background' => 'fifth', 'title' => 'Title', 'intro' => '', 'carousel' => false, 'items' => [['icon' => 'img/icons/quality.svg', 'title' => 'Title', 'text' => 'Text.']]],
        'breadcrumbs'        => ['title' => 'Page title'],
        'topicLinks'         => ['label' => 'Browse by topic', 'topics' => ['Seasonal', 'Nutrition']],
    ];

    public function index(Request $request)
    {
        $pageKey = $request->query('page_key', 'home');

        return view('admin.sections.index', [
            'sections' => PageSection::where('page_key', $pageKey)->orderBy('region')->orderBy('sort_order')->get(),
            'pageKey'  => $pageKey,
            'pageKeys' => collect(self::PAGES)->keys()->merge(PageSection::distinct()->pluck('page_key'))->unique()->values(),
            'pages'    => self::PAGES,
        ]);
    }

    public function create(Request $request)
    {
        $pageKey = $request->query('page_key', 'home');
        $type    = array_key_exists($request->query('type'), self::TYPES) ? $request->query('type') : 'heroBanner';

        $section = new PageSection([
            'page_key'   => $pageKey,
            'region'     => $pageKey === 'pdp' ? 'bottom' : 'main',
            'type'       => $type,
            'payload'    => self::EXAMPLES[$type] ?? [],
            'is_visible' => true,
            'sort_order' => (PageSection::where('page_key', $pageKey)->max('sort_order') ?? 0) + 10,
        ]);

        return $this->form($section);
    }

    public function edit(PageSection $section)
    {
        return $this->form($section);
    }

    private function form(PageSection $section)
    {
        $locale = $section->exists ? $this->translationLocale(request('lang')) : null;

        return view('admin.sections.form', [
            'section'  => $section,
            'types'    => self::TYPES,
            'examples' => self::EXAMPLES,
            'schema'   => $this->schema($section->type),
            'images'   => Media::where('kind', 'image')->latest()->get(['title', 'path']),
            'videos'   => Media::where('kind', 'video')->latest()->get(['title', 'path']),
            'locales'  => Locale::orderBy('sort_order')->get(),
            'lang'     => $locale,
        ]);
    }

    /** A non-default language by URL prefix, or null. */
    private function translationLocale(?string $prefix): ?Locale
    {
        return $prefix ? Locale::where('url_prefix', $prefix)->where('is_default', false)->first() : null;
    }

    /** Field types whose value differs per language. */
    public const TEXT_FIELDS = ['text', 'textarea', 'html'];

    /** Keep only text fields (lists by position) from the submitted language form. */
    private function buildTranslation(array $schema, array $input): array
    {
        $out = [];

        foreach ($schema as $key => $def) {
            $value = $input[$key] ?? null;

            if ($def['type'] === 'list') {
                $items = [];
                foreach ((array) $value as $i => $item) {
                    $items[(int) $i] = $this->buildTranslation($def['fields'], (array) $item);
                }
                $out[$key] = $items;
            } elseif ($def['type'] === 'group') {
                $out[$key] = $this->buildTranslation($def['fields'], (array) $value);
            } elseif (in_array($def['type'], self::TEXT_FIELDS, true)) {
                $out[$key] = trim((string) $value);
            }
        }

        return $out;
    }

    public function store(Request $request)
    {
        $section = PageSection::create($this->validated($request));

        return redirect()->route('admin.sections.edit', $section)->with('status', 'Section created.');
    }

    public function update(Request $request, PageSection $section)
    {
        // Language mode: only the section's text in that language is saved.
        if ($locale = $this->translationLocale($request->input('lang'))) {
            $translations = $section->translations ?? [];
            $translations[$locale->url_prefix] = $this->buildTranslation($this->schema($section->type), (array) $request->input('fields', []));
            $section->update(['translations' => $translations]);

            return redirect()->route('admin.sections.edit', [$section, 'lang' => $locale->url_prefix])
                ->with('status', $locale->label.' text saved.');
        }

        $section->update($this->validated($request, $section));

        return redirect()->route('admin.sections.edit', $section)->with('status', 'Section saved.');
    }

    public function destroy(PageSection $section)
    {
        $pageKey = $section->page_key;
        $section->delete();

        return redirect()->route('admin.sections.index', ['page_key' => $pageKey])
            ->with('status', 'Section deleted.');
    }

    /** Move a section up or down in the page order. */
    public function move(Request $request, PageSection $section)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $neighbour = PageSection::where('page_key', $section->page_key)
            ->where('sort_order', $direction === 'up' ? '<' : '>', $section->sort_order)
            ->orderBy('sort_order', $direction === 'up' ? 'desc' : 'asc')
            ->first();

        if ($neighbour) {
            $order = $section->sort_order;
            $section->update(['sort_order' => $neighbour->sort_order]);
            $neighbour->update(['sort_order' => $order]);
        }

        return back();
    }

    public function toggle(PageSection $section)
    {
        $section->update(['is_visible' => ! $section->is_visible]);

        return back()->with('status', $section->is_visible ? 'Section shown.' : 'Section hidden.');
    }

    private function validated(Request $request, ?PageSection $section = null): array
    {
        $data = $request->validate([
            'page_key'   => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'],
            'region'     => ['required', 'string', 'max:60'],
            'type'       => ['required', 'string', 'max:60'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'editor'     => ['nullable', 'in:form,json'],
            'payload'    => ['nullable', 'string'],
            'fields'     => ['nullable', 'array'],
            'is_visible' => ['nullable'],
        ]);

        $schema   = $this->schema($data['type']);
        $existing = $section?->payload ?? [];

        $typeChanged = $section && $data['type'] !== $section->type;

        if ($typeChanged) {
            // The submitted fields belong to the old type: start the new type from its example.
            $payload = self::EXAMPLES[$data['type']] ?? [];
        } elseif (($data['editor'] ?? 'form') === 'json' || ! $schema) {
            // Validate the JSON here so a typo returns a field error rather than a silently emptied payload.
            $decoded = json_decode((string) ($data['payload'] ?? '{}'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages(['payload' => 'Payload is not valid JSON: '.json_last_error_msg()]);
            }

            $payload = $decoded ?: [];
        } else {
            $built   = $this->build($schema, (array) $request->input('fields', []), (array) $request->file('uploads', []));
            // Keys the form does not know about are kept as they were.
            $payload = array_replace(array_diff_key($existing, $schema), $built);
        }

        unset($data['editor'], $data['fields']);
        $data['payload']    = $payload;
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);

        return $data;
    }

    /** Turn submitted form fields (and uploads at the same paths) into a payload. */
    private function build(array $schema, array $input, array $uploads): array
    {
        $out = [];

        foreach ($schema as $key => $def) {
            $value  = $input[$key] ?? null;
            $upload = $uploads[$key] ?? null;

            $out[$key] = match ($def['type']) {
                'list'     => collect((array) $value)->map(fn ($item, $i) => $this->build($def['fields'], (array) $item, (array) (((array) $upload)[$i] ?? [])))->values()->all(),
                'group'    => $this->build($def['fields'], (array) $value, (array) $upload),
                'checkbox' => (bool) $value,
                'number'   => is_numeric($value) ? $value + 0 : null,
                'tags'     => array_values(array_filter(array_map('trim', explode(',', (string) $value)), 'strlen')),
                'image', 'video' => $this->mediaValue($value, $upload, $def['type']),
                'select'   => $value === '' ? null : $value,
                default    => trim((string) $value),
            };
        }

        return $out;
    }

    private function mediaValue(?string $value, mixed $upload, string $kind): ?string
    {
        if ($upload instanceof UploadedFile && $upload->isValid()) {
            $allowed = $kind === 'video' ? ['mp4', 'webm', 'ogg'] : ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (! in_array(strtolower($upload->getClientOriginalExtension()), $allowed, true)) {
                throw ValidationException::withMessages(['uploads' => ucfirst($kind).' must be one of: '.implode(', ', $allowed).'.']);
            }

            return app(MediaImporter::class)->importUpload($upload)->path;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Form fields per section type.
     * Types: text, textarea, html, link, image, video, select, checkbox, number, tags, group, list.
     */
    public function schema(string $type): array
    {
        $link   = fn (string $label = 'Button link') => ['type' => 'link', 'label' => $label, 'help' => 'e.g. our-products, faq.html, or a full https:// address'];
        $cta    = fn () => ['cta_label' => ['type' => 'text', 'label' => 'Button label'], 'cta_url' => $link()];
        $bg     = fn (array $only = []) => ['type' => 'select', 'label' => 'Background', 'options' => array_intersect_key([
            'secondary' => 'Light blue', 'primary' => 'Navy', 'gradient-first' => 'Navy gradient', 'gradient-third' => 'Blue gradient',
            'fourth' => 'Peach', 'fifth' => 'Cream', 'third' => 'Pink', 'sixth' => 'Blush', 'none' => 'None (white)',
        ], array_flip($only ?: ['secondary', 'primary', 'gradient-first', 'gradient-third', 'fourth', 'fifth', 'none']))];
        $articleCategories = ['' => 'All', 'gut-health' => 'Gut health', 'our-uniqueness' => 'Our uniqueness', 'news' => 'News'];

        return match ($type) {
            'heroCarousel' => [
                'interval' => ['type' => 'number', 'label' => 'Autoplay delay (ms)'],
                'slides'   => ['type' => 'list', 'label' => 'Slides', 'item' => 'Slide', 'fields' => [
                    'title'        => ['type' => 'text', 'label' => 'Title'],
                    'text'         => ['type' => 'html', 'label' => 'Text', 'rows' => 3],
                    ...$cta(),
                    'new_tab'      => ['type' => 'checkbox', 'label' => 'Open the link in a new tab'],
                    'image'        => ['type' => 'image', 'label' => 'Image (desktop)'],
                    'image_mobile' => ['type' => 'image', 'label' => 'Image (mobile, optional)'],
                    'image_alt'    => ['type' => 'text', 'label' => 'Image description (alt text)'],
                    'shape'        => ['type' => 'select', 'label' => 'Image shape', 'options' => ['blob' => 'Cut to an organic shape', 'plain' => 'As uploaded (already shaped)']],
                ]],
            ],
            'keyFigures' => [
                'items' => ['type' => 'list', 'label' => 'Figures', 'item' => 'Figure', 'fields' => [
                    'icon'  => ['type' => 'image', 'label' => 'Icon'],
                    'value' => ['type' => 'text', 'label' => 'Figure', 'help' => 'e.g. +60'],
                    'label' => ['type' => 'text', 'label' => 'Label'],
                    'ref'   => ['type' => 'text', 'label' => 'Reference number', 'help' => 'Shown as a superscript, matching the references list.'],
                ]],
            ],
            'introBlock' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'text'  => ['type' => 'textarea', 'label' => 'Intro', 'rows' => 3],
                ...$cta(),
            ],
            'productTabs' => [
                'category'  => ['type' => 'select', 'label' => 'Range (parent category)', 'options' => Category::whereNull('parent_id')->pluck('name', 'code')->all()],
                'all_label' => ['type' => 'text', 'label' => '"All" tab label'],
                'layout'    => ['type' => 'select', 'label' => 'Layout', 'options' => ['carousel' => 'Carousel', 'grid' => 'Grid']],
            ],
            'featureBlock' => [
                'title'      => ['type' => 'text', 'label' => 'Title'],
                'text'       => ['type' => 'textarea', 'label' => 'Intro', 'rows' => 3],
                'image'      => ['type' => 'image', 'label' => 'Image'],
                'item_title' => ['type' => 'text', 'label' => 'Item title'],
                'item_text'  => ['type' => 'textarea', 'label' => 'Item text', 'rows' => 4],
                ...$cta(),
            ],
            'iconCarousel' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'items' => ['type' => 'list', 'label' => 'Items', 'item' => 'Item', 'fields' => [
                    'icon'      => ['type' => 'image', 'label' => 'Icon'],
                    'color'     => ['type' => 'select', 'label' => 'Icon background', 'options' => ['magenta' => 'Magenta', 'mint' => 'Mint', 'rose' => 'Rose', 'orange' => 'Orange', 'blue' => 'Blue', 'yellow' => 'Yellow']],
                    'title'     => ['type' => 'text', 'label' => 'Title'],
                    'url'       => $link('Link'),
                    'cta_label' => ['type' => 'text', 'label' => 'Button label', 'help' => 'Empty = "Learn more"'],
                ]],
            ],
            'learnGrid' => [
                'bubbles'   => ['type' => 'checkbox', 'label' => 'Show decorative bubbles (large screens)'],
                'highlight' => ['type' => 'group', 'label' => 'Highlight card (optional)', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title'], ...$cta(), 'image' => ['type' => 'image', 'label' => 'Image'],
                ]],
                'cards' => ['type' => 'list', 'label' => 'Cards', 'item' => 'Card', 'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'text'  => ['type' => 'html', 'label' => 'Text', 'rows' => 3],
                    ...$cta(),
                    'image' => ['type' => 'image', 'label' => 'Image'],
                ]],
            ],
            'tvSpot' => [
                'video_url'        => ['type' => 'video', 'label' => 'Video', 'help' => 'Upload an MP4/WebM, pick one from the library, or paste a YouTube / Vimeo link.'],
                'image'            => ['type' => 'image', 'label' => 'Still image (optional)', 'help' => 'Empty = the first frame of the uploaded video.'],
                'legend'           => ['type' => 'text', 'label' => 'Caption'],
                'transcript_title' => ['type' => 'text', 'label' => 'Transcript title'],
                'transcript'       => ['type' => 'html', 'label' => 'Text transcript', 'rows' => 6],
            ],
            'heroBanner' => [
                'variant'      => ['type' => 'select', 'label' => 'Style', 'options' => ['informative' => 'Informative', 'testimonial' => 'Testimonial', 'highlight' => 'Highlight']],
                'align'        => ['type' => 'select', 'label' => 'Text position', 'options' => ['left' => 'Text left, image right', 'right' => 'Image left, text right']],
                'background'   => $bg(),
                'full_width'   => ['type' => 'checkbox', 'label' => 'Colour spans the full page width'],
                'breadcrumb'   => ['type' => 'text', 'label' => 'Breadcrumb label (optional)'],
                'surtitle'     => ['type' => 'text', 'label' => 'Small title above'],
                'title'        => ['type' => 'text', 'label' => 'Title'],
                'title_tag'    => ['type' => 'select', 'label' => 'Heading level', 'options' => ['h2' => 'H2', 'h1' => 'H1 (page title)', 'h3' => 'H3']],
                'title_style'  => ['type' => 'select', 'label' => 'Title style', 'options' => ['' => 'Display font', 'quote' => 'Large quote', 'h1' => 'Extra large']],
                'text'         => ['type' => 'html', 'label' => 'Text', 'rows' => 4],
                'list_style'   => ['type' => 'select', 'label' => 'Bullet style', 'options' => ['' => 'Squares', 'check' => 'Check marks']],
                ...$cta(),
                'image'        => ['type' => 'image', 'label' => 'Image'],
                'image_shape'  => ['type' => 'select', 'label' => 'Image shape', 'options' => ['blob' => 'Cut to an organic shape', 'square' => 'Square photo', 'plain' => 'As uploaded (already shaped)']],
                'product_text' => ['type' => 'text', 'label' => 'Product attribute that replaces the text (product pages)', 'help' => 'e.g. how_it_works'],
            ],
            'articleCarousel' => [
                'style'    => ['type' => 'select', 'label' => 'Style', 'options' => ['default' => 'Three tiles', 'featured' => 'Large featured stories', 'listing' => 'Topic row']],
                'title'    => ['type' => 'text', 'label' => 'Title'],
                'intro'    => ['type' => 'textarea', 'label' => 'Intro', 'rows' => 2],
                ...$cta(),
                'category' => ['type' => 'select', 'label' => 'Articles from', 'options' => $articleCategories],
                'tag'      => ['type' => 'text', 'label' => 'Only this topic tag (optional)'],
                'featured' => ['type' => 'checkbox', 'label' => 'Only featured articles'],
                'limit'    => ['type' => 'number', 'label' => 'How many'],
            ],
            'articleGrid' => [
                'title'    => ['type' => 'text', 'label' => 'Title'],
                'intro'    => ['type' => 'textarea', 'label' => 'Intro', 'rows' => 2],
                'slugs'    => ['type' => 'tags', 'label' => 'Specific articles (slugs, comma-separated)', 'help' => 'Leave empty to list by category.'],
                'category' => ['type' => 'select', 'label' => 'Articles from', 'options' => $articleCategories],
                'offset'   => ['type' => 'number', 'label' => 'Skip the first'],
                'limit'    => ['type' => 'number', 'label' => 'How many'],
            ],
            'collapsibleContent' => [
                'label_show' => ['type' => 'text', 'label' => 'Label when closed'],
                'label_hide' => ['type' => 'text', 'label' => 'Label when open'],
                'body'       => ['type' => 'html', 'label' => 'Content', 'rows' => 8],
            ],
            'richText' => [
                'title'     => ['type' => 'text', 'label' => 'Title'],
                'title_tag' => ['type' => 'select', 'label' => 'Heading level', 'options' => ['h2' => 'H2', 'h1' => 'H1 (page title)', 'h3' => 'H3']],
                'subtitle'  => ['type' => 'textarea', 'label' => 'Subtitle', 'rows' => 2],
                'html'      => ['type' => 'html', 'label' => 'Text', 'rows' => 8],
                'width'     => ['type' => 'select', 'label' => 'Width', 'options' => ['full' => 'Full', 'md' => 'Medium', '75' => 'Three quarters']],
            ],
            'mosaic' => [
                'html'       => ['type' => 'html', 'label' => 'Main text', 'rows' => 6],
                'image'      => ['type' => 'image', 'label' => 'Image (or use the second text)'],
                'image_side' => ['type' => 'select', 'label' => 'Image side', 'options' => ['right' => 'Right', 'left' => 'Left']],
                'aside'      => ['type' => 'html', 'label' => 'Second text (when no image)', 'rows' => 4],
            ],
            'categoryBanner' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'text'  => ['type' => 'textarea', 'label' => 'Intro', 'rows' => 2],
                'image' => ['type' => 'image', 'label' => 'Image'],
            ],
            'faqList' => [
                'numbered' => ['type' => 'checkbox', 'label' => 'Number the questions'],
                'group'    => ['type' => 'text', 'label' => 'Only this Q&A group (optional)'],
            ],
            'ctaBox' => [
                'background' => $bg(['secondary', 'fourth', 'fifth', 'third']),
                'title'      => ['type' => 'text', 'label' => 'Title'],
                'text'       => ['type' => 'textarea', 'label' => 'Text', 'rows' => 2],
                'buttons'    => ['type' => 'list', 'label' => 'Buttons', 'item' => 'Button', 'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label'],
                    'url'   => $link('Link'),
                    'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['pink' => 'Pink', 'navy' => 'Navy', 'outline' => 'Outline']],
                ]],
            ],
            'timeline' => [
                'background' => $bg(['fourth', 'fifth', 'secondary']),
                'title'      => ['type' => 'text', 'label' => 'Title'],
                'items'      => ['type' => 'list', 'label' => 'Milestones', 'item' => 'Milestone', 'fields' => [
                    'year'  => ['type' => 'text', 'label' => 'Year'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'text'  => ['type' => 'textarea', 'label' => 'Text', 'rows' => 2],
                ]],
            ],
            'iconGrid' => [
                'background' => $bg(['primary', 'fourth', 'fifth', 'secondary', 'sixth', 'none']),
                'title'      => ['type' => 'text', 'label' => 'Title'],
                'intro'      => ['type' => 'textarea', 'label' => 'Intro', 'rows' => 2],
                'carousel'   => ['type' => 'checkbox', 'label' => 'Swipeable on mobile'],
                'items'      => ['type' => 'list', 'label' => 'Items', 'item' => 'Item', 'fields' => [
                    'icon'  => ['type' => 'image', 'label' => 'Icon'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'text'  => ['type' => 'html', 'label' => 'Text', 'rows' => 2],
                ]],
            ],
            'breadcrumbs' => ['title' => ['type' => 'text', 'label' => 'Current page label']],
            'topicLinks'  => [
                'label'  => ['type' => 'text', 'label' => 'Label'],
                'topics' => ['type' => 'tags', 'label' => 'Topics (comma-separated)', 'help' => 'Article topic tags, e.g. Seasonal, Nutrition'],
            ],
            default => [],
        };
    }
}

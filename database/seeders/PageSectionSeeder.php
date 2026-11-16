<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;

/**
 * Page composition: ordered, typed components per page (the Page Designer
 * model). Each `type` renders resources/views/sections/{kebab-type}.blade.php.
 *
 * Links are stored without the locale ("our-products", "faq.html") and
 * resolved with site_url(). Copy may use {brand}; product-page sections may
 * also use {product}. Images point at placeholders in public/img — upload
 * real artwork in the media library and paste its path into the section.
 *
 * Re-running this seeder rebuilds the pages listed below.
 */
class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $pageKey => $sections) {
            PageSection::where('page_key', $pageKey)->delete();

            foreach ($sections as $i => $section) {
                PageSection::create([
                    'page_key'   => $pageKey,
                    'region'     => $section['region'] ?? 'main',
                    'type'       => $section['type'],
                    'sort_order' => ($i + 1) * 10,
                    'payload'    => $section['payload'],
                    'is_visible' => $section['visible'] ?? true,
                ]);
            }
        }
    }

    private function pages(): array
    {
        return [
            'home'                  => $this->home(),
            'our-products'          => $this->range(),
            'pdp'                   => $this->productPage(),
            'gut-health'            => $this->gutHealth(),
            'our-uniqueness'        => $this->uniqueness(),
            'our-news'              => $this->news(),
            'about-us'              => $this->aboutUs(),
            'faq'                   => $this->faq(),
            'gut-health-quiz-intro' => $this->quiz(),
        ];
    }

    // ------------------------------------------------------------------ home

    private function home(): array
    {
        return [
            ['type' => 'heroCarousel', 'payload' => [
                'interval' => 6000,
                'slides'   => [
                    [
                        'title'     => 'Protection that starts on the inside',
                        'text'      => '<p>{brand}, a probiotic yeast for everyday gut comfort.</p><p>It helps protect and restore the balance of your gut microbiota.<sup>1</sup></p>',
                        'cta_label' => 'Explore now',
                        'cta_url'   => 'our-products',
                        'image'     => $hero = $this->img('photo-hero-home-dad-and-daughter-desktop', 'img/placeholders/hero-1.svg'),
                        'shape'     => $this->shape($hero),
                    ],
                    [
                        'title'     => 'Gut health made simple',
                        'text'      => '<p>Clear, practical advice to understand your gut microbiota and take care of it every day.</p>',
                        'cta_label' => 'Read our advice',
                        'cta_url'   => 'gut-health.html',
                        'image'     => $this->img('gut-microbiota-1', 'img/placeholders/hero-2.svg'),
                    ],
                ],
            ]],
            ['type' => 'keyFigures', 'payload' => [
                'items' => [
                    ['icon' => 'img/icons/studies.svg',   'value' => '135',   'label' => 'clinical studies',    'ref' => '2'],
                    ['icon' => 'img/icons/expertise.svg', 'value' => '+60',   'label' => 'years of expertise',  'ref' => '3'],
                    ['icon' => 'img/icons/patients.svg',  'value' => '+513M', 'label' => 'patients since 1980', 'ref' => '4'],
                ],
            ]],
            ['type' => 'introBlock', 'payload' => [
                'title'     => 'A probiotic yeast for the whole family',
                'text'      => '{brand} helps support gut health at every age by protecting and restoring the gut microbiota.<sup>1</sup>',
                'cta_label' => 'See all products',
                'cta_url'   => 'our-products',
            ]],
            ['type' => 'productTabs', 'payload' => [
                'category'  => 'our-products',
                'all_label' => 'All the products',
                'layout'    => 'carousel',
            ]],
            ['type' => 'featureBlock', 'payload' => $this->expertise()],
            ['type' => 'iconCarousel', 'payload' => $this->indications()],
            ['type' => 'learnGrid', 'payload' => [
                'bubbles'   => true,
                'highlight' => [
                    'title'     => 'Get to know your gut microbiota and how to care for it',
                    'cta_label' => 'Learn more',
                    'cta_url'   => 'gut-health.html',
                    'image'     => $this->img('learn-big', 'img/placeholders/highlight.svg'),
                ],
                'cards' => $this->learnCards(),
            ]],
            ['type' => 'tvSpot', 'payload' => [
                'image'            => $this->img('tv-spot-still', $this->img('cddf371d-8b07168b', '') ? '' : 'img/placeholders/tv-spot.svg'),
                'video_url'        => $this->img('cddf371d-8b07168b', ''),
                'legend'           => 'Our TV spot',
                'transcript_title' => 'Video text transcript',
                'transcript'       => '<p>Add the text transcript of your video here, so the film is accessible to everyone.</p>',
            ]],
            ['type' => 'heroBanner', 'payload' => [
                'variant'     => 'informative',
                'align'       => 'right',
                'background'  => 'secondary',
                'surtitle'    => 'Questions you might have',
                'title'       => 'How do I choose the right probiotic?',
                'title_style' => 'quote',
                'cta_label'   => 'Discover more',
                'cta_url'     => 'faq.html',
                'image'       => 'img/icons/checks.svg',
                'image_shape' => 'blob',
            ]],
            ['type' => 'heroBanner', 'payload' => [
                'variant'     => 'testimonial',
                'align'       => 'right',
                'background'  => 'secondary',
                'surtitle'    => 'Dr. [Name], general practitioner',
                'title'       => '“Add a short, approved quote from a healthcare professional here.”',
                'title_style' => 'quote',
                'cta_label'   => 'Discover more',
                'cta_url'     => 'our-products',
                'image'       => $this->img('testimonials-image', 'img/placeholders/testimonial.svg'),
            ]],
            ['type' => 'articleCarousel', 'payload' => [
                'title'     => 'What\'s new in gut health?',
                'intro'     => 'Fresh tips and discoveries for keeping your gut microbiota healthy.',
                'cta_label' => 'Our latest news',
                'cta_url'   => 'all-news.html',
                'category'  => 'news',
                'limit'     => 6,
            ]],
            ['type' => 'heroBanner', 'payload' => [
                'variant'     => 'informative',
                'align'       => 'left',
                'background'  => 'gradient-first',
                'full_width'  => true,
                'title'       => 'About us',
                'text'        => '<p>Meet the company behind {brand}: its history, its research and its commitment to health.</p>',
                'cta_label'   => 'Learn about our story',
                'cta_url'     => 'about-us.html',
                'image'       => $about = $this->img('about-us', 'img/placeholders/about.svg'),
                'image_shape' => $this->shape($about),
            ]],
            ['type' => 'collapsibleContent', 'payload' => $this->references(4)],
        ];
    }

    // ------------------------------------------------------- range (PLP)

    private function range(): array
    {
        return [
            ['type' => 'categoryBanner', 'payload' => [
                'title' => 'Our products',
                'text'  => 'Find the {brand} format that suits you and your family.',
                'image' => is_file(public_path('uploads/products/bioflor-250mg-sachets.webp')) ? 'products/bioflor-250mg-sachets.webp' : 'img/placeholders/category-banner.svg',
            ]],
            ['type' => 'introBlock', 'payload' => [
                'title' => 'One probiotic yeast, two formats',
                'text'  => 'Sachets or capsules — choose the one that fits your routine.',
            ]],
            ['type' => 'productTabs', 'payload' => [
                'category'  => 'our-products',
                'all_label' => 'All the products',
                'layout'    => 'grid',
            ]],
            ['type' => 'heroBanner', 'payload' => [
                'variant'     => 'informative',
                'align'       => 'left',
                'background'  => 'secondary',
                'title'       => 'Which format is right for me?',
                'text'        => '<ul><li>Sachets mix easily into a little water.</li><li>Capsules are practical at home or on the go.</li></ul>',
                'list_style'  => 'check',
                'cta_label'   => 'Ask our Q&A',
                'cta_url'     => 'faq.html',
                'image'       => 'img/icons/checks.svg',
                'image_shape' => 'blob',
            ]],
            ['type' => 'iconCarousel', 'payload' => $this->indications()],
            ['type' => 'learnGrid', 'payload' => ['cards' => $this->learnCards()]],
            ['type' => 'collapsibleContent', 'payload' => $this->references(2)],
        ];
    }

    // ---------------------------------------------------------- product page

    private function productPage(): array
    {
        return [
            ['region' => 'after_main', 'type' => 'heroBanner', 'payload' => [
                'variant'      => 'informative',
                'align'        => 'left',
                'background'   => 'gradient-third',
                'full_width'   => true,
                'title'        => 'How {product} works',
                'text'         => '<p>{product} contains <em>Saccharomyces boulardii</em> CNCM I-745, a probiotic yeast that helps restore the balance of the gut microbiota.<sup>1</sup></p>',
                'product_text' => 'how_it_works',
                'cta_label'    => 'Discover more',
                'cta_url'      => 'a-unique-probiotic-yeast-strain.html',
                'image'        => $strain = $this->img('bioflor-sb', 'img/placeholders/how-it-works.svg'),
                'image_shape'  => $this->shape($strain),
            ]],
            ['region' => 'middle', 'type' => 'iconGrid', 'payload' => [
                'background' => 'fourth',
                'title'      => 'Why choose a probiotic yeast?',
                'intro'      => 'Four reasons to choose <em>Saccharomyces boulardii</em> CNCM I-745.',
                'carousel'   => true,
                'items'      => [
                    ['icon' => 'img/icons/efficacy.svg',   'title' => 'Efficacy',   'text' => 'Studied in many clinical trials.<sup>2</sup>'],
                    ['icon' => 'img/icons/safety.svg',     'title' => 'Safety',     'text' => 'Used by millions of people around the world.<sup>3</sup>'],
                    ['icon' => 'img/icons/quality.svg',    'title' => 'Quality',    'text' => 'Made to pharmaceutical quality standards.'],
                    ['icon' => 'img/icons/validation.svg', 'title' => 'Recognised', 'text' => 'Cited in international recommendations.<sup>4</sup>'],
                ],
            ]],
            ['region' => 'bottom', 'type' => 'introBlock', 'payload' => [
                'title'     => 'Discover our other products',
                'text'      => 'The same probiotic yeast in another format for your routine.',
                'cta_label' => 'See all products',
                'cta_url'   => 'our-products',
            ]],
            ['region' => 'bottom', 'type' => 'productTabs', 'payload' => ['category' => 'our-products', 'all_label' => 'All the products']],
            ['region' => 'bottom', 'type' => 'articleCarousel', 'payload' => [
                'title'     => 'What\'s new in gut health?',
                'intro'     => 'Fresh tips and discoveries for keeping your gut microbiota healthy.',
                'cta_label' => 'Our latest news',
                'cta_url'   => 'all-news.html',
                'category'  => 'news',
                'limit'     => 6,
            ]],
        ];
    }

    // -------------------------------------------------------------- hubs

    private function gutHealth(): array
    {
        return [
            ['type' => 'breadcrumbs', 'payload' => ['title' => 'Gut health']],
            ['type' => 'richText', 'payload' => [
                'title'     => 'Gut health',
                'title_tag' => 'h1',
                'subtitle'  => 'Your gut microbiota does much more than help you digest. Learn how it works and how to look after it.',
                'width'     => 'md',
            ]],
            ['type' => 'heroBanner', 'payload' => [
                'variant'    => 'highlight',
                'align'      => 'left',
                'background' => 'fifth',
                'container'  => true,
                'title'      => 'Your gut microbiota in a nutshell',
                'text'       => '<p>Trillions of microorganisms live in your gut. In balance, they help digestion, defend against unwanted microbes and talk to your immune system.</p>',
                'cta_label'  => 'Understand dysbiosis',
                'cta_url'    => 'understanding-dysbiosis.html',
                'image'      => $this->img('support-health-gut-microbiota', 'img/placeholders/hub-gut-health.svg'),
            ]],
            ['type' => 'articleGrid', 'payload' => [
                'title'    => 'Understand your gut',
                'intro'    => 'The basics of the gut microbiota, at every age.',
                'slugs'    => ['support-health-gut-microbiota', 'gut-microbiota-children', 'understanding-dysbiosis'],
            ]],
            ['type' => 'articleGrid', 'payload' => [
                'title'    => 'Symptoms and advice',
                'intro'    => 'What to do when your gut is out of balance.',
                'slugs'    => ['living-with-ibs', 'watery-diarrhea', 'yeast-vs-bacterial-probiotics'],
            ]],
            // Quiz banner: hidden until a real quiz is built.
            ['visible' => false, 'type' => 'heroBanner', 'payload' => [
                'variant'    => 'highlight',
                'align'      => 'left',
                'background' => 'fifth',
                'container'  => true,
                'title'      => 'How well do you know your gut?',
                'cta_label'  => 'Take the quiz',
                'cta_url'    => 'gut-health-quiz-intro.html',
                'image'      => $this->img('learn-big', 'img/placeholders/highlight.svg'),
            ]],
        ];
    }

    private function uniqueness(): array
    {
        return [
            ['type' => 'breadcrumbs', 'payload' => ['title' => 'Our uniqueness']],
            ['type' => 'richText', 'payload' => [
                'title'     => 'Our uniqueness',
                'title_tag' => 'h1',
                'subtitle'  => 'A probiotic yeast, studied for decades, that works differently from bacterial probiotics.',
                'width'     => 'md',
            ]],
            ['type' => 'iconGrid', 'payload' => [
                'background' => 'primary',
                'title'      => 'What makes our yeast different',
                'items'      => [
                    ['icon' => 'img/icons/yeast.svg',      'title' => 'A yeast, not a bacterium', 'text' => 'A different kind of probiotic, with its own way of working.<sup>1</sup>'],
                    ['icon' => 'img/icons/studies.svg',    'title' => 'A well-studied strain',    'text' => 'Backed by a large body of clinical research.<sup>2</sup>'],
                    ['icon' => 'img/icons/quality.svg',    'title' => 'Pharmaceutical quality',   'text' => 'Every batch produced to the same specification.'],
                    ['icon' => 'img/icons/expertise.svg',  'title' => 'Decades of use',           'text' => 'Trusted by families in many countries.<sup>3</sup>'],
                ],
            ]],
            ['type' => 'mosaic', 'payload' => [
                'image'      => $this->img('gut-flora', 'img/placeholders/learn-2.svg'),
                'image_side' => 'right',
                'html'       => '<h2>A unique probiotic yeast strain</h2><p><em>Saccharomyces boulardii</em> CNCM I-745 is a specific strain. Its properties are tied to that strain, which is why the full name matters.</p><p><a class="btn btn--pink" href="'.'a-unique-probiotic-yeast-strain.html">Read more</a></p>',
            ]],
            ['type' => 'articleGrid', 'payload' => [
                'title'    => 'How {brand} helps',
                'intro'    => 'Situations where a probiotic yeast can support your gut.',
                'category' => 'our-uniqueness',
                'limit'    => 6,
            ]],
            ['type' => 'collapsibleContent', 'payload' => $this->references(3)],
        ];
    }

    private function news(): array
    {
        $topics = [
            'Seasonal'                => 'Advice for travel, holidays and the changing seasons.',
            'Nutrition'               => 'Food ideas that are kind to your gut microbiota.',
            'Lifestyle'               => 'Sleep, movement and everyday habits.',
            'Innovation and research' => 'What new research tells us about the microbiome.',
            'Company news'            => 'News from the {brand} team.',
            'Product news'            => 'New formats and product updates.',
        ];

        $rows = [
            ['type' => 'breadcrumbs', 'payload' => ['title' => 'Our news']],
            ['type' => 'richText', 'payload' => [
                'title'     => 'Our news',
                'title_tag' => 'h1',
                'subtitle'  => 'Seasonal advice, nutrition ideas and research highlights about gut health.',
                'width'     => 'md',
            ]],
            ['type' => 'topicLinks', 'payload' => ['label' => 'Browse by topic', 'topics' => array_keys($topics)]],
            ['type' => 'articleCarousel', 'payload' => ['style' => 'featured', 'featured' => true, 'limit' => 6]],
            ['type' => 'richText', 'payload' => ['title' => 'Latest by topic', 'title_tag' => 'h2']],
        ];

        foreach ($topics as $tag => $description) {
            $rows[] = ['type' => 'articleCarousel', 'payload' => [
                'style'       => 'listing',
                'title'       => $tag,
                'intro'       => $description,
                'tag'         => $tag,
                'limit'       => 4,
                'cta_label'   => 'See all',
                'cta_url'     => 'all-news.html?tag='.rawurlencode($tag),
            ]];
        }

        return $rows;
    }

    // ------------------------------------------------------------- about

    /** About us, mission and history: the user's "FQA Website Bioflor" deck (2026-10-28). */
    private function aboutUs(): array
    {
        return [
            ['type' => 'heroBanner', 'payload' => [
                'variant'     => 'informative',
                'align'       => 'left',
                'background'  => 'gradient-first',
                'full_width'  => true,
                'breadcrumb'  => 'About us',
                'title'       => 'About us',
                'title_tag'   => 'h1',
                'text'        => '<p>Bioflor® is a probiotic brand developed by Biocodex, a French pharmaceutical company specializing in microbiota health.</p>'
                    .'<p>Bioflor® contains <em>Saccharomyces boulardii</em> CNCM I-745, a well-researched probiotic yeast.</p>'
                    .'<p>In Cambodia, Bioflor® is distributed by PPM, committed to improving access to trusted healthcare products and supporting digestive health.</p>',
                'image'       => $about = $this->img('about-us', 'img/placeholders/about.svg'),
                'image_shape' => $this->shape($about),
            ]],
            ['type' => 'mosaic', 'payload' => [
                'html'  => '<h2>Our mission</h2>',
                'aside' => '<p class="text-intro">Our mission is to improve digestive health by providing trusted, science-backed probiotic solutions.</p>'
                    .'<p>Through Bioflor® containing <em>Saccharomyces boulardii</em> CNCM I-745, we are committed to supporting gut health, sharing reliable health information, and improving access to quality healthcare in Cambodia.</p>',
            ]],
            ['type' => 'timeline', 'payload' => [
                'background' => 'fourth',
                'title'      => 'Our history',
                'items'      => [
                    ['year' => '1923', 'title' => 'The discovery',                  'text' => 'Henri Boulard discovered <em>Saccharomyces boulardii</em> in Indochina.'],
                    ['year' => '1953', 'title' => 'Biocodex founded',               'text' => 'Biocodex was established in France to develop and commercialize the probiotic yeast.'],
                    ['year' => '1961', 'title' => 'Medical milestone',              'text' => '<em>Saccharomyces boulardii</em> CNCM I-745 was officially registered as a medicinal product.'],
                    ['year' => '2026', 'title' => 'Bioflor® relaunch in Cambodia', 'text' => 'Bioflor® is relaunched in Cambodia through a new partnership with PPM, marked by a relaunch event in October 2026.'],
                ],
            ]],
            // Not in the approved About page content: kept, hidden, for later use.
            ['visible' => false, 'type' => 'heroBanner', 'payload' => [
                'variant'    => 'highlight',
                'align'      => 'left',
                'background' => 'fifth',
                'container'  => true,
                'title'      => 'Research at the heart of what we do',
                'text'       => '<p>Our teams work with doctors and scientists to keep improving our understanding of the gut microbiota.</p>',
                'cta_label'  => 'Our uniqueness',
                'cta_url'    => 'our-uniqueness.html',
                'image'      => $this->img('gut-microbiota-2', 'img/placeholders/learn-1.svg'),
            ]],
            ['visible' => false, 'type' => 'iconGrid', 'payload' => [
                'background' => 'fifth',
                'title'      => 'Our values',
                'items'      => [
                    ['icon' => 'img/icons/efficacy.svg',   'title' => 'Science',        'text' => 'Evidence first, in every product and every message.'],
                    ['icon' => 'img/icons/quality.svg',    'title' => 'Quality',        'text' => 'The same high standard, batch after batch.'],
                    ['icon' => 'img/icons/patients.svg',   'title' => 'Care',           'text' => 'Products and advice for every age.'],
                    ['icon' => 'img/icons/validation.svg', 'title' => 'Responsibility', 'text' => 'Acting for patients, partners and the planet.'],
                ],
            ]],
        ];
    }

    // ---------------------------------------------------------------- Q&A

    private function faq(): array
    {
        return [
            ['type' => 'breadcrumbs', 'payload' => ['title' => 'Q&A']],
            ['type' => 'richText', 'payload' => [
                'title'     => 'Questions & answers',
                'title_tag' => 'h1',
                'subtitle'  => 'Answers to common questions about probiotics and the {brand} range.',
                'width'     => 'md',
            ]],
            ['type' => 'faqList', 'payload' => ['numbered' => true]],
            ['type' => 'ctaBox', 'payload' => [
                'background' => 'secondary',
                'title'      => 'Didn\'t find your answer?',
                'text'       => 'Our team is happy to help. You can also ask your pharmacist.',
                'buttons'    => [
                    ['label' => 'Contact us',   'url' => 'contact-us.html', 'style' => 'pink'],
                    ['label' => 'Where to buy', 'url' => 'find-in-store',   'style' => 'outline'],
                ],
            ]],
            ['type' => 'collapsibleContent', 'payload' => $this->references(2)],
        ];
    }

    /** Quiz intro: hidden (the page answers 404) until a real quiz is built. */
    private function quiz(): array
    {
        return [
            ['visible' => false, 'type' => 'mosaic', 'payload' => [
                'image'      => $this->img('learn-big', 'img/placeholders/quiz.svg'),
                'image_side' => 'right',
                'html'       => '<h1 class="h1-sm">How well do you know your gut?</h1><p class="text-intro">Five quick questions to test what you know about your gut microbiota — and learn something new along the way.</p><p><a class="btn btn--pink" href="gut-health-quiz.html">Start the quiz</a></p>',
            ]],
            ['visible' => false, 'type' => 'collapsibleContent', 'payload' => $this->references(1)],
        ];
    }

    // ------------------------------------------------------------- shared

    private function expertise(): array
    {
        return [
            'title'      => 'Our expertise in action',
            'text'       => 'See how {brand} supports digestive health in different situations.',
            'image'      => $this->img('indications', 'img/placeholders/feature.svg'),
            'item_title' => 'Managing acute diarrhoea',
            'item_text'  => 'Acute diarrhoea is common and usually short-lived. Knowing how to rehydrate and when to ask for advice helps you care for yourself and your family.',
            'cta_label'  => 'Learn more',
            'cta_url'    => 'how-to-manage-acute-diarrhea.html',
        ];
    }

    /** The approved indications (Bioflor product list for the website, 2026). */
    private function indications(): array
    {
        return [
            'title' => 'When can {brand} help?',
            'items' => self::INDICATIONS,
        ];
    }

    public const INDICATIONS = [
        ['icon' => 'img/icons/diarrhea.svg',     'color' => 'magenta', 'title' => 'Acute infectious diarrhoea in adults and children',           'url' => 'how-to-manage-acute-diarrhea.html'],
        ['icon' => 'img/icons/antibiotic.svg',   'color' => 'mint',    'title' => 'Antibiotic-associated colitis and diarrhoea',                 'url' => 'preventing-antibiotic-associated-diarrhea.html'],
        ['icon' => 'img/icons/c-difficile.svg',  'color' => 'blue',    'title' => 'Preventing <em>Clostridium difficile</em> recurrence, with vancomycin/<wbr>metronidazole', 'url' => ''],
        ['icon' => 'img/icons/tube-feeding.svg', 'color' => 'yellow',  'title' => 'Tube-feeding associated diarrhoea',                           'url' => ''],
        ['icon' => 'img/icons/ibs.svg',          'color' => 'orange',  'title' => 'Irritable bowel syndrome',                                    'url' => 'supporting-ibs-symptoms.html'],
    ];

    private function learnCards(): array
    {
        return [
            [
                'title'     => 'Your gut microbiota, your health',
                'text'      => 'How the microbes in your gut influence digestion, immunity and more.',
                'cta_label' => 'Explore',
                'cta_url'   => 'gut-health.html',
                'image'     => $this->img('learn-small-1', 'img/placeholders/learn-1.svg'),
            ],
            [
                'title'     => 'Meet the yeast behind {brand}',
                'text'      => 'Discover <em>Saccharomyces boulardii</em> CNCM I-745 and how it helps keep your gut microbiota in balance.',
                'cta_label' => 'Discover',
                'cta_url'   => 'our-uniqueness.html',
                'image'     => $this->img('learn-small-2', 'img/placeholders/learn-2.svg'),
            ],
        ];
    }

    /** Library image if it was imported from the Product folder, else the placeholder. */
    private function img(string $name, string $fallback): string
    {
        return MediaSeeder::path($name, $fallback);
    }

    /** Imported images are already cut to an organic shape; placeholders get the CSS blob mask. */
    private function shape(string $path): string
    {
        return str_starts_with($path, 'library/') ? 'plain' : 'blob';
    }

    private function references(int $count): array
    {
        $body = '';

        for ($i = 1; $i <= $count; $i++) {
            $body .= "<p>({$i}) Add the approved reference for claim {$i} on this page.</p>";
        }

        return ['label_show' => 'See all references', 'label_hide' => 'Hide references', 'body' => $body];
    }
}

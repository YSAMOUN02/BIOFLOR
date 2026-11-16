<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Editorial skeleton mirroring the storefront's three content hubs
 * (gut health / our uniqueness / news). Body copy is placeholder text.
 */
class ArticleSeeder extends Seeder
{
    /** Short storefront slugs (/en/{slug}.html) for the articles below. */
    private const SLUGS = [
        'how-to-support-a-healthy-gut-microbiota'                => 'support-health-gut-microbiota',
        'your-childs-gut-health-building-strong-foundations-early' => 'gut-microbiota-children',
        'understanding-dysbiosis-a-guide-to-the-gut-microbiota'  => 'understanding-dysbiosis',
        'can-irritable-bowel-syndrome-be-cured'                  => 'living-with-ibs',
        'watery-diarrhoea'                                       => 'watery-diarrhea',
        'yeast-probiotic-vs-bacterial-probiotic'                 => 'yeast-vs-bacterial-probiotics',
        'quiz-learn-more-about-your-gut-microbiota'              => 'gut-health-quiz',
        'how-it-works-in-acute-diarrhoea'                        => 'how-to-manage-acute-diarrhea',
        'antibiotic-associated-diarrhoea-rationale'              => 'preventing-antibiotic-associated-diarrhea',
        'travellers-diarrhoea-preparing-before-you-go'           => 'preventing-travelers-diarrhea',
        'supporting-ibs-symptoms'                                => 'supporting-ibs-symptoms',
        'the-story-behind-our-probiotic-yeast'                   => 'the-story-behind-saccharomyces-boulardii',
        'a-unique-probiotic-yeast-strain'                        => 'a-unique-probiotic-yeast-strain',
        'a-refreshed-look-for-our-global-site'                   => 'a-refreshed-look-for-our-site',
        'notes-from-this-years-microbiota-congress'              => 'notes-from-the-microbiota-congress',
        'new-paediatric-drops-presentation'                      => 'new-paediatric-drops-presentation',
    ];

    /** News topic (tile tag / "all news" filter) per news slug. */
    private const NEWS_TAGS = [
        'a-refreshed-look-for-our-site'       => 'Company news',
        'notes-from-the-microbiota-congress'  => 'Innovation and research',
        'new-paediatric-drops-presentation'   => 'Product news',
        'travelling-with-a-sensitive-gut'     => 'Seasonal',
        'fibre-on-your-plate'                 => 'Nutrition',
        'sleep-and-your-gut'                  => 'Lifestyle',
    ];

    /** Media-library image (base name) per article slug. */
    private const IMAGES = [
        'support-health-gut-microbiota'             => 'support-health-gut-microbiota',
        'gut-microbiota-children'                   => 'photo-gut-health-microbiota-and-children',
        'understanding-dysbiosis'                   => 'gut-flora',
        'living-with-ibs'                           => 'photo-gut-health-gut-protection',
        'watery-diarrhea'                           => 'photo-gut-health-antibiotics-and-child',
        'yeast-vs-bacterial-probiotics'             => 'photo-gut-health-what-are-probiotics',
        'gut-health-quiz'                           => 'learn-big',
        'how-to-manage-acute-diarrhea'              => 'indications',
        'preventing-antibiotic-associated-diarrhea' => 'photo-gut-health-antibiotics-and-probiotics',
        'preventing-travelers-diarrhea'             => 'gut-microbiota-1',
        'supporting-ibs-symptoms'                   => 'learn-small-1',
        'the-story-behind-saccharomyces-boulardii'  => 'bioflor-sb',
        'a-unique-probiotic-yeast-strain'           => 'learn-small-2',
        'a-refreshed-look-for-our-site'             => 'photo-gut-health-choosing-probiotic',
        'notes-from-the-microbiota-congress'        => 'learn-small-2',
        'new-paediatric-drops-presentation'         => 'photo-gut-health-first-1000-days',
        'travelling-with-a-sensitive-gut'           => 'gut-microbiota-1',
        'fibre-on-your-plate'                       => 'photo-gut-health-probiotics-benefits',
        'sleep-and-your-gut'                        => 'gut-microbiota-2',
    ];

    public function run(): void
    {
        $articles = array_merge($this->articles(), $this->newsArticles());
        $slugs    = [];

        foreach ($articles as $i => $article) {
            $article['slug']  = self::SLUGS[$article['slug']] ?? $article['slug'];
            $article['title'] = str_replace(['Enterol', 'ENTEROL'], [brand(), mb_strtoupper(brand())], $article['title']);

            if ($article['category'] === 'news') {
                $article['eyebrow'] = self::NEWS_TAGS[$article['slug']] ?? $article['eyebrow'];
            }

            // Photo from the media library (Product folder) when there is one, else placeholder art.
            $article['image']      = MediaSeeder::path(self::IMAGES[$article['slug']] ?? '', 'img/placeholders/article-'.(($i % 6) + 1).'.svg');
            $article['references'] = $article['references']
                ?? '<p>(1) Add the approved reference supporting the claims in this article.</p>';

            $draft = $article['draft'] ?? false;
            unset($article['draft']);

            Article::updateOrCreate(
                ['slug' => $article['slug']],
                $article + [
                    'published_at' => $draft ? null : now()->startOfDay()->subDays(($i + 1) * 4),
                    'read_minutes' => 3 + ($i % 5),
                ],
            );

            $slugs[] = $article['slug'];
        }

        // Rows left over from earlier slugs.
        Article::whereNotIn('slug', $slugs)->delete();
    }

    /** Extra news items so each topic in "all news" has something to show. */
    private function newsArticles(): array
    {
        return [
            [
                'title'       => 'Travelling with a sensitive gut',
                'slug'        => 'travelling-with-a-sensitive-gut',
                'category'    => 'news',
                'eyebrow'     => 'Seasonal',
                'is_featured' => true,
                'excerpt'     => 'Simple preparation for trips: water, food hygiene and what to pack in your bag.',
                'body'        => $this->body([
                    'Before you leave' => [
                        'Pack oral rehydration salts, keep a bottle of safe water within reach and plan how you will wash your hands on the go.',
                    ],
                    'On the way' => [
                        '<ul><li>Choose freshly cooked food served hot.</li><li>Peel fruit yourself.</li><li>Ask a pharmacist for advice that suits your destination.</li></ul>',
                    ],
                ]),
            ],
            [
                'title'       => 'Fibre on your plate: an easy weekly plan',
                'slug'        => 'fibre-on-your-plate',
                'category'    => 'news',
                'eyebrow'     => 'Nutrition',
                'is_featured' => true,
                'excerpt'     => 'Small swaps that add variety of plants across a normal week.',
                'body'        => $this->body([
                    'Think variety' => [
                        'Aim for many different plants over the week — grains, pulses, vegetables, fruit, nuts and seeds all count.',
                    ],
                    'Swaps that stick' => [
                        '<ul><li>Add a handful of beans to soups and salads.</li><li>Choose wholegrain rice or noodles some days.</li><li>Keep fruit visible on the table.</li></ul>',
                    ],
                ]),
            ],
            [
                'title'       => 'Sleep and your gut: why rhythm matters',
                'slug'        => 'sleep-and-your-gut',
                'category'    => 'news',
                'eyebrow'     => 'Lifestyle',
                'is_featured' => true,
                'excerpt'     => 'Regular sleep supports the daily rhythm your digestion follows.',
                'body'        => $this->body([
                    'A daily rhythm' => [
                        'Digestion follows a daily cycle. Keeping similar sleep and meal times helps it stay regular.',
                    ],
                    'Small steps' => [
                        'Try a consistent bedtime, a lighter late meal and some daylight in the morning.',
                    ],
                ]),
            ],
        ];
    }

    private function articles(): array
    {
        return [
            // ---------------- gut health ----------------
            [
                'title'    => 'How to support a healthy gut microbiota',
                'slug'     => 'how-to-support-a-healthy-gut-microbiota',
                'category' => 'gut-health',
                'eyebrow'  => 'Gut health',
                'image'    => 'flora',
                'excerpt'  => 'Diet, sleep, movement and stress all leave a mark on the community of microbes living in your gut. Here is what the evidence supports.',
                'body'     => $this->body([
                    'What the microbiota actually is' => [
                        'Your large intestine hosts a dense community of bacteria, yeasts and other microorganisms. Collectively they help break down fibre, produce short-chain fatty acids, and interact with the cells lining your gut wall.',
                        'No two people carry the same mix, and yours shifts with what you eat, how you sleep, the medicines you take and how much you move.',
                    ],
                    'Four habits with decent evidence behind them' => [
                        '<ul><li><strong>Eat a wider range of plants.</strong> Variety of fibre matters more than volume of any single source.</li><li><strong>Include fermented foods.</strong> Yoghurt, kefir, sauerkraut and kimchi introduce live cultures alongside the fibre you already eat.</li><li><strong>Protect your sleep window.</strong> Irregular sleep disturbs the daily rhythm your gut works to.</li><li><strong>Move most days.</strong> Moderate, regular activity is associated with a more diverse microbiota.</li></ul>',
                    ],
                    'When to ask for help' => [
                        'Occasional bloating or a change in rhythm is normal. Persistent pain, blood, unexplained weight loss or symptoms that wake you at night are not — those warrant a conversation with your doctor rather than a supplement.',
                    ],
                ]),
            ],
            [
                'title'    => 'Your child\'s gut health: building strong foundations early',
                'slug'     => 'your-childs-gut-health-building-strong-foundations-early',
                'category' => 'gut-health',
                'eyebrow'  => 'Gut health',
                'image'    => 'family',
                'excerpt'  => 'The first years of life shape a child\'s microbiota. What helps, what to worry about, and when to call the doctor.',
                'body'     => $this->body([
                    'The first thousand days' => [
                        'A newborn\'s gut is colonised rapidly after birth, and the mix keeps changing through weaning and into early childhood. Feeding, antibiotics and everyday contact with the world all contribute.',
                    ],
                    'Practical points for parents' => [
                        '<ul><li>Introduce a broad range of foods as advised by your paediatrician.</li><li>Keep oral rehydration solution at home — for a small child, fluid loss matters more than the cause.</li><li>Never give an adult dose or format to an infant.</li></ul>',
                    ],
                    'Signs that need medical attention' => [
                        'In babies and toddlers, seek advice quickly for persistent vomiting, dry nappies, drowsiness, a sunken fontanelle, blood in the stool or a fever that does not settle.',
                    ],
                ]),
            ],
            [
                'title'    => 'Understanding dysbiosis: a guide to the gut microbiota',
                'slug'     => 'understanding-dysbiosis-a-guide-to-the-gut-microbiota',
                'category' => 'gut-health',
                'eyebrow'  => 'Gut health',
                'image'    => 'gut',
                'excerpt'  => 'Dysbiosis describes an imbalance in the gut community. It is a useful shorthand — and an easy term to over-interpret.',
                'body'     => $this->body([
                    'What the word means' => [
                        'Dysbiosis is a shift away from the balance that normally keeps your gut working comfortably: fewer of the organisms that help, more of those that do not, or simply a loss of overall diversity.',
                        'It is a description, not a diagnosis. There is no single test that returns a yes or no.',
                    ],
                    'Common triggers' => [
                        '<ul><li>A course of antibiotics</li><li>An acute infection</li><li>A sudden, sustained change in diet</li><li>Prolonged physical or psychological stress</li></ul>',
                    ],
                    'Recovering balance' => [
                        'In most healthy adults the community re-establishes itself over weeks. Fibre variety, adequate fluids, sleep and — where appropriate — a probiotic can support that process.',
                    ],
                ]),
            ],
            [
                'title'    => 'Can irritable bowel syndrome be cured?',
                'slug'     => 'can-irritable-bowel-syndrome-be-cured',
                'category' => 'gut-health',
                'eyebrow'  => 'Gut health',
                'image'    => 'gut',
                'excerpt'  => 'IBS is managed rather than cured — but management can be good enough that it stops organising your week.',
                'body'     => $this->body([
                    'A condition of function, not damage' => [
                        'IBS is diagnosed on a pattern of symptoms once other conditions have been excluded. The bowel looks structurally normal; it behaves differently.',
                    ],
                    'What management usually involves' => [
                        '<ul><li>Identifying and moderating trigger foods, ideally with a dietitian</li><li>Regular meals and regular sleep</li><li>Stress and pain management, including gut-directed approaches</li><li>Targeted products, discussed with your doctor or pharmacist</li></ul>',
                    ],
                    'Setting expectations' => [
                        'Most people find a combination that reduces the frequency and intensity of flares substantially. That is a realistic goal, and a worthwhile one.',
                    ],
                ]),
            ],
            [
                'title'    => 'Watery diarrhoea: what it is and what to do first',
                'slug'     => 'watery-diarrhoea',
                'category' => 'gut-health',
                'eyebrow'  => 'Gut health',
                'image'    => 'gut',
                'excerpt'  => 'The first priority is fluid, not stopping the symptom. Here is the order to work through.',
                'body'     => $this->body([
                    'Rehydrate first' => [
                        'Watery stools move fluid and electrolytes out of the body quickly. Oral rehydration solution replaces both in the right proportions — plain water alone does not.',
                    ],
                    'Then consider the cause' => [
                        'Most acute episodes in healthy adults are self-limiting and viral. Recent travel, a new medicine or a recent course of antibiotics are all worth noting and mentioning.',
                    ],
                    'Seek advice without delay if' => [
                        '<ul><li>There is blood in the stool, or a high fever</li><li>Symptoms last more than two or three days</li><li>The person affected is a baby, is frail or elderly, or is pregnant</li><li>There are signs of dehydration: little urine, dizziness, confusion</li></ul>',
                    ],
                ]),
            ],
            [
                'title'    => 'Yeast probiotic vs bacterial probiotic',
                'slug'     => 'yeast-probiotic-vs-bacterial-probiotic',
                'category' => 'gut-health',
                'eyebrow'  => 'Gut health',
                'image'    => 'yeast',
                'excerpt'  => 'They are grouped under one label but behave quite differently — particularly around antibiotics.',
                'body'     => $this->body([
                    'Different organisms, different behaviour' => [
                        'Bacterial probiotics are bacteria; a yeast probiotic is a fungus. That distinction is not cosmetic — it changes how each one responds to antibacterial medicines and how long it stays in the gut.',
                    ],
                    'Three practical differences' => [
                        '<ul><li><strong>Antibiotics.</strong> Antibacterials act on bacteria, so a yeast is not their target.</li><li><strong>Transit.</strong> A yeast probiotic passes through without permanently colonising, and clears after the course ends.</li><li><strong>Storage.</strong> Freeze-dried yeast presentations are typically stable at room temperature.</li></ul>',
                    ],
                    'Choosing between them' => [
                        'The useful question is not which category is better but which strain has evidence for the situation in front of you. Your pharmacist can help you match the two.',
                    ],
                ]),
            ],
            [
                'title'    => 'Quiz: how well do you know your gut microbiota?',
                'slug'     => 'quiz-learn-more-about-your-gut-microbiota',
                'draft'    => true,   // hidden until a real quiz is built
                'category' => 'gut-health',
                'eyebrow'  => 'Interactive',
                'image'    => 'flora',
                'excerpt'  => 'Six questions on fibre, fermentation and the myths that refuse to die.',
                'body'     => $this->body([
                    'Before you start' => [
                        'This is a short knowledge check, not a health assessment. It does not collect answers and it does not produce medical advice.',
                    ],
                    'Placeholder' => [
                        'The interactive quiz component belongs here. Until it is built, this page stands in for it so the navigation and internal links stay intact.',
                    ],
                ]),
            ],

            // ---------------- our uniqueness ----------------
            [
                'title'    => 'How the strain works in acute diarrhoea',
                'slug'     => 'how-it-works-in-acute-diarrhoea',
                'category' => 'our-uniqueness',
                'eyebrow'  => 'Our uniqueness',
                'image'    => 'science',
                'excerpt'  => 'An outline of the mechanisms studied for this probiotic yeast in acute episodes.',
                'body'     => $this->body([
                    'Mechanisms under study' => [
                        'Research into this strain has looked at several routes of action at once: interaction with pathogens in the gut lumen, effects on the intestinal barrier, and modulation of the local immune response.',
                    ],
                    'What that means in practice' => [
                        'Rehydration remains the foundation of managing an acute episode. A probiotic is an adjunct to that, not a replacement for it.',
                    ],
                    'Placeholder' => [
                        'Replace this section with the approved mechanism-of-action summary and the corresponding references for your market.',
                    ],
                ]),
            ],
            [
                'title'    => 'Antibiotic-associated diarrhoea: the rationale',
                'slug'     => 'antibiotic-associated-diarrhoea-rationale',
                'category' => 'our-uniqueness',
                'eyebrow'  => 'Our uniqueness',
                'image'    => 'science',
                'excerpt'  => 'Why a yeast rather than a bacterium is the usual choice alongside a course of antibiotics.',
                'body'     => $this->body([
                    'The problem' => [
                        'Antibiotics do not distinguish between the bacteria causing an infection and the bacteria that keep your gut in balance. Loose stools during or after a course are a common consequence.',
                    ],
                    'Why a yeast' => [
                        'Because antibacterial agents act on bacteria, a yeast probiotic can be taken through the course without being neutralised by it.',
                    ],
                    'Placeholder' => [
                        'Insert the approved indication wording, dosing schedule and study references here.',
                    ],
                ]),
            ],
            [
                'title'    => 'Travellers\' diarrhoea: preparing before you go',
                'slug'     => 'travellers-diarrhoea-preparing-before-you-go',
                'category' => 'our-uniqueness',
                'eyebrow'  => 'Our uniqueness',
                'image'    => 'travel',
                'excerpt'  => 'What to pack, what to avoid, and where a probiotic fits into a travel kit.',
                'body'     => $this->body([
                    'Build the kit before departure' => [
                        '<ul><li>Oral rehydration sachets — the single most useful item</li><li>A probiotic in a format that does not need refrigeration</li><li>Hand sanitiser, and a plan for drinking water</li></ul>',
                    ],
                    'On the ground' => [
                        'Bottled or treated water, food served hot, and fruit you peel yourself remain the sensible defaults in higher-risk destinations.',
                    ],
                    'Placeholder' => [
                        'Replace with the approved prophylaxis guidance and dosing for your market.',
                    ],
                ]),
            ],
            [
                'title'    => 'Supporting IBS symptoms over the longer term',
                'slug'     => 'supporting-ibs-symptoms',
                'category' => 'our-uniqueness',
                'eyebrow'  => 'Our uniqueness',
                'image'    => 'gut',
                'excerpt'  => 'IBS management is measured in weeks and months, which changes how a product is used.',
                'body'     => $this->body([
                    'A different time horizon' => [
                        'Unlike an acute episode, IBS is a long game. Products aimed at it are taken daily over an extended period and assessed against a symptom diary rather than a single good day.',
                    ],
                    'Alongside, not instead of' => [
                        'Dietary work, sleep, activity and stress management do the heavy lifting. A targeted product sits alongside them.',
                    ],
                    'Placeholder' => [
                        'Insert the approved claims, evidence summary and duration-of-use guidance here.',
                    ],
                ]),
            ],
            [
                'title'    => 'The story behind our probiotic yeast',
                'slug'     => 'the-story-behind-our-probiotic-yeast',
                'category' => 'our-uniqueness',
                'eyebrow'  => 'Heritage',
                'image'    => 'yeast',
                'excerpt'  => 'From a field observation to a characterised strain with a manufacturing standard behind it.',
                'body'     => $this->body([
                    'From observation to strain' => [
                        'The history of most probiotics follows the same arc: something is noticed in the field, isolated in a laboratory, characterised, and then held to a manufacturing specification so that every batch behaves like the one that was studied.',
                    ],
                    'Why the specific strain matters' => [
                        'Effects demonstrated for one strain cannot be assumed for its relatives. The strain designation on a pack is the part that ties a product to the research behind it.',
                    ],
                    'Placeholder' => [
                        'Replace with the approved corporate history and strain documentation.',
                    ],
                ]),
            ],
            [
                'title'    => 'A unique probiotic yeast strain',
                'slug'     => 'a-unique-probiotic-yeast-strain',
                'category' => 'our-uniqueness',
                'eyebrow'  => 'Science',
                'image'    => 'yeast',
                'excerpt'  => 'What characterisation, stability and batch consistency actually involve.',
                'body'     => $this->body([
                    'Characterisation' => [
                        'A probiotic strain is defined genetically and phenotypically, then deposited in a recognised culture collection so that it can be verified independently.',
                    ],
                    'Stability and consistency' => [
                        'Freeze-drying allows a living organism to be stored at room temperature and counted reliably at the end of shelf life. Every batch is held to the same viable-count specification.',
                    ],
                    'Placeholder' => [
                        'Insert the approved technical description and quality documentation.',
                    ],
                ]),
            ],

            // ---------------- news ----------------
            [
                'title'       => 'A refreshed look for our global site',
                'slug'        => 'a-refreshed-look-for-our-global-site',
                'category'    => 'news',
                'eyebrow'     => 'Company news',
                'image'       => 'science',
                'is_featured' => true,
                'excerpt'     => 'A rebuilt front end, clearer navigation and a content model local teams can actually work with.',
                'body'        => $this->body([
                    'What changed' => [
                        'The site has been rebuilt around a component-based content model: ordered, typed sections that editors compose per page rather than bespoke templates per campaign.',
                    ],
                    'What it means for local teams' => [
                        'New market pages can be assembled from existing components, which shortens the path from approved copy to a published page.',
                    ],
                ]),
            ],
            [
                'title'    => 'Notes from this year\'s microbiota congress',
                'slug'     => 'notes-from-this-years-microbiota-congress',
                'category' => 'news',
                'eyebrow'  => 'Research',
                'image'    => 'science',
                'excerpt'  => 'Three themes worth following: strain specificity, study design, and the gap between marketing and evidence.',
                'body'     => $this->body([
                    'Strain specificity, again' => [
                        'The recurring message from clinical sessions is that evidence attaches to strains, not to categories. Grouping all probiotics together is the most common error in public communication.',
                    ],
                    'Better trials, slowly' => [
                        'Sample sizes and endpoint definitions continue to improve, which makes meta-analysis more useful than it was a decade ago.',
                    ],
                ]),
            ],
            [
                'title'    => 'New paediatric drops presentation',
                'slug'     => 'new-paediatric-drops-presentation',
                'category' => 'news',
                'eyebrow'  => 'Product news',
                'image'    => 'family',
                'excerpt'  => 'A dropper format that makes small doses easier to measure accurately.',
                'body'     => $this->body([
                    'Why a dropper' => [
                        'Accurate small doses are difficult with a sachet. A calibrated dropper removes the guesswork for parents and carers.',
                    ],
                    'Availability' => [
                        'Rolling out by market. Check the Where to buy page, or ask your pharmacist about local availability.',
                    ],
                ]),
            ],
        ];
    }

    /** Compose an article body from a heading => paragraphs map. */
    private function body(array $sections): string
    {
        $html = '';

        foreach ($sections as $heading => $paragraphs) {
            $html .= '<h2>'.$heading.'</h2>';
            foreach ($paragraphs as $paragraph) {
                $html .= str_starts_with($paragraph, '<') ? $paragraph : '<p>'.$paragraph.'</p>';
            }
        }

        return $html;
    }
}

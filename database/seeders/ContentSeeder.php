<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([...$this->pages(), ...$this->metaPages()] as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }

        // Earlier slug for the terms page.
        Page::where('slug', 'terms-of-use')->delete();

        Faq::query()->delete();
        foreach ($this->faqs() as $i => $faq) {
            Faq::create($faq + ['sort_order' => $i + 1]);
        }
    }

    private function pages(): array
    {
        return [
            [
                'slug'             => 'about-us',
                'title'            => 'About us',
                // The page itself is built from page sections; this text serves search and the meta tags.
                'subtitle'         => 'Bioflor® is a probiotic brand developed by Biocodex and distributed in Cambodia by PPM.',
                'meta_description' => 'Bioflor® is a probiotic brand developed by Biocodex, a French pharmaceutical company specializing in microbiota health, and distributed in Cambodia by PPM.',
                'body'             => '<p>Bioflor® is a probiotic brand developed by Biocodex, a French pharmaceutical company specializing in microbiota health. Bioflor® contains <em>Saccharomyces boulardii</em> CNCM I-745, a well-researched probiotic yeast. In Cambodia, Bioflor® is distributed by PPM, committed to improving access to trusted healthcare products and supporting digestive health.</p>'
                    .'<h2>Our mission</h2>'
                    .'<p>Our mission is to improve digestive health by providing trusted, science-backed probiotic solutions. Through Bioflor® containing <em>Saccharomyces boulardii</em> CNCM I-745, we are committed to supporting gut health, sharing reliable health information, and improving access to quality healthcare in Cambodia.</p>'
                    .'<h2>Our history</h2>'
                    .'<ul><li><strong>1923 — The discovery.</strong> Henri Boulard discovered <em>Saccharomyces boulardii</em> in Indochina.</li>'
                    .'<li><strong>1953 — Biocodex founded.</strong> Biocodex was established in France to develop and commercialize the probiotic yeast.</li>'
                    .'<li><strong>1961 — Medical milestone.</strong> <em>Saccharomyces boulardii</em> CNCM I-745 was officially registered as a medicinal product.</li>'
                    .'<li><strong>2026 — Bioflor® relaunch in Cambodia.</strong> A new partnership with PPM, marked by a relaunch event in October 2026.</li></ul>',
            ],
            [
                'slug'             => 'terms',
                'title'            => 'Terms of use',
                'subtitle'         => 'The conditions that apply when you use this website.',
                'meta_description' => 'Terms governing use of this website.',
                'body'             => '<h2>Scope</h2><p>These terms govern your use of this website. By browsing it, you accept them.</p>'
                    .'<h2>Information, not medical advice</h2><p>Content here is general information. It does not replace a consultation with a doctor or pharmacist, and it must not be used to diagnose or treat a condition.</p>'
                    .'<h2>Intellectual property</h2><p>Trade marks, text, images and code on this site are protected. Do not reuse them without written permission.</p>'
                    .'<h2>Liability</h2><p>We take care to keep information accurate and current, but we cannot guarantee it is complete or error-free at all times.</p>'
                    .'<h2>Placeholder</h2><p><strong>Legal placeholder text.</strong> Replace with wording reviewed by your legal team for each market before publication.</p>',
            ],
            [
                'slug'             => 'data-protection',
                'title'            => 'Data protection',
                'subtitle'         => 'What we collect, why, and the rights you can exercise.',
                'meta_description' => 'How personal data is handled on this website.',
                'body'             => '<h2>What we collect</h2><p>Only what a given feature needs: an email address if you subscribe to the newsletter, and the contents of any message you send us through the contact form.</p>'
                    .'<h2>Why</h2><p>To send the newsletter you asked for, and to answer your enquiry. Nothing else.</p>'
                    .'<h2>Your rights</h2><ul><li>Access the data we hold about you</li><li>Have it corrected or erased</li><li>Withdraw consent to the newsletter at any time</li><li>Object to processing, or ask for it to be restricted</li></ul>'
                    .'<h2>Placeholder</h2><p><strong>Legal placeholder text.</strong> Replace with your GDPR-reviewed privacy notice, including the controller identity, retention periods, lawful basis and supervisory authority.</p>',
            ],
            [
                'slug'             => 'cookies-settings',
                'title'            => 'Cookies settings',
                'subtitle'         => 'Choose which categories of cookies this site may use.',
                'meta_description' => 'Manage cookie preferences for this website.',
                'body'             => '<h2>Categories</h2>'
                    .'<ul><li><strong>Strictly necessary.</strong> Required for the site to function, including your session and security tokens. Always on.</li>'
                    .'<li><strong>Analytics.</strong> Aggregate measurement of how pages are used.</li>'
                    .'<li><strong>Marketing.</strong> Used to measure campaign performance.</li></ul>'
                    .'<h2>Placeholder</h2><p>The consent-management interface belongs on this page. Until a CMP is integrated, this site sets only the session and CSRF cookies Laravel requires.</p>',
            ],
            [
                'slug'             => 'accessibility-assessment',
                'title'            => 'Accessibility assessment',
                'subtitle'         => 'Our current conformance position and the work still outstanding.',
                'meta_description' => 'Accessibility conformance statement for this website.',
                'body'             => '<h2>Our target</h2><p>WCAG 2.1 level AA.</p>'
                    .'<h2>What is in place</h2><ul><li>Skip links to navigation, main content and footer</li>'
                    .'<li>Keyboard-operable menu, tabs, accordions and carousels with ARIA state</li>'
                    .'<li>Visible focus styles throughout</li>'
                    .'<li>Text alternatives on decorative and informative graphics</li>'
                    .'<li>Reduced-motion support via <code>prefers-reduced-motion</code></li>'
                    .'<li>A text transcript alongside video content</li></ul>'
                    .'<h2>Known gaps</h2><p>Colour-contrast verification on final photography, and a full audit with assistive technology, are still to be completed.</p>'
                    .'<h2>Feedback</h2><p>If you meet a barrier on this site, contact us and we will address it.</p>',
            ],
        ];
    }

    /**
     * Title and meta data for pages whose body is composed from page_sections
     * (or rendered by a dedicated controller). The body stays empty.
     */
    private function metaPages(): array
    {
        $rows = [
            'our-products'          => ['Our products', 'The {brand} probiotic yeast range: sachets and capsules.'],
            'gut-health'            => ['Gut health', 'Understand your gut microbiota and how to look after it.'],
            'our-uniqueness'        => ['Our uniqueness', 'What makes a probiotic yeast different, and the science behind it.'],
            'our-news'              => ['Our news', 'News, seasonal advice and research highlights.'],
            'all-news'              => ['All news', 'Gut health tips, product news and research from {brand}: every article in one place, newest first.'],
            'faq'                   => ['Q&A', 'Answers to common questions about probiotics and the {brand} range.'],
            'contact-us'            => ['Contact us', 'Questions about our products? Send us a message.'],
            'find-in-store'         => ['Where to buy', 'Pharmacies and online partners selling {brand}.'],
            'gut-health-quiz-intro' => ['Gut health quiz', 'A short quiz to learn more about your gut microbiota.'],
        ];

        return collect($rows)->map(fn ($row, $slug) => [
            'slug'             => $slug,
            'title'            => $row[0],
            'subtitle'         => null,
            'meta_description' => $row[1],
        ])->values()->all();
    }

    private function faqs(): array
    {
        return [
            ['group' => 'about-probiotics', 'question' => 'What is a probiotic?',
             'answer' => '<p>A live microorganism which, taken in an adequate amount, is intended to confer a health benefit. The evidence attaches to a specific strain rather than to the category as a whole.</p>'],
            ['group' => 'about-probiotics', 'question' => 'What is the difference between a probiotic and a prebiotic?',
             'answer' => '<p>A probiotic is a live organism. A prebiotic is a food component — typically a fibre — that the organisms already in your gut can use.</p>'],
            ['group' => 'about-probiotics', 'question' => 'Why does the strain matter so much?',
             'answer' => '<p>Because effects shown for one strain cannot be assumed for a related one. The strain designation on the pack is what connects a product to its research.</p>'],

            ['group' => 'taking-the-product', 'question' => 'Can I take it with antibiotics?',
             'answer' => '<p>A yeast probiotic is commonly taken during a course, since antibacterial agents do not act on yeasts. Check the timing with your pharmacist.</p>'],
            ['group' => 'taking-the-product', 'question' => 'What is the right dose?',
             'answer' => '<p>Follow the pack and the leaflet, which differ by format and by market. If you are unsure, ask your pharmacist rather than estimating.</p>'],
            ['group' => 'taking-the-product', 'question' => 'Can I mix a sachet into a hot drink?',
             'answer' => '<p>No. Use water or a cool drink — heat is not appropriate for a live culture.</p>'],
            ['group' => 'taking-the-product', 'question' => 'What if I miss a dose?',
             'answer' => '<p>Take the next one as scheduled. Do not double up.</p>'],

            ['group' => 'storage-and-safety', 'question' => 'How should I store it?',
             'answer' => '<p>Store below 30&nbsp;&deg;C in a cool, dry place, following the instructions on the pack. Keep out of the reach and sight of children.</p>'],
            ['group' => 'storage-and-safety', 'question' => 'Are there people who should not take it?',
             'answer' => '<p>Yes. Anyone immunocompromised, critically ill, or with a central venous catheter should not take a probiotic without medical supervision. Check the leaflet and ask your doctor.</p>'],
            ['group' => 'storage-and-safety', 'question' => 'Can I take it while pregnant or breastfeeding?',
             'answer' => '<p>Ask your doctor or midwife first — guidance varies by market and by individual circumstances.</p>'],

            ['group' => 'buying-and-availability', 'question' => 'Where can I buy it?',
             'answer' => '<p>Through pharmacies and selected online partners. See the Where to buy page for the list in your country.</p>'],
            ['group' => 'buying-and-availability', 'question' => 'Why is a format I have seen not available here?',
             'answer' => '<p>Registration and availability differ by market. Your pharmacist can advise on the closest locally approved equivalent.</p>'],
        ];
    }
}

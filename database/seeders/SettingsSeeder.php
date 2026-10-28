<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Site identity and shared wording. Existing values are kept on re-seed, so
 * edits made in /admin/settings survive `php artisan db:seed`; only labels,
 * help text and ordering are refreshed.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach ($this->settings() as $group => $rows) {
            foreach ($rows as $key => [$label, $type, $value, $help]) {
                $setting = Setting::firstOrNew(['key' => $key]);

                $setting->fill([
                    'group'      => $group,
                    'label'      => $label,
                    'type'       => $type,
                    'help'       => $help,
                    'sort_order' => $sort += 10,
                ]);

                if (! $setting->exists) {
                    $setting->value = $value;
                }

                $setting->save();
            }
        }
    }

    /** group => key => [label, type, default value, help] */
    private function settings(): array
    {
        return [
            'brand' => [
                'brand.name'        => ['Brand name', 'text', 'Bioflor', 'Used in the logo, page titles and wherever content says {brand}.'],
                'brand.strain'      => ['Logo strapline', 'text', 'Saccharomyces boulardii CNCM I-745', 'Small line under the wordmark.'],
                'brand.logo'        => ['Logo (header)', 'image', MediaSeeder::path('bioflor-logo', null), 'Upload the official logo. Empty = generated wordmark.'],
                'brand.logo_footer' => ['Logo (footer, light)', 'image', MediaSeeder::path('bioflor-logo-white', null), 'Light version for the navy footer. Empty = generated wordmark.'],
                'company.name'      => ['Manufacturer name', 'text', 'Biocodex', 'Shown in the footer legal bar.'],
                'company.logo'      => ['Manufacturer logo (light)', 'image', null, 'Light logo for the footer legal bar.'],
            ],
            'site' => [
                'site.title'            => ['Home page title', 'text', '{brand} | Official site', 'Browser title of the home page. {brand} is replaced.'],
                'site.meta_description' => ['Default meta description', 'textarea', 'Probiotic yeast for everyday gut health: discover the {brand} range, gut health advice and where to buy.', null],
                'site.share_image'      => ['Share image', 'image', MediaSeeder::path('photo-hero-home-dad-and-daughter-desktop', null), 'Picture shown when a page is shared on Facebook, TikTok or a chat app (about 1200 × 630 px). Products and articles use their own photo.'],
                'header.where_to_buy'   => ['"Where to buy" button label', 'text', 'Where to buy', null],
                'header.search_placeholder' => ['Search placeholder', 'text', 'Search (keywords, etc.)', null],
            ],
            'footer' => [
                'distributor.name'     => ['Distributor name', 'text', 'PPM Pharma', 'Company responsible for the product in your market.'],
                'distributor.address1' => ['Address line 1', 'text', 'No.138 Bis, Russian Federation Boulevard (St.110)', null],
                'distributor.address2' => ['Address line 2', 'text', 'Phum Prey Chisak, Sangkat Chom Chao 3, Khan Porsenchey, Phnom Penh, Cambodia', null],
                'distributor.phone'    => ['Telephone', 'text', '+(855) 23 890 093', null],
                'distributor.email'    => ['Email', 'email', 'info@ppmpharma.com', 'Leave empty to hide it.'],
                'distributor.website'  => ['Website', 'url', 'https://ppmpharma.com/', 'Leave empty to hide it.'],
                'social.title'         => ['Social links title', 'text', 'Stay tuned', null],
                'social.facebook'      => ['Facebook URL', 'url', 'https://www.facebook.com/share/1Do3xsXNAC/', 'Leave empty to hide the icon.'],
                'social.tiktok'        => ['TikTok URL', 'url', 'https://www.tiktok.com/@bioflorcambodia', 'Leave empty to hide the icon.'],
                'social.instagram'     => ['Instagram URL', 'url', null, 'Leave empty to hide the icon.'],
                'social.youtube'       => ['YouTube URL', 'url', null, 'Leave empty to hide the icon.'],
            ],
            'newsletter' => [
                'newsletter.title'         => ['Block title', 'text', 'Our newsletter', null],
                'newsletter.text'          => ['Block text', 'textarea', 'Gut health tips, news about {brand} and simple ideas for looking after your gut microbiota, straight to your inbox.', null],
                'newsletter.consent'       => ['Consent text', 'textarea', 'Your email address is only used to send you our newsletter. You can unsubscribe at any time with the link in every email. Read our data protection policy.', 'The words "data protection policy" link to the data protection page.'],
                'newsletter.success_title' => ['Success title', 'text', 'You\'re in! Thanks for subscribing to our newsletter.', null],
                'newsletter.success_text'  => ['Success text', 'textarea', 'Look out for gut-friendly tips and the latest {brand} news in your inbox.', null],
                'newsletter.popup_title'   => ['Pop-in title', 'text', 'Newsletter', null],
                'newsletter.popup_image'   => ['Pop-in image', 'image', MediaSeeder::path('gut-microbiota-2', ''), 'Portrait image beside the pop-in form.'],
                'newsletter.popup_enabled' => ['Show the pop-in', 'boolean', '1', 'Shown once a visitor has viewed two pages; snoozed for 7 days when closed.'],
            ],
        ];
    }
}

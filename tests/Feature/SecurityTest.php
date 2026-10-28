<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    private const ADMIN_PAGES = [
        '/admin', '/admin/checklist', '/admin/account', '/admin/products', '/admin/settings', '/admin/sections',
        '/admin/media', '/admin/import', '/admin/messages', '/admin/subscribers', '/admin/subscribers/export',
        '/admin/retailers', '/admin/reviews',
    ];

    public function test_pages_send_security_headers(): void
    {
        $response = $this->get('/en/home');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringNotContainsString("'unsafe-inline' 'unsafe-eval'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('X-Powered-By');
    }

    public function test_admin_needs_a_login(): void
    {
        foreach (self::ADMIN_PAGES as $page) {
            $this->get($page)->assertRedirect('/admin/login');
        }

        $this->delete('/admin/products/1')->assertRedirect('/admin/login');
        $this->put('/admin/settings', [])->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_a_visitor_account_cannot_open_the_admin(): void
    {
        $this->actingAs(User::forceCreate([
            'name' => 'Visitor', 'email' => 'visitor@example.test', 'password' => bcrypt('x'), 'is_admin' => false,
        ]));

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
    }

    public function test_admin_pages_open_and_use_no_inline_script(): void
    {
        $this->admin();

        foreach (self::ADMIN_PAGES as $page) {
            $response = $this->get($page)->assertOk();
            if ($page !== '/admin/subscribers/export') {
                $this->assertDoesNotMatchRegularExpression('/\son(click|submit|change|load)=/', $response->getContent(), $page.' has an inline handler the CSP would block');
            }
        }
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/admin/login', ['email' => 'admin@sbglobal.test', 'password' => 'wrong-'.$i])->assertSessionHasErrors('email');
        }

        $this->post('/admin/login', ['email' => 'admin@sbglobal.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_public_forms_are_rate_limited(): void
    {
        $form = ['name' => 'Bot', 'email' => 'bot@example.test', 'message' => 'Spam', 'consent' => '1'];

        for ($i = 0; $i < 5; $i++) {
            $this->post('/en/contact-us.html', $form)->assertRedirect();
        }

        $this->post('/en/contact-us.html', $form)->assertStatus(429);
    }

    public function test_search_escapes_what_the_visitor_typed(): void
    {
        $this->get('/en/search?q='.urlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_odd_query_strings_do_not_break_pages(): void
    {
        foreach (['/en/search?q[]=x', '/en/search/suggest?q[]=x', '/en/all-news.html?tag[]=x&sort[]=y&show[]=1',
                  '/en/find-in-store?country[]=x', '/en/search?q='.str_repeat('a', 5000)] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_uploads_keep_only_allowed_extensions(): void
    {
        $this->admin();

        $this->post('/admin/media', ['file' => UploadedFile::fake()->image('photo.html')])->assertSessionHasErrors('file');
        $this->post('/admin/media', ['file' => UploadedFile::fake()->create('shell.php', 1, 'image/png')])->assertSessionHasErrors('file');
    }

    public function test_uploaded_svg_is_cleaned(): void
    {
        $this->admin();
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 10 10" onload="alert(1)">'
            .'<script>alert(2)</script><a xlink:href="javascript:alert(3)"><rect width="10" height="10" onclick="alert(4)"/></a>'
            .'<foreignObject><iframe src="https://evil.example"></iframe></foreignObject><use href="#ok"/></svg>';

        $this->post('/admin/media', ['file' => UploadedFile::fake()->createWithContent('logo.svg', $svg)])->assertSessionHasNoErrors();

        $media = Media::latest('id')->first();
        $file  = public_path('uploads/'.$media->path);

        try {
            $clean = (string) file_get_contents($file);
            $this->assertStringContainsString('<rect', $clean);
            $this->assertStringContainsString('href="#ok"', $clean);
            foreach (['alert', 'script', 'onload', 'onclick', 'javascript:', 'foreignObject', 'iframe'] as $bad) {
                $this->assertStringNotContainsStringIgnoringCase($bad, $clean);
            }
        } finally {
            @unlink($file);
        }
    }

    public function test_subscriber_export_cannot_carry_spreadsheet_formulas(): void
    {
        $this->admin();
        NewsletterSubscriber::create(['email' => '=HYPERLINK("http://evil.example")@x.test', 'locale' => 'en_US', 'consent' => true, 'source' => 'block']);

        $csv = $this->get('/admin/subscribers/export')->assertOk()->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertDoesNotMatchRegularExpression('/^"?=HYPERLINK/m', $csv);
    }
}

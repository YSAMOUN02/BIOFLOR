<?php

namespace Tests\Unit;

use App\Support\SafeUpload;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SafeUploadTest extends TestCase
{
    public function test_only_listed_extensions_pass(): void
    {
        $this->assertSame('webp', SafeUpload::extension('Photo.WEBP', SafeUpload::IMAGES));

        foreach (['x.html', 'x.php', 'x.phtml', 'x.png.php', 'x', 'x.svgz'] as $name) {
            try {
                SafeUpload::extension($name, SafeUpload::IMAGES);
                $this->fail($name.' should be refused');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_svg_with_doctype_or_not_svg_is_refused(): void
    {
        foreach ([
            '<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg">&x;</svg>',
            '<html><script>alert(1)</script></html>',
            'not xml at all',
        ] as $content) {
            $file = tempnam(sys_get_temp_dir(), 'svg');
            file_put_contents($file, $content);

            try {
                SafeUpload::cleanSvg($file);
                $this->fail('Should be refused: '.$content);
            } catch (ValidationException) {
                $this->assertFileDoesNotExist($file);
            }
        }
    }

    public function test_clean_svg_keeps_the_drawing(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'svg');
        file_put_contents($file, '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 1h22" style="fill:red"/>'
            .'<set attributeName="href" to="javascript:alert(1)"/><image href="https://evil.example/x.png"/></svg>');

        SafeUpload::cleanSvg($file);
        $svg = file_get_contents($file);
        unlink($file);

        $this->assertStringContainsString('<path d="M1 1h22" style="fill:red"/>', $svg);
        $this->assertStringNotContainsString('<set', $svg);
        $this->assertStringNotContainsString('evil.example', $svg);
    }
}

<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Upload rules shared by every admin upload that lands in public/uploads.
 *
 * Laravel's "mimes" rule checks the file's content, but the saved name kept
 * the extension the uploader typed — so a real PNG named "x.html" was served
 * as a web page. Here the extension must be on the allow-list, and SVG files
 * (which can carry script) are cleaned before they are published.
 */
class SafeUpload
{
    public const IMAGES = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif', 'svg'];
    public const VIDEOS = ['mp4', 'webm', 'ogg'];

    /** Elements that can run script, load other documents or hide HTML inside an SVG. */
    private const SVG_BLOCKED = ['script', 'foreignobject', 'iframe', 'embed', 'object', 'audio', 'video', 'handler', 'listener', 'set', 'animate'];

    /** Move $file into $dir under a slugged, random-suffixed name; returns the file name. */
    public static function move(UploadedFile $file, string $dir, array $allowed = self::IMAGES, string $field = 'file'): string
    {
        $extension = self::extension($file->getClientOriginalName(), $allowed, $field);

        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = substr($name ?: 'file', 0, 60).'-'.Str::random(6).'.'.$extension;

        $file->move($dir, $name);

        if ($extension === 'svg') {
            self::cleanSvg($dir.'/'.$name, $field);
        }

        return $name;
    }

    /** The lower-case extension of $name, or a validation error when it is not allowed. */
    public static function extension(string $name, array $allowed, string $field = 'file'): string
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([$field => 'Allowed file types: '.implode(', ', $allowed).'.']);
        }

        return $extension;
    }

    /** Strip scripts, event handlers and outside links from an SVG file, in place. */
    public static function cleanSvg(string $path, string $field = 'file'): void
    {
        $svg = (string) file_get_contents($path);

        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        // No DOCTYPE: entity declarations are how XML smuggles in outside files.
        $ok = ! preg_match('/<!DOCTYPE|<!ENTITY/i', $svg) && $dom->loadXML($svg, LIBXML_NONET);
        libxml_use_internal_errors($previous);

        if (! $ok || ! $dom->documentElement || strtolower($dom->documentElement->localName) !== 'svg') {
            @unlink($path);
            throw ValidationException::withMessages([$field => 'That SVG file could not be read.']);
        }

        $xpath = new DOMXPath($dom);

        foreach (iterator_to_array($xpath->query('//*')) as $element) {
            /** @var DOMElement $element */
            if (in_array(strtolower($element->localName), self::SVG_BLOCKED, true)) {
                $element->parentNode?->removeChild($element);
                continue;
            }

            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name  = strtolower($attribute->localName);
                $value = preg_replace('/[\s\x00-\x1f]+/', '', strtolower($attribute->value));

                $handler  = str_starts_with($name, 'on');
                $link     = $name === 'href' && ! str_starts_with($value, '#') && ! str_starts_with($value, 'data:image/');
                $scripted = str_contains($value, 'javascript:') || ($name === 'style' && (str_contains($value, 'url(') || str_contains($value, 'expression(')));

                if ($handler || $link || $scripted) {
                    $element->removeAttributeNode($attribute);
                }
            }
        }

        foreach (iterator_to_array($xpath->query('//processing-instruction()')) as $node) {
            $node->parentNode?->removeChild($node);
        }

        file_put_contents($path, $dom->saveXML($dom->documentElement));
    }
}

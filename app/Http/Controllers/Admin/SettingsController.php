<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use App\Models\Setting;
use App\Support\SafeUpload;
use Illuminate\Http\Request;

/**
 * Site settings grouped by section, edited on one page. With ?lang=km the
 * text settings are edited in that language; they are stored as "key:km"
 * rows and fall back to the default value when empty.
 */
class SettingsController extends Controller
{
    private const GROUPS = [
        'brand'      => 'Brand',
        'site'       => 'Site',
        'footer'     => 'Footer & contact',
        'newsletter' => 'Newsletter',
    ];

    /** Setting types that hold language-dependent text. */
    private const TEXT_TYPES = ['text', 'textarea', 'richtext'];

    public function index(Request $request)
    {
        $lang = $this->locale($request->query('lang'));

        $settings = Setting::where('key', 'not like', '%:%')->orderBy('sort_order')->get();

        if ($lang) {
            $settings = $settings->filter(fn ($s) => in_array($s->type, self::TEXT_TYPES, true))->values();
            $translated = Setting::where('key', 'like', '%:'.$lang->url_prefix)->pluck('value', 'key');
            $settings->each(fn ($s) => $s->translated_value = $translated[$s->key.':'.$lang->url_prefix] ?? null);
        }

        return view('admin.settings.index', [
            'groups'     => $settings->groupBy('group'),
            'groupNames' => self::GROUPS,
            'locales'    => Locale::orderBy('sort_order')->get(),
            'lang'       => $lang,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings'   => ['nullable', 'array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
            'uploads.*'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $settings = Setting::where('key', 'not like', '%:%')->get()->keyBy('key');

        if ($lang = $this->locale($request->input('lang'))) {
            foreach ((array) $request->input('settings', []) as $field => $value) {
                $base = $settings[str_replace('__', '.', $field)] ?? null;
                if (! $base || ! in_array($base->type, self::TEXT_TYPES, true)) {
                    continue;
                }

                $key = $base->key.':'.$lang->url_prefix;
                if (trim((string) $value) === '') {
                    Setting::where('key', $key)->delete();
                } else {
                    Setting::updateOrCreate(['key' => $key], [
                        'group' => $base->group, 'label' => $base->label.' ('.$lang->label.')', 'type' => $base->type,
                        'value' => $value, 'sort_order' => $base->sort_order,
                    ]);
                }
            }

            return redirect()->route('admin.settings.index', ['lang' => $lang->url_prefix])->with('status', $lang->label.' settings saved.');
        }

        foreach ((array) $request->input('settings', []) as $field => $value) {
            $key = str_replace('__', '.', $field);   // dots are not allowed in input names
            if ($setting = $settings[$key] ?? null) {
                $setting->update(['value' => $setting->type === 'boolean' ? ($value ? '1' : '0') : $value]);
            }
        }

        foreach ((array) $request->file('uploads', []) as $field => $file) {
            $key = str_replace('__', '.', $field);
            if ($setting = $settings[$key] ?? null) {
                $name = SafeUpload::move($file, public_path('uploads/branding'), ['jpg', 'jpeg', 'png', 'webp', 'svg'], 'uploads.'.$field);
                $setting->update(['value' => 'branding/'.$name]);
            }
        }

        foreach ((array) $request->input('remove', []) as $field => $remove) {
            $key = str_replace('__', '.', $field);
            if ($remove && ($setting = $settings[$key] ?? null)) {
                $setting->update(['value' => null]);
            }
        }

        return back()->with('status', 'Settings saved.');
    }

    private function locale(?string $prefix): ?Locale
    {
        return $prefix ? Locale::where('url_prefix', $prefix)->where('is_default', false)->first() : null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /** Newsletter block and pop-in. Answers JSON to fetch() calls, redirects otherwise. */
    public function subscribe(Request $request)
    {
        $nameRequired = $request->input('source') !== 'popup';

        $data = $request->validate([
            'first_name' => [$nameRequired ? 'required' : 'nullable', 'string', 'max:40'],
            'last_name'  => [$nameRequired ? 'required' : 'nullable', 'string', 'max:80'],
            'email'      => ['required', 'email', 'max:50'],
            'consent'    => ['accepted'],
            'source'     => ['nullable', 'in:block,popup'],
        ], [
            'required'         => __('This field is required.'),
            'email'            => __('Please enter a valid e-mail address.'),
            'consent.accepted' => __('This field is required.'),
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => $data['email']],
            [
                'first_name' => $data['first_name'] ?? null,
                'last_name'  => $data['last_name'] ?? null,
                'locale'     => optional(app('storefront.locale_model'))->code ?? 'en_US',
                'consent'    => true,
                'source'     => $data['source'] ?? 'block',
            ],
        );

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('newsletter_status', 'subscribed')->withFragment('newsletter');
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:190'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:4000'],
            'consent' => ['accepted'],
        ], [
            'required'         => __('This field is required.'),
            'consent.accepted' => __('This field is required.'),
        ]);

        unset($data['consent']);
        ContactMessage::create($data);

        return back()->with('contact_status', __('Thank you for reaching out. Our team will reply shortly.'));
    }
}

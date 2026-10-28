<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InboxController extends Controller
{
    public function subscribers()
    {
        return view('admin.inbox.subscribers', [
            'subscribers' => NewsletterSubscriber::latest()->paginate(30),
        ]);
    }

    public function messages()
    {
        return view('admin.inbox.messages', [
            'messages' => ContactMessage::latest()->paginate(20),
        ]);
    }

    public function destroySubscriber(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return back()->with('status', 'Subscriber removed.');
    }

    public function destroyMessage(ContactMessage $message)
    {
        $message->delete();

        return back()->with('status', 'Message deleted.');
    }

    /** CSV export for the mailing tool. */
    public function exportSubscribers(): StreamedResponse
    {
        $filename = 'subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'locale', 'consent', 'subscribed_at']);

            // Visitors type these values: a cell starting with = + - @ would run as a formula in Excel.
            $cell = fn ($value) => preg_match('/^[=+\-@\t\r]/', (string) $value) ? "'".$value : $value;

            NewsletterSubscriber::orderBy('id')->chunk(500, function ($rows) use ($out, $cell) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map($cell, [
                        $row->email,
                        $row->locale,
                        $row->consent ? 'yes' : 'no',
                        optional($row->created_at)->toDateTimeString(),
                    ]));
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}

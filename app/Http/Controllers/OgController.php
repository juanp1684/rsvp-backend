<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class OgController extends Controller
{
    public function rsvp(string $eventSlug, string $code): Response
    {
        $event = Event::where('slug', $eventSlug)->first();

        $title       = e($event?->name ?? 'RSVP');
        $description = e($event?->subtitle ?? '');
        $pageUrl     = e(request()->url());
        $image       = null;

        if ($event?->invitation_image) {
            $raw = Storage::disk('public')->url($event->invitation_image);
            $image = e(str_starts_with($raw, 'http') ? $raw : url($raw));
        }

        $ogImage = $image
            ? "<meta property=\"og:image\" content=\"{$image}\">\n    <meta property=\"og:image:width\" content=\"1200\">"
            : '';

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>{$title}</title>
            <meta property="og:type" content="website">
            <meta property="og:title" content="{$title}">
            <meta property="og:description" content="{$description}">
            <meta property="og:url" content="{$pageUrl}">
            {$ogImage}
        </head>
        <body></body>
        </html>
        HTML;

        return response($html, 200, ['Content-Type' => 'text/html']);
    }
}

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
        $pageUrl     = e($this->publicUrl());
        $image       = null;

        if ($event?->invitation_image) {
            $raw = Storage::disk('public')->url($event->invitation_image);
            $image = e(str_starts_with($raw, 'http') ? $raw : url($raw));
        }

        $ogImage = $image ? implode("\n    ", [
            "<meta property=\"og:image\" content=\"{$image}\">",
            "<meta property=\"og:image:secure_url\" content=\"{$image}\">",
            "<meta property=\"og:image:type\" content=\"image/jpeg\">",
            "<meta property=\"og:image:width\" content=\"1200\">",
            "<meta property=\"og:image:height\" content=\"630\">",
        ]) : '';

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>{$title}</title>
            <meta property="og:type" content="website">
            <meta property="og:site_name" content="RSVP">
            <meta property="og:title" content="{$title}">
            <meta property="og:description" content="{$description}">
            <meta property="og:url" content="{$pageUrl}">
            {$ogImage}
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{$title}">
            <meta name="twitter:description" content="{$description}">
        </head>
        <body></body>
        </html>
        HTML;

        return response($html, 200, ['Content-Type' => 'text/html']);
    }

    private function publicUrl(): string
    {
        $request = request();

        $proto = $request->header('X-Forwarded-Proto') ?? $request->getScheme();
        $host  = $request->header('X-Forwarded-Host')  ?? $request->getHost();
        $port  = $request->header('X-Forwarded-Port');

        $hostWithPort = $host;
        if ($port && !in_array($port, ['80', '443'])) {
            $hostWithPort = "{$host}:{$port}";
        }

        return "{$proto}://{$hostWithPort}{$request->getRequestUri()}";
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\LinkClick;
use App\Models\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShortLinkController extends Controller
{
    public function show(ShortLink $shortLink, Request $request)
    {
        if (! $shortLink->is_active) {
            abort(404);
        }

        if ($shortLink->isExpired()) {
            abort(410, 'Link sudah kedaluwarsa.');
        }

        $referer = $request->header('referer', '');
        $refererPath = parse_url($referer, PHP_URL_PATH) ?: '';
        $shouldTrack = ! str_starts_with($refererPath, '/upanel');

        DB::transaction(function () use ($shortLink, $request, $referer, $shouldTrack) {
            if ($shouldTrack) {
                $shortLink->increment('click_count');
                $shortLink->update(['last_clicked_at' => now()]);

                LinkClick::create([
                    'short_link_id' => $shortLink->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr($request->userAgent() ?? '', 0, 500),
                    'referer' => mb_substr($referer, 0, 2048) ?: null,
                    'clicked_at' => now(),
                ]);
            }
        });

        return redirect()->away($shortLink->original_url);
    }
}
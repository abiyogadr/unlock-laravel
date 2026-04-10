<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\LandingPageLink;
use App\Models\LinkClick;
use Illuminate\Http\Request;

class LandingPagePublicController extends Controller
{
    public function show(string $slug)
    {
        $page = LandingPage::where('slug', $slug)
            ->where('status', 'published')
            ->where('is_active', true)
            ->with('activeLinks')
            ->firstOrFail();

        return view('landing-page.public', [
            'page'  => $page,
            'links' => $page->activeLinks,
        ]);
    }

    public function trackClick(Request $request, LandingPageLink $link)
    {
        $page = $link->landingPage;

        if (!$page || !$page->isPublished() || !$link->is_active) {
            abort(404);
        }

        LinkClick::create([
            'landing_page_id'      => $page->id,
            'landing_page_link_id' => $link->id,
            'ip_address'           => $request->ip(),
            'user_agent'           => mb_substr($request->userAgent() ?? '', 0, 500),
            'referer'              => mb_substr($request->header('referer', ''), 0, 2048) ?: null,
            'clicked_at'           => now(),
        ]);

        $targetUrl = $request->integer('slot') === 2
            ? ($link->url_2 ?: $link->url)
            : $link->url;

        return redirect()->away($targetUrl);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminShortLinkController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');

        $query = ShortLink::query()->withCount('clicks')->withMax('clicks', 'clicked_at');

        if ($request->filled('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('short_code', 'like', '%' . $search . '%')
                    ->orWhere('original_url', 'like', '%' . $search . '%');
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true)->where(function ($builder) {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'expired') {
            $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
        }

        $shortLinks = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total_links' => ShortLink::count(),
            'total_clicks' => ShortLink::sum('click_count'),
            'active_links' => ShortLink::where('is_active', true)
                ->where(function ($builder) {
                    $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count(),
            'expired_links' => ShortLink::whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->count(),
        ];

        return view('admin.short-links.index', compact('shortLinks', 'stats', 'status', 'search'));
    }

    public function create()
    {
        return view('admin.short-links.create', [
            'shortLink' => new ShortLink([
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $userId = $request->user()?->id;

        $validated = $request->validate([
            'original_url' => ['required', 'url:http,https', 'max:2048'],
            'short_code' => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:short_links,short_code'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $shortCode = $validated['short_code'] ?? null;

        if (!filled($shortCode)) {
            $shortCode = ShortLink::generateUniqueShortCode();
        } else {
            $shortCode = Str::lower(trim($shortCode));
        }

        $shortLink = ShortLink::create([
            'user_id' => $userId,
            'short_code' => $shortCode,
            'original_url' => $validated['original_url'],
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.short-links.edit', $shortLink)
            ->with('success', 'Short link berhasil dibuat.');
    }

    public function edit(ShortLink $shortLink)
    {
        $shortLink->load(['clicks' => function ($builder) {
            $builder->latest()->limit(10);
        }])->loadCount('clicks');

        return view('admin.short-links.edit', compact('shortLink'));
    }

    public function update(Request $request, ShortLink $shortLink)
    {
        $validated = $request->validate([
            'original_url' => ['required', 'url:http,https', 'max:2048'],
            'short_code' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:short_links,short_code,' . $shortLink->id],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $shortLink->update([
            'original_url' => $validated['original_url'],
            'short_code' => Str::lower(trim($validated['short_code'])),
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('admin.short-links.edit', $shortLink)
            ->with('success', 'Short link berhasil diperbarui.');
    }

    public function destroy(ShortLink $shortLink)
    {
        $shortLink->delete();

        return redirect()
            ->route('admin.short-links.index')
            ->with('success', 'Short link berhasil dihapus.');
    }
}
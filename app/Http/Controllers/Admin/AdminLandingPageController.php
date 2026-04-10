<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\LandingPageLink;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminLandingPageController extends Controller
{
    // ── Inertia Page ─────────────────────────────────────────

    public function index()
    {
        $pages = LandingPage::where('user_id', auth()->id())
            ->with(['links' => fn ($q) => $q->withCount('clicks')->orderBy('sort_order')])
            ->withCount('clicks')
            ->latest()
            ->get()
            ->each(fn ($p) => $p->append('resolved_style'));

        return Inertia::render('Index', [
            'pages' => $pages,
        ]);
    }

    public function preview(LandingPage $landingPage)
    {
        $this->authorizeOwner($landingPage);

        $landingPage->load(['links' => fn ($q) => $q->withCount('clicks')->orderBy('sort_order')]);
        $landingPage->loadCount('clicks');
        $landingPage->append('resolved_style');

        return view('landing-page.public', [
            'page'    => $landingPage,
            'links'   => $landingPage->links,
            'preview' => true,
        ]);
    }

    // ── JSON API ─────────────────────────────────────────────

    public function store(Request $request)
    {
        $count = LandingPage::where('user_id', auth()->id())->count();

        $page = LandingPage::create([
            'user_id'       => auth()->id(),
            'name'          => 'Landing Page ' . ($count + 1),
            'slug'          => LandingPage::generateUniqueSlug('landing-page-' . ($count + 1)),
            'title'         => 'My Landing Page',
            'status'        => 'draft',
            'template_type' => 'bio',
            'style'         => LandingPage::DEFAULT_STYLE,
        ]);

        $page->load(['links' => fn ($q) => $q->withCount('clicks')->orderBy('sort_order')]);
        $page->loadCount('clicks');
        $page->append('resolved_style');

        return response()->json(['page' => $page]);
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $this->authorizeOwner($landingPage);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('landing_pages')->ignore($landingPage->id)],
            'title'            => 'required|string|max:255',
            'bio'              => 'nullable|string|max:2000',
            'status'           => 'required|in:draft,published',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'style'            => 'nullable|array',
            'links'                   => 'nullable|array',
            'links.*.id'              => 'nullable|integer',
            'links.*.type'            => 'nullable|in:link,button,text,image,catalog',
            'links.*.label'           => 'nullable|string|max:255',
            'links.*.label_2'         => 'nullable|string|max:255',
            'links.*.url'             => 'nullable|url:http,https|max:2048',
            'links.*.url_2'           => 'nullable|url:http,https|max:2048',
            'links.*.icon'            => 'nullable|string|max:100',
            'links.*.thumbnail'       => 'nullable|string|max:500',
            'links.*.content'         => 'nullable|string|max:5000',
            'links.*.image_path'      => 'nullable|string|max:500',
            'links.*.elem_style'      => 'nullable|array',
            'links.*.elem_style.variant' => 'nullable|in:solid,outline,soft,ghost',
            'links.*.elem_style.bg_color' => 'nullable|string|max:20',
            'links.*.elem_style.text_color' => 'nullable|string|max:20',
            'links.*.elem_style.rounded' => 'nullable|string|max:20',
            'links.*.elem_style.scale' => 'nullable|integer|in:25,40,50,60,75,100',
            'links.*.elem_style.shadow' => 'nullable|boolean',
            'links.*.elem_style.catalog_layout' => 'nullable|integer|in:1,2,4,6',
            'links.*.elem_style.catalog_main_card' => 'nullable|boolean',
            'links.*.elem_style.catalog_show_price' => 'nullable|boolean',
            'links.*.elem_style.catalog_items' => 'nullable|array',
            'links.*.is_active'       => 'boolean',
            'links.*.opens_in_new_tab'=> 'boolean',
        ]);

        DB::transaction(function () use ($landingPage, $validated) {
            $landingPage->update(Arr::except($validated, ['links']));

            $incomingLinks = $validated['links'] ?? [];
            $existingIds   = $landingPage->links()->pluck('id')->toArray();
            $incomingIds   = collect($incomingLinks)->pluck('id')->filter()->toArray();

            $toDelete = array_diff($existingIds, $incomingIds);
            if ($toDelete) {
                LandingPageLink::whereIn('id', $toDelete)
                    ->where('landing_page_id', $landingPage->id)
                    ->delete();
            }

            foreach ($incomingLinks as $index => $linkData) {
                $linkData['sort_order']      = $index;
                $linkData['landing_page_id'] = $landingPage->id;
                unset($linkData['clicks_count']);

                if (($linkData['type'] ?? 'link') === 'catalog') {
                    $linkData['elem_style']['catalog_layout'] = (int) ($linkData['elem_style']['catalog_layout'] ?? 4);
                    $linkData['elem_style']['catalog_main_card'] = $linkData['elem_style']['catalog_main_card'] ?? true;
                    $linkData['elem_style']['catalog_show_price'] = $linkData['elem_style']['catalog_show_price'] ?? true;
                }

                if (($linkData['type'] ?? 'link') === 'button') {
                    $linkData['type'] = 'link';
                    $linkData['elem_style'] = array_merge($linkData['elem_style'] ?? [], ['kind' => 'button']);
                }

                if (!empty($linkData['id'])) {
                    $linkId = $linkData['id'];
                    unset($linkData['id']);
                    LandingPageLink::where('id', $linkId)
                        ->where('landing_page_id', $landingPage->id)
                        ->update($linkData);
                } else {
                    unset($linkData['id']);
                    LandingPageLink::create($linkData);
                }
            }
        });

        $fresh = $landingPage->fresh()
            ->load(['links' => fn ($q) => $q->withCount('clicks')->orderBy('sort_order')])
            ->loadCount('clicks')
            ->append('resolved_style');

        return response()->json(['page' => $fresh, 'message' => 'Berhasil disimpan.']);
    }

    public function destroy(LandingPage $landingPage)
    {
        $this->authorizeOwner($landingPage);

        if ($landingPage->avatar) {
            Storage::disk('public')->delete($landingPage->avatar);
        }
        if ($landingPage->cover_image) {
            Storage::disk('public')->delete($landingPage->cover_image);
        }

        $landingPage->delete();

        return response()->json(['message' => 'Landing page berhasil dihapus.']);
    }

    public function toggleStatus(LandingPage $landingPage)
    {
        $this->authorizeOwner($landingPage);

        $newStatus = $landingPage->status === 'published' ? 'draft' : 'published';
        $landingPage->update(['status' => $newStatus]);

        return response()->json(['status' => $newStatus]);
    }

    public function duplicate(LandingPage $landingPage)
    {
        $this->authorizeOwner($landingPage);

        $newPage = DB::transaction(function () use ($landingPage) {
            $clone = $landingPage->replicate(['slug']);
            $clone->name   = $landingPage->name . ' (Copy)';
            $clone->slug   = LandingPage::generateUniqueSlug($landingPage->slug . '-copy');
            $clone->status = 'draft';
            $clone->save();

            foreach ($landingPage->links as $link) {
                $newLink = $link->replicate();
                $newLink->landing_page_id = $clone->id;
                $newLink->save();
            }

            return $clone;
        });

        $newPage->load(['links' => fn ($q) => $q->withCount('clicks')->orderBy('sort_order')]);
        $newPage->loadCount('clicks');
        $newPage->append('resolved_style');

        return response()->json(['page' => $newPage]);
    }

    public function uploadImage(Request $request, LandingPage $landingPage)
    {
        $this->authorizeOwner($landingPage);

        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
            'type'  => ['required', 'in:avatar,cover,element'],
        ]);

        $type  = $request->input('type');

        // Element images: just store and return path, no DB write
        if ($type === 'element') {
            $path = $request->file('image')->store('landing-pages/elements', 'public');
            return response()->json([
                'path' => $path,
                'url'  => asset("storage/{$path}"),
            ]);
        }

        $field = $type === 'avatar' ? 'avatar' : 'cover_image';

        if ($landingPage->$field) {
            Storage::disk('public')->delete($landingPage->$field);
        }

        $path = $request->file('image')->store("landing-pages/{$type}s", 'public');
        $landingPage->update([$field => $path]);

        return response()->json([
            'path' => $path,
            'url'  => asset("storage/{$path}"),
        ]);
    }

    public function removeImage(Request $request, LandingPage $landingPage)
    {
        $this->authorizeOwner($landingPage);

        $request->validate([
            'type' => ['required', 'in:avatar,cover'],
        ]);

        $field = $request->input('type') === 'avatar' ? 'avatar' : 'cover_image';

        if ($landingPage->$field) {
            Storage::disk('public')->delete($landingPage->$field);
            $landingPage->update([$field => null]);
        }

        return response()->json(['message' => 'Gambar dihapus.']);
    }

    // ── Helpers ──────────────────────────────────────────────

    private function authorizeOwner(LandingPage $landingPage): void
    {
        if ($landingPage->user_id === auth()->id()) {
            return;
        }

        if (auth()->user()?->isAdmin()) {
            return;
        }

        abort_if(true, 403, 'Anda tidak memiliki akses.');
    }
}

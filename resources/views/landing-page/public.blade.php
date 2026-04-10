@php
    $s = $page->resolved_style;
    $isPreview = $preview ?? false;
    $sizeMap = ['xs'=>'0.75rem','sm'=>'0.875rem','base'=>'1rem','lg'=>'1.125rem','xl'=>'1.25rem','2xl'=>'1.5rem','3xl'=>'1.875rem'];
    $roundedMap = ['none'=>'0','sm'=>'4px','md'=>'6px','lg'=>'8px','xl'=>'12px','full'=>'9999px'];
    $avatarSizeMap = ['sm'=>'3.5rem','md'=>'5rem','lg'=>'7rem'];
    $widthMap = ['sm' => '480px', 'md' => '540px', 'lg' => '720px'];
    $fontMap = [
        'sans' => 'ui-sans-serif, system-ui, sans-serif',
        'serif' => 'ui-serif, Georgia, Cambria, serif',
        'mono' => 'ui-monospace, SFMono-Regular, monospace',
        'rounded' => 'ui-rounded, system-ui, sans-serif',
    ];
    $bgStyle = $s['bg_type'] === 'gradient'
        ? "background:linear-gradient(135deg,{$s['bg_gradient_from']},{$s['bg_gradient_to']})"
        : "background-color:{$s['bg_color']}";
    $pageWidth = $widthMap[$s['page_max_width'] ?? 'sm'] ?? '360px';
    $fontFamily = $fontMap[$s['font_family'] ?? 'sans'] ?? $fontMap['sans'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->title }}</title>
    <meta name="description" content="{{ $page->meta_description ?: Str::limit($page->bio, 160) }}">
    <meta property="og:title" content="{{ $page->meta_title ?: $page->title }}">
    <meta property="og:description" content="{{ $page->meta_description ?: Str::limit($page->bio, 160) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @if($page->avatar)
        <meta property="og:image" content="{{ asset('storage/' . $page->avatar) }}">
    @endif
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $page->meta_title ?: $page->title }}">
    <meta name="twitter:description" content="{{ $page->meta_description ?: Str::limit($page->bio, 160) }}">
    @vite(['resources/css/app.css'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; font-family: {{ $fontFamily }}; }
        .lnk-btn { transition: transform 0.15s ease, box-shadow 0.15s ease; text-decoration: none; }
        .lnk-btn:hover { transform: translateY(-2px); }
        .lnk-btn:active { transform: translateY(0); }
        .mini-link { display:inline-flex; align-items:center; justify-content:center; padding:10px 12px; border-radius:9999px; background:rgba(255,255,255,.18); color:inherit; font-size:12px; font-weight:600; text-decoration:none; }
        .catalog-card { transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease; }
        .catalog-card:hover { transform: translateY(-3px); box-shadow: 0 14px 28px rgba(0,0,0,.12); border-color: rgba(180, 121, 243, 0.22); }
    </style>
</head>
<body style="{{ $bgStyle }}">
    @if($isPreview)
        <div class="flex justify-center pt-3">
            <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-medium text-amber-700 shadow-sm">
                <i class="fas fa-eye"></i>
                Mode preview
            </div>
        </div>
    @endif

    <div class="mx-auto px-4 py-6" style="max-width: {{ $pageWidth }}">
        <div class="bg-white border border-white shadow-2xl rounded-[2rem] overflow-hidden">
            <div class="flex items-center justify-center px-5 py-3 border-b border-gray-100 bg-white/95 backdrop-blur">
                <div class="text-sm font-semibold text-gray-900">Unlock</div>
            </div>

            <div class="px-5 py-6">
                @if(($s['use_cover'] ?? true) && $page->cover_image)
                    <div class="w-full h-48 sm:h-56 overflow-hidden rounded-3xl mb-5 shadow-sm">
                        <img src="{{ asset('storage/' . $page->cover_image) }}" alt="{{ $page->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                @if(($s['use_avatar'] ?? true))
                    <div class="flex flex-col mb-6" style="align-items:{{ $s['title_align'] ?? 'center' }}">
                        @if($page->avatar)
                            <div class="border-4 border-white shadow-lg overflow-hidden mb-3 {{ $page->cover_image ? 'relative z-10' : '' }}"
                                 style="width:{{ $avatarSizeMap[$s['avatar_size'] ?? 'md'] }};height:{{ $avatarSizeMap[$s['avatar_size'] ?? 'md'] }};border-radius:{{ $roundedMap[$s['avatar_rounded'] ?? 'full'] }}">
                                <img src="{{ asset('storage/' . $page->avatar) }}" alt="{{ $page->title }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center border-4 border-white shadow-lg mb-3 {{ $page->cover_image ? 'relative z-10' : '' }}"
                                 style="width:{{ $avatarSizeMap[$s['avatar_size'] ?? 'md'] }};height:{{ $avatarSizeMap[$s['avatar_size'] ?? 'md'] }};border-radius:{{ $roundedMap[$s['avatar_rounded'] ?? 'full'] }}">
                                <span class="text-xl font-bold text-white">{{ strtoupper(substr($page->title, 0, 2)) }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <h1 style="text-align:{{ $s['title_align'] ?? 'center' }};font-size:{{ $sizeMap[$s['title_size'] ?? '2xl'] }};color:{{ $s['title_color'] ?? '#1f2937' }};font-weight:{{ ($s['title_bold'] ?? true) ? '700' : '400' }};margin-bottom:0.375rem">
                    {{ $page->title }}
                </h1>

                @if($page->bio)
                    <p style="text-align:{{ $s['bio_align'] ?? 'center' }};font-size:{{ $sizeMap[$s['bio_size'] ?? 'sm'] }};color:{{ $s['bio_color'] ?? '#6b7280' }};font-weight:{{ ($s['bio_bold'] ?? false) ? '700' : '400' }};margin-bottom:1.5rem;line-height:1.6">
                        {{ $page->bio }}
                    </p>
                @endif

                <div class="space-y-3">
                    @foreach($links as $link)
                        @php
                            $type = $link->type ?? 'link';
                            $es = $link->elem_style ?? [];
                            $isActive = $link->is_active ?? true;
                            $linkUrl = trim((string) ($link->url ?? ''));
                            $linkIsExternal = (bool) preg_match('/^(https?:|mailto:|tel:)/i', $linkUrl) || (!preg_match('/^(https?:|mailto:|tel:|#|\/)/i', $linkUrl) && $linkUrl !== '');
                        @endphp

                        @continue(!$isActive)

                        @if($type === 'text')
                            <div style="text-align:{{ $es['align'] ?? 'left' }};font-size:{{ $sizeMap[$es['size'] ?? 'sm'] ?? '0.875rem' }};color:{{ $es['color'] ?? '#374151' }};font-weight:{{ ($es['bold'] ?? false) ? '700' : '400' }};line-height:1.6">
                                {!! $link->content !!}
                            </div>
                        @elseif($type === 'image' && $link->image_path)
                            <div style="text-align:{{ $es['align'] ?? 'center' }}">
                                <img src="{{ asset('storage/' . $link->image_path) }}"
                                     alt=""
                                     style="width:{{ (int) ($es['scale'] ?? 100) }}%;display:inline-block;border-radius:{{ $roundedMap[$es['rounded'] ?? 'lg'] ?? '8px' }};{{ !empty($es['border']) ? 'border:1px solid rgba(229,231,235,1);' : '' }}{{ !empty($es['shadow']) ? 'box-shadow:0 6px 20px rgba(0,0,0,.12);' : '' }}">
                            </div>
                        @elseif($type === 'catalog')
                            @php
                                $catalogCount = max(1, min(6, (int) ($es['catalog_layout'] ?? 4)));
                                $catalogItems = array_slice($es['catalog_items'] ?? [], 0, $catalogCount);
                                $bgColor = $es['bg_color'] ?? '#f8fafc';
                                $textColor = $es['text_color'] ?? '#111827';
                                $mainCard = $es['catalog_main_card'] ?? true;
                                $shadow = ($mainCard && !empty($es['shadow'])) ? 'box-shadow:0 2px 10px rgba(0,0,0,.15)' : '';
                                $showPrice = $es['catalog_show_price'] ?? true;
                                $shellStyle = $mainCard ? "background:{$bgColor};color:{$textColor};{$shadow}" : '';
                            @endphp
                            <div class="{{ $mainCard ? 'rounded-3xl overflow-hidden border border-white/60' : 'w-full' }}" style="{{ $shellStyle }}">
                                <div class="{{ $mainCard ? 'p-4' : '' }}">
                                    @if($mainCard && $link->label)
                                        <div class="text-sm font-semibold leading-tight {{ $mainCard ? 'mb-4' : 'mb-3' }}">{{ $link->label }}</div>
                                    @endif
                                    <div class="grid {{ $catalogCount === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-3">
                                        @foreach($catalogItems as $item)
                                            @php
                                                $itemUrl = trim((string) ($item['url'] ?? ''));
                                                $itemIsExternal = (bool) preg_match('/^(https?:|mailto:|tel:)/i', $itemUrl) || (!preg_match('/^(https?:|mailto:|tel:|#|\/)/i', $itemUrl) && $itemUrl !== '');
                                                if (!preg_match('/^(https?:|mailto:|tel:|#|\/)/i', $itemUrl)) {
                                                    $itemUrl = 'https://' . ltrim($itemUrl);
                                                    $itemIsExternal = true;
                                                }
                                                $itemImage = $item['image_path'] ?? null;
                                            @endphp
                                            <a href="{{ $itemUrl }}" class="catalog-card block overflow-hidden rounded-2xl bg-white/90 text-gray-900 shadow-sm border border-black/5" @if(($link->opens_in_new_tab && !$isPreview) || $itemIsExternal) target="_blank" rel="noopener noreferrer" @endif>
                                                <div class="aspect-square bg-gray-50 overflow-hidden">
                                                    @if($itemImage)
                                                        <img src="{{ asset('storage/' . $itemImage) }}" class="w-full h-full object-cover" alt="{{ $item['label'] ?? 'Produk' }}" />
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                            <i class="fas fa-box text-2xl"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="p-3">
                                                    <div class="text-xs font-semibold leading-snug mb-1" style="min-height:2.4em">{{ $item['label'] ?? 'Produk' }}</div>
                                                    @if($showPrice)
                                                        <div class="text-[11px] font-bold text-orange-600 mb-2">{{ $item['price'] ?? 'Rp 0' }}</div>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            @php
                                $variant = $es['variant'] ?? 'solid';
                                $bgColor = $es['bg_color'] ?? '#111827';
                                $textColor = $es['text_color'] ?? '#ffffff';
                                $rounded = $roundedMap[$es['rounded'] ?? 'lg'] ?? '16px';
                                $shadow = !empty($es['shadow']) ? 'box-shadow:0 2px 10px rgba(0,0,0,.15)' : '';
                                $href = $isPreview ? ($link->url ?: '#') : route('landing-page.track', $link);
                                $styleBg = $bgColor;
                                $styleText = $textColor;
                                $styleBorder = '1px solid transparent';
                                if ($variant === 'outline') {
                                    $styleBg = 'transparent';
                                    $styleText = $bgColor;
                                    $styleBorder = '1px solid ' . $bgColor;
                                } elseif ($variant === 'soft') {
                                    $styleBg = 'rgba(17,24,39,.12)';
                                    $styleText = $bgColor;
                                } elseif ($variant === 'ghost') {
                                    $styleBg = 'transparent';
                                    $styleText = $bgColor;
                                }
                            @endphp
                                     <a href="{{ $href }}"
                               class="lnk-btn"
                               style="display:flex;width:100%;align-items:center;background:{{ $styleBg }};color:{{ $styleText }};border:{{ $styleBorder }};border-radius:{{ $rounded }};{{ $shadow }};padding:12px 16px;"
                                         @if(($link->opens_in_new_tab && !$isPreview) || $linkIsExternal) target="_blank" rel="noopener noreferrer" @endif>
                                @if($link->thumbnail)
                                    <img src="{{ asset('storage/' . $link->thumbnail) }}" class="w-8 h-8 rounded object-cover shrink-0" />
                                @elseif($link->icon)
                                    <i class="{{ $link->icon }} shrink-0 mr-3"></i>
                                @endif
                                <span style="flex:1;text-align:center;font-size:13px;font-weight:500">{{ $link->label }}</span>
                                <i class="fas fa-arrow-right" style="font-size:10px;opacity:.4;flex-shrink:0"></i>
                            </a>
                        @endif
                    @endforeach
                </div>

                @if($links->isEmpty())
                    <p class="text-center text-gray-400 text-sm mt-8">Belum ada elemen.</p>
                @endif

                <div class="mt-12 text-center">
                    <p style="font-size:11px;color:{{ $s['bio_color'] ?? '#9ca3af' }};opacity:.5">Powered by <span style="font-weight:600">Unlock</span></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

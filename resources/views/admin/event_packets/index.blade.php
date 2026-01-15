@extends('admin.layouts.app')

@section('title', 'Relasi Event–Paket')
@section('page_title', 'Relasi Event–Paket')
@section('page_subtitle', 'Atur paket mana saja yang tersedia di setiap event')

@section('content')
<div class="mb-4 flex justify-between items-center">
    <h2 class="text-base font-semibold text-slate-800">Relasi Event–Paket</h2>
    <a href="{{ route('admin.event-packets.create') }}"
       class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-purple-600 to-orange-500
              text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
        <i class="fa-solid fa-plus mr-2"></i> Tambah Relasi
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
        <form method="GET" class="flex items-center gap-2 flex-1">
            <select name="event_id"
                    class="px-3 py-2 border border-slate-200 rounded-lg text-xs text-slate-700
                           focus:outline-none focus:ring-2 focus:ring-purple-500/60 focus:border-purple-500">
                <option value="">Filter event</option>
                @foreach($events as $e)
                    <option value="{{ $e->id }}" @selected(request('event_id') == $e->id)>
                        {{ Str::limit($e->event_title, 40) }}
                    </option>
                @endforeach
            </select>
        </form>
        <span class="text-xs text-slate-500">
            Total: {{ $rows->total() }} relasi
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase tracking-wide text-slate-500">
                <th class="px-4 py-2 text-left">Event</th>
                <th class="px-4 py-2 text-left">Paket</th>
                <th class="px-4 py-2 text-left">Harga Override</th>
                <th class="px-4 py-2 text-left">Urutan</th>
                <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($rows as $row)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-4 py-2">
                        <div class="font-semibold text-slate-800">
                            {{ Str::limit($row->event->event_title ?? '-', 50) }}
                        </div>
                        <div class="text-[11px] text-slate-500">
                            {{ $row->event->event_code ?? '' }}
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        <div class="font-semibold text-slate-800">
                            {{ $row->packet->name ?? '-' }}
                        </div>
                        <div class="text-[11px] text-slate-500">
                            Base: Rp {{ number_format($row->packet->price ?? 0, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="px-4 py-2 text-sm text-slate-800">
                        @if($row->price_override)
                            Rp {{ number_format($row->price_override, 0, ',', '.') }}
                        @else
                            <span class="text-xs text-slate-400 italic">Mengikuti harga paket</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-xs text-slate-700">
                        {{ $row->sort_order ?? '-' }}
                    </td>
                    <td class="px-4 py-2 text-right whitespace-nowrap">
                        <a href="{{ route('admin.event-packets.edit', $row) }}"
                           class="inline-flex items-center px-2 py-1 text-xs rounded-lg border border-slate-200
                                  text-slate-700 hover:border-purple-500 hover:text-purple-600 mr-1">
                            <i class="fa-solid fa-pen-to-square mr-1"></i>Edit
                        </a>
                        <form action="{{ route('admin.event-packets.destroy', $row) }}" method="POST"
                              class="inline-block"
                              onsubmit="return confirm('Hapus relasi ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-2 py-1 text-xs rounded-lg border border-red-200
                                           text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-trash mr-1"></i>Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                        Belum ada relasi event–paket.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-slate-100">
        {{ $rows->links() }}
    </div>
</div>
@endsection

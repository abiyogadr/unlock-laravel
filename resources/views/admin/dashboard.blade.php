@extends('layouts.admin')

@section('title', 'Unlock - Admin Dashboard')
@section('page-title', 'Dashboard')

@section('admin-content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <!-- Total Events -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Events</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total_events'] }}</p>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-alt text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Active Events -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Active Events</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['active_events'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Registrations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Registrations</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['total_registrations'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Admins -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Admins</p>
                <p class="text-3xl font-bold text-purple-600">{{ $stats['total_admins'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-shield text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Events -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-gray-100">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <div class="md:col-span-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Events</h3>
                <p class="text-sm text-gray-500 mt-1">
                    @if($status === 'all')
                        Semua event ({{ $events->total() }})
                    @elseif($status === 'active')
                        Event aktif ({{ $events->total() }})
                    @else
                        Event tidak aktif ({{ $events->total() }})
                    @endif
                </p>
            </div>
            
            <!-- Filter Dropdown -->
            <div class="flex items-center gap-2">
                <form method="GET" class="w-full">
                    <x-custom-select name="status" :value="request('status')" placeholder="Semua Status" onchange="this.form.submit()">
                        <x-custom-select-item val="all" label="Semua Status">Semua Status</x-custom-select-item>
                        <x-custom-select-item val="open" label="Open">
                            <span class="text-green-600">●</span> Open
                        </x-custom-select-item>
                        <x-custom-select-item val="close" label="Close">
                            <span class="text-gray-600">●</span> Close
                        </x-custom-select-item>
                    </x-custom-select>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Desktop Table View (Hidden on mobile) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($events as $event)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-2 whitespace-nowrap text-sm font-mono text-gray-900">{{ $event->event_code }}</td>
                    <td class="px-6 py-2 text-sm text-gray-700 max-w-xs truncate" title="{{ $event->event_title }}">
                        {{ Str::limit($event->event_title, 40) }}
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($event->date_start)->format('d M Y') }}
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $event->status === 'open' ? 'bg-green-100 text-green-800' : 
                               ($event->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('admin.events.edit', $event) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs rounded-lg hover:bg-blue-200 transition">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50 block"></i>
                        <p>Belum ada event {{ $status === 'all' ? '' : strtolower($status) }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Horizontal Scroll View -->
    <div class="md:hidden -mx-4 px-4">
        <div class="flex flex-col gap-4 py-2 px-4 w-full">
            @forelse($events as $event)
            <div class="w-full bg-white border border-gray-200 rounded-lg p-2 shadow-sm">
                <div class="grid grid-cols-5 gap-2">
                    <div class="col-span-4">
                        <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded">
                            {{ $event->event_code }}
                        </span>
                        <h4 class="text-sm font-semibold text-gray-800 mb-2 mt-2 line-clamp-2" title="{{ $event->event_title }}">
                            {{ Str::limit($event->event_title, 40) }}
                        </h4>
                        
                        <div class="flex items-center text-xs text-gray-500 mb-2">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ \Carbon\Carbon::parse($event->date_start)->format('d M Y') }}
                        </div>
                    </div>
                    <div class="col-span-1 flex flex-col item-center">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full w-full text-center
                            {{ $event->status === 'open' ? 'bg-green-100 text-green-800' : 
                            ($event->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($event->status) }}
                        </span>
                        <a href="{{ route('admin.events.edit', $event) }}" 
                            class="inline-flex items-center justify-center w-full px-3 py-2 mt-2 bg-blue-100 text-blue-700 text-xs rounded-lg hover:bg-blue-200 transition">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                    </div>
                </div> 
            </div>
            @empty
            <div class="w-full py-12 text-center text-gray-500">
                <i class="fas fa-calendar-times text-4xl mb-4 opacity-50 block"></i>
                <p>Belum ada event {{ $status === 'all' ? '' : strtolower($status) }}</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($events->hasPages())
    <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-100">
        {{ $events->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endsection

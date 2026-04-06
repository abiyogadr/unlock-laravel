@extends('layouts.admin')

@section('title', 'Unlock - Ecourse')
@section('page-title', 'Ecourse Dashboard')

@section('admin-content')
<div class="space-y-6" data-ecourse-dashboard>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 md:p-5 border-b border-gray-100 space-y-5">
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-[11px] uppercase font-semibold tracking-wide text-gray-500">Total Transaksi</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900" data-stat="total_transactions">0</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 shadow-sm border-l-4 border-l-blue-500">
                    <p class="text-[11px] uppercase font-semibold tracking-wide text-gray-500">Total User Beli</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600" data-stat="total_buyers">0</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 shadow-sm border-l-4 border-l-emerald-500">
                    <p class="text-[11px] uppercase font-semibold tracking-wide text-gray-500">Total Ecourse Dibeli</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600" data-stat="total_courses_sold">0</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
                    <p class="text-[11px] uppercase font-semibold tracking-wide text-gray-500">Total Revenue</p>
                    <p class="mt-2 text-xl md:text-2xl font-bold text-amber-600" data-stat="total_revenue">Rp 0</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Bulan</label>
                    <select class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white focus:ring-primary focus:border-primary" data-filter="month" data-month-select>
                        @foreach($monthOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($initialFilters['month'] === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Tipe Transaksi</label>
                    <select class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white focus:ring-primary focus:border-primary" data-filter="type">
                        <option value="all" @selected($initialFilters['type'] === 'all')>Semua Tipe</option>
                        <option value="ecourse" @selected($initialFilters['type'] === 'ecourse')>Ecourse</option>
                        <option value="package" @selected($initialFilters['type'] === 'package')>Package</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Search</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        <input
                            type="text"
                            value="{{ $initialFilters['search'] }}"
                            placeholder="ID transaksi / judul / customer"
                            class="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 rounded-lg focus:ring-primary focus:border-primary"
                            data-filter="search"
                        >
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 lg:justify-end lg:col-span-2">
                    <button type="button" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition cursor-pointer" data-summary-open>
                        <i class="fas fa-chart-pie mr-2"></i>Summary
                    </button>
                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200 transition cursor-pointer" data-reset-filters>
                        <i class="fas fa-rotate-left mr-2"></i>Reset
                    </button>
                    <button type="button" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition cursor-pointer" data-download-csv>
                        <i class="fas fa-file-csv mr-2"></i>Download CSV
                    </button>
                </div>
            </div>
        </div>

        <div class="px-4 md:px-5 py-3 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-sm text-gray-500">
            <div data-summary-text>Memuat data transaksi...</div>
            <div class="flex items-center gap-2" data-loading-indicator hidden>
                <i class="fas fa-spinner animate-spin"></i>
                <span>Memperbarui data</span>
            </div>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-4 text-left">No</th>
                        <th class="px-5 py-4 text-left">ID Transaksi</th>
                        <th class="px-5 py-4 text-left">Judul</th>
                        <th class="px-5 py-4 text-left">Tipe</th>
                        <th class="px-5 py-4 text-left">Nama Cust</th>
                        <th class="px-5 py-4 text-left">Tanggal</th>
                        <th class="px-5 py-4 text-left">Revenue</th>
                        <th class="px-5 py-4 text-left">Status</th>
                        <th class="px-5 py-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" data-table-body>
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center text-gray-400">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-100" data-mobile-list>
            <div class="p-5 text-center text-gray-400">Memuat data...</div>
        </div>

        <div class="px-4 md:px-5 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between gap-3" data-pagination hidden>
            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 text-gray-700 hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed" data-page-action="prev">
                <i class="fas fa-arrow-left mr-2"></i>Sebelumnya
            </button>
            <div class="text-sm text-gray-500 font-medium" data-page-label>Halaman 1</div>
            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 text-gray-700 hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed" data-page-action="next">
                Berikutnya<i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden" data-detail-modal>
        <div class="absolute inset-0 bg-black/50" data-close-modal></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl max-h-[80vh] mt-6 bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide font-semibold text-gray-500">Detail transaksi</p>
                        <h3 class="text-lg font-bold text-gray-900" data-detail-title>Memuat...</h3>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-700 cursor-pointer" data-close-modal>
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[calc(80vh-96px)]" data-detail-content>
                    <div class="text-sm text-gray-500">Memuat detail transaksi...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden" data-summary-modal>
        <div class="absolute inset-0 bg-black/50" data-summary-close></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-5xl max-h-[80vh] mt-6 bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900" data-summary-title>Ringkasan Bulanan</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <select class="w-full sm:w-56 text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white focus:ring-primary focus:border-primary" data-summary-month></select>
                        <button type="button" class="text-gray-400 hover:text-gray-700 cursor-pointer" data-summary-close>
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="p-2 md:p-4 overflow-y-auto max-h-[calc(80vh-96px)] space-y-3" data-summary-content>
                    <div class="text-sm text-gray-500">Memuat summary...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="ecourse-dashboard-config">
    {!! json_encode(['endpoints' => $endpoints, 'filters' => $initialFilters, 'summaryDefaultMonth' => $summaryDefaultMonth, 'summaryMonthOptions' => $summaryMonthOptions], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>
@endsection

@push('scripts')
<script>
    (() => {
        const root = document.querySelector('[data-ecourse-dashboard]');
        const configNode = document.getElementById('ecourse-dashboard-config');

        if (!root || !configNode) {
            return;
        }

        const config = JSON.parse(configNode.textContent);

        const state = {
            filters: { ...config.filters },
            page: 1,
            pagination: null,
            requestId: 0,
        };

        const elements = {
            summaryText: root.querySelector('[data-summary-text]'),
            loadingIndicator: root.querySelector('[data-loading-indicator]'),
            tableBody: root.querySelector('[data-table-body]'),
            mobileList: root.querySelector('[data-mobile-list]'),
            pagination: root.querySelector('[data-pagination]'),
            pageLabel: root.querySelector('[data-page-label]'),
            prevButton: root.querySelector('[data-page-action="prev"]'),
            nextButton: root.querySelector('[data-page-action="next"]'),
            monthSelect: root.querySelector('[data-month-select]'),
            filterInputs: {
                type: root.querySelector('[data-filter="type"]'),
                month: root.querySelector('[data-filter="month"]'),
                search: root.querySelector('[data-filter="search"]'),
            },
            resetButton: root.querySelector('[data-reset-filters]'),
            downloadButton: root.querySelector('[data-download-csv]'),
            summaryButton: root.querySelector('[data-summary-open]'),
            modal: root.querySelector('[data-detail-modal]'),
            detailTitle: root.querySelector('[data-detail-title]'),
            detailContent: root.querySelector('[data-detail-content]'),
            summaryModal: root.querySelector('[data-summary-modal]'),
            summaryContent: root.querySelector('[data-summary-content]'),
            summaryTitle: root.querySelector('[data-summary-title]'),
            summaryMonth: root.querySelector('[data-summary-month]'),
            statNodes: {
                total_transactions: root.querySelector('[data-stat="total_transactions"]'),
                total_buyers: root.querySelector('[data-stat="total_buyers"]'),
                total_courses_sold: root.querySelector('[data-stat="total_courses_sold"]'),
                total_revenue: root.querySelector('[data-stat="total_revenue"]'),
            },
        };

        const statusClasses = {
            paid: 'bg-green-100 text-green-700',
            pending: 'bg-yellow-100 text-yellow-700',
            failed: 'bg-red-100 text-red-700',
            expired: 'bg-gray-200 text-gray-700',
            canceled: 'bg-red-100 text-red-700',
        };

        const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
        const formatCurrency = (value) => `Rp ${formatNumber(value)}`;
        const formatPercent = (value) => `${Number(value || 0).toFixed(1)}%`;

        const escapeHtml = (value) => String(value ?? '-')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const buildQuery = (page = 1) => {
            const params = new URLSearchParams({
                month: state.filters.month,
                type: state.filters.type,
                search: state.filters.search,
                page: String(page),
                per_page: String(state.filters.per_page || 15),
            });

            return params.toString();
        };

        const syncInputsToState = () => {
            state.filters.month = elements.filterInputs.month.value;
            state.filters.type = elements.filterInputs.type.value;
            state.filters.search = elements.filterInputs.search.value.trim();
        };

        const resetFilters = () => {
            state.filters = {
                ...config.filters,
                search: '',
                type: 'all',
            };
            state.page = 1;
            elements.filterInputs.type.value = state.filters.type;
            elements.filterInputs.month.value = state.filters.month;
            elements.filterInputs.search.value = state.filters.search;
            fetchData();
        };

        const renderStats = (stats) => {
            elements.statNodes.total_transactions.textContent = formatNumber(stats.total_transactions);
            elements.statNodes.total_buyers.textContent = formatNumber(stats.total_buyers);
            elements.statNodes.total_courses_sold.textContent = formatNumber(stats.total_courses_sold);
            elements.statNodes.total_revenue.textContent = formatCurrency(stats.total_revenue);
        };

        const renderTable = (transactions) => {
            if (!transactions.length) {
                elements.tableBody.innerHTML = '<tr><td colspan="9" class="px-5 py-10 text-center text-gray-400">Tidak ada transaksi untuk filter ini.</td></tr>';
                return;
            }

            elements.tableBody.innerHTML = transactions.map((transaction) => `
                <tr class="hover:bg-gray-50 transition text-gray-700">
                    <td class="px-5 py-4 font-semibold">${transaction.number}</td>
                    <td class="px-5 py-4">
                        <div class="font-semibold text-xs text-gray-900">${escapeHtml(transaction.transaction_id)}</div>
                    </td>
                    <td class="px-5 py-4 min-w-[240px]">
                        <div class="font-semibold text-xs text-gray-900">${escapeHtml(transaction.title)}</div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold text-xs bg-slate-100 text-slate-700 uppercase">${escapeHtml(transaction.type)}</span>
                    </td>
                    <td class="px-5 py-4"><div class="font-semibold text-xs text-gray-800">${escapeHtml(transaction.customer_name)}</div></td>
                    <td class="px-5 py-4 whitespace-nowrap text-gray-500"><div class="font-semibold text-xs text-gray-800">${escapeHtml(transaction.date)}</div></td>
                    <td class="px-5 py-4 font-semibold text-emerald-600 whitespace-nowrap"><div class="font-semibold text-xs text-emerald-600">${formatCurrency(transaction.revenue)}</div></td>
                    <td class="px-5 py-4">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold text-xs ${statusClasses[transaction.status] || 'bg-gray-100 text-gray-700'}">${escapeHtml(transaction.status_label)}</span>
                    </td>
                    <td class="px-5 py-4">
                        <button type="button" class="px-3 py-2 text-xs font-semibold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition cursor-pointer" data-view-transaction="${transaction.id}">Lihat</button>
                    </td>
                </tr>
            `).join('');
        };

        const renderMobile = (transactions) => {
            if (!transactions.length) {
                elements.mobileList.innerHTML = '<div class="p-5 text-center text-gray-400">Tidak ada transaksi untuk filter ini.</div>';
                return;
            }

            elements.mobileList.innerHTML = transactions.map((transaction) => `
                <div class="p-4 space-y-3 bg-white">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-mono text-gray-400 mb-1">${escapeHtml(transaction.transaction_id)}</div>
                            <div class="font-bold text-xs text-gray-900">${escapeHtml(transaction.title)}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold text-xs ${statusClasses[transaction.status] || 'bg-gray-100 text-gray-700'}">${escapeHtml(transaction.status_label)}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs text-gray-600 bg-gray-50 rounded-xl p-3">
                        <div>
                            <div class="text-[10px] uppercase font-semibold text-gray-400">Tipe</div>
                            <div class="font-semibold text-xs text-gray-800">${escapeHtml(transaction.type)}</div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-semibold text-gray-400">Revenue</div>
                            <div class="font-semibold text-xs text-emerald-600">${formatCurrency(transaction.revenue)}</div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-semibold text-gray-400">Customer</div>
                            <div class="font-semibold text-xs text-gray-800">${escapeHtml(transaction.customer_name)}</div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-semibold text-gray-400">Tanggal</div>
                            <div class="font-semibold text-xs text-gray-800">${escapeHtml(transaction.date)}</div>
                        </div>
                    </div>
                    <button type="button" class="w-full px-3 py-2 text-sm font-semibold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition" data-view-transaction="${transaction.id}">Lihat Detail</button>
                </div>
            `).join('');
        };

        const renderPagination = (pagination) => {
            state.pagination = pagination;

            if (!pagination || pagination.last_page <= 1) {
                elements.pagination.hidden = true;
                return;
            }

            elements.pagination.hidden = false;
            elements.pageLabel.textContent = `Halaman ${pagination.current_page} dari ${pagination.last_page}`;
            elements.prevButton.disabled = pagination.current_page <= 1;
            elements.nextButton.disabled = pagination.current_page >= pagination.last_page;
        };

        const renderSummary = (pagination) => {
            if (!pagination || !pagination.total) {
                elements.summaryText.textContent = 'Tidak ada transaksi yang cocok dengan filter saat ini.';
                return;
            }

            elements.summaryText.textContent = `Menampilkan ${pagination.from}-${pagination.to} dari ${pagination.total} transaksi.`;
        };

        const setLoading = (loading) => {
            elements.loadingIndicator.hidden = !loading;
        };

        const fetchData = async (page = 1) => {
            state.page = page;
            const requestId = ++state.requestId;
            setLoading(true);

            try {
                const response = await fetch(`${config.endpoints.data}?${buildQuery(page)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat data transaksi.');
                }

                const payload = await response.json();

                if (requestId !== state.requestId) {
                    return;
                }

                renderStats(payload.stats);
                renderTable(payload.transactions);
                renderMobile(payload.transactions);
                renderPagination(payload.pagination);
                renderSummary(payload.pagination);
            } catch (error) {
                elements.tableBody.innerHTML = '<tr><td colspan="9" class="px-5 py-10 text-center text-red-500">Gagal memuat data transaksi.</td></tr>';
                elements.mobileList.innerHTML = '<div class="p-5 text-center text-red-500">Gagal memuat data transaksi.</div>';
                elements.summaryText.textContent = error.message;
                elements.pagination.hidden = true;
            } finally {
                if (requestId === state.requestId) {
                    setLoading(false);
                }
            }
        };

        const openModal = () => {
            elements.modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            elements.modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        const openSummaryModal = () => {
            elements.summaryModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeSummaryModal = () => {
            elements.summaryModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        const renderSummaryModal = (payload) => {
            elements.summaryTitle.textContent = `Ringkasan ${payload.month_label}`;
            elements.summaryMonth.innerHTML = payload.month_options.map((option) => `
                <option value="${option.value}" ${option.value === payload.month ? 'selected' : ''}>${escapeHtml(option.label)}</option>
            `).join('');

            const overview = payload.overview || {};
            const status = payload.status_breakdown || {};
            const types = payload.type_breakdown || {};
            const topItems = payload.top_items || [];

            const statusCards = Object.values(status).map((item) => `
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-600">${escapeHtml(item.label)}</p>
                    <p class="mt-1 text-lg font-bold text-gray-900">${formatNumber(item.count)}</p>
                    <p class="text-xs text-gray-600 mt-1">${formatCurrency(item.amount)}</p>
                </div>
            `).join('');

            const typeCards = Object.values(types).map((item) => `
                <div class="bg-slate-900 rounded-xl p-4 text-white">
                    <p class="text-[10px] uppercase font-semibold tracking-wide text-white/60">${escapeHtml(item.label)}</p>
                    <p class="mt-1 text-lg font-bold">${formatNumber(item.count)}</p>
                    <p class="text-xs text-white/70 mt-1">${formatCurrency(item.amount)}</p>
                </div>
            `).join('');

            const topItemRows = topItems.map((item, index) => `
                <tr class="border-t border-gray-100">
                    <td class="py-3 px-4 text-gray-500">${index + 1}</td>
                    <td class="py-3 pr-3">
                        <div class="font-semibold text-gray-900">${escapeHtml(item.title)}</div>
                        <div class="text-xs text-gray-500 uppercase">${escapeHtml(item.type)}</div>
                    </td>
                    <td class="py-3 pr-3 text-right font-semibold text-gray-900">${formatNumber(item.count)}</td>
                    <td class="py-3 px-4 text-right font-semibold text-emerald-600">${formatCurrency(item.amount)}</td>
                </tr>
            `).join('');

            const summaryRows = [
                ['Total Transaksi', formatNumber(overview.total_transactions), 'text-gray-900'],
                ['Paid Transaction', formatNumber(overview.paid_transactions), 'text-emerald-600'],
                ['Unique Buyers', formatNumber(overview.unique_buyers), 'text-blue-600'],
                ['Avg Ticket', formatCurrency(overview.average_ticket), 'text-gray-900'],
                ['Net Revenue', formatCurrency(overview.net_revenue), 'text-slate-900'],
            ].map(([label, value, valueClass]) => `
                <div class="flex items-center justify-between gap-4 py-1.5 md:py-3 border-b border-gray-100 last:border-b-0">
                    <span class="text-sm font-medium text-gray-600">${label}</span>
                    <span class="text-sm font-semibold ${valueClass}">${value}</span>
                </div>
            `).join('');

            const statusRows = Object.values(status).map((item) => `
                <div class="flex items-center justify-between gap-4 py-1.5 md:py-2 border-b border-gray-100 last:border-b-0">
                    <div>
                        <div class="text-sm font-medium text-gray-700">${escapeHtml(item.label)}</div>
                        <div class="text-xs text-gray-500">Status transaksi</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-900">${formatNumber(item.count)} trx</div>
                        <div class="text-xs text-gray-600">${formatCurrency(item.amount)}</div>
                    </div>
                </div>
            `).join('');

            const typeRows = Object.values(types).map((item) => `
                <div class="flex items-center justify-between gap-4 py-1.5 md:py-2 border-b border-gray-100 last:border-b-0">
                    <div>
                        <div class="text-sm font-medium text-gray-700">${escapeHtml(item.label)}</div>
                        <div class="text-xs text-gray-500">Breakdown tipe transaksi</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-900">${formatNumber(item.count)} trx</div>
                        <div class="text-xs text-gray-600">${formatCurrency(item.amount)}</div>
                    </div>
                </div>
            `).join('');

            elements.summaryContent.innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-[320px_minmax(0,1fr)] gap-4">
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                            <div class="px-4 py-1 md:py-3 border-b border-gray-100">
                                <h4 class="font-bold text-gray-900">Overview</h4>
                            </div>
                            <div class="px-4">${summaryRows}</div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <h4 class="font-bold text-gray-900">Status Breakdown</h4>
                                </div>
                                <div class="px-4">${statusRows}</div>
                            </div>

                            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <h4 class="font-bold text-gray-900">Type Breakdown</h4>
                                </div>
                                <div class="px-4">${typeRows}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <h4 class="font-bold text-gray-900">Top Item Revenue</h4>
                            <span class="text-xs text-gray-500">Top 5</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                                    <tr>
                                        <th class="text-left px-4 py-3">#</th>
                                        <th class="text-left px-4 py-3">Item</th>
                                        <th class="text-right px-4 py-3">Qty</th>
                                        <th class="text-right px-4 py-3">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${topItemRows || '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        };

        const fetchSummary = async (month = config.summaryDefaultMonth) => {
            elements.summaryContent.innerHTML = '<div class="text-sm text-gray-500">Memuat summary...</div>';

            const response = await fetch(`${config.endpoints.summary}?month=${encodeURIComponent(month)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Gagal memuat summary transaksi.');
            }

            const payload = await response.json();
            renderSummaryModal(payload);
        };

        const renderDetail = (transaction) => {
            const metaRows = Object.entries(transaction.meta || {}).map(([key, value]) => `
                <div class="flex items-start justify-between gap-4 py-2 border-b border-gray-100 last:border-b-0">
                    <dt class="text-sm font-semibold text-gray-500 uppercase tracking-wide">${escapeHtml(key.replaceAll('_', ' '))}</dt>
                    <dd class="text-sm text-gray-800 text-right break-all">${escapeHtml(Array.isArray(value) || typeof value === 'object' ? JSON.stringify(value) : value)}</dd>
                </div>
            `).join('');

            elements.detailTitle.textContent = transaction.title;
            elements.detailContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">ID Transaksi</p>
                        <p class="mt-1 font-bold text-gray-900 text-sm break-all">${escapeHtml(transaction.transaction_id)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">Status</p>
                        <p class="mt-1 font-bold text-gray-900 text-sm">${escapeHtml(transaction.status_label)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">Revenue</p>
                        <p class="mt-1 font-bold text-emerald-600 text-sm">${formatCurrency(transaction.revenue)} ${escapeHtml(transaction.currency)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">Customer</p>
                        <p class="mt-1 font-bold text-gray-900 text-sm">${escapeHtml(transaction.customer_name)}</p>
                        <p class="text-xs text-gray-500 truncate">${escapeHtml(transaction.customer_email)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">Tipe / Payment</p>
                        <p class="mt-1 font-bold text-gray-900 text-sm">${escapeHtml(transaction.type)}</p>
                        <p class="text-xs text-gray-500 truncate">${escapeHtml(transaction.payment_method)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">Tanggal</p>
                        <p class="mt-1 font-bold text-gray-900 text-sm">${escapeHtml(transaction.created_at)}</p>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <h4 class="text-sm font-bold uppercase tracking-wide text-gray-500">Info Item</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">Course / Package</p>
                            <p class="mt-1 font-bold text-gray-900 text-sm">${escapeHtml(transaction.course_title || transaction.package_name || '-')}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500">Item ID</p>
                            <p class="mt-1 font-bold text-gray-900 text-sm">${escapeHtml(transaction.course_id ?? transaction.package_id ?? '-')}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-3">Meta</h4>
                    <div class="max-h-52 overflow-y-auto rounded-xl border border-gray-100 bg-white px-4 py-3 text-sm text-gray-700">
                        ${metaRows || '<div class="py-2 text-sm text-gray-400">Tidak ada metadata tambahan.</div>'}
                    </div>
                </div>
            `;
        };

        const fetchDetail = async (id) => {
            openModal();
            elements.detailTitle.textContent = 'Memuat...';
            elements.detailContent.innerHTML = '<div class="text-sm text-gray-500">Memuat detail transaksi...</div>';

            try {
                const response = await fetch(`${config.endpoints.detailBase}/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat detail transaksi.');
                }

                const payload = await response.json();
                renderDetail(payload.transaction);
            } catch (error) {
                elements.detailTitle.textContent = 'Detail transaksi';
                elements.detailContent.innerHTML = `<div class="text-sm text-red-500">${escapeHtml(error.message)}</div>`;
            }
        };

        elements.resetButton.addEventListener('click', resetFilters);

        elements.downloadButton.addEventListener('click', () => {
            syncInputsToState();
            window.location.href = `${config.endpoints.export}?${buildQuery(1)}`;
        });

        elements.summaryButton.addEventListener('click', async () => {
            openSummaryModal();

            if (!elements.summaryMonth.options.length) {
                elements.summaryMonth.innerHTML = config.summaryMonthOptions.map((option) => `
                    <option value="${option.value}" ${option.value === config.summaryDefaultMonth ? 'selected' : ''}>${escapeHtml(option.label)}</option>
                `).join('');
            }

            try {
                await fetchSummary(elements.summaryMonth.value || config.summaryDefaultMonth);
            } catch (error) {
                elements.summaryContent.innerHTML = `<div class="text-sm text-red-500">${escapeHtml(error.message)}</div>`;
            }
        });

        elements.summaryMonth.addEventListener('change', async (event) => {
            try {
                await fetchSummary(event.target.value);
            } catch (error) {
                elements.summaryContent.innerHTML = `<div class="text-sm text-red-500">${escapeHtml(error.message)}</div>`;
            }
        });

        let searchTimer = null;

        const triggerAutoFilter = () => {
            syncInputsToState();
            fetchData(1);
        };

        elements.filterInputs.month.addEventListener('change', triggerAutoFilter);
        elements.filterInputs.type.addEventListener('change', triggerAutoFilter);
        elements.filterInputs.search.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(triggerAutoFilter, 350);
        });

        elements.prevButton.addEventListener('click', () => {
            if (state.pagination?.current_page > 1) {
                fetchData(state.pagination.current_page - 1);
            }
        });

        elements.nextButton.addEventListener('click', () => {
            if (state.pagination?.current_page < state.pagination?.last_page) {
                fetchData(state.pagination.current_page + 1);
            }
        });

        root.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-view-transaction]');
            if (trigger) {
                fetchDetail(trigger.getAttribute('data-view-transaction'));
                return;
            }

            const closeTrigger = event.target.closest('[data-close-modal]');
            if (closeTrigger) {
                closeModal();
            }

            const summaryCloseTrigger = event.target.closest('[data-summary-close]');
            if (summaryCloseTrigger) {
                closeSummaryModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        fetchData();
    })();
</script>
@endpush
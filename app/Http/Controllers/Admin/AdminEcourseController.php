<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ecourse\CourseTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminEcourseController extends Controller
{
    public function index(Request $request)
    {
        $months = $this->buildMonthOptions();
        $summaryMonths = $this->buildSummaryMonthOptions();
        $selectedMonth = $this->resolveMonth($request->string('month')->toString(), $months);
        $summaryDefaultMonth = $this->resolveSummaryMonth(null, $summaryMonths);

        return view('admin.ecourse.index', [
            'monthOptions' => $months,
            'summaryMonthOptions' => $summaryMonths,
            'initialFilters' => [
                'month' => $selectedMonth,
                'type' => $request->string('type')->toString() ?: 'all',
                'search' => $request->string('search')->toString(),
                'per_page' => 15,
            ],
            'summaryDefaultMonth' => $summaryDefaultMonth,
            'endpoints' => [
                'data' => route('admin.ecourse.data'),
                'summary' => route('admin.ecourse.summary'),
                'export' => route('admin.ecourse.export'),
                'detailBase' => url('/upanel/ecourse/transactions'),
            ],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $months = $this->buildMonthOptions();
        $filters = $this->extractFilters($request, $months);
        $baseQuery = $this->buildFilteredQuery($filters);

        $transactions = (clone $baseQuery)
            ->latest('created_at')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return response()->json([
            'filters' => $filters,
            'stats' => $this->buildStats($baseQuery),
            'transactions' => collect($transactions->items())->map(function (CourseTransaction $transaction, int $index) use ($transactions) {
                return $this->transformTransactionRow($transaction, $index, $transactions->currentPage(), $transactions->perPage());
            })->values(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
        ]);
    }

    public function show(CourseTransaction $transaction): JsonResponse
    {
        $transaction->loadMissing(['user:id,name,email', 'course:id,title', 'package:id,name']);

        return response()->json([
            'transaction' => [
                'id' => $transaction->id,
                'transaction_id' => $this->resolveTransactionId($transaction),
                'title' => $this->resolveItemTitle($transaction),
                'type' => $this->resolveItemType($transaction),
                'customer_name' => $transaction->user?->name ?? '-',
                'customer_email' => $transaction->user?->email ?? '-',
                'status' => $transaction->status,
                'status_label' => $this->formatStatus($transaction->status),
                'revenue' => (float) $transaction->amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method ?: '-',
                'course_title' => $transaction->course?->title,
                'package_name' => $transaction->package?->name,
                'course_id' => $transaction->course_id,
                'package_id' => $transaction->package_id,
                'subscription_id' => $transaction->user_subscription_id,
                'created_at' => optional($transaction->created_at)?->format('d M Y H:i'),
                'updated_at' => optional($transaction->updated_at)?->format('d M Y H:i'),
                'meta' => $transaction->meta ?? [],
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $months = $this->buildSummaryMonthOptions();
        $selectedMonth = $this->resolveSummaryMonth($request->string('month')->toString(), $months);
        $monthLabel = $this->monthLabelForValue($selectedMonth, $months);

        $query = $this->buildSummaryQuery($selectedMonth);

        $paidQuery = (clone $query)->where('status', 'paid');
        $failedQuery = (clone $query)->whereIn('status', ['failed', 'expired', 'canceled']);

        $typeBreakdown = [
            'ecourse' => $this->summaryBreakdownForType((clone $paidQuery), 'ecourse'),
            'package' => $this->summaryBreakdownForType((clone $paidQuery), 'package'),
        ];

        $statusBreakdown = [
            'paid' => ['label' => 'Paid', 'count' => (clone $paidQuery)->count(), 'amount' => (float) (clone $paidQuery)->sum('amount')],
            'pending' => ['label' => 'Pending', 'count' => (clone $query)->where('status', 'pending')->count(), 'amount' => (float) (clone $query)->where('status', 'pending')->sum('amount')],
            'failed' => ['label' => 'Failed', 'count' => $failedQuery->count(), 'amount' => (float) $failedQuery->sum('amount')],
        ];

        $topItems = $this->buildTopItems((clone $paidQuery));

        $recentTransactions = (clone $query)
            ->with(['user:id,name,email', 'course:id,title', 'package:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (CourseTransaction $transaction) => [
                'id' => $transaction->id,
                'transaction_id' => $this->resolveTransactionId($transaction),
                'title' => $this->resolveItemTitle($transaction),
                'type' => $this->resolveItemType($transaction),
                'customer_name' => $transaction->user?->name ?? '-',
                'amount' => (float) $transaction->amount,
                'status' => $transaction->status,
                'status_label' => $this->formatStatus($transaction->status),
                'created_at' => optional($transaction->created_at)?->format('d M Y H:i'),
            ]);

        return response()->json([
            'month' => $selectedMonth,
            'month_label' => $monthLabel,
            'month_options' => $months,
            'overview' => [
                'total_transactions' => (clone $query)->count(),
                'paid_transactions' => (clone $paidQuery)->count(),
                'unique_buyers' => (clone $paidQuery)->distinct()->count('user_id'),
                'total_revenue' => (float) (clone $paidQuery)->sum('amount'),
                'average_ticket' => (float) ((clone $paidQuery)->count() > 0 ? (clone $paidQuery)->avg('amount') : 0),
                'net_revenue' => (float) (clone $paidQuery)->sum('amount'),
            ],
            'status_breakdown' => $statusBreakdown,
            'type_breakdown' => $typeBreakdown,
            'top_items' => $topItems,
            'recent_transactions' => $recentTransactions,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->extractFilters($request, $this->buildMonthOptions());
        $transactions = $this->buildFilteredQuery($filters)
            ->latest('created_at')
            ->get();

        $fileName = 'ecourse-transactions-' . ($filters['month'] === 'all' ? 'all' : $filters['month']) . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'ID Transaksi',
                'Judul',
                'Tipe',
                'Nama Customer',
                'Email',
                'Tanggal',
                'Revenue',
                'Currency',
                'Status',
                'Payment Method',
            ]);

            foreach ($transactions as $index => $transaction) {
                fputcsv($handle, [
                    $index + 1,
                    $this->resolveTransactionId($transaction),
                    $this->resolveItemTitle($transaction),
                    $this->resolveItemType($transaction),
                    $transaction->user?->name ?? '-',
                    $transaction->user?->email ?? '-',
                    optional($transaction->created_at)?->format('Y-m-d H:i:s'),
                    (float) $transaction->amount,
                    $transaction->currency,
                    $this->formatStatus($transaction->status),
                    $transaction->payment_method ?: '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery(array $filters): Builder
    {
        $query = CourseTransaction::query()
            ->with(['user:id,name,email', 'course:id,title', 'package:id,name']);

        if ($filters['month'] !== 'all') {
            $month = Carbon::createFromFormat('Y-m', $filters['month'])->startOfMonth();

            $query->whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ]);
        }

        if ($filters['type'] === 'ecourse') {
            $query->where(function (Builder $builder) {
                $builder->where(function (Builder $directCourseQuery) {
                    $directCourseQuery->whereNotNull('course_id')
                        ->whereNull('package_id');
                })->orWhereRaw(
                    "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.type')), '')) = ?",
                    ['course']
                );
            });
        }

        if ($filters['type'] === 'package') {
            $query->where(function (Builder $builder) {
                $builder->whereNotNull('package_id')
                    ->orWhereRaw(
                        "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.type')), '')) = ?",
                        ['package']
                    );
            });
        }

        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('external_reference', 'like', $search)
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    })
                    ->orWhereHas('course', function (Builder $courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', $search);
                    })
                    ->orWhereHas('package', function (Builder $packageQuery) use ($search) {
                        $packageQuery->where('name', 'like', $search);
                    })
                    ->orWhereRaw(
                        "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.item_name')), '') like ?",
                        [$search]
                    );
            });
        }

        return $query;
    }

    private function buildStats(Builder $baseQuery): array
    {
        $paidQuery = (clone $baseQuery)->where('status', 'paid');

        return [
            'total_transactions' => (clone $baseQuery)->count(),
            'total_buyers' => (clone $paidQuery)->distinct()->count('user_id'),
            'total_courses_sold' => (clone $paidQuery)
                ->where(function (Builder $builder) {
                    $builder->where(function (Builder $directCourseQuery) {
                        $directCourseQuery->whereNotNull('course_id')
                            ->whereNull('package_id');
                    })->orWhereRaw(
                        "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.type')), '')) = ?",
                        ['course']
                    );
                })
                ->count(),
            'total_revenue' => (float) (clone $paidQuery)->sum('amount'),
        ];
    }

    private function extractFilters(Request $request, array $months): array
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 50) : 15;

        return [
            'month' => $this->resolveMonth($request->string('month')->toString(), $months),
            'type' => in_array($request->string('type')->toString(), ['all', 'ecourse', 'package'], true)
                ? $request->string('type')->toString()
                : 'all',
            'search' => trim($request->string('search')->toString()),
            'per_page' => $perPage,
        ];
    }

    private function buildMonthOptions(): array
    {
        $months = CourseTransaction::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_value")
            ->distinct()
            ->orderByDesc('month_value')
            ->pluck('month_value')
            ->filter()
            ->values()
            ->all();

        if (!in_array(now()->format('Y-m'), $months, true)) {
            array_unshift($months, now()->format('Y-m'));
        }

        $months = array_values(array_unique($months));

        $options = [
            [
                'value' => 'all',
                'label' => 'Semua Bulan',
            ],
        ];

        foreach ($months as $month) {
            $options[] = [
                'value' => $month,
                'label' => Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y'),
            ];
        }

        return $options;
    }

    private function buildSummaryMonthOptions(): array
    {
        $months = CourseTransaction::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_value, COUNT(*) as total_rows")
            ->groupBy('month_value')
            ->havingRaw('COUNT(*) > 0')
            ->orderByDesc('month_value')
            ->pluck('month_value')
            ->filter()
            ->values()
            ->all();

        return array_map(function (string $month) {
            return [
                'value' => $month,
                'label' => Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y'),
            ];
        }, $months);
    }

    private function resolveSummaryMonth(?string $requestedMonth, array $months): string
    {
        $validMonths = collect($months)->pluck('value')->all();

        if ($requestedMonth && in_array($requestedMonth, $validMonths, true)) {
            return $requestedMonth;
        }

        return $validMonths[0] ?? now()->format('Y-m');
    }

    private function monthLabelForValue(string $monthValue, array $months): string
    {
        if ($monthValue === 'all') {
            return 'Semua Bulan';
        }

        return collect($months)->firstWhere('value', $monthValue)['label'] ?? $monthValue;
    }

    private function buildSummaryQuery(?string $monthValue): Builder
    {
        $query = CourseTransaction::query();

        if ($monthValue && $monthValue !== 'all') {
            $month = Carbon::createFromFormat('Y-m', $monthValue)->startOfMonth();

            $query->whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ]);
        }

        return $query;
    }

    private function summaryBreakdownForType(Builder $paidQuery, string $type): array
    {
        $typeQuery = (clone $paidQuery)->where(function (Builder $builder) use ($type) {
            if ($type === 'ecourse') {
                $builder->where(function (Builder $courseQuery) {
                    $courseQuery->whereNotNull('course_id')->whereNull('package_id');
                })->orWhereRaw("LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.type')), '')) = ?", ['course']);
            } else {
                $builder->whereNotNull('package_id')->orWhereRaw("LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.type')), '')) = ?", ['package']);
            }
        });

        return [
            'label' => ucfirst($type),
            'count' => $typeQuery->count(),
            'amount' => (float) $typeQuery->sum('amount'),
        ];
    }

    private function buildTopItems(Builder $paidQuery): array
    {
        return $paidQuery
            ->get()
            ->groupBy(fn (CourseTransaction $transaction) => $this->resolveItemTitle($transaction) . '|' . $this->resolveItemType($transaction))
            ->map(function ($group) {
                $sample = $group->first();

                return [
                    'title' => $this->resolveItemTitle($sample),
                    'type' => $this->resolveItemType($sample),
                    'count' => $group->count(),
                    'amount' => (float) $group->sum('amount'),
                ];
            })
            ->sortByDesc('amount')
            ->take(5)
            ->values()
            ->all();
    }

    private function resolveMonth(?string $requestedMonth, array $months): string
    {
        $validMonths = collect($months)->pluck('value')->all();

        if ($requestedMonth && in_array($requestedMonth, $validMonths, true)) {
            return $requestedMonth;
        }

        return 'all';
    }

    private function transformTransactionRow(CourseTransaction $transaction, int $index, int $currentPage, int $perPage): array
    {
        return [
            'number' => (($currentPage - 1) * $perPage) + $index + 1,
            'id' => $transaction->id,
            'transaction_id' => $this->resolveTransactionId($transaction),
            'title' => $this->resolveItemTitle($transaction),
            'type' => $this->resolveItemType($transaction),
            'customer_name' => $transaction->user?->name ?? '-',
            'date' => optional($transaction->created_at)?->format('d M Y H:i'),
            'revenue' => (float) $transaction->amount,
            'status' => $transaction->status,
            'status_label' => $this->formatStatus($transaction->status),
        ];
    }

    private function resolveTransactionId(CourseTransaction $transaction): string
    {
        return $transaction->external_reference ?: 'TRX-' . $transaction->id;
    }

    private function resolveItemTitle(CourseTransaction $transaction): string
    {
        return $transaction->course?->title
            ?? $transaction->package?->name
            ?? data_get($transaction->meta, 'item_name')
            ?? 'Item tidak diketahui';
    }

    private function resolveItemType(CourseTransaction $transaction): string
    {
        $metaType = strtolower((string) data_get($transaction->meta, 'type', ''));

        if ($metaType === 'package' || $transaction->package_id) {
            return 'package';
        }

        return 'ecourse';
    }

    private function formatStatus(?string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'canceled' => 'Canceled',
            default => ucfirst((string) $status),
        };
    }
}
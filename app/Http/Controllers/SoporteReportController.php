<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SoporteReportController extends Controller
{
    public function index(Request $request)
    {
        $to = $this->parseDate($request->input('to')) ?? Carbon::today();
        $from = $this->parseDate($request->input('from')) ?? $to->copy()->subDays(13);

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $status = (string) $request->input('status', '');
        $allowedStatus = ['paid', 'pending', 'cancelled'];

        $channel = (string) $request->input('channel', '');
        $allowedChannel = ['web', 'api', 'phone', 'email'];

        $base = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.sold_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        if (in_array($status, $allowedStatus, true)) {
            $base->where('sales.status', $status);
        } else {
            $status = '';
        }

        if (in_array($channel, $allowedChannel, true)) {
            $base->where('sales.channel', $channel);
        } else {
            $channel = '';
        }

        $counts = (clone $base)
            ->select('sales.status', DB::raw('count(*) as total'))
            ->groupBy('sales.status')
            ->pluck('total', 'status');

        $total = (int) $counts->sum();
        $paid = (int) ($counts['paid'] ?? 0);
        $pending = (int) ($counts['pending'] ?? 0);
        $cancelled = (int) ($counts['cancelled'] ?? 0);

        $paidRevenueCents = (int) ((clone $base)
            ->where('sales.status', 'paid')
            ->sum('sales.total_cents'));

        $uniqueCustomers = (int) ((clone $base)
            ->where('sales.status', 'paid')
            ->whereNotNull('sales.customer_id')
            ->distinct('sales.customer_id')
            ->count('sales.customer_id'));

        $avgOrderCents = $paid > 0 ? (int) round($paidRevenueCents / $paid) : 0;

        $series = (clone $base)
            ->selectRaw("date(sales.sold_at) as day")
            ->selectRaw("sum(case when sales.status = 'paid' then 1 else 0 end) as paid_orders")
            ->selectRaw("sum(case when sales.status = 'paid' then sales.total_cents else 0 end) as paid_revenue_cents")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $chartLabels = $series->pluck('day')->values();
        $chartPaidOrders = $series->pluck('paid_orders')->map(fn ($v) => (int) $v)->values();
        $chartPaidRevenue = $series->pluck('paid_revenue_cents')->map(fn ($v) => (float) ((int) $v / 100))->values();

        $sales = (clone $base)
            ->select([
                'sales.id',
                'sales.status',
                'sales.channel',
                'sales.sold_at',
                'sales.total_cents',
                DB::raw("coalesce(customers.name, 'Consumidor final') as customer_name"),
                DB::raw('(select sum(quantity) from sale_items where sale_items.sale_id = sales.id) as units'),
                DB::raw('(select count(*) from sale_items where sale_items.sale_id = sales.id) as items'),
            ])
            ->orderByDesc('sales.sold_at')
            ->limit(500)
            ->get();

        $rows = $sales->map(function ($r) {
            $soldAt = Carbon::parse($r->sold_at);
            $totalCents = (int) $r->total_cents;
            $total = number_format($totalCents / 100, 2, '.', ',');

            return [
                (int) $r->id,
                (string) $r->customer_name,
                (string) $r->status,
                (string) $r->channel,
                $soldAt->toDateTimeString(),
                (int) ($r->items ?? 0),
                (int) ($r->units ?? 0),
                'MXN '.$total,
            ];
        })->values();

        return view('reportes.soporte', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'status' => $status,
            'channel' => $channel,
            'kpis' => [
                'total_orders' => $total,
                'paid_orders' => $paid,
                'pending_orders' => $pending,
                'cancelled_orders' => $cancelled,
                'paid_revenue_cents' => $paidRevenueCents,
                'avg_order_cents' => $avgOrderCents,
                'unique_customers' => $uniqueCustomers,
            ],
            'chartLabels' => $chartLabels,
            'chartPaidOrders' => $chartPaidOrders,
            'chartPaidRevenue' => $chartPaidRevenue,
            'rows' => $rows,
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}

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
        $allowedChannel = ['web', 'api', 'phone', 'email', 'whatsapp'];

        $country = strtoupper((string) $request->input('country', ''));
        $allowedCountry = ['CO', 'MX', 'CL', 'AR', 'PE'];

        $segment = (string) $request->input('segment', '');
        $allowedSegment = ['SMB', 'Mid-Market', 'Enterprise'];

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

        if (in_array($country, $allowedCountry, true)) {
            $base->where('customers.country', $country);
        } else {
            $country = '';
        }

        if (in_array($segment, $allowedSegment, true)) {
            $base->where('customers.segment', $segment);
        } else {
            $segment = '';
        }

        $days = $from->diffInDays($to) + 1;
        $prevTo = $from->copy()->subDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1);

        $basePrev = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.sold_at', [$prevFrom->copy()->startOfDay(), $prevTo->copy()->endOfDay()]);

        if ($status !== '') {
            $basePrev->where('sales.status', $status);
        }

        if ($channel !== '') {
            $basePrev->where('sales.channel', $channel);
        }

        if ($country !== '') {
            $basePrev->where('customers.country', $country);
        }

        if ($segment !== '') {
            $basePrev->where('customers.segment', $segment);
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

        $paidPrev = (int) ((clone $basePrev)->where('sales.status', 'paid')->count());
        $paidRevenuePrevCents = (int) ((clone $basePrev)->where('sales.status', 'paid')->sum('sales.total_cents'));
        $uniqueCustomersPrev = (int) ((clone $basePrev)
            ->where('sales.status', 'paid')
            ->whereNotNull('sales.customer_id')
            ->distinct('sales.customer_id')
            ->count('sales.customer_id'));
        $avgOrderPrevCents = $paidPrev > 0 ? (int) round($paidRevenuePrevCents / $paidPrev) : 0;

        $pct = function (int $current, int $previous): ?float {
            if ($previous <= 0) {
                return null;
            }

            return (($current - $previous) / $previous) * 100;
        };

        $series = (clone $base)
            ->selectRaw("date(sales.sold_at) as day")
            ->selectRaw("sum(case when sales.status = 'paid' then 1 else 0 end) as paid_orders")
            ->selectRaw("sum(case when sales.status = 'paid' then sales.total_cents else 0 end) as paid_revenue_cents")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $chartLabels = $series->pluck('day')->values();
        $chartPaidOrders = $series->pluck('paid_orders')->map(fn ($v) => (int) $v)->values();
        $chartPaidRevenue = $series->pluck('paid_revenue_cents')->map(fn ($v) => (int) round(((int) $v) / 100))->values();

        $mix = (clone $base)
            ->where('sales.status', 'paid')
            ->whereNotNull('customers.country')
            ->select('customers.country')
            ->selectRaw('sum(sales.total_cents) as revenue_cents')
            ->groupBy('customers.country')
            ->orderByDesc('revenue_cents')
            ->limit(6)
            ->get();

        $mixCountryLabels = $mix->pluck('country')->values();
        $mixCountryRevenue = $mix->pluck('revenue_cents')->map(fn ($v) => (int) round(((int) $v) / 100))->values();

        $topChannels = (clone $base)
            ->where('sales.status', 'paid')
            ->select('sales.channel')
            ->selectRaw('sum(sales.total_cents) as revenue_cents')
            ->selectRaw('count(*) as orders')
            ->groupBy('sales.channel')
            ->orderByDesc('revenue_cents')
            ->limit(5)
            ->get();

        $topCustomers = (clone $base)
            ->where('sales.status', 'paid')
            ->whereNotNull('sales.customer_id')
            ->select('sales.customer_id', DB::raw("coalesce(customers.name, 'Consumidor final') as customer_name"))
            ->selectRaw('sum(sales.total_cents) as revenue_cents')
            ->selectRaw('count(*) as orders')
            ->groupBy('sales.customer_id', 'customer_name')
            ->orderByDesc('revenue_cents')
            ->limit(5)
            ->get();

        $pendingThresholdDays = 3;
        $pendingOlderThan = Carbon::now()->subDays($pendingThresholdDays);
        $pendingOldCount = (int) ((clone $base)
            ->where('sales.status', 'pending')
            ->where('sales.sold_at', '<=', $pendingOlderThan)
            ->count());

        $cancelRate = $total > 0 ? ($cancelled / $total) : 0.0;
        $cancelRateThreshold = 0.25;
        $cancelAlert = $total > 0 && $cancelRate >= $cancelRateThreshold;

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
            $totalPesos = (int) round($totalCents / 100);

            return [
                (int) $r->id,
                (string) $r->customer_name,
                (string) $r->status,
                (string) $r->channel,
                $soldAt->format('Y-m-d h:i A'),
                (int) ($r->items ?? 0),
                (int) ($r->units ?? 0),
                $totalPesos,
            ];
        })->values();

        return view('reportes.soporte', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'prevFrom' => $prevFrom->toDateString(),
            'prevTo' => $prevTo->toDateString(),
            'status' => $status,
            'channel' => $channel,
            'country' => $country,
            'segment' => $segment,
            'kpis' => [
                'total_orders' => $total,
                'paid_orders' => $paid,
                'pending_orders' => $pending,
                'cancelled_orders' => $cancelled,
                'paid_revenue_cents' => $paidRevenueCents,
                'avg_order_cents' => $avgOrderCents,
                'unique_customers' => $uniqueCustomers,
                'paid_orders_prev' => $paidPrev,
                'paid_orders_change_pct' => $pct($paid, $paidPrev),
                'paid_revenue_prev_cents' => $paidRevenuePrevCents,
                'paid_revenue_change_pct' => $pct($paidRevenueCents, $paidRevenuePrevCents),
                'avg_order_prev_cents' => $avgOrderPrevCents,
                'avg_order_change_pct' => $pct($avgOrderCents, $avgOrderPrevCents),
                'unique_customers_prev' => $uniqueCustomersPrev,
                'unique_customers_change_pct' => $pct($uniqueCustomers, $uniqueCustomersPrev),
                'pending_threshold_days' => $pendingThresholdDays,
                'pending_old_count' => $pendingOldCount,
                'cancel_rate' => $cancelRate,
                'cancel_rate_threshold' => $cancelRateThreshold,
                'cancel_alert' => $cancelAlert,
            ],
            'chartLabels' => $chartLabels,
            'chartPaidOrders' => $chartPaidOrders,
            'chartPaidRevenue' => $chartPaidRevenue,
            'mixCountryLabels' => $mixCountryLabels,
            'mixCountryRevenue' => $mixCountryRevenue,
            'topChannels' => $topChannels,
            'topCustomers' => $topCustomers,
            'rows' => $rows,
        ]);
    }

    public function export(Request $request)
    {
        $to = $this->parseDate($request->input('to')) ?? Carbon::today();
        $from = $this->parseDate($request->input('from')) ?? $to->copy()->subDays(13);

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $status = (string) $request->input('status', '');
        $allowedStatus = ['paid', 'pending', 'cancelled'];

        $channel = (string) $request->input('channel', '');
        $allowedChannel = ['web', 'api', 'phone', 'email', 'whatsapp'];

        $country = strtoupper((string) $request->input('country', ''));
        $allowedCountry = ['CO', 'MX', 'CL', 'AR', 'PE'];

        $segment = (string) $request->input('segment', '');
        $allowedSegment = ['SMB', 'Mid-Market', 'Enterprise'];

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

        if (in_array($country, $allowedCountry, true)) {
            $base->where('customers.country', $country);
        } else {
            $country = '';
        }

        if (in_array($segment, $allowedSegment, true)) {
            $base->where('customers.segment', $segment);
        } else {
            $segment = '';
        }

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
            ->limit(5000)
            ->get();

        $filename = 'reporte_ventas_'.$from->toDateString().'_a_'.$to->toDateString().'.csv';
        $statusLabels = [
            'paid' => 'Pagada',
            'pending' => 'Pendiente',
            'cancelled' => 'Cancelada',
        ];
        $channelLabels = [
            'web' => 'Web',
            'api' => 'API',
            'phone' => 'Teléfono',
            'email' => 'Correo',
            'whatsapp' => 'WhatsApp',
        ];

        return response()->streamDownload(function () use ($sales, $statusLabels, $channelLabels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID',
                'Cliente',
                'Estado',
                'Canal',
                'Fecha',
                'Ítems',
                'Unidades',
                'Total (COP)',
            ]);

            foreach ($sales as $r) {
                $soldAt = Carbon::parse($r->sold_at)->format('Y-m-d h:i A');
                $totalPesos = (int) round(((int) $r->total_cents) / 100);

                fputcsv($out, [
                    (int) $r->id,
                    (string) $r->customer_name,
                    (string) ($statusLabels[(string) $r->status] ?? (string) $r->status),
                    (string) ($channelLabels[(string) $r->channel] ?? (string) $r->channel),
                    $soldAt,
                    (int) ($r->items ?? 0),
                    (int) ($r->units ?? 0),
                    $totalPesos,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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

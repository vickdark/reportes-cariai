<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Reporte de Ventas</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

        <link rel="stylesheet" href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css">
        <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
    </head>

    <body class="bg-light text-dark">
        <div class="container py-4">
            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2 align-items-baseline justify-content-between">
                    <div>
                        <h1 class="h3 mb-1">Reporte de Ventas</h1>
                        <div class="text-body-secondary">
                            Rango: <span class="fw-semibold">{{ $from }}</span> a <span class="fw-semibold">{{ $to }}</span>
                            @if ($status !== '')
                                <span class="mx-1">·</span>
                                Estado: <span class="fw-semibold">{{ $status }}</span>
                            @endif
                            @if (($channel ?? '') !== '')
                                <span class="mx-1">·</span>
                                Canal: <span class="fw-semibold">{{ $channel }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-body-secondary small">
                        Actualizado: <span class="fw-semibold">{{ now()->toDateTimeString() }}</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Filtros</div>
                    <form method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label for="from" class="form-label">Desde</label>
                                <input id="from" name="from" type="date" value="{{ $from }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="to" class="form-label">Hasta</label>
                                <input id="to" name="to" type="date" value="{{ $to }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-2">
                                <label for="status" class="form-label">Estado</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="" @selected($status === '')>Todos</option>
                                    <option value="paid" @selected($status === 'paid')>paid</option>
                                    <option value="pending" @selected($status === 'pending')>pending</option>
                                    <option value="cancelled" @selected($status === 'cancelled')>cancelled</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <label for="channel" class="form-label">Canal</label>
                                <select id="channel" name="channel" class="form-select">
                                    <option value="" @selected(($channel ?? '') === '')>Todos</option>
                                    <option value="web" @selected(($channel ?? '') === 'web')>web</option>
                                    <option value="api" @selected(($channel ?? '') === 'api')>api</option>
                                    <option value="phone" @selected(($channel ?? '') === 'phone')>phone</option>
                                    <option value="email" @selected(($channel ?? '') === 'email')>email</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <button type="submit" class="btn btn-dark w-100">Aplicar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-body-secondary small">Ingresos pagados</div>
                            <div class="h4 mb-0">
                                MXN {{ number_format(($kpis['paid_revenue_cents'] ?? 0) / 100, 2, '.', ',') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-body-secondary small">Órdenes pagadas</div>
                            <div class="h4 mb-0">{{ $kpis['paid_orders'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-body-secondary small">Ticket promedio</div>
                            <div class="h4 mb-0">
                                MXN {{ number_format(($kpis['avg_order_cents'] ?? 0) / 100, 2, '.', ',') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-body-secondary small">Clientes únicos</div>
                            <div class="h4 mb-0">{{ $kpis['unique_customers'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-body-secondary small">Órdenes pendientes</div>
                            <div class="h4 mb-1">{{ $kpis['pending_orders'] ?? 0 }}</div>
                            <div class="text-body-secondary small">Requieren seguimiento</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-body-secondary small">Órdenes canceladas</div>
                            <div class="h4 mb-1">{{ $kpis['cancelled_orders'] ?? 0 }}</div>
                            <div class="text-body-secondary small">Se excluyen de ingresos</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Ingresos y órdenes por día</h2>
                    <canvas id="chart" height="90"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="h6 mb-3">Detalle de ventas</h2>
                    <div id="grid"></div>
                </div>
            </div>
        </div>

        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const gridData = @json($rows);

                new gridjs.Grid({
                    columns: [
                        'ID',
                        'Cliente',
                        'Estado',
                        'Canal',
                        'Fecha',
                        'Ítems',
                        'Unidades',
                        'Total'
                    ],
                    data: gridData,
                    search: true,
                    sort: true,
                    pagination: { limit: 15 },
                    fixedHeader: true,
                    height: '520px'
                }).render(document.getElementById('grid'));

                const labels = @json($chartLabels);
                const paidOrders = @json($chartPaidOrders);
                const paidRevenue = @json($chartPaidRevenue);

                const ctx = document.getElementById('chart');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            { label: 'Ingresos (MXN)', data: paidRevenue, borderWidth: 2, tension: 0.2, yAxisID: 'y' },
                            { label: 'Órdenes pagadas', data: paidOrders, borderWidth: 2, tension: 0.2, yAxisID: 'y1' },
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: (v) => `MXN ${v}` } },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } }
                        }
                    }
                });
            });
        </script>
    </body>
</html>

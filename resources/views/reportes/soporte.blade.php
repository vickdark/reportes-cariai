<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Reporte de Ventas</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
        <style>
            .gridjs-container { width: 100%; }
            .gridjs-wrapper { overflow-x: hidden !important; }
            .gridjs-table { width: 100% !important; table-layout: fixed; }
            .gridjs-td, .gridjs-th { vertical-align: middle; }
        </style>

        <link rel="stylesheet" href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css">
        <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
    </head>

    <body class="bg-light text-dark">
        <div class="container py-4">
            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2 align-items-baseline justify-content-between">
                    <div>
                        <h1 class="h3 mb-1">Reporte de Ventas LATAM</h1>
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
                            <span class="mx-1">·</span>
                            Moneda: <span class="fw-semibold">COP</span>
                        </div>
                    </div>

                    <div class="text-body-secondary small">
                        Actualizado: <span class="fw-semibold">{{ now()->toDateTimeString() }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm bg-success-subtle">
                        <div class="card-body d-flex align-items-start justify-content-between gap-3">
                            <div>
                            <div class="text-body-secondary small">Ingresos pagados</div>
                            <div class="h4 mb-0">
                                COP $ {{ number_format((int) round(($kpis['paid_revenue_cents'] ?? 0) / 100), 0, ',', '.') }}
                            </div>
                            </div>
                            <div class="text-success fs-3">
                                <i class="fa-solid fa-sack-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm bg-primary-subtle">
                        <div class="card-body d-flex align-items-start justify-content-between gap-3">
                            <div>
                            <div class="text-body-secondary small">Órdenes pagadas</div>
                            <div class="h4 mb-0">{{ $kpis['paid_orders'] ?? 0 }}</div>
                            </div>
                            <div class="text-primary fs-3">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm bg-info-subtle">
                        <div class="card-body d-flex align-items-start justify-content-between gap-3">
                            <div>
                            <div class="text-body-secondary small">Ticket promedio</div>
                            <div class="h4 mb-0">
                                COP $ {{ number_format((int) round(($kpis['avg_order_cents'] ?? 0) / 100), 0, ',', '.') }}
                            </div>
                            </div>
                            <div class="text-info fs-3">
                                <i class="fa-solid fa-chart-simple"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm bg-warning-subtle">
                        <div class="card-body d-flex align-items-start justify-content-between gap-3">
                            <div>
                            <div class="text-body-secondary small">Clientes únicos</div>
                            <div class="h4 mb-0">{{ $kpis['unique_customers'] ?? 0 }}</div>
                            </div>
                            <div class="text-warning fs-3">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm bg-secondary-subtle">
                        <div class="card-body d-flex align-items-start justify-content-between gap-3">
                            <div>
                            <div class="text-body-secondary small">Órdenes pendientes</div>
                            <div class="h4 mb-1">{{ $kpis['pending_orders'] ?? 0 }}</div>
                            <div class="text-body-secondary small">Requieren seguimiento</div>
                            </div>
                            <div class="text-secondary fs-3">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm bg-danger-subtle">
                        <div class="card-body d-flex align-items-start justify-content-between gap-3">
                            <div>
                            <div class="text-body-secondary small">Órdenes canceladas</div>
                            <div class="h4 mb-1">{{ $kpis['cancelled_orders'] ?? 0 }}</div>
                            <div class="text-body-secondary small">Se excluyen de ingresos</div>
                            </div>
                            <div class="text-danger fs-3">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-2">
                        <div class="fw-semibold">Filtros</div>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('reportes.ventas.export', request()->query()) }}">
                            <i class="fa-solid fa-file-export me-1"></i>Exportar CSV
                        </a>
                    </div>
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
                                    <option value="whatsapp" @selected(($channel ?? '') === 'whatsapp')>whatsapp</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <button type="submit" class="btn btn-dark w-100">Aplicar</button>
                            </div>
                        </div>
                    </form>
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
                        { name: 'ID', sort: true, width: '80px' },
                        {
                            name: 'Cliente',
                            sort: true,
                            width: '260px',
                            formatter: (cell) => {
                                const v = String(cell || '');
                                const safe = v.replaceAll('"', '&quot;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
                                return gridjs.html(`<div class="text-truncate" title="${safe}">${safe}</div>`);
                            }
                        },
                        {
                            name: 'Estado',
                            sort: true,
                            width: '120px',
                            formatter: (cell) => {
                                const v = String(cell || '');
                                const cls = v === 'paid' ? 'text-bg-success' : (v === 'pending' ? 'text-bg-warning' : 'text-bg-danger');
                                return gridjs.html(`<span class="badge ${cls}">${v}</span>`);
                            }
                        },
                        {
                            name: 'Canal',
                            sort: true,
                            width: '150px',
                            formatter: (cell) => {
                                const v = String(cell || '');
                                const icon = v === 'whatsapp'
                                    ? 'fa-brands fa-whatsapp'
                                    : (v === 'email' ? 'fa-regular fa-envelope' : (v === 'phone' ? 'fa-solid fa-phone' : (v === 'api' ? 'fa-solid fa-code' : 'fa-solid fa-globe')));
                                return gridjs.html(`<span class="d-inline-flex align-items-center gap-2"><i class="${icon}"></i><span>${v}</span></span>`);
                            }
                        },
                        { name: 'Fecha', sort: true, width: '190px' },
                        { name: 'Ítems', sort: true, width: '90px' },
                        { name: 'Unidades', sort: true, width: '110px' },
                        {
                            name: 'Total',
                            sort: true,
                            width: '170px',
                            formatter: (cell) => cop.format(Number(cell || 0)),
                        }
                    ],
                    data: gridData,
                    search: { enabled: true, placeholder: 'Buscar en la tabla…' },
                    sort: true,
                    pagination: { enabled: true, limit: 15, summary: true },
                    fixedHeader: true,
                    height: '520px'
                }).render(document.getElementById('grid'));

                const labels = @json($chartLabels);
                const paidOrders = @json($chartPaidOrders);
                const paidRevenue = @json($chartPaidRevenue);
                const cop = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });

                const ctx = document.getElementById('chart');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            { label: 'Ingresos (COP)', data: paidRevenue, borderWidth: 2, tension: 0.2, yAxisID: 'y' },
                            { label: 'Órdenes pagadas', data: paidOrders, borderWidth: 2, tension: 0.2, yAxisID: 'y1' },
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: (v) => cop.format(v) } },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } }
                        }
                    }
                });
            });
        </script>
    </body>
</html>

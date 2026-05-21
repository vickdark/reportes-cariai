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
            @php
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

                $countryLabels = [
                    'CO' => 'Colombia',
                    'MX' => 'México',
                    'CL' => 'Chile',
                    'AR' => 'Argentina',
                    'PE' => 'Perú',
                ];

                $segmentLabels = [
                    'SMB' => 'Pyme',
                    'Mid-Market' => 'Mediana',
                    'Enterprise' => 'Enterprise',
                ];
            @endphp

            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2 align-items-baseline justify-content-between">
                    <div>
                        <h1 class="h3 mb-1">Reporte de Ventas LATAM</h1>
                        <div class="text-body-secondary">
                            Rango: <span class="fw-semibold">{{ $from }}</span> a <span class="fw-semibold">{{ $to }}</span>
                            @if ($status !== '')
                                <span class="mx-1">·</span>
                                Estado: <span class="fw-semibold">{{ $statusLabels[$status] ?? $status }}</span>
                            @endif
                            @if (($channel ?? '') !== '')
                                <span class="mx-1">·</span>
                                Canal: <span class="fw-semibold">{{ $channelLabels[$channel] ?? $channel }}</span>
                            @endif
                            @if (($country ?? '') !== '')
                                <span class="mx-1">·</span>
                                País: <span class="fw-semibold">{{ $countryLabels[$country] ?? $country }}</span>
                            @endif
                            @if (($segment ?? '') !== '')
                                <span class="mx-1">·</span>
                                Segmento: <span class="fw-semibold">{{ $segmentLabels[$segment] ?? $segment }}</span>
                            @endif
                            <span class="mx-1">·</span>
                            Moneda: <span class="fw-semibold">COP</span>
                        </div>
                    </div>

                    <div class="text-body-secondary small">
                        @php
                            $updatedAt = now()->setTimezone('America/Bogota');
                            $updatedMeridiem = $updatedAt->format('A') === 'AM' ? 'a. m.' : 'p. m.';
                        @endphp
                        Actualizado: <span class="fw-semibold">{{ $updatedAt->format('d/m/Y h:i') }} {{ $updatedMeridiem }} (COT)</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-primary fs-4">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1">¿Para qué sirve este reporte?</div>
                            <div class="text-body-secondary">
                                Consolida el desempeño de ventas por rango de fechas, mostrando ingresos, volumen de órdenes, ticket promedio y clientes únicos.
                                Ayuda a detectar tendencias diarias, identificar canales con mayor conversión y priorizar seguimiento de órdenes pendientes.
                            </div>
                            <div class="text-body-secondary small mt-2">
                                Comparativo: vs período anterior ({{ $prevFrom }} a {{ $prevTo }})
                            </div>
                        </div>
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
                            @php
                                $d = $kpis['paid_revenue_change_pct'] ?? null;
                                $dText = $d === null ? 'N/A' : (($d >= 0 ? '+' : '').number_format($d, 1, ',', '.').'%');
                                $dCls = $d === null ? 'text-body-secondary' : ($d >= 0 ? 'text-success' : 'text-danger');
                                $dIcon = $d === null ? '' : ($d >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
                            @endphp
                            <div class="small {{ $dCls }}">
                                @if ($dIcon !== '') <i class="fa-solid {{ $dIcon }} me-1"></i>@endif
                                {{ $dText }} vs período anterior
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
                            @php
                                $d = $kpis['paid_orders_change_pct'] ?? null;
                                $dText = $d === null ? 'N/A' : (($d >= 0 ? '+' : '').number_format($d, 1, ',', '.').'%');
                                $dCls = $d === null ? 'text-body-secondary' : ($d >= 0 ? 'text-success' : 'text-danger');
                                $dIcon = $d === null ? '' : ($d >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
                            @endphp
                            <div class="small {{ $dCls }}">
                                @if ($dIcon !== '') <i class="fa-solid {{ $dIcon }} me-1"></i>@endif
                                {{ $dText }} vs período anterior
                            </div>
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
                            @php
                                $d = $kpis['avg_order_change_pct'] ?? null;
                                $dText = $d === null ? 'N/A' : (($d >= 0 ? '+' : '').number_format($d, 1, ',', '.').'%');
                                $dCls = $d === null ? 'text-body-secondary' : ($d >= 0 ? 'text-success' : 'text-danger');
                                $dIcon = $d === null ? '' : ($d >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
                            @endphp
                            <div class="small {{ $dCls }}">
                                @if ($dIcon !== '') <i class="fa-solid {{ $dIcon }} me-1"></i>@endif
                                {{ $dText }} vs período anterior
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
                            @php
                                $d = $kpis['unique_customers_change_pct'] ?? null;
                                $dText = $d === null ? 'N/A' : (($d >= 0 ? '+' : '').number_format($d, 1, ',', '.').'%');
                                $dCls = $d === null ? 'text-body-secondary' : ($d >= 0 ? 'text-success' : 'text-danger');
                                $dIcon = $d === null ? '' : ($d >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
                            @endphp
                            <div class="small {{ $dCls }}">
                                @if ($dIcon !== '') <i class="fa-solid {{ $dIcon }} me-1"></i>@endif
                                {{ $dText }} vs período anterior
                            </div>
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
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('reportes.ventas') }}">
                                <i class="fa-solid fa-rotate-left me-1"></i>Restablecer
                            </a>
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('reportes.ventas.export', request()->query()) }}">
                                <i class="fa-solid fa-file-export me-1"></i>Exportar CSV
                            </a>
                        </div>
                    </div>
                    <form method="GET">
                        <div class="row g-3">
                            <div class="col-12 col-lg-3">
                                <label for="from" class="form-label">Desde</label>
                                <input id="from" name="from" type="date" value="{{ $from }}" class="form-control">
                            </div>

                            <div class="col-12 col-lg-3">
                                <label for="to" class="form-label">Hasta</label>
                                <input id="to" name="to" type="date" value="{{ $to }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="status" class="form-label">Estado</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="" @selected($status === '')>Todos</option>
                                    <option value="paid" @selected($status === 'paid')>Pagada</option>
                                    <option value="pending" @selected($status === 'pending')>Pendiente</option>
                                    <option value="cancelled" @selected($status === 'cancelled')>Cancelada</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="channel" class="form-label">Canal</label>
                                <select id="channel" name="channel" class="form-select">
                                    <option value="" @selected(($channel ?? '') === '')>Todos</option>
                                    <option value="web" @selected(($channel ?? '') === 'web')>Web</option>
                                    <option value="api" @selected(($channel ?? '') === 'api')>API</option>
                                    <option value="phone" @selected(($channel ?? '') === 'phone')>Teléfono</option>
                                    <option value="email" @selected(($channel ?? '') === 'email')>Correo</option>
                                    <option value="whatsapp" @selected(($channel ?? '') === 'whatsapp')>WhatsApp</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="country" class="form-label">País</label>
                                <select id="country" name="country" class="form-select">
                                    <option value="" @selected(($country ?? '') === '')>Todos</option>
                                    <option value="CO" @selected(($country ?? '') === 'CO')>Colombia</option>
                                    <option value="MX" @selected(($country ?? '') === 'MX')>México</option>
                                    <option value="CL" @selected(($country ?? '') === 'CL')>Chile</option>
                                    <option value="AR" @selected(($country ?? '') === 'AR')>Argentina</option>
                                    <option value="PE" @selected(($country ?? '') === 'PE')>Perú</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="segment" class="form-label">Segmento</label>
                                <select id="segment" name="segment" class="form-select">
                                    <option value="" @selected(($segment ?? '') === '')>Todos</option>
                                    <option value="SMB" @selected(($segment ?? '') === 'SMB')>Pyme</option>
                                    <option value="Mid-Market" @selected(($segment ?? '') === 'Mid-Market')>Mediana</option>
                                    <option value="Enterprise" @selected(($segment ?? '') === 'Enterprise')>Enterprise</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-dark">
                                <i class="fa-solid fa-filter me-1"></i>Aplicar filtros
                            </button>
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

            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-2">Mix por país</h2>
                            <div class="text-body-secondary small mb-3">Distribución de ingresos pagados (COP)</div>
                            <canvas id="countryMixChart" height="220"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-2">Top 5 canales</h2>
                            <div class="text-body-secondary small mb-3">Por ingresos pagados (COP)</div>
                            <div class="list-group list-group-flush">
                                @forelse (($topChannels ?? []) as $r)
                                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold">{{ $channelLabels[$r->channel] ?? $r->channel }}</span>
                                            <span class="text-body-secondary small">({{ (int) $r->orders }} órdenes)</span>
                                        </div>
                                        <div class="fw-semibold">
                                            COP $ {{ number_format((int) round(((int) $r->revenue_cents) / 100), 0, ',', '.') }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-body-secondary">Sin datos.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-2">Top 5 clientes</h2>
                            <div class="text-body-secondary small mb-3">Por ingresos pagados (COP)</div>
                            <div class="list-group list-group-flush">
                                @forelse (($topCustomers ?? []) as $r)
                                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $r->customer_name }}</span>
                                            <span class="text-body-secondary small">{{ (int) $r->orders }} órdenes</span>
                                        </div>
                                        <div class="fw-semibold">
                                            COP $ {{ number_format((int) round(((int) $r->revenue_cents) / 100), 0, ',', '.') }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-body-secondary">Sin datos.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-2">Alertas</h2>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @php
                            $pendingOld = (int) ($kpis['pending_old_count'] ?? 0);
                            $pendingDays = (int) ($kpis['pending_threshold_days'] ?? 0);
                            $cancelRate = (float) ($kpis['cancel_rate'] ?? 0);
                            $cancelAlert = (bool) ($kpis['cancel_alert'] ?? false);
                            $cancelRatePct = number_format($cancelRate * 100, 1, ',', '.');
                        @endphp

                        <span class="badge {{ $pendingOld > 0 ? 'text-bg-danger' : 'text-bg-success' }}">
                            Pendientes &gt; {{ $pendingDays }} días: {{ $pendingOld }}
                        </span>

                        <span class="badge {{ $cancelAlert ? 'text-bg-danger' : 'text-bg-success' }}">
                            Tasa de cancelación: {{ $cancelRatePct }}%
                        </span>
                    </div>
                    <div class="text-body-secondary small mt-2">
                        Las alertas se calculan con los filtros actuales y el rango seleccionado.
                    </div>
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
                                const label = statusLabels[v] || v;
                                return gridjs.html(`<span class="badge ${cls}">${label}</span>`);
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
                                const label = channelLabels[v] || v;
                                return gridjs.html(`<span class="d-inline-flex align-items-center gap-2"><i class="${icon}"></i><span>${label}</span></span>`);
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
                const statusLabels = { paid: 'Pagada', pending: 'Pendiente', cancelled: 'Cancelada' };
                const channelLabels = { web: 'Web', api: 'API', phone: 'Teléfono', email: 'Correo', whatsapp: 'WhatsApp' };
                const mixCountryLabels = @json($mixCountryLabels ?? []);
                const mixCountryRevenue = @json($mixCountryRevenue ?? []);

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

                const mixCtx = document.getElementById('countryMixChart');
                new Chart(mixCtx, {
                    type: 'doughnut',
                    data: {
                        labels: mixCountryLabels,
                        datasets: [
                            {
                                label: 'Ingresos (COP)',
                                data: mixCountryRevenue,
                                borderWidth: 1,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.label}: ${cop.format(ctx.parsed)}`
                                }
                            }
                        }
                    }
                });
            });
        </script>
    </body>
</html>

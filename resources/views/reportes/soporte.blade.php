<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Reporte de Ventas</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css">
    </head>

    <body class="bg-light text-dark">
        <div class="container py-4">

            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2 align-items-baseline justify-content-between">
                    <div>
                        <h1 class="h3 mb-1">Reporte de Ventas LATAM</h1>
                        <div class="text-body-secondary">
                            Rango: <span class="fw-semibold">{{ $from }}</span> a <span class="fw-semibold">{{ $to }}</span>
                            @if ($statusLabel !== null)
                                <span class="mx-1">·</span>
                                Estado: <span class="fw-semibold">{{ $statusLabel }}</span>
                            @endif
                            @if ($channelLabel !== null)
                                <span class="mx-1">·</span>
                                Canal: <span class="fw-semibold">{{ $channelLabel }}</span>
                            @endif
                            @if ($countryLabel !== null)
                                <span class="mx-1">·</span>
                                País: <span class="fw-semibold">{{ $countryLabel }}</span>
                            @endif
                            @if ($segmentLabel !== null)
                                <span class="mx-1">·</span>
                                Segmento: <span class="fw-semibold">{{ $segmentLabel }}</span>
                            @endif
                            <span class="mx-1">·</span>
                            Moneda: <span class="fw-semibold">COP</span>
                        </div>
                    </div>

                    <div class="text-body-secondary small">
                        Actualizado: <span class="fw-semibold">{{ $updatedAtLabel }}</span>
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
                        <div class="card-body kpi-card">
                            <div class="kpi-card-content">
                                <div class="kpi-card-label">Ingresos pagados</div>
                                <div class="kpi-card-value">{{ $formatMoney($paidRevenueCents) }}</div>
                                <div class="kpi-card-delta {{ $paidRevenueDelta['cls'] }}">
                                    @if ($paidRevenueDelta['icon'] !== null)
                                        <i class="fa-solid {{ $paidRevenueDelta['icon'] }} me-1"></i>
                                    @endif
                                    {{ $paidRevenueDelta['text'] }} vs período anterior
                                </div>
                            </div>
                            <div class="text-success kpi-card-icon">
                                <i class="fa-solid fa-sack-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm bg-primary-subtle">
                        <div class="card-body kpi-card">
                            <div class="kpi-card-content">
                                <div class="kpi-card-label">Órdenes pagadas</div>
                                <div class="kpi-card-value">{{ $paidOrders }}</div>
                                <div class="kpi-card-delta {{ $paidOrdersDelta['cls'] }}">
                                    @if ($paidOrdersDelta['icon'] !== null)
                                        <i class="fa-solid {{ $paidOrdersDelta['icon'] }} me-1"></i>
                                    @endif
                                    {{ $paidOrdersDelta['text'] }} vs período anterior
                                </div>
                            </div>
                            <div class="text-primary kpi-card-icon">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm bg-info-subtle">
                        <div class="card-body kpi-card">
                            <div class="kpi-card-content">
                                <div class="kpi-card-label">Ticket promedio</div>
                                <div class="kpi-card-value">{{ $formatMoney($avgOrderCents) }}</div>
                                <div class="kpi-card-delta {{ $avgOrderDelta['cls'] }}">
                                    @if ($avgOrderDelta['icon'] !== null)
                                        <i class="fa-solid {{ $avgOrderDelta['icon'] }} me-1"></i>
                                    @endif
                                    {{ $avgOrderDelta['text'] }} vs período anterior
                                </div>
                            </div>
                            <div class="text-info kpi-card-icon">
                                <i class="fa-solid fa-chart-simple"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card border-0 shadow-sm bg-warning-subtle">
                        <div class="card-body kpi-card">
                            <div class="kpi-card-content">
                                <div class="kpi-card-label">Clientes únicos</div>
                                <div class="kpi-card-value">{{ $uniqueCustomers }}</div>
                                <div class="kpi-card-delta {{ $uniqueCustomersDelta['cls'] }}">
                                    @if ($uniqueCustomersDelta['icon'] !== null)
                                        <i class="fa-solid {{ $uniqueCustomersDelta['icon'] }} me-1"></i>
                                    @endif
                                    {{ $uniqueCustomersDelta['text'] }} vs período anterior
                                </div>
                            </div>
                            <div class="text-warning kpi-card-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm bg-secondary-subtle">
                        <div class="card-body kpi-card">
                            <div class="kpi-card-content">
                                <div class="kpi-card-label">Órdenes pendientes</div>
                                <div class="kpi-card-value">{{ $pendingOrders }}</div>
                                <div class="text-body-secondary small">Requieren seguimiento</div>
                            </div>
                            <div class="text-secondary kpi-card-icon">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm bg-danger-subtle">
                        <div class="card-body kpi-card">
                            <div class="kpi-card-content">
                                <div class="kpi-card-label">Órdenes canceladas</div>
                                <div class="kpi-card-value">{{ $cancelledOrders }}</div>
                                <div class="text-body-secondary small">Se excluyen de ingresos</div>
                            </div>
                            <div class="text-danger kpi-card-icon">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="filters-header">
                        <div class="fw-semibold">Filtros</div>
                        <div class="filters-buttons">
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
                            <h2 class="chart-title">Mix por país</h2>
                            <div class="chart-description">Distribución de ingresos pagados (COP)</div>
                            <canvas id="countryMixChart" height="220"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="chart-title">Top 5 canales</h2>
                            <div class="chart-description">Por ingresos pagados (COP)</div>
                            <div class="list-group list-group-flush">
                                @forelse (($topChannels ?? []) as $r)
                                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold">{{ $channelLabels[$r->channel] ?? $r->channel }}</span>
                                            <span class="text-body-secondary small">({{ (int) $r->orders }} órdenes)</span>
                                        </div>
                                        <div class="fw-semibold">
                                            {{ $formatMoney((int) $r->revenue_cents) }}
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
                            <h2 class="chart-title">Top 5 clientes</h2>
                            <div class="chart-description">Por ingresos pagados (COP)</div>
                            <div class="list-group list-group-flush">
                                @forelse (($topCustomers ?? []) as $r)
                                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $r->customer_name }}</span>
                                            <span class="text-body-secondary small">{{ (int) $r->orders }} órdenes</span>
                                        </div>
                                        <div class="fw-semibold">
                                            {{ $formatMoney((int) $r->revenue_cents) }}
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
                    <div class="alerts-container">
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
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
                        <h2 class="h6 mb-0">Detalle de ventas</h2>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-body-secondary small">Filas:</span>
                            <select id="pageSize" class="form-select form-select-sm" style="width: 110px;">
                                <option value="15">15</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                    </div>
                    <div id="grid"></div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
        <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
        
        <script>
            // Pasar datos al módulo JavaScript
            window.mixCountryLabels = @json($mixCountryLabels ?? []);
            window.mixCountryRevenue = @json($mixCountryRevenue ?? []);
            window.baseParams = @json(request()->query());
            window.dataBaseUrl = @json(route('reportes.ventas.data'));
            window.chartLabels = @json($chartLabels);
            window.chartPaidOrders = @json($chartPaidOrders);
            window.chartPaidRevenue = @json($chartPaidRevenue);
        </script>
        
        @vite(['resources/js/soporte-report.js'])
    </body>
</html>

/**
 * Soporte para informes JavaScript

* Gestiona toda la funcionalidad del lado del cliente para el informe de ventas.
 */

import '../css/soporte-report.css';

window.addEventListener('DOMContentLoaded', () => {
    // Formatters and labels
    const cop = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0
    });

    const statusLabels = {
        paid: 'Pagada',
        pending: 'Pendiente',
        cancelled: 'Cancelada'
    };

    const channelLabels = {
        web: 'Web',
        api: 'API',
        phone: 'Teléfono',
        email: 'Correo',
        whatsapp: 'WhatsApp'
    };

    // Obtener datos de la página
    const mixCountryLabels = window.mixCountryLabels || [];
    const mixCountryRevenue = window.mixCountryRevenue || [];
    const baseParams = window.baseParams || {};

    const dataBaseUrl = new URL(window.dataBaseUrl, window.location.origin);
    const gridContainer = document.getElementById('grid');
    const pageSizeSelect = document.getElementById('pageSize');

    if (!gridContainer || !pageSizeSelect) return;

    let currentSearch = '';
    let currentSort = null;

    /**
     * Construir URL con parámetros
     */
    const buildUrl = (extra) => {
        const u = new URL(dataBaseUrl.toString());
        const params = { ...baseParams, ...extra };

        Object.entries(params).forEach(([k, v]) => {
            if (v === null || v === undefined) return;
            if (typeof v === 'string' && v.trim() === '') return;
            u.searchParams.set(k, String(v));
        });

        return u.toString();
    };

    /**
     * Renderizar la tabla Grid.js
     */
    const renderGrid = (pageSize) => {
        gridContainer.innerHTML = '';
        currentSearch = '';
        currentSort = null;

        new gridjs.Grid({
            columns: [
                { name: 'ID', sort: true, width: '80px' },
                {
                    name: 'Cliente',
                    sort: true,
                    width: '260px',
                    formatter: (cell) => {
                        const v = String(cell || '');
                        const safe = v
                            .replaceAll('"', '&quot;')
                            .replaceAll('<', '&lt;')
                            .replaceAll('>', '&gt;');
                        return gridjs.html(
                            `<div class="text-truncate" title="${safe}">${safe}</div>`
                        );
                    }
                },
                {
                    name: 'Estado',
                    sort: true,
                    width: '120px',
                    formatter: (cell) => {
                        const v = String(cell || '');
                        const cls =
                            v === 'paid'
                                ? 'text-bg-success'
                                : v === 'pending'
                                  ? 'text-bg-warning'
                                  : 'text-bg-danger';
                        const label = statusLabels[v] || v;
                        return gridjs.html(
                            `<span class="badge ${cls}">${label}</span>`
                        );
                    }
                },
                {
                    name: 'Canal',
                    sort: true,
                    width: '150px',
                    formatter: (cell) => {
                        const v = String(cell || '');
                        const icon =
                            v === 'whatsapp'
                                ? 'fa-brands fa-whatsapp'
                                : v === 'email'
                                  ? 'fa-regular fa-envelope'
                                  : v === 'phone'
                                    ? 'fa-solid fa-phone'
                                    : v === 'api'
                                      ? 'fa-solid fa-code'
                                      : 'fa-solid fa-globe';
                        const label = channelLabels[v] || v;
                        return gridjs.html(
                            `<span class="d-inline-flex align-items-center gap-2"><i class="${icon}"></i><span>${label}</span></span>`
                        );
                    }
                },
                { name: 'Fecha', sort: true, width: '190px' },
                { name: 'Ítems', sort: true, width: '90px' },
                { name: 'Unidades', sort: true, width: '110px' },
                {
                    name: 'Total',
                    sort: true,
                    width: '170px',
                    formatter: (cell) => cop.format(Number(cell || 0))
                }
            ],
            server: {
                url: buildUrl({ page: 1, limit: pageSize }),
                then: (res) => res.data,
                total: (res) => res.total
            },
            search: {
                enabled: true,
                placeholder: 'Buscar en la tabla…',
                server: {
                    url: (prev, keyword) => {
                        currentSearch = String(keyword || '');
                        const extra = { page: 1, limit: pageSize };
                        if (currentSort) {
                            extra.sort = currentSort.sort;
                            extra.dir = currentSort.dir;
                        }
                        if (currentSearch.trim() !== '') {
                            extra.search = currentSearch.trim();
                        }
                        return buildUrl(extra);
                    }
                }
            },
            sort: {
                server: {
                    url: (prev, columns) => {
                        const c = (columns || [])[0];
                        if (!c) {
                            currentSort = null;
                        } else {
                            currentSort = {
                                sort: c.index,
                                dir: c.direction === 1 ? 'asc' : 'desc'
                            };
                        }

                        const extra = { page: 1, limit: pageSize };
                        if (currentSort) {
                            extra.sort = currentSort.sort;
                            extra.dir = currentSort.dir;
                        }
                        if (currentSearch.trim() !== '') {
                            extra.search = currentSearch.trim();
                        }
                        return buildUrl(extra);
                    }
                }
            },
            pagination: {
                enabled: true,
                limit: pageSize,
                summary: true,
                server: {
                    url: (prev, page, limit) => {
                        const extra = { page: page + 1, limit };
                        if (currentSort) {
                            extra.sort = currentSort.sort;
                            extra.dir = currentSort.dir;
                        }
                        if (currentSearch.trim() !== '') {
                            extra.search = currentSearch.trim();
                        }
                        return buildUrl(extra);
                    }
                }
            },
            fixedHeader: true,
            height: '520px'
        }).render(gridContainer);
    };

    // Controlador de cambio de tamaño de página
    pageSizeSelect.addEventListener('change', () => {
        renderGrid(Number(pageSizeSelect.value) || 25);
    });

    // Initial render
    renderGrid(Number(pageSizeSelect.value) || 25);

    // Inicialice los gráficos si hay datos disponibles.
    const labels = window.chartLabels || [];
    const paidOrders = window.chartPaidOrders || [];
    const paidRevenue = window.chartPaidRevenue || [];

    if (labels.length > 0) {
        initializeLineChart(labels, paidOrders, paidRevenue, cop);
    }

    if (mixCountryLabels.length > 0) {
        initializeDoughnutChart(mixCountryLabels, mixCountryRevenue, cop);
    }
});

/**
 * Inicialice el gráfico de líneas para ingresos y pedidos.
 */
function initializeLineChart(labels, paidOrders, paidRevenue, cop) {
    const ctx = document.getElementById('chart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Ingresos (COP)',
                    data: paidRevenue,
                    borderWidth: 2,
                    tension: 0.2,
                    yAxisID: 'y'
                },
                {
                    label: 'Órdenes pagadas',
                    data: paidOrders,
                    borderWidth: 2,
                    tension: 0.2,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => cop.format(v)
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { precision: 0 }
                }
            }
        }
    });
}

/**
 * Inicialice el gráfico de donas para la mezcla de países.
 */
function initializeDoughnutChart(labels, revenue, cop) {
    const mixCtx = document.getElementById('countryMixChart');
    if (!mixCtx) return;

    new Chart(mixCtx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [
                {
                    label: 'Ingresos (COP)',
                    data: revenue,
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            `${ctx.label}: ${cop.format(ctx.parsed)}`
                    }
                }
            }
        }
    });
}

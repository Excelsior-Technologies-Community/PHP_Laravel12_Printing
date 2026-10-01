<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Queue Analytics & Auto-Failover Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .metric-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-4px);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .card-custom {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .progress-bar-custom {
            height: 10px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="container-fluid py-4">

        {{-- NAVIGATION HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-2 text-primary">📊</span>
                <div>
                    <h3 class="fw-bold mb-0">Print Queue Analytics & Auto-Failover Router</h3>
                    <p class="text-muted mb-0 small">Real-time load balancing, standby printer auto-failover, paper & ink cost tracking</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('printing.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('printing.thermal-studio') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-receipt"></i> Thermal Studio
                </a>
                <a href="{{ route('printing.analytics') }}" class="btn btn-dark btn-sm active">
                    <i class="bi bi-graph-up-arrow"></i> Analytics & Failover
                </a>
                <a href="{{ route('printing.printers') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer"></i> Printers
                </a>
            </div>
        </div>

        {{-- STAT METRIC CARDS --}}
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card metric-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Total Pages Printed</span>
                            <h2 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($totalPagesPrinted) }}</h2>
                        </div>
                        <div class="icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card metric-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Paper & Ink Cost</span>
                            <h2 class="fw-bold mb-0 mt-1 text-success">${{ number_format($totalCost, 2) }}</h2>
                        </div>
                        <div class="icon-box bg-success-subtle text-success">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card metric-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Auto-Failover Triggers</span>
                            <h2 class="fw-bold mb-0 mt-1 text-warning">{{ number_format($failoverCount) }}</h2>
                        </div>
                        <div class="icon-box bg-warning-subtle text-warning">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card metric-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Queue Success Rate</span>
                            <h2 class="fw-bold mb-0 mt-1 text-info">{{ $successRate }}%</h2>
                        </div>
                        <div class="icon-box bg-info-subtle text-info">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 2: LOAD BALANCER & FORMAT BREAKDOWN --}}
        <div class="row g-4 mb-4">
            {{-- PRINTER LOAD BALANCER TABLE --}}
            <div class="col-lg-7">
                <div class="card card-custom p-4 h-100">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-cpu text-primary me-2"></i> Print Queue Load Balancer & Printer Status
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Printer Name</th>
                                    <th>Status</th>
                                    <th>Active Jobs</th>
                                    <th>Queue Load %</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($printerStats as $p)
                                <tr>
                                    <td class="fw-semibold">
                                        <i class="bi bi-printer me-2 text-secondary"></i> {{ $p['name'] }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $p['status_badge'] }} px-2 py-1">
                                            {{ strtoupper($p['state']) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">{{ $p['job_count'] }}</td>
                                    <td style="width: 30%;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress w-100 progress-bar-custom">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $p['load_percentage'] }}%;"></div>
                                            </div>
                                            <small class="fw-bold">{{ $p['load_percentage'] }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">Primary</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No registered printers found.</td>
                                </tr>
                                @endforelse
                                <tr>
                                    <td class="fw-semibold text-primary">
                                        <i class="bi bi-shield-check me-2"></i> Standby Thermal #2 (Backup)
                                    </td>
                                    <td><span class="badge bg-success px-2 py-1">READY (STANDBY)</span></td>
                                    <td class="fw-bold">{{ $failoverCount }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress w-100 progress-bar-custom">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalJobs > 0 ? round(($failoverCount / $totalJobs) * 100, 1) : 0 }}%;"></div>
                                            </div>
                                            <small class="fw-bold">{{ $totalJobs > 0 ? round(($failoverCount / $totalJobs) * 100, 1) : 0 }}%</small>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-warning text-dark border">Auto-Failover</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- FORMAT CONSUMPTION BREAKDOWN --}}
            <div class="col-lg-5">
                <div class="card card-custom p-4 h-100">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-pie-chart text-success me-2"></i> Format & Template Consumption
                    </h5>

                    <div class="d-flex flex-column gap-3 mt-2">
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">80mm POS Thermal Receipts</span>
                                <span class="fw-bold text-primary">{{ $formatBreakdown['thermal_80mm'] }} jobs</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-primary" style="width: {{ $totalJobs > 0 ? round(($formatBreakdown['thermal_80mm']/$totalJobs)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">58mm POS Mini Receipts</span>
                                <span class="fw-bold text-info">{{ $formatBreakdown['thermal_58mm'] }} jobs</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-info" style="width: {{ $totalJobs > 0 ? round(($formatBreakdown['thermal_58mm']/$totalJobs)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Shipping Labels (4x6")</span>
                                <span class="fw-bold text-warning">{{ $formatBreakdown['shipping_label'] }} jobs</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-warning" style="width: {{ $totalJobs > 0 ? round(($formatBreakdown['shipping_label']/$totalJobs)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Product Barcode / QR Tags</span>
                                <span class="fw-bold text-dark">{{ $formatBreakdown['barcode_tag'] }} jobs</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-dark" style="width: {{ $totalJobs > 0 ? round(($formatBreakdown['barcode_tag']/$totalJobs)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Standard Invoice PDF</span>
                                <span class="fw-bold text-secondary">{{ $formatBreakdown['invoice_pdf'] }} jobs</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-secondary" style="width: {{ $totalJobs > 0 ? round(($formatBreakdown['invoice_pdf']/$totalJobs)*100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AUTO-FAILOVER AUDIT TRAIL LOGS --}}
        <div class="row">
            <div class="col-12">
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-shield-exclamation text-warning me-2"></i> Auto-Failover Reroute Audit Logs
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Primary (Failed/Busy) Printer</th>
                                    <th>Rerouted Standby Printer</th>
                                    <th>Format</th>
                                    <th>Status</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($failoverJobs as $fJob)
                                <tr>
                                    <td class="fw-bold">#{{ $fJob->order_id }}</td>
                                    <td>{{ $fJob->customer }}</td>
                                    <td><span class="badge bg-danger text-wrap">{{ $fJob->failover_printer }}</span></td>
                                    <td><span class="badge bg-success text-wrap">{{ $fJob->printer_name }}</span></td>
                                    <td><span class="badge bg-secondary">{{ strtoupper($fJob->format_type ?? 'THERMAL') }}</span></td>
                                    <td><span class="badge bg-warning text-dark"><i class="bi bi-lightning-fill"></i> Auto-Failover Resolved</span></td>
                                    <td class="small text-muted">{{ $fJob->printed_at ? $fJob->printed_at->format('d M Y H:i:s') : $fJob->created_at->format('d M Y H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No printer failovers recorded yet. Trigger a simulated jam in the Thermal Studio to test auto-failover routing.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thermal Receipt & Barcode Label Studio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .thermal-viewport-card {
            background: #2a2d34;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 520px;
        }

        /* Thermal Simulated Paper Paper Roll */
        .thermal-paper {
            background: #ffffff;
            color: #111111;
            font-family: 'Courier New', Courier, monospace;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border-top: 4px dashed #cccccc;
            border-bottom: 6px dashed #aaaaaa;
            padding: 20px;
            transition: width 0.3s ease, min-height 0.3s ease;
            position: relative;
        }

        .width-80mm {
            width: 320px;
        }

        .width-58mm {
            width: 240px;
        }

        .width-shipping {
            width: 360px;
        }

        .width-barcode {
            width: 220px;
        }

        .barcode-lines {
            height: 40px;
            background: repeating-linear-gradient(90deg,
                    #000 0,
                    #000 2px,
                    #fff 2px,
                    #fff 4px,
                    #000 4px,
                    #000 7px,
                    #fff 7px,
                    #fff 8px);
            width: 100%;
            margin: 10px 0 5px 0;
        }

        .qr-placeholder {
            width: 70px;
            height: 70px;
            border: 2px solid #000;
            background: repeating-conic-gradient(#000 0% 25%, #fff 0% 50%) 50% / 10px 10px;
            margin: 10px auto;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .btn-thermal {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border: none;
            color: white;
            font-weight: 600;
        }

        .btn-thermal:hover {
            opacity: 0.95;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container-fluid py-4">

        {{-- NAVIGATION HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-2 text-primary">🖨️</span>
                <div>
                    <h3 class="fw-bold mb-0">Thermal Receipt & Barcode Label Studio</h3>
                    <p class="text-muted mb-0 small">Multi-format thermal printing simulator, paper cost tracker & barcode studio</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('printing.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('printing.thermal-studio') }}" class="btn btn-primary btn-sm active">
                    <i class="bi bi-receipt"></i> Thermal Studio
                </a>
                <a href="{{ route('printing.analytics') }}" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-graph-up-arrow"></i> Analytics & Failover
                </a>
                <a href="{{ route('printing.printers') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer"></i> Printers
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-4">
            {{-- LEFT PANEL: PRINT CONTROLS FORM --}}
            <div class="col-lg-6">
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                        <i class="bi bi-sliders text-primary me-2"></i> Receipt & Label Configurator
                    </h5>

                    <form action="{{ route('printing.thermal.print') }}" method="POST" id="thermalForm">
                        @csrf

                        <div class="row g-3">
                            {{-- Format Selection --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Format Template</label>
                                <select class="form-select" name="format_type" id="formatSelect" onchange="updatePreview()">
                                    <option value="thermal_80mm" selected>80mm POS Thermal Receipt</option>
                                    <option value="thermal_58mm">58mm POS Mini Receipt</option>
                                    <option value="shipping_label">Shipping Label (4x6")</option>
                                    <option value="barcode_tag">Product Barcode / QR Tag</option>
                                </select>
                            </div>

                            {{-- Target Printer --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Primary Printer</label>
                                <select class="form-select" name="printer_id" id="printerSelect">
                                    @if(isset($printers) && count($printers) > 0)
                                    @foreach($printers as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ $p['state'] }})</option>
                                    @endforeach
                                    @else
                                    <option value="POS-80-MAIN">POS 80mm Main Thermal</option>
                                    <option value="POS-58-MINI">POS 58mm Mini Printer</option>
                                    <option value="OFFLINE-01" class="text-danger">POS Printer #3 (Simulate Jam/Offline)</option>
                                    @endif
                                </select>
                            </div>

                            {{-- Standby Failover Printer --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Standby Failover Printer</label>
                                <select class="form-select" name="standby_printer_id">
                                    <option value="">Auto-Detect Standby Printer</option>
                                    <option value="STANDBY-THERMAL-01" selected>Standby Thermal #2 (Backup)</option>
                                    <option value="STANDBY-LABEL-02">Standby Label Printer #2</option>
                                </select>
                            </div>

                            {{-- Pages Count --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pages / Label Copies</label>
                                <input type="number" class="form-control" name="pages_count" id="pagesInput" value="1" min="1" max="100" oninput="updatePreview()">
                            </div>

                            {{-- Header Title --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Header Title</label>
                                <input type="text" class="form-control" name="header_title" id="titleInput" value="METRO SUPERMARKET" oninput="updatePreview()">
                            </div>

                            {{-- Customer Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer / Recipient</label>
                                <input type="text" class="form-control" name="customer" id="customerInput" value="Rajesh Patel" oninput="updatePreview()">
                            </div>

                            {{-- Barcode payload --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Barcode / Tracking ID Payload</label>
                                <input type="text" class="form-control" name="barcode_text" id="barcodeInput" value="TRK-8849201948" oninput="updatePreview()">
                            </div>

                            {{-- Simulate Jam / Failover Toggle --}}
                            <div class="col-md-12">
                                <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="simulate_jam" value="1" id="jamSwitch">
                                    <label class="form-check-label fw-semibold text-danger" for="jamSwitch">
                                        <i class="bi bi-exclamation-triangle"></i> Simulate Primary Printer Jam / Paper Out (Test Auto-Failover)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Estimated Paper Cost: </small>
                                <span class="badge bg-success fs-6" id="costBadge">$0.05</span>
                            </div>

                            <button type="submit" class="btn btn-thermal px-4 py-2 rounded-3 shadow">
                                <i class="bi bi-printer-fill me-1"></i> Print Thermal Job
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT PANEL: LIVE THERMAL SIMULATOR STUDIO --}}
            <div class="col-lg-6">
                <div class="thermal-viewport-card">
                    <div class="d-flex justify-content-between w-100 mb-3 text-white">
                        <span class="fw-bold"><i class="bi bi-eye"></i> Live Thermal Print Simulator</span>
                        <span class="badge bg-secondary" id="formatLabelBadge">80mm POS Format</span>
                    </div>

                    {{-- SIMULATED THERMAL PAPER ROLL VIEWPORT --}}
                    <div class="thermal-paper width-80mm" id="paperRoll">
                        <div class="text-center fw-bold fs-6 text-uppercase" id="pvTitle">METRO SUPERMARKET</div>
                        <div class="text-center small text-muted mb-2">POS RECEIPT #{{ rand(1000, 9999) }}</div>

                        <div class="border-top border-bottom border-dark py-1 my-2 small">
                            <div class="d-flex justify-content-between">
                                <span>Date:</span>
                                <span>{{ date('Y-m-d H:i') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Customer:</span>
                                <span id="pvCustomer">Rajesh Patel</span>
                            </div>
                        </div>

                        <div id="pvItems" class="small">
                            <div class="d-flex justify-content-between">
                                <span>1x Premium Rice 5kg</span>
                                <span>$14.50</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>2x Refined Sunflower Oil</span>
                                <span>$12.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>1x Organic Wheat Flour</span>
                                <span>$8.50</span>
                            </div>
                        </div>

                        <div class="border-top border-dark pt-1 mt-2 fw-bold d-flex justify-content-between fs-6">
                            <span>TOTAL PAID:</span>
                            <span>$35.00</span>
                        </div>

                        <div class="text-center mt-3">
                            <div class="barcode-lines"></div>
                            <div class="small font-monospace" id="pvBarcode">TRK-8849201948</div>
                        </div>

                        <div class="qr-placeholder" id="pvQr"></div>

                        <div class="text-center small text-muted mt-2">*** Thank You For Shopping ***</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RECENT THERMAL JOBS TABLE --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i> Recent Thermal Print History & Failover Logs
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Format Type</th>
                                    <th>Target Printer</th>
                                    <th>Pages</th>
                                    <th>Paper Cost</th>
                                    <th>Routing Status</th>
                                    <th>Printed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($thermalJobs as $job)
                                <tr>
                                    <td class="fw-bold">#{{ $job->order_id }}</td>
                                    <td>{{ $job->customer }}</td>
                                    <td>
                                        <span class="badge {{ $job->format_type == 'thermal_80mm' ? 'bg-primary' : ($job->format_type == 'thermal_58mm' ? 'bg-info' : ($job->format_type == 'shipping_label' ? 'bg-warning text-dark' : 'bg-dark')) }}">
                                            {{ strtoupper($job->format_type ?? 'THERMAL') }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-printer me-1"></i> {{ $job->printer_name }}
                                    </td>
                                    <td>{{ $job->pages_count ?? 1 }}</td>
                                    <td class="fw-semibold text-success">${{ number_format($job->paper_cost ?? 0.05, 2) }}</td>
                                    <td>
                                        @if($job->is_failover)
                                        <span class="badge bg-danger shadow-sm">
                                            <i class="bi bi-arrow-repeat"></i> Auto-Failover (from {{ $job->failover_printer }})
                                        </span>
                                        @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Direct Print
                                        </span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $job->printed_at ? $job->printed_at->format('d M Y H:i') : $job->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No thermal jobs executed yet. Use the simulator above to submit jobs.</td>
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

    <script>
        function updatePreview() {
            const format = document.getElementById('formatSelect').value;
            const title = document.getElementById('titleInput').value || 'METRO SUPERMARKET';
            const customer = document.getElementById('customerInput').value || 'Customer';
            const barcode = document.getElementById('barcodeInput').value || 'TRK-000000';
            const pages = parseInt(document.getElementById('pagesInput').value) || 1;

            const paper = document.getElementById('paperRoll');
            const formatBadge = document.getElementById('formatLabelBadge');
            const costBadge = document.getElementById('costBadge');

            document.getElementById('pvTitle').innerText = title;
            document.getElementById('pvCustomer').innerText = customer;
            document.getElementById('pvBarcode').innerText = barcode;

            let unitCost = 0.05;

            // Update simulator paper width
            paper.className = 'thermal-paper';
            if (format === 'thermal_80mm') {
                paper.classList.add('width-80mm');
                formatBadge.innerText = '80mm POS Thermal';
                unitCost = 0.05;
            } else if (format === 'thermal_58mm') {
                paper.classList.add('width-58mm');
                formatBadge.innerText = '58mm POS Mini';
                unitCost = 0.03;
            } else if (format === 'shipping_label') {
                paper.classList.add('width-shipping');
                formatBadge.innerText = 'Shipping Label (4x6")';
                unitCost = 0.15;
            } else if (format === 'barcode_tag') {
                paper.classList.add('width-barcode');
                formatBadge.innerText = 'Product Barcode / QR Tag';
                unitCost = 0.02;
            }

            const totalCost = (unitCost * pages).toFixed(2);
            costBadge.innerText = '$' + totalCost;
        }

        // Initialize preview on load
        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</body>

</html>

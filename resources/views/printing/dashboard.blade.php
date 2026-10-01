<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Printing Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f6fa;
        }

        .stat-card {
            border: 0;
            border-radius: 14px;
            transition: .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
        }

        .action-btn {
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .progress {
            height: 8px;
        }

        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            min-width: 40px;
            text-align: center;
        }

    </style>

</head>

<body>

<div class="container-fluid py-4">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                🖨️ Printing Management Dashboard
            </h2>

            <p class="text-muted mb-0">
                Monitor invoices, printers and print jobs.
            </p>

        </div>

        <div class="d-flex gap-2">

            <a
                href="{{ route('printing.dashboard') }}"
                class="btn btn-primary"
            >
                📊 Dashboard
            </a>

            <a
                href="{{ route('printing.thermal-studio') }}"
                class="btn btn-outline-primary"
            >
                🏷️ Thermal Studio
            </a>

            <a
                href="{{ route('printing.analytics') }}"
                class="btn btn-outline-dark"
            >
                📈 Analytics & Failover
            </a>

            <a
                href="{{ route('invoice.preview') }}"
                class="btn btn-outline-secondary"
            >
                📄 Invoice Preview
            </a>

            <a
                href="{{ route('printing.printers') }}"
                class="btn btn-dark"
            >
                🖨️ Printers
            </a>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('success'))

        <div
            class="alert alert-success alert-dismissible fade show"
        >

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- ERROR MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('error'))

        <div
            class="alert alert-danger alert-dismissible fade show"
        >

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- STATISTICS --}}
    {{-- ========================================================= --}}

    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card stat-card shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Total Print Jobs
                    </div>

                    <div class="stat-number">
                        {{ $totalJobs }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stat-card shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Successful
                    </div>

                    <div class="stat-number text-success">
                        {{ $successfulJobs }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stat-card shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Failed
                    </div>

                    <div class="stat-number text-danger">
                        {{ $failedJobs }}
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card stat-card shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Pending
                    </div>

                    <div class="stat-number text-warning">
                        {{ $pendingJobs }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SECOND STATS --}}
    {{-- ========================================================= --}}

    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Today's Jobs
                    </div>

                    <h3 class="fw-bold">
                        {{ $todayJobs }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Today's Success
                    </div>

                    <h3 class="fw-bold text-success">
                        {{ $todaySuccessfulJobs }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Today's Failed
                    </div>

                    <h3 class="fw-bold text-danger">
                        {{ $todayFailedJobs }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Success Rate
                    </div>

                    <h3 class="fw-bold">
                        {{ $successRate }}%
                    </h3>

                    <div class="progress">

                        <div
                            class="progress-bar"
                            style="width: {{ $successRate }}%"
                        ></div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- TOTAL AMOUNT --}}
    {{-- ========================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-8">

                    <h5 class="fw-bold mb-1">
                        Successful Invoice Value
                    </h5>

                    <p class="text-muted mb-0">
                        Total invoice amount from successful
                        print jobs.
                    </p>

                </div>

                <div class="col-md-4 text-md-end">

                    <h2 class="fw-bold mb-0">
                        ₹{{ number_format($totalAmount, 2) }}
                    </h2>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- PRINTER OVERVIEW --}}
    {{-- ========================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0">
                    Available Printers
                </h5>

                <a
                    href="{{ route('printing.printers') }}"
                    class="btn btn-sm btn-outline-dark"
                >
                    View All
                </a>

            </div>

            <hr>

            <div class="row">

                @forelse($printers as $printer)

                    <div class="col-md-4 mb-3">

                        <div class="border rounded p-3 h-100">

                            <div class="d-flex justify-content-between">

                                <h6 class="fw-bold">
                                    {{ $printer['name'] }}
                                </h6>

                                <span>
                                    🖨️
                                </span>

                            </div>

                            <small class="text-muted">

                                ID:
                                {{ $printer['id'] }}

                            </small>

                            <br>

                            @if(
                                strtolower((string) $printer['state'])
                                === 'online'
                            )

                                <span class="badge bg-success mt-2">
                                    Online
                                </span>

                            @elseif(
                                strtolower((string) $printer['state'])
                                === 'offline'
                            )

                                <span class="badge bg-danger mt-2">
                                    Offline
                                </span>

                            @else

                                <span class="badge bg-secondary mt-2">
                                    {{ $printer['state'] }}
                                </span>

                            @endif

                            <div class="small text-muted mt-2">

                                Computer:
                                {{ $printer['computer'] }}

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="col-12">

                        <div class="alert alert-warning mb-0">

                            No PrintNode printers found.

                        </div>

                    </div>

                @endforelse

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SEARCH + FILTERS --}}
    {{-- ========================================================= --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="fw-bold mb-0">
                    Print Job History
                </h5>

                <a
                    href="{{ route(
                        'printing.export',
                        request()->query()
                    ) }}"
                    class="btn btn-success"
                >
                    📥 Export CSV
                </a>

            </div>


            <form
                method="GET"
                action="{{ route('printing.dashboard') }}"
            >

                <div class="row g-3">

                    {{-- SEARCH --}}

                    <div class="col-md-4">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Order, customer, printer..."
                            value="{{ request('search') }}"
                        >

                    </div>


                    {{-- STATUS --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All
                            </option>

                            <option
                                value="success"
                                @selected(request('status') === 'success')
                            >
                                Success
                            </option>

                            <option
                                value="failed"
                                @selected(request('status') === 'failed')
                            >
                                Failed
                            </option>

                            <option
                                value="pending"
                                @selected(request('status') === 'pending')
                            >
                                Pending
                            </option>

                        </select>

                    </div>


                    {{-- PRINTER --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Printer
                        </label>

                        <select
                            name="printer_id"
                            class="form-select"
                        >

                            <option value="">
                                All Printers
                            </option>

                            @foreach($printers as $printer)

                                <option
                                    value="{{ $printer['id'] }}"
                                    @selected(
                                        (string) request('printer_id')
                                        === (string) $printer['id']
                                    )
                                >
                                    {{ $printer['name'] }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- DATE FROM --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Date From
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}"
                        >

                    </div>


                    {{-- DATE TO --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Date To
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}"
                        >

                    </div>


                    {{-- SORT --}}

                    <div class="col-md-3">

                        <label class="form-label">
                            Sort By
                        </label>

                        <select
                            name="sort"
                            class="form-select"
                        >

                            <option
                                value="created_at"
                                @selected($sort === 'created_at')
                            >
                                Created Date
                            </option>

                            <option
                                value="id"
                                @selected($sort === 'id')
                            >
                                ID
                            </option>

                            <option
                                value="order_id"
                                @selected($sort === 'order_id')
                            >
                                Order ID
                            </option>

                            <option
                                value="customer"
                                @selected($sort === 'customer')
                            >
                                Customer
                            </option>

                            <option
                                value="total"
                                @selected($sort === 'total')
                            >
                                Amount
                            </option>

                            <option
                                value="status"
                                @selected($sort === 'status')
                            >
                                Status
                            </option>

                        </select>

                    </div>


                    {{-- DIRECTION --}}

                    <div class="col-md-3">

                        <label class="form-label">
                            Direction
                        </label>

                        <select
                            name="direction"
                            class="form-select"
                        >

                            <option
                                value="desc"
                                @selected($direction === 'desc')
                            >
                                Descending
                            </option>

                            <option
                                value="asc"
                                @selected($direction === 'asc')
                            >
                                Ascending
                            </option>

                        </select>

                    </div>


                    {{-- APPLY --}}

                    <div class="col-md-3 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            🔎 Apply Filters
                        </button>

                    </div>


                    {{-- CLEAR --}}

                    <div class="col-md-3 d-flex align-items-end">

                        <a
                            href="{{ route('printing.dashboard') }}"
                            class="btn btn-secondary w-100"
                        >
                            Clear Filters
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- BULK DELETE FORM --}}
    {{-- ========================================================= --}}

    <form
        method="POST"
        action="{{ route('printing.jobs.bulkDestroy') }}"
        id="bulkDeleteForm"
    >

        @csrf

        @method('DELETE')


        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h5 class="fw-bold mb-0">
                        Jobs
                    </h5>

                    <button
                        type="submit"
                        class="btn btn-danger"
                        onclick="
                            return confirm(
                                'Delete all selected print jobs?'
                            )
                        "
                    >
                        🗑️ Delete Selected
                    </button>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="selectAll"
                                    >

                                </th>

                                <th>#</th>

                                <th>Order</th>

                                <th>Customer</th>

                                <th>Printer</th>

                                <th>Total</th>

                                <th>Status</th>

                                <th>Printed At</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($jobs as $job)

                                <tr>

                                    {{-- CHECKBOX --}}

                                    <td>

                                        <input
                                            type="checkbox"
                                            name="job_ids[]"
                                            value="{{ $job->id }}"
                                            class="form-check-input job-checkbox"
                                        >

                                    </td>


                                    {{-- ID --}}

                                    <td>
                                        {{ $job->id }}
                                    </td>


                                    {{-- ORDER --}}

                                    <td>
                                        #{{ $job->order_id }}
                                    </td>


                                    {{-- CUSTOMER --}}

                                    <td>
                                        {{ $job->customer }}
                                    </td>


                                    {{-- PRINTER --}}

                                    <td>

                                        {{ $job->printer_name ?? 'Unknown' }}

                                        <br>

                                        <small class="text-muted">

                                            ID:
                                            {{ $job->printer_id ?? '-' }}

                                        </small>

                                    </td>


                                    {{-- TOTAL --}}

                                    <td>

                                        ₹{{ number_format(
                                            $job->total,
                                            2
                                        ) }}

                                    </td>


                                    {{-- STATUS --}}

                                    <td>

                                        @if($job->status === 'success')

                                            <span class="badge bg-success">
                                                Success
                                            </span>

                                        @elseif($job->status === 'failed')

                                            <span class="badge bg-danger">
                                                Failed
                                            </span>

                                        @else

                                            <span
                                                class="badge bg-warning text-dark"
                                            >
                                                Pending
                                            </span>

                                        @endif

                                    </td>


                                    {{-- PRINTED AT --}}

                                    <td>

                                        {{ $job->printed_at
                                            ? $job->printed_at->format(
                                                'd M Y H:i'
                                            )
                                            : '-'
                                        }}

                                    </td>


                                    {{-- ACTIONS --}}

                                    <td>

                                        {{-- VIEW --}}

                                        <a
                                            href="{{ route(
                                                'printing.show',
                                                $job
                                            ) }}"
                                            class="btn btn-sm btn-info text-white action-btn"
                                        >
                                            View
                                        </a>


                                        {{-- DOWNLOAD PDF --}}

                                        @if($job->file_path)

                                            <a
                                                href="{{ route(
                                                    'printing.job.download',
                                                    $job
                                                ) }}"
                                                class="btn btn-sm btn-success action-btn"
                                            >
                                                PDF
                                            </a>

                                        @endif


                                        {{-- RETRY --}}

                                        @if($job->status === 'failed')

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'printing.job.retry',
                                                    $job
                                                ) }}"
                                                class="d-inline"
                                            >

                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-warning action-btn"
                                                    onclick="
                                                        return confirm(
                                                            'Retry this failed print job?'
                                                        )
                                                    "
                                                >
                                                    Retry
                                                </button>

                                            </form>

                                        @endif


                                        {{-- REPRINT --}}

                                        @if($job->status === 'success')

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'printing.job.reprint',
                                                    $job
                                                ) }}"
                                                class="d-inline"
                                            >

                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-primary action-btn"
                                                    onclick="
                                                        return confirm(
                                                            'Reprint this invoice?'
                                                        )
                                                    "
                                                >
                                                    Reprint
                                                </button>

                                            </form>

                                        @endif


                                        {{-- DELETE --}}

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'printing.job.destroy',
                                                $job
                                            ) }}"
                                            class="d-inline"
                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger action-btn"
                                                onclick="
                                                    return confirm(
                                                        'Delete this print job?'
                                                    )
                                                "
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>


                                {{-- ERROR MESSAGE --}}

                                @if(
                                    $job->status === 'failed'
                                    && $job->error_message
                                )

                                    <tr>

                                        <td colspan="9">

                                            <div
                                                class="alert alert-danger mb-0"
                                            >

                                                <strong>
                                                    Error:
                                                </strong>

                                                {{ $job->error_message }}

                                            </div>

                                        </td>

                                    </tr>

                                @endif


                            @empty

                                <tr>

                                    <td
                                        colspan="9"
                                        class="text-center py-5"
                                    >

                                        <h5>
                                            No print jobs found.
                                        </h5>

                                        <p class="text-muted mb-0">
                                            Try changing your filters.
                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- ================================================= --}}
                {{-- NUMERIC-ONLY PAGINATION --}}
                {{-- ================================================= --}}

                @if($jobs->hasPages())

                    <div class="d-flex justify-content-center mt-4">

                        <nav aria-label="Print job pagination">

                            <ul class="pagination">

                                @for(
                                    $page = 1;
                                    $page <= $jobs->lastPage();
                                    $page++
                                )

                                    <li
                                        class="page-item
                                            {{ $page == $jobs->currentPage()
                                                ? 'active'
                                                : ''
                                            }}"
                                    >

                                        <a
                                            class="page-link"
                                            href="{{ $jobs->url($page) }}"
                                        >
                                            {{ $page }}
                                        </a>

                                    </li>

                                @endfor

                            </ul>

                        </nav>

                    </div>

                @endif

            </div>

        </div>

    </form>

</div>


{{-- ============================================================= --}}
{{-- SELECT ALL SCRIPT --}}
{{-- ============================================================= --}}

<script>

    const selectAll = document.getElementById('selectAll');

    if (selectAll) {

        selectAll.addEventListener('change', function () {

            document
                .querySelectorAll('.job-checkbox')
                .forEach(function (checkbox) {

                    checkbox.checked = selectAll.checked;

                });

        });

    }

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
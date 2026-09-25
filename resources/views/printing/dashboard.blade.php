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

</head>

<body class="bg-light">

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">
                🖨️ Printing Management Dashboard
            </h2>

            <p class="text-muted mb-0">
                Monitor invoices, printers and print jobs.
            </p>

        </div>

        <div>

            <a href="{{ route('invoice.preview') }}"
               class="btn btn-primary">

                📄 Invoice Preview

            </a>

            <a href="{{ route('printing.printers') }}"
               class="btn btn-dark">

                🖨️ Printers

            </a>

        </div>

    </div>


    {{-- Success message --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- Error message --}}

    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- Statistics --}}

    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Total Print Jobs
                    </div>

                    <h2 class="fw-bold">
                        {{ $totalJobs }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Successful Jobs
                    </div>

                    <h2 class="fw-bold text-success">
                        {{ $successfulJobs }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Failed Jobs
                    </div>

                    <h2 class="fw-bold text-danger">
                        {{ $failedJobs }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="text-muted">
                        Today's Jobs
                    </div>

                    <h2 class="fw-bold">
                        {{ $todayJobs }}
                    </h2>

                </div>

            </div>

        </div>

    </div>


    {{-- Second statistics row --}}

    <div class="row g-4 mb-4">

        <div class="col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h6 class="text-muted">
                        Pending Print Jobs
                    </h6>

                    <h3>
                        {{ $pendingJobs }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h6 class="text-muted">
                        Successful Invoice Value
                    </h6>

                    <h3>
                        ₹{{ number_format($totalAmount, 2) }}
                    </h3>

                </div>

            </div>

        </div>

    </div>


{{-- Printer quick overview --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <div class="d-flex justify-content-between">

            <h5 class="fw-bold">
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

                            Printer ID:
                            {{ $printer['id'] }}

                        </small>

                        <br>

                        @if(
                            strtolower(
                                (string) $printer['state']
                            ) === 'online'
                        )

                            <span class="badge bg-success mt-2">
                                Online
                            </span>

                        @elseif(
                            strtolower(
                                (string) $printer['state']
                            ) === 'offline'
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


    {{-- Search and filters --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                Print Job History
            </h5>

            <form method="GET"
                  action="{{ route('printing.dashboard') }}">

                <div class="row g-3">

                    <div class="col-md-4">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search order, customer or printer..."
                            value="{{ request('search') }}"
                        >

                    </div>


                    <div class="col-md-3">

                        <select name="status"
                                class="form-select">

                            <option value="">
                                All Statuses
                            </option>

                            <option value="success"
                                @selected(request('status') === 'success')>

                                Successful

                            </option>

                            <option value="failed"
                                @selected(request('status') === 'failed')>

                                Failed

                            </option>

                            <option value="pending"
                                @selected(request('status') === 'pending')>

                                Pending

                            </option>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <input
                            type="date"
                            name="date"
                            class="form-control"
                            value="{{ request('date') }}"
                        >

                    </div>


                    <div class="col-md-2">

                        <button class="btn btn-primary w-100">

                            Search

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Print job table --}}

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>Order</th>

                            <th>Customer</th>

                            <th>Printer</th>

                            <th>Total</th>

                            <th>Status</th>

                            <th>Printed At</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($jobs as $job)

                            <tr>

                                <td>
                                    {{ $job->id }}
                                </td>

                                <td>
                                    #{{ $job->order_id }}
                                </td>

                                <td>
                                    {{ $job->customer }}
                                </td>

                                <td>

                                    {{ $job->printer_name ?? 'Unknown' }}

                                    <br>

                                    <small class="text-muted">
                                        ID:
                                        {{ $job->printer_id ?? '-' }}
                                    </small>

                                </td>

                                <td>
                                    ₹{{ number_format(
                                        $job->total,
                                        2
                                    ) }}
                                </td>

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

                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    {{ $job->printed_at
                                        ? $job->printed_at->format(
                                            'd M Y H:i'
                                        )
                                        : '-' }}

                                </td>

                            </tr>

                            @if($job->status === 'failed'
                                && $job->error_message)

                                <tr>

                                    <td colspan="7">

                                        <div class="alert alert-danger mb-0">

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

                                <td colspan="7"
                                    class="text-center py-4">

                                    No print jobs found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            <div class="mt-3">

                {{ $jobs->links() }}

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>
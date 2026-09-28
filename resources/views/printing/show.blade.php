<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Print Job #{{ $printJob->id }}
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    {{-- Header --}}

    <div
        class="d-flex
               justify-content-between
               align-items-center
               mb-4"
    >

        <div>

            <h2 class="fw-bold mb-1">

                🖨️ Print Job Details

            </h2>

            <p class="text-muted mb-0">

                Job #{{ $printJob->id }}

            </p>

        </div>

        <a
            href="{{ route(
                'printing.dashboard'
            ) }}"
            class="btn btn-secondary"
        >
            ← Dashboard
        </a>

    </div>


    {{-- Status --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold">
                Print Status
            </h5>

            <hr>

            @if($printJob->status === 'success')

                <span
                    class="badge bg-success fs-6"
                >
                    SUCCESS
                </span>

            @elseif($printJob->status === 'failed')

                <span
                    class="badge bg-danger fs-6"
                >
                    FAILED
                </span>

            @else

                <span
                    class="badge
                           bg-warning
                           text-dark
                           fs-6"
                >
                    PENDING
                </span>

            @endif

        </div>

    </div>


    {{-- Details --}}

    <div class="row g-4">

        <div class="col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        Invoice Information
                    </h5>


                    <div class="mb-3">

                        <small class="text-muted">
                            Order ID
                        </small>

                        <div class="fw-bold">
                            #{{ $printJob->order_id }}
                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">
                            Customer
                        </small>

                        <div class="fw-bold">
                            {{ $printJob->customer }}
                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">
                            Total
                        </small>

                        <div class="fw-bold fs-5">
                            ₹{{ number_format(
                                $printJob->total,
                                2
                            ) }}
                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">
                            File Name
                        </small>

                        <div>
                            {{ $printJob->file_name ?? '-' }}
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        Printer Information
                    </h5>


                    <div class="mb-3">

                        <small class="text-muted">
                            Printer ID
                        </small>

                        <div class="fw-bold">
                            {{ $printJob->printer_id }}
                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">
                            Printer Name
                        </small>

                        <div class="fw-bold">
                            {{ $printJob->printer_name }}
                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">
                            Printed At
                        </small>

                        <div>
                            {{ $printJob->printed_at
                                ? $printJob->printed_at
                                    ->format(
                                        'd M Y H:i:s'
                                    )
                                : '-' }}
                        </div>

                    </div>


                    <div class="mb-3">

                        <small class="text-muted">
                            Created At
                        </small>

                        <div>
                            {{ $printJob->created_at
                                ? $printJob->created_at
                                    ->format(
                                        'd M Y H:i:s'
                                    )
                                : '-' }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Error --}}

    @if($printJob->error_message)

        <div
            class="alert alert-danger mt-4"
        >

            <strong>
                Print Error:
            </strong>

            <br>

            {{ $printJob->error_message }}

        </div>

    @endif


    {{-- Actions --}}

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                Actions
            </h5>


            @if(
                $printJob->file_path &&
                \Illuminate\Support\Facades\File::exists(
                    $printJob->file_path
                )
            )

                <a
                    href="{{ route(
                        'printing.job.download',
                        $printJob
                    ) }}"
                    class="btn btn-success"
                >
                    📥 Download PDF
                </a>

            @endif


            @if($printJob->status === 'failed')

                <form
                    method="POST"
                    action="{{ route(
                        'printing.job.retry',
                        $printJob
                    ) }}"
                    class="d-inline"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-warning"
                        onclick="
                            return confirm(
                                'Retry this failed print job?'
                            )
                        "
                    >
                        🔄 Retry Print
                    </button>

                </form>

            @endif


            @if($printJob->status === 'success')

                <form
                    method="POST"
                    action="{{ route(
                        'printing.job.reprint',
                        $printJob
                    ) }}"
                    class="d-inline"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-primary"
                        onclick="
                            return confirm(
                                'Reprint this invoice?'
                            )
                        "
                    >
                        🖨️ Reprint
                    </button>

                </form>

            @endif


            <form
                method="POST"
                action="{{ route(
                    'printing.job.destroy',
                    $printJob
                ) }}"
                class="d-inline"
            >

                @csrf

                @method('DELETE')

                <button
                    type="submit"
                    class="btn btn-danger"
                    onclick="
                        return confirm(
                            'Delete this print job?'
                        )
                    "
                >
                    🗑️ Delete
                </button>

            </form>

        </div>

    </div>

</div>

</body>

</html>
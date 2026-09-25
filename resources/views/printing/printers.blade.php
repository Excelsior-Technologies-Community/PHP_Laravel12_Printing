<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Printer Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f6fa;
        }

        .printer-card {
            transition: .2s;
        }

        .printer-card:hover {
            transform: translateY(-3px);
        }

        .printer-icon {
            font-size: 42px;
        }
    </style>

</head>

<body>

<div class="container py-5">

    <div class="d-flex justify-content-between
                align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                🖨️ Printer Management
            </h2>

            <p class="text-muted mb-0">
                Printers connected through PrintNode.
            </p>

        </div>

        <a
            href="{{ route('printing.dashboard') }}"
            class="btn btn-secondary"
        >
            ← Dashboard
        </a>

    </div>


    @if($printers->count() > 0)

        <div class="row g-4">

            @foreach($printers as $printer)

                <div class="col-md-6 col-lg-4">

                    <div class="card
                                printer-card
                                border-0
                                shadow-sm
                                h-100">

                        <div class="card-body">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-start">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        {{ $printer['name'] }}

                                    </h5>

                                    <span class="badge
                                                 bg-success">

                                        Connected

                                    </span>

                                </div>

                                <div class="printer-icon">
                                    🖨️
                                </div>

                            </div>

                            <hr>


                            {{-- Printer ID --}}

                            <div class="mb-3">

                                <small class="text-muted">
                                    Printer ID
                                </small>

                                <div class="fw-semibold">

                                    {{ $printer['id'] }}

                                </div>

                            </div>


                            {{-- Printer State --}}

                            <div class="mb-3">

                                <small class="text-muted">
                                    State
                                </small>

                                <div>

                                    @if(
                                        strtolower(
                                            (string) $printer['state']
                                        ) === 'online'
                                    )

                                        <span
                                            class="badge bg-success"
                                        >
                                            Online
                                        </span>

                                    @elseif(
                                        strtolower(
                                            (string) $printer['state']
                                        ) === 'offline'
                                    )

                                        <span
                                            class="badge bg-danger"
                                        >
                                            Offline
                                        </span>

                                    @else

                                        <span
                                            class="badge bg-secondary"
                                        >
                                            {{ $printer['state'] }}
                                        </span>

                                    @endif

                                </div>

                            </div>


                            {{-- Computer --}}

                            <div class="mb-3">

                                <small class="text-muted">
                                    Computer
                                </small>

                                <div class="fw-semibold">

                                    {{ $printer['computer'] }}

                                </div>

                            </div>


                            {{-- Description --}}

                            @if(
                                !empty($printer['description'])
                            )

                                <div class="mb-3">

                                    <small class="text-muted">
                                        Description
                                    </small>

                                    <div>

                                        {{ $printer['description'] }}

                                    </div>

                                </div>

                            @endif


                            {{-- Print button --}}

                            <form
                                method="POST"
                                action="{{ route(
                                    'printing.print'
                                ) }}"
                            >

                                @csrf

                                <input
                                    type="hidden"
                                    name="printer_id"
                                    value="{{ $printer['id'] }}"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100"
                                    @disabled(
                                        empty($printer['id'])
                                    )
                                >

                                    🖨️ Print Test Invoice

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="alert alert-warning">

            <h5 class="fw-bold">
                No printers found
            </h5>

            <p class="mb-0">

                Make sure the PrintNode client is
                running and your printers are connected.

            </p>

        </div>

    @endif

</div>

</body>

</html>
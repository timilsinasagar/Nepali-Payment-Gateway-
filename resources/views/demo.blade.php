{{-- resources/views/demo.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>eSewa / Khalti Payment Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5" style="max-width: 640px;">

        <h1 class="h3 mb-4">Nepal Payment Demo</h1>

        @if(session('error'))
        <div class="alert alert-danger" role="alert">
            <strong>Error:</strong> {{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('result'))
        @php($result = session('result'))
        <div class="card shadow-sm mb-4 {{ $result->isSuccessful() ? 'border-success-subtle' : 'border-warning-subtle' }}">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">
                    Verification Result
                    <span class="badge {{ $result->isSuccessful() ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $result->status->value }}
                    </span>
                </h2>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">Gateway</div>
                        <div class="fw-semibold">{{ ucfirst($result->gateway->value) }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Amount (NPR)</div>
                        <div class="fw-semibold">{{ number_format($result->amount, 2) }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Gateway Reference</div>
                        <div class="fw-semibold">{{ $result->gatewayReference }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('nepal-payment.demo.pay') }}" class="row g-3">
                    @csrf

                    <div class="col-12">
                        <label class="form-label fw-semibold">Gateway</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gateway" id="gw-esewa" value="esewa" checked>
                                <label class="form-check-label" for="gw-esewa">eSewa</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gateway" id="gw-khalti" value="khalti">
                                <label class="form-check-label" for="gw-khalti">Khalti</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="amount" class="form-label fw-semibold">Amount (NPR)</label>
                        <input type="number" step="0.01" min="1" name="amount" id="amount"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', 100) }}">
                        @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="order_name" class="form-label fw-semibold">Order Description</label>
                        <input type="text" name="order_name" id="order_name"
                            class="form-control @error('order_name') is-invalid @enderror"
                            value="{{ old('order_name', 'Test Order') }}">
                        @error('order_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">Pay</button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-muted small mt-3">
            Sandbox mode uses eSewa's public test credentials automatically. For Khalti, set
            <code>KHALTI_SECRET_KEY</code> in your <code>.env</code> to your test secret key from the
            Khalti merchant dashboard first.
        </p>

    </div>

</body>

</html> 
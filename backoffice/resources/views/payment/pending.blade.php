<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Pending - TAEAB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .payment-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
        }

        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #fef3c7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .status-icon i {
            font-size: 2.5rem;
            color: #f59e0b;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="payment-card text-center">
            <div class="status-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h2 class="mb-3">Payment Pending</h2>
            <p class="text-muted mb-4">
                Your payment request is pending approval. Once approved by our team, you will receive a payment link to
                complete your transaction.
            </p>
            <div class="card bg-light p-3 mb-3">
                <p class="mb-1"><strong>Transaction ID:</strong> {{ $payment->transaction_id }}</p>
                <p class="mb-1"><strong>Amount:</strong> {{ $payment->currency }}
                    {{ number_format($payment->amount, 2) }}</p>
                <p class="mb-0"><strong>Package:</strong> {{ $payment->package->name ?? 'N/A' }}</p>
            </div>
            <p class="text-muted small">
                If you have any questions, please contact our support team.
            </p>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>

</html>

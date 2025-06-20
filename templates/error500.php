<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Server Error - IST Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            max-width: 600px;
            padding: 2rem;
            text-align: center;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .error-details {
            color: #495057;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1 class="error-message">Internal Server Error</h1>
        <p class="error-details">
            We apologize, but something went wrong on our end.<br>
            Our technical team has been notified and is working to resolve the issue.
        </p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="/" class="btn btn-primary px-4 me-sm-3">Return Home</a>
            <button onclick="window.location.reload()" class="btn btn-outline-secondary px-4">Try Again</button>
        </div>
    </div>
</body>
</html>
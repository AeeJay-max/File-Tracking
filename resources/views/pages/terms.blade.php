<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Use &mdash; FileTrack Office Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
</head>
<body>
<div class="container py-5" style="max-width:800px;">
    <a href="{{ route('welcome') }}" class="btn btn-outline-secondary btn-sm mb-4">&larr; Back to Home</a>
    <h1 class="fw-800 mb-4">Terms of Use</h1>
    <p class="text-muted">Last updated: {{ date('F Y') }}</p>
    <hr>
    <h5>1. Authorized Use</h5>
    <p>This portal is authorized for use by government department personnel only. Unauthorized access is prohibited and may be subject to legal action.</p>
    <h5>2. User Responsibilities</h5>
    <p>Users are responsible for maintaining the confidentiality of their credentials and for all actions taken under their account.</p>
    <h5>3. Data Integrity</h5>
    <p>Users must not alter, delete, or manipulate official file records without proper authorization.</p>
    <h5>4. Monitoring</h5>
    <p>All activity within this system is logged for audit purposes.</p>
</div>
</body>
</html>

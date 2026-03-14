<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Migration Trigger</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f8fb; color: #1f2937; padding: 2rem; }
        .card { max-width: 900px; margin: 0 auto; background: #fff; border: 1px solid #dbe3ef; border-radius: 12px; padding: 1.5rem; }
        pre { background: #111827; color: #f9fafb; padding: 1rem; border-radius: 8px; overflow-x: auto; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Migrations Executed</h1>
        <p>The application ran <code>php artisan migrate --force</code>.</p>
        <pre>{{ $output ?: 'No output returned.' }}</pre>
    </div>
</body>
</html>

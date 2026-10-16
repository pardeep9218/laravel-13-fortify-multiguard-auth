<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <style>
        :root { --bg:#f4f5f7; --card:#fff; --text:#1f2933; --muted:#667085; --line:#d0d5dd; --accent:#4f46e5; --err:#b42318; --ok:#067647; }
        @media (prefers-color-scheme: dark) {
            :root { --bg:#101319; --card:#181c24; --text:#e6e8ec; --muted:#98a2b3; --line:#2f3644; --accent:#818cf8; --err:#f97066; --ok:#47cd89; }
        }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif; }
        .card { width:100%; max-width:400px; margin:24px; padding:28px; background:var(--card);
                border:1px solid var(--line); border-radius:12px; }
        h1 { margin:0 0 20px; font-size:1.4rem; }
        label { display:block; margin:14px 0 6px; font-size:.9rem; color:var(--muted); }
        input[type=text], input[type=email], input[type=password] {
            width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px;
            background:transparent; color:var(--text); font-size:1rem; }
        button { width:100%; margin-top:20px; padding:11px; border:0; border-radius:8px;
                 background:var(--accent); color:#fff; font-size:1rem; cursor:pointer; }
        .remember { display:flex; align-items:center; gap:8px; margin-top:14px; color:var(--muted); font-size:.9rem; }
        .remember input { width:auto; }
        .error { margin:6px 0 0; color:var(--err); font-size:.85rem; }
        .status { margin:0 0 16px; color:var(--ok); font-size:.9rem; }
        .links { margin-top:18px; display:flex; justify-content:space-between; font-size:.9rem; }
        a { color:var(--accent); text-decoration:none; }
        p.note { color:var(--muted); font-size:.9rem; line-height:1.5; }
    </style>
</head>
<body>
    <main class="card">
        <h1>@yield('heading')</h1>
        @if (session('status') && session('status') !== 'verification-link-sent')
            <p class="status">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>

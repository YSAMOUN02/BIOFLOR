<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in &middot; {{ brand() }} admin</title>
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<main class="login">
    <div class="login__card">
        <img src="{{ asset('img/favicon.svg') }}" alt="{{ brand() }}">
        <h1>Sign in</h1>
        <p style="color:var(--muted);margin-top:-.25rem">Content administration</p>

        <form method="post" action="{{ route('admin.login.attempt') }}" class="form-grid" style="gap:1rem">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="field field--check">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Keep me signed in</label>
            </div>

            <button class="btn btn--primary" type="submit" style="width:100%;padding:.7rem">Sign in</button>
        </form>

        <p class="login__hint">
            Seeded account: <strong>admin@sbglobal.test</strong> / <strong>password</strong><br>
            Change this before deploying anywhere public.
        </p>
    </div>
</main>
</body>
</html>

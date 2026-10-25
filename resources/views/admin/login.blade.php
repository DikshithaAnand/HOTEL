
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Temairazu Hotel</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #18332e;
            background: linear-gradient(135deg, #eef4f0, #f8f5ee);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: #fff;
            border: 1px solid #e2e9e4;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(25, 54, 45, .09);
        }

        .brand {
            margin-bottom: 32px;
            text-align: center;
        }

        .brand-mark {
            display: inline-flex;
            width: 52px;
            height: 52px;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            color: #fff;
            background: #214b40;
            border-radius: 14px;
            font-size: 23px;
            font-weight: 700;
        }

        .brand h1 {
            margin: 0;
            font-size: 23px;
            letter-spacing: -.5px;
        }

        .brand p {
            margin: 7px 0 0;
            color: #7b8982;
            font-size: 12px;
            letter-spacing: 1.8px;
        }

        h2 { margin: 0 0 8px; font-size: 22px; }

        .subtitle {
            margin: 0 0 26px;
            color: #718078;
            font-size: 14px;
            line-height: 1.6;
        }

        label {
            display: block;
            margin: 18px 0 8px;
            font-size: 13px;
            font-weight: 600;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dce4df;
            border-radius: 9px;
            outline: none;
            font: inherit;
            font-size: 14px;
        }

        input:focus {
            border-color: #477c69;
            box-shadow: 0 0 0 3px rgba(71, 124, 105, .12);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 18px 0 22px;
            color: #64736b;
            font-size: 13px;
        }

        .remember label { margin: 0; font-weight: 400; }

        button {
            width: 100%;
            padding: 14px;
            color: white;
            background: #214b40;
            border: 0;
            border-radius: 9px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        button:hover { background: #173b32; }

        .error {
            margin-top: 7px;
            color: #b42318;
            font-size: 12px;
        }

        .alert {
            padding: 12px;
            color: #9e2b25;
            background: #fff0ef;
            border-radius: 8px;
            font-size: 13px;
        }

        .footer {
            margin-top: 25px;
            color: #94a099;
            text-align: center;
            font-size: 11px;
        }

        @media (max-width: 480px) {
            .login-card { padding: 28px 22px; }
        }
    </style>
</head>
<body>
    <main class="login-card">
        <header class="brand">
            <div class="brand-mark">T</div>
            <h1>Temairazu Hotel</h1>
            <p>HOTEL MANAGEMENT · TOKYO</p>
        </header>

        <h2>Administrator sign in</h2>
        <p class="subtitle">Sign in to manage hotel operations, bookings and room availability.</p>

        @if ($errors->any())
            <div class="alert" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <label for="email">Administrator email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                autofocus
            >

            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <div class="remember">
                <input id="remember" type="checkbox" name="remember" value="1">
                <label for="remember">Remember me on this device</label>
            </div>

            <button type="submit">Sign in to dashboard</button>
        </form>

        <div class="footer">
            Secure administrator access · Temairazu Hotel
        </div>
    </main>
</body>
</html>

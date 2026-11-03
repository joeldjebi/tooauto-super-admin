<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="shortcut icon" type="image/x-icon" href="/dist-assets/images/logo.png">
    <title>Connexion Call Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cc-font: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", "Helvetica Neue", Arial, sans-serif;
            --cc-label: #1d1d1f;
            --cc-label-2: #6e6e73;
            --cc-blue: #0071e3;
        }
        * {
            box-sizing: border-box;
        }
        body {
            align-items: center;
            background:
                radial-gradient(1200px 600px at 15% -10%, rgba(0, 113, 227, .14), transparent 60%),
                radial-gradient(900px 500px at 100% 110%, rgba(175, 82, 222, .12), transparent 60%),
                #f5f5f7;
            color: var(--cc-label);
            display: flex;
            font-family: var(--cc-font);
            justify-content: center;
            letter-spacing: -.01em;
            margin: 0;
            min-height: 100vh;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }
        .cc-login {
            max-width: 400px;
            width: 100%;
        }
        .cc-login-card {
            background: rgba(255, 255, 255, .82);
            -webkit-backdrop-filter: saturate(180%) blur(24px);
            backdrop-filter: saturate(180%) blur(24px);
            border: 1px solid rgba(255, 255, 255, .7);
            border-radius: 24px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04), 0 24px 60px rgba(0, 0, 0, .08);
            padding: 40px 32px 32px;
        }
        .cc-logo {
            display: block;
            border-radius: 15px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .12);
            height: 64px;
            margin: 0 auto 20px;
            object-fit: contain;
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -.028em;
            margin: 0;
            text-align: center;
        }
        .cc-sub {
            color: var(--cc-label-2);
            font-size: 15px;
            line-height: 1.45;
            margin: 8px 0 28px;
            text-align: center;
        }
        .cc-fields {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .14);
            border-radius: 12px;
            overflow: hidden;
            transition: box-shadow .15s ease, border-color .15s ease;
        }
        .cc-fields:focus-within {
            border-color: var(--cc-blue);
            box-shadow: 0 0 0 4px rgba(0, 113, 227, .18);
        }
        .cc-field {
            display: block;
            padding: 10px 14px 8px;
            position: relative;
        }
        .cc-field + .cc-field {
            border-top: 1px solid rgba(0, 0, 0, .1);
        }
        .cc-field span {
            color: var(--cc-label-2);
            display: block;
            font-size: 12px;
            font-weight: 500;
        }
        .cc-field input {
            background: transparent;
            border: 0;
            color: var(--cc-label);
            font-family: inherit;
            font-size: 17px;
            letter-spacing: -.01em;
            outline: 0;
            padding: 2px 0 0;
            width: 100%;
        }
        .cc-submit {
            background: var(--cc-blue);
            border: 0;
            border-radius: 12px;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            font-size: 17px;
            font-weight: 500;
            margin-top: 20px;
            padding: 14px;
            transition: background .15s ease, transform .1s ease;
            width: 100%;
        }
        .cc-submit:hover {
            background: #0077ed;
        }
        .cc-submit:active {
            transform: scale(.985);
        }
        .cc-submit:focus-visible {
            box-shadow: 0 0 0 4px rgba(0, 113, 227, .3);
            outline: 0;
        }
        .alert {
            background: rgba(0, 113, 227, .1);
            border-radius: 12px;
            color: #00509e;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 12px 14px;
        }
        .alert-danger { background: rgba(255, 59, 48, .12); color: #a3000f; }
        .alert-success { background: rgba(52, 199, 89, .14); color: #1b6b30; }
        .alert-warning { background: rgba(255, 149, 0, .15); color: #9a4a00; }
        .cc-foot {
            color: var(--cc-label-2);
            font-size: 12px;
            margin-top: 20px;
            text-align: center;
        }
        @media (max-width: 480px) {
            .cc-login-card {
                padding: 32px 20px 24px;
            }
        }
    </style>
</head>
<body>
    <main class="cc-login">
        <div class="cc-login-card">
            <img class="cc-logo" src="/dist-assets/images/logo.png" alt="Logo" />
            <h1>Call Center</h1>
            <p class="cc-sub">Connectez-vous avec l'URL dediee du call center.</p>

            @if(session()->has("message"))
                <div class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
            @endif

            <form action="{{ route('call-center.authenticate') }}" method="POST">
                @csrf
                <div class="cc-fields">
                    <label class="cc-field" for="email">
                        <span>Adresse email</span>
                        <input name="email" id="email" type="email" value="{{ old('email') }}" autocomplete="username" autofocus required />
                    </label>
                    <label class="cc-field" for="password">
                        <span>Mot de passe numerique</span>
                        <input name="password" id="password" type="password" inputmode="numeric" pattern="[0-9]*" autocomplete="current-password" required />
                    </label>
                </div>
                <button type="submit" class="cc-submit">Se connecter</button>
            </form>
        </div>
        <p class="cc-foot">Espace reserve aux agents du call center.</p>
    </main>
</body>
</html>

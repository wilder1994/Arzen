<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Arzen') }}</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: Figtree, sans-serif;
                background: #000;
                min-height: 100svh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .wrap {
                position: relative;
                width: 100%;
                height: 100svh;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            .hero {
                position: relative;
                z-index: 1;
                width: 100%;
                height: 100%;
                object-fit: fill;
            }
            .cta {
                position: absolute;
                right: 4%;
                top: 4.5%;
                z-index: 2;
            }
            .login-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 9.5rem;
                height: 2.5rem;
                padding: 0 1.15rem;
                border-radius: 0.75rem;
                text-decoration: none;
                color: #ffffff;
                font-weight: 650;
                letter-spacing: 0.06em;
                font-size: 0.82rem;
                text-transform: uppercase;
                background: linear-gradient(180deg, #2a7c88 0%, #1a6572 100%);
                border: 1px solid rgba(196, 255, 246, 0.95);
                box-shadow:
                    0 0 0 1px rgba(120, 236, 220, 0.45),
                    0 0 16px rgba(72, 220, 204, 0.72),
                    0 8px 18px rgba(8, 24, 28, 0.28);
                transition: background 150ms ease, box-shadow 150ms ease, transform 150ms ease;
            }
            .login-button:hover {
                background: linear-gradient(180deg, #1a6572 0%, #124852 100%);
                box-shadow:
                    0 0 0 1px rgba(210, 255, 248, 0.95),
                    0 0 22px rgba(110, 240, 224, 0.9),
                    0 10px 20px rgba(8, 24, 28, 0.32);
                transform: translateY(-1px);
            }
            @media (max-width: 900px) {
                .cta {
                    right: 3.5%;
                    top: 3.5%;
                }
                .login-button {
                    min-width: 0;
                    height: 2.25rem;
                    font-size: 0.72rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="wrap">
            <img src="{{ asset('images/Arzenwelcome.png') }}" alt="Arzen" class="hero" />
            <div class="cta">
                <a href="{{ route('login') }}" class="login-button">{{ __('Iniciar sesion') }}</a>
            </div>
            @include('partials.developer-mark', ['overlay' => true])
        </div>
    </body>
</html>





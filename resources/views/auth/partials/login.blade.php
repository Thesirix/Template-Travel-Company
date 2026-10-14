
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $brand->primary() }}">
    <title>Connexion · {{ $brand->name() }}</title>
    @include('layouts.partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/tc/theme.css') }}?v={{ filemtime(public_path('css/tc/theme.css')) }}">
    <style>
        :root{ {!! $brand->cssVariables() !!} }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; }
        .tc-login { display: grid; grid-template-columns: 1.1fr 1fr; min-height: 100vh; }
        .tc-login__art {
            position: relative; overflow: hidden; color: #fff; padding: 48px;
            display: flex; flex-direction: column; justify-content: space-between;
            background: linear-gradient(135deg, var(--tc-primary-dark), var(--tc-primary) 50%, rgba(var(--tc-accent-rgb), 1) 140%);
        }
        .tc-login__art::before { content: ''; position: absolute; inset: -20%; background: radial-gradient(500px 300px at 20% 20%, rgba(255,255,255,.18), transparent 70%), radial-gradient(500px 360px at 90% 90%, rgba(var(--tc-accent-rgb), .55), transparent 70%); animation: tc-drift 16s ease-in-out infinite alternate; }
        .tc-login__art > * { position: relative; }
        .tc-login__brand { display: flex; align-items: center; gap: 14px; font-weight: 800; font-size: 1.2rem; }
        .tc-login__brand .tc-logo-mark { background: rgba(255,255,255,.18); box-shadow: inset 0 0 0 1px rgba(255,255,255,.3); }
        .tc-login__brand img { max-height: 56px; max-width: 260px; object-fit: contain; }
        .tc-login__pitch h1 { color: #fff; font-size: clamp(2rem, 3.4vw, 3rem); font-weight: 800; line-height: 1.08; letter-spacing: -.03em; margin: 0 0 14px; max-width: 520px; }
        .tc-login__pitch p { color: rgba(255,255,255,.8); font-size: 1.05rem; max-width: 460px; margin: 0; }
        .tc-route { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }
        .tc-route path { fill: none; stroke: rgba(255,255,255,.45); stroke-width: 2; stroke-dasharray: 2 10; stroke-linecap: round; }
        .tc-route circle { fill: #fff; }
        .tc-route .tc-pin { fill: var(--tc-accent); stroke: #fff; stroke-width: 3; }
        .tc-route .tc-pulse { fill: none; stroke: #fff; stroke-width: 2; animation: tc-pulse 2.4s ease-out infinite; transform-origin: center; transform-box: fill-box; }
        @keyframes tc-pulse { from { opacity: .9; transform: scale(.6); } to { opacity: 0; transform: scale(2.6); } }
        .tc-login__chips { display: flex; gap: 10px; flex-wrap: wrap; }
        .tc-login__chips > span { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; background: rgba(255,255,255,.14); backdrop-filter: blur(6px); font-weight: 700; font-size: .82rem; border: 1px solid rgba(255,255,255,.2); }
        .tc-login__form { display: flex; align-items: center; justify-content: center; padding: 48px 24px; }
        .tc-login__card { width: 100%; max-width: 420px; animation: tc-rise .7s var(--tc-ease) both; }
        .tc-login__space { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; font-weight: 800; font-size: .75rem; letter-spacing: .06em; text-transform: uppercase; background: rgba(var(--tc-primary-rgb), .1); color: var(--tc-primary); }
        .tc-login__card h2 { font-size: 2rem; font-weight: 800; margin: 16px 0 6px; }
        .tc-login__card > p { color: var(--tc-muted); font-weight: 600; margin-bottom: 30px; }
        .tc-field { position: relative; margin-bottom: 18px; }
        .tc-field i.tc-field__icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--tc-muted); transition: color .2s; }
        .tc-field input {
            width: 100%; height: 56px; padding: 18px 46px 6px 46px; border-radius: 14px; border: 1px solid var(--tc-border-strong);
            background: var(--tc-surface); font: inherit; font-weight: 600; color: var(--tc-text); outline: 0; transition: border-color .2s, box-shadow .2s;
        }
        .tc-field label { position: absolute; left: 46px; top: 50%; transform: translateY(-50%); margin: 0; font-size: .9rem; font-weight: 600; color: var(--tc-muted); pointer-events: none; transition: all .2s var(--tc-ease); }
        .tc-field input:focus { border-color: var(--tc-primary); box-shadow: var(--tc-ring); }
        .tc-field input:focus ~ i.tc-field__icon { color: var(--tc-primary); }
        .tc-field input:focus ~ label, .tc-field input:not(:placeholder-shown) ~ label { top: 15px; font-size: .7rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: var(--tc-primary); }
        .tc-field.is-invalid input { border-color: var(--tc-danger); animation: tc-shake .4s; }
        @keyframes tc-shake { 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
        .tc-field__eye { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border: 0; border-radius: 10px; background: transparent; color: var(--tc-muted); cursor: pointer; }
        .tc-field__eye:hover { background: var(--tc-surface-2); color: var(--tc-text); }
        .tc-login__submit { width: 100%; height: 54px; font-size: 1rem; border-radius: 14px; margin-top: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 10px; }
        .tc-login__submit .fa-spinner { display: none; }
        .tc-login__submit.is-loading .fa-spinner { display: inline-block; }
        .tc-login__submit.is-loading .fa-arrow-right { display: none; }
        .tc-login__submit .fa-arrow-right { transition: transform .25s var(--tc-spring); }
        .tc-login__submit:hover .fa-arrow-right { transform: translateX(4px); }
        .tc-login__switch { margin-top: 26px; text-align: center; color: var(--tc-muted); font-weight: 600; font-size: .88rem; }
        .tc-caps { display: none; margin: -8px 0 14px; font-size: .78rem; font-weight: 700; color: var(--tc-warning); }
        @media (max-width: 900px) { .tc-login { grid-template-columns: 1fr; } .tc-login__art { min-height: 260px; padding: 32px; } .tc-login__pitch h1 { font-size: 1.8rem; } .tc-login__chips { display: none; } }
    </style>
</head>
<body>
<div class="tc-login">
    <section class="tc-login__art">
        <svg class="tc-route" viewBox="0 0 600 700" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <path id="tcRoutePath" d="M70 560 C 180 420, 260 520, 340 360 S 480 160, 540 120"/>
            <circle cx="70" cy="560" r="7" class="tc-pin"/>
            <circle cx="540" cy="120" r="7" class="tc-pin"/>
            <circle cx="540" cy="120" r="7" class="tc-pulse"/>
            <g>
                <text font-size="26" text-anchor="middle" dominant-baseline="central" fill="#fff">✈</text>
                <animateMotion dur="7s" repeatCount="indefinite" rotate="auto" keyPoints="0;1" keyTimes="0;1" calcMode="spline" keySplines=".45 0 .55 1">
                    <mpath href="#tcRoutePath"/>
                </animateMotion>
            </g>
        </svg>

        <div class="tc-login__brand">
            @if($brand->logoOnDarkUrl())
                <img src="{{ $brand->logoOnDarkUrl() }}" alt="{{ $brand->name() }}">
            @else
                <span class="tc-logo-mark">{{ $brand->initials() }}</span>
                {{ $brand->name() }}
            @endif
        </div>
        <div class="tc-login__pitch">
            <h1>{{ $tagline }}</h1>
            <p>Vols, bateau, devis, clients et planning de l'équipe, réunis dans un seul espace.</p>
        </div>
        <div class="tc-login__chips">
            @foreach($brand->destinations()->take(4) as $destination)
                <span><span class="tc-dot" style="background: {{ $destination->color }}"></span> {{ $destination->name }}</span>
            @endforeach
        </div>
    </section>

    <section class="tc-login__form">
        <form class="tc-login__card" method="POST" action="{{ $action }}" id="loginForm" novalidate>
            @csrf
            <h2><span data-tc-greeting>Bonjour</span> 👋</h2>
            <p>Connectez-vous pour continuer.</p>

            <div class="tc-field @error('email') is-invalid @enderror">
                <input type="email" id="email" name="email" value="{{ old('email', request('email')) }}" placeholder=" " required autocomplete="email" autofocus>
                <i class="fa-solid fa-envelope tc-field__icon"></i>
                <label for="email">Adresse e-mail</label>
            </div>
            <div class="tc-field @error('password') is-invalid @enderror">
                <input type="password" id="password" name="password" placeholder=" " required autocomplete="current-password">
                <i class="fa-solid fa-lock tc-field__icon"></i>
                <label for="password">Mot de passe</label>
                <button type="button" class="tc-field__eye" aria-label="Afficher le mot de passe"><i class="fa-solid fa-eye"></i></button>
            </div>
            <div class="tc-caps" id="capsWarn"><i class="fa-solid fa-triangle-exclamation"></i> Majuscules activées</div>

            <button type="submit" class="btn btn-primary tc-login__submit">
                Se connecter <i class="fa-solid fa-arrow-right"></i><i class="fa-solid fa-spinner fa-spin"></i>
            </button>


        </form>
    </section>
</div>

<script src="{{ asset('js/tc/ui.js') }}?v={{ filemtime(public_path('js/tc/ui.js')) }}"></script>
@include('layouts.partials.flash')
<script>
    (function () {
        const form = document.getElementById('loginForm');
        const pwd = document.getElementById('password');
        const eye = form.querySelector('.tc-field__eye');
        eye.addEventListener('click', () => {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            eye.innerHTML = `<i class="fa-solid ${show ? 'fa-eye-slash' : 'fa-eye'}"></i>`;
            pwd.focus();
        });
        pwd.addEventListener('keyup', (e) => {
            document.getElementById('capsWarn').style.display = e.getModifierState && e.getModifierState('CapsLock') ? 'block' : 'none';
        });
        form.addEventListener('submit', (e) => {
            const empty = [...form.querySelectorAll('input[required]')].filter((i) => !i.value.trim());
            form.querySelectorAll('.tc-field').forEach((f) => f.classList.remove('is-invalid'));
            if (empty.length) {
                e.preventDefault();
                empty.forEach((i) => { void i.parentElement.offsetWidth; i.parentElement.classList.add('is-invalid'); });
                TC.toast.warning('Renseignez votre e-mail et votre mot de passe.');
                empty[0].focus();
                return;
            }
            form.querySelector('.tc-login__submit').classList.add('is-loading');
        });
    })();
</script>
</body>
</html>

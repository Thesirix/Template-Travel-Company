
@php
    $displayName = trim(($user->prenom ?? '') . ' ' . ($user->name ?? ''));
    $avatarFile = $user->image ?? null;
    $initials = mb_strtoupper(mb_substr($user->prenom ?? $user->name ?? '?', 0, 1) . mb_substr($user->name ?? '', 0, 1));
@endphp
<header class="tc-topbar tc-topbar--{{ $brand->bannerStyle() }}">
    <span class="tc-topbar__fx" aria-hidden="true"><i class="fa-solid fa-plane tc-topbar__plane"></i></span>
    <button type="button" class="tc-topbar__btn" data-tc-toggle="sidebar" aria-label="Afficher ou masquer le menu">
        <i class="fa-solid fa-bars-staggered"></i>
    </button>
    @php($pageTitle = trim(explode('·', html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES))[0]))
    <div class="tc-topbar__title">
        <span class="tc-topbar__space">{{ $space ?? '' }}</span>
        <span class="tc-topbar__sep">›</span>
        <strong data-tc-page-title>{{ $pageTitle && $pageTitle !== $brand->name() ? $pageTitle : '' }}</strong>
    </div>
    <div class="tc-topbar__greet">
        <small><span data-tc-greeting>Bonjour</span>,</small>
        <strong>{{ $user->prenom ?? $user->name }} 👋</strong>
    </div>

    <button type="button" class="tc-search" data-tc-open="palette">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Rechercher une page…</span>
        <kbd>Ctrl K</kbd>
    </button>
    <span class="tc-clock" data-tc-clock></span>

    <div class="dropdown">
        <a class="tc-user" href="#" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @if($avatarFile)
                <img class="tc-avatar" src="{{ asset('uploads/agents/'.$avatarFile) }}" alt="">
            @else
                <span class="tc-avatar">{{ $initials }}</span>
            @endif
            <span class="tc-user__name">{{ $displayName }}<small>{{ $roleLabel }}</small></span>
            <i class="fa-solid fa-chevron-down" style="font-size:.7rem;opacity:.7"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="{{ $profileUrl }}"><i class="fa-solid fa-user"></i> Mon profil</a>
            @isset($settingsUrl)
                <a class="dropdown-item" href="{{ $settingsUrl }}"><i class="fa-solid fa-palette"></i> Identité &amp; couleurs</a>
            @endisset
            <a class="dropdown-item" href="#" data-tc-open="palette"><i class="fa-solid fa-magnifying-glass"></i> Recherche rapide <span class="tc-chip ml-auto">Ctrl K</span></a>
            <div class="dropdown-divider"></div>
            @if(! empty($farewell))
                <a class="dropdown-item text-danger" href="{{ $logoutUrl }}" onclick="showFarewellMessage(event)"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
            @else
                <a class="dropdown-item text-danger" href="{{ $logoutUrl }}"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
            @endif
        </div>
    </div>
</header>

<x-agent.common.operation-failed-modal/>
<x-agent.common.operation-success-modal/>

@if(! empty($farewell))
    <div class="modal fade" id="farewellModal" tabindex="-1" role="dialog" aria-labelledby="farewellTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tc-farewell">
                <div class="tc-farewell__head">
                    <div class="tc-farewell__wave">👋</div>
                    <h5 class="modal-title" id="farewellTitle">À bientôt !</h5>
                </div>
                <div class="modal-body text-center">
                    <p id="farewellMessage" class="mb-0"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Rester connecté</button>
                    <a class="btn btn-primary" href="{{ $logoutUrl }}"><i class="fa-solid fa-right-from-bracket mr-1"></i> Se déconnecter</a>
                </div>
            </div>
        </div>
    </div>
    <style>
        .tc-farewell__head { padding: 28px 24px 8px; text-align: center; color: var(--tc-on-primary); background: linear-gradient(135deg, var(--tc-primary), var(--tc-accent)); }
        .tc-farewell__head .modal-title { color: inherit; font-size: 1.3rem; }
        .tc-farewell__wave { font-size: 2.6rem; display: inline-block; transform-origin: 70% 70%; animation: tc-wave 1.6s ease-in-out infinite; }
        #farewellMessage { font-size: 1.05rem; font-weight: 600; color: var(--tc-text-2); min-height: 3em; }
        #farewellMessage span { display: inline-block; white-space: pre; }
        @keyframes tc-wave { 0%, 60%, 100% { transform: rotate(0); } 10%, 30% { transform: rotate(14deg); } 20% { transform: rotate(-8deg); } 40% { transform: rotate(-4deg); } 50% { transform: rotate(10deg); } }
    </style>
    <script>
        function showFarewellMessage(event) {
            event.preventDefault();
            const pick = (a) => a[Math.floor(Math.random() * a.length)];
            const message = `${pick(['Merci pour votre travail aujourd’hui !', 'Encore une belle journée accomplie !', 'Votre engagement fait la différence !', 'Fin de journée, bon repos !'])} ${pick(['Rechargez vos batteries.', 'Profitez de votre temps libre.', 'On vous attend pour la prochaine session.'])}`;
            const el = document.getElementById('farewellMessage');
            el.innerHTML = '';
            [...message].forEach((letter, i) => {
                const span = document.createElement('span');
                span.textContent = letter;
                span.style.opacity = 0;
                el.appendChild(span);
                if (window.gsap) {
                    gsap.fromTo(span, { opacity: 0, y: 12, rotation: gsap.utils.random(-25, 25) }, { opacity: 1, y: 0, rotation: 0, duration: .45, delay: i * .015, ease: 'back.out(1.7)' });
                } else {
                    span.style.opacity = 1;
                }
            });
            $('#farewellModal').modal('show');
        }
    </script>
@endif

<script>
    // Les modales « opération réussie / échouée » des scripts métier s'affichent en toasts
    document.addEventListener('DOMContentLoaded', function () {
        $(document).on('show.bs.modal', '#operation-success-modal, #operation-failed-modal', function (e) {
            e.preventDefault();
            const ok = this.id === 'operation-success-modal';
            const text = $(this).find('[id$="-message"]').text().trim();
            TC.toast({ type: ok ? 'success' : 'error', message: text || (ok ? 'Opération réussie.' : 'Une erreur est survenue.') });
        });
    });
</script>

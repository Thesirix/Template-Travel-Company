@php
    $adminLink = fn (string $href, string $icon, string $label) => compact('href', 'icon', 'label');
    $sections = [
        'Catalogue' => [
            $adminLink(route('admin.destinations'), 'fa-earth-africa', 'Destinations'),
            $adminLink(route('admin.compagnies'), 'fa-plane-up', 'Compagnies'),
            $adminLink(route('admin.aeroports'), 'fa-plane-arrival', 'Aéroports'),
            $adminLink(route('admin.villes'), 'fa-city', 'Villes'),
            $adminLink(route('admin.pays'), 'fa-flag', 'Pays'),
            $adminLink(route('admin.conditions'), 'fa-scale-balanced', 'Conditions'),
            $adminLink(route('admin.assurances'), 'fa-shield-heart', 'Assurances'),
            $adminLink(route('admin.bateau'), 'fa-ship', 'Bateau'),
        ],
        'Équipe' => [
            $adminLink(route('admin.agences'), 'fa-building', 'Agences'),
            $adminLink(route('admin.agents'), 'fa-user-tie', 'Agents'),
        ],
        'Suivi' => [
            $adminLink(route('admin.clients'), 'fa-users', 'Clients'),
            $adminLink(route('admin.historique'), 'fa-clock-rotate-left', 'Historiques'),
            $adminLink(route('admin.paiements'), 'fa-credit-card', 'Paiements'),
            $adminLink(route('admin.block-power'), 'fa-layer-group', 'Allotements'),
            $adminLink(route('admin.procedures'), 'fa-book-open', 'Procédures'),
            $adminLink(route('admin.taches'), 'fa-list-check', 'To-do list'),
        ],
    ];
@endphp
<aside class="tc-sidebar" id="accordionSidebar">
    @include('layouts.partials.sidebar-brand', ['href' => route('admin.home'), 'subtitle' => 'Administration'])

    <div class="tc-sidebar__scroll">
        <ul class="tc-nav">
            <li class="tc-nav__label">Vols</li>
            @foreach($brand->destinations() as $destination)
                <li>
                    <a class="tc-nav__link collapsed" href="#" data-toggle="collapse" data-target="#adest-{{ $destination->id }}"
                       aria-expanded="false" data-title="{{ $destination->name }}">
                        <span class="tc-nav__icon tc-nav__icon--dest" style="background: {{ $destination->color }}">
                            @if($destination->iconUrl())
                                <img src="{{ $destination->iconUrl() }}" alt="">
                            @else
                                {{ $destination->initials() }}
                            @endif
                        </span>
                        <span class="tc-nav__text">{{ $destination->name }}</span>
                        <i class="fa-solid fa-chevron-right tc-nav__chev"></i>
                    </a>
                    <div id="adest-{{ $destination->id }}" class="collapse" data-parent="#accordionSidebar">
                        <ul class="tc-nav__sub">
                            <li><a class="tc-nav__sublink" href="{{ route('admin.vols', [$destination->slug, 'archive' => 0]) }}"><i class="fa-solid fa-list fa-fw"></i> Vols en vente</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('admin.vols.create', $destination->slug) }}"><i class="fa-solid fa-plus fa-fw"></i> Ajouter un vol</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('admin.vols', [$destination->slug, 'archive' => 1]) }}"><i class="fa-solid fa-box-archive fa-fw"></i> Archives</a></li>
                        </ul>
                    </div>
                </li>
            @endforeach
            <li>
                <a class="tc-nav__link collapsed" href="#" data-toggle="collapse" data-target="#nav-groups" aria-expanded="false" data-title="Groupes de vols">
                    <span class="tc-nav__icon"><i class="fa-solid fa-layer-group"></i></span>
                    <span class="tc-nav__text">Groupes de vols</span>
                    <i class="fa-solid fa-chevron-right tc-nav__chev"></i>
                </a>
                <div id="nav-groups" class="collapse" data-parent="#accordionSidebar">
                    <ul class="tc-nav__sub">
                        <li><a class="tc-nav__sublink" href="{{ route('admin.groupes-vols') }}"><i class="fa-solid fa-list fa-fw"></i> Liste</a></li>
                        <li><a class="tc-nav__sublink" href="{{ route('admin.groupes-vols.create') }}"><i class="fa-solid fa-plus fa-fw"></i> Ajouter</a></li>
                    </ul>
                </div>
            </li>

            @foreach($sections as $title => $links)
                <li class="tc-nav__label">{{ $title }}</li>
                @foreach($links as $link)
                    <li>
                        <a class="tc-nav__link" href="{{ $link['href'] }}" data-title="{{ $link['label'] }}">
                            <span class="tc-nav__icon"><i class="fa-solid {{ $link['icon'] }}"></i></span>
                            <span class="tc-nav__text">{{ $link['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            @endforeach

            <li class="tc-nav__label">Réglages</li>
            <li>
                <a class="tc-nav__link" href="{{ route('admin.parametres') }}" data-title="Paramètres">
                    <span class="tc-nav__icon"><i class="fa-solid fa-palette"></i></span>
                    <span class="tc-nav__text">Identité &amp; couleurs</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="tc-sidebar__foot">
        <div class="tc-sidebar__card">
            <strong>Astuce</strong><br>
            <kbd>Ctrl</kbd> + <kbd>K</kbd> pour aller à n'importe quelle page.
        </div>
    </div>
</aside>

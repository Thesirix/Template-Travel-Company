@php
    $agent = \Illuminate\Support\Facades\Auth::guard('gent')->user();
    $isPartner = (bool) optional($agent?->agence)->is_partner_agency;
    $groupLinks = function ($group) use ($isPartner) {
        $links = [
            ['Vente', 'fa-cart-shopping', 'agent.vols.vente'],
            ['Réservations', 'fa-magnifying-glass', 'agent.vols.reservations'],
        ];
        if (! $isPartner) {
            $links[] = ['Dossiers', 'fa-sliders', 'agent.vols.dossiers'];
            $links[] = ['Émission', 'fa-ticket', 'agent.vols.emission'];
        }
        return $links;
    };
@endphp
<aside class="tc-sidebar" id="accordionSidebar">
    @include('layouts.partials.sidebar-brand', ['href' => route('agent.home'), 'subtitle' => 'Espace agent'])

    <div class="tc-sidebar__scroll">
        <ul class="tc-nav">
            <li>
                <a class="tc-nav__link" href="{{ route('agent.home') }}" data-title="Accueil">
                    <span class="tc-nav__icon"><i class="fa-solid fa-house"></i></span>
                    <span class="tc-nav__text">Accueil</span>
                </a>
            </li>

            <li class="tc-nav__label">Réservations</li>
            @foreach($brand->destinations() as $destination)
                @continue(! $destination->permissionName())
                @can($destination->permissionName())
                    @php
                        $groups = $flightGroups->where('country_id', $destination->country_id);
                        $activeGroups = $groups->where('is_active', 1);
                        $archivedGroups = $groups->where('is_active', 0);
                    @endphp
                    <li>
                        <a class="tc-nav__link collapsed" href="#" data-toggle="collapse" data-target="#dest-{{ $destination->id }}"
                           aria-expanded="false" data-title="{{ $destination->name }}">
                            <span class="tc-nav__icon tc-nav__icon--dest" style="background: {{ $destination->color }}">
                                @if($destination->iconUrl())
                                    <img src="{{ $destination->iconUrl() }}" alt="">
                                @else
                                    {{ $destination->initials() }}
                                @endif
                            </span>
                            <span class="tc-nav__text">{{ $destination->name }}</span>
                            @if($activeGroups->count())
                                <span class="tc-nav__badge">{{ $activeGroups->count() }}</span>
                            @endif
                            <i class="fa-solid fa-chevron-right tc-nav__chev"></i>
                        </a>
                        <div id="dest-{{ $destination->id }}" class="collapse" data-parent="#accordionSidebar">
                            <ul class="tc-nav__sub">
                                @forelse($activeGroups as $group)
                                    <li>
                                        <a class="tc-nav__sublink collapsed" href="#" data-toggle="collapse" data-target="#group-{{ $group->id }}" aria-expanded="false">
                                            {{ $group->name }} <i class="fa-solid fa-chevron-right"></i>
                                        </a>
                                        <div id="group-{{ $group->id }}" class="collapse">
                                            <ul class="tc-nav__sub">
                                                @foreach($groupLinks($group) as [$label, $icon, $routeName])
                                                    <li><a class="tc-nav__sublink" href="{{ route($routeName, $group->id) }}"><i class="fa-solid {{ $icon }} fa-fw"></i> {{ $label }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                @empty
                                    <li><span class="tc-nav__sublink" style="opacity:.6">Aucun groupe de vols actif</span></li>
                                @endforelse

                                @if(! $isPartner && $archivedGroups->count())
                                    <li>
                                        <a class="tc-nav__sublink collapsed" href="#" data-toggle="collapse" data-target="#archives-{{ $destination->id }}" aria-expanded="false">
                                            <i class="fa-solid fa-box-archive fa-fw"></i> Archives <i class="fa-solid fa-chevron-right"></i>
                                        </a>
                                        <div id="archives-{{ $destination->id }}" class="collapse">
                                            <ul class="tc-nav__sub">
                                                @foreach($archivedGroups as $group)
                                                    @foreach($groupLinks($group) as [$label, $icon, $routeName])
                                                        <li><a class="tc-nav__sublink" href="{{ route($routeName, $group->id) }}">{{ $group->name }} · {{ $label }}</a></li>
                                                    @endforeach
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endcan
            @endforeach

            @can('gerer Bateau')
                <li>
                    <a class="tc-nav__link collapsed" href="#" data-toggle="collapse" data-target="#nav-bateau" aria-expanded="false" data-title="Bateau">
                        <span class="tc-nav__icon"><i class="fa-solid fa-ship"></i></span>
                        <span class="tc-nav__text">Bateau</span>
                        <i class="fa-solid fa-chevron-right tc-nav__chev"></i>
                    </a>
                    <div id="nav-bateau" class="collapse" data-parent="#accordionSidebar">
                        <ul class="tc-nav__sub">
                            <li><a class="tc-nav__sublink" href="{{ route('agent.bateau.create') }}">Inscription</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.bateau.pending') }}">À traiter</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.bateau.confirmed') }}">Confirmées</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.bateau.sold') }}">Vendues</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.bateau.vouchers') }}">Bons d'échange</a></li>
                        </ul>
                    </div>
                </li>
            @endcan

            @canany(['gerer Payer', 'gerer devis', 'gerer Clients', 'gerer Extraction'])
                <li class="tc-nav__label">Clients</li>
            @endcanany
            @can('gerer Clients')
                <li>
                    <a class="tc-nav__link" href="{{ route('agent.demandes.create') }}" data-title="Demandes clients">
                        <span class="tc-nav__icon"><i class="fa-solid fa-user-plus"></i></span>
                        <span class="tc-nav__text">Demandes clients</span>
                    </a>
                </li>
            @endcan
            @can('gerer devis')
                <li>
                    <a class="tc-nav__link" href="{{ route('agent.devis') }}" data-title="Devis">
                        <span class="tc-nav__icon"><i class="fa-solid fa-file-invoice"></i></span>
                        <span class="tc-nav__text">Devis</span>
                    </a>
                </li>
            @endcan
            @can('gerer Payer')
                <li>
                    <a class="tc-nav__link" href="{{ route('agent.paiements') }}" data-title="Paiements">
                        <span class="tc-nav__icon"><i class="fa-solid fa-credit-card"></i></span>
                        <span class="tc-nav__text">Paiements</span>
                    </a>
                </li>
            @endcan
            @can('gerer Extraction')
                <li>
                    <a class="tc-nav__link" href="{{ route('agent.extractions') }}" data-title="Extractions">
                        <span class="tc-nav__icon"><i class="fa-solid fa-file-export"></i></span>
                        <span class="tc-nav__text">Extractions</span>
                    </a>
                </li>
            @endcan

            @canany(['gerer Procedure', 'gerer Block power', 'gerer Planning'])
                <li class="tc-nav__label">Outils</li>
            @endcanany
            @can('gerer Planning')
                <li>
                    <a class="tc-nav__link" href="{{ route('agent.planning') }}" data-title="Planning">
                        <span class="tc-nav__icon"><i class="fa-solid fa-calendar-days"></i></span>
                        <span class="tc-nav__text">Planning équipe</span>
                    </a>
                </li>
            @endcan
            @can('gerer Procedure')
                <li>
                    <a class="tc-nav__link" href="{{ route('agent.procedures') }}" data-title="Procédures">
                        <span class="tc-nav__icon"><i class="fa-solid fa-book-open"></i></span>
                        <span class="tc-nav__text">Procédures</span>
                    </a>
                </li>
            @endcan
            @can('gerer Block power')
                <li>
                    <a class="tc-nav__link collapsed" href="#" data-toggle="collapse" data-target="#nav-blockpower" aria-expanded="false" data-title="Allotements">
                        <span class="tc-nav__icon"><i class="fa-solid fa-layer-group"></i></span>
                        <span class="tc-nav__text">Allotements</span>
                        <i class="fa-solid fa-chevron-right tc-nav__chev"></i>
                    </a>
                    <div id="nav-blockpower" class="collapse" data-parent="#accordionSidebar">
                        <ul class="tc-nav__sub">
                            <li><a class="tc-nav__sublink" href="{{ route('agent.block-power') }}">Options en vente</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.block-power', ['statut' => 'nominatives']) }}">Nominatives</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.block-power', ['statut' => 'a-emettre']) }}">À émettre</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.block-power.create') }}">Nouvelle option</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.block-power.requests') }}">Demandes de groupes</a></li>
                            <li><a class="tc-nav__sublink" href="{{ route('agent.block-power.guide') }}" target="_blank">Guide</a></li>
                        </ul>
                    </div>
                </li>
            @endcan
        </ul>
    </div>

    <div class="tc-sidebar__foot">
        <div class="tc-sidebar__card">
            <strong>Astuce</strong><br>
            <kbd>Ctrl</kbd> + <kbd>K</kbd> pour aller à n'importe quelle page.
        </div>
    </div>
</aside>

@extends('layouts.admin')
@section('title', 'Destinations · ' . $brand->name())

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">Destinations</h1>
                <p class="text-gray-500 mb-0">Chaque destination crée ses menus de vols et sa permission pour les agents.</p>
            </div>
            <button type="button" class="btn btn-primary" data-tc-dest="new"><i class="fa-solid fa-plus mr-1"></i> Nouvelle destination</button>
        </div>

        <div class="tc-dest-grid">
            @forelse($destinations as $destination)
                @php($stats = $volCounts[$destination->slug] ?? null)
                <article class="tc-dest {{ $destination->is_active ? '' : 'is-off' }}" style="--dest: {{ $destination->color }}">
                    <div class="tc-dest__top">
                        <span class="tc-dest__icon">
                            @if($destination->iconUrl())
                                <img src="{{ $destination->iconUrl() }}" alt="">
                            @else
                                {{ $destination->initials() }}
                            @endif
                        </span>
                        <div class="tc-dest__title">
                            <h5>{{ $destination->name }}</h5>
                            <code>/{{ $destination->slug }}</code>
                        </div>
                        @unless($destination->is_active)
                            <span class="badge badge-secondary ml-auto">Masquée</span>
                        @endunless
                    </div>
                    <div class="tc-dest__stats">
                        <div><strong data-tc-count="{{ $stats->total ?? 0 }}">0</strong><span>vols en vente</span></div>
                        <div><strong data-tc-count="{{ (int) ($stats->seats ?? 0) }}">0</strong><span>places libres</span></div>
                        <div><strong data-tc-count="{{ $destination->vol_groups_count }}">0</strong><span>groupes</span></div>
                    </div>
                    <div class="tc-dest__meta">
                        <span class="tc-chip"><i class="fa-solid fa-flag"></i> {{ $destination->country->nom ?? '—' }}</span>
                        @if($destination->permissionName())
                            <span class="tc-chip" title="Permission agent"><i class="fa-solid fa-key"></i> {{ $destination->permissionName() }}</span>
                        @endif
                    </div>
                    <div class="tc-dest__actions">
                        <a class="btn btn-light btn-sm" href="{{ route('admin.vols', [$destination->slug, 'archive' => 0]) }}"><i class="fa-solid fa-plane mr-1"></i> Vols</a>
                        <button type="button" class="btn btn-light btn-sm" data-tc-dest="edit"
                                data-id="{{ $destination->id }}" data-name="{{ $destination->name }}" data-slug="{{ $destination->slug }}"
                                data-country="{{ $destination->country_id }}" data-color="{{ $destination->color }}"
                                data-position="{{ $destination->position }}" data-active="{{ $destination->is_active ? 1 : 0 }}">
                            <i class="fa-solid fa-pen mr-1"></i> Modifier
                        </button>
                        <form method="POST" action="{{ route('admin.destinations.destroy', $destination->id) }}" class="ml-auto"
                              onsubmit="return confirm('Supprimer « {{ addslashes($destination->name) }} » ? Si elle a des vols, elle sera seulement masquée.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-light btn-sm text-danger" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="tc-empty card"><i class="fa-solid fa-earth-africa"></i>Aucune destination pour l'instant.</div>
            @endforelse
            <button type="button" class="tc-dest tc-dest--add" data-tc-dest="new">
                <i class="fa-solid fa-plus"></i>
                <span>Ajouter une destination</span>
            </button>
        </div>
    </div>

    <div class="modal fade" id="destModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="POST" enctype="multipart/form-data" id="destForm" action="{{ route('admin.destinations.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="destMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="destTitle">Nouvelle destination</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="col-md-8 form-group">
                            <label for="d_name">Nom</label>
                            <input class="form-control" id="d_name" name="name" required maxlength="60" placeholder="Ex. Sénégal">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="d_color">Couleur</label>
                            <input type="color" class="form-control" id="d_color" name="color" value="#4f46e5">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="d_slug">Identifiant dans les URL</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">/vols/</span></div>
                            <input class="form-control" id="d_slug" name="slug" placeholder="senegal">
                        </div>
                        <small class="text-gray-500">Rempli automatiquement depuis le nom. À ne pas changer si des vols existent déjà.</small>
                    </div>
                    <div class="form-group">
                        <label for="d_country">Pays des groupes de vols</label>
                        <select class="form-control" id="d_country" name="country_id">
                            <option value="">Créer le pays automatiquement</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="col-md-8 form-group">
                            <label for="d_icon">Drapeau ou icône</label>
                            <input type="file" class="form-control" id="d_icon" name="icon_file" accept="image/png,image/jpeg,image/webp">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="d_position">Ordre</label>
                            <input type="number" min="0" class="form-control" id="d_position" name="position">
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="d_active" name="is_active" value="1" checked>
                        <label class="custom-control-label" for="d_active">Visible dans les menus</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check mr-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .tc-dest-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .tc-dest {
            position: relative; overflow: hidden; border-radius: var(--tc-radius-lg); padding: 20px; background: var(--tc-surface);
            border: 1px solid var(--tc-border); box-shadow: var(--tc-shadow-sm); transition: transform .3s var(--tc-ease), box-shadow .3s;
        }
        .tc-dest::before { content: ''; position: absolute; inset: 0 0 auto; height: 5px; background: var(--dest); }
        .tc-dest:hover { transform: translateY(-4px); box-shadow: var(--tc-shadow); }
        .tc-dest.is-off { opacity: .6; }
        .tc-dest__top { display: flex; align-items: center; gap: 14px; }
        .tc-dest__icon { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; color: #fff; font-weight: 800; background: var(--dest); box-shadow: 0 10px 22px -10px var(--dest); flex-shrink: 0; }
        .tc-dest__icon img { width: 30px; height: 30px; object-fit: contain; border-radius: 5px; }
        .tc-dest__title h5 { margin: 0; font-size: 1.12rem; }
        .tc-dest__title code { color: var(--tc-muted); font-size: .78rem; }
        .tc-dest__stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin: 18px 0 14px; padding: 12px; border-radius: 14px; background: var(--tc-surface-2); }
        .tc-dest__stats div { text-align: center; }
        .tc-dest__stats strong { display: block; font-size: 1.35rem; font-weight: 800; color: var(--tc-text); font-variant-numeric: tabular-nums; }
        .tc-dest__stats span { font-size: .7rem; font-weight: 700; color: var(--tc-muted); text-transform: uppercase; letter-spacing: .05em; }
        .tc-dest__meta { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
        .tc-dest__actions { display: flex; gap: 8px; align-items: center; }
        .tc-dest--add { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 250px; border: 2px dashed var(--tc-border-strong); background: transparent; color: var(--tc-muted); font-weight: 700; cursor: pointer; box-shadow: none; }
        .tc-dest--add::before, .tc-dest--add::after { display: none; }
        .tc-dest--add i { width: 54px; height: 54px; border-radius: 16px; display: grid; place-items: center; font-size: 1.3rem; background: rgba(var(--tc-primary-rgb), .08); color: var(--tc-primary); transition: transform .3s var(--tc-spring); }
        .tc-dest--add:hover { border-color: var(--tc-primary); color: var(--tc-primary); }
        .tc-dest--add:hover i { transform: rotate(90deg) scale(1.08); }
    </style>
@endsection

@push('script')
    <script>
        (function () {
            const form = document.getElementById('destForm');
            const slugify = (s) => s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            let slugTouched = false;
            form.slug.addEventListener('input', () => { slugTouched = true; });
            form.name.addEventListener('input', () => { if (!slugTouched) form.slug.value = slugify(form.name.value); });

            document.querySelectorAll('[data-tc-dest]').forEach((btn) => btn.addEventListener('click', () => {
                const edit = btn.dataset.tcDest === 'edit';
                form.reset();
                slugTouched = edit;
                document.getElementById('destTitle').textContent = edit ? `Modifier « ${btn.dataset.name} »` : 'Nouvelle destination';
                document.getElementById('destMethod').value = edit ? 'PUT' : 'POST';
                form.action = edit ? `{{ route('admin.destinations') }}/${btn.dataset.id}` : `{{ route('admin.destinations') }}`;
                if (edit) {
                    form.name.value = btn.dataset.name;
                    form.slug.value = btn.dataset.slug;
                    form.country_id.value = btn.dataset.country || '';
                    form.color.value = btn.dataset.color;
                    form.position.value = btn.dataset.position;
                    form.is_active.checked = btn.dataset.active === '1';
                }
                $('#destModal').modal('show');
                setTimeout(() => form.name.focus(), 300);
            }));
        })();
    </script>
@endpush

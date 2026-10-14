@extends('layouts.admin')
@section('title', 'Identité & couleurs · ' . $brand->name())

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
                <h1 class="h3 mb-1">Identité &amp; couleurs</h1>
                <p class="text-gray-500 mb-0">Le nom, le logo et les couleurs s'appliquent aux deux espaces. Les changements s'affichent en direct.</p>
            </div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Paramètres</li>
            </ol>
        </div>

        <form method="POST" action="{{ route('admin.parametres.update') }}" enctype="multipart/form-data" id="brandForm">
            @csrf
            <div class="row">
                <div class="col-xl-7">
                    <div class="card mb-4">
                        <div class="card-header"><h6 class="m-0">Agence</h6></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="agency_name">Nom affiché</label>
                                <input type="text" class="form-control @error('agency_name') is-invalid @enderror" id="agency_name" name="agency_name"
                                       value="{{ old('agency_name', $brand->name()) }}" maxlength="80" required>
                            </div>
                            <label>Logo</label>
                            <div class="tc-logo-drop" id="logoDrop">
                                <div class="tc-logo-drop__preview" id="logoPreview">
                                    @if($brand->logoUrl())
                                        <img src="{{ $brand->logoUrl() }}" alt="">
                                    @else
                                        <span class="tc-logo-mark">{{ $brand->initials() }}</span>
                                    @endif
                                </div>
                                <div>
                                    <strong>Glissez une image ici</strong> ou <label for="logo" class="tc-link-label">choisissez un fichier</label>
                                    <div class="text-gray-500 small">PNG, JPG ou WebP, 2 Mo max. Sans logo, les initiales de l'agence sont utilisées.</div>
                                    @if($brand->logoUrl())
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="checkbox" class="custom-control-input" id="remove_logo" name="remove_logo" value="1">
                                            <label class="custom-control-label" for="remove_logo">Retirer le logo actuel</label>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp" hidden>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><h6 class="m-0">Couleurs</h6></div>
                        <div class="card-body">
                            <label>Palettes prêtes</label>
                            <div class="tc-presets mb-4">
                                @foreach($presets as [$label, $primary, $accent])
                                    <button type="button" class="tc-preset" data-primary="{{ $primary }}" data-accent="{{ $accent }}">
                                        <span class="tc-preset__swatch" style="background: linear-gradient(135deg, {{ $primary }} 55%, {{ $accent }} 55%)"></span>
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="color_primary">Bannière et boutons</label>
                                    <div class="tc-color">
                                        <input type="color" id="color_primary" name="color_primary" value="{{ old('color_primary', $brand->primary()) }}">
                                        <input type="text" class="form-control" data-mirror="color_primary" value="{{ old('color_primary', $brand->primary()) }}" maxlength="7">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="color_accent">Accent (indicateurs, dégradés)</label>
                                    <div class="tc-color">
                                        <input type="color" id="color_accent" name="color_accent" value="{{ old('color_accent', $brand->accent()) }}">
                                        <input type="text" class="form-control" data-mirror="color_accent" value="{{ old('color_accent', $brand->accent()) }}" maxlength="7">
                                    </div>
                                </div>
                            </div>
                            <label>Style de la bannière</label>
                            <div class="tc-choice">
                                @foreach(['gradient' => ['Dégradé', 'fa-wand-magic-sparkles'], 'solid' => ['Uni', 'fa-square'], 'glass' => ['Verre', 'fa-droplet']] as $value => [$label, $icon])
                                    <label class="tc-choice__item">
                                        <input type="radio" name="banner_style" value="{{ $value }}" @checked(old('banner_style', $brand->bannerStyle()) === $value)>
                                        <span><i class="fa-solid {{ $icon }}"></i> {{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="card mb-4 tc-sticky">
                        <div class="card-header"><h6 class="m-0">Aperçu</h6></div>
                        <div class="card-body">
                            <div class="tc-mock">
                                <div class="tc-mock__side">
                                    <span class="tc-logo-mark" id="mockMark">{{ $brand->initials() }}</span>
                                    <i></i><i class="is-on"></i><i></i><i></i>
                                </div>
                                <div class="tc-mock__main">
                                    <div class="tc-mock__bar" id="mockBar"><span id="mockName">{{ $brand->name() }}</span></div>
                                    <div class="tc-mock__cards"><b></b><b></b><b></b></div>
                                    <div class="tc-mock__btn">Enregistrer</div>
                                </div>
                            </div>
                            <p class="text-gray-500 small mt-3 mb-4">La bannière en haut de cette page change aussi pendant vos réglages.</p>
                            <div class="d-flex" style="gap:10px">
                                <button type="button" class="btn btn-light flex-fill" id="resetColors"><i class="fa-solid fa-rotate-left mr-1"></i> Annuler</button>
                                <button type="submit" class="btn btn-primary flex-fill"><i class="fa-solid fa-check mr-1"></i> Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <style>
        .tc-logo-drop { display: flex; gap: 18px; align-items: center; padding: 18px; border: 2px dashed var(--tc-border-strong); border-radius: 16px; transition: border-color .2s, background .2s; }
        .tc-logo-drop.is-over { border-color: var(--tc-primary); background: rgba(var(--tc-primary-rgb), .05); }
        .tc-logo-drop__preview { width: 120px; height: 72px; flex-shrink: 0; display: grid; place-items: center; border-radius: 12px; background: var(--tc-surface-2); overflow: hidden; }
        .tc-logo-drop__preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .tc-link-label { color: var(--tc-primary); cursor: pointer; font-size: inherit; margin: 0; text-decoration: underline; }
        .tc-presets { display: grid; grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)); gap: 10px; }
        .tc-preset { display: flex; align-items: center; gap: 10px; padding: 8px 12px 8px 8px; border-radius: 12px; border: 1px solid var(--tc-border); background: var(--tc-surface); font-weight: 700; font-size: .82rem; color: var(--tc-text-2); cursor: pointer; transition: transform .2s var(--tc-spring), border-color .2s, box-shadow .2s; }
        .tc-preset:hover { transform: translateY(-2px); border-color: var(--tc-border-strong); box-shadow: var(--tc-shadow-sm); }
        .tc-preset__swatch { width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0; }
        .tc-color { display: flex; gap: 10px; }
        .tc-color input[type=color] { width: 52px; height: 42px; border: 1px solid var(--tc-border-strong); border-radius: 11px; padding: 4px; background: var(--tc-surface); cursor: pointer; }
        .tc-color .form-control { font-family: ui-monospace, monospace; text-transform: uppercase; }
        .tc-choice { display: flex; gap: 10px; flex-wrap: wrap; }
        .tc-choice__item { margin: 0; cursor: pointer; }
        .tc-choice__item input { position: absolute; opacity: 0; }
        .tc-choice__item span { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 12px; border: 1px solid var(--tc-border-strong); font-weight: 700; color: var(--tc-text-2); transition: all .2s; }
        .tc-choice__item input:checked + span { border-color: var(--tc-primary); background: rgba(var(--tc-primary-rgb), .08); color: var(--tc-primary); box-shadow: var(--tc-ring); }
        .tc-sticky { position: sticky; top: calc(var(--tc-top-h) + 22px); }
        .tc-mock { display: flex; height: 220px; border-radius: 14px; overflow: hidden; border: 1px solid var(--tc-border); box-shadow: var(--tc-shadow-sm); background: var(--tc-bg); }
        .tc-mock__side { width: 64px; background: linear-gradient(180deg, var(--tc-side-bg-2), var(--tc-side-bg)); padding: 12px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .tc-mock__side .tc-logo-mark { width: 36px; height: 36px; font-size: .75rem; }
        .tc-mock__side i { width: 28px; height: 8px; border-radius: 4px; background: rgba(255, 255, 255, .12); }
        .tc-mock__side i.is-on { background: var(--tc-primary); }
        .tc-mock__main { flex: 1; display: flex; flex-direction: column; }
        .tc-mock__bar { height: 46px; padding: 0 14px; display: flex; align-items: center; font-weight: 800; color: var(--tc-on-primary); background: linear-gradient(120deg, var(--tc-primary-dark), var(--tc-primary) 55%, var(--tc-accent) 140%); transition: background .3s; }
        .tc-mock__cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; padding: 14px; }
        .tc-mock__cards b { height: 60px; border-radius: 10px; background: #fff; border: 1px solid var(--tc-border); position: relative; }
        .tc-mock__cards b::after { content: ''; position: absolute; left: 10px; top: 10px; width: 22px; height: 22px; border-radius: 7px; background: linear-gradient(135deg, var(--tc-primary), var(--tc-accent)); }
        .tc-mock__btn { margin: auto 14px 14px auto; padding: 8px 16px; border-radius: 10px; font-weight: 800; font-size: .78rem; color: var(--tc-on-primary); background: var(--tc-primary); }
    </style>
@endsection

@push('script')
    <script>
        (function () {
            const root = document.documentElement;
            const form = document.getElementById('brandForm');
            const initial = { primary: '{{ $brand->primary() }}', accent: '{{ $brand->accent() }}', style: '{{ $brand->bannerStyle() }}' };
            const hex = (h) => { const n = parseInt(h.slice(1), 16); return [n >> 16 & 255, n >> 8 & 255, n & 255]; };
            const mix = (h, r) => '#' + hex(h).map((c) => Math.round(r >= 0 ? c + (255 - c) * r : c * (1 + r)).toString(16).padStart(2, '0')).join('');
            const lum = (h) => { const [r, g, b] = hex(h); return (0.299 * r + 0.587 * g + 0.114 * b) / 255; };

            function apply(primary, accent) {
                if (!/^#[0-9a-f]{6}$/i.test(primary) || !/^#[0-9a-f]{6}$/i.test(accent)) return;
                root.style.setProperty('--tc-primary', primary);
                root.style.setProperty('--tc-primary-rgb', hex(primary).join(','));
                root.style.setProperty('--tc-primary-dark', mix(primary, -0.22));
                root.style.setProperty('--tc-primary-light', mix(primary, 0.86));
                root.style.setProperty('--tc-accent', accent);
                root.style.setProperty('--tc-accent-rgb', hex(accent).join(','));
                root.style.setProperty('--tc-on-primary', lum(primary) > 0.55 ? '#0f172a' : '#ffffff');
            }
            function applyStyle(style) {
                const bar = document.querySelector('.tc-topbar');
                bar.className = bar.className.replace(/tc-topbar--\w+/, 'tc-topbar--' + style);
            }
            function setColors(primary, accent) {
                form.color_primary.value = primary;
                form.color_accent.value = accent;
                document.querySelector('[data-mirror=color_primary]').value = primary.toUpperCase();
                document.querySelector('[data-mirror=color_accent]').value = accent.toUpperCase();
                apply(primary, accent);
            }

            ['color_primary', 'color_accent'].forEach((name) => {
                const picker = form[name];
                const text = document.querySelector(`[data-mirror=${name}]`);
                picker.addEventListener('input', () => { text.value = picker.value.toUpperCase(); apply(form.color_primary.value, form.color_accent.value); });
                text.addEventListener('input', () => {
                    let v = text.value.trim();
                    if (v[0] !== '#') v = '#' + v;
                    if (/^#[0-9a-f]{6}$/i.test(v)) { picker.value = v; apply(form.color_primary.value, form.color_accent.value); }
                });
            });
            document.querySelectorAll('.tc-preset').forEach((b) => b.addEventListener('click', () => {
                setColors(b.dataset.primary, b.dataset.accent);
                TC.toast.info(`Palette « ${b.textContent.trim()} » appliquée en aperçu. Pensez à enregistrer.`, 'Aperçu');
            }));
            form.querySelectorAll('[name=banner_style]').forEach((r) => r.addEventListener('change', () => applyStyle(r.value)));
            document.getElementById('resetColors').addEventListener('click', () => {
                setColors(initial.primary, initial.accent);
                form.querySelector(`[name=banner_style][value=${initial.style}]`).checked = true;
                applyStyle(initial.style);
            });
            document.getElementById('agency_name').addEventListener('input', (e) => { document.getElementById('mockName').textContent = e.target.value || '…'; });

            // Logo : glisser-déposer et aperçu
            const drop = document.getElementById('logoDrop');
            const input = document.getElementById('logo');
            const preview = (file) => {
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = () => { document.getElementById('logoPreview').innerHTML = `<img src="${reader.result}" alt="">`; };
                reader.readAsDataURL(file);
            };
            input.addEventListener('change', () => preview(input.files[0]));
            ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
            ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.remove('is-over'); }));
            drop.addEventListener('drop', (e) => {
                const file = e.dataTransfer.files[0];
                if (!file) return;
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                preview(file);
            });
        })();
    </script>
@endpush

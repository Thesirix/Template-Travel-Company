@extends('layouts.admin')

@php
    $exists = $booking->exists;
    $readOnly = $mode === 'show';
    $withReservation = in_array($mode, ['reserve', 'manage']);
    $reservation = $booking->reservation;
    $status = $booking->status();
    $titles = ['create' => 'Nouvelle demande de traversée', 'edit' => 'Modifier la demande', 'show' => 'Demande de traversée',
        'reserve' => 'Réserver la traversée', 'manage' => \App\Models\BoatBooking::STATUSES[$status] . ' · réservation ' . ($reservation->num_reservation ?? '')];
    $action = match ($mode) {
        'create' => route('agent.bateau.store'),
        'edit' => route('agent.bateau.update', $booking),
        'reserve' => route('agent.bateau.reserve', $booking),
        'manage' => route('agent.bateau.finalize', $booking),
        default => null,
    };
    $value = fn (string $field, $default = null) => old($field, $booking->$field ?? $default);
    $date = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '';
    $roundTrip = $value('trajet', 'aller-retour') === 'aller-retour';
    $responsible = old('responsable', ($lead = $booking->passengers->firstWhere('respo', true)) ? 'existing:' . $lead->id : '');
@endphp

@section('title', $titles[$mode] . ' · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $titles[$mode] }}</h1>
                @if($exists)
                    <p class="text-gray-500 mb-0">
                        Demande du {{ \Illuminate\Support\Carbon::parse($booking->created_at)->format('d/m/Y') }}
                        par {{ trim(($booking->agent->prenom ?? '') . ' ' . ($booking->agent->name ?? '')) }}
                    </p>
                @endif
            </div>
            @if($mode === 'show')
                <div>
                    <a href="{{ route('agent.bateau.edit', $booking) }}" class="btn btn-light"><i class="fa-solid fa-pen mr-1"></i> Modifier</a>
                    <a href="{{ route('agent.bateau.reserve-form', $booking) }}" class="btn btn-primary ml-2"><i class="fa-solid fa-ship mr-1"></i> Réserver</a>
                    <button type="button" class="btn btn-outline-danger ml-2" data-toggle="modal" data-target="#delete-booking"><i class="fa-solid fa-trash mr-1"></i> Supprimer</button>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ $action ?? '#' }}" enctype="multipart/form-data" id="booking-form">
            @csrf
            <fieldset @disabled($readOnly)>
                <div class="row">
                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Traversée</h6></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="trajet-retour" name="trajet" value="aller-retour" class="custom-control-input" @checked($roundTrip)>
                                        <label class="custom-control-label" for="trajet-retour">Aller-retour</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="trajet-simple" name="trajet" value="aller-simple" class="custom-control-input" @checked(! $roundTrip)>
                                        <label class="custom-control-label" for="trajet-simple">Aller simple</label>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="destination_id">Destination</label>
                                        <select name="destination_id" id="destination_id" class="form-control" required>
                                            @foreach($destinations as $item)
                                                <option value="{{ $item->id }}" @selected($destination?->id == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="company_id">Compagnie</label>
                                        <select name="company_id" id="company_id" class="form-control" required>
                                            @foreach($destination?->companies ?? [] as $item)
                                                <option value="{{ $item->id }}" @selected($value('company_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="departure_port_id">Port de départ</label>
                                        <select name="departure_port_id" id="departure_port_id" class="form-control" required>
                                            @foreach($destination?->departurePorts ?? [] as $item)
                                                <option value="{{ $item->id }}" @selected($value('departure_port_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6 round-trip">
                                        <label for="arrival_port_id">Port de retour</label>
                                        <select name="arrival_port_id" id="arrival_port_id" class="form-control">
                                            @foreach($destination?->arrivalPorts ?? [] as $item)
                                                <option value="{{ $item->id }}" @selected($value('arrival_port_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="date_aller">Date aller</label>
                                        <input type="date" name="date_aller" id="date_aller" class="form-control" required value="{{ $date($value('date_aller')) }}" @if(! $exists) min="{{ date('Y-m-d') }}" @endif>
                                    </div>
                                    <div class="form-group col-md-6 round-trip">
                                        <label for="date_retour">Date retour</label>
                                        <input type="date" name="date_retour" id="date_retour" class="form-control" value="{{ $date($value('date_retour')) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="accommodation_id">Installation</label>
                                        <select name="accommodation_id" id="accommodation_id" class="form-control" required>
                                            @foreach($accommodations as $item)
                                                <option value="{{ $item->id }}" @selected($value('accommodation_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="fare_id">Tarif</label>
                                        <select name="fare_id" id="fare_id" class="form-control" required>
                                            @foreach($fares as $item)
                                                <option value="{{ $item->id }}" @selected($value('fare_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="insurance_id">Assurance voyage</label>
                                        <select name="insurance_id" id="insurance_id" class="form-control" required>
                                            @foreach($insurances as $item)
                                                <option value="{{ $item->id }}" @selected($value('insurance_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="note">Remarque</label>
                                    <textarea name="note" id="note" class="form-control" rows="2" maxlength="300">{{ $value('note') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Véhicule</h6></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="vehicle_id">Véhicule embarqué</label>
                                    <select name="vehicle_id" id="vehicle_id" class="form-control">
                                        <option value="">Aucun véhicule</option>
                                        @foreach($vehicles as $item)
                                            <option value="{{ $item->id }}" @selected($value('vehicle_id') == $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-row vehicle">
                                    <div class="form-group col-md-6">
                                        <label for="voiture_marque">Marque</label>
                                        <input type="text" name="voiture_marque" id="voiture_marque" class="form-control" maxlength="50" value="{{ $value('voiture_marque') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="voiture_modele">Modèle</label>
                                        <input type="text" name="voiture_modele" id="voiture_modele" class="form-control" maxlength="50" value="{{ $value('voiture_modele') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="voiture_matricule">Immatriculation</label>
                                        <input type="text" name="voiture_matricule" id="voiture_matricule" class="form-control" maxlength="50" value="{{ $value('voiture_matricule') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="voiture_anne">Mise en circulation</label>
                                        <input type="date" name="voiture_anne" id="voiture_anne" class="form-control" value="{{ $date($value('voiture_anne')) }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="vehicle_size_id">Gabarit</label>
                                        <select name="vehicle_size_id" id="vehicle_size_id" class="form-control">
                                            @foreach($vehicleSizes as $item)
                                                <option value="{{ $item->id }}" @selected($value('vehicle_size_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="vehicle_insurance_id">Assurance du véhicule</label>
                                        <select name="vehicle_insurance_id" id="vehicle_insurance_id" class="form-control">
                                            @foreach($vehicleInsurances as $item)
                                                <option value="{{ $item->id }}" @selected($value('vehicle_insurance_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-12 mb-0">
                                        <label for="carte_grise">Carte grise</label>
                                        <input type="file" name="carte_grise" id="carte_grise" class="form-control-file" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        @if($booking->vehicleDocumentUrl())
                                            <a href="{{ $booking->vehicleDocumentUrl() }}" target="_blank" class="d-inline-block mt-2"><i class="fa-solid fa-file mr-1"></i> Carte grise enregistrée</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Passagers</h6>
                        @unless($readOnly)
                            <div class="form-inline">
                                <select id="new-passenger-type" class="form-control form-control-sm mr-2">
                                    @foreach($passengerTypes as $key => [$label, $min, $max])
                                        <option value="{{ $key }}">{{ $label }} ({{ $min }} à {{ $max }} ans)</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-primary" id="add-passenger"><i class="fa-solid fa-user-plus mr-1"></i> Ajouter</button>
                            </div>
                        @endunless
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>Resp.</th>
                                <th>Type</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Naissance</th>
                                <th>Âge aller / retour</th>
                                <th>Téléphone</th>
                                <th>E-mail</th>
                                <th>N° passeport</th>
                                <th>Copie</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody id="passengers">
                            @foreach($booking->passengers as $passenger)
                                @php($field = fn ($name) => "passengers[$passenger->id][$name]")
                                <tr>
                                    <td><input type="radio" name="responsable" value="existing:{{ $passenger->id }}" @checked($responsible === 'existing:' . $passenger->id)></td>
                                    <td>{{ $passenger->type }}</td>
                                    <td><input type="text" name="{{ $field('nom') }}" class="form-control form-control-sm" required value="{{ old("passengers.$passenger->id.nom", $passenger->nom) }}"></td>
                                    <td><input type="text" name="{{ $field('prenom') }}" class="form-control form-control-sm" required value="{{ old("passengers.$passenger->id.prenom", $passenger->prenom) }}"></td>
                                    <td><input type="date" name="{{ $field('date_naissance') }}" class="form-control form-control-sm" required value="{{ $date(old("passengers.$passenger->id.date_naissance", $passenger->date_naissance)) }}"></td>
                                    <td class="text-nowrap">{{ $passenger->ageA ?? '—' }} / {{ $passenger->ageR ?? '—' }}</td>
                                    <td><input type="text" name="{{ $field('tele') }}" class="form-control form-control-sm" maxlength="15" value="{{ old("passengers.$passenger->id.tele", $passenger->tele) }}"></td>
                                    <td><input type="email" name="{{ $field('mail') }}" class="form-control form-control-sm" value="{{ old("passengers.$passenger->id.mail", $passenger->mail) }}"></td>
                                    <td><input type="text" name="{{ $field('passport_num') }}" class="form-control form-control-sm" maxlength="15" value="{{ old("passengers.$passenger->id.passport_num", $passenger->passport_num) }}"></td>
                                    <td class="text-nowrap">
                                        @if($passenger->passportUrl())
                                            <a href="{{ $passenger->passportUrl() }}" target="_blank" title="Voir la copie"><i class="fa-solid fa-file"></i></a>
                                        @endif
                                        @unless($readOnly)
                                            <input type="file" name="{{ $field('passport') }}" class="form-control-file d-inline-block" style="width: 190px" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        @endunless
                                    </td>
                                    <td>
                                        @unless($readOnly)
                                            <button type="submit" form="delete-passenger-{{ $passenger->id }}" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                                    onclick="return confirm('Supprimer ce passager ?')"><i class="fa-solid fa-trash"></i></button>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                            @foreach(old('nouveaux', []) as $index => $passenger)
                                @include('agent.bateau.partials.new-passenger', ['index' => $index, 'passenger' => $passenger])
                            @endforeach
                            </tbody>
                        </table>
                        @if($booking->passengers->isEmpty() && ! old('nouveaux'))
                            <p class="text-gray-500 mb-0 mt-3" id="no-passenger">Ajoutez les passagers avec le bouton « Ajouter ».</p>
                        @endif
                    </div>
                </div>

                @if($withReservation || ($readOnly && $reservation))
                    <div class="card mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Réservation auprès de la compagnie</h6></div>
                        <div class="card-body form-row">
                            <div class="form-group col-md-3">
                                <label for="num_reservation">N° de réservation</label>
                                <input type="text" name="num_reservation" id="num_reservation" class="form-control" required maxlength="100" value="{{ old('num_reservation', $reservation->num_reservation ?? '') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="date_option">Date d'option</label>
                                <input type="date" name="date_option" id="date_option" class="form-control" required value="{{ $date(old('date_option', $reservation->date_option ?? '')) }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="reservation_prix">Prix (€)</label>
                                <input type="number" step="0.01" min="0" name="reservation_prix" id="reservation_prix" class="form-control" required value="{{ old('reservation_prix', $reservation->prix ?? '') }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="details">Détails</label>
                                <input type="text" name="details" id="details" class="form-control" maxlength="500" value="{{ old('details', $reservation->details ?? '') }}">
                            </div>
                        </div>
                    </div>
                @endif
            </fieldset>

            @unless($readOnly)
                <div class="mb-5">
                    @if($mode === 'manage')
                        <button type="submit" name="action" value="save" class="btn btn-light">Enregistrer</button>
                        @if($status !== 'final')
                            <button type="submit" name="action" value="final" class="btn btn-primary ml-2"><i class="fa-solid fa-check mr-1"></i> Vendue</button>
                        @endif
                        @if($status !== 'echange')
                            <button type="submit" name="action" value="echange" class="btn btn-warning ml-2"><i class="fa-solid fa-right-left mr-1"></i> Bon d'échange</button>
                        @endif
                    @else
                        <button type="submit" class="btn btn-primary">{{ $mode === 'reserve' ? 'Réserver' : 'Enregistrer' }}</button>
                    @endif
                    @if($exists)
                        <a href="{{ route('agent.bateau.show', $booking) }}" class="btn btn-link">Annuler</a>
                    @endif
                </div>
            @endunless
        </form>

        {{-- Formulaires hors du formulaire principal (les formulaires imbriqués sont ignorés par les navigateurs) --}}
        @foreach($booking->passengers as $passenger)
            <form method="POST" action="{{ route('agent.bateau.passenger.destroy', $passenger) }}" id="delete-passenger-{{ $passenger->id }}">@csrf</form>
        @endforeach
    </div>

    @if($exists)
        <div class="modal fade" id="delete-booking" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('agent.bateau.destroy', $booking) }}" class="modal-content">
                    @csrf
                    <div class="modal-body">Supprimer définitivement cette demande et ses passagers ?</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <template id="new-passenger-template">
        @include('agent.bateau.partials.new-passenger', ['index' => '__INDEX__', 'passenger' => ['type' => '__TYPE__']])
    </template>
@endsection

@push('script')
    <script>
        $(function () {
            const labels = @json(collect($passengerTypes)->map(fn ($type) => $type[0]));
            const catalogUrl = @json(route('agent.bateau.catalog', '__ID__'));
            let next = {{ count(old('nouveaux', [])) }};

            function toggleTrip() {
                $('.round-trip').toggle($('#trajet-retour').is(':checked'))
            }
            function toggleVehicle() {
                $('.vehicle').toggle($('#vehicle_id').val() !== '')
            }
            toggleTrip()
            toggleVehicle()
            $('input[name=trajet]').on('change', toggleTrip)
            $('#vehicle_id').on('change', toggleVehicle)
            $('#date_aller').on('change', function () {
                $('#date_retour').attr('min', $(this).val())
            })

            // Ports et compagnies de la destination choisie
            $('#destination_id').on('change', function () {
                $.getJSON(catalogUrl.replace('__ID__', $(this).val()), function (data) {
                    const fill = (select, items) => $(select).html(items.map(i => $('<option>').val(i.id).text(i.name)))
                    fill('#departure_port_id', data.departurePorts)
                    fill('#arrival_port_id', data.arrivalPorts)
                    fill('#company_id', data.companies)
                })
            })

            $('#add-passenger').on('click', function () {
                const type = $('#new-passenger-type').val()
                const html = $('#new-passenger-template').html().replaceAll('__INDEX__', next++).replaceAll('__TYPE__', type)
                const row = $(html)
                row.find('.passenger-type').text(labels[type])
                $('#passengers').append(row)
                $('#no-passenger').remove()
                if (!$('input[name=responsable]:checked').length) {
                    row.find('input[name=responsable]').prop('checked', true)
                }
            })
            $(document).on('click', '.remove-new-passenger', function () {
                $(this).closest('tr').remove()
            })
        })
    </script>
@endpush

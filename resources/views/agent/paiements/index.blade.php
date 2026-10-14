@extends('layouts.admin')
@section('title', 'Paiement par carte · ' . $brand->name())

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
                <h1 class="h3 mb-1">Autorisation de débit par carte</h1>
                <p class="text-gray-500 mb-0">Le client règle sur la page sécurisée Paybox ; le paiement accepté est envoyé à {{ config('agency.emails.payments') }}.</p>
            </div>
        </div>

        <div class="card" style="max-width: 760px">
            @if(empty($payment))
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Titulaire et montant</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('agent.paiements.pay') }}">
                        @csrf
                        <div class="form-group">
                            <label for="paynom">Je soussigné(e), titulaire de la carte bancaire</label>
                            <input type="text" class="form-control" id="paynom" name="paynom" required value="{{ old('paynom') }}">
                        </div>
                        <div class="form-group">
                            <label for="payadresse">Adresse du titulaire</label>
                            <input type="text" class="form-control" id="payadresse" name="payadresse" required value="{{ old('payadresse') }}">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-sm-4">
                                <label for="paypostal">Code postal</label>
                                <input type="text" class="form-control" id="paypostal" name="paypostal" required value="{{ old('paypostal') }}">
                            </div>
                            <div class="form-group col-sm-8">
                                <label for="paycity">Ville</label>
                                <input type="text" class="form-control" id="paycity" name="paycity" required value="{{ old('paycity') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="paytele">Téléphone</label>
                            <input type="tel" class="form-control" id="paytele" name="paytele" required value="{{ old('paytele') }}">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-sm-5">
                                <label for="payprice">Autorise {{ $brand->name() }} à prélever (€)</label>
                                <input type="number" step="0.01" min="1" class="form-control" id="payprice" name="payprice" required value="{{ old('payprice') }}">
                            </div>
                            <div class="form-group col-sm-7">
                                <label for="paytype">Pour le paiement de</label>
                                <input type="text" class="form-control" id="paytype" name="paytype" required value="{{ old('paytype') }}" placeholder="Ex. acompte réservation 700123">
                            </div>
                        </div>
                        <div class="custom-control custom-checkbox mb-4">
                            <input class="custom-control-input" type="checkbox" id="acceptpaypolicy" name="acceptpaypolicy" value="1" required>
                            <label class="custom-control-label" for="acceptpaypolicy">
                                Je veillerai à l’approvisionnement de mon compte bancaire en vue du présent prélèvement et comprends
                                que si le paiement est rejeté par la banque, ma demande est susceptible d’être annulée.
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right mr-1"></i> Continuer</button>
                    </form>
                </div>
            @else
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Vérification avant paiement</h6></div>
                <div class="card-body">
                    <dl class="mb-4">
                        <dt>Titulaire</dt><dd>{{ $payment['fullname'] }}</dd>
                        <dt>Objet</dt><dd>{{ $payment['text'] }}</dd>
                        <dt>Montant</dt><dd>{{ number_format($payment['price'], 2, ',', ' ') }} €</dd>
                    </dl>
                    <form method="POST" action="{{ $payboxUrl }}">
                        @foreach($paybox as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <a href="{{ route('agent.paiements') }}" class="btn btn-light mr-2">Modifier</a>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-lock mr-1"></i> Payer sur Paybox</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection

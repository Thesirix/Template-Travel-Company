@extends('layouts.admin')
@section('title', 'Placer des passagers · Allotements · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@php($rows = old('passagers') ? count(old('passagers')) : min(1, $option->freeSeats()))

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">Placer des passagers · {{ $option->name }}</h1>
            <p class="text-gray-500 mb-0">PNR {{ $option->pnr }} · {{ $option->freeSeats() }} place(s) libre(s). La date de naissance sert à vérifier le type (Mr / Mrs dès 12 ans, CHD de 2 à 11 ans, INF avant 2 ans).</p>
        </div>

        <form method="POST" action="{{ route('agent.block-power.passengers.store', $option) }}" class="card">
            @csrf
            <div class="card-body table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                    <tr><th>Type</th><th>Nom</th><th>Prénom</th><th>Naissance</th><th>Vendu (€)</th><th>Acompte (€)</th><th>N° acompte</th><th></th></tr>
                    </thead>
                    <tbody id="passengers">
                    @for($i = 0; $i < $rows; $i++)
                        @include('agent.block-power.partials.passenger-row', ['index' => $i, 'passenger' => old("passagers.$i", [])])
                    @endfor
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                <button type="button" class="btn btn-light mr-auto" id="add-row" @disabled($option->freeSeats() <= $rows)><i class="fa-solid fa-plus mr-1"></i> Ajouter une ligne</button>
                <a href="{{ route('agent.block-power.show', $option) }}" class="btn btn-link">Annuler</a>
                <button class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>

    <template id="passenger-row">@include('agent.block-power.partials.passenger-row', ['index' => '__INDEX__', 'passenger' => []])</template>
@endsection

@push('script')
    <script>
        $(function () {
            const max = {{ $option->freeSeats() }};
            let next = {{ $rows }};
            $('#add-row').on('click', function () {
                $('#passengers').append($('#passenger-row').html().replaceAll('__INDEX__', next++))
                $(this).prop('disabled', $('#passengers tr').length >= max)
            })
            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove()
                $('#add-row').prop('disabled', $('#passengers tr').length >= max)
            })
        })
    </script>
@endpush

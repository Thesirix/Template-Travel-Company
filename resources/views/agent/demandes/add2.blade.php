@extends('layouts.admin')

@section('styles')
    <link href="{{ asset('front/css/clientvolestimate/clientvolestimate.css') }}" rel="stylesheet">
@endsection

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection


@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800 mx-auto">Devis Clients</h1>

            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('agent.home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('agent.demandes.create') }}">Devis Clients</a></li>
            </ol>

        </div>

        <x-common.toaster />

        {{-- ------------------section ------------ --}}

        <x-agent.clientvolestimate.demande-client :favorites="$favorites" />

        <x-agent.clientvolestimate.info-client />

        <x-agent.clientvolestimate.devis :favorites="$favorites" />

        <x-agent.clientvolestimate.description />

        <div id="confetti-container"></div>
    @endsection


    @push('script')
        <script>
            const route = {
                referenceData: '{{ route('agent.demandes.reference-data') }}',
                addClientRequest: '{{ route('agent.demandes.store') }}',
                searchClient: '{{ route('agent.demandes.clients.search') }}',
                addDevis: '{{ route('agent.demandes.devis.store') }}',
                sendDevisSms: '{{ route('agent.demandes.devis.sms') }}',
                addClient: '{{ route('agent.demandes.clients.store') }}',
                updateClient: (id) => @json(url('agent/demandes/clients')) + '/' + id
            };
        </script>
        <script src="{{ asset('assets/js/agent/clientVolEstimate/common.js') }}"></script>
        <script src="{{ asset('assets/js/agent/clientVolEstimate/calendrier.js') }}"></script>
        <script src="{{ asset('assets/js/agent/clientVolEstimate/clientRequest.js') }}"></script>
        <script src="{{ asset('assets/js/agent/clientVolEstimate/infosClient.js') }}"></script>
        <script src="{{ asset('assets/js/agent/clientVolEstimate/devisSms.js') }}"></script>
        <script src="{{ asset('assets/js/agent/clientVolEstimate/description.js') }}"></script>
        <script src="{{ asset('assets/js/agent/clientVolEstimate/dropdown.js') }}"></script>
    @endpush

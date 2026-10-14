@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="{{ asset('front/css/planning/planning.css') }}">
@endsection

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection



@section('content')
    <x-admin.DashBoard.structure />
    <x-admin.DashBoard.confirmation-modal />
    <x-admin.DashBoard.refusal-modal />
    <x-admin.DashBoard.add-modal />
    <x-admin.DashBoard.edit-modal />
    <x-admin.DashBoard.solde-modal />
@endsection

@push('script')
<script>
    window.planningBoot = @json($boot);
    window.route = {
        // ---------- Ressources et événements calendrier ----------
        planningEvents: "{{ route('agent.planning.events') }}",
        planningResources: "{{ route('agent.planning.resources') }}",
        planningAttendances: "{{ route('agent.planning.attendances') }}",

        // ---------- Actions administratives ----------
        planningAdd: "{{ route('agent.planning.store') }}",
        planningUpdateAdmin: "{{ route('agent.planning.update', ':id') }}",
        planningDelete: "{{ route('agent.planning.destroy', ':id') }}",
        planningApprove: "{{ route('agent.planning.approve', ':id') }}",
        planningReject: "{{ route('agent.planning.reject', ':id') }}",
        // ---------- Compteurs et soldes ----------
        planningMetrics: "{{ route('agent.planning.metrics') }}",
        planningAgentCounters: "{{ route('agent.planning.counters') }}",
        planningCounterUpdate: "{{ route('agent.planning.counters.update') }}",

        // ---------- Sécurité ----------
        csrfToken: "{{ csrf_token() }}"
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js"></script>
    <script src="{{ asset('assets/js/admin/planning/helper.js') }}"></script>
    <script src="{{ asset('assets/js/admin/planning/presence.js') }}"></script>
    <script src="{{ asset('assets/js/admin/planning/table_metrics.js') }}"></script>
    <script src="{{ asset('assets/js/admin/planning/solde_agent.js') }}"></script>
    <script src="{{ asset('assets/js/admin/planning/modal_admin_approve.js') }}"></script>
    <script src="{{ asset('assets/js/admin/planning/modal_admin_add.js') }}"></script>
    <script src="{{ asset('assets/js/admin/planning/modal_admin_edit.js') }}"></script>
    <script src="{{ asset('assets/js/admin/planning/calendar.js') }}"></script>
    <script src="{{ asset('assets/js/agent/Gent_DashBoard/Gent_Dashboard.js') }}"></script>
@endpush

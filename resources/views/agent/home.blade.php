@extends('layouts.admin')
@section('title', 'Tableau de bord · ' . $brand->name())

@section('styles')
    <link href="{{ asset('front/css/Gent_Dashboard/Left_Click_Schedule.css') }}" rel="stylesheet">
    <link href="{{ asset('front/css/Gent_Dashboard/Calendar.css') }}" rel="stylesheet">
    <link href="{{ asset('front/css/Gent_Dashboard/Modal.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fullcalendar/main.css') }}">
@endsection

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <x-agent.DashBoard.content :todolists="$todolists" :blacknotes="$blacknotes" />
@endsection

@push('script')
    <script>
        window.attendanceDates = [];
        fetch("{{ route('agent.calendrier.attendances') }}")
            .then(res => res.json())
            .then(dates => {
                window.attendanceDates = dates;
                if (window.calendarTeam) {
                    appliquerPresences(window.calendarTeam.view.type);
                }
            });
    </script>
    <script>
        window.joursFeries = @json(\App\Services\LeaveService::joursFeriesJS());
    </script>

    <script>
        window.leaveBalances = @json($leaveBalances);
    </script>
    <script>
        window.route = {

            //    ------------------- bloc-note-----------------

            addNote: '{{ route('agent.notes.store') }}',
            deleteNote: '{{ route('agent.notes.destroy') }}',

            //-------------- emploi du temps -----------------

            emploiEvents: '{{ route('agent.emploi.events') }}',
            emploiUpdate: '{{ route('agent.emploi.update') }}',
            emploiUpdateColor: '{{ route('agent.emploi.color') }}',
            emploiAdd: '{{ route('agent.emploi.store') }}',
            emploiDelete: '{{ route('agent.emploi.destroy') }}',

            // ---------Calendrier des congés ----------------------

            calendrierEvents: '{{ route('agent.calendrier.events') }}',
            calendrierAdd: '{{ route('agent.calendrier.store') }}',
            calendrierUpdate: '{{ route('agent.calendrier.update') }}',
            calendrierDelete: '{{ route('agent.calendrier.destroy') }}',

            // ----affichage congé-------

            agentCounters: "{{ route('agent.calendrier.counters') }}",
            attendances: "{{ route('agent.calendrier.attendances') }}",

            //    -------------------------- csfr-------------

            csrfToken: '{{ csrf_token() }}',

            // -----------------gentid-------------------

            gentid: "{{ Auth::guard('gent')->id() }}",
        };
    </script>
    <script src="{{ asset('assets/js/agent/Gent_DashBoard/Gent_Dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/agent/Gent_DashBoard/motivation.js') }}"></script>
    <script src="{{ asset('assets/js/agent/Gent_DashBoard/bloc-note.js') }}"></script>
    <script src="{{ asset('fullcalendar/main.js') }}"></script>
    <script src="{{ asset('assets/js/agent/Gent_DashBoard/single-calendar.js') }}"></script>
    <script src="{{ asset('assets/js/agent/Gent_DashBoard/helper.js') }}"></script>
    <script src="{{ asset('assets/js/agent/Gent_DashBoard/team-calendar.js') }}"></script>
@endpush

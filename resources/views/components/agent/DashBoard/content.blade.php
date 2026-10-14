    <div class="container-fluid" id="container-wrapper">

        @if (session('showMotivationMessage'))
            <x-agent.DashBoard.modal-motivation />
        @endif

        <x-agent.DashBoard.performance />

        <div class="row">
            <div class="col-xl-7">
                <div class="card mb-4">
                    <x-agent.DashBoard.hearder />
                    <x-agent.DashBoard.calendar />
                </div>
            </div>

            <div class="col-xl-5">
                <x-agent.DashBoard.profil-card />
                <x-agent.DashBoard.todolist-card :todolists="$todolists" />
                <x-agent.DashBoard.bloc-note :blacknotes="$blacknotes" />
            </div>
        </div>

    </div>

    <x-agent.DashBoard.delete-modal />
    <x-agent.DashBoard.add-modal />
    <x-agent.DashBoard.refusal-modal />

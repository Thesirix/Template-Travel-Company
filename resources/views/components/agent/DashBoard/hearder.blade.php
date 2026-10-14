@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Gate;

    $gent = Auth::guard('gent')->user();
@endphp

<style>
    .dropdown-toggle::after {
        display: none !important;
    }


    #dropdownAgences {
        margin-left: 1.5em;
        font-size: 18px !important;
        padding: 5px 12px !important;
        cursor: pointer;
        transition: 0.2s ease;
    }

    #dropdownAgences:hover {
        transform: scale(1.1);
    }


    .dropdown-menu.show {
        display: block !important;
    }
</style>

<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">

    <div class="d-flex align-items-center">
        <h6 class="m-0 mr-1 d-flex align-items-center">
            Agenda


            @if ($gent && ($gent->is_super_agent || Gate::forUser($gent)->allows('gerer Planning')))
                <div class="dropdown ms-1" id="agenceDropdown" style="display: none;">
                    <button class="btn btn-sm dropdown-toggle p-0 border-0 bg-transparent text-primary" type="button"
                        id="dropdownAgences" data-bs-toggle="dropdown" aria-expanded="false">
                        ▼
                    </button>

                    <ul class="dropdown-menu shadow-sm small" aria-labelledby="dropdownAgences"
                        style="min-width: 160px;">
                        <li><a class="dropdown-item agence-choice" href="#" data-id="">TOUT</a></li>
                        @foreach (\App\Models\Agency::all() as $agence)
                            <li>
                                <a class="dropdown-item agence-choice" href="#" data-id="{{ $agence->id }}">
                                    {{ $agence->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </h6>
    </div>


    <button type="button" class="btn btn-secondary btn-sm" id="btnOuvrirModalEvenement"><i class="fa-solid fa-plus mr-1"></i> Ajouter</button>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const dropdown = document.getElementById("agenceDropdown");

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr("href");


            if (target === "#calendar2") {
                $(dropdown).show();
            } else {
                $(dropdown).hide();
            }
        });


        $(document).on("click", "#dropdownAgences", function(e) {
            const menu = $(this).next(".dropdown-menu");
            menu.toggleClass("show");
        });


        $(document).on("click", function(e) {
            if (!$(e.target).closest("#agenceDropdown").length) {
                $(".dropdown-menu").removeClass("show");
            }
        });
    });
</script>

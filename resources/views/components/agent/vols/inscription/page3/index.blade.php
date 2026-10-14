@props(['vol'])
<div id="page3"
     style="margin-top:50px;display: none;font-size: 17px;color: #413e3e">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Conditions</h1>
    </div>
    <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
        <div class="card-header pb-3 pt-0">
            <div style="height: 30px" class="bg-primary text-center">
                <h6 class="m-0 font-weight-bold text-light">
                    Conditions d'inscription
                </h6>
            </div>
        </div>
        <div class="card-body">
            {!! $vol->conditions->Formalite_inscription!!}
        </div>
        <div class="card-footer pb-0 d-flex justify-content-center">
            <button type="button" class="btn btn-secondary page-validation-check"
                    pageNum="3" validationNum="3"
                    id="page3-validation-check1" next="page3-validation-check2">Valider
            </button>
        </div>

    </div>
    <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
        <div class="card-header pb-3 pt-0">
            <div style="height: 30px" class="bg-primary text-center">
                <h6 class="m-0 font-weight-bold text-light">
                    Conditions d'annulation
                </h6>
            </div>
        </div>
        <div class="card-body">
            {!!$vol->conditions->Formalite_annulation!!}
        </div>
        <div class="card-footer pb-0 d-flex justify-content-center">
            <button type="button" class="btn btn-secondary page-validation-check"
                    pageNum="3" validationNum="3"
                    id="page3-validation-check2" next="page3-validation-check3">Valider
            </button>
        </div>

    </div>
    <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
        <div class="card-header pb-3 pt-0">
            <div style="height: 30px" class="bg-primary text-center">
                <h6 class="m-0 font-weight-bold text-light">
                    Conditions de modification
                </h6>
            </div>
        </div>
        <div class="card-body">
            {!! $vol->conditions->Formalite_modifiation !!}
        </div>
        <div class="card-footer pb-0 d-flex justify-content-center">
            <button type="button" class="btn btn-secondary page-validation-check"
                    pageNum="3" validationNum="3"
                    id="page3-validation-check3" next="page5-validation-check1">Valider
            </button>
        </div>

    </div>
</div>

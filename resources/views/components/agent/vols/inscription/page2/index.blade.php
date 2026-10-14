@props(['vol'])
<div id="page2"
     style="margin-top:50px;display: none;font-size: 17px;color: #413e3e">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Conditions</h1>
    </div>
    @if($vol->conditions)
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4  pb-3" style="border: solid 5px #6777ef">
                    <div class="card-header pb-3 pt-0">
                        <div style="height: 30px" class="bg-primary text-center">
                            <h6 class="m-0 font-weight-bold text-light">
                                Conditions Sante
                            </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        {!! $vol->conditions->Formalite_sante !!}
                    </div>
                    <div class="card-footer pb-0 d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary page-validation-check"
                                pageNum="2" validationNum="2"
                                id="page2-validation-check1" next="page2-validation-check2">Valider
                        </button>
                    </div>

                </div>
                <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
                    <div class="card-header pb-3 pt-0">
                        <div style="height: 30px" class="bg-primary d-flex justify-content-center">
                            <h6 class="m-0 font-weight-bold text-light mr-4">
                                Conditions Police
                            </h6>
                            <a href="{{asset('sys/conditions/police.pdf')}}"
                               target="_blank" class="btn text-light px-3 "
                               style="background-color: #fe6219;font-size: 15px">Pdf</a>
                        </div>
                    </div>
                    <div class="card-body">
                        {!! $vol->conditions->Formalite_police!!}
                    </div>
                    <div class="card-footer pb-0 d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary page-validation-check"
                                pageNum="2" validationNum="2"
                                id="page2-validation-check2" next="page3-validation-check1">Valider
                        </button>
                    </div>

                </div>
            </div>

        </div>
    @endif
</div>

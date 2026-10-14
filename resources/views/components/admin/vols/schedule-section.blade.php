@props(['vol' => null])
<div class="card my-5">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-primary" id="h1_aller">
            <i class="fas fa-calendar-check"></i>
            Les Horaires
        </h4>
    </div>
    <div class="card-body row">
        <div class="col-md-3 ">
            <label>Heur de depart (<span class="aller_name">aller</span>)</label>
            <input type="time" name="heur_depart_aller" class="form-control"
                   required value="{{isset($vol) ? $vol->heur_depart_aller : null }}">
        </div>
        <div class="col-md-3 ">
            <label>Heur d'arriver (<span class="aller_name">aller</span>)</label>
            <input type="time" name="heur_arriver_aller" class="form-control"
                   required value="{{isset($vol) ? $vol->heur_arriver_aller: null  }}">
        </div>

        <div class="col-md-3 retour-section">
            <label>Heur de depart (retour)</label>
            <input type="time" name="heur_depart_retour" class="form-control"
                   value="{{isset($vol) ? $vol->heur_depart_retour: null }}">
        </div>
        <div class="col-md-3 retour-section">
            <label>Heur d'arriver (retour)</label>
            <input type="time" name="heur_arriver_retour" class="form-control"
                   value="{{isset($vol) ? $vol->heur_arriver_retour : null }}">
        </div>
        <div class="col-md-3 ">
            <label>Duration (<span class="aller_name">aller</span>)</label>
            <input type="text" name="duration_aller" class="form-control" placeholder="Exam : 09:10"
                   required value="{{isset($vol) ? $vol->duration_aller : null }}">
        </div>
        <div class="col-md-3 retour-section">
            <label>Duration (retour)</label>
            <input type="text" name="duration_retour" class="form-control" placeholder="Exam : 09:10"
                   value="{{isset($vol) ? $vol->duration_retour: null }}">
        </div>

    </div>
</div>

@props(['vol' => null])
<div class="card my-5">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-primary" id="h1_aller">
            <i class="fas fa-suitcase-rolling"></i>
            Packages
        </h4>
    </div>
    <div class="card-body row">
        <div class="col-md-3 {{ $errors->has('bagage') ? ' has-error' : '' }}">
            <label>Bagages en soute (Kg)</label>
            <div class="input-group">
                <input id="bagage" type="number" class="form-control" name="bagage"
                        required min="0" value="{{isset($vol) ? $vol->bagage : 0}}">
            </div>
            @if ($errors->has('bagage'))
                <p class="help-block">
                    <strong>{{ $errors->first('bagage') }}</strong>
                </p>
            @endif
        </div>
        <div class="col-md-3 {{ $errors->has('soute') ? ' has-error' : '' }}">
            <label>Bagage nombre</label>
            <div class="input-group">
                <input id="soute" type="number" class="form-control" name="soute"
                       value="{{isset($vol) ? $vol->soute_num : 1}}" required>
            </div>
            @if ($errors->has('soute'))
                <p class="help-block">
                    <strong>{{ $errors->first('soute') }}</strong>
                </p>
            @endif
        </div>
        <div class="col-md-3 {{ $errors->has('hand') ? ' has-error' : '' }}">
            <label>Bagage A Main (Kg)</label>
            <div class="input-group">

                <input id="hand" type="number" class="form-control" name="hand"
                       value="{{isset($vol) ? $vol->bg_hand : 1}}" required>
            </div>
            @if ($errors->has('hand'))
                <p class="help-block">
                    <strong>{{ $errors->first('hand') }}</strong>
                </p>
            @endif
        </div>
    </div>
</div>

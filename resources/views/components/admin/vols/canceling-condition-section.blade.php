@props(['vol' => null])
<div class="card mt-5">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-window-close"></i>
            Condition d'annulation
        </h4>
    </div>
    <div class="card-body row">
        <div class="col-md-3 {{ $errors->has('j_inscription') ? ' has-error' : '' }}">
            <label>J inscription</label>
            <div class="input-group">
                <input type="number" class="form-control" name="j_inscription" required
                       value="{{ isset($vol) ? $vol->j_inscription :old('j_inscription')}}">
            </div>
            @if ($errors->has('j_inscription'))
                <p class="help-block">
                    <strong>{{ $errors->first('j_inscription') }}</strong>
                </p>
            @endif
        </div>
        <div class="col-md-3 {{ $errors->has('j_45') ? ' has-error' : '' }}">
            <label>j-45</label>
            <div class="input-group">
                <input type="number" class="form-control" name="j_45" required
                       value="{{ isset($vol) ? $vol->j_45 :old('j_45')}}">
            </div>
            @if ($errors->has('j_45'))
                <p class="help-block">
                    <strong>{{ $errors->first('j_45') }}</strong>
                </p>
            @endif
        </div>
        <div class="col-md-3 {{ $errors->has('j_44_j_1') ? ' has-error' : '' }}">
            <label>j-44 a j-1</label>
            <div class="input-group">
                <input type="number" class="form-control" name="j_44_j_1" required
                       value="{{ isset($vol) ? $vol->j_44_j_1 :old('j_44_j_1')}}">
            </div>
            @if ($errors->has('j_44_j_1'))
                <p class="help-block">
                    <strong>{{ $errors->first('j_44_j_1') }}</strong>
                </p>
            @endif
        </div>

    </div>
</div>

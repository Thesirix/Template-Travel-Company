@props(['destination', 'vol' => null])
<div class="card mt-5">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-scroll"></i>
            Formalités
        </h4>
    </div>
    <div class="card-body row">
        <div class="col-12 {{ $errors->has('prix_comprend') ? ' has-error' : '' }}">
            <label>Ce prix comprend</label>
            <div class="input-group">
                <textarea type="text" name="prix_comprend" class="editor">@isset($vol) {!! $vol->prix_comprend !!} @endisset</textarea>
            </div>
            @if ($errors->has('prix_comprend'))
                <p class="help-block">
                    <strong>{{ $errors->first('prix_comprend') }}</strong>
                </p>
            @endif
        </div>
        <div class="col-12 {{ $errors->has('prix_comprend_pas') ? ' has-error' : '' }}">
            <label>Ce prix comprend pas</label>
            <div class="input-group">
                <textarea type="text" name="prix_comprend_pas" class="editor">@isset($vol) {!! $vol->prix_comprend_pas !!} @endisset</textarea>
            </div>
            @if ($errors->has('prix_comprend_pas'))
                <p class="help-block">
                    <strong>{{ $errors->first('prix_comprend_pas') }}</strong>
                </p>
            @endif
        </div>
        
            <div class="col-12 {{ $errors->has('formalite') ? ' has-error' : '' }}">
                <label>Formalités</label>
                <div class="input-group">
                    <textarea type="text" name="formalite" class="editor">@isset($vol) {!! $vol->formalite !!} @endisset</textarea>
                </div>
                @if ($errors->has('formalite'))
                    <p class="help-block">
                        <strong>{{ $errors->first('formalite') }}</strong>
                    </p>
                @endif
            </div>
        
    </div>
</div>

{{-- Logo du menu latéral : logo complet (fond sombre) ; menu replié : icône seule, sinon initiales --}}
<a class="tc-sidebar__brand" href="{{ $href }}">
    @if($brand->logoOnDarkUrl())
        <img class="tc-logo-img" src="{{ $brand->logoOnDarkUrl() }}" alt="{{ $brand->name() }}">
        @if($brand->iconUrl())
            <img class="tc-logo-icon" src="{{ $brand->iconUrl() }}" alt="">
        @else
            <span class="tc-logo-mark tc-logo-mark--collapsed">{{ $brand->initials() }}</span>
        @endif
    @else
        <span class="tc-logo-mark">{{ $brand->initials() }}</span>
        <span class="tc-sidebar__name">{{ $brand->name() }}<small>{{ $subtitle }}</small></span>
    @endif
</a>

<div class="card mb-4">
    <div class="card-header"><h6 class="m-0">À faire</h6></div>
    @if (count($todolists) == 0)
        <div class="tc-empty">Rien à faire aujourd'hui</div>
    @else
        <ul class="tc-list">
            @foreach ($todolists as $todolist)
                <li>{{ $todolist->text }}</li>
            @endforeach
        </ul>
    @endif
</div>

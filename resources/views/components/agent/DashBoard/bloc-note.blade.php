                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="m-0">Bloc-notes</h6>
                            <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#addblacknote">
                                <i class="fa-solid fa-plus mr-1"></i> Ajouter
                            </button>
                        </div>
                        <ul class="tc-list" id="blacknote-card-body">
                            @foreach ($blacknotes as $blacknote)
                                <li class="blacknote blacknote{{ $blacknote->id }}" blacknoteid="{{ $blacknote->id }}" title="Cliquer pour supprimer">
                                    {{ $blacknote->text }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal fade" id="addblacknote" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                @csrf
                                <div class="modal-header border-bottom-0">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body d-flex align-items-center">
                                    <label for="blacknotename" style="font-size: 18px">Nom</label>
                                    <input type="text" class="form-control ml-4" id="blacknotename"
                                        name="blacknotename" />
                                </div>
                                <div class="modal-footer border-top-0 mt-0">
                                    <button type="button" class="btn btn-primary" id="addBlacknoteBtn">Ajouter</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="deleteblacknote" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header border-bottom-0">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p style="font-size: 18px">Voulez-vous supprimer cette Block note?</p>
                                </div>
                                <div class="modal-footer border-top-0 mt-0">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal" blocknoteid=""
                                        id="deteblocknotebutton">Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>




                    <script>
                        window.Laravel = {
                            deleteNoteUrl: '{{ route('agent.notes.destroy') }}',
                            addNoteUrl: '{{ route('agent.notes.store') }}',
                            csrfToken: '{{ csrf_token() }}',
                        };
                    </script>

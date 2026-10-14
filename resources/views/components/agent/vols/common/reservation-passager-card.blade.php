@props(['reservation', 'destination', 'person', 'assurences', 'showVerificationSection' => false])
<div class="py-2 px-1">
    <table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">
        <tr>
            <td class="pt-3 pl-3">Genre</td>
            <td class="pt-3 pr-3">
                <select class="form-control input_{{$person->id}} input" readonly
                        name="old_{{$person->type}}_genre_{{$person->id}}"
                        style="height:35px!important">
                    <option @if($person->genre=='male') selected
                            @endif value="male">
                        Male
                    </option>
                    <option @if($person->genre=='female') selected
                            @endif value="female">
                        Female
                    </option>
                </select>
                <p class="error"></p>

            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Nom</td>
            <td class="pt-3 pr-3">
                <input required="required" type="text" readonly
                       id="old_{{$person->type}}_nom_{{$person->id}}"
                       name="old_{{$person->type}}_nom_{{$person->id}}"
                       value="{{$person->nom}}"
                       class="form-control input_{{$person->id}} input"
                       style="height:35px!important; ">
                <p class="error"></p>

            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Prenom</td>
            <td class="pt-3 pr-3">
                <input required="required" type="text" readonly
                       id="old_{{$person->type}}_prenom_{{$person->id}}"
                       value="{{$person->prenom}}"
                       name="old_{{$person->type}}_prenom_{{$person->id}}"
                       class="form-control input_{{$person->id}} input"
                       style="height:35px!important; ">
                <p class="error"></p>

            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Date naissance</td>
            <td class="pt-3 pr-3">
                @if($person->type=='adulte')
                    @if($reservation->type_voyage=='aller_retour_same_vol')
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               max="{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(12)->subDays(1)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               max="{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(12)->subDays(1)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @else
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               max="{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(12)->subDays(1)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @endif
                @elseif($person->type=='enfant')
                    @if($reservation->type_voyage=='aller_retour_same_vol')
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               min="{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(12)->format('Y-m-d')}}"
                               max="{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(2)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               min="{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(12)->format('Y-m-d')}}"
                               max="{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(2)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @elseif(in_array($reservation->type_voyage,['retour_simple','aller_simple']))
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               min="{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(12)->format('Y-m-d')}}"
                               max="{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(2)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @endif
                @else
                    @if($reservation->type_voyage=='aller_retour_same_vol')
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               min="{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(2)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               min="{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(2)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @elseif(in_array($reservation->type_voyage,['retour_simple','aller_simple']))
                        <input required="required" readonly
                               value="{{$person->date_naissance}}"
                               min="{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(2)->format('Y-m-d')}}"
                               type="date"
                               name="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               id="old_{{$person->type}}_date_naissance_{{$person->id}}"
                               class="form-control input_{{$person->id}} input"
                               style="height:35px!important; ">
                    @endif
                @endif
                <p class="error"></p>
            </td>
        </tr>
        
    </table>
    <table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">
        <tr>
            <td class="pt-3 pl-3">N Passport</td>
            <td class="pt-3 pr-3">
                <div class="iti iti--allow-dropdown">
                    <input type="text" readonly
                           name="old_{{$person->type}}_passport_{{$person->id}}"
                           value="{{$person->passport}}"
                           id="old_{{$person->type}}_passport_{{$person->id}}"
                           class="passportfield form-control input_{{$person->id}} input"
                           style="height:35px!important; ">
                    <p class="error"></p>
                </div>
            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Copie du passeport</td>
            <td class="pt-3 pr-3">
                <input type="file" disabled
                       name="old_{{$person->type}}_passport_file_{{$person->id}}"
                       id="old_{{$person->type}}_passport_file_{{$person->id}}"
                       class="passportfieldfile form-control input_{{$person->id}} input"
                       style="height:35px!important;display: none ">
                <label for="old_{{$person->type}}_passport_file_{{$person->id}}"
                       class="custom-file-upload">
                    <i class="fa fa-cloud-upload"></i> Choisir un fichier
                </label>
                <div class="img_preview" id="img-preview-{{$person->id}}">
                    @if($person->passport_image)
                        @if(str_ends_with(strtolower($person->passport_image), '.pdf'))
                            <embed
                                src="{{asset('storage/passagers/passports/'.$person->passport_image)}}?v={{ time() }}"
                                class="pasport_file" style="height: 50px;"/>
                        @else
                            <img
                                src="{{asset('storage/passagers/passports/'.$person->passport_image)}}?v={{ time() }}"
                                class="pasport_file"
                                style="height: 50px"/>
                        @endif
                        <img src="{{asset('sys/delete.png')}}" style="width: 25px; cursor: pointer" alt="Supprimer"
                             class="delete-passenger-document" data-passenger="{{$person->id}}" data-type="passport"/>
                    @endif
                </div>
                <p class="error"></p>
            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Date expiration</td>
            <td class="pt-3 pr-3">
                @if($reservation->type_voyage=='aller_retour_same_vol')
                    <input
                        value="{{$person->expiration}}" readonly
                        min="{{\Carbon\Carbon::parse($reservation->vol->date_retour)->addMonths(6)->format('Y-m-d')}}"
                        type="date"
                        name="old_{{$person->type}}_expiration_{{$person->id}}"
                        id="old_{{$person->type}}_expiration_{{$person->id}}"
                        class="form-control input_{{$person->id}} input"
                        style="height:35px!important; ">
                @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                    <input
                        value="{{$person->expiration}}" readonly
                        min="{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->addMonths(6)->format('Y-m-d')}}"
                        type="date"
                        name="old_{{$person->type}}_expiration_{{$person->id}}"
                        id="old_{{$person->type}}_expiration_{{$person->id}}"
                        class="form-control input_{{$person->id}} input"
                        style="height:35px!important; ">
                @elseif(in_array($reservation->type_voyage,['retour_simple','aller_simple']))
                    <input
                        value="{{$person->expiration}}" readonly
                        min="{{\Carbon\Carbon::parse($reservation->vol->date_aller)->addMonths(6)->format('Y-m-d')}}"
                        type="date"
                        name="old_{{$person->type}}_expiration_{{$person->id}}"
                        id="old_{{$person->type}}_expiration_{{$person->id}}"
                        class="form-control input_{{$person->id}} input"
                        style="height:35px!important; ">
                @endif
                <p class="error"></p>
            </td>
        </tr>
    </table>
    <table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">
        <tr>
            <td class="pt-3 pl-3">N Titre Séjour</td>
            <td class="pt-3 pr-3">
                <input type="text" readonly
                       name="old_{{$person->type}}_sejour_{{$person->id}}"
                       value="{{$person->sijour}}"
                       id="old_{{$person->type}}_sejour_{{$person->id}}"
                       class="passportfield form-control input_{{$person->id}} input"
                       style="height:35px!important; ">
                <p class="error"></p>
            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Download Titre Séjour</td>
            <td class="pt-3 pr-3">
                <input type="file" disabled
                       name="old_{{$person->type}}_sejour_file_{{$person->id}}"
                       id="old_{{$person->type}}_sejour_file_{{$person->id}}"
                       class="passportfieldfile form-control input_{{$person->id}} input"
                       style="height:35px!important;display: none ">

                <label for="old_{{$person->type}}_sejour_file_{{$person->id}}"
                       class="custom-file-upload">
                    <i class="fa fa-cloud-upload"></i> Choisir un fichier
                </label>
                <div class="img_preview">
                    @if($person->sijour_image)
                        @if(str_ends_with(strtolower($person->sijour_image), '.pdf'))
                            <embed
                                src="{{asset('storage/passagers/sejours/'.$person->sijour_image)}}?v={{ time() }}"
                                class="pasport_file" style="height: 50px;"/>
                        @else
                            <img
                                src="{{asset('storage/passagers/sejours/'.$person->sijour_image)}}?v={{ time() }}"
                                class="pasport_file"
                                style="height: 50px"/>
                        @endif
                        <img src="{{asset('sys/delete.png')}}" style="width: 25px; cursor: pointer" alt="Supprimer"
                             class="delete-passenger-document" data-passenger="{{$person->id}}" data-type="sejour"/>
                    @endif
                </div>
                <p class="error"></p>

            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Date expiration</td>
            <td class="pt-3 pr-3">
                @if($reservation->type_voyage=='aller_retour_same_vol')
                    <input
                        value="{{$person->sijour_expiration}}" readonly
                        min="{{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('Y-m-d')}}"
                        type="date"
                        name="old_{{$person->type}}_sejour_expiration_{{$person->id}}"
                        id="old_{{$person->type}}_sejour_expiration_{{$person->id}}"
                        class="form-control input_{{$person->id}} input"
                        style="height:35px!important; ">
                @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                    <input
                        value="{{$person->sijour_expiration}}" readonly
                        min="{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->format('Y-m-d')}}"
                        type="date"
                        name="old_{{$person->type}}_sejour_expiration_{{$person->id}}"
                        id="old_{{$person->type}}_sejour_expiration_{{$person->id}}"
                        class="form-control input_{{$person->id}} input"
                        style="height:35px!important; ">
                @elseif(in_array($reservation->type_voyage,['retour_simple','aller_simple']))
                    <input
                        value="{{$person->sijour_expiration}}" readonly
                        min="{{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('Y-m-d')}}"
                        type="date"
                        name="old_{{$person->type}}_sejour_expiration_{{$person->id}}"
                        id="old_{{$person->type}}_sejour_expiration_{{$person->id}}"
                        class="form-control input_{{$person->id}} input"
                        style="height:35px!important; ">
                @endif
                <p class="error"></p>
            </td>
        </tr>
        <tr>
            <td class="pl-3 pl-3">Titre Séjour en cours</td>
            <td class="pt-3 pr-3">
                <input type="checkbox" disabled
                       name="old_{{$person->type}}_sejour_en_cour_{{$person->id}}"
                       id="old_{{$person->type}}_sejour_en_cour_{{$person->id}}"
                       class="pl-3 input_{{$person->id}} input"
                       @if($person->sijour_en_cour==1) checked @endif>
                <p class="error"></p>
            </td>
        </tr>
    </table>
    @if($person->type=='adulte')
        <table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">
            <tr>
                <td class="pt-3 pl-3">
                        Telephone 1
                    </td>
                <td class="pt-3 pr-3">
                    <input type="text"
                           
                           name="old_{{$person->type}}_tele1_{{$person->id}}"
                           id="old_{{$person->type}}_tele1_{{$person->id}}"
                           class="form-control tele input_{{$person->id}} input"
                           readonly
                           value="{{$person->tele1}}"
                           style="height:35px!important; ">
                    <p class="error"></p>

                </td>
            </tr>
            <tr>
                <td class="pt-3 pl-3">
                        Telephone 2
                    </td>
                <td class="pt-3 pr-3">
                    <input type="text"
                           name="old_{{$person->type}}_tele2_{{$person->id}}"
                           id="old_{{$person->type}}_tele2_{{$person->id}}"
                           class="form-control tele input_{{$person->id}} input"
                           readonly
                           value="{{$person->tele2}}"
                           style="height:35px!important; ">
                    <p class="error"></p>
                </td>
            </tr>
            

                <tr>
                    <td class="pt-3 pl-3">Téléphone sur place</td>
                    <td class="pt-3 pr-3">
                        <input type="text" readonly
                               name="old_{{$person->type}}_tele_destination_{{$person->id}}"
                               id="old_{{$person->type}}_tele_destination_{{$person->id}}"

                               value="{{$person->tele_destination}}"
                               class="form-control tele input_{{$person->id}} input"
                               style="height:35px!important; ">
                        <p class="error"></p>
                    </td>
                </tr>
            
            <tr>
                <td class="pt-3 pl-3">Email</td>
                <td class="pt-3 pr-3">
                    <input type="email"
                           name="old_{{$person->type}}_email_{{$person->id}}"
                           id="old_{{$person->type}}_email_{{$person->id}}"
                           value="{{$person->email}}"
                           class="form-control input_{{$person->id}} input" readonly
                           style="height:35px!important; ">
                    <p class="error"></p>
                </td>
            </tr>
        </table>
    @endif
    <table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">
        <tr>
            <td class="pt-3 pl-3">Assistance specifique</td>
            <td class="pt-3 pr-3">
                <input type="text" readonly
                       name="old_{{$person->type}}_assistance_{{$person->id}}"
                       id="old_{{$person->type}}_assistance_{{$person->id}}"
                       value="{{$person->assistance}}"
                       class="form-control input_{{$person->id}} input"
                       style="height:35px!important; ">
            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Observation</td>
            <td class="pt-3 pr-3">
                                        <textarea type="text" name="old_{{$person->type}}_observation_{{$person->id}}"
                                                  class="form-control input_{{$person->id}} input" readonly
                                        >{{$person->Observation}}</textarea>
            </td>
        </tr>
        <tr>
            <td class="pt-3 pl-3">Dossier</td>
            <td class="pt-3 pr-3">
                <label
                    for="old_{{$person->type}}_dossier_etat_{{$person->id}}1">Complet</label>
                <input
                    @if($person->dossier_etat=='Complet') checked
                    @endif type="radio"
                    disabled
                    name="old_{{$person->type}}_dossier_etat_{{$person->id}}"
                    id="old_{{$person->type}}_dossier_etat_{{$person->id}}1"
                    value="Complet"
                    class="mr-3 ml-2 input_{{$person->id}} input"/>
                <label
                    for="old_{{$person->type}}_dossier_etat_{{$person->id}}2">Incomplet</label><input
                    @if($person->dossier_etat!='Complet') checked
                    @endif type="radio"
                    disabled
                    name="old_{{$person->type}}_dossier_etat_{{$person->id}}"
                    id="old_{{$person->type}}_dossier_etat_{{$person->id}}2"
                    value="Incomplet"
                    class="ml-2 input_{{$person->id}} input"/>'

            </td>
        </tr>
    </table>
    <table class="px-3 py-2 mb-2 d-none"
           style="border:solid 1px orange;width:100%;">
        @foreach($assurences as $assurence)
            <tr>
                <td class="pt-3 pl-3">{{$assurence->name}}</td>
                <td class="pt-3 pl-3">{{$assurence->prix}} €</td>
                <td class="pt-3 pr-3">
                    <input type="radio"
                           id="old_{{$person->type}}_assurance_{{$person->id}}"
                           disabled @if($assurence->prix==0) checked @endif
                           class="input_{{$person->id}} input"
                           name="old_{{$person->type}}_assurance_{{$person->id}}">
                </td>
            </tr>
        @endforeach
    </table>
    @if($showVerificationSection)
        <table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%;">
            <tr>
                <td class="pt-3 pl-3">
                    <label
                        for="old_{{$person->type}}_verifier_{{$person->id}}1">Verifier</label>
                </td>
                <td class="pt-3 pr-3">
                    <input type="checkbox" disabled value="Oui"
                           @if($person->verifier== "Oui") checked @endif
                           name="old_{{$person->type}}_verifier_{{$person->id}}"
                           id="old_{{$person->type}}_verifier_{{$person->id}}1"
                           class="mr-3 ml-2 input input_{{$person->id}}"/>
                </td>
            </tr>
            @if($reservation->type_voyage== 'aller_retour_diffrent_vol'  || $reservation->type_voyage == 'aller_simple')
                <tr>
                    <td class="pt-3 pl-3">
                        <label
                            for="old_{{$person->type}}_inscrit_aller_{{$person->id}}1">Inscrit aller</label>
                    </td>
                    <td class="pt-3">
                        <input type="checkbox" value="1" disabled
                               @if($person->inscrit_aller) checked @endif
                               name="old_{{$person->type}}_inscrit_aller_{{$person->id}}"
                               id="old_{{$person->type}}_inscrit_aller_{{$person->id}}1"
                               class="mr-3 ml-2 input input_{{$person->id}}"/>
                    </td>
                    <td class="pt-3 pr-3">
                        <input type="text" placeholder="ref aller" readonly
                               name="old_{{$person->type}}_ref_aller_{{$person->id}}"
                               id="old_{{$person->type}}_ref_aller_{{$person->id}}"
                               value="{{$person->ref_aller}}"
                               class="form-control input input_{{$person->id}}"
                               style="height:35px!important; ">
                    </td>
                </tr>
            @endif
            @if($reservation->type_voyage== 'aller_retour_diffrent_vol'  || $reservation->type_voyage == 'retour_simple')
                <tr>
                    <td class="pt-3 pl-3">
                        <label
                            for="old_{{$person->type}}_inscrit_retour_{{$person->id}}1">Inscrit retour</label>
                    </td>
                    <td class="pt-3">
                        <input type="checkbox" value="1" disabled
                               @if($person->inscrit_retour) checked @endif
                               name="old_{{$person->type}}_inscrit_retour_{{$person->id}}"
                               id="old_{{$person->type}}_inscrit_retour_{{$person->id}}1"
                               class="mr-3 ml-2 input input_{{$person->id}}"/>
                    </td>
                    <td class="pt-3 pr-3">
                        <input type="text" placeholder="ref retour" readonly
                               name="old_{{$person->type}}_ref_retour_{{$person->id}}"
                               id="old_{{$person->type}}_ref_retour_{{$person->id}}"
                               value="{{$person->ref_retour}}"
                               class="form-control input input_{{$person->id}}"
                               style="height:35px!important; ">
                    </td>
                </tr>
            @endif
        </table>
    @endif
</div>

@once
    @push('script')
        <script>
            // Pièces jointes du passager : aperçu en grand, suppression après confirmation
            $(document).on('click', '.pasport_file', function () {
                $('#image_preview_mdal_img').attr('src', $(this).attr('src'))
                $('#image_preview_mdal').modal('show')
            })
            $(document).on('click', '.delete-passenger-document', function () {
                $('#delete-passport-passenger-form [name=passenger_id]').val($(this).data('passenger'))
                $('#delete-passport-passenger-form [name=file_type]').val($(this).data('type'))
                $('#delete-passport-passenger-modal').modal('show')
            })
        </script>
    @endpush
@endonce

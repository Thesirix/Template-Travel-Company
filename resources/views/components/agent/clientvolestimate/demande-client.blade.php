@props(['favorites'])
<div class="card my-3">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between accordion-header">

        <h6 class="m-0 font-weight-bold text-primary">
            <img src="{{ asset('sys/demande.png') }}" alt="Icone Demande"
                style="width: 60px; height: 60px; margin-right: 10px;">

            Demande Client
        </h6>
    </div>
    <div class="accordion-content">
        <div class="card-body">
            @csrf

            <div class="row align-items-center mt-3">
                <label for="Formule" class="mb-0 col-2 "><strong>Formule</strong></label>
                <select class="form-control col-5 " name="formule" id="formule" required>
                    <option value="Aérien">Aérien</option>
                    <option value="Maritime">Maritime</option>
                    <option value="Sejour">Sejour</option>
                </select>
            </div>

            <div class="row align-items-center mt-3" id="bagage-row">
                <label for="demande_bagage" class="mb-0 col-2 "><strong>Bagage</strong></label>
                <select class="form-control col-5" type="text" name="demande_bagage" id="demande_bagage" required>

                    <option value="Sans">Sans</option>
                    <option value="Avec">Avec</option>


                </select>
            </div>

            <div class="row align-items-center mt-3">
                <label for="Destination" class="mb-0 col-2 "><strong>Destination</strong></label>
                <select class="form-control col-5" type="text" name="destination" id="destination" required>


                    <option disabled>─── Nos destinations ───</option>
                    @foreach($brand->destinations() as $favorite)
                        <option value="{{ $favorite->name }}">{{ $favorite->name }}</option>
                    @endforeach

                    <option disabled>─── Autres ───</option>
                    <option value="Afghanistan,AF">Afghanistan</option>
                    <option value="Albania,AL">Albanie</option>
                    <option value="Algeria,DZ">Algérie</option>
                    <option value="American Samoa,AS,WS,AS">Samoa Américaines</option>
                    <option value="Andorra,AD">Andorre</option>
                    <option value="Angola,AO">Angola</option>
                    <option value="Anguilla,AI">Anguilla</option>
                    <option value="Antigua and Barbuda,AG">Antigua-et-Barbuda</option>
                    <option value="Argentina,AR">Argentine</option>
                    <option value="Armenia,AM">Arménie</option>
                    <option value="Aruba,AW">Aruba</option>
                    <option value="Australia,AU">Australie</option>
                    <option value="Austria,AT">Autriche</option>
                    <option value="Azerbaijan,AZ">Azerbaïdjan</option>
                    <option value="Bahamas,BS">Bahamas</option>
                    <option value="Bahrain,BH">Bahreïn</option>
                    <option value="Bangladesh,BD">Bangladesh</option>
                    <option value="Barbados,BB">Barbade</option>
                    <option value="Belarus,BY">Biélorussie</option>
                    <option value="Belgium,BE">Belgique</option>
                    <option value="Belize,BZ">Belize</option>
                    <option value="Benin,BJ">Bénin</option>
                    <option value="Bermuda,BM">Bermudes</option>
                    <option value="Bhutan,BT">Bhoutan</option>
                    <option value="Bolivia,BO">Bolivie</option>
                    <option value="Bonaire,BQ">Bonaire</option>
                    <option value="Bosnia and Herzegovina,BA">Bosnie-Herzégovine</option>
                    <option value="Botswana,BW">Botswana</option>
                    <option value="Brazil,BR">Brésil</option>
                    <option value="British India,INn Ocean Territory,IO">Territoire britannique de l'océan
                        Indien
                    </option>
                    <option value="Brunei,BN">Brunéi</option>
                    <option value="Bulgaria,BG">Bulgarie</option>
                    <option value="Burkina Faso,BF">Burkina Faso</option>
                    <option value="Burundi,BI">Burundi</option>
                    <option value="Cambodia,KH">Cambodge</option>
                    <option value="Cameroon,CM">Cameroun</option>
                    <option value="Canada,CA">Canada</option>
                    <option value="Canary Islands,IC">Îles Canaries</option>
                    <option value="Cape Verde,CV">Cap-Vert</option>
                    <option value="Cayman Islands,KY">Îles Caïmans</option>
                    <option value="Central African Republic,CF">République Centrafricaine</option>
                    <option value="Chad,TD">Tchad</option>
                    <option value="Channel Islands,JE">Îles Anglo-Normandes</option>
                    <option value="Chile,CL">Chili</option>
                    <option value="China,CN">Chine</option>
                    <option value="Christmas Island,CX">Île Christmas</option>
                    <option value="Burma,CC">Îles Cocos</option>
                    <option value="Colombia,CO">Colombie</option>
                    <option value="Comoros,KM">Comores</option>
                    <option value="Congo (Brazzaville),CG">Congo</option>
                    <option value="Cook Islands,CK">Îles Cook</option>
                    <option value="Costa Rica,CR">Costa Rica</option>
                    <option value="Cote d'Ivoire,CI">Côte d'Ivoire</option>
                    <option value="Croatia,HR">Croatie</option>
                    <option value="Cuba,CU">Cuba</option>
                    <option value="Netherlands Antilles,CW">Curaçao</option>
                    <option value="Cyprus,CY">Chypre</option>
                    <option value="Czech Republic,CZ">République tchèque</option>
                    <option value="Denmark,DK">Danemark</option>
                    <option value="Djibouti,DJ">Djibouti</option>
                    <option value="Dominica,DM">Dominique</option>
                    <option value="Dominican Republic,DO">République dominicaine</option>
                    <option value="East Timor,TL">Timor oriental</option>
                    <option value="Ecuador,EC">Équateur</option>
                    <option value="Egypt,EG">Égypte</option>
                    <option value="El Salvador,SV">El Salvador</option>
                    <option value="Equatorial Guinea,GN,GQ">Guinée équatoriale</option>
                    <option value="Eritrea,ER">Érythrée</option>
                    <option value="Estonia,EE">Estonie</option>
                    <option value="Ethiopia,ET">Éthiopie</option>
                    <option value="Falkland Islands,FK">Îles Malouines</option>
                    <option value="Faroe Islands,FO">Îles Féroé</option>
                    <option value="Fiji,FJ">Fidji</option>
                    <option value="Finland,FI">Finlande</option>
                    <option value="France,FR">France</option>
                    <option value="French Guiana,GF">Guyane française</option>
                    <option value="French Polynesia,PF,PF">Polynésie française</option>
                    <option value="French Southern Territories,TF">Terres australes et antarctiques françaises
                    </option>
                    <option value="Gabon,GA">Gabon</option>
                    <option value="Gambia,GM">Gambie</option>
                    <option value="Georgia,GE">Géorgie</option>
                    <option value="Germany,DE">Allemagne</option>
                    <option value="Ghana,GH">Ghana</option>
                    <option value="Gibraltar,GI">Gibraltar</option>
                    <option value="United Kingdom,GB">Royaume-Uni</option>
                    <option value="Greece,GR">Grèce</option>
                    <option value="Greenland,GL">Groenland</option>
                    <option value="Grenada,GD">Grenade</option>
                    <option value="Guadeloupe,GP">Guadeloupe</option>
                    <option value="Guam,GU">Guam</option>
                    <option value="Guatemala,GT">Guatemala</option>
                    <option value="Guinea">Guinée</option>
                    <option value="Guyana,GY">Guyana</option>
                    <option value="Haiti,HT">Haïti</option>
                    <option value="Honduras,HN">Honduras</option>
                    <option value="Hong Kong,HK">Hong Kong</option>
                    <option value="Hungary,HU">Hongrie</option>
                    <option value="Iceland,IS">Islande</option>
                    <option value="Indonesia,ID">Indonésie</option>
                    <option value="India">Inde</option>
                    <option value="Iran,IR">Iran</option>
                    <option value="Iraq,IQ">Irak</option>
                    <option value="Ireland,IE">Irlande</option>
                    <option value="Isle of Man,IM">Île de Man</option>
                    <option value="Israel,IL">Israël</option>
                    <option value="Italy,IT">Italie</option>
                    <option value="Jamaica,JM">Jamaïque</option>
                    <option value="Japan,JP">Japon</option>
                    <option value="Jordan,JO">Jordanie</option>
                    <option value="Kazakhstan,KZ">Kazakhstan</option>
                    <option value="Kenya,KE">Kenya</option>
                    <option value="Kiribati,KI">Kiribati</option>
                    <option value="North Korea,KP">Corée du Nord</option>
                    <option value="South Korea,KR">Corée du Sud</option>
                    <option value="Kuwait,KW">Koweït</option>
                    <option value="Kyrgyzstan,KG">Kirghizistan</option>
                    <option value="Laos,LA">Laos</option>
                    <option value="Latvia,LV">Lettonie</option>
                    <option value="Lebanon,LB">Liban</option>
                    <option value="Lesotho,LS">Lesotho</option>
                    <option value="Liberia,LR">Libéria</option>
                    <option value="Libya,LY">Libye</option>
                    <option value="Liechtenstein,LI">Liechtenstein</option>
                    <option value="Lithuania,LT">Lituanie</option>
                    <option value="Luxembourg,LU">Luxembourg</option>
                    <option value="Macau,MO">Macao</option>
                    <option value="Macedonia,MK">Macédoine du Nord</option>
                    <option value="Madagascar,MG">Madagascar</option>
                    <option value="Malaysia,MY">Malaisie</option>
                    <option value="Malawi,MW">Malawi</option>
                    <option value="Maldives,MV">Maldives</option>
                    <option value="Mali,ML">Mali</option>
                    <option value="Malta,MT">Malte</option>
                    <option value="Marshall Islands,MH">Îles Marshall</option>
                    <option value="Martinique,MQ">Martinique</option>
                    <option value="Mauritania,MR">Mauritanie</option>
                    <option value="Mauritius,MU">Maurice</option>
                    <option value="Mayotte,YT">Mayotte</option>
                    <option value="Mexico,MX">Mexique</option>
                    <option value="Midway Islands,UM">Îles Midway</option>
                    <option value="Moldova,MD">Moldavie</option>
                    <option value="Monaco,MC">Monaco</option>
                    <option value="Mongolia,MN">Mongolie</option>
                    <option value="Montserrat,MS">Montserrat</option>
                    <option value="Morocco,MA">Maroc</option>
                    <option value="Mozambique,MZ">Mozambique</option>
                    <option value="Myanmar,MM">Myanmar</option>
                    <option value="Namibia,NA">Namibie</option>
                    <option value="Nauru,NR">Nauru</option>
                    <option value="Nepal,NP">Népal</option>
                    <option value="Netherlands,NL">Pays-Bas</option>
                    <option value="New Caledonia,NC">Nouvelle-Calédonie</option>
                    <option value="New Zealand,NZ">Nouvelle-Zélande</option>
                    <option value="Nicaragua,NI">Nicaragua</option>
                    <option value="Niger,NE">Niger</option>
                    <option value="Nigeria,NG">Nigéria</option>
                    <option value="Niue,NU">Niue</option>
                    <option value="Norfolk Island,NF">Île Norfolk</option>
                    <option value="Norway,NO">Norvège</option>
                    <option value="Oman,OM">Oman</option>
                    <option value="Pakistan,PK">Pakistan</option>
                    <option value="Palau,PW">Palaos</option>
                    <option value="Palestine,PS">Palestine</option>
                    <option value="Panama,PA">Panama</option>
                    <option value="Papua New Guinea,PG">Papouasie-Nouvelle-Guinée</option>
                    <option value="Paraguay,PY">Paraguay</option>
                    <option value="Peru,PE">Pérou</option>
                    <option value="Philippines,PH">Philippines</option>
                    <option value="Pitcairn Islands,PN">Îles Pitcairn</option>
                    <option value="Poland,PL">Pologne</option>
                    <option value="Portugal,PT">Portugal</option>
                    <option value="Puerto Rico,PR">Porto Rico</option>
                    <option value="Qatar,QA">Qatar</option>
                    <option value="Montenegro,ME">Monténégro</option>
                    <option value="Serbia,RS">Serbie</option>
                    <option value="Reunion,RE">La Réunion</option>
                    <option value="Romania,RO">Roumanie</option>
                    <option value="Russia,RU">Russie</option>
                    <option value="Rwanda,RW">Rwanda</option>
                    <option value="Saint Barthelemy,BL">Saint-Barthélemy</option>
                    <option value="Saint Eustatius,BQ">Saint-Eustache</option>
                    <option value="Saint Helena,SH">Sainte-Hélène</option>
                    <option value="Saint Kitts and Nevis,KN">Saint-Christophe-et-Niévès</option>
                    <option value="Saint Lucia,LC">Sainte-Lucie</option>
                    <option value="Saint Martin,MF">Saint-Martin</option>
                    <option value="Saint Pierre and Miquelon,PM">Saint-Pierre-et-Miquelon</option>
                    <option value="Saint Vincent and the Grenadines,VC">Saint-Vincent-et-les-Grenadines</option>
                    <option value="Northern Mariana Islands,MP">Saïpan</option>
                    <option value="Samoa">Samoa</option>
                    <option value="American Samoa">Samoa américaines</option>
                    <option value="San Marino,SM">Saint-Marin</option>
                    <option value="Sao Tome and Principe,ST">Sao Tomé-et-Principe</option>
                    <option value="Saudi Arabia,SA">Arabie saoudite</option>
                    <option value="Senegal,SN">Sénégal</option>
                    <option value="Seychelles,SC">Seychelles</option>
                    <option value="Sierra Leone,SL">Sierra Leone</option>
                    <option value="Singapore,SG">Singapour</option>
                    <option value="Slovakia,SK">Slovaquie</option>
                    <option value="Slovenia,SI">Slovénie</option>
                    <option value="Solomon Islands,SB">Îles Salomon</option>
                    <option value="Somalia,SO">Somalie</option>
                    <option value="South Africa,ZA">Afrique du Sud</option>
                    <option value="Spain,ES">Espagne</option>
                    <option value="Sri Lanka,LK">Sri Lanka</option>
                    <option value="Sudan,SD">Soudan</option>
                    <option value="Suriname,SR">Suriname</option>
                    <option value="Swaziland,SZ">Eswatini</option>
                    <option value="Sweden,SE">Suède</option>
                    <option value="Switzerland,CH">Suisse</option>
                    <option value="Syria,SY">Syrie</option>
                    <option value="French Polynesia">Tahiti</option>
                    <option value="Taiwan,TW">Taïwan</option>
                    <option value="Tajikistan,TJ">Tadjikistan</option>
                    <option value="Tanzania,TZ">Tanzanie</option>
                    <option value="Thailand,TH">Thaïlande</option>
                    <option value="Togo,TG">Togo</option>
                    <option value="Tokelau,TK">Tokelau</option>
                    <option value="Tonga,TO">Tonga</option>
                    <option value="Trinidad and Tobago,TT">Trinité et Tobago</option>
                    <option value="Tunisia,TN">Tunisie</option>
                    <option value="Turkey,TR">Turquie</option>
                    <option value="Turkmenistan,TM">Turkménistan</option>
                    <option value="Turks and Caicos Islands,TC">Îles Turques et Caïques</option>
                    <option value="Tuvalu,TV">Tuvalu</option>
                    <option value="Uganda,UG">Ouganda</option>
                    <option value="United Kingdom">Royaume-Uni</option>
                    <option value="Ukraine,UA">Ukraine</option>
                    <option value="United Arab Emirates,AE">Émirats arabes unis</option>
                    <option value="United States,US">États-Unis</option>
                    <option value="Uruguay,UY">Uruguay</option>
                    <option value="Uzbekistan,UZ">Ouzbékistan</option>
                    <option value="Vanuatu,VU">Vanuatu</option>
                    <option value="Vatican City,VA">Vatican</option>
                    <option value="Venezuela,VE">Venezuela</option>
                    <option value="Vietnam,VN">Vietnam</option>
                    <option value="British Virgin Islands,VG">Îles Vierges britanniques</option>
                    <option value="Virgin Islands,VI">Îles Vierges américaines</option>
                    <option value="Wake Island,UM">Île Wake</option>
                    <option value="Wallis and Futuna,WF">Wallis et Futuna</option>
                    <option value="Yemen,YE">Yémen</option>
                    <option value="Congo (Kinshasa),CD">Zaïre</option>
                    <option value="Zambia,ZM">Zambie</option>
                    <option value="Zimbabwe,ZW">Zimbabwe</option>


                </select>
            </div>

            <div class="row align-items-center mt-3">
                <label for="departure_city" class="mb-0 col-2 "><strong>Ville départ</strong></label>
                <select class="form-control col-5 " name="depart" id="depart" required>
                    <option disabled>─── Favoris ───</option>
                    @foreach ($favorites as [$city, $code])
                        <option value="{{ $city }}">{{ $city }} ({{ $code }})</option>
                    @endforeach


                </select>
            </div>

            <div class="row align-items-center mt-3">
                <label for="arrived_city" class="mb-0 col-2 "><strong>Ville arrivée</strong></label>
                <select class="form-control col-5 arrival_city demande-client" name="arrived_city" id="arrival_city"
                    required style="visibility: hidden;">


                </select>
            </div>


            <div class="row align-items-center mt-3">
                <label for="departure_date" class="mb-0 col-2"><strong>Date d'aller</strong></label>
                <div class="col-5" style="position: relative;padding: 0;">
                    <input class="form-control date-input" type="text" name="departure_date" id="departure_date"
                        placeholder="JJ/MM/AAAA" required style="padding-right: 40px;">
                    <i class="fa fa-calendar input-date-icon" target="#departure_date"
                        style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;"></i>
                </div>
            </div>

            <div class="row align-items-center mt-3">
                <label for="return_date" class="mb-0 col-2"><strong>Date Retour</strong></label>
                <div class="col-5" style="position: relative;padding: 0;">
                    <input class="form-control date-input" type="text" name="return_date" id="return_date"
                        placeholder="JJ/MM/AAAA" required>
                    <i class="fa fa-calendar input-date-icon" target="#return_date"
                        style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;"></i>
                </div>
            </div>


            <div class="row  mt-3">
                <label for="nbpassagers" class="mb-0 col-2 "><strong>Nbres passagers</strong></label>
                <div class="listtotalnbpassagers d-flex flex-column col-5 w-100 pl-0">
                    <div class="align-items-center" style="border-radius:5px">
                        <button class="btn d-flex" id="nbpassagers" type="button"
                            style="font-size: 14px;height:37.5px;background-color: white;color: #005eb8;width: 300px;">
                            <img src="{{ asset('sys/clients.svg') }}" width="20" height="20" class=""
                                style="fill: #005eb8 !important;">
                            <span id="nbprsn"> 1 Adulte - 0 Enfant - 0 Bébé (total : 1)</span>
                        </button>
                        <div class="passagersdropdown" id="passagersdropdown" style="display:none;width: 300px; ">
                            <div class="card card-body"
                                style="width:300px;position: absolute;z-index: 5 ">
                                <!-- Modal body -->
                                <h4 style="color: #22366b">Passager</h4>
                                <div class="modal-body">
                                    <div class="input-group">
                                        <div class="d_menu d-flex align-items-center">
                                            <img src="{{ asset('front/img/moins.png') }}" style="height: 15px"
                                                class="img_ps fa-minus-circle" v="adult" id="removeadulat">
                                            <span id="adult_m" class="span_num mx-1" style="font-size: 22px">1
                                            </span>
                                            <input type="hidden" name="nb_passagers_adultes"
                                                id="nb_passagers_adultes" value="1">
                                            <img src="{{ asset('front/img/plus.png') }}" style="height: 15px"
                                                class="img_ps fa-plus-circle" v="adult" id="addadulat">
                                            Adulte
                                        </div>
                                        <div class="d_menu d-flex align-items-center">
                                            <img src="{{ asset('front/img/moins.png') }}"
                                                class="img_ps fa-minus-circle" v="enfant" style="height: 15px"
                                                id="removeenfant">
                                            <span id="enfant_m" class="span_num mx-1"
                                                style="font-size: 22px !important;">0</span>
                                            <input type="hidden" name="nb_passagers_childs" id="nb_passagers_childs"
                                                value="0">
                                            <img src="{{ asset('front/img/plus.png') }}" style="height: 15px"
                                                class="img_ps fa-plus-circle" v="enfant" id="addenfant">
                                            <span>Enfant (2-11 ans)</span>
                                        </div>

                                        <div class="d_menu d-flex align-items-center">
                                            <img src="{{ asset('front/img/moins.png') }}" style="height: 15px"
                                                class="img_ps fa-minus-circle" v="bebe" id="removebebe">
                                            <span id="bebe_m" class="span_num mx-1" style="font-size: 22px"> 0
                                            </span>
                                            <input type="hidden" name="nb_passagers_babies" id="nb_passagers_babies"
                                                value="0">
                                            <img src="{{ asset('front/img/plus.png') }}" style="height: 15px"
                                                class="img_ps fa-plus-circle" v="bebe" id="addbebe">
                                            <span>Bébé (< 2 ans)</span>
                                        </div>
                                        <div id="message_div" style="color: red;font-size: 12px;padding-left: 15px;">
                                        </div>
                                        <div class="text-right">
                                            <button class="btn btn-sm btn-primary closepassagersmodal"
                                                type="button">Valider
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <p class="text-danger mb-0">{{ $error }}</p>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="card-footer d-flex align-items-center position-relative">
            <button type="button" id="createClientRequest"
                class="btn-save-request btn btn-primary mt-3 px-3 py-2 mx-auto scroll-save-button"
                style="border-radius: 11px;">
                Enregistrer
            </button>
            <button type="button" class="reset-button btn btn-warning px-3 py-2 mx-3"
                style="border-radius: 11px;">Réinitialiser
            </button>
        </div>


        <div id="demande-client-message" class="mt-3"></div>
    </div>
</div>

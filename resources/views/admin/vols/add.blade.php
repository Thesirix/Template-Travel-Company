@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
@include('admin.partials.top-nav')
@endsection
@section('content')

    <div class="container-fluid py-4" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Ajouter vol
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Gestion vols</li>
                <li class="breadcrumb-item">Ajouter</li>
            </ol>
        </div>

        <form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left"
              method="post" action="{{route('admin.vols.store', $destination)}}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="nb_escale_a" id="nb_escale_a" value="1">
            <input type="hidden" name="nb_escale_r" id="nb_escale_r" value="1">
            <input type="hidden" name="type_r_0" value="1">
            <input type="hidden" name="type_a_0" value="0">
            <input type="hidden" name="destination" value="{{$destination}}">

            <x-admin.vols.information-general-section :villes="$villes" :conditions="$conditions"
                                                        :destination="$destination" :groups="$groups" operation="add" />

            <!-----------------------------  aller---------------------------------------->
            <x-admin.vols.aller-section :destination="$destination" :companies="$companies" :airPorts="$AirPorts"/>

            <!-----------------------------  retour---------------------------------------->
            <x-admin.vols.retour-section :destination="$destination" :companies="$companies" :airPorts="$AirPorts"/>


            <x-admin.vols.schedule-section />

            
                <x-admin.vols.passager-prices-section :destination="$destination"/>
            
            <x-admin.vols.packages-section/>

            <x-admin.vols.formalite-section :destination="$destination"/>

            
                <x-admin.vols.canceling-condition-section/>
            
            <div class="col-md-3 mt-4">
                <button type="submit" class="btn btn-primary row" style="margin: auto;">
                    Enregistrer le Vol
                </button>
            </div>


        </form>

    </div>

@endsection
@push("script")
    <script src="https://cdn.ckeditor.com/ckeditor5/32.0.0/classic/ckeditor.js"></script>

    <script>
        var editors = document.querySelectorAll('.editor')
        for (let i = 0; i < editors.length; i++) {
            ClassicEditor
                .create(editors[i])
                .catch(error => {
                    console.error(error);
                });
        }

    </script>
    {{--    aller simple--}}
    <script>
        function type_voyage() {

            if ($('#type_voyage').val() == 'aller_simple') {
                $('.retour-section').hide();
                $('.aller_name').text('Aller')
            } else if ($('#type_voyage').val() == 'retour_simple') {
                $('.retour-section').hide();
                $('.aller_name').text('Retour')
            } else {
                $('.retour-section').show();
                $('.aller_name').text('Aller')
            }

        }

        $(window).ready(function () {
            type_voyage()
        });
        $('#type_voyage').change(function (e) {
            type_voyage()
        })
    </script>
    <script type="text/javascript">

        function deleteEscale_a(id) {
            $("#" + id).remove();
            var nb = parseInt($("#nb_escale_a").val());
            var k = nb - 2;
            $("#btn_sup_escale_a_" + k).css('display', 'inline-block')
            $("#nb_escale_a").val(k + 1);
        }

        function deleteEscale_r(id) {

            $("#" + id).remove();
            var nb = parseInt($("#nb_escale_r").val());
            var k = nb - 2;
            $("#btn_sup_escale_r_" + k).css('display', 'inline-block')
            $("#nb_escale_r").val(nb - 1);
        }

        $(document).ready(function () {

            $("#menu_toggle").on("click", function () {
                var st = $(this).attr("status");
                if (st == "none") {
                    $(".menu_text").css('display', 'inline-block');
                    $(this).attr("status", 'block');
                } else {
                    $(".menu_text").css('display', 'none');
                    $(this).attr("status", 'none');
                }

            });
            $("#fermer_not").on("click", function () {

                $("#pop_inf_show").css('display', 'none');
            });

            $("#escale_btn_a").click(function () {
                var nb = parseInt($("#nb_escale_a").val());

                if (nb < 3) {

                    var k = nb - 1;
                    var sel = $("#air_a_a_" + k).val();
                    $("#btn_sup_escale_a_" + k).css('display', 'none')
                    var airport = $('#airPort_escale').html();
                    var nbrd = "a_" + nb;
                    var img = "{{asset('sys/delete.png')}}";
                    var cls = "'row_escala_" + nb + "'";

                    $("#content_escale_a").append('<div class="card my-5 "  id=' + cls + '><div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"><img src="' + img + '"   style="width: 30px;float: right;cursor: pointer;right: -95%;position:relative;height: 30px;" onclick="deleteEscale_a(' + cls + ')" id="btn_sup_escale_a_' + nb + '"><input type="hidden" name="type_a_' + nb + '" value="0"></div><div class="card-body"><div class="row clearfix"><div class="col-md-6"><p>aéroport  de depart</p><select class="form-control show-tick" name="air_de_' + nbrd + '" id="air_de_' + nbrd + '">' + airport + '</select></div><div class="col-md-6"><p>aéroport  d’arriver</p><select class="form-control show-tick" name="air_a_' + nbrd + '" id="air_a_' + nbrd + '">' + airport + '</select></div></div><div class="row "><div class="col-md-3" ><p>Date de depart</p><div class="input-group"><input type="date" required class="form-control date" name="date_de_' + nbrd + '" placeholder="Ex: 30/07/2016"  id="date_de_' + nbrd + '"></div></div><div class="col-md-3"><p>heure de depart</p><div class="input-group"><input type="time" required class="form-control time24  temp" placeholder="Ex: 23:59" name="heure_de_' + nbrd + '" hd="heure_de_' + nbrd + '" ha="heure_a_' + nbrd + '" temp="temp_' + nbrd + '" id="heure_de_' + nbrd + '" hv="heure_a_a_' + k + '" dv1="date_de_a_' + k + '" dv2="date_de_' + nbrd + '"></div></div><div class="col-md-3"><p style="color:red">heure d’arriver</p><div class="input-group"><input type="time" required class="form-control time24 temp" placeholder="Ex: 23:59" name="heure_a_' + nbrd + '" hd="heure_de_' + nbrd + '" ha="heure_a_' + nbrd + '" temp="temp_' + nbrd + '" id="heure_a_' + nbrd + '" hv=""></div></div><div class="col-md-3 " ><p style="color:red">le temp de vol</p><div class="input-group"><input type="text" required class="form-control" placeholder="Ex: 23:59" name="temp_' + nbrd + '" id="temp_' + nbrd + '" ></div></div>' +
                        '<div class="col-md-3 " ><p style="">Modele</p><div class="input-group"><input type="text" required class="form-control"  name="modele_' + nbrd + '" id="temp_' + nbrd + '" ></div></div></div>' +
                        '</div></div>');

                    $("#air_de_" + nbrd + " option").each(function (index) {
                        if (index !== 0) {

                            $row = $(this);

                            if ($row.attr('value') == sel) {
                                $row.attr('selected', "selected");
                            }

                        }
                    });


                    nb++;
                    $("#nb_escale_a").val(nb);

                    //Date
                    //Time
                    $(".temp").blur(function () {
                        var heuredeb = $("#" + $(this).attr('hd')).val();
                        var heurefin = $("#" + $(this).attr('ha')).val();
                        var hv = $(this).attr('hv');
                        if (heuredeb != "" && heurefin != "") {
                            hd = heuredeb.split(":");
                            hf = heurefin.split(":");
                            hd[0] = eval(hd[0]);
                            hd[1] = eval(hd[1]);
                            hd[2] = eval(hd[2]);
                            hf[0] = eval(hf[0]);
                            hf[1] = eval(hf[1]);
                            hf[2] = eval(hf[2]);
                            if (hf[2] < hd[2]) {
                                hf[1] = hf[1] - 1;
                                hf[2] = hf[2] + 60;
                            }
                            if (hf[1] < hd[1]) {
                                hf[0] = hf[0] - 1;
                                hf[1] = hf[1] + 60;
                            }
                            if (hf[0] < hd[0]) {
                                hf[0] = hf[0] + 24;
                            }
                            $("#" + $(this).attr('temp')).val((hf[0] - hd[0]) + ":" + (hf[1] - hd[1]));

                        }

                        if ($("#" + hv).val() != undefined) {

                            hd = $("#" + hv).val().split(":");
                            hf = $(this).val().split(":");
                            hd[0] = eval(hd[0]);
                            hd[1] = eval(hd[1]);
                            hf[0] = eval(hf[0]);
                            hf[1] = eval(hf[1]);

                            d1 = $("#" + $(this).attr('dv1')).val();
                            d2 = $("#" + $(this).attr('dv2')).val();

                            if (hd[0] < hf[0] || d1 != d2) {

                                /* valde */
                                $(this).removeClass('not_error');
                                $("#pop_inf_show").hide();

                            } else {

                                /*verification date */
                                $("#pop_inf_show").show();
                                $("#error_not").html("verifier l'heure d'arriver ou date de depart");
                                $(this).addClass('not_error');

                            }

                        }

                    });
                }

            });

            $("#escale_btn_r").click(function () {
                var nb = parseInt($("#nb_escale_r").val());

                if (nb < 3) {
                    var k = nb - 1;
                    var sel = $("#air_a_r_" + k).val();
                    $("#btn_sup_escale_r_" + k).css('display', 'none')
                    var airport = $('#airPort_escale').html();
                    var nbrd = "r_" + nb;
                    var img = "{{asset('sys/delete.png')}}";
                    var cls = "'row_escalr_" + nb + "'";
                    $("#content_escale_r").append('<div class="card my-5"  id=' + cls + '><div class=" card-header py-3 d-flex flex-row align-items-center justify-content-between"><img src="' + img + '"   style="width: 30px;float: right;cursor: pointer;right: -95%;position:relative;height: 30px;" onclick="deleteEscale_r(' + cls + ')" id="btn_sup_escale_r_' + nb + '"><input type="hidden" name="type_r_' + nb + '" value="1"></div><div class="card-body"><div class=" row clearfix"><div class="col-md-6"><p>aéroport  de depart</p><select class="form-control show-tick" name="air_de_' + nbrd + '" id="air_de_' + nbrd + '">' + airport + '</select></div><div class="col-md-6"><p>aéroport  d’arriver</p><select class="form-control show-tick" name="air_a_' + nbrd + '" id="air_a_' + nbrd + '">' + airport + '</select></div></div><div class="row "><div class="col-md-3" ><p>Date de depart</p><div class="input-group"><input type="date" required class="form-control date" name="date_de_' + nbrd + '" placeholder="Ex: 30/07/2016"  ></div></div><div class="col-md-3"><p>heure de depart</p><div class="input-group"><input type="time" required class="form-control time24 temp" placeholder="Ex: 23:59" name="heure_de_' + nbrd + '" hd="heure_de_' + nbrd + '" ha="heure_a_' + nbrd + '" temp="temp_' + nbrd + '" id="heure_de_' + nbrd + '" hv="heure_a_r_' + k + '" dv1="date_de_r_' + k + '" dv2="date_de_' + nbrd + '"></div></div><div class="col-md-3"><p style="color:red">heure d’arriver</p><div class="input-group"><input type="time" required class="form-control time24 temp" placeholder="Ex: 23:59" name="heure_a_' + nbrd + '" hd="heure_de_' + nbrd + '" ha="heure_a_' + nbrd + '" temp="temp_' + nbrd + '" id="heure_a_' + nbrd + '" hv=""></div></div><div class="col-md-3 " ><p style="color:red">le temp de vol</p><div class="input-group"><input type="text" required class="form-control" placeholder="Ex: 23:59" name="temp_' + nbrd + '" id="temp_' + nbrd + '" ></div></div>' +
                        '<div class="col-md-3 " ><p style="">Modele</p><div class="input-group"><input type="text" required class="form-control"  name="modele_' + nbrd + '" id="temp_' + nbrd + '" ></div></div></div>' +
                        '</div></div>');
                    $("#air_de_" + nbrd + " option").each(function (index) {
                        if (index !== 0) {

                            $row = $(this);

                            if ($row.attr('value') == sel) {
                                $row.attr('selected', "selected");
                            }

                        }
                    });

                    nb++;
                    $("#nb_escale_r").val(nb);

                    //Date
                    //Time
                    $(".temp").blur(function () {
                        var heuredeb = $("#" + $(this).attr('hd')).val();
                        var heurefin = $("#" + $(this).attr('ha')).val();
                        var hv = $(this).attr('hv');
                        if (heuredeb != "" && heurefin != "") {
                            hd = heuredeb.split(":");
                            hf = heurefin.split(":");
                            hd[0] = eval(hd[0]);
                            hd[1] = eval(hd[1]);
                            hd[2] = eval(hd[2]);
                            hf[0] = eval(hf[0]);
                            hf[1] = eval(hf[1]);
                            hf[2] = eval(hf[2]);
                            if (hf[2] < hd[2]) {
                                hf[1] = hf[1] - 1;
                                hf[2] = hf[2] + 60;
                            }
                            if (hf[1] < hd[1]) {
                                hf[0] = hf[0] - 1;
                                hf[1] = hf[1] + 60;
                            }
                            if (hf[0] < hd[0]) {
                                hf[0] = hf[0] + 24;
                            }
                            $("#" + $(this).attr('temp')).val((hf[0] - hd[0]) + ":" + (hf[1] - hd[1]));

                        }
                        if ($("#" + hv).val() != undefined) {
                            hd = $("#" + hv).val().split(":");
                            hf = $(this).val().split(":");
                            hd[0] = eval(hd[0]);
                            hd[1] = eval(hd[1]);
                            hf[0] = eval(hf[0]);
                            hf[1] = eval(hf[1]);

                            d1 = $("#" + $(this).attr('dv1')).val();
                            d2 = $("#" + $(this).attr('dv2')).val();

                            if (hd[0] < hf[0] && d1 != d2) {
                                /* valde */
                                $(this).removeClass('not_error');
                                $("#pop_inf_show").hide();

                            } else {

                                /*verification date */
                                $("#pop_inf_show").show();
                                $("#error_not").html("verifier l'heure de depart");
                                $(this).addClass('not_error');

                            }

                        }

                    });


                }
            });


            $(".changeTable_23").click(function () {
                $tb = $(this).attr('tb');
                $(".tb_company").css('display', 'none');
                $("#tb_company_" + $tb).css('display', 'block');


            });


            /* ----------------------  serach with write*/

            $(".search_tb").on("keyup", function () {
                /*var value = $(this).val();*/
                var tb = $(this).attr('tb');
                var td = $(this).attr('td');
                var value = $(this).val().toLowerCase();

                $("#" + tb + " tr").each(function (index) {
                    if (index !== 0) {

                        $row = $(this);

                        var id = $row.find("." + td).text().trim().toLowerCase();
                        if (id.indexOf(value) !== 0) {
                            $row.hide();
                        } else {
                            $row.show();
                        }


                    }
                });
            });

            $(".search_tb1").on("change", function () {
                /*var value = $(this).val();*/
                var tb = $(this).attr('tb');
                var td = $(this).attr('td');
                var value = $(this).val();

                $("#" + tb + " tr").each(function (index) {
                    if (index !== 0) {

                        $row = $(this);


                        if ($(this).attr('y') != value) {
                            $row.hide();
                        } else {
                            $row.show();
                        }


                    }
                });
            });
            /*---------------------------------------------------*/

            $(".date_n").blur(function () {
                var $date_t = "";
                if ($('#retour_date').val() != undefined) {
                    $date_t = $('#retour_date').val();
                } else {
                    $date_t = $('#depart_date').val();
                }

                var n = $(this).val().split("/");
                var r = $date_t.split("/");
                var cv = $(this).attr('cv');
                var vl = $(this).attr('vl');

                r[0] = eval(r[0]);
                r[1] = eval(r[1]);
                r[2] = eval(r[2]);
                n[0] = eval(n[0]);
                n[1] = eval(n[1]);
                n[2] = eval(n[2]);

                var d = r[0] - n[0];

                if (r[0] < n[0]) {
                    d = d + 30;
                    r[1] = r[1] - 1
                }

                var m = r[1] - n[1];
                if (r[1] < n[1]) {
                    m = m + 12;
                    r[2] = r[2] - 1
                }
                var y = r[2] - n[2];

                dn = y * 30 * 12 + m * 30 + d;
                var type = $("#" + cv).val();
                y = dn / 360;

                var valide = false;
                if (type == "INF") {
                    if (y < 2 && dn > 0) {
                        valide = true;
                    } else {
                        valide = false;
                    }
                } else if (type == "CHD") {
                    if (y > 2 && y < 12) {
                        valide = true;
                    } else {
                        valide = false;
                    }
                } else {
                    if (y > 12) {
                        valide = true;
                    } else {
                        valide = false;
                    }
                }

                if (valide) {
                    /* valde */
                    $(this).removeClass('not_error');
                    $("#" + vl).css('display', 'block');
                    $("#pop_inf_show").hide();

                } else {

                    /*verification date */
                    $("#" + vl).css('display', 'none');
                    $("#pop_inf_show").show();
                    $("#error_not").html("verifier la date de naissance");
                    $(this).addClass('not_error');

                    $('#' + cv + ' .opt').attr('selected', true);
                    $('#' + cv + ' .opt').attr('selected', false);

                }


            });

            $(".date_e").blur(function () {
                var vl = $(this).attr('vl');
                var pv = $(this).attr('pv');
                var e = $(this).val().split("/");
                if ($(this).attr('required') == "required") {
                    var r = $('#retour_date').val().split("/");


                    r[0] = eval(r[0]);
                    r[1] = eval(r[1]);
                    r[2] = eval(r[2]);
                    e[0] = eval(e[0]);
                    e[1] = eval(e[1]);
                    e[2] = eval(e[2]);

                    var d = e[0] - r[0];
                    if (e[0] < r[0]) {
                        d = d + 30;
                        e[1] = e[1] - 1
                    }
                    var m = e[1] - r[1];
                    if (e[1] <= r[1]) {
                        m = m + 12;
                        e[2] = e[2] - 1
                    }
                    var y = e[2] - r[2];

                    m = m + y * 12 + d / 30;

                    if (m > 6) {
                        /* valde */
                        $(this).removeClass('not_error');
                        $("#" + vl).css('display', 'block');
                        $("#pop_inf_show").hide();

                        $('#' + pv).removeAttr("required");

                    } else {

                        /*verification date */
                        $("#" + vl).css('display', 'none');
                        $("#pop_inf_show").show();
                        $("#error_not").html("verifier la date d'experation");
                        $(this).addClass('not_error');
                        $('#' + pv).attr("required", "required");

                    }


                } else {
                    var vl = $(this).attr('vl');
                    $(this).removeClass('not_error');
                    $("#pop_inf_show").hide();
                    $("#" + vl).css('display', 'none');
                }


            });


            $(".temp").blur(function () {
                var heuredeb = $("#" + $(this).attr('hd')).val();
                var heurefin = $("#" + $(this).attr('ha')).val();

                var hv = $(this).attr('hv');

                if (heuredeb != "" && heurefin != "") {
                    hd = heuredeb.split(":");
                    hf = heurefin.split(":");
                    hd[0] = eval(hd[0]);
                    hd[1] = eval(hd[1]);
                    hd[2] = eval(hd[2]);
                    hf[0] = eval(hf[0]);
                    hf[1] = eval(hf[1]);
                    hf[2] = eval(hf[2]);
                    if (hf[2] < hd[2]) {
                        hf[1] = hf[1] - 1;
                        hf[2] = hf[2] + 60;
                    }
                    if (hf[1] < hd[1]) {
                        hf[0] = hf[0] - 1;
                        hf[1] = hf[1] + 60;
                    }
                    if (hf[0] < hd[0]) {
                        hf[0] = hf[0] + 24;
                    }
                    $("#" + $(this).attr('temp')).val((hf[0] - hd[0]) + ":" + (hf[1] - hd[1]));

                }
                if ($("#" + hv).val() != undefined) {

                    hd = $("#" + hv).val().split(":");
                    hf = $(this).val().split(":");
                    hd[0] = eval(hd[0]);
                    hd[1] = eval(hd[1]);
                    hf[0] = eval(hf[0]);
                    hf[1] = eval(hf[1]);

                    if (hd[0] < hf[0]) {
                        /* valde */
                        $(this).removeClass('not_error');
                        $("#pop_inf_show").hide();

                    } else {

                        /*verification date */
                        $("#pop_inf_show").show();
                        $("#error_not").html("verifier l'heure de depart");
                        $(this).addClass('not_error');

                    }

                }


            });


        });

    </script>

@endpush

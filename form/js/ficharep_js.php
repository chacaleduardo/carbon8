<?
?>
<script>
    (async function(){
    $("[id^='modallocalbioensaio_']").each((index,element) => {
        debugger
    //     $.ajax({
    //             url: "ajax/carregamapadebioensaio.php",
    //             type: "POST",	
    //             data: { 
    //                 idtagpai : $(element).attr('idtagpai'),
    //                 idtagtipo : $(element).attr('idtagtipo'),
    //                 idunidadepadrao : $("#unidadepadrao").val(),
    //             },	
    //             success: function(ret){
    //                 $(element).find(".panel-body.carregando").html(ret).removeClass("carregando")
    //             },						
    //             error: function(objxmlreq){
    //                 console.error(objxmlreq.status); 
    //             }
    //         });
    });
})()
    jProd = <?= $jProd ?>; // autocomplete produto
    //mapear autocomplete de produto
    jProd = jQuery.map(jProd, function(o, id) {
        return {
            "label": o.descr,
            value: id
        }
    });

    $('.select-picker').selectpicker('render');

    $("#idlote").autocomplete({
        source: jProd,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });
    $("#idlote2").autocomplete({
        source: jProd,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });

    function deletalote(inidficharep, campo) {
        $pos = "_x_u_ficharep_idficharep=" + inidficharep + "&" + campo + "=NULL"
        CB.post({
            objetos: $pos,
            parcial: true
        });
    }

    function inovolote() {
        var strCabecalho = "</strong>NOVO LOTE <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='criarlote();'><i class='fa fa-circle'></i>Salvar</button></strong>";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#novolote").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#idlotelote").attr("name", "_999_i_lote_idlote");
        objfrm.find("#idprodservlote").attr("name", "_999_i_lote_idprodserv");

        objfrm.find("#exerciciolote").attr("name", "_999_i_lote_exercicio");
        objfrm.find("#statuslote").attr("name", "_999_i_lote_status");
        objfrm.find("#qtdprod").attr("name", "_999_i_lote_qtdprod");
        objfrm.find("#idunidade").attr("name", "_999_i_lote_idunidade");

        objfrm.find("#tipoobjetolote").attr("name", "_999_i_lote_tipoobjetosolipor");
        objfrm.find("#idobjetolote").attr("name", "_999_i_lote_idobjetosolipor");

        $("#cbModalCorpo").html(objfrm.html());
        $('#cbModal').modal('show');

    }

    function criarlote() {

        var str = "_x_i_lote_idprodserv=" + $("[name=_999_i_lote_idprodserv]").val() +
            "&_x_i_lote_status=ABERTO&_x_i_lote_exercicio=" + $("[name=_999_i_lote_exercicio]").val() +
            "&_x_i_lote_idunidade=" + $("[name=_999_i_lote_idunidade]").val() +
            "&_x_i_lote_qtdprod=" + $("[name=_999_i_lote_qtdprod]").val() +
            "&_x_i_lote_qtdpedida=" + $("[name=_999_i_lote_qtdprod]").val() +
            "&_x_i_lote_tipoobjetosolipor=" + $("[name=_999_i_lote_tipoobjetosolipor]").val() +
            "&_x_i_lote_idobjetosolipor=" + $("[name=_999_i_lote_idobjetosolipor]").val();

        CB.post({
            objetos: str,
            parcial: true,
            posPost: function(resp, status, ajax) {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });

    }


    function mostrarmodal(idanalise) {
        CB.modal({
            titulo: "Locais de Bioensaio",
            corpo: $("#modallocalbioensaio_" + idanalise).html(),
            classe: "oitenta"
        })
    }

    function calculDiff() {
        var ndt = $("[name=_999_u_servicoensaio_data]").val();
        var vdt = $('[name=old_servicoensaio_data]').val();

        var data1 = moment(ndt, 'DD/MM/YYYY');
        //setando data2
        var data2 = moment(vdt, 'DD/MM/YYYY');
        //tirando a diferenca da data2 - data1 em dias
        var diff = data1.diff(data2, 'days');

        var vdia = $('[name=_999_u_servicoensaio_dia]').val();

        var novodia = (parseInt(vdia) + parseInt(diff));

        $('[name=_999_u_servicoensaio_dia]').val(novodia);
        $('[name=old_servicoensaio_data]').val(ndt);
        // alert(diff);
    }

    function inovoservico(vthis, inidservicoensaio) {
        var strCabecalho = "</strong>SERVIÇO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#servico").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#idservicoensaio").attr("name", "_999_u_servicoensaio_idservicoensaio");
        objfrm.find("#idservicoensaio").attr("value", inidservicoensaio);

        if ($(vthis).attr('tservico') == 'TRANSFERENCIA') {
            objfrm.find("#fdata").attr("disabled", 'disabled');
        }
        objfrm.find("#fdata").attr("name", "_999_u_servicoensaio_data");
        objfrm.find("#fdata").attr("value", $(vthis).attr('dmadata'));

        objfrm.find("#fdata2").attr("name", "old_servicoensaio_data");
        objfrm.find("#fdata2").attr("value", $(vthis).attr('dmadata'));

        objfrm.find("#status").attr("name", "_999_u_servicoensaio_status");
        objfrm.find("#status option[value='" + $(vthis).attr('status') + "']").attr("selected", "selected");

        objfrm.find("#dia").attr("name", "_999_u_servicoensaio_dia");
        objfrm.find("#dia").attr("value", $(vthis).attr('dia'));

        objfrm.find("#obsdias").text($(vthis).attr('difdias'));

        objfrm.find("#observ").attr("name", "_999_u_servicoensaio_obs");
        objfrm.find("textarea#observ").text($(vthis).attr('observ'));

        $("#cbModalCorpo").html(objfrm.html());
        $('#cbModal').modal('show');

    }

    function qtdanalise(vthis, inidanalise, bioqtd, inidbioensaio) {
        if ($(vthis).val() > bioqtd) {
            alert("A quantidade máxima do estudo é " + bioqtd);
            $(vthis).val(bioqtd)
            return false;
        } else {
            CB.post({
                objetos: "_x_u_analise_idanalise=" + inidanalise + "&_x_u_analise_qtd=" + $(vthis).val(),
                parcial: true
            });
        }

    }

    function iulocalensaio(inidtag, inidlocalensaio, inidanalise, inigaiola) {
        //debugger;
        $_post = "_x_u_localensaio_idlocalensaio=" + inidlocalensaio + "&_x_u_localensaio_idtag=" + inidtag + "&_x_u_localensaio_gaiola=" + inigaiola + "&_x_u_localensaio_status=AGENDADO";

        CB.post({
            objetos: $_post,
            refresh: "refresh"
            //,parcial: true
        });
    }

    function dellocalensaio(inidtag, inidlocalensaio, inidanalise, inigaiola) {
        //debugger;
        $_post = "_x_u_localensaio_idlocalensaio=" + inidlocalensaio + "&_x_u_localensaio_idtag=";

        CB.post({
            objetos: $_post,
            refresh: "refresh"
            //,parcial: true
        });
    }



    function novogrupo() {
        var strCabecalho = "Gerar Estudos&nbsp;&nbsp;<button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='inserebio()'><i class='fa fa-circle'></i>Salvar</button>";
        $("#cbModalTitulo").html((strCabecalho));

        $('#bioensaio :input').removeAttr('disabled');

        var htmloriginal = $("#bioensaio").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#b_idespeciefinalidade").attr("id", "i_b_especiefinalidade").attr('name', '_b_i_bioensaio_idespeciefinalidade');
        objfrm.find("#b_idficharep").attr("id", "i_b_idficharep").attr('name', '_b_i_bioensaio_idficharep');
        objfrm.find("#b_nascimento").attr("id", "i_b_nascimento").attr('name', '_b_i_bioensaio_nascimento');
        objfrm.find("#b_idunidade").attr("id", "i_b_idunidade").attr('name', '_b_i_bioensaio_idunidade');
        objfrm.find("#b_qtd").attr("id", "i_b_qtd").attr('name', '_b_i_bioensaio_qtd');
        objfrm.find("#b_estudos").attr("id", "i_b_estudos").attr('name', 'qtd_bioensaios');
        objfrm.find("#bioensaio_status").attr("id", "i_b_status").attr('name', '_b_i_bioensaio_status');
        objfrm.find("#b_lotepd").attr("id", "i_b_lotepd").attr('name', '_b_i_bioensaio_idlotepd');

        $("#cbModalCorpo").html(objfrm.html());
        $("#cbModal").attr("class", "modal sessenta in");
        $('#cbModal').modal('show');

        //mapear autocomplete de clientes
        jCli = jQuery.map(jCli, function(o, id) {
            return {
                "label": o.nome,
                value: id
            }
        });
        //autocomplete de clientes
        $("#idpessoa_bioensaio").autocomplete({
            source: jCli,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
                };
            }
        });

    }

    $('#bioensaio :input').attr('disabled', true);

    function inserebio() {

	    const qtd = parseInt($('#i_b_qtd').val() );    
		if(!qtd || isNaN(qtd)) return alertAtencao(`Informe a Quantidade de Animais por Estudo.`);
        const estudos = parseInt($('#i_b_estudos').val() );
        if(!estudos || isNaN(estudos)) return alertAtencao(`Informe a Quantidade de Estudos a Criar.`);
        
        debugger;
        CB.post({
            objetos: "_b_i_bioensaio_idficharep=" + $("#i_b_idficharep").val() + "&_b_i_bioensaio_nascimento=" + $('#i_b_nascimento').val() + "&_b_i_bioensaio_idespeciefinalidade=" + $('#i_b_especiefinalidade').val() +
                "&_b_i_bioensaio_idunidade=" + $('#i_b_idunidade').val() + "&_b_i_bioensaio_qtd=" + $('#i_b_qtd').val() + "&_b_i_bioensaio_idlotepd=" + $('#i_b_lotepd').val() +
                "&_b_i_bioensaio_status=" + $('#i_b_status').val()+ "&qtd_bioensaios=" + $('#i_b_estudos').val(),
            parcial: true
        });
    }


    function setanalise(inI, bioqtd) {
        //debugger;
        CB.post({
            objetos: "_x_u_analise_idanalise=" + $('#idanalise' + inI).val() + "&_x_u_analise_datadzero=" + $("[name=_ficharep_oldinicio]").val() + "&_x_u_analise_idbioterioanalise=" + $('#idbioterioanalise' + inI).val() + "&_x_u_analise_qtd=" + bioqtd,
            parcial: true
        });

    }

    function altservico(vidservico, vstatus) {

        CB.post({
            objetos: "_x_u_servicoensaio_idservicoensaio=" + vidservico + "&_x_u_servicoensaio_status=" + vstatus,
            refresh: "refresh"
        });
    }

    function setdt(indt) {
        //alert(indt);
        var theDate = new Date(indt);
        var myNewDate = new Date(theDate);
        myNewDate.setDate(myNewDate.getDate() + 22);
        //$("#end_date").val(myNewDate.getFullYear() + '-' + ("0" + (myNewDate.getMonth() + 1)).slice(-2) + '-' + ("0" + myNewDate.getDate()).slice(-2))
        $("#fim").val(("0" + myNewDate.getDate()).slice(-2) + '/' + ("0" + (myNewDate.getMonth() + 1)).slice(-2) + '/' + myNewDate.getFullYear());
    }

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
    CB.posPost = function() {

        $("#cbModalCorpo").html("");
        $('#cbModal').modal('hide');
    }
</script>
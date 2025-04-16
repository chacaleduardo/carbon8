<script>
	//------- Injeção PHP no Jquery -------
	var calendario = '<?=$calendario?>';
	var modulo = '<?=$pagvalmodulo?>';
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	$(".historicoPrevisaoEntrega").webuiPopover({
        trigger: "click",
        placement: "right",
        width: 700,
        delay: {
            show: 300,
            hide: 0
        }
    });

	$(".oEmailorc").webuiPopover({
		trigger: "hover",
		placement: "right",
		delay: {
			show: 300,
			hide: 0
		}
	});
	//------- Funções JS -------

	//------- Funções Módulo -------
	function alteravalor(campo, valor, tabela, inid, texto) 
    {
        htmlTrModelo = "";
        htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>
                    <td>${texto}</td>
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="nf" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                        <input name="_h1_i_${tabela}_valor" value="${valor}" class="size10 calendario" type="text">
                    </td>
                </tr>
                <tr>
                    <td>Justificativa:</td>
                    <td>
                        <select name="_h1_i_${tabela}_justificativa" onchange="alteraoutros(this)" vnulo class="size50 justificativa">
                            <?=fillselect(NfEntradaController::$_justificativa)?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>`;

        if (campo == 'previsaoentrega') 
        {
            var objfrm = $(htmlTrModelo);
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        } else {
            var objfrm = $(htmlTrModelo);
            objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        }

        strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";
    
        CB.modal({
            titulo: strCabecalho,
            corpo: "<table>" + objfrm.html() + "</table>",
            classe: 'sessenta',
            aoAbrir: function(vthis) {
                $("[name='_h1_i_modulohistorico_valor']").daterangepicker({
                    "singleDatePicker": true,
                    "locale": CB.jDateRangeLocale
                }).on('apply.daterangepicker', function(ev, picker) {
                    console.log(picker.startDate.format('YYYY-MM-DD'));
                    $(this).html(picker.startDate.format("DD/MM/YYYY") || "");
                });

                 $(`[name="_h1_i_${tabela}_valor"]`).val(valor);
            }
        });
    }

	function conferencia(incampo) 
    {
        var idfluxostatus = getIdFluxoStatus(modulo, 'DIVERGENCIA');
        var idFluxoStatusHist = getIdFluxoStatusHist(modulo, 'DIVERGENCIA');
        CB.post({
            objetos: `_x_i_nfpendencia_idnf=${$("[name=_1_u_nf_idnf]").val()}&_1_u_nf_idnf=${$("[name=_1_u_nf_idnf]").val()}&_1_u_nf_tiponf=${$("[name=_1_u_nf_tiponf]").val()}&_1_u_nf_status=DIVERGENCIA&_1_u_nf_idfluxostatus=${idfluxostatus}`,
            parcial: true,
            posPost: function() {
                CB.post({
                    urlArquivo: 'ajax/_fluxo.php?fluxo=fluxo',
                    refresh: false,
                    objetos: {
                        "_modulo": modulo,
                        "_primary": 'idnf',
                        "_idobjeto": $("[name=_1_u_nf_idnf]").val(),
                        "idfluxo": '',
                        "idfluxostatushist": idFluxoStatusHist,
                        "idstatusf": idfluxostatus,
                        "statustipo": 'DIVERGENCIA',
                        "idfluxostatus": idfluxostatus,
                        "idfluxostatuspessoa": '',
                        "ocultar": '',
                        "prioridade": '20',
                        "tipobotao": '',
                        "acao": "alterarstatus"
                    }
                });
            }
        })
    }

	function atualizapendencia(inidnfpendencia, vthis, campo) 
    {
        CB.post({
            objetos: `_x_u_nfpendencia_idnfpendencia=` + inidnfpendencia + `&_x_u_nfpendencia_` + campo + `=` + $(vthis).val(),
            parcial: true
        });
    }

	function conferenciaok(inidnfpendencia) 
    {
        var today = new Date();
        var date = today.getDate() + '-' + (today.getMonth() + 1) + '-' + today.getFullYear();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        var dateTime = date + ' ' + time;
        CB.post({
            objetos: `_x_u_nfpendencia_idnfpendencia=` + inidnfpendencia + `&_x_u_nfpendencia_status=RESOLVIDO&_x_u_nfpendencia_datatratativa=${dateTime}`,
            parcial: true
        });
    }

    function atualizanfconferenciaitem(vthis, inid) 
    {
        CB.post({
            objetos: `_x_u_nfconferenciaitem_idnfconferenciaitem=` + inid + `&_x_u_nfconferenciaitem_resultado=` + $(vthis).val(),
            parcial: true,
            refresh: false
        })
    }

    function alteraoutros(vthis) 
    {
        valor = $(vthis).val();
        if (valor == 'OUTROS') {
            $(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_modulohistorico_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" vnulo/>');
            $('.justificativa').attr('name', '');
        } else {
            $('#justificaticaText').remove();
            $('.justificativa').attr('name', '_h1_i_modulohistorico_justificativa');
        }
    }
	//------- Funções Módulo -------
	
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>
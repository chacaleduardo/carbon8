<script>
	//------- Injeção PHP no Jquery -------
    idSpedC100 = '<?=$idSpedC100?>';
    <? if($_1_u_nf_idnf){ ?>
        var qtdArquivo = '<?=NfEntradaController::buscarArquivoPorTipoObjetoEIdObjeto('nf', $_1_u_nf_idnf)?>';
    <? } else { ?>
        var qtdArquivo = 0 ;
    <? } ?>
    var gIdEmpresa = "<?=cb::idempresa();?>";
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	if ($("[name=_1_u_nf_idnf]").val()) 
    {
		$("#xmlnfe").dropzone({
            previewTemplate: $("#cbDropzone").html(),
            url: "form/_arquivo.php",
            idObjeto: $("[name=_1_u_nf_idnf]").val(),
            tipoObjeto: 'nf',
            tipoArquivo: 'XMLNFE'
        });
	}

    CB.on('posPost',() => {
        if(idSpedC100.lenght > 0 && qtdArquivo == 0)
        {
            CB.post({
                objetos: `_9999_d_spedc100_idspedc100=${idspedc100}`
            });
        }
	})
	//------- Funções JS -------

	//------- Funções Módulo -------
	function atualizafinalidade(vthis) 
    {
        CB.post({
            objetos: "_atf_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_atf_u_nf_idfinalidadeprodserv=" + $(vthis).val(),
            parcial: true,
            posPost: function(resp, status, ajax) {
                gerainfsped();             
            }
        });
    }
 
    function atnfitemxml(vthis, inidnfitemxml, coluna,consumo) 
	{ 
        fnidpessoa = $(vthis).attr('fnidpessoa');
        cprodforn = $(vthis).attr('cprodforn');
        if(consumo!='Y'){
            $('.nfitemxml_idprodserv').each(function(i, o) {
                if($(o).attr('cprodforn') != cprodforn) {
                    $(o).find(`[value="${$(vthis).val()}"]`).remove();
                }
            });  
        }

        CB.post({
            objetos: `_atx_u_nfitemxml_idnfitemxml=${inidnfitemxml}&_atx_u_nfitemxml_${coluna}=${$(vthis).val()}&fncprodforn=${cprodforn}&fnidpessoa=${fnidpessoa}`,
            parcial: true,
            refresh: false
        });
    }

    function altcheck(vtab, vcampo, vid, vcheck) 
    {
        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck,
            parcial: true
        });
    }


  function manifestanf(vthis){
       
    CB.post({
            objetos: "_1_u_nf_idnf="+<?=$_1_u_nf_idnf ?>+"&_1_u_nf_idnfe="+$(vthis).val(),
            parcial: true,
            posPost: function() {
                enviomanifestacao();
            }
        });
    }

    function enviomanifestacao() 
	{
        vurl = "../inc/nfe/sefaz4/func/manidest.php?idnotafiscal=<?=$_1_u_nf_idnf ?>";
        if (confirm("Confirmar recebimento da Nota Fiscal para a Sefaz?")) 
		{
            $.ajax({
                type: "get",
                url: vurl,
                success: function(data) {
                    alert(data);
                    document.location.reload();
                },
                error: function(objxmlreq) {
                    alert('Erro:\n' + objxmlreq.status);
                }
            }) //$.ajax
        }
    }

    function altfimnfe(infim) 
	{
        var str;
        if (infim == 'faticms') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=Y&_x_u_nf_consumo=N&_x_u_nf_imobilizado=N&_x_u_nf_outro=N&_x_u_nf_comercio=N`;
        }
        if (infim == 'consumo') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=Y&_x_u_nf_imobilizado=N&_x_u_nf_outro=N&_x_u_nf_comercio=N`;
        }
        if (infim == 'imobilizado') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=N&_x_u_nf_imobilizado=Y&_x_u_nf_outro=N&_x_u_nf_comercio=N`;
        }
        if (infim == 'outro') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=N&_x_u_nf_imobilizado=N&_x_u_nf_outro=Y&_x_u_nf_comercio=N`;
        }
        if (infim == 'comercio') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=N&_x_u_nf_imobilizado=N&_x_u_nf_outro=N&_x_u_nf_comercio=Y`;
        }

        CB.post({
            objetos: str,
            parcial: true,
            posPost: function() {
                gerainfsped();
            }
        })
    }

    function gerainfsped() 
    {
        var prazo = $("[name=_1_u_nf_prazo]").val();
        if (!prazo && (gIdEmpresa != 4)) {
            alert('Para atualizar as informações preencher a data de entrada.');
        } else {

            var idnotafiscal = $("#idnf").val();
            vurl = "inc/php/gerainfsped.php?idnf=" + idnotafiscal;

            $.ajax({
                type: "get",
                url: vurl,
                success: function(data) {
                    gerainfspedfiscal()
                },
                error: function(objxmlreq) {
                    alert('Erro:\n' + objxmlreq.status);
                }
            }) //$.ajax
        }
    }

    function gerainfspedfiscal() 
    {
        var idnotafiscal = $("#idnf").val();
        vurl = "inc/php/gerainfspedfiscal.php?idnf=" + idnotafiscal;

        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                alert(data);
                document.location.reload();
            },
            error: function(objxmlreq) {
                alert('Erro:\n' + objxmlreq.status);
            }
        }) //$.ajax
    }

    function devolvernf() 
	{
        $.ajax({
            type: "get",
            url: "ajax/htmldevolvenf.php",
            data: {
                idnf: $("[name=_1_u_nf_idnf]").val()
            },
            success: function(data) {
                CB.modal({
                    titulo: "</strong>Devolver Nota <button type='button' class='btn btn-danger btn-xs' onclick='salvardevolucao();'><i class='fa fa-circle'></i>Salvar</button></strong>",
                    corpo: data,
                    classe: 'sessenta'
                });
            },
            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);
            }
        }) //$.ajax		

    }

    function salvardevolucao() 
    {
        var vstr = '';
        var virg = '';
        $("#itensdev").find('input:checked').each(function(i, o) {
            vstr += virg;
            vstr += $(o).attr("idnfitemxml");
            virg = ',';
        });

        console.log(vstr);
        if ($("[name=_dev_i_nf_idnatop]").val() == '' || vstr == '') {
            alert("É necessário marcar um dos itens e selecionar a natureza da operação.");
        } else {

            var strcbpost = "nf_idcontato=" + $("[name=_dev_i_nf_idcontato]").val() + "&nf_idnatop=" + $("[name=_dev_i_nf_idnatop]").val() + "&idnfitemxml=" + vstr + "&_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val();
            console.log(strcbpost);
            CB.post({
                objetos: strcbpost,
                parcial: true,
                posPost: function(data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader("X-CB-PKID") &&
                        jqXHR.getResponseHeader("X-CB-PKFLD") == "idnf") {
                        janelamodal("?_modulo=pedido&_acao=u&idnf=" + jqXHR.getResponseHeader("X-CB-PKID"));
                    } else {
                        alert("js: gerarDevolução: A resposta de inserção não retornou a coluna `idnf` ou Autoincremento.");
                    }
                }
            });
        }
    }

    function getnfe() 
	{
        vurl = "../inc/nfe/sefaz4/func/getnfe.php?idnotafiscal=<?=$_1_u_nf_idnf ?>";
        if (confirm("Baixar o XML da NF?")) 
		{
            $.ajax({
                type: "get",
                url: vurl,
                success: function(data) {
                    alert(data);
                    document.location.reload();
                },
                error: function(objxmlreq) {
                    alert('Erro:\n' + objxmlreq.status);
                }
            }) //$.ajax
        }
    }


    function salvachavecte(vthis){
       
       CB.post({
               objetos: "_1_u_nf_idnf="+<?=$_1_u_nf_idnf ?>+"&_1_u_nf_idnfe="+$(vthis).val(),
               parcial: true,
               posPost: function() {
                vinculacte();
               }
           });
       }
    
    function vinculacte() 
	{
        var idnotafiscal = $("#idnf").val();
        vurl = "ajax/vinculacte.php?idnf=" + idnotafiscal;

        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                alert(data);
                document.location.reload();
            },
            error: function(objxmlreq) {
                alert('Erro:\n' + objxmlreq.status);
            }
        }) //$.ajax
    }

    function gerainfcte() 
	{
        var idnotafiscal = $("#idnf").val();
        vurl = "inc/php/gerainfcte.php?idnf=" + idnotafiscal;

        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                alert(data);
                document.location.reload();
            },
            error: function(objxmlreq) {
                alert('Erro:\n' + objxmlreq.status);
            }
        }) //$.ajax
    }

    //Exclui o XML, caso seja de outra Nota Fiscal - Lidiane (19/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=327401
    function excluirxml(idnf) 
	{
        if (confirm("Deseja realmente excluir o XML?")) 
		{
            CB.post({
                objetos: '_retxml_u_nf_idnf=' + idnf + '&_retxml_u_nf_xmlret=&_retxml_u_nf_envionfe=PENDENTE',
                parcial: true
            });
        }
    }

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
                        <select id="justificativa" name="_h1_i_${tabela}_justificativa" onchange="alteraoutros(this,'${tabela}')" vnulo class="size50">
                            <?=fillselect(NfEntradaController::$_justificativaPrazo)?>
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

    function alteraoutros(vthis, tabela) 
	{
		valor = $(vthis).val();
		if (valor == 'OUTROS') {
           
			$(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_' + tabela + '_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
            $('#justificativa').remove();
        } else {
			$('#justificaticaText').remove();
		}
	}

    $('[name="_1_u_nf_prazo"].calendario').on('apply.daterangepicker', function(ev, picker) {
        validaprazo(picker.startDate.format('YYYY-MM-DD'));
    });

    function validaprazo(indate){

        let currentDate = new Date();
        let inputDate = new Date(indate);
        inputDate.setDate(inputDate.getDate() + 1);
        var situacao = 'OK';
        
        let formattedDate = inputDate.getDate().toString().padStart(2, '0') + '/' +
                        (inputDate.getMonth() + 1).toString().padStart(2, '0') + '/' +
                        inputDate.getFullYear();                     
        
        let permitido = (currentDate.getMonth() + 1).toString().padStart(2, '0') + '/' +
                        currentDate.getFullYear();    

        // Verifica se a data de entrada está no mês corrente ou anterior
        if (inputDate.getFullYear() === currentDate.getFullYear()) {
            if (inputDate.getMonth() === currentDate.getMonth()) {
                situacao='OK';
            } else if (inputDate.getMonth() < currentDate.getMonth()) {
                alert("Data "+formattedDate+" menor que o periodo permitido 01/"+permitido+" e 30/"+permitido+".");
                situacao='NOK';
            } else {
                alert("Data "+formattedDate+" maior que o periodo permitido 01/"+permitido+" e 30/"+permitido+".");
                situacao='NOK';
            }
        } else if (inputDate.getFullYear() < currentDate.getFullYear()) {
            alert("Ano "+inputDate.getFullYear()+" fora do ano corrente." );
            situacao='NOK';
        } else {
            alert("Data "+formattedDate+" fora do período permitido 01/"+permitido+" e 30/"+permitido+".");
            situacao='NOK';
        }

    }
	//------- Funções Módulo -------
	
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>
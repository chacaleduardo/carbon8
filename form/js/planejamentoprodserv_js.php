<script>
function novoPlanejamento(inidunidade){
 $('#mais_'+inidunidade).addClass('hide');
 $('#select_'+inidunidade).removeClass('hide');
}

function inserirPlanejamento(vthis,idprodserv,idunidade){
    
    var exercicio = $(vthis).val();
    form = '';
    var idprodservformula = $(vthis).attr('idprodservformula');
    
    let str='';

    for (let i = 1; i < 13; i++) {
    if(idprodservformula)
        form = '&_'+i+'_i_planejamentoprodserv_idprodservformula='+idprodservformula;

    str +='&_'+i+'_i_planejamentoprodserv_idprodserv='+idprodserv+'&_'+i+'_i_planejamentoprodserv_idunidade='+idunidade+'&_'+i+'_i_planejamentoprodserv_exercicio='+exercicio+'&_'+i+'_i_planejamentoprodserv_mes='+i+form;
    }

    CB.post({
            objetos: str,
            parcial: true,
        });
            
}

function atualizaad(vthis,vclass){
    debugger;
    $("."+vclass).val($(vthis).val());
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
                        <input name="_h1_i_${tabela}_tipoobjeto" value="planejamentoprodserv" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                        <input name="_h1_i_${tabela}_valor" value="${valor}" class="size10 " type="text">
                    </td>
                </tr>
                <tr>
                    <td>Justificativa:</td>
                    <td>
                        <select id="justificativa" name="_h1_i_${tabela}_justificativa" onchange="alteraoutros(this,'${tabela}')" vnulo class="size50">
                            <?=fillselect(PlanejamentoProdServController::$_justificativa)?>
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

    $(".historicoEnvio").webuiPopover({
        trigger: "click",
        placement: "right",
        width: 500,
        delay: {
            show: 300,
            hide: 0
        }
    });

    function vinculaPlanementoAFormula(vthis){
        $(vthis).parent().siblings('table').find("[name*=u_planejamentoprodserv_idprodservformula]").each((i,e)=>{
            $(e).val($(vthis).val())
        })
        CB.post()
    }

</script>
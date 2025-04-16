<?


if($_1_u_amostra_status=="FECHADO" && $_GET["_pagereadonly"]!="N" && getModsUsr("MODULOS")[$_GET['_modulo']]["permissao"] == 'w'){
	$_pagereadonly=false;
	$escondejquery = false;
	
}else if (getModsUsr("MODULOS")[$_GET['_modulo']]["permissao"] == 'r'){
	$_pagereadonly=true;
	$escondejquery = true;
}else{
	$_pagereadonly=false;
	$escondejquery = false;
}

if(strpos($_GET['_modulo'], 'cqd') == true){
	$modulo = 'nucleocqd';
} elseif (strpos($_GET['_modulo'],'pesqdes') == true) {
	$modulo = 'nucleopesqdes';
}else{
	$modulo = 'nucleo';
}
require_once (__DIR__.'/../../inc/php/readonly.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>	
ModuloNuc = '<?=$modulo?>';
jsonInputsTipoamostra = <?=jsonInputsTipoamostra()?>;
jsonAmostraCampos = <?=jsonObgTipoamostra()?>;
jsonTipoSubtipo = <?=jsonTipoSubtipo()?>;
jsonClientes = <?=jsonClientes()?>;
jsonDetClientes = <?=$jsonDetClientes?>;
jsonServicos = <?=jsonServicos()?>;
jsonEspecieFinalidade = <?=jsonEspecieFinalidade()?>;
jsonTelaamostraconf = <?=$jsonTelaamostraconf?>;
jsonamostracamposid = <?=$jsonamostracampos?>;
jsonamostracamposobgtra = <?=$jsonamostracamposobgtra?>;
dataAmostra = '<?= $dataAmostra ?>';
$_vids = '<?=$_vids?>';
idPessoaLogada = '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>';
amostraOficial=($("[name='_1_"+CB.acao+"_amostra_idsecretaria']").val() == "")?false:true;
dtUltimaAmostra = '<?=$dtUltimaAmostra?>';
booAtualizaNucleo = false;
unidadePadrao = <?=$unidadepadrao?>;
idempresaAmostra = <?=cb::idempresa()?>;
$escondejquery = <?=!$escondejquery?'false':'true'?>
vIdTra='<?=$arrTRAAssociado["idtra"]?>';
impressoraMod = '<?=$impressora?>';
$(`.btnqtdteste`).hide();

<?if($_1_u_amostra_status=="FECHADO" && $_GET["_pagereadonly"]!="N" && getModsUsr("MODULOS")[$_GET['_modulo']]["permissao"] == 'w'){
	echo "confInputs();";
	
}else if (getModsUsr("MODULOS")[$_GET['_modulo']]["permissao"] == 'r'){
	echo "confInputs(1);";
}?>

function botaoEditarAmostra(){
	$(`.btnqtdteste`).show()
	$bteditar = $("#editarAmostra");
	if($bteditar.length==0){
		CB.novoBotaoUsuario({
			id:"editarAmostra"
			,rotulo:"Editar Amostra"
			,icone:"fa fa-pencil"
			,onclick:function(){
				$("#cbModalTitulo").html("EDIÇÃO DE AMOSTRA");
				$("#cbModalCorpo").html(
							"<p>Os resultados vinculados à esta amostra serão <b>revisados</b> e será novamente solicitada assinatura do mesmo. </p>"+
							"<div class='panel panel-default'>"+
							"<div class='panel-body'>"+
							"<div class='row'>"+
							"<div class='col-md-12'>"+
							"</div>"+
							"<div class='col-md-12'>"+
							"<label>Motivo</label>"+
							"	<select name='_100_u_amostra_edicaomotivo' id='_100_u_amostra_edicaomotivo' title='' tabindex='-99'>"+
							"		<option value='Alteração (Erro Cliente)'>Alteração (Erro Cliente)</option>"+	
							"		<option value='Correção (Erro Laudo)'>Correção (Erro Laudo)</option>"+
							"	</select>"+
							"</div>"+
							"<div class='col-md-12'>"+
							"<label>Campos Alterados</label>"+
							"	<input type='text' id='_100_u_amostra_edicaoobs' name='_100_u_amostra_edicaoobs' value=''>"+
							"</div>"+
							"<div class='col-md-12'>"+
							"<button type='button' id='btnSubmitModal' class='btn btn-danger' name='reabrir' value='Reabrir'>Reabrir</button>"+
							"</div>"+
							"</div>"+
							"</div>"+
							"</div>");
			
				$( "#btnSubmitModal" ).click(function() {
					var _100_u_amostra_edicaomotivo = $("#_100_u_amostra_edicaomotivo").val();
					var _100_u_amostra_edicaoobs = $("#_100_u_amostra_edicaoobs").val();
					var idamostra = $("[name='_1_u_amostra_idamostra']").attr("value");
					if (_100_u_amostra_edicaoobs.trim().length >= 5) {
						CB.post({
							objetos: {
								"_x_u_amostra_idamostra": idamostra
								,"_x_u_amostra_status" : "ABERTO"
								,"_fluxostatushistobs_motivo_": _100_u_amostra_edicaomotivo
								,"_fluxostatushistobs_motivoobs_": _100_u_amostra_edicaoobs
								}
							,parcial:true
							,refresh: "refresh"
						});
						$("#cbModalCorpo").html('');
						$("#cbModal").modal("hide");
						$("#editarAmostra").hide();
					}else{
						alertAtencao("O motivo deve conter ao menos 5 caracteres!");
					}
				});

				$("#cbModal").modal("show");
			}
		});
	}
}
function alteraqtdteste(vthis,idresultado){
	qtd = $(vthis).siblings("input").val()
	if(qtd != ""){
		post = `_1_u_resultado_idresultado=${idresultado}&_1_u_resultado_quantidade=${qtd}`
		CB.post({
			objetos: post,
			parcial:true
		})
	}else{
		alert("Quantidade não pode ser vazia!");
	}
}
<?if($_1_u_amostra_idamostra){
	if(AmostraController::contarResultadosAssinados($_1_u_amostra_idamostra) >= 1) {
	echo "botaoEditarAmostra();";
	}
}?>

function setNameDadosAmostra(acao,vthis){
	let valObj=vthis.value;
	if(valObj != ''){
		vthis.name='_d1_'+acao+'_dadosamostra_valorobjeto';
		$('#damostraobj').attr('name','_d1_'+acao+'_dadosamostra_objeto');
		$('#didamostra').attr('name','_d1_'+acao+'_dadosamostra_idamostra');
		$('#iddadosamostra').attr('name','_d1_'+acao+'_dadosamostra_iddadosamostra');		
	} else if (valObj == '' &&  acao=='i'){
		vthis.name='';
		$('#damostraobj').removeAttr('name');
		$('#didamostra').removeAttr('name');
		$('#iddadosamostra').removeAttr('name');
	}
}	

//Verifica se a data de recebimento da amostra é maior que a data da coleta.
	$('#dtchegada').on('apply.daterangepicker', function(ev, change) {
		dataAmostra=dataAmostra.getTime();


		let dataChegada= change.startDate.format('YYYY-MM-DD');
		dataChegada= new Date(dataChegada);	
		dataChegada=dataChegada.getTime();

		if (dataChegada > dataAmostra) {
			alert('A data de chegada da amostra não pode ser maior que a data de registro da amostra');
			morre();
		}
	}).on("change", function (ev,change) {
		dataAmostra=dataAmostra.getTime();


		let dataChegada= $(ev.delegateTarget).val().split('/');

		dataChegada= new Date(dataChegada[2]+'-'+dataChegada[1]+'-'+dataChegada[0]);	
		dataChegada=dataChegada.getTime();

		if (dataChegada > dataAmostra) {
			alert('A data de chegada da amostra não pode ser maior que a data de registro da amostra');
			morre();
		}
	});


function confInputs(inv){ 
	//debugger
	if($(":input[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").val()==""){
		//return false;
	}

	vIdSubtipoAmostra = $("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()||$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").attr("value");

	if(!vIdSubtipoAmostra ){
		console.warn("tipoamostra não informado");
		return false;
	}

	//Nenhuma coluna configurada para o Tipo de Amostra selecionado. Isto gera erro ao tentar recuperar configuração
	if(jsonAmostraCampos[unidadePadrao]===undefined||jsonAmostraCampos[unidadePadrao][vIdSubtipoAmostra]===undefined){
		alertAtencao("Nenhum campo configurado para o tipo/subtipo de amostra selecionado!\nUtilize a opção <i class='fa fa-eye'></i>");
		if(!jsonAmostraCampos[unidadePadrao][vIdSubtipoAmostra])return false;
	}

	$(".rowDin [id*=lb_], .rowDin [id*=col_]").hide().each(function(k,v){
		oTd = $(v);
		oTdName = oTd.attr("name");
		oTdId = oTd.attr("id");
		sColuna=(oTdId&&oTdId.indexOf("col_")>=0)?oTdId.replace("col_",""):"";

		if(sColuna!="" && sColuna!="nucleoamostra" && sColuna!=undefined  && vIdSubtipoAmostra!=""){
			//Recuperar o input relacionado
			if(sColuna=="valorobjeto"){
						
				oInput = oTd.children("[id=col_valorobjeto]");

				if(oInput.length==0){
					oInput = oTd.children("[name=_d1_i_dadosamostra_"+sColuna+"]");
				}
			}else{
				oInput = oTd.children("[name=_1_"+CB.acao+"_amostra_"+sColuna+"]");
			}
			try{$obrigatorio = jsonamostracamposobgtra[sColuna][vIdSubtipoAmostra]['obrigatorio']}catch(e){$obrigatorio = false;}
			if ($obrigatorio == 'Y') {
				$(oInput).attr("vnulo",'')
			}
			$(oInput).attr("disabled",'disabled')


			//Se a coluna estiver configurada, mostrar
			try{
				$show = jsonAmostraCampos[unidadePadrao][vIdSubtipoAmostra][sColuna];
			}catch(e){
				$show = false;
			}

			if ($show) {
				$(".rowDin div[id=lb_"+sColuna+"], .rowDin div[id=col_"+sColuna+"]").show();
				$(oInput).removeAttr("disabled")

				if(sColuna == "idnucleo"){
					$(".rowDin div[id=lb_nucleoamostra], .rowDin div[id=col_nucleoamostra]").show();
					$("[name=_1_"+CB.acao+"_amostra_nucleoamostra").removeAttr("disabled")
				}

				if(sColuna=="valorobjeto"){
					$(oInput).removeAttr("style")		
				}
			}

		}
	});

	$(".rowDin").removeClass("hidden");
	if (inv == 1){
		$("#limparnucleo").addClass("hidden");
		$("#editarnucleo").addClass("hidden");
	}
}

//LTM - 01-10-2020: Modal para mostrar as impressoras de acordo com cada tipo de Amostra.
	function showModal(vInvalor){
		$oModal = $(`
			<div id="modaletiqueta">
				<div class="row" id="imp_content_row">
					<div class="col-md-12">
						<table style="width:100%;" id="imp_tabela">
							<table style="width:100%;" id="imp_tipos">
								<tr onclick="janelamodal('report/tra.php?idamostra=${$("[name='_1_"+CB.acao+"_amostra_idamostra']").val()}&unidadepadrao=9')" style="cursor: pointer;border: 1px dotted #e4dada;border-radius: 80px;">
									<td style="padding: 10px 10px 10px 4px;">
										<a title="Imprimir TRA" class="fa fa-print pull-right fa-lg cinza hoverazul"></a>
									</td>
									<td>Imprimir TRA</td>
								</tr>
								<tr onclick="janelamodal('report/traresultados.php?idamostratra=${$("[name='_1_"+CB.acao+"_amostra_idamostra']").val()}&unidadepadrao=9')" style="cursor: pointer;border: 1px dotted #e4dada;border-radius: 80px;">
									<td style="padding: 10px 10px 10px 4px;">
										<i title="Resumo Diagnóstico" class="fa fa-file pull-right  cinza hoverazul" ></i>
									</td>
									<td>Resumo Diagnóstico</td>
								</tr>
								<tr onclick="janelamodal('report/emissaoresultado.php?_vids=${$_vids}')" style="cursor: pointer;border: 1px dotted #e4dada;border-radius: 80px;">
									<td style="padding: 10px 10px 10px 4px;">
										<a title="Imprimir LDA" class="fa fa-print pointer pull-right fa-lg cinza hoverazul"></a>	
									</td>
									<td>Imprimir LDA</td>
								</tr>
								<tr onclick="janelamodal('report/impamostra.php?idamostra=${$("[name='_1_"+CB.acao+"_amostra_idamostra']").val()}')" style="cursor: pointer;border: 1px dotted #e4dada;border-radius: 80px;">
									<td style="padding: 10px 10px 10px 4px;">
										<a title="Imprimir Amostra" class="fa fa-print pointer pull-right fa-lg cinza hoverazul" ></a>
									</td>
									<td>Imprimir Amostra</td>
								</tr>
								<tr onclick="janelamodal('report/tra.php?idamostra=${$("[name='_1_"+CB.acao+"_amostra_idamostra']").val()}&unidadepadrao=9&provisorio=Y')" style="cursor: pointer;border: 1px dotted #e4dada;border-radius: 80px;">
									<td style="padding: 10px 10px 10px 4px;">
										<a title="Imprimir TEA" class="fa fa-print pull-right pointer fa-lg cinza hoverazul" ></a>
									</td>
									<td>Imprimir TEA</td>
								</tr>
							</table>
						</table>
					</div>
					<div class="col-md-12" id="imp_tabela_qtd_imprimir">
						<table style="width:100%;" id="imp_qtd_imprimir"></table>
					</div>
				</div>
			</div>
		`);
			
		CB.modal({
			titulo: "</strong>Escolha um relatório</strong>",
			corpo: [$oModal],
			classe: 'vinte',
		});
	}

if($escondejquery == false) {
	if($("[name='_1_"+CB.acao+"_amostra_idamostra']") != ''){
		if(CB.acao != 'i'){
			$("#anexos").dropzone({
				idObjeto: $("[name=_1_u_amostra_idamostra]").val()
				,tipoObjeto: 'amostra'
				,tipoArquivo: 'AMOSTRA'
				,idPessoaLogada: idPessoaLogada
			});
		} else {
			$("#anexos").addClass("hidden");
		}
	}

	//LTM - 22-09-2020 - 374022: Função para cancelar a Amostra Provisória
	function cancelarAmostra(inIdlote){
		if(confirm("Deseja realmente cancelar a Amostra?")){
			CB.post({
				"objetos":"_x_u_amostra_idamostra="+inIdlote+"&_x_u_amostra_status=CANCELADO"
				,parcial:true
			});
		}
	}


	//LTM - 14-09-2020 - 373062: Ação inserida para não mostrar o campo Oficial quando for insert, pois não tem o cliente selecionado.
	//Voltei para aparecer a secretaria mesmo quando for insert. Foi solicitado que apareça apenas uma mensagem de alerta
	if(CB.acao == 'i'){
		//$("#oficialInsert").hide();
	} else {
		//$("#oficialInsert").show();
		$("body").removeClass("novoprovisorio");
	}

	/*
	* Mostra ou esconde os inputs conforme configuração
	*/

	function mostraDetalhesCliente(){
		vIdPessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()||"";

		if(vIdPessoa!=""){
			if(jsonDetClientes[vIdPessoa])
				if(jsonDetClientes[vIdPessoa].observacaore && jsonDetClientes[vIdPessoa].observacaore!=""){
					$("#observacaore").html("<div class='alert alert-warning alert-danger' role='alert'> \
						"+jsonDetClientes[vIdPessoa].observacaore.replace(/\r?\n/g,"<br>")+"</div>").show();
					$("#lbcnpj").html(jsonDetClientes[vIdPessoa].cpfcnpj);

				}else{
					$("#observacaore").hide();
				}
		}
	}

	function mostracamporesadd(){
		//debugger;
		vIdPessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()||"";
		if(vIdPessoa!=""){
			if(jsonDetClientes[vIdPessoa])
				if(jsonDetClientes[vIdPessoa].pedidocp && jsonDetClientes[vIdPessoa].pedidocp==="Y"){
						$(".namenpedido").attr('type', 'text');
						$(".namenpedido").removeAttr("disabled");

				}else{
						$(".namenpedido").attr('type', 'hidden');
						$(".namenpedido").attr("disabled","disabled");
				}
		}
	}

	/*
	* Novo teste [ctrl]+[+]
	*/
	$(document).keydown(function(event) {

		if (!((event.ctrlKey || event.altKey) && event.keyCode == 80)) return true;

		if(CB.acao=="i"){
			alert("Salve a amostra primeiro");
		}

		if(!teclaLiberada(e)) return;//Evitar repetição do comando abaixo

		idamostra = $("[name=_1_u_amostra_idamostra]").val();
		imprimeTestes(idamostra, impressoraMod);
		//showModalEtiqueta(idamostra);

		return false;

	});

	function novaamostra(vmodulo){
		//Recuperar o formulário em modo de update (sem o readonly) para posteriormente transformar os inputs em uma nova amostra
		CB.loadUrl({
			urldestino: CB.urlDestino+"?_modulo=amostra&_acao=u&_pagereadonly=N&duplicaramostra=Y&idamostra="+$("[name=_1_u_amostra_idamostra]").attr("value")
			,render: function(data){
				//Ao invés de mostrar os objetos no formulário, tratar antes:
				duplicarAmostra(data,vmodulo);
			}
		})
	}

	/*
	* Duplicar amostra [ctrl]+[d]
	*/
	$(document).keydown(function(event) {

		if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

		if(!teclaLiberada(event)) return;//Evitar repetição do comando abaixo

		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa="+getUrlParameter("_idempresa") : '';
		//Recuperar o formulário em modo de update (sem o readonly) para posteriormente transformar os inputs em uma nova amostra
		CB.loadUrl({
			urldestino: CB.urlDestino+"?_modulo=amostra&_acao=u"+idempresa+"&_pagereadonly=N&duplicaramostra=Y&idamostra="+$("[name=_1_u_amostra_idamostra]").attr("value")
			,render: function(data){
				//Ao invés de mostrar os objetos no formulário, tratar antes:
				duplicarAmostra(data,CB.modulo);
			}
		})

		return false;
	});

	function duplicarAmostra(data,vmodulo){

		//Inicializa um objeto jquery com todo o conteúdo que retornou do servidor
		$data = $(data);

		//Verifica se o formulário está visível, para evitar o trigger em outras condições
		if(CB.acao=="u" && CB.oModuloForm.is(":visible")){
			var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa="+getUrlParameter("_idempresa") : '';
			//Ajusta os parâmetros do carbon para simular um clique no botão de "novo"
			lSearch="_modulo="+vmodulo+""+idempresa+"&_acao=i";
			CB.locationSearch=lSearch;
			window.history.pushState(null, window.document.title, "?"+lSearch);

			//Recupera todos os campos de input
			$data.find(":input").each(function(k,obj){
				$obj = $(obj);
				$oNome = $obj.attr("name");
				//console.log("name:"+$oNome);
				//Inputs de Dados Amostra
				if(($oNome) && $oNome.indexOf("_d1_u_dadosamostra")==0){
					$obj.attr("name",$oNome.replace("_d1_u_dadosamostra","_d1_i_dadosamostra"));
					$obj.removeAttr("onkeyup");
					if($oNome.match(/^.*idamostra$/)){//Reset
						$obj.val("");
					}else if($oNome.match(/^.*exercicio$/)){//Reset
						$obj.val("");
					}else if($oNome.match(/^.*dataamostra$/)){//Data: hoje
						$obj.val(dtUltimaAmostra);
					}else if($oNome.match(/^.*idunidade/)){//Data: hoje
						if(vmodulo=='amostraautogenas'){
							$obj.val(6);
						}else if(vmodulo=='amostratra'){
							$obj.val(9);
						}
					}
				//inputs de Amostra
				}else if(($oNome) && $oNome.indexOf("_1_u_amostra")==0){
					$obj.attr("name",$oNome.replace("_1_u_amostra","_1_i_amostra"));
					if($oNome.match(/^.*idamostra$/)){//Reset
						$obj.val("");
					}else if($oNome.match(/^.*exercicio$/)){//Reset
						$obj.val("");
					}else if($oNome.match(/^.*dataamostra$/)){//Data: hoje
						$obj.val(dtUltimaAmostra);
					}else if($oNome.match(/^.*idunidade/)){//Data: hoje
						if(vmodulo=='amostraautogenas'){
							$obj.val(6);
						}else if(vmodulo=='amostratra'){
							$obj.val(9);
						}
					}
				//inputs de resultado
				}else if(($oNome) && $oNome.indexOf("_u_resultado")>0){
					//Quebra o nome capturando 2 grupos: numero da linha e nome do campo, excluindo-se o campo idresultado
					if($oNome.match(/^.*idresultado$/)){//Se é o campo idresultado, resetar
						//$obj.attr("name","");
						$obj.val("");
					}
					//Renomeia o input para o padrão que será aproveitado no saveposchange
					grCap = $oNome.match(/_(\d+)_u_resultado_(.+)/);
					$obj.attr("name","_"+grCap[1]+"#"+grCap[2]);

				}else if(($oNome) && $oNome.indexOf("_u_identificador_")>0){
					//Quebra o nome capturando 2 grupos: numero da linha e nome do campo, excluindo-se o campo idresultado
					if($oNome.match(/^.*ididentificador/)){//Se é o campo idresultado, resetar
						//$obj.attr("name","");
						$obj.val("");
					}
					//Renomeia o input para o padrão que será aproveitado no saveposchange
					grCap = $oNome.match(/_(\d+)_u_identificador_(.+)/);
					$obj.attr("name","_"+grCap[1]+"#"+grCap[2]);

				}
				//Verificar processo de redefinir input names:
				//console.log($obj.attr("name")+"-"+$obj.cbval()+"-"+$obj.val());

				//Remove o idresultado para insert - Lidiane (19/06-2020)
				//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=327494
				$obj.removeAttr("idresultado");
				$obj.removeAttr("readonly");
				$obj.removeAttr("disabled");
			});

			$data.find('#editarCamposVisiveis').remove();

			if(CB.modulo == "amostratra"){
				$data.find('#identificadores').remove();
				$data.find('.indicador').remove();
			}

			//Permite que os testes sejam deletados
			$data.find("#tbTestes tbody tr").addClass("dragExcluir").removeAttr("idresultado").find("i.move.hidden").removeClass("hidden");
			
			//Altera o Cabeçalho e estilo
			$data.find("#cabRegistro").html("Nova amostra");
			CB.acao="i";
			$("body").addClass("novo");
			
			//LTM - 03/092020 - 370913: Caso seja o módulo Provisório ficará com a cor azul para difenciar dos demais.
			if(CB.acao == "i" && CB.modulo=="amostraavesprovisorio"){
				$("body").addClass("novoprovisorio");
			}

			CB.oModuloForm.html("").html($data);
			//debugger
			//Seta o status para Aberto.
			if(CB.modulo == 'amostraavesprovisorio' || (CB.modulo == 'amostratra' && CB.acao == "i"))
			{
				$("[name=_1_i_amostra_status]").attr("value","PROVISORIO");
				
			} else {
				$("[name=_1_i_amostra_status]").attr("value","ABERTO");
			}
				

			CB.removeBotoesUsuario();
			ST.desbloquearCBPost();
			$("#panelEtiquetas").remove();
		}
	}

	//Autocomplete de Clientes
	$(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").autocomplete({
		source: jsonClientes,
		delay: 0,
		select: function(event, ui){
			mostraDetalhesCliente();
			preencheDropNucleos(ui.item.value);
			verificaTestesOficiais();
			mostracamporesadd();
			getResponsavel(ui.item.value);
			//autocompleteResponsavel();
			//autocompleteResponsavelOf();
		},
		create: function( event, ui ) {
			mostraDetalhesCliente();
			mostracamporesadd();
		}
	});

	//Autocomplete de Responsável
	$(":input[name=_1_"+CB.acao+"_amostra_idpessoaresponsavel]").autocomplete({
		source: jsonClientes,
		delay: 0,
		select: function(event, ui){
			getCrmv(ui.item.value);
		}
	});

	//Autocomplete de Tipo e Subtipo de amostra
	$(":input[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").autocomplete({
		source: jsonTipoSubtipo[unidadePadrao],
		delay: 0,
		select: function(event, ui){
			
			confInputs();
			popoverEditarCamposVisiveis();
		}
		,create: function( event, ui ) {
			confInputs();
		}
	});


	//Autocomplete de Servicos (testes)
	function criaAutocompletesTestes(){
		$("#tbTestes .idprodserv").autocomplete({
			source: jsonServicos,
			delay: 0
			,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.label;
			
			return $('<li class="listateste#'+item.value+'">')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
			};
			},select: function(event, ui){
				msg=false;
				var inputs = $('[name*="#idtipoteste"]').not(this);
				$.each(inputs,function(i, val){
					console.log($(val).attr("cbvalue"));
					if ($(val).attr("cbvalue") == ui.item.value) {
						
						msg=true;
					}
					
				})
				if (msg) {
					alertAtencao('Registro já possui este teste solicitado');
						//$(this).attr("cbvalue", "");
						//$(this).val("");
					}
				var id = $(this).attr('name');
				var id_s = id.split('#');
				if (ui.item.ofc == 'Y') {
					$('#tbTestes [name="'+id_s[0]+'#idsecretaria"]').attr('class', 'show');
					$('#tbTestes [name="'+id_s[0]+'#idsecretaria"]').removeAttr('disabled', 'disabled');
				}else{
					$('#tbTestes [name="'+id_s[0]+'#idsecretaria"]').attr('class', 'hide');
					$('#tbTestes [name="'+id_s[0]+'#idsecretaria"]').attr('disabled', 'disabled');
				}
			}
		});
		//Permite ordenação dos elementos
		$("#tbTestes tbody").sortable({
			update: function(event, objUi){
				ordenaTestes();
			}
		});
	}

	function verificaTestesOficiais(){
		$.each($("#tbTestes").find(":input[name*=idsecretaria]"), function(i,o){
			$o=$(o);
			if($o.val() && $o.val().length>0){
				alertAtencao("Teste marcado como Oficial<br>Salve a amostra para alterar.");
				$o.val("");
			}
		});
	}

	function configuraOficial(){
		$aTOficial = $("#aToggleOficial");
		$idsec = $(":input[name=_1_"+CB.acao+"_amostra_idsecretaria]");
		$idpessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]");
		$infoSecretaria = $("#infoSecretaria");

		if(amostraOficial){
			$aTOficial.removeClass("hoverlaranja")
					.addClass("laranja")
					.attr("title","Amostra Oficial: "+jsonDetClientes[$idpessoa.cbval()].secretaria);
			//$infoSecretaria.html("Secretaria: "+jsonDetClientes[$idpessoa.cbval()].secretaria).show();
			$('.idsecretaria').show();
		}else{
			$aTOficial.removeClass("laranja")
					.addClass("hoverlaranja")
					.attr("title","Marcar Amostra como Oficial");
					//$infoSecretaria.html("").hide();
					$('.idsecretaria').hide();
		}

		
		$(".idsecretaria").each(function(){
			if($(this).val().length > 0){
			
				$(this).show()
			}
		});
	}


	//Configura a amostra como oficial, para marcar testes oficiais ou não
	function toggleOficial(){
		$aTOficial = $("#aToggleOficial");
		$idsec = $(":input[name=_1_"+CB.acao+"_amostra_idsecretaria]");
		$idpessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]");

		//Caso o cliente não possua informação de secretaria ou já estiver marcado, retirar o valor do campo idsecretaria
		if(!jsonDetClientes[$idpessoa.cbval()] || jsonDetClientes[$idpessoa.cbval()].idsecretaria==null || $idsec.val()!=""){
			$idsec.val("");
			amostraOficial=false;
			configuraOficial();
		}else{
			$idsec.val(jsonDetClientes[$idpessoa.cbval()].idsecretaria);
			amostraOficial=true;
			configuraOficial();
		}
		console.log("AmostraOficial: "+$idsec.val());
		CB.post();
	}


	//Autocomplete de Espécie/Finalidade
	$(":input[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").autocomplete({
		source: jQuery.map(jsonEspecieFinalidade, function(item, id) {
					return {"label": item.especie+" - "+item.tipoespecie + "/" + item.finalidade, value:id, "especie":item.especie, "tipoespecie":item.tipoespecie, "finalidade":item.finalidade}
				})
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				
				lbItem = "<span class=cinzaclaro>"+item.especie+" - </span>"+item.tipoespecie + " / " + item.finalidade;
				
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
			,select: function(event,ui){
				preencherotulo(ui.item.value);
			}
		,delay: 0
	});

	function preencherotulo(inidespecie){
	//alert(jsonEspecieFinalidade[inidespecie]['rotulo']);
	$("#rotulonucleotipo").val(jsonEspecieFinalidade[inidespecie]['rotulo']);

	}

	function ordenaTestes(){
		$.each($("#tbTestes tbody").find("tr"), function(i,otr){
			//Recupera objetos de update e de insert
			$(this).find(":input[name*=resultado_ord],:input[name*=ord]").val(i);
		})
	}

	$("#excluirTeste").droppable({
		accept: ".dragExcluir"
		,drop: function( event, ui ) {
			//verifica se existe o idresultado em mode de update. caso positivo, alternar para excluir
			$idres = $(ui.draggable).attr("idresultado");
			if(parseInt($idres) && CB.acao!=="i"){
				if(confirm("Deseja realmente excluir o teste selecionado?")){
					ui.draggable.remove();
					CB.post({
						"objetos":{
							"_x_u_resultado_idresultado":$idres,
							"_x_u_resultado_status":"CANCELADO",
							"_x_u_resultado_idfluxostatus":2445,
						}
						,parcial:true
					});
				}
			}else{
				if($(ui.draggable).find(":input[name*=#idresultado]").length==1){//Modo de inclusão
					ui.draggable.remove();
				}
			}
			setTimeout(function(){ atualizaQuantTestes(); }, 100);//Deve ser colocado timeout para postergar atualização de contagem de testes
		}
	});

	/*
	* Criar novo objeto html de tipo de teste
	*/
	function novoTeste(objTeste){
		status=$("[name='_1_"+CB.acao+"_amostra_status']").val();
		//JLAL - 12-08-20 **Remoção da validação que verifica se o status é diferente de aberto e se é diferente de provisorio**

		oTbTestes = $("#tbTestes tbody");
		iNovoTeste = (oTbTestes.find("input.idprodserv").length + 11);
		htmlTrModelo = $("#modeloNovoTeste").html();


		htmlTrModelo = htmlTrModelo.replace("#nameidresultado", "_"+iNovoTeste+"#idresultado");
		htmlTrModelo = htmlTrModelo.replace("#nameloteetiqueta", "_"+iNovoTeste+"#loteetiqueta");
		htmlTrModelo = htmlTrModelo.replace("#nameord", "_"+iNovoTeste+"#ord");
		//Inserir Y se caso for Novo Teste - Lidiane (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328394
		htmlTrModelo = htmlTrModelo.replace("#namecobrar", "_"+iNovoTeste+"#cobrar"); 
		htmlTrModelo = htmlTrModelo.replace("#nameidtipoteste", "_"+iNovoTeste+"#idtipoteste");
		htmlTrModelo = htmlTrModelo.replace("#namenpedido", "_"+iNovoTeste+"#npedido");
		htmlTrModelo = htmlTrModelo.replace("#namequantidade", "_"+iNovoTeste+"#quantidade");
		htmlTrModelo = htmlTrModelo.replace("#nameidsecretaria", "_"+iNovoTeste+"#idsecretaria");
		htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoTeste);

		novoTr = "<tr class='dragExcluir'>"+htmlTrModelo+"</tr>";
		oTbTestes.append(novoTr);
		criaAutocompletesTestes();
		atualizaQuantTestes();
	}

	/*
	* Cria lotes para envio de etiquetas para a impressora térmica
	*/
	function alteraLoteEtiqueta(inRow){
		$oLote = $("[name=_"+inRow+"_u_resultado_loteetiqueta]");
		if($oLote.length===0) $oLote = $("[name=_"+inRow+"#loteetiqueta]");

		$iLote = $oLote.val()||0;
		$iLote++;
		if(arrCoresLoteEtiquetas[$iLote]){
			$("#cbimp"+inRow).css("color",arrCoresLoteEtiquetas[$iLote]).attr("title","Lote "+$iLote);
			console.log(arrCoresLoteEtiquetas[$iLote]);
		}else{
			$iLote=0;
			$("#cbimp"+inRow).css("color","").attr("title","Alterar Lote de Impressão");
		}
		$oLote.val($iLote);
		CB.lotesImpressaoAlterado=true;
	}

	/*
	* Recuperar os núcleos do client
	*/
	function preencheDropNucleos(item_idpessoa = null){

		vIdPessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval();
		//vNucleoSelecionado = $(":input[name=_1_"+CB.acao+"_amostra_idnucleo]").val();
		if(vIdPessoa){

			$.ajax({
				type: "get",
				url : "ajax/nucleosCliente.php?idpessoa="+vIdPessoa+"&idunidade="+unidadePadrao,
				success: function(data){
					console.log(data);
					//Transforma a string json em objeto
					jsonNuc =jsonStr2Object(data);
					if(jsonNuc){

						$oIdnucleo = $("[name=_1_"+CB.acao+"_amostra_idnucleo]");

						//Nova versão de núcleo: autocomplete
						jsonAc = jQuery.map( jsonNuc, function(n, id) {
							return {"label": n.nucleo, value:id ,"lote":n.lote}
						});
						
						// GVT - 02/07/2020 - Adicionado a condição para que apague o conteúdo do núcleo quando trocar de cliente
						if(item_idpessoa){
							$("[name=_1_"+CB.acao+"_amostra_nucleoamostra]").attr("value","");
							$oIdnucleo.attr("value","");
							$oIdnucleo.attr("cbvalue","");
						}

						$oIdnucleo.autocomplete({
							source: jsonAc
							,delay: 0
							,select: function(){
								console.log($(this).cbval());
								preencheInputsNucleoAmostra($(this).cbval());
							}
							,create: function(){
								$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
									vnucleo = "<span class='cinzaclaro'>Núcleo: </span>" + item.label;
									vnucleo = (item.lote)?vnucleo+" <span class=cinzaclaro> - Lote: </span>" + item.lote:vnucleo;
									return $('<li>')
										.append('<a>' + vnucleo + '</a>')
										.appendTo(ul);
								};
							}
							,noMatch: function(objAc){
								console.log("Executei callback");
								CB.post({
									objetos: "_x_i_nucleo_idunidade="+unidadePadrao+"&_x_i_nucleo_situacao=ATIVO&_x_i_nucleo_nucleo="+objAc.term+"&_x_i_nucleo_idpessoa="+$(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()
									,parcial:true
									,refresh: false
									,msgSalvo: "Núcleo criado"
									,posPost: function(data, textStatus, jqXHR){
										//Atualiza source json
										$oIdnucleo.data('uiAutocomplete').options.source.push({
											label: $oIdnucleo.val()
											,value: CB.lastInsertId
										});
										//Atualiza o objeto DATA associado ao input
										$oIdnucleo.data("nucleos")[CB.lastInsertId]={"nucleo":$oIdnucleo.val()};
										//Mostra a nova opção
										$oIdnucleo.autocomplete( "search", $oIdnucleo.val());
										//Informa visualmente o usuário para que complete as informações do núcleo
										getInputsNucleoAmostra().addClass("aguardandoCbpost");
										//Mostra campos adicionais do Núcleo
										$("#col_tipoaves, #col_finalidade").show()
										//Possibilita atualização do núcleo no prePost
										booAtualizaNucleo=true;
									}
								});
							}
						})
						//Uma propriedade com o json é adicionada ao objeto para tornar possível a consulta posterior
						.data("nucleos",jsonNuc);

						//if($valIdnucleo) $oIdnucleo.val($valIdnucleo);
						//if(vNucleoSelecionado) $oIdnucleo.val(vNucleoSelecionado);//Caso de "duplicar amostra" devolver valor à drop

					}else{
						alertErro("Javascript: preencheDropNucleos(): Erro ao recuperar json de nucleos.\nVerificar Console de erros javascript.");
					}
				}
			});

		}else{
			console.warn("js: preencheDropNucleos: Erro: idIdpessoa não informado;")
		}
	}

	/*
	* LTM (23/04/2021): Recuperar o Responsável de acordo com o cliente selecionado
	*/
	function getResponsavel(idpessoa = null)
	{
		vIdPessoa = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval();
		if(vIdPessoa)
		{
			$.ajax({
				type: "get",
				url : "ajax/getresponsavel.php?idpessoa="+vIdPessoa+"&tipo=responsavel",
				success: function(data)
				{
					//Transforma a string json em objeto
					jsonNuc =jsonStr2Object(data);
					if(jsonNuc)
					{
						$oIdPessoaResponsavel = $("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavel]");
						$oIdPessoaResponsavelof = $("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelof]");

						//Nova versão de núcleo: autocomplete
						jsonAc = jQuery.map( jsonNuc, function(n, id) {
							return {"label": n.nome, value:n.idcontato}
						});
						
						if(idpessoa)
						{
							$("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavel]").attr("value","");
							$("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelof]").attr("value","");
							$oIdPessoaResponsavel.attr("value","");
							$oIdPessoaResponsavel.attr("cbvalue","");

							$oIdPessoaResponsavelof.attr("value","");
							$oIdPessoaResponsavelof.attr("cbvalue","");
						}

						$oIdPessoaResponsavel.autocomplete({
							source: jsonAc,
							delay: 0,
							select: function(event, ui){
								
								var celular = jsonNuc[ui.item.label] && jsonNuc[ui.item.label].tel2 || "";
								var telefone = jsonNuc[ui.item.label] && jsonNuc[ui.item.label].tel1 || "";

								if(celular !== "-"){
									$("[name=_1_"+CB.acao+"_amostra_responsavelcolcont]").attr("value", celular);
								} else {
									$("[name=_1_"+CB.acao+"_amostra_responsavelcolcont]").attr("value", telefone);
								}

								$("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelcrmv]").attr("value", "");
								$("[name=_1_"+CB.acao+"_amostra_responsavelcolcrmv]").attr("value", "");
								getCrmv(ui.item.value, 'idpessoaresponsavelcrmv');								

							},
							create: function(){
								$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
									return $('<li>')
										.append('<a>' + item.label + '</a>')
										.appendTo(ul);
								};
							}
						}).data("idpessoaresponsavel",jsonNuc);

						$oIdPessoaResponsavelof.autocomplete({
							source: jsonAc,
							delay: 0,
							select: function(event, ui){

								var celular = jsonNuc[ui.item.label] && jsonNuc[ui.item.label].tel2 || "";
								var telefone = jsonNuc[ui.item.label] && jsonNuc[ui.item.label].tel1 || "";
								if(celular !== "-"){
									$("[name=_1_"+CB.acao+"_amostra_responsaveloftel]").attr("value", celular);
								} else {
									$("[name=_1_"+CB.acao+"_amostra_responsaveloftel]").attr("value", telefone);
								}

								$("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelcrmvof]").attr("value", "");
								$("[name=_1_"+CB.acao+"_amostra_responsavelofcrmv]").attr("value", "");
								getCrmv(ui.item.value, 'idpessoaresponsavelcrmvof');
							},
							create: function(){
								$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
									return $('<li>')
										.append('<a>' + item.label + '</a>')
										.appendTo(ul);
								};
							}
						}).data("idpessoaresponsavelof",jsonNuc);
					}else{
						alertErro("Javascript: idpessoaresponsavel(): Erro ao recuperar json de responsavel.\nVerificar Console de erros javascript.");
					}
				}
			});
		}
	}

	/*
	* LTM (23/04/2021): Recuperar o CRMV de acordo com o veterinário selecionado
	*/
	function getCrmv(idpessoa = null, tipo)
	{
		if(idpessoa)
		{	
			if(tipo == 'idpessoaresponsavelcrmv') { var tipo2 = 'responsavelcolcrmv'; } 
			if(tipo == 'idpessoaresponsavelcrmvof') { var tipo2 = 'responsavelofcrmv'; } 
			vIdPessoaCliente = $(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval();
			$.ajax({
				type: "get",
				url : `ajax/getresponsavel.php?idpessoa=${idpessoa}&tipo=crmv&vIdPessoaCliente=${vIdPessoaCliente}`,
				success: function(data)
				{
					//Transforma a string json em objeto
					jsonNucCrmv =jsonStr2Object(data);
					if(jsonNucCrmv)
					{
						//Nova versão de núcleo: autocomplete
						jsonAcCrmv = jQuery.map( jsonNucCrmv, function(n, id) {
							return {"label": n.crmv+" - "+n.uf, value:n.id}
						});

						if(jsonAcCrmv.length > 0)
						{
							if($("[name=_1_"+CB.acao+"_amostra_"+tipo+"]").length == 0){
								$("#"+tipo2).attr('cbvalue','');
								$("#"+tipo2).attr('name','_1_'+CB.acao+'_amostra_'+tipo);
								$("#"+tipo2).attr('id', tipo);								
								$("#"+tipo2).addClass("ui-autocomplete-input");
							}
							$oCrmv = $("[name=_1_"+CB.acao+"_amostra_"+tipo+"]");
							
							$oCrmv.autocomplete({
								source: jsonAcCrmv,
								delay: 0,
								create: function(){
									$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
										return $('<li>')
											.append('<a>' + item.label + '</a>')
											.appendTo(ul);
									};
								}
							}).data("crmv",jsonNucCrmv);
						} else {
							crmvatualiza = "_cx_u_amostra_idamostra="+$("[name='_1_u_amostra_idamostra']").attr("value")+
										   "&_cx_u_amostra_"+tipo+"=''";
							//Altera os dados do Núcleo
							CB.post({
								objetos: crmvatualiza
								,parcial:true
								,refresh: false
								,msgSalvo:false
							});
							$("input").remove("#"+tipo);
							if($("#"+tipo2).length == 0){
								$("#col_"+tipo).prepend('<input autocomplete="off" class="crmv" type="text" name="_1_'+CB.acao+'_amostra_'+tipo2+'" id="'+tipo2+'" title="CRMV" value="">');
							} 						
						}					
					}else{
						alertErro("Javascript: crmv(): Erro ao recuperar json de crmv.\nVerificar Console de erros javascript.");
					}
				}
			});

		}
	}

	/*
	* Recuperar a coleção de inputs da amostra relacionados à informções de Núcleo
	*/
	function getInputsNucleoAmostra(){
		return $("[name=_1_"+CB.acao+"_amostra_nucleoamostra] \
			,[name=_1_"+CB.acao+"_amostra_lote] \
			,[name=_1_"+CB.acao+"_amostra_granja] \
			,[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica] \
			,[name=_1_"+CB.acao+"_amostra_idespeciefinalidade] \
			,[name=_1_"+CB.acao+"_amostra_regoficial] \
			,[name=_1_"+CB.acao+"_amostra_tipoaves] \
			,[name=_1_"+CB.acao+"_amostra_tipoidade] \
			,[name=_1_"+CB.acao+"_amostra_idade]\
			,#finalidade");
	}

	/*
	* Calcular data de alojamento a partir da quantidade de Dias ou Semanas
	*/
	function getDataAlojamento(){
		vQuant = $("[name=_1_i_amostra_idade]").val();
		diasAlojamento = ($("[name=_1_i_amostra_tipoidade]").val()=="Semana(s)")?(vQuant*7):vQuant;
		return moment().subtract(diasAlojamento, 'days').format("DD/MM/YYYY");
	}

	function acEspecialidadeNucleo(){
		$oIdnucleo.autocomplete({
			source: jsonAc
			,delay: 0
			,select: function(){
				console.log($(this).cbval());
				preencheInputsNucleoAmostra($(this).cbval());
			}
			,create: function(){
				$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					vnucleo = "<span class='cinzaclaro'>Núcleo: </span>" + item.label;
					vnucleo = (item.lote)?vnucleo+" <span class=cinzaclaro> - Lote: </span>" + item.lote:vnucleo;
					return $('<li>')
						.append('<a>' + vnucleo + '</a>')
						.appendTo(ul);
				};
			}
			,noMatch: function(objAc){
				console.log("Executei callback");
				CB.post({
					objetos: "_x_i_nucleo_situacao=ATIVO&_x_i_nucleo_nucleo="+objAc.term+"&_x_i_nucleo_idpessoa="+$(":input[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval()
					,parcial:true
					,refresh: false
					,msgSalvo: "Núcleo criado"
					,posPost: function(data, textStatus, jqXHR){
						//Atualiza source json
						$oIdnucleo.data('uiAutocomplete').options.source.push({
							label: $oIdnucleo.val()
							,value: CB.lastInsertId
						});
						//Atualiza o objeto DATA associado ao input
						$oIdnucleo.data("nucleos")[CB.lastInsertId]={"nucleo":$oIdnucleo.val()};
						//Mostra a nova opção
						$oIdnucleo.autocomplete( "search", $oIdnucleo.val());
						//Informa visualmente o usuário para que complete as informações do núcleo
						getInputsNucleoAmostra().addClass("aguardandoCbpost");
						//Mostra campos adicionais do Núcleo
						$("#col_tipoaves, #col_finalidade").show()
						//Possibilita atualização do núcleo no prePost
						booAtualizaNucleo=true;
					}
				});
			}
		})
		//Uma propriedade com o json é adicionada ao objeto para tornar possível a consulta posterior
		.data("nucleos",jsonNuc);
	}

	/*
	* Ações antes de salvar amostra
	*/
	CB.prePost = function(inpar){
		if(booAtualizaNucleo){
			updNuc = "_x_u_nucleo_idnucleo="+$("[name=_1_"+CB.acao+"_amostra_idnucleo]").cbval()+
				"&_x_u_nucleo_lote="+$("[name=_1_"+CB.acao+"_amostra_lote]").val()+
				"&_x_u_nucleo_granja="+$("[name=_1_"+CB.acao+"_amostra_granja]").val()+
				"&_x_u_nucleo_unidadeepidemiologica="+$("[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica]").val()+
				"&_x_u_nucleo_idespeciefinalidade="+$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").cbval()+
				"&_x_u_nucleo_regoficial="+$("[name=_1_"+CB.acao+"_amostra_regoficial]").val()+
				"&_x_u_nucleo_alojamento="+getDataAlojamento()+
				"&_x_u_nucleo_rotulonucleotipo="+$("#rotulonucleotipo").val();

			//Altera os dados do Núcleo
			CB.post({
				objetos: updNuc
				,parcial:true
				,refresh: false
				,msgSalvo: "Núcleo atualizado"
			});
		}
	}

	function preencheInputsNucleoAmostra(inIdNucleo){
		jNucleo = $("[name=_1_"+CB.acao+"_amostra_idnucleo]").data("nucleos")[inIdNucleo];

		booAltera=false;
		if(
			//A pergunta de confirmação somente ocorrerá quando acao==u
			$("[name=_1_u_amostra_granja]").val()
			|| $("[name=_1_u_amostra_unidadeepidemiologica]").val()
			|| $("[name=_1_u_amostra_nucleoamostra]").val()
			|| $("[name=_1_u_amostra_lote]").val()
			|| $("[name=_1_u_amostra_tipoaves]").val()
			|| $("[name=_1_u_amostra_idade]").val()
			|| $("[name=_1_u_amostra_tipoidade]").val()){
			if(confirm("Os valores do Núcleo na amostra serão alterados.\nDeseja realmente confirmar a alteração\ndessas informações?")){
				booAltera=true;
			}else{
				booAltera=false;
			}
		}else{
			booAltera=true;
		}

		if(booAltera){
			//Caso seja selecionada na drop uma opção inexistente, esta variável será undefined, portanto isto limpará os campos relacionados ao núcleo
			if(jNucleo===undefined){
				$("[name=_1_"+CB.acao+"_amostra_granja] \
					,[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica] \
					,[name=_1_"+CB.acao+"_amostra_nucleoamostra] \
					,[name=_1_"+CB.acao+"_amostra_lote] \
					,[name=_1_"+CB.acao+"_amostra_tipoaves] \
					,[name=_1_"+CB.acao+"_amostra_tipoidade] \
					,[name=_1_"+CB.acao+"_amostra_idade] \
					,[name=_1_"+CB.acao+"_amostra_nsvo] \
					,[name=_1_"+CB.acao+"_amostra_cpfcnpjprod] \
					,[name=_1_"+CB.acao+"_amostra_uf] \
					,[name=_1_"+CB.acao+"_amostra_cidade] \
					,[name=_1_"+CB.acao+"_amostra_regoficial]").val("");
					$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").val("").cbval("");
			}else{
				//Alterar os campos da amostra
				$("[name=_1_"+CB.acao+"_amostra_granja]").val(jNucleo.granja);
				$("[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica]").val(jNucleo.unidadeepidemiologica);
				$("[name=_1_"+CB.acao+"_amostra_nucleoamostra]").val(jNucleo.nucleo);
				$("[name=_1_"+CB.acao+"_amostra_lote]").val(jNucleo.lote);
				$("[name=_1_"+CB.acao+"_amostra_tipoaves]").val(jNucleo.tipoaves);
				$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").cbval(jNucleo.idespeciefinalidade);
				$("[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").val(jNucleo.especiefinalidade);
				$("[name=_1_"+CB.acao+"_amostra_regoficial]").val(jNucleo.regoficial);
				$("[name=_1_"+CB.acao+"_amostra_nsvo]").val(jNucleo.nsvo);
				$("[name=_1_"+CB.acao+"_amostra_cpfcnpjprod]").val(jNucleo.cpfcnpj);
				$("[name=_1_"+CB.acao+"_amostra_uf]").val(jNucleo.uf);
				//preenchecidade();
				$("[name=_1_"+CB.acao+"_amostra_cidade]").val(jNucleo.cidade);

				if(jNucleo.rotulonucleotipo){//Caso o núcleo tenha sido inserido sem salvar a amostra, ele não terá estas propriedades
					$("#lb_idnucleo").html("<b>"+jNucleo.rotulonucleotipo+":</b>")
				};

				if(jsonEspecieFinalidade[jNucleo.idespeciefinalidade] && jsonEspecieFinalidade[jNucleo.idespeciefinalidade].flgcalculo=='Y'){

					if(jsonEspecieFinalidade[jNucleo.idespeciefinalidade].calculoidade=='D'){
						tipoidade="Dia(s)";
						tipoCalc="days";
					} else if(jsonEspecieFinalidade[jNucleo.idespeciefinalidade].calculoidade=='G'){
						tipoidade="Dias de Gestação";
						tipoCalc="days";
					}else if(jsonEspecieFinalidade[jNucleo.idespeciefinalidade].calculoidade=='S'){
						tipoidade="Semana(s)";
						tipoCalc="weeks";
					}else{
						tipoCalc="";
					}


					$("[name=_1_"+CB.acao+"_amostra_tipoidade]").val(tipoidade);

					if(jNucleo.alojamento && tipoCalc!=""){
						//Calcula a idade do nucleo conforme o tipo de ave (utiliza o plugin http://momentjs.com/)
						dataamostra = $("[name=_1_"+CB.acao+"_amostra_dataamostra]").val()||"";
						dataamostra = moment(dataamostra,"DD/MM/YYYY");
						dataaloj = moment(jNucleo.alojamento);
						idadenuc = dataamostra.diff(dataaloj,tipoCalc);
						$("[name=_1_"+CB.acao+"_amostra_idade]").val(idadenuc);
					}else{
						$("[name=_1_"+CB.acao+"_amostra_idade]").val("");
					}
				}
			}
		}
	}

	cbPostCallback = function(jqXHR,data,objFoco){debugger

		//idamostra = jqXHR.getResponseHeader("X-CB-PKID")||$("[name=_1_"+CB.acao+"_amostra_idamostra]").val();

		idamostra = jqXHR.getResponseHeader("X-CB-PKID");
		
		//LTM - 03/092020 - 370913: Remove o Azul e alerta se imprime quando for provisório
		if(idamostra){
			if((CB.modulo=="amostraaves" || CB.modulo=="amostraavesprovisorio") && confirm("====== REGISTRO CONCLUÍDO ======\n\nDeseja imprimir as etiquetas?\n\n===========================")){
				if(CB.modulo=="amostraaves")
				{
					imprimeTestes(idamostra, impressoraMod);
					//showModalEtiqueta(idamostra);
					//duplicarAmostra();
				}					
			}
			$("body").removeClass("novoprovisorio");
			if($("[name=_1_"+CB.acao+"_amostra_idunidade]").val() == 9 && $("[name=_1_"+CB.acao+"_amostra_idunidade]").val() == 'PROVISORIO'){
				location.reload();
			}		
		}
	}

	//Valida se data coleta é inferior à 2 dias da data da amostra, para alertar o usuário
	function validaDataColeta(inDataColeta, inDataAmostra){

		oDatacoleta = $("[name=_1_"+CB.acao+"_amostra_datacoleta]");

		if(inDataColeta && inDataAmostra){

			dataamostra = moment(dataamostra,"DD/MM/YYYY");
			datacoleta = moment(datacoleta,"DD/MM/YYYY");

			difColeta = datacoleta.diff(dataamostra,"days");

			if(difColeta < -3){
				alertAtencao("Data de registro superior a 72 horas da data de coleta");
			}

			if(difColeta<-1 ){
				oDatacoleta.addClass("fundovermelho branco");
			}else{
				oDatacoleta.removeClass("fundovermelho branco");
			}
		}else{
			oDatacoleta.removeClass("fundovermelho branco");
		}
	}

	//O daterangepicker não dispara o "change" do elemento. Portanto deve ser feita verificação do evento do plugin
	$("[name=_1_"+CB.acao+"_amostra_datacoleta]").on('apply.daterangepicker', function (ev, picker) {

		dataamostra = ($("[name=_1_"+CB.acao+"_amostra_dataamostra]").val()||"");
		datacoleta = (picker.startDate.format("DD/MM/YYYY")||"");

		validaDataColeta(dataamostra, datacoleta);

	}).on('change', function () {

		dataamostra = ($("[name=_1_"+CB.acao+"_amostra_dataamostra]").val()||"");
		datacoleta = $("[name=_1_"+CB.acao+"_amostra_datacoleta]").val();
		datacoleta = moment(datacoleta,"DD/MM/YYYY");

		validaDataColeta(dataamostra, datacoleta);

	});

	function showModalEtiqueta(inIdamostra){
		var imprimir=true;

		if(CB.lotesImpressaoAlterado){
			if(!confirm("Você alterou os lotes de impressão.\nDeseja realmente enviar para a impressora?")){
				imprimir=false;
			}
		}

		if(imprimir){
			_controleImpressaoModulo({
				modulo: getUrlParameter("_modulo"),
				grupo: 1,
				idempresa: idempresaAmostra,
				objetos:{
					idamostra: inIdamostra
				},
				impressaoDireta: true,
				posPrint: function(data, textStatus, jqXHR){
					CB.lotesImpressaoAlterado=false;
					if($("[name=_1_u_amostra_status]").val() && $("[name=_1_u_amostra_status]").val()=="ABERTO"){
						CB.oBtNovo.removeClass("disabled");
						CB.oBtSalvar.addClass("disabled");

						botaoEditarAmostra(); 

					}
				}
			});
		}
	}

	function imprimeTestes(inIdamostra, impressora){
		var imprimir=true;
		CB.imprimindo=true;

		if(CB.lotesImpressaoAlterado){
			if(!confirm("Você alterou os lotes de impressão.\nDeseja realmente enviar para a impressora?")){
				imprimir=false;
			}
		}

		if(imprimir){
			showModalEtiqueta(inIdamostra);
		}
	}

	function resetNucleo(){
		$("[name=_1_"+CB.acao+"_amostra_idnucleo],[name=_1_"+CB.acao+"_amostra_idespeciefinalidade]").val("").cbval(0);
		$("[name=_1_"+CB.acao+"_amostra_rotulonucleotipo],[name=_1_"+CB.acao+"_amostra_idade],[name=_1_"+CB.acao+"_amostra_tipoidade],[name=_1_"+CB.acao+"_amostra_nucleoamostra],[name=_1_"+CB.acao+"_amostra_lote],[name=_1_"+CB.acao+"_amostra_granja],[name=_1_"+CB.acao+"_amostra_unidadeepidemiologica],[name=_1_"+CB.acao+"_amostra_regoficial],[name=_1_"+CB.acao+"_amostra_nsvo],[name=_1_"+CB.acao+"_amostra_cidade],[name=_1_"+CB.acao+"_amostra_uf],[name=_1_"+CB.acao+"_amostra_cpfcnpj]").val("");
		
	}

	function alterarNucleo(){
		vIdNucleo = $("[name=_1_"+CB.acao+"_amostra_idnucleo]").cbval();
		if(vIdNucleo){
			janelamodal('?_modulo='+ModuloNuc+'&_acao=u&idnucleo='+vIdNucleo,1000,1100);
		}else{
			alertAtencao("Núcleo não selecionado!","");
		}
	}

	function novoNucleo(){
		vIdpessoa = $("[name=_1_"+CB.acao+"_amostra_idpessoa]").cbval();
		janelamodal('?_modulo='+ModuloNuc+'&_acao=i&idpessoa='+vIdpessoa,1000,1100);
	}

	function resetResponsavel(){
		$("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavel],[name=_1_"+CB.acao+"_amostra_responsavelcolcrmv],[name=_1_"+CB.acao+"_amostra_responsavelcolcont],[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelcrmv]").val("").cbval(0);
	}

	function resetResponsavelof(){
		$("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelof],[name=_1_"+CB.acao+"_amostra_responsavelofcrmv],[name=_1_"+CB.acao+"_amostra_responsaveloftel],[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelcrmvof]").val("").cbval(0);
	}

	function alterarResponsavel(){
		vidpessoaresponsavel = $("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavel]").cbval();
		if(vidpessoaresponsavel){
			janelamodal('?_modulo=pessoa&_acao=u&idpessoa='+vidpessoaresponsavel,1000,1100);
		}else{
			alertAtencao("Responsável pela Coleta não selecionado!","");
		}
	}

	function alterarResponsavelof(){
		vidpessoaresponsavelof = $("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelof]").cbval();
		if(vidpessoaresponsavelof){
			janelamodal('?_modulo=pessoa&_acao=u&idpessoa='+vidpessoaresponsavelof,1000,1100);
		}else{
			alertAtencao("Responsável pela Coleta Oficial não selecionado!","");
		}
	}

	//Atualiza rótulo indicador da quantidade de testes da amostra
	function atualizaQuantTestes(){
		oQuant = $("#testesquant");
		iTrsTeste = $("#tbTestes tbody tr").length;
		oQuant.html(iTrsTeste);
	}

	//Inicializa tela
	$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").focus();
	criaAutocompletesTestes();
	arrCoresLoteEtiquetas = $.extend({}, ["silver","#cc0000", "#0000cc", "#00cc00", "#990000","#ff6600", "#fcd202", "#b0de09", "#0d8ecf",  "#cd0d74"]);
	preencheDropNucleos();
	getResponsavel();
	if($("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelcrmv]").attr("cbvalue") != "" && !$("[name=_1_"+CB.acao+"_amostra_responsavelcolcrmv]").attr("cbvalue")){
		getCrmv($("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavel]").attr("cbvalue"), 'idpessoaresponsavelcrmv');
	}
	
	if($("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelof]").attr("cbvalue") != ""  && !$("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelof]").attr("cbvalue")){
		getCrmv($("[name=_1_"+CB.acao+"_amostra_idpessoaresponsavelof]").attr("cbvalue"), 'idpessoaresponsavelcrmvof');
	}

	atualizaQuantTestes();
	configuraOficial();
		
	$("#cbModal").on('hidden.bs.modal', function (e) {
		$("#cbModalCorpo").html('');
	});

	if($("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()!==""){
		popoverEditarCamposVisiveis();
	}

	function incluirEmLote(){
		novajanela("google");
	}

	function EditarNucleoAmostra(){
		$(`#nucleoamostra`).removeAttr('readonly');
		$(`#nucleoamostra`).focus();
	}

	$('input[name="datahorarecebimento"]').daterangepicker({
		timePicker: true,
		timePickerIncrement: 15,
		"singleDatePicker": true,
		"showDropdowns": true,
		"linkedCalendars": false,
		"opens": "left",
		"locale": {format: 'DD/MM/YYYY h:mm'}
	});

	//Recuperar estado da coluna para impressão do documento de TRA
	function getColunaVisivelTra(inInputName){
		//debugger
		//Separa o nome da coluna para update/delete na tabela de configuracoes
		$coluna = inInputName.explodeInputNameCarbon()[4];

		//Verifica se existe no Json da tabela telaamostraconf
		try{
			idconf=jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()][$coluna];
			if(idconf){
				return jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()][$coluna];
			}else{
				return false;
			}
		}catch(e){
			return false;
		}
	}
	function getColunaObrigatorioTra(inInputName){
		//debugger
		//Separa o nome da coluna para update/delete na tabela de configuracoes
		$coluna = inInputName.explodeInputNameCarbon()[4];

		//Verifica se existe no Json da tabela telaamostraconf
		try{
			idconf=jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()][$coluna];
			if(idconf){
				return jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()][$coluna];
			}else{
				return false;
			}
		}catch(e){
			return false;
		}
	}
	/*
	* Monta uma listagem com os inputs disponíveis na tela, para que o usuário configure a tela como desejar
	*/
	function listaInputsAmostra(){
		vIdSubtipoAmostra = $("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()||$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").attr("value");
		strTable = "<table>";
		//Recupera todos os containers que contém inputs visíveis/invisíveis
		$.each($(".rowDin"), function(i, row){
			strTable += "<tr>";
			inputs = $(row).find(":input[name*=_]");

			//Recupera todos os inputs da amostra
			$.each(inputs, function(i, input){
				$input = $(input);
				rotulo = $input.attr("title") || "";
				$campo = $input.attr("name").explodeInputNameCarbon()[4];
				if (jsonamostracamposobgtra != null) {
					$nome = jsonamostracamposobgtra[$campo];
					if ($nome != undefined && $nome[vIdSubtipoAmostra] != undefined) {
							$obrigatorio = $nome[vIdSubtipoAmostra]['obrigatorio']
							$visutra = $nome[vIdSubtipoAmostra]['visualizatra']
							$visuEmissao = $nome[vIdSubtipoAmostra]['visualizaemissao']
						}else{
							$obrigatorio = false;
							$visutra = false;
							$visuEmissao = false;
						}
					}else{
						$obrigatorio = false;
						$visutra = false;
						$visuEmissao = false;
					}
				
				
				//Classe para o botão de toggle do formulário
				strclass = $input.is(":visible")?"btn btn-success btn-xs pointer":"btn btn-default btn-xs pointer";
				stricon = $input.is(":visible")?"fa fa-eye":"fa fa-eye-slash";
				strVisivel = $input.is(":visible")?"Y":"N";
				//Classe para o botão de toggle da visibilidade no TRA
				iidtelamostraconf=getColunaVisivelTra($input.attr("name"));
				iidamostracampostra=getColunaObrigatorioTra($input.attr("name"));
				strclassTra = $visutra == 'Y'?"laudoicon docaut fa-lg verde pointer":"laudoicon docaut fa-lg fade cinzaclaro hovercinza pointer";
				strTitleTra = $visutra == 'Y'?"Ocultar informação no TRA":"Mostrar informação na impressão de TRA";
				strclassobg = $obrigatorio == 'Y'?"fa fa-lock fa-lg verde pointer":"fa fa-unlock fa-lg fade cinzaclaro hovercinza pointer";
				strclassEmissao = $visuEmissao == 'Y'?"fa fa-file-text fa-lg verde pointer":"fa fa-file-text fa-lg fade cinzaclaro hovercinza pointer";
				strTitleobg = $obrigatorio == 'Y'?"Não obrigatório no TRA":"Tornar campo obrigatório no TRA";
				strTitleEmissao = $visuEmissao == 'Y'?"Ocultar informação na Emissão do Resultado":"Mostrar informação na Emissão do Resultado";
				
				//Adiciona ao html
				if(rotulo != "" && $input.attr("name") != "_1_u_amostra_nucleoamostra"){
					strTable += "<td>";
					strTable += "<button class='"+strclass+"' onclick='toggleInputsVisiveis(this)' cbvisivel='"+strVisivel+"' cbinputname='"+$input.attr("name")+"'><i class='"+stricon+"'></i> "+rotulo+"</button>"
					strTable += "<i title='"+strTitleTra+"' class='"+strclassTra+"' vtra='"+$visutra+"' onclick='toggleInputsVisiveisTra(this)' idtelaamostraconf='"+iidtelamostraconf+"' inputname='"+$input.attr("name")+"' style='margin-left: 6px;vertical-align: middle;'></i>";
					strTable += "<i title='"+strTitleobg+"' class='"+strclassobg+"' obg='"+$obrigatorio+"' onclick='toggleInputsObrigatorioTra(this)' idamostracampos='"+iidamostracampostra+"' inputname='"+$input.attr("name")+"' style='margin-left: 6px;vertical-align: middle;'></i>";
					strTable += "<i title='"+strTitleEmissao+"' class='"+strclassEmissao+"' emissao='"+$visuEmissao+"' onclick='toggleInputsVisiveisEmissao(this)' idamostracampos='"+iidamostracampostra+"' inputname='"+$input.attr("name")+"' style='margin-left: 6px;vertical-align: middle;'></i>";
					strTable += "</td>";
				}
			})
			strTable += "</tr>";
		})
		strTable += "</table>";
		return strTable;
	}

	function popoverEditarCamposVisiveis(){
	
		$('#editarCamposVisiveis').webuiPopover("destroy").webuiPopover({
			title:'Selecionar campos visíveis <div align="Right"><i  class="fa fa-times fa-lg preto hoververmelho" onclick="(fechapopover(this))"></i></div>'
			,content: listaInputsAmostra
		});
	}
	function fechapopover(vthis){
		var divs = $(vthis).parents();
		$(divs['3']).removeClass('in').addClass('out').css('display','none')
	}
	
	function toggleInputsVisiveis(inBotao){
		$inbt = $(inBotao);

		//Recupera o input associado ao Botão clicado:
		$input = $("[name="+$inbt.attr("cbinputname")+"]");

		//Separa o nome da coluna para update/delete na tabela de configuracoes
		$coluna = $input.attr("name").explodeInputNameCarbon()[4];
		$tabela =  $input.attr("name").explodeInputNameCarbon()[3]
		//Recupera o container mais próximo com o atributo id=col_nomecoluna
		$col_ = $("[name="+$input.attr("name")+"]").parents("[id*=col_]");
		if($col_.length==0) console.error("Erro ao alterar visibilidade: O objeto Input ["+$inbt.attr("cbinputname")+"] deve estar contido em uma tag (Ex:div) com id=col_"+$coluna);

		//Recupera o container mais próximo contendo o label id=lb_nomecoluna
		$lb_ = $("#"+$col_.attr("id").replace("col_","lb_"));
		if($lb_.length==0) console.error("Erro ao alterar visibilidade: O LABEL para o objeto Input ["+$inbt.attr("cbinputname")+"] deve estar contido em uma tag 'parent' com id=lb_"+$coluna);

		//Objetos de label e coluna
		$collb=$().add($col_).add($lb_);

		if($inbt.attr("cbvisivel")=="Y"){
			let idamostra = $("[name=_1_"+CB.acao+"_amostra_idamostra]").val();
			let iddadosamostra = $('#iddadosamostra').val();
			let apagacampo='';

			//Recupera a PK da configuração que será apagada
			idtelaamostraconf = jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()][$coluna];
			if ($coluna != 'idunucleo') {
				if($tabela=='dadosamostra'){
					if(iddadosamostra>=1){
						apagacampo='_v_u_dadosamostra_iddadosamostra='+iddadosamostra+'&_v_u_dadosamostra_'+$coluna+'=';
					} else {
						apagacampo='';
					}
				}else{
					if(idamostra != ""){
						apagacampo='_v_u_amostra_idamostra='+idamostra+'&_v_u_amostra_'+$coluna+'=';
					}				
				}
			}else{
				apagacampo = '';
				idtelaamostraconfnucleo = jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['nucleoamostra'];
				if(idtelaamostraconfnucleo){
					apagacampo='_xn_d_amostracampos_idamostracampos='+idtelaamostraconfnucleo;
				}
			}
			if(!idtelaamostraconf){
				alertAtencao("Erro: idtelaamostraconf não recuperado para");
			}else{
				CB.post({
					objetos: apagacampo+'&_x_d_amostracampos_idamostracampos='+idtelaamostraconf
					,parcial:true
					,refresh: false
					,msgSalvo: "Coluna oculta"
					,posPost: function(data, textStatus, jqXHR){

						jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()][$coluna] = CB.lastInsertId;

						if($coluna == 'idnucleo'){
							$inputaux = $("[name=_1_"+CB.acao+"_amostra_nucleoamostra]");

							//Recupera o container mais próximo com o atributo id=col_nomecoluna
							$colaux_ = $("[name="+$inputaux.attr("name")+"]").parents("[id*=col_]");
							$lbaux_ = $("#"+$colaux_.attr("id").replace("col_","lb_"));
							$collbaux=$().add($colaux_).add($lbaux_);
							$collbaux.hide();
						}

						$collb.hide();
						$inbt.removeClass("btn-success")
								.addClass("btn-default")
								.attr("cbvisivel","N")
								.find("i")
								.removeClass("fa-eye")
								.addClass("fa-eye-slash");
					}
				});
			}
		}else{
			nm=$inbt.attr("cbinputname");
			nom= document.getElementsByName(nm);
			$(nom).removeAttr("style");
			$(nom).removeAttr("disabled");
			$(nom).removeAttr("name");
			concat = '';
			if($coluna == 'idnucleo'){
				idtelaamostraconfnucleo = jsonamostracamposid[unidadePadrao][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['nucleoamostra'];
				if(idtelaamostraconfnucleo == null){
					concat ="&_x1_i_amostracampos_local=TELA&_x1_i_amostracampos_idunidade="+$("[name=_1_"+CB.acao+"_amostra_idunidade]").val()+"&_x1_i_amostracampos_idsubtipoamostra="+$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()+"&_x1_i_amostracampos_campo=nucleoamostra";
				}
			}
			CB.post({
				objetos: "_x_i_amostracampos_local=TELA&_x_i_amostracampos_idunidade="+$("[name=_1_"+CB.acao+"_amostra_idunidade]").val()+"&_x_i_amostracampos_idsubtipoamostra="+$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()+"&_x_i_amostracampos_campo="+$coluna+concat
				,parcial:true
				,refresh: false
				,msgSalvo: "Coluna visível"
				,posPost: function(data, textStatus, jqXHR){
					if($coluna == 'idnucleo'){
						$inputaux = $("[name=_1_"+CB.acao+"_amostra_nucleoamostra]");

						//Recupera o container mais próximo com o atributo id=col_nomecoluna
						$colaux_ = $("[name="+$inputaux.attr("name")+"]").parents("[id*=col_]");
						$lbaux_ = $("#"+$colaux_.attr("id").replace("col_","lb_"));
						$collbaux=$().add($colaux_).add($lbaux_);
						$collbaux.show();
					}
					$collb.show();
					$inbt.removeClass("btn-default").addClass("btn-success").attr("cbvisivel","Y").find("i").removeClass("fa-eye-slash").addClass("fa-eye");
				}
			});
		}
		//console.log($input);
		//console.log(explodeInputNameCarbon($input.attr("cbinputname")));
	}

	function toggleInputsVisiveisTra(inBotao){
		$inbt = $(inBotao);
		$coluna = $inbt.attr("inputname").explodeInputNameCarbon()[4];
		$idtelaamostraconf = $inbt.attr("idtelaamostraconf");
		$visutra = $inbt.attr("vtra")
		
		if($idtelaamostraconf=="false"){//comparação textual
			CB.post({
				objetos: "_x_i_amostracampos_local=TELA&_x_i_amostracampos_idunidade="+$("[name=_1_"+CB.acao+"_amostra_idunidade]").val()+"&_x_i_amostracampos_idsubtipoamostra="+$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()+"&_x_i_amostracampos_campo="+$coluna+"&_x_i_amostracampos_visualizatra=Y"
				,parcial:true
				,refresh: false
				,msgSalvo: "Informação visível no TRA"
				,posPost: function(){
					$inbt.removeClass("fade cinzaclaro").addClass("verde");
				}
			});
		}else{
			if ($visutra == 'Y') {
				CB.post({
				objetos: "_x_u_amostracampos_idamostracampos="+$idtelaamostraconf+"&_x_u_amostracampos_visualizatra=N"
				,parcial:true
				,refresh: false
				,msgSalvo: "Coluna oculta"
				,posPost: function(){
					$inbt.removeClass("verde").addClass("fade cinzaclaro");
					}
				});
			}else{
				CB.post({
				objetos:"_x_u_amostracampos_idamostracampos="+$idtelaamostraconf+"&_x_u_amostracampos_visualizatra=Y"
				,parcial:true
				,refresh: false
				,msgSalvo: "Informação visível no TRA"
				,posPost: function(){
					$inbt.removeClass("fade cinzaclaro").addClass("verde");
					}
				});
			}
		}
		
	}
	function toggleInputsObrigatorioTra(inBotao){
		
		$inbt = $(inBotao);
		$coluna = $inbt.attr("inputname").explodeInputNameCarbon()[4];
		$idamostracampos = $inbt.attr("idamostracampos");
		$obrigatorio = $inbt.attr("obg");
		
		if($idamostracampos=="false"){//comparação textual
			CB.post({
				objetos: "_x_i_amostracampos_local=TELA&_x_i_amostracampos_idunidade="+$("[name=_1_"+CB.acao+"_amostra_idunidade]").val()+"&_x_i_amostracampos_idsubtipoamostra="+$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()+"&_x_i_amostracampos_campo="+$coluna+"&_x_i_amostracampos_obrigatorio=Y"
				,parcial:true
				,refresh: false
				,msgSalvo: "Informação obrigatória"
				,posPost: function(){
					jsonamostracamposobgtra[$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['obrigatorio'] = "Y"
					$("[name='"+$inbt.attr("inputname")+"']").attr('vnulo','')
					$inbt.removeClass("fa-unlock fade cinzaclaro").addClass("fa-lock verde");
				}
			});
		}else{
			if ($obrigatorio == 'Y') {
				CB.post({
				objetos: "_x_u_amostracampos_idamostracampos="+$idamostracampos+"&_x_u_amostracampos_obrigatorio=N"
				,parcial:true
				,refresh: false
				,msgSalvo: "Informação opcional"
				,posPost: function(){
					jsonamostracamposobgtra[$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['obrigatorio'] = "N"
					$("[name='"+$inbt.attr("inputname")+"']").removeAttr('vnulo')
					$inbt.removeClass("fa-lock verde").addClass("fa-unlock fade cinzaclaro");
					}
				});
			}else{
				CB.post({
				objetos: "_x_u_amostracampos_idamostracampos="+$idamostracampos+"&_x_u_amostracampos_obrigatorio=Y"
				,parcial:true
				,refresh: false
				,msgSalvo: "Informação obrigatória"
				,posPost: function(){
					jsonamostracamposobgtra[$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['obrigatorio'] = "Y"
					$("[name='"+$inbt.attr("inputname")+"']").attr('vnulo','')
					$inbt.removeClass("fa-unlock fade cinzaclaro").addClass("fa-lock verde");
					}
				});
			}
		}
	

	}
	function toggleInputsVisiveisEmissao(inBotao){
		
		$inbt = $(inBotao);
		$coluna = $inbt.attr("inputname").explodeInputNameCarbon()[4];
		$idamostracampos = $inbt.attr("idamostracampos");
		$emissao = $inbt.attr("emissao");
		
		if($idamostracampos=="false"){//comparação textual
			CB.post({
				objetos: "_x_i_amostracampos_local=TELA&_x_i_amostracampos_idunidade="+$("[name=_1_"+CB.acao+"_amostra_idunidade]").val()+"&_x_i_amostracampos_idsubtipoamostra="+$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()+"&_x_i_amostracampos_campo="+$coluna+"&_x_i_amostracampos_visualizaemissao=Y"
				,parcial:true
				,refresh: false
				,msgSalvo: "Visível na Emissão"
				,posPost: function(){
					jsonamostracamposobgtra[$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['visualizaemissao'] = "Y"
					$inbt.removeClass("cinzaclaro").addClass("verde");
				}
			});
		}else{
			if ($emissao == 'Y') {
				CB.post({
				objetos: "_x_u_amostracampos_idamostracampos="+$idamostracampos+"&_x_u_amostracampos_visualizaemissao=N"
				,parcial:true
				,refresh: false
				,msgSalvo: "Ocultada na Emissão"
				,posPost: function(){
					jsonamostracamposobgtra[$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['visualizaemissao'] = "N"
					$inbt.removeClass("verde").addClass("fade cinzaclaro");
					}
				});
			}else{
				CB.post({
				objetos: "_x_u_amostracampos_idamostracampos="+$idamostracampos+"&_x_u_amostracampos_visualizaemissao=Y"
				,parcial:true
				,refresh: false
				,msgSalvo: "Visível na Emissão"
				,posPost: function(){
					jsonamostracamposobgtra[$coluna][$("[name=_1_"+CB.acao+"_amostra_idsubtipoamostra]").cbval()]['visualizaemissao'] = "Y"
					$inbt.removeClass("fade cinzaclaro").addClass("verde");
					}
				});
			}
		}
	

	}
	//<i class="fa fa-times cbFecharForm" title="Fechar" onclick="CB.fecharForm()"></i>

	$(".dragtra").draggable();

	$("#novoTra").droppable({
		accept: ".dragtra"
		,drop: function( event, ui ) {
			alertAtencao("Crie um novo Termo de Recepção de Amostra para relacionar lotes");
		}
	});

	$("#formTra").droppable({
		accept: ".dragtra"
		,drop: function( event, ui ) {
			CB.post({
				objetos: "_x_i_traitem_idtra="+vIdTra+
						"&_x_i_traitem_idobjeto="+$(ui.draggable).attr("idlote")+
						"&_x_i_traitem_tipoobjeto=lote"
				,parcial:true
				,refresh: "refresh"
			});
		}
	});

	function novoTra(inIdpessoa){
		CB.post({
		objetos: "_x_i_tra_idpessoa="+inIdpessoa
		,parcial:true
		,posPost: function(data, textStatus, jqXHR){					
			amostra_tra(CB.lastInsertId);
		}
		})
	}



	function excluiTraItem(inIdTraItem){
		CB.post({
			objetos: "_x_d_traitem_idtraitem="+inIdTraItem
			,refresh: "refresh"
		})
	}

	if( $("[name=_1_u_amostra_idamostra]").val() ){
		$("#anexo").dropzone({
			idObjeto: $("[name=_1_u_amostra_idamostra]").val()
			,tipoObjeto: 'amostra'
			,idPessoaLogada: idPessoaLogada
		});
	}

	function statusenviaemailtea(intra){
		CB.post({
			objetos: "_x_u_tra_idtra="+intra+"&_x_u_tra_emailtea=Y"
			,refresh:"refresh"
		});
	}

	function preenchecidade(){
		
		$("#cidade").html("<option value=''>Procurando....</option>");
		
		$.ajax({
				type: "get",
				url : "ajax/buscacidade.php",
				data: { uf : $("#uf").val() },

				success: function(data){
					$("#cidade").html(data);
				},

				error: function(objxmlreq){
					alert('Erro:<br>'+objxmlreq.status); 

				}
			})//$.ajax

	}

	function flgcobrancaobrig(vthis){
		var atval=$(vthis).attr('atval');
		var idresultado=$(vthis).attr('idresultado');
		CB.post({
		objetos: "_x_u_resultado_idresultado="+idresultado+"=&_x_u_resultado_cobrancaobrig="+atval	
			,parcial:true
			,refresh: false
			,msgSalvo: "Alterada Cobrança do Teste."
			,posPost: function(){
				if(atval='Y'){
					$(vthis).attr('atval','N');
				}else{
					$(vthis).attr('atval','Y');
				}
			}
		})
		
	}

	//Function criada para quando a Amostra com status fechado alterar somente o input tirando a cobrança (resultado_cobrancaobrig resultado_cobrar ) (02/06/2020)
	//Lidiane (09/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=325790
	function flgcobrar(vthis){
		var atval = $(vthis).attr('atval');
		var idresultado = $(vthis).attr('idresultado');
		var name = $(vthis).attr('name');
		var bloqueio =  $(vthis).attr('bloqueio');
		//Valida se o campo que vem é de insert. Se não for, roda o CB.post - Lidiane (19/06-2020)
		//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=327494
		if(atval == 'Y'){
			cobrarobrig = 'N';
			$(vthis).attr('atval','N');
			$(vthis).attr('value','Y');
		} else {
			//@520296 - ACESSO AO PAINEL COBRANÇAS - não desflegar quando já existir cobrança enviada ao cliente. Lucas Melo
			if(bloqueio=='true'){
				alert("Amostra possui teste em cobrança."); event.preventDefault();return;
			}
			cobrarobrig = 'Y';
			$(vthis).attr('atval','Y');
			$(vthis).attr('value','N');
		}

		if(name.indexOf("#cobrar") > 1){
			$(vthis).removeAttr("idresultado");
		} else {
			CB.post({
			objetos: "_x_u_resultado_idresultado="+idresultado+"=&_x_u_resultado_cobrancaobrig="+atval+"=&_x_u_resultado_cobrar="+atval
				,parcial:true
				,refresh: false
				,msgSalvo: "Alterada Cobrança do Teste."
			});
		}    
	}

	function novoIdentificador(inidamostra){
		CB.post({
			objetos: "_x_i_identificador_idobjeto="+inidamostra+"&_x_i_identificador_tipoobjeto=amostra&qtdidentificador="+$("[name=_1_"+CB.acao+"_amostra_nroamostra]").val()
			,refresh:"refresh"
		});
	}

	function Novaidentificacao(inidamostra){
		CB.post({
			objetos: "_2_i_identificador_idobjeto="+inidamostra+"&_2_i_identificador_tipoobjeto=amostra"
			,refresh:"refresh"
		});
	}

	function excluiridentificacao(vthis,ididentificador,ini){
	
		
		var inid=$("[name=_"+ini+"_"+CB.acao+"_identificador_ididentificador").val();
		
		if(inid>0){			
			CB.post({
				objetos: "_x_d_identificador_ididentificador="+ididentificador
				,parcial:true
				,refresh: false
				,msgSalvo: "Excluido"
				,posPost: function(){
					$(vthis).parent().remove();
				}
			})
		}else{
			$(vthis).parent().remove();
		}
	}

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
}

   CB.preLoadUrl = function(){
   	//Como o carregamento é via ajax, os popups ficavam aparecendo após o load
   	$(".webui-popover").remove();
   }
   
   $(".alert-warning").webuiPopover({
	trigger: "hover"
	,width:300
	,offsetTop:0 
   	,placement: "bottom-right"
   	,delay: {
           show: 300,
           hide: 0
       }
   }); 

  if(CB.acao != 'i'){
	CB.on("posPost",function(data){
		if(data.jqXHR.getResponseHeader('vinculados'))
		{
			vinculados = JSON.parse(data.jqXHR.getResponseHeader('vinculados'));
			console.log(vinculados);
			
			if(vinculados > 0){
				alert(vinculados + " serviço(s) vínculado(s) serão incluídos.");
			}			
		}
	});
 } 


if ($('.assinarTEA').length ) {
    $('#btAssina').hide();
    $('#btRejeita').hide();
    
}

setTimeout(() => {
	$('.cpf').mask('000.000.000-00', {reverse: true})
}, 3000)

$('.selectpicker').selectpicker();
<?
if($_GET['_showerrors']=='Y'){
	echo showControllerErrors(AmostraController::$controllerErrors);
}

?>
// adicionar rodapé em todos os JS de forms p/ ser possível debuggar em produção
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| 17/02/2021 PEDRO LIMA |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

</script>
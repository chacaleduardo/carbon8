<script>
	//------- Injeção PHP no Jquery -------
	const logistica = '<?= $_1_u_prativ_logistica ?>';

	status = '<?=$_1_u_prativ_status?>';
	idprativ = '<?=$_1_u_prativ_idprativ?>';
	var idpessoa = '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>';
	jsonAtiv = <?=json_encode(PrativController::listarAtividadePorTamanhoAtivMaiorDois())?> || 0;
	if(idprativ)
	{
		var jEquipamentos = <?=json_encode(PrativController::buscarTagTipoPorIdTagClassEShare(1))?> || 0;
		var jTestes = <?=json_encode(PrativController::listarProdservPorTipoEIdEmpresa('SERVICO'))?> || 0;
	}
	//------- Injeção PHP no Jquery -------

	//------- Funções Módulo -------
	if(status == "INATIVO")
	{
		$("#cbModuloForm").find('input').prop("disabled", true);
		$("#cbModuloForm").find("select").prop("disabled", true);
		$("#cbModuloForm").find("textarea").prop("disabled", true);
	}

	$('#tipo-logistica').on('click', () => {
		const valorLogistica = logistica == 'Y' ? 'N' : 'Y';
		
		CB.post({
			objetos: {
				_1_u_prativ_idprativ: idprativ,
				_1_u_prativ_logistica: valorLogistica
			},
			parcial: true,
			refresh: false
		})
	})

	if(idprativ)
	{
		if (jEquipamentos != 0) 
		{
			$("#prativ_equipamento").autocomplete({
				source: jEquipamentos,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
						return $('<li>').append("<a>" + item.tagtipo + "</a>").appendTo(ul);
					};
				},
				select: function(event, ui) {
					CB.post({
						objetos: `_x_i_prativobj_idprativ=${$("[name='_1_u_prativ_idprativ']").val()}&_x_i_prativobj_tipoobjeto=tagtipo&_x_i_prativobj_idobjeto=${ui.item.idtagtipo}`,
						parcial: true
					});
				}
			});
		}
		
		if (jTestes != 0) 
		{
			$("#prativ_prodserv").autocomplete({
				source: jTestes,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
						return $('<li>').append("<a>" + item.descr + "</a>").appendTo(ul);
					};
				},
				select: function(event, ui) {
					CB.post({
						objetos: `_x_i_prativobj_idprativ=${$("[name='_1_u_prativ_idprativ']").val()}&_x_i_prativobj_tipoobjeto=prodserv&_x_i_prativobj_idobjeto=${ui.item.idprodserv}`,
						parcial: true
					});
				}
			});
		}
		
		$(".cbupload").dropzone({
			idObjeto: idprativ,
			tipoObjeto: 'prativ',
			idPessoaLogada: idpessoa
		});
	}

	$(".ctrlproc").keypress(function(e) {
		if (e.which == 13) {
			CB.post({
				objetos: `_x_i_prativobj_idprativ=${$(this).attr("idprativ")}&_x_i_prativobj_tipoobjeto=ctrlproc&_x_i_prativobj_descr=${$(this).val()}`,
				parcial: true
			});
		}
	});

	$(".materiais").keypress(function(e) {
		if (e.which == 13) {
			CB.post({
				objetos: `_x_i_prativobj_idprativ=" + $(this).attr("idprativ") + "&_x_i_prativobj_tipoobjeto=materiais&_x_i_prativobj_descr=" + $(this).val()`,
				parcial: true
			});
		}
	});

	if(status != "APROVADO")
	{
		// Editar informacao especifica
		(function() {
			$('.edit-info-espec').on('click', function() {
				let inputEdit = $(`#${$(this).data('inputid')}`);
				inputEdit.removeAttr('disabled');

				if (!$(this).replaceClass('fa-edit', 'fa-check-circle hoververde pointer cinzaclaro')) {
					if (CB.ajaxPostAtivo) {
						return alertAtencao("Aguarde: ação anterior ainda não concluída");
					}

					$(this).replaceClass('fa-check-circle', 'fa-edit');
					CB.post({
						objetos: `_x_u_prativobj_idprativobj=${inputEdit.data('id')}&_x_u_prativobj_descr=${inputEdit.val()}`,
						refresh: false,
						parcial: true
					});

					inputEdit.attr('disabled', 'disabled');
				}
			})
		})();

		$(".enable-sortable tr .move").on('mousedown', sortableEvent);
	}
	//------- Funções Módulo -------

	//------- Funções Módulo -------
	function geraprativobj(vthis) 
	{
		CB.post({
			objetos: `_x_i_prativobj_idprativ=${$(vthis).attr("idprativ")}&_x_i_prativobj_tipoobjeto=ctrlproc&_x_i_prativobj_descr=${$(vthis).val()}`,
			parcial: true
		});
	}

	function geraprativobjm(vthis) 
	{
		CB.post({
			objetos: `_x_i_prativobj_idprativ=${$(vthis).attr("idprativ")}&_x_i_prativobj_tipoobjeto=materiais&_x_i_prativobj_descr=${$(vthis).val()}`,
			parcial: true
		});
	}

	function excluiitem(inidprativobj) 
	{
		CB.post({
			objetos: `_x_d_prativobj_idprativobj=${inidprativobj}`,
			parcial: true
		});
	}

	function inserircontr(inidprativ, inidobjeto, intipoobjeto) 
	{
		CB.post({
			objetos: `_x_i_prativobj_idprativ=${inidprativ}&_x_i_prativobj_tipoobjeto=${intipoobjeto}&_x_i_prativobj_idobjeto=${inidobjeto}`,
			parcial: true
		});
	}

	function alteraInputmanual(inObj) 
	{
		$inObj = $(inObj);
		var vIdprativobj = $inObj.attr("idprativobj");
		var vOpcaoatual = $inObj.attr("inputmanual");

		//Recuperar a proxima (ou a primeira) opção possà­vel
		$arrOpcoes = $arrOpcoes = [{
			"opcao": "",
			"icone": "fa fa-eye-slash cinzaclaro"
		}, {
			"opcao": "check",
			"icone": "fa fa-check-square-o verde"
		}, {
			"opcao": "linha",
			"icone": "fa fa-window-minimize verde"
		}, {
			"opcao": "text",
			"icone": "fa fa-comment verde"
		}];
		var oProximaOpcao;
		$.each($arrOpcoes, function(i, o) {
			if (vOpcaoatual == o.opcao) {
				//Recupera o próximo. Caso esteja na última opção, retorna o primeiro item do array. Isto faz um "Loop" nas opçàµes
				oProximaOpcao = $arrOpcoes[i + 1] || $arrOpcoes[0];
				return false;
			}
		})

		//Salva
		CB.post({
			objetos: "_x_u_prativobj_idprativobj=" + vIdprativobj + "&_x_u_prativobj_inputmanual=" + oProximaOpcao.opcao,
			parcial: true,
			refresh: false,
			msgSalvo: "Opção alterada"
		});

		//Isto vai ocorrer ANTES da resposta do servidor, porque o ajax é "assà­ncrono"
		$inObj.attr("class", "hoververde pointer " + oProximaOpcao.icone).attr("inputmanual", oProximaOpcao.opcao);

		//Impede que os eventos de clique do mouse passem pros objetos que estão embaixo
		fim(event);
	}

	if(status != "APROVADO")
	{
		function sortableEvent() 
		{
			let $tBody = $(".enable-sortable tbody");
			//Permitir ordenar/arrastar os TR de insumos
			$tBody.sortable({
				update: function(event, objUi) {
					ordenaInfoEspec($(this));
				},
				stop: function(event, ui) {
					$(this).sortable("disable");
				}
			});

			$tBody.sortable('enable');
		}

		function ordenaInfoEspec($element) 
		{
			$.each($element.find("tr"), function(i, otr) {
				$(this).find(":input[name*=ord]").val(i);
			});
		};
	}
	//------- Funções Módulo -------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>
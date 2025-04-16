<script>
	//------- Injeção PHP no Jquery -------
	var idprproc = "<?=$_1_u_prproc_idprproc?>" || "";
	var status = '<?=$_1_u_prproc_status?>';
	var idpessoa = '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>';
	if(idprproc)
	{
		var jAti = <?=$JSON->encode(PrProcController::listarAtividadesPorIdempresaEAtividadeNaoNulo())?> || 0;
		var $arrCores = <?=$JSON->encode(PrProcController::$arrCores)?>;
		//------- Injeção PHP no Jquery -------

		//------- Funções JS -------

		//mapear autocomplete de clientes
		jAti = jQuery.map(jAti, function(o, id) {
			return {
				"label": o.ativ,
				value: id
			}
		});

		function setMaskPattern(inputElement) {
			// Get the user's input value
			var inputValue = inputElement.value;
			if(inputValue == ''){
				return;
			}

			if(inputValue.includes(':') == false && inputValue.length == 4){
				inputValue = inputValue.replace(/(\d{2})(\d{2})/, '$1:$2');
			}else if(inputValue.includes(':') == false && inputValue.length == 5){
				inputValue = inputValue.replace(/(\d{3})(\d{2})/, '$1:$2');
			}

			// Determine the mask pattern based on the input length
			var maskPattern = (inputValue.length <= 5) ? '99:99' : '999:99';
			if(inputValue.includes(':') == false){
				switch (inputValue.length) {
					case 1:
						maskPattern = '9';
						break;
					case 2:
						maskPattern = '99';
						break;
					case 3:
						maskPattern = '9:99';
						break;
					case 4:
						maskPattern = '99:99';
						break;
					case 5:
						maskPattern = '999:99';
						break;
					case 6:
						maskPattern = '9999:99';
						break;
					
					default:
						maskPattern = '99:99';
						break;
				}
			}

			// Apply the mask pattern using VMasker
			VMasker(inputElement).maskPattern(maskPattern);
			calculaTempoFinal(inputElement);
		}

		function calculaTempoFinal(this_){
			if($(this_).attr('id') != 'tempofinal'){
				var tempoEstimado = 0;
				$.each($('.tempoestimado'), function(i, obj) {
					tempoEstimado += parseInt($(obj).val().replace(':', ''));
				});
				$('#tempofinal').val(tempoEstimado);
				setMaskPattern($('#tempofinal')[0]);
			}
		}

		//autocomplete de clientes
		$("[name*=prproc_idprativ]").autocomplete({
			source: jAti,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
				};
			},
			select: function() {
				console.log($(this).cbval());
				iprprocprativ(idprproc, $(this).cbval(), $('#prproc_idprativ').attr('ordem'));
			}
		});

		CB.prePost = function(obj) {
			obj = {
				objetos: {}
			}
			obj['beforeSend'] = function(jqXHR, settings) {
				jqXHR.setRequestHeader('_jwt_', localStorage.getItem('jwt'))
			}
			return obj;
		}

		function alterarTempoGastoObrigatorio(this_){
			if($(this_).attr('readonly') == undefined){
				var idprproc = $(this_).attr('idprproc');
				var tempoGastoObrigatorio = $(this_).is(':checked') ? 'Y' : 'N';
				CB.post({
					objetos: `_x_u_prproc_idprproc=${idprproc}&_x_u_prproc_tempogastoobrigatorio=${tempoGastoObrigatorio}`,
					parcial: true,
					refresh: false
				});
			}
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
					objetos: `_x_i_prativobj_idprativ=${$(this).attr("idprativ")}&_x_i_prativobj_tipoobjeto=materiais&_x_i_prativobj_descr=${$(this).val()}`,
					parcial: true
				});
			}
		});

		if (status != 'APROVADO')
		{
			//Permite ordenação dos elementos
			function sortableEvent() 
			{
				$divBody = $(".divbody");
				$divBody.sortable({
					update: function(event, objUi) {
						ordenaAtividades();
					},
					stop: function(event, ui) {
						$(this).sortable("disable");
					}
				});

				$divBody.sortable('enable');
			}
			$(".enable-sortable tr .move").on('mousedown', sortableEvent);
		}

		$("table.sortable > tbody").sortable({
			update: function(event, objUi) {
				ordenaPrativobj(objUi);
			}
		});

		//Salvar o estado de cada atividade: collapse/collapse in
		$('[id*=atividadeinfo]').on('hide.bs.collapse', function(e) {
			vIdprativ = $("#" + e.currentTarget.id).attr("idprativ");
			CB.post({
				objetos: `_x_u_prativ_idprativ=${vIdprativ}&_x_u_prativ_collapse=collapse`,
				parcial: true,
				refresh: false
			})
		}).on('show.bs.collapse', function(e) {
			vIdprativ = $("#" + e.currentTarget.id).attr("idprativ");
			CB.post({
				objetos: `_x_u_prativ_idprativ=${vIdprativ}&_x_u_prativ_collapse=collapse in`,
				parcial: true,
				refresh: false
			})
		});

		$(".cbupload").dropzone({
			idObjeto: idprproc,
			tipoObjeto: 'prproc',
			idPessoaLogada: idpessoa
		});
		//------- Funções JS -------

		//------- Funções Módulo -------
		function iprprocprativ(inidprproc, inidprativ, inordem) 
		{
			CB.post({
				objetos: `_x_i_prprocprativ_idprativ=${inidprativ}&_x_i_prprocprativ_idprproc=${inidprproc}&_x_i_prprocprativ_ord=${inordem}`,
				parcial: true
			});
		}

		function alteraLoteImpressao(inObj) 
		{
			const corSelecionadaJQ = $(inObj),
					btnPagina = corSelecionadaJQ.closest('td').find('button'),
					vIdprprocprativ = btnPagina.attr("idprprocprativ"),
					vLoteatual = parseInt(btnPagina.attr("loteimpressao")),
					indiceCor = corSelecionadaJQ.data('indice');

			CB.post({
				objetos: `_x_u_prprocprativ_idprprocprativ=${vIdprprocprativ}&_x_u_prprocprativ_loteimpressao=${indiceCor}`,
				parcial: true,
				refresh: false
			});

			//Isto vai ocorrer ANTES da resposta do servidor
			btnPagina.css("color", $arrCores[indiceCor]).attr("loteimpressao", indiceCor);

			//Impede que os eventos de clique do mouse passem pros objetos que estão embaixo
			fim();
		}

		function geraprativobj(vthis) 
		{
			CB.post({
				objetos: `_x_i_prativobj_idprativ=${$(vthis).attr("idprativ")}&_x_i_prativobj_tipoobjeto=ctrlproc&_x_i_prativobj_descr=${$(vthis).val()}`,
				parcial: true
			});
		}

		function novaversao(inidprproc) 
		{
			CB.post({
				objetos: `_1_u_prproc_idprproc=${inidprproc}&_1_u_prproc_status=REVISAO`,
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
				objetos: `_x_d_prativobj_idprativobj=${inidprativobj}`
			});
		}

		function novoitem(inidprativ, intipoobjeto, inname, ini) 
		{
			var vidobjeto = $('[name=' + inname + '' + ini + ']').val();
			CB.post({
				objetos: `_x_i_prativobj_idprativ=${inidprativ}&_x_i_prativobj_tipoobjeto=${intipoobjeto}&_x_i_prativobj_idobjeto=${vidobjeto}`,
				parcial: true
			});
		}

		function excluiatividade(inidprprocprativ) 
		{
			CB.post({
				objetos: `_x_d_prprocprativ_idprprocprativ=${inidprprocprativ}`,
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

		function ordenaAtividades() 
		{
			$.each($('[name*=_prprocprativ_ord]'), function(i, obj) {
				$(obj).val(i);
			})
		}

		function ordenaPrativobj(objUi) 
		{
			$inputsOrd = objUi.item.closest("table").find("input[name*=prativobj_ord]");
			$.each($inputsOrd, function(i, obj) {
				$(obj).val(i);
			});
			CB.post();
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
					//Recupera o prà³ximo. Caso esteja na última opção, retorna o primeiro item do array. Isto faz um "Loop" nas opçàµes
					oProximaOpcao = $arrOpcoes[i + 1] || $arrOpcoes[0];
					return false;
				}
			})

			//Salva
			CB.post({
				objetos: `_x_u_prativobj_idprativobj=${vIdprativobj}&_x_u_prativobj_inputmanual=${oProximaOpcao.opcao}`,
				parcial: true,
				refresh: false,
				msgSalvo: "Opção alterada"
			});

			//Isto vai ocorrer ANTES da resposta do servidor, porque o ajax é "assà­ncrono"
			$inObj.attr("class", "hoververde pointer " + oProximaOpcao.icone).attr("inputmanual", oProximaOpcao.opcao);

			//Impede que os eventos de clique do mouse passem pros objetos que estão embaixo
			fim(event);
		}

		function atualizardia(vthis, idprprocprativ) 
		{
			CB.post({
				objetos: `_x_u_prprocprativ_idprprocprativ=${idprprocprativ}&_x_u_prprocprativ_dia=${$(vthis).val()}`,
				parcial: true,
				refresh: false,
				msgSalvo: "Dia alterado"
			});
		}

		function alterarBloqueioStatus(idprprocprativ, vthis)
		{
			bloqueio = $(vthis).attr('bloquearstatus');
			bloqueioval = (bloqueio == 'Y') ? 'N' : 'Y';
			$(vthis).attr('bloquearstatus', bloqueioval);
			CB.post({
				objetos: `_x_u_prprocprativ_idprprocprativ=${idprprocprativ}&_x_u_prprocprativ_bloquearstatus=${bloqueioval}`,
				parcial: true,
				refresh: true
			});			
		}
		//------- Funções Módulo -------

		function montarCoresImpressao() {
			let coresImpressaoHTML = `<div 
										class="d-flex align-items-center p-2 flex-wrap" 
										style="justify-content: space-evenly">`;

			$arrCores.forEach((cor, index) => {
				coresImpressaoHTML += `<div 
											onclick="alteraLoteImpressao(this)"
											data-indice="${index}" 
											class="pointer" style="background-color: ${cor};
											width: 20px;height:20px;border-radius: 50%;margin: .3rem .5rem;"
										></div>`
			});

			coresImpressaoHTML += '</div>';

			return coresImpressaoHTML;
		}

		$('[data-toggle="popover"]').popover({
			html: true,
			content: montarCoresImpressao(),
			trigger: 'focus'
		});
	}

	const selects = document.querySelectorAll('.fluxo-select');

	function updateOptions() {
		const selectedValues = Array.from(selects)
			.map(select => select.value)
			.filter(value => value !== ""); // Obter valores selecionados, ignorando vazios

		selects.forEach(select => {
			const currentValue = select.value;

			Array.from(select.options).forEach(option => {
				if (selectedValues.includes(option.value) && option.value !== currentValue) {
					option.style.display = 'none'; // Ocultar opções selecionadas em outros <select>
				} else {
					option.style.display = ''; // Exibir as opções disponíveis
				}
			});
		});
	}

	// Atualizar opções sempre que um <select> mudar
	selects.forEach(select => {
		select.addEventListener('change', updateOptions);
	});

	// Inicializar a lógica
	updateOptions();
	
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>
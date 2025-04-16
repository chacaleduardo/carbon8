<script>
	//------- Injeção PHP no Jquery -------
	jClientes = <?=$JSON->encode($arrCli)?> || "";
	jResponsavel = <?=$JSON->encode($arrResponsavel)?> || "";
	jInsumos = <?=$JSON->encode($arrInsumos)?> || "";
	jAtividadeInsumos = <? if(empty($jAtividadeInsumos)) { echo "null"; } else { echo $jAtividadeInsumos; } ?> || "";
	jLoteAtiv = <?=$JSON->encode($arrLoteAtiv)?> || "";
	jprodForm = <?=$JSON->encode($prodForm)?> || "";
	jprodFormula = <?=$JSON->encode($prodFormula)?> || "";	
	jInfRotulo = <?=$JSON->encode($arrInfRotulo[0])?> || "";
	jLotecons = <?=$JSON->encode($arrLotecons)?> || "";
	jLoteObj = <?=$JSON->encode($arrLoteObj)?> || "";	
	jSgareasetor = <?=$JSON->encode($arrsgareasetor)?> || "";
	jProd = <?=$JSON->encode($arrProd);?> || "";
	jClientesSolfab = <?=$JSON->encode($arrClientesSolFab)?> || "";
	$_modulo_lote = "<?=$modulo_lote ?>";
	$_2_u_lote_idprodserv = "<?=$_2_u_lote_idprodserv ?>";
	$_2_u_lote_tipoform = "<?=$_2_u_lote_tipoform ?>";
	$_2_u_lote_tipo = "<?=$_2_u_lote_tipo ?>";
	$_1_u_formalizacao_status = "<?=$_1_u_formalizacao_status ?>";
	$_2_u_lote_idpessoa = "<?=$_2_u_lote_idpessoa ?>";
	$_2_u_lote_idlote = "<?=$_2_u_lote_idlote ?>";
	$_2_u_lote_idsolfab = "<?=$_2_u_lote_idsolfab ?>";
	$_2_u_lote_qtdpedida_exp = "<?=$_2_u_lote_qtdpedida_exp ?>";
	$_2_u_lote_qtdpedida = "<?=tratanumero($_2_u_lote_qtdpedida) ?>";
	$_2_u_lote_qtdajust_exp = "<?=$_2_u_lote_qtdajust_exp ?>";
	$_2_u_lote_qtdajust = "<?=tratanumero($_2_u_lote_qtdajust) ?>";
	$_2_u_lote_qtdprod_exp = "<?=$_2_u_lote_qtdprod_exp ?>";
	$_2_u_lote_qtdprod = "<?=tratanumero($_2_u_lote_qtdprod) ?>";
	$_2_u_lote_statusao = "<?=$_2_u_lote_statusao ?>";
	$_2_u_lote_idprodservformula = "<?=$_2_u_lote_idprodservformula ?>";
	var idFormalizacao = '<?= $_1_u_formalizacao_idformalizacao ?>';
	var partidaLote = '<?= $_2_u_lote_partida ?>';
	var exercicioLote = '<?= $_2_u_lote_exercicio ?>';
	var idempresa = '<?=$_1_u_formalizacao_idempresa?>' || 1;
	var idprproc = '<?=$_1_u_formalizacao_idprproc ?>';
	var exercicio = '<?=$_1_u_formalizacao_exercicio ?>';
	var idunidade = '<?=$_1_u_formalizacao_idunidade ?>';
	var opMaster = '<?=array_key_exists("opmaster", getModsUsr("MODULOS"))?>';
	var moduloformalizacao = '<?=$_GET["_modulo"]?>';
	var getIdprodserv = '<?=$_GET["idprodserv"]?>';
	//------- Injeção PHP no Jquery -------

	//------- Variáveis Globais -------
	//Alterar rótulos conforme status
	var statusFormalizacao = ['FORMALIZACAO', 'PROCESSANDO', 'TRIAGEM', 'AGUARDANDO', 'ABERTO'];
	if (statusFormalizacao.indexOf($_1_u_formalizacao_status) > -1) {
		strlt = "Lotes disponíveis";
		strlut = "Utilizando";
	} else {
		strlt = "Lotes utilizados / Quant. real";
		strlut = "Utilizado";
	}

	//esconder botões da formalização
	// Maf: Verificar real necessidade de aplicação de Javascript ao invés de CSS puro. Caso seja realmente necessário, descomentar
	var status = $("[name=_1_u_formalizacao_status]").val();
	if (status != "FORMALIZACAO" && status != "TRIAGEM" && status != "PROCESSANDO") {
		$("span.badge").toggleClass("hidden");
		$("span.rotpadrao").toggleClass("hidden");
		$("a.fa-plus-circle").toggleClass("hidden");
		$("i.fa-eye").toggleClass("hidden");
		$("label").addClass("preto");
	} 
	//------- Variáveis Globais -------

	//------- Exececuções para Carregar o módulo ----------
	//------- Exececuções para Carregar o módulo ----------

	//------- Funções JS -------
	jClientes = jQuery.map(jClientes, function(o, id) {
		return {
			"label": o.nome,
			value: id + "",
			"centrocusto": o.centrocusto
		}
	});

	$("[name*=_lote_idpessoa]").autocomplete({
		source: jClientes,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.codprodserv + "</span></a>").appendTo(ul);
			};
		},
		select: function(event, ui) {
			$("[name=_2_u_lote_idsolfab]").val("").cbval("");
			autoCompleteSolfab();
			resetFormalizacao();
			preencherSolfab(ui.item.value, 'cliente');
		}
	});

	jResponsavel = jQuery.map(jResponsavel, function(o, id) {
		return {
			"label": o.nome,
			value: id + ""
		}
	});

	$("[name*=_formalizacao_responsavel]").autocomplete({
		source: jResponsavel,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append(`<a>${item.label}</a>`).appendTo(ul);
			};
		}
	});

	jProd = jQuery.map(jProd, function(o, id) {
		return {
			"label": o.descr,
			value: id,
			"codprodserv": o.codprodserv,
			"qtdpadrao": o.qtdpadrao,
			"qtdpadrao_exp": o.qtdpadrao_exp
		}
	});

	$("[name*=_lote_idprodserv]").autocomplete({
		source: jProd,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.codprodserv + "</span></a>").appendTo(ul);
			};
		},
		select: function(event, ui) {
			qtdpadrao = recuperaExpoente(ui.item.qtdpadrao, ui.item.qtdpadrao_exp)
			$("[name=_1_" + CB.acao + "_lote_qtdpedida]").val(qtdpadrao).addClass("highlightVermelho");
			resetFormalizacao();
			preencherFormula(ui.item.value);
		}
	});

	$("#modalloteformulains").click(function() {
		var idlote = $("[name=_2_u_lote_idlote]").val();
		CB.modal({
			url: "?_modulo=loteformulains&_acao=u&idlote=" + idlote,
			header: "Insumos do lote"
		});
	});
	
	var statusFormalizacaoLiberarOp = ['APROVADO', 'CANCELADO', 'REPROVADO'];
	if(opMaster == 1 && statusFormalizacaoLiberarOp.indexOf($_1_u_formalizacao_status) == -1)
	{
		if($("#aprovareprovadotemporario").length == 0)
		{
			CB.novoBotaoUsuario({
				id:"aprovareprovadotemporario",
				rotulo:"Finalizar OP",
				class:"btn-danger",
				icone:"fa fa-check",
				onclick:function(){
					let $ol = $("div[title*='idloteativ:']");
					var count = 0;
					var countPendente = 0;

					if($ol.length < 1){
						alertAtencao('Esta OP não possui atividades', 'Atenção');
						return false;
					}	

					var objcbpost = "";
					//Carrega todas as atividades Pendentes para que sejam concluídas
					$("div[title*='idloteativ:'] label.alert-warning").each((i,o)=>{
						if(o.innerText == 'PENDENTE' || o.innerText == 'PROCESSANDO'){
							countPendente++;
						} else if(o.innerText == 'CONCLUIDO'){
							count++;
						}
					})

					let lastIndex = $ol.length - 1;
					let idloteativ = $ol[lastIndex].title.replace('idloteativ: ','')

					if(countPendente > 0){		
						finalizaop('CONCLUIDO', idloteativ, true);
					}

					if(count == $ol.length){
						finalizaop('CONCLUIDO', idloteativ);
					}
				}
			});
		}
	}

	$('[data-toggle="tooltip"]').tooltip();

	$('#dataproducao').on('apply.daterangepicker', function(ev, picker) {
		setdataproducao(picker.startDate.format('DD/MM/YYYY'));
	});

	CB.prePost = function() {
		//Caso seja mudança de status
		if (($_1_u_formalizacao_status == "FORMALIZACAO" || $_1_u_formalizacao_status == "TRIAGEM") && $("[name=_1_u_formalizacao_status]").val() == "PROCESSANDO") 
		{
			//Criar testes
			//Adicionar 1 input ao POST do carbon, para ser referenciado posteriormente do lado do servidor
			return {
				objetos: "gerartestes=Y"
			}
		}

		var StatusConcluido = {};

		$.each($("#corpoFormalizacao").find(":input[name*=_loteativ_status]"), function(i, o) {
			$o = $(o);
			//console.log($o.val())
			if ($o.val() == "CONCLUIDO") {
				StatusConcluido[i] = o;
			}
		});

		if (tamanho(jLoteAtiv) == tamanho(StatusConcluido)) {
			//se o AO do lote estiver pendente
			if ($_2_u_lote_statusao == "PENDENTE") {
				alert("Lote possui consumo pendente!!!");
			} else {
				return {
					objetos: "concluido=Y"
				}
			}
		}
	}

	//se apertar ctrol P grava impressao
	$(document).on('keydown', function(e) {
		// You may replace `p` with whatever key you want
		if ((e.metaKey || e.ctrlKey) && (String.fromCharCode(e.which).toLowerCase() === 'p')) {
			// console.log( "You pressed CTRL + p" );
			gravaimpressao();
		}
	});

	if ($("[name=_1_u_formalizacao_idformalizacao]").val())
	{
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_formalizacao_idformalizacao]").val(),
			tipoObjeto: 'formalizacao',
			idPessoaLogada: $_2_u_lote_idpessoa
		});
	}
	//------- Funções JS -------

	//------- Funções Módulo -------
	/*
	* Esta função recebe uma coleção de objetos (insumos) para loop e extração  das fórmulas existentes em cada um
	*/
	function getFormulas(inJInsumos)
	{
		jFormulas = {};
		$.each(inJInsumos, function(i,o){
			if(o.idprodservformula){
				of = {rotulo:o.rotulo, cor:o.cor}
				jFormulas[o.idprodservformula] = of;
			}else{
				console.warn("getFormulas: o.idprodservformula inválido: "+i);
			}
		});
		return jFormulas;
	}

	/*
	* Montar html com as possíveis fórmulas para seleção no lote em questão
	* Recebe como parâmetro os insumos/objetos FILHO, que contém as possíveis fórmulas para produção
	* Uma função *externa* de callback será utilizado para o evento de clique/seleção da fórmula
	*/
	function htmlSeletorFormula(inObj, inNomeFuncaoCallback)
	{
		//Recupera o rotulo de algum objeto para alcançar o pai
		vIdArvoreInsumos = primeiro(inObj);
		vInsumoPai = getInsumoPai(vIdArvoreInsumos,inObj);

		vFormulas = getFormulas(inObj);
		hSelecao = "";
		$.each(vFormulas, function(io,o){
			hSelecao += `
			<div class="radio">
				<label class="hoverazul">
					<input type="radio" value="${io}" name="selecaoFormulaFormalizacao" idformula="${io}" title="#${io}" onclick="${inNomeFuncaoCallback}(this)">
					<i class="fa fa-1x fa-circle" style="color:${o.cor};"></i>
					${o.rotulo}
				</label>
			</div>`;
		});

		return hSelecao;
	}	

	/*
	* Recebe como parâmetro os insumos/objetos FILHO, que contém as possíveis fórmulas para produção
	* Uma função *externa* de callback será utilizado para o evento de clique/seleção da fórmula
	*/
	function seletorFormulaModal(inObj, inNomeFuncaoCallback)
	{
		vObj = inObj || jInsumos;
		hSeletor = htmlSeletorFormula(vObj, inNomeFuncaoCallback);
		
		$("#corpoFormalizacao").html(`<div class="alert alert-warning pointer" role="alert" onclick="seletorFormula()"><i class="fa fa-info-circle hoverazul"></i> Aguardando sele&ccedil;&atilde;o de F&oacute;rmula</a></div>`);
		$("#cbModalTitulo").html("Selecione a f&oacute;rmula para produ&ccedil;&atilde;o:");
		$("#cbModalCorpo").html(hSeletor);
		$('#cbModal').modal('show');

	}

	/*
	* Informada a chave idprodservformulains, esta função percorre recursivamente a árvore para encontrar o insumo superior
	*/
	function getInsumoPai(inKeyInsumo,jFilhos, iPai)
	{
		var oRetPai;
		$.each(jFilhos, function(i, o) {			
			if(i==inKeyInsumo){
				oRetPai = iPai;
				return false;
			}
			if(tamanho(o.insumos)){
				oRetPai = getInsumoPai(inKeyInsumo, o.insumos, i);
			}
		});

		return oRetPai;
	}

	//Recebe coleção de lotes (objetos) para extração de fórmulas selecionadas
	function getFormulasLotes(inLotes)
	{
		aFormulas=[];
		$.each(inLotes, function(i,o){
			if(o.idprodservformula){
				aFormulas.push(o.idprodservformula);
			}
		});
		return aFormulas;
	}

	function geraSolfab() 
	{
		idpessoa = $("[name=_2_u_lote_idpessoa]").cbval();
		idpessoa = idpessoa.length == 0 ? $('#lote_idprodservformula').val() : idpessoa; 		
		idlote = $("[name=_2_u_lote_idlote]").val();
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

		if (!idpessoa) {
			alertAtencao("Informe corretamente o Cliente!");
		} else {
			janelamodal(`?_modulo=solfab&_acao=i&idlote=${idlote}&idpessoa=${idpessoa}${idempresa}`);
		}
	}

	function alteraformulasel(vthis) 
	{
		//Altera o lote
		sPost = `&_lote_u_lote_idlote=${$_2_u_lote_idlote}&_lote_u_lote_idprodservformula=${$(vthis).val()}`

		CB.post({
			objetos: sPost
		})
	}

	function buscarSolfab(vthis) 
	{
		$('.div-formula').append(`<input type="hidden" name="novomodelo" value="Y"/><br/><input type="hidden" name="lote_u_lote_idprodservformula" value="${vthis.value}"/>`);
		preencherSolfab(vthis.value);
	}

	function mostraConsumo(inOConsumo) 
	{
		$oc = $(inOConsumo);

		$tbInsumo = $oc.closest("table");
		$trInsumo = $oc.closest("tr.trInsumo");
		$sQtdpadrao = $trInsumo.find(".sQtdpadrao");
		$sUtilizando = $trInsumo.find(".sUtilizando");
		$sRestante = $trInsumo.find(".sRestante");
		$sParticipacao = $trInsumo.find(".sParticipacao");
		somaUtilizacao = 0;
		$oConsumos = $trInsumo.find("[name*=_qtdd]");

		$.each($oConsumos, function(isc, osc) {
			var $o = $(osc);
			if ($o.val()) {

				if ($o.attr("cbqtddispexp") != "" && ($o.val().toLowerCase().indexOf("e") <= 0 && $o.val().toLowerCase().indexOf("d") <= 0)) 
				{
					alertAtencao("Valor inválido. <br> Inserir e ou d.");
					return false;
				}

				valor = $o.val().replace(/,/g, '.');
				valor = normalizaQtd(valor);

				somaUtilizacao += valor;
			}
		})

		qtdPadrao = normalizaQtd($sQtdpadrao.html());
		qtdUsando = normalizaQtd($(inOConsumo).val());
		valparcipacao = parseInt(Math.ceil((qtdUsando * 100) / qtdPadrao));

		idlote = $(inOConsumo).attr('idlote');

		if (valparcipacao > 0 && valparcipacao != 'Infinity') {
			$('#sParticipacao' + idlote).html(valparcipacao.toFixed(2) + '%');

		}

		if (parseInt(somaUtilizacao) >= parseInt(qtdPadrao)) {
			sclass = "fundoverde";
		} else {
			sclass = "fundolaranja";
		}

		if (somaUtilizacao > 0) 
		{
			//Formata o badge de 'utilizando'
			$sUtilizando
				.html(somaUtilizacao)
				.removeClass("fundoverde")
				.removeClass("fundolaranja")
				.addClass(sclass)
				.attr("title", (parseInt(Math.ceil(somaUtilizacao / qtdPadrao) * 100)) + "%");
		} else { //zero ou vazio
			//Formata o badge de 'utilizando'
			$sUtilizando
				.html(somaUtilizacao)
				.removeClass("fundoverde")
				.removeClass("fundolaranja")
				.attr("title", (parseInt(Math.ceil(somaUtilizacao / qtdPadrao) * 100)) + "%");
		}

		$sRestante
			.html(parseInt(qtdPadrao - somaUtilizacao))
			.removeClass("fundoverde")
			.removeClass("fundolaranja")
			.addClass(sclass);

		//Re-calcula placeholders com qtd restante
		atualizaPlaceholderSugestao($trInsumo);
	}

	var Formalizacao = function() 
	{
		//Armazenar para referenciar a classe Formalizacao dentro dos métodos
		selfer = this;

		selfer.debug = false;

		//Armazenar a estrutura de itens
		selfer.jEstrutura = {};

		selfer.localInsumo = false;

		//Este método deve ser chamado para retornar o html com a estrutura inteira da formalização
		selfer.getHtml = function() {

			if (tamanho(jLoteAtiv) == 0) {
				console.warn("Erro: Nenhuma Atividade foi gerada para o Lote informado!");
			}

			//Recupera o texto em html dos insumos que não estão relacionado à  nenhuma atividade
			hInsumosProduto = selfer.getHtmlInsumosProduto(jLoteAtiv);

			//Loop para montagem de cada Atividade
			hAtividades = "";
			selfer.jEstrutura.atividades = {};
			$.each(jLoteAtiv, function(i, o) {
				selfer.jEstrutura.atividades[o.idloteativ] = o;
				hAtividades += selfer.getHtmlAtividade(o);
			});
			hProdutosform = "";
			hProdutosform = selfer.getHtmlProdUtilizadosForm();
			hSementesProdutosform = "";
			hSementesProdutosform = selfer.getHtmlSementesForm();

			return hInsumosProduto + "\n" + hAtividades + "\n" + hProdutosform + "\n" + hSementesProdutosform;
		};

		//Variável global para gravar os lotes consumidos
		selfer.oLotesConsumidos = {};
		//Variável global para gravar os lotes especiais utilizados
		selfer.oLotesConsumidosEsp = {};

		//Variável global para possibilitar não repetir [name] para inputs type=radio
		selfer.grupoOptions = 1;
		//Variável global para gerar nomeclaturas de Atividades
		selfer.iAtividade = 100;

		//Variável global para controlar consumo
		selfer.iConsumo = 1000;

		//Armazenar a Fórmula selecionada (ou única cadastrada) pelo usuário.
		selfer.idprodservformula = $_2_u_lote_idprodservformula;

		//Controlar a quebra de página da impressão
		selfer.loteimpressao = false;
		var ctativ = 0;

		/****************************************************************
		 *            Div de cada Atividade da Formalização             *
		 ****************************************************************/
		selfer.getHtmlAtividade = function(inAtiv) {

			ctativ = ctativ + 1;

			//Monta o seletor de Salas
			hSeletorsalas = selfer.getHtmlSalas(inAtiv);

			//Recupera os insumos da atividade
			hInsumosAtividade = selfer.getHtmlInsumosAtividade(inAtiv.idprativ, inAtiv.idloteativ, inAtiv.ord);

			//Recupera os equipamentos disponíveis
			hEquipamentos = selfer.getHtmlEquipamentos(inAtiv);

			//Recupera os testes disponíveis
			hTestes = selfer.getHtmlTestes(inAtiv);

			//Recupera os Controles em Processo
			hContrProc = selfer.getHtmlContrProc(inAtiv);

			//Recupera os Materiais selecionados
			hMateriais = selfer.getHtmlMateriais(inAtiv);

			//Recupera as opções de impressão/conclusão
			var oOpcoes = selfer.getPrativopcoes(inAtiv.objetos);
			var vInicioTermino = oOpcoes["initerm"] ? `<label class="nowrap">Início:&nbsp;___/___/_______&nbsp;&nbsp;&nbsp;___:___&nbsp;&nbsp;&nbsp;&nbsp;Término:&nbsp;___/___/_______&nbsp;&nbsp;&nbsp;___:___</label>` : "";
			var vNomeTecnicoRealizado = oOpcoes["nometec"] ? `<label class="nowrap">Realizado por:&nbsp;___________________</label>` : "";
			var vNomeConferidoPor = oOpcoes["conferidopor"] ? `<label class="nowrap">Conferido por:&nbsp;___________________</label>` : "";
			var vObservacao = oOpcoes["obs"] ? `<div class="print evitaQuebraPagina"><label>Observação:</label><div class="observacao100"></div></div>` : "";
			var vProduto = oOpcoes["produto"] ? `<label class="">Produto:&nbsp;${jInfRotulo.descr}</label>` : "";
			var vCliente = oOpcoes["cliente"] ? `<label class="nowrap">Propriedade:&nbsp;${jInfRotulo.nome}&nbsp;CPF/CNPJ:&nbsp;${jInfRotulo.cpfcnpj||""}&nbsp;I.E.&nbsp;${jInfRotulo.inscrest||""}</label>` : "";
			var vEndereco = oOpcoes["endereco"] ? `<label class="nowrap">Endereco:&nbsp;${jInfRotulo.enderecosacado||""}</label>` : "";
			var vVencimento = oOpcoes["vencimento"] ? `<label class="nowrap">Vencimento:&nbsp;${jInfRotulo.vencimento}</label>` : "";

			var vBioensaio = "";
			if (oOpcoes["bioterio"]) {
				hResultadosBiensaio = selfer.getHtmlResultadosBioensaio(inAtiv.objetos);
				vBioensaio = `
					<div>
						<div class='papel hover screen' >
							<h6 class="cinza bold">Biotério:</h6>
							<hr>
							<div>${hResultadosBiensaio}</div>
						</div>
					</div>`;

			}
			var vRotulo = "";
			if (oOpcoes["rotulo"]) 
			{
				vRotulo = `
					<div>
						<div class='papel hover screen' >
							<h6 class="cinza bold">Gerar Rótulo:</h6>
							<hr>
							<div class="checkbox checked">
								<a target="_blank" href="?_modulo=rotulolote&idlote=${$_2_u_lote_idlote}" title="Gerar Rotulo">Rótulo</a>
							</div>
						</div>
					</div>`;
			}
			//Recupera listagens conforme opções selecionadas no cadastro do processo
			hListaInsumos = oOpcoes.insumos ? selfer.getHtmlQuadroOpcoes(inAtiv, 'insumos') : "";
			hListaSementes = oOpcoes.sementes ? selfer.getHtmlQuadroOpcoes(inAtiv, 'sementes') : "";
			hListaFP = oOpcoes.fp ? selfer.getHtmlQuadroOpcoes(inAtiv, 'fp') : "";

			var loteativdisabled = '';

			statusLoteAtiv = "";
			statusLoteAtibBotao = "";
			sPost = "";
			sOpstatus = "";
			let informacao = "";
			if (inAtiv.status == "CONCLUIDO") {
				var loteativdisabled = 'disabled="disabled"';
				statusLoteAtiv = 'CONCLUIDO';
			} else if (inAtiv.status == "PENDENTE") {
				statusLoteAtiv = 'PROCESSANDO';
				statusLoteAtibBotao = 'PROCESSAR';
			} else if (inAtiv.status == "PROCESSANDO") {
				statusLoteAtiv = 'CONCLUIDO';
				statusLoteAtibBotao = 'CONCLUIR';
			}

			// se a atividade for atribuida a um setor somente pessoas do setor podem imprimir a atividade
			if (inAtiv.idsgareasetor === 'undefined' || inAtiv.idsgareasetor === null) {
				var ImpAtiv = true;
				var SgAreainAtiv = false;
			} else {
				var SgAreainAtiv = true;

				function verificasgarea(jSgareasetor, Atividsgareasetor) {

					for (i = 0; i < jSgareasetor.length; i++) {
						if (jSgareasetor[i] == Atividsgareasetor)
							return true;
					}
					return false;
				}
				var ImpAtiv = verificasgarea(jSgareasetor, inAtiv.idsgareasetor);
			}

			vModoImpressaoVisualizacao = ($_1_u_formalizacao_status == "ABERTO" || $_1_u_formalizacao_status == "AGUARDANDO" || $_1_u_formalizacao_status == "FORMALIZACAO") ? "screen" : "";
			vModoImpressaoVisualizacao = (ImpAtiv == false && $_1_u_formalizacao_status == "PROCESSANDO") ? " screen opacity04" : vModoImpressaoVisualizacao;
			//Controla a quebra de linha da impressão
			var pageBreak;
			if (selfer.loteimpressao !== inAtiv.loteimpressao && selfer.loteimpressao) {
				pageBreak = "pagebreakbefore";
			} else {
				pageBreak = "";
			}
			selfer.loteimpressao = inAtiv.loteimpressao;

			if (SgAreainAtiv == true) {
				var corimp = inAtiv.impresso == "Y" ? "verde" : "vermelho";
			} else {
				var corimp = inAtiv.impresso == "Y" ? "verde" : "cinza";
			}

			var verificatagreserva = `onchange="verificaTagReserva(${inAtiv.idloteativ},${selfer.iAtividade},'${inAtiv.travasala}');"`;

			if ($_1_u_formalizacao_status == "QUARENTENA") {
				var obnulo = "";
			} else {
				var obnulo = "";
			}

			if (inAtiv.duracao == '00:00:00') {
				inAtiv.duracao = '';
			}
			if (inAtiv.idloteativ == inAtiv.idloteativConcluir && statusLoteAtiv == 'CONCLUIDO') {
				var vfnalizaop = `finalizaop('CONCLUIDO',${inAtiv.idloteativ})`;
			} else {
				var vfnalizaop = `alteraStatusAtiv(${inAtiv.idloteativ}, '${statusLoteAtiv}', '${inAtiv.bloquearstatus}')`;
			}

			if ((inAtiv.status != "CONCLUIDO" && (inAtiv.statusFormalizacao == 'ATIVO' || inAtiv.statusFormalizacao == 'PENDENTE')) || ((inAtiv.status == 'PENDENTE' || inAtiv.status == 'PROCESSANDO') && inAtiv.idfluxostatus == null)) {
				let desabilitado = "";
				if (inAtiv['salas'][inAtiv['salas']['selected']] == undefined) {
					desabilitado = 'disabled';
					informacao = ` onclick='alertAtencao("Selecionar a Sala, antes de prosseguir!")'`;
				}
				sOpstatus = `<button id="statusatividade" onclick="${vfnalizaop}" type="button" ${desabilitado} ${informacao} class="btn btn-primary btn-xs"><i class="fa fa-refresh"></i>${statusLoteAtibBotao}</button>`;
			}

			//LTM - 04/05/2021 - Esconder as atividades que não está no histórico
			if (inAtiv.statusFormalizacao == 'ATIVO' || inAtiv.statusFormalizacao == 'PENDENTE' || (inAtiv.status == 'PENDENTE' && inAtiv.idfluxostatus == null)) {
				var escondediv = '';
				var nodrop = '';
			} else {
				var escondediv = 'escondeatividade';
				var nodrop = 'nodrop';
				var loteativdisabled = 'disabled="disabled"';
			}
			var inicioFim = '';
			//Esconder o Inicio e Fim a partir de 07/07/2021

			if (inAtiv.execucao) {
				inicioFim = `<label class="screen">Início:</label> 
							<label class="screen"> ${dmahm(inAtiv.execucao)}</label>
							<label class="screen" style="padding-left: 15px;">Fim:</label> 
							<label class="screen" style="padding-right: 15px;"> ${dmahm(inAtiv.execucaofim)}</label>`;
			}

			//Monta o DIV da Atividade
			sAtiv = `
			<div class='panel panel-default loteativ loteativ_order ${vModoImpressaoVisualizacao} ${pageBreak} ${escondediv}' loteativ_order="${inAtiv.loteimpressao}"  idloteativ="${inAtiv.idloteativ}" style=" min-width: 21cm; order:${inAtiv.loteimpressao};  ">
				<div class="${nodrop}">
					<div class='panel-heading ${statusloteativ(inAtiv.status)}' title="idloteativ: ${inAtiv.idloteativ}">
						<div class="print tituloAtividade">[${ctativ}/${Object.keys(jLoteAtiv).length}] - ${inAtiv.nomecurtoativ}</div>
						<table style="width: 100%;">
							<tr>
								<td class="screen col-md-5">[${ctativ}/${Object.keys(jLoteAtiv).length}] - ${inAtiv.nomecurtoativ}</td>
								<td class="form-group form-inline text-right col-md-4">
									<table>
										<tr>
											<td>
												<input name="_${selfer.iAtividade}_u_loteativ_idloteativ" type="hidden" value="${inAtiv.idloteativ}">
												${inicioFim}
											</td>	
											<td><label>Sala:</label></td>
											<td>${hSeletorsalas}</td>
										</tr>
									</table>
								</td>
								<td class="col-md-4">
									<table style="width:100%">
										<tr>
											<td><a class="fa fa-calendar pointer hoverazul" title="Calendário" onclick="janelamodal('?_modulo=calendario')"></a></td>
											<td> <span id="msgbox${selfer.iAtividade}" style="display:none"></span></td>
											<td><i class="fa fa-print ${corimp}" onclick="imprimir()"></i></td>
											<td  align="right">
												<table>
													<tr>
														<td ${informacao}>
															${sOpstatus}
														</td>
														<td><label class="statusativ screen">Status:</label></td>
														<td><label class="alert-warning" id="statusatividade">${inAtiv.status}</label></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class='panel-body' id='${inAtiv.idprativ}'>
					${hInsumosAtividade}
					${hEquipamentos}
					${hTestes}
					${hContrProc}
					${hMateriais}
					${hListaInsumos}
					${hListaSementes}
					${hListaFP}
					<div class="print evitaQuebraPagina">
						<table>
							<tr>
								<td>${vProduto}</td>
							</tr>
							<tr>
								<td>${vCliente}</td>
							</tr>
							<tr>
								<td>${vEndereco}</td>
							</tr>
							<tr>
								<td>${vVencimento}</td>
							</tr>
						</table>
					</div>
					${vBioensaio}
					${vRotulo}
					<div class="print evitaQuebraPagina">
						<table>
							<tr>
								<td>${vInicioTermino}</td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>${vNomeTecnicoRealizado}&nbsp;&nbsp;${vNomeConferidoPor}</td>
								<td></td>
								<td></td>
							</tr>			
						</table>
					</div>
					${vObservacao}
				</div>
			</div>`;

			selfer.iAtividade++;

			return sAtiv;
		};


		//Isto será executado antes de desenhada a formalização, para permitir aproveitar as informações de toda a formalização durante a montagem do html
		selfer.getAtividadesInsumos = function() {
			var oProdForm = {};

			//Produtos que não estão vinculados à  atividades
			oProdForm["0#"] = {};
			oProdForm["0#"]["localconsumo"] = "formalizacao";
			oProdForm["0#"]["insumos"] = formalizacao.getInsumosFormalizacao();

			//Loop nas atividades na ordem em que aparecem na impressao, para recuperar insumos ligados à  atividades
			$.each(jLoteAtiv, function(ia, oa) {
				oProdForm[ia] = oProdForm[ia] || {};
				oProdForm[ia]["insumos"] = oProdForm[ia]["insumos"] || {};
				$.each(formalizacao.getInsumosAtividade(oa.idprativ) || {}, function(iins, oins) {
					if (oins.idprodservformula == $_2_u_lote_idprodservformula) {
						oProdForm[ia]["localconsumo"] = "loteativ";
						oProdForm[ia].ativ = oa.ativ;
						oProdForm[ia].idloteativ = oa.idloteativ;
						oProdForm[ia]["insumos"]["#" + oins.idprodservformulains] = oProdForm[ia]["insumos"]["#" + oins.idprodservformulains] || {};
						oProdForm[ia]["insumos"]["#" + oins.idprodservformulains] = jInsumos["#" + oins.idprodservformulains];
					}
				});
			})

			return oProdForm;
		};

		selfer.getAtividadesInsumosConsumos = function() {
			//Recupera as atividades relacionadas aos insumos
			var oProdutosForm = selfer.getAtividadesInsumos();

			//Neste ponto estarão listados todos os produtos, independente se estão listados diretamente na formalização (#0) ou dentro de alguma atividade (#idloteativ)
			$.each(oProdutosForm, function(iof, oof) {
				//Recupera os estoques disponíveis
				$.each(oof.insumos, function(ii, oi) {
					oProdutosForm[iof]["consumosformalizacao"] = oProdutosForm[iof]["consumosformalizacao"] || {};
					oProdutosForm[iof]["consumosformalizacao"][oi.descr] = formalizacao.getObjEstoque(oi, oof.localconsumo, oof.idloteativ);
				});

			})
			return oProdutosForm;
		};

		selfer.getHtmlProdUtilizadosForm = function() {
			var sProdutos;
			if (tamanho(selfer.oLotesConsumidos) > 0) {
				sProdutos = `
					<div class='panel panel-default loteativ pagebreakbefore'>
						<div class='panel-heading' title="Produtos Utilizados">
							<div >Produtos Utilizados</div>				
						</div>
						<div class='panel-body'>`;
							$.each(selfer.oLotesConsumidos, function(i, o) {
								sProdutos += `<div class="checkbox checked">${o.oLotecons.qtdd}-${o.descr}</div>`;
							});
						sProdutos += `</div>
					</div>`;
			} else {
				sProdutos = " "
			}

			return sProdutos;
		}

		selfer.getHtmlSementesForm = function() {
			if (tamanho(selfer.oLotesConsumidosEsp) > 0) {
				slotecons = `
				<div class='panel panel-default loteativ pagebreakbefore'>
					<div class='panel-heading' title="Sementes dos Produtos Utilizados">
						<div>Sementes dos Produtos Utilizados</div>				
					</div>
					<div class='panel-body'>`;
						$.each(selfer.oLotesConsumidosEsp, function(i, o) {
							slotecons += `<div class="checkbox checked">${o.oLotecons.qtdd}-${o.descr}</div>`;
							slotecons += `<div class="checkbox checked">${o.semente}</div>`;
						});
					slotecons += `</div>
				</div>`;
			} else {
				slotecons = " "
			}
			return slotecons;
		}

		//Separa as TAGs de classe SALA
		selfer.getSalas = function(inPrAtivObjetos) {
			//Separa os tipo de objeto sala
			$osalas = {}; //Abre novo objeto
			$.each(inPrAtivObjetos, function(i, o) {
				if (o.idtagclass && o.idtagclass == "2") {
					//Recupera as tags disponíveis
					$.each(o.subitens || {}, function(i, s) {
						$osalas[i] = s;
					})
				}
			});
			return $osalas;
		};

		//Monta o HTML das salas disponíveis
		selfer.getHtmlSalas = function(inPrAtiv) {
			prAtiv = inPrAtiv; //Instanciar o parà¢metro para o contexto
			$salas = selfer.getSalas(prAtiv.objetos);

			//Monta o Html de cada sala, verificando se vai haver Insert ou Update na loteobj
			sOpcoes = "";
			ii = 0;

			vidloteobj = "";
			areserva = "";
			vacao = "i";
			selfer.jEstrutura.atividades[prAtiv.idloteativ].salas = {};
			$.each($salas, function(i, o) {
				selfer.jEstrutura.atividades[prAtiv.idloteativ].salas[o.idtag] = o;
				selfer.jEstrutura.atividades[prAtiv.idloteativ].salas[o.idtag].loteobj = evalJson(`jLoteObj["tag"][${o.idtag}][${prAtiv.idloteativ}]`) || {};
				ii++;
				vselected = "";
				//Verifica se a Sala existe na loteobj
				vexiste = evalJson(`jLoteObj["tag"][${o.idtag}][${prAtiv.idloteativ}].idloteobj`) || "";

				if (vexiste) {
					selfer.debug && console.warn("Sala tag " + o.idtag + " selecionada");
					vidloteobj = evalJson(`jLoteObj["tag"][${o.idtag}][${prAtiv.idloteativ}].idloteobj`);
					vacao = "u";
					vselected = "selected";
					selfer.jEstrutura.atividades[prAtiv.idloteativ].salas.selected = o.idtag;
					areserva = `<td><a class="fa fa-bars pointer hoverazul" title="Reservas da Sala" onclick="janelamodal('report/reservatag.php?idtag=${o.idtag}')"></a></td>`;
				}
				sOpcoes += `<option value="${o.idtag}" ${vselected}>[${o.sigla}-${o.tag}] - ${o.descricao}</option>`;
			});

			var verificatagreserva = `verificaTagReserva(${prAtiv.idloteativ},${selfer.iAtividade},'${prAtiv.travasala}');`;

			if (($_1_u_formalizacao_status == "ABERTO" || $_1_u_formalizacao_status == "AGUARDANDO") && $_1_u_formalizacao_status == "PRODUTO") {
				var desabsala = "disabled='disabled'";
			} else {
				var desabsala = "";
			}

			if (prAtiv.status == 'CONCLUIDO') {
				statusATiv = 'disabled';
			} else {
				statusATiv = '';
			}

			hSala = `
				<input name="_${prAtiv.idloteativ}sala_${vacao}_loteobj_idloteobj" type="hidden" value="${vidloteobj}">
				<input name="_${prAtiv.idloteativ}sala_${vacao}_loteobj_idlote" type="hidden" value="${$_2_u_lote_idlote}">
				<input name="_${prAtiv.idloteativ}sala_${vacao}_loteobj_idloteativ" type="hidden" value="${prAtiv.idloteativ}">
				<input name="_${prAtiv.idloteativ}sala_${vacao}_loteobj_tipoobjeto" type="hidden" value="tag">
				<select ${desabsala} ${statusATiv} idloteobj="${vidloteobj}" name="_${prAtiv.idloteativ}sala_${vacao}_loteobj_idobjeto" class="form-control selectsala ${$_1_u_formalizacao_status}" onchange="alteracaoSala(this);" style="width: 380px !important;">
					<option value=""></option>
					${sOpcoes}
				</select>
					${areserva}
			`;

			return hSala;
		};

		//Separa as TAGs de classe EQUIPAMENTO
		selfer.getEquipamentos = function(inPrAtivObjetos) {
			//Separa os tipo de objeto equipamentos
			$oTipoTagEquips = {}; //Abre novo objeto
			$.each(inPrAtivObjetos, function(i, o) { //Loop pelo idprativobj
				if (o.idtagclass && o.idtagclass == "1") 
				{
					//Recupera as tags disponíveis
					if (o.subitens) {
						$.each(o.subitens, function(i, s) {
							$oTipoTagEquips[s.idtagtipo] = ($oTipoTagEquips[s.idtagtipo]) ? $oTipoTagEquips[s.idtagtipo] : {};
							$oTipoTagEquips[s.idtagtipo][s.idtag] = s;
							$oTipoTagEquips[s.idtagtipo][s.idtag]["idprativobj"] = i;
						})
					}
				}
			});
			return $oTipoTagEquips;
		};

		//Monta o HTML dos Equipamentos disponíveis
		selfer.getHtmlEquipamentos = function(inAtiv) {
			prAtiv = inAtiv; //Instanciar o parámetro para o contexto
			$oTipoTagEquips = selfer.getEquipamentos(prAtiv.objetos);

			//Monta o Html agrupando os equipamentos conforme o idtipotag. Isto permite separar visualmente os tipos de objeto diferentes
			sOpcoes = "";
			ii = 0;
			sDivisor = "";

			vacao = "i";
			selfer.jEstrutura.atividades[prAtiv.idloteativ].equipamentos = {};
			$.each($oTipoTagEquips, function(tt, e) {

				oE = e;
				sOpcoes += `${sDivisor}`;
				bMarcaPrimeiro = false;
				vchecked = "";
				sOpcoes += "<div class='equipamentos'>";

				selfer.jEstrutura.atividades[prAtiv.idloteativ].equipamentos[tt] = {};
				//Loop nos Equipamentos disponíveis para montar o HTML
				$.each(oE, function(i, o) {
					ii++;
					var vidloteobj = "";
					eLoteObj = evalJson(`jLoteObj["tag-EQUIPAMENTO"][${o.idtag}][${prAtiv.idloteativ}]`);
					selfer.jEstrutura.atividades[prAtiv.idloteativ].equipamentos[tt][o.idtag] = o;
					selfer.jEstrutura.atividades[prAtiv.idloteativ].equipamentos[tt][o.idtag].loteobj = evalJson(`jLoteObj["tag-EQUIPAMENTO"][${o.idtag}][${prAtiv.idloteativ}]`) || {};
					let vescondeCheckbox = 'escondeCheckbox';
					//Verifica se o Equipamento existe na loteobj
					if (eLoteObj) {
						selfer.debug && console.warn("Equip tag " + o.idtag + " selecionado");
						vidloteobj = evalJson(`jLoteObj["tag-EQUIPAMENTO"][${o.idtag}][${prAtiv.idloteativ}].idloteobj`);
						vidloteobj = ("" + vidloteobj).length == 0 ? o.idtag : vidloteobj;
						vacao = "u";
						vchecked = "checked";
						vescondeCheckbox = "";
					}

					//Esconde equipamentos de outras salas
					if (o.idtag_pais != null) {
						vHidden = o.idtag_pais.split("#").indexOf(selfer.jEstrutura.atividades[prAtiv.idloteativ].salas.selected) >= 0 ? "" : "hidden";
					} else {
						vHidden = "hidden";
					}

					//LTM - 22-10-2020: Alterado para quando processando aparecendo apenas os equipamentos que foram checkados
					if ('<?=$_1_u_formalizacao_status ?>' == 'PROCESSANDO' && eLoteObj != null && o.idtag_pais.split("#").indexOf(selfer.jEstrutura.atividades[prAtiv.idloteativ].salas.selected) >= 0) {
						vHidden = "";
					} else if ('<?=$_1_u_formalizacao_status ?>' == 'PROCESSANDO' && eLoteObj == null) {
						vHidden = "hidden";
					}

					if (prAtiv.status == 'CONCLUIDO') {
						statusATiv = 'disabled';
					} else {
						statusATiv = '';
					}

					//Monta o HTML do input
					sOpcoes += `
							<div class="checkbox ${vHidden} ${vchecked} ${vescondeCheckbox}" inputmanual="${o.inputmanual||""}">
								<label>
									<input type="checkbox" ${statusATiv} name="_${prAtiv.idloteativ}equip${selfer.grupoOptions}" idloteobj="${vidloteobj}" idobjeto="${o.idtag}" idlote="${$_2_u_lote_idlote}" idprativ="${prAtiv.idprativ}" idloteativ="${prAtiv.idloteativ}" tipoobjeto="tag-EQUIPAMENTO" idtagtipo="${o.idtagtipo}" idtagpai="${o.idtagpai}" idtag_pais="${o.idtag_pais}" value="${o.idtag}" ${vchecked} title="#${o.idtag}" onchange="toggleCheckbox(this,'${prAtiv.status}')">
									${o.descricao} - TAG ${o.tag}
									<div class="print" inputmanual="${o.inputmanual||""}"></div>
								</label>
							</div>
					`;
					vchecked = "";
				});
				sOpcoes += "</div>";
				sDivisor = "<hr class='screen'>";
				selfer.grupoOptions++; //Incrementa por grupo para propriedade [name] do input
			});

			if (ii > 0) {
				sOpcoes = `
					<div class="col-md-3">
						<div class='papel hover quadroOpcoes' id=formTra>
							<h6 class="cinza bold">Equipamentos:</h6>
							<hr class="screen">
							${sOpcoes}
						</div>
					</div>`;

				return sOpcoes;
			} else {
				return "";
			}
		};

		//HTML com os insumos que não estão relacionados à  nenhuma Atividade
		selfer.getHtmlInsumosProduto = function(jLoteAtiv) {

			//Recupera os insumos nessa condição
			oInsumos = selfer.getInsumosFormalizacao();

			if (tamanho(oInsumos) == 0) {
				strret = "selfer.getHtmlInsumosProduto: Nenhum insumo encontrado ou todos os insumos possuem và­nculo com Atividades";
				selfer.debug && console.log(strret);
				return "<!-- " + strret + "-->";
			} else {

				hTr = "";
				selfer.jEstrutura.insumosformalizacao = {};

				$.each(oInsumos, function(i, o) {
					selfer.jEstrutura.insumosformalizacao[i] = o;
					hTr += selfer.getHtmlInsumo(o, "formalizacao", $_2_u_lote_idlote, jLoteAtiv.ord);
				});

				hTbInsumos = ` `;
				return hTbInsumos;
			}
		};

		selfer.htmlCabinsumo = `
			<tr class="trcabecalho">
				<th>Qtd. Padrão</th>
				<th class="screen"><span class="rotpadrao">${strlut}</span></th>
				<th class="screen"><span class="rotpadrao">Restante</span></th>	
				<th>Un</th>
				<th>Insumo</th>
				<th class="screen"></th>
				<th colspan="100">${strlt}</th>
			</tr>`;

		selfer.montaItemHtmlInsumo = function(inobj, inLocalConsumo, idLocalConsumo) {
			obj = inobj;

			var strIdobjetosolipor = "";
			var partidasSolfab;
			var hInsumosEspeciais = "";

			//Recupera os produtos especiais vinculados
			if (inobj.especial == "Y") {
				partidasSolfab = selfer.getInsumosSolfab(inobj);
				var hInsumosEspeciais = "";
				var sBr = "";

				//altera o objeto para um array ordenado
				var arrpartidasSolfab = Object.values(partidasSolfab).sort(function(a, b) {
					return a.idpool - b.idpool
				})
				//retorna o array para objeto						
				partidasSolfab = Object.assign({}, arrpartidasSolfab);
				var vidpool = '';
				var sempool = '';
				var sBr = "&nbsp;";
				$.each(partidasSolfab, function(ip, op) {

					if (op.status == "APROVADO" && op.statussemente != "ESGOTADO") {
						var idel = '';
						var fdel = '';
					} else {
						var idel = '<del>';
						var fdel = '</del>';
					}

					if (op.flgalerta == 'P' || op.flgalerta == 'R') {
						var ialerta = ' * ';
					} else {
						var ialerta = '';
					}

					if (op.idpool > 0) {
						if (vidpool > 0 && vidpool != op.idpool) {
							hInsumosEspeciais += `</div>`;
							hInsumosEspeciais += `<div style='border: 1px silver dotted; background-color: #ffffffd6; width: fit-content;padding: 0px 4px;float: left; margin-right: 2px;'>`;
							var divaberto = 'Y';

						} else if (vidpool == '') {
							var divaberto = 'Y';
							hInsumosEspeciais += `<div style='border: 1px silver dotted; background-color: #ffffffd6; width: fit-content;padding: 0px 4px;float: left; margin-right: 2px;'>`;
						}
						vidpool = op.idpool;
						sempool = 'N';
					} else if (vidpool > 0 && op.idpool < 1) {
						vidpool = '';
						var divaberto = 'Y';
						hInsumosEspeciais += `</div>`;
						hInsumosEspeciais += `<div style='border: 1px silver dotted; background-color: #ffffffd6; width: fit-content;padding: 0px 4px;float: left; margin-right: 2px;'>`;
						sempool = 'N';
					} else {
						sempool = '';
						hInsumosEspeciais += `<div style='border: 1px silver dotted; background-color: #ffffffd6; width: fit-content;padding: 0px 4px;float: left; margin-right: 2px;'>`;
					}

					hInsumosEspeciais += `${sBr}			
						<a href="javascript:janelamodal('?_modulo=semente&amp;_acao=u&idlote=${op.idlote}')"  class="nowrap fonte08">
							<i class="fa fa-star laranja bold" title=" ${op.partida} ${op.tipificacao} ${op.orgao} ${ialerta}"></i>
							${idel} ${op.partida.replace(op.codprodserv,"")} ${fdel}
						</a>`;
					if (sempool == '') {
						hInsumosEspeciais += `</div>`;
					}
					sBr = "&nbsp;";
				})
				hInsumosEspeciais = `<div class="insumosEspeciais screen">${hInsumosEspeciais}</div>`;
			}

			//Recupera o estoque
			var oEstoque = selfer.getObjEstoque(obj, inLocalConsumo, idLocalConsumo);
			hEstoque = oEstoque.html;

			if (obj) 
			{
				iqtdsoli = calculaEstoqueProduzir(obj);
				iqtdpadrao = recuperaExpoente((($_2_u_lote_qtdajust * obj.qtdpadrao) / jprodFormula.qtdpadrao), obj.qtdpadrao_exp);

				pedFilho = ($_2_u_lote_qtdajust * obj.qtdi) / jprodFormula.qtdpadrao;
				vRestante = parseInt(pedFilho) - oEstoque.totalUtilizacao;
				var geraform = obj.fabricado == "N" ? "hidden" : "";
				tmpTr = `
				<tr id="tr${obj.idprodservformulains}" class="trInsumo" idprodserv="${obj.idprodserv}">
					<td><span class=" badgepadrao sQtdpadrao" id="iqtdpadrao${obj.idprodserv}">${pedFilho.toFixed(2)}</span></td>
					<td class="screen"><span class="badge sUtilizando" id="iqtdutilizando${obj.idprodserv}">${oEstoque.totalUtilizacao}</span></td>
					<td class="screen"><span class="badge sRestante" id="iqtdrestante${obj.idprodserv}">${vRestante.toFixed(2)}</span></td>
					<td >${obj.un}</td>
					<td class="nowrap">
						<a class="screen" href="javascript:janelamodal('?_modulo=prodserv&_acao=u&idprodserv=${obj.idprodserv}')" >
							${obj.descr}
						</a>
						<a class="print" href="javascript:janelamodal('?_modulo=prodserv&_acao=u&idprodserv=${obj.idprodserv}')" >
							${obj.descrcurta}
						</a>
						${hInsumosEspeciais}
					</td>
					<td class="screen">
						<a class="fa fa-plus-circle pointer fade hoververde fa-2x ${geraform}" href="javascript:gerarFormalizacao('${iqtdsoli}','${obj.qtdpadrao_exp}',${obj.idprodserv},${$_2_u_lote_idlote},'${obj.descr}',${obj.idprodservformulains},'${strIdobjetosolipor}','${obj.fabricado}')"></a>
					</td>
					${hEstoque}
				</tr>
				`;
				return tmpTr;
			} else {
				return false;
			}
		};

		/*
		 * O primeiro parà¢metro é o insumo
		 * O segundo é a localização dos lotes na formalização (fora de atividades ou relacionados à  atividade), para associação de consumo
		 * Isto porque um mesmo insumo pode ser consumido em várias atividades
		 */
		selfer.getHtmlInsumo = function(o, inLocalConsumo, idLocalConsumo) {

			var hTr = "";

			hTr = selfer.montaItemHtmlInsumo(o, inLocalConsumo, idLocalConsumo);

			return hTr;
		};

		/*
		 * inLocalConsumo: "loteativ" ou "formalizacao"
		 */
		selfer.getObjEstoque = function(o, inLocalConsumo, idLocalConsumo) {
			sInsumos = "";
			objInsumoEstoque = o;

			//Informa no consumo se é produto especial
			vLocalConsumo = (objInsumoEstoque.especial == "Y") ? inLocalConsumo + "especial" : inLocalConsumo;

			var iiOcultar = 0;
			var sHidden = "";
			var sToggleVisiveis = "";
			var vQtdUtilizacao = 0;
			var oConsumos = {};

			var vEstoque = o.estoque;

			if (tamanho(vEstoque) == 0) {
				selfer.debug && console.warn("getObjEstoque: Objeto não possui estoque");
				return {
					html: "<td><div class='checkbox checked' inputmanual='linha'><label class='preto'><div class='print' inputmanual='linha'></div></label></div></td>",
					totalUtilizacao: ""
				};
			} else {
				//Caso exista uma solicitação de fabricação, recuperar as partidas do cliente
				if ($_2_u_lote_idsolfab.length >= 1 && objInsumoEstoque.especial == "Y") {
					vLotesSolfab = selfer.getInsumosSolfab(objInsumoEstoque);
				}

				/**************************************************************************************************
												Loop em cada item de estoque
				**************************************************************************************************/
				var vutlizacao = 'N';
				var sttdinsumo = `<div class='checkbox checked' style="float: left;display: inline-block;" inputmanual='linha'>
									<span class='print'>${o.codprodserv}</span>
									<label class='preto'><div class='print' inputmanual='linha'></div></label>
								</div>
								<div class='checkbox checked' style="display: inline-block;" inputmanual='linha'>
									<span class='print'>/</span>
									<label class='preto'><div class='print' inputmanual='linha'></div></label>
								</div>`;
				var sttdqtd = "";
				$.each(vEstoque, function(i, oe) {
					iiOcultar++;
					selfer.iConsumo++;
					oEstoque = oe;

					var cEsconderLoteDaUtilizacao = "";
					var cEspecial = "";
					var cEspecialVisivel = "";
					var oConsumosEspeciaisEstrela = {};
					var oConsumosEspeciaisInvalidosParaSolFabAtual = {};

					//Se houver Solicitação de fabricação, somente devem aparecer aqui lotes que têm o mesmo idprodserv+idlote da solicitação de fabricação. Caso contrário esconder
					if ($_2_u_lote_idsolfab.length >= 1 && objInsumoEstoque.especial == "Y" && tamanho(vLotesSolfab) >= 1) {
						//Primeiro verifica-se se o insumo em questão existe DIRETAMENTE na solicitação de fabricação
						vProdservExisteSolfab = false;
						$.each(vLotesSolfab, function(ils, olsf) {
							if (olsf.idprodserv == objInsumoEstoque.idprodserv) {
								vProdservExisteSolfab = true;
								return false; //sai do each
							}
						})

						//O primeiro caso é quando o insumo em questão utiliza insumos especiais que estão contidos na Solicitação de Fabricação
						if (!vProdservExisteSolfab) {

							/*
							 * Após verificar que o insumo em questão NÃO está diretamente associado na Solicitação de fabricação,
							 * deve-se realizar um Loop para verificar em cada insumo, se o idprodserv dele está contido na Solicitação de Fabricação
							 */
							var mostraLoteEspecial = false;
							$.each(objInsumoEstoque.insumos || {}, function(ii, ei) {
								var insumoAtualPossuiInsumoEspecialNaSolfab = false;

								$.each(vLotesSolfab, function(ilsf, olsf) {
									if (olsf.idprodserv == ei.idprodserv) insumoAtualPossuiInsumoEspecialNaSolfab = true;
								});

								if (insumoAtualPossuiInsumoEspecialNaSolfab) 
								{
									//Comparar cada lote encontrado, para filtrar quais foram consumidos pelo insumo superior
									$.each(ei.estoque, function(ie, ee) {
										vEe = ee;

										//Loop em todos os consumos do insumo em questão, para verificar se foi utilizado
										$.each(ee.consumosdolote[vLocalConsumo] || "", function(ic, c) {
											//Se foi consumido no lote em questão
											if (c.tipoobjeto == "lote" && c.idobjeto == oe.idlote && c.qtdd !== null && c.qtdd !== 0) {

												oConsumosEspeciaisEstrela[c.idlote] = vEe;
												//Neste ponto temos todos os consumos dos insumos especiais deste insumo. Se o lote do consumo não existir na solicitação de fabricação significa que ele não pode ser mostrado
												if (tamanho(vLotesSolfab[c.idlote]) == 0) {
													mostraLoteEspecial = false;
													oConsumosEspeciaisInvalidosParaSolFabAtual[c.idlote] = vEe;
												} else {
													mostraLoteEspecial = true;
													cEspecialVisivel = "especialvisivel";
												}
											} else {
												//iiOcultar--;
												//v="hidden";
												mostraLoteEspecial = false;
											}
										});
									})
								}
							});

							if (mostraLoteEspecial == true) 
							{
								iiOcultar--;
							}

							//O segundo caso é quando o LOTE do insumo em questão está contido na Solicitação de Fabricação, 
						} else if (vProdservExisteSolfab) {
							//Neste ponto o idprodserv do insumo existe na Solfab. Portanto deve-se comparar o lote, mostrando somente lotes que estão inclusos na Solfab
							if (tamanho(vLotesSolfab[oe.idlote]) >= 1) {
								cEsconderLoteDaUtilizacao = "";
								cEspecialVisivel = "especialvisivel";
							} else {
								iiOcultar--;
								cEsconderLoteDaUtilizacao = "hidden";
							}
						} else {
							iiOcultar--;
							cEsconderLoteDaUtilizacao = "hidden";
						}
					}

					oe.qtd = (oe.qtd) ? oe.qtd : 0; //Evita undefined||null oriundo da tabela

					//Verifica se realizará um insert ou update do consumo relacionado. Deve existir somente 1 neste caso
					oLotecons = selfer.getConsumoDoLote(oe, vLocalConsumo, idLocalConsumo);

					var vescondeobj = "Y";

					if (tamanho(oLotecons) > 0) {
						//Foi encontrado 1 (e somente 1) consumo para o lote em questão
						acaoCons = "u";
						vQtdInput = recuperaExpoente(oLotecons.qtdd, oLotecons.qtdd_exp) || "";
						vQtd = oLotecons.qtdd;
						vIdlotecons = oLotecons.idlotecons;

						//Objeto contendo somente os estoques consumidos
						if (oLotecons.qtdd && oLotecons.qtdd !== "" && oLotecons.qtdd !== 0) {
							oConsumos[oe.idlote] = oLotecons;
						}
					} else {
						acaoCons = "i";
						vQtd = "";
						vQtdInput = "";
						vIdlotecons = "";
					}
					//SE O LOTE FOI CONSUMIDO MAS ESTA ESGOTADO DEVE APARECER hermesp 16-10-2017
					if (vQtd > 0 || oe.status != 'ESGOTADO') {
						vQtdUtilizacao += parseInt(vQtd) || null; //trazer somente o qtdd
						//se o lote não foi consumido na formalização posso estotar o mesmo
						if (vQtd > 0) {
							vescondeobj = "N";
						}

						if (vQtd > 0) {

							sttdinsumo = "";
							sttdqtd = "";
						}
						//Quantidade disponà­vel em estoque
						vQtddisp = recuperaExpoente(oe.qtddisp, oe.qtddisp_exp);

						//Monta estrelas (lotes especiais)
						var sInsumosEspeciais = "";
						if (tamanho(oConsumosEspeciaisEstrela) > 0) {
							var sEstrelas = "";
							var sEsp = "";

							$.each(oConsumosEspeciaisEstrela, function(iO, oEst) {
								sEstrelas += `${sEsp}<a href="javascript:janelamodal('?_modulo=semente&_acao=u&idlote=${oEst.idlote}')"  class="nowrap fonte07">
												<i class="fa fa-star amarelo bold" title="${oEst.partida}/${oEst.exercicio}"></i>
												${oEst.partida}
											</a>`;
								sEsp = "";
							})

							sInsumosEspeciais = `<div class="insumosEspeciais">${sEstrelas}</div>`;

							//Esconde da utilização
							if (tamanho(oConsumosEspeciaisInvalidosParaSolFabAtual) > 0) {
								cEspecialVisivel = "";
							}
						}

						//Forçar esconder o lote, se o status for após PROCESSANDO e ele não estiver sendo utilizado
						cEsconderLoteDaUtilizacao = ($_1_u_formalizacao_status !== "PROCESSANDO" && $_1_u_formalizacao_status !== "ABERTO" && $_1_u_formalizacao_status !== "AGUARDANDO" && $_1_u_formalizacao_status !== "FORMALIZACAO" && $_1_u_formalizacao_status !== "TRIAGEM") && (!vQtd || ("" + vQtd).length == 0 || vQtd == 0) ? "hidden" : cEsconderLoteDaUtilizacao;

						//retira da impressao no processando se não estiver consumido
						cEsconderimpressao = (!vQtd || ("" + vQtd).length == 0 || vQtd == 0) ? "screen escondeimpressao" : cEsconderLoteDaUtilizacao;
						cEspecial = ($_1_u_formalizacao_status == "PROCESSANDO") && (!vQtd || ("" + vQtd).length == 0 || vQtd == 0) ? "" : cEspecial;

						//Monta estrelas (lotes especiais)
						var sInsumosEspeciais = "";
						if (tamanho(oConsumosEspeciaisEstrela) > 0) {
							var sEstrelas = "";
							var sEsp = "";

							//LTM 28-10-2020 379901 - Alterado para apacer o ano das Partidas
							$.each(oConsumosEspeciaisEstrela, function(iO, oEst) {
								sEstrelas += `${sEsp}<a href="javascript:janelamodal('?_modulo=semente&_acao=u&idlote=${oEst.idlote}')"  class="nowrap fonte07">
														<i class="fa fa-star amarelo bold" title="${oEst.partida}/${oEst.exercicio}"></i>
														${oEst.partida}/${oEst.exercicio}
													</a>`;
								sEsp = "";
							})

							sInsumosEspeciais = `<div class="insumosEspeciais">${sEstrelas}</div>`;

							//Esconde da utilização
							if (tamanho(oConsumosEspeciaisInvalidosParaSolFabAtual) > 0) {
								cEspecialVisivel = "";
							}
						}
						if (oe.status == "APROVADO") {
							var labelStatus = "label-primary";
						} else if (oe.status == "ESGOTADO") {
							var labelStatus = "label-secundary";
						} else {
							var labelStatus = "label-warning";
						}

						if ((!vQtd || ("" + vQtd).length == 0 || vQtd == 0)) {
							sprintse = ``;
						} else {
							sprintse = `<i title="Etiqueta da partida" class="fa fa-print preto pointer hoververmelho " onclick="showEtiquetas(2,${vIdlotecons})"></i>`;
						}

						var linkpartida;
						if (oe.idformalizacao) {
							linkpartida = `?_modulo=${CB.modulo}&_acao=u&idformalizacao=${oe.idformalizacao}`;
						} else {
							linkpartida = `?_modulo=<?=$modulolote ?>&_acao=u&idlote=${oe.idlote}`;
						}

						<? if (($_1_u_formalizacao_status == "APROVADO" || $_1_u_formalizacao_status == "REPROVADO") && !in_array($_SESSION["SESSAO"]["USUARIO"], ['marcusviniciusferranti', 'lidianppemelo'])) { ?>
							statusATiv = 'disabled';
							classAtvi = 'desabilitado';
						<? } else { ?>
							statusATiv = '';
							classAtvi = '';
						<? } ?>

						let rotuloStatus = oe.rotulo ? oe.rotulo : oe.rotulolote;
						sInsumos += `
								<span class="label ${labelStatus} fonte10 itemestoque ${sHidden} ${cEsconderLoteDaUtilizacao} ${cEsconderimpressao} ${cEspecial} ${cEspecialVisivel}" qtddisp="${oe.qtddisp||""}" qtddispexp="${oe.qtddisp_exp||""}" idlote="${oe.idlote}" data-toggle="tooltip" title="${oe.partida} - ${rotuloStatus.toUpperCase()}" data-original-title="${oe.partida} - ${rotuloStatus.toUpperCase()}">
									<div style="text-align: left;">
										<a class="branco hoverbranco" href="${linkpartida}" target="_blank">${oe.partida}/${oe.exercicio}</a>
										<span class="badge pointer screen" idlote="${oe.idlote}" onclick="janelamodal('?_modulo=${$_modulo_lote}&amp;_acao=u&amp;idlote=${oe.idlote}')">${vQtddisp}</span>
										<a class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="esgotarlote(${oe.idlotefracao},'${vescondeobj}',this)"></a>
										<input type="text" ${statusATiv} name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_qtdd" value="${vQtdInput}" idlote="${oe.idlote}" class="reset screen ${classAtvi}" cbqtddispexp="${oe.qtddisp_exp||""}" style="width: 80px !important;" onkeyup="mostraConsumo(this)">
										<span class="badge sParticipacao" id="sParticipacao${oe.idlote}"></span>
										<label class="print">&nbsp;&nbsp;&nbsp;${vQtdInput} <label class="cinza">${objInsumoEstoque.un}: </label></label>
										<input type="hidden" name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_idlotecons" value="${vIdlotecons}">
										<input type="hidden" name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_tipoobjeto" value="lote">
										<input type="hidden" name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_idobjeto" value="${$_2_u_lote_idlote}">
										<input type="hidden" name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_tipoobjetoconsumoespec" value="${vLocalConsumo}">
										<input type="hidden" name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_idobjetoconsumoespec" value="${idLocalConsumo}">
										<input type="hidden" name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_idlote" value="${oe.idlote}">
										<input type="hidden" name="_cons${selfer.iConsumo}_${acaoCons}_lotecons_idlotefracao" value="${oe.idlotefracao}">
										${sprintse}
										<label class="print">____________<label class="cinza">${objInsumoEstoque.un}</label></label>
									</div>
									${sInsumosEspeciais}
								</span>							
							`;

						//Existem mais de 10 lotes disponíveis?
						if (iiOcultar >= 100000) {
							sHidden = "excedenteOculto";
							sToggleVisiveis = `<i class='fa fa-2x fa-eye fade azul' title='Clique para mostrar/ocultar ${tamanho(vEstoque)-3} lotes excedentes' onclick="toggleLotesOcultos(this)"></i>`;
						}
					}
				});

				return {
					html: `<td >${sInsumos}${sToggleVisiveis}</td><td class='nowrap'>${sttdinsumo}</td><td>${sttdqtd}</td>`,
					totalUtilizacao: vQtdUtilizacao,
					lotesUtilizados: oConsumos
				};
			}
		};

		/*
		 * Cada lote de algum insumo deve possuir 1 (e somente 1) consumo na tabela.
		 * Qualquer falha nessa regra será sobreposta porque existe um `return false` dentro do loop, para retornar SOMENTE 1 consumo
		 */
		selfer.getConsumoDoLote = function(oe, inTipoobjetoconsumoespec, inIdobjetoconsumoespec) {
			oCons = null;
			oLoteEstoque = oe;

			/*
			 * Todo registro da lotecons:
			 * - Deve obrigatoriamente apontar para o lote superior que o consumiu: tipoobjeto + idobjeto
			 * - Caso necessário utilizar as colunas tipoobjetoconsumoespec+idobjetoconsumoespec.
			 *   Estas são utilizadas na formalização, visto que o mesmo insumo pode aparecer em atividades distintas (consumo específico)
			 */
			$.each(oe.consumosdolote[inTipoobjetoconsumoespec] || {}, function(i, c) {
				if (c.idobjetoconsumoespec == inIdobjetoconsumoespec) {
					oCons = c;
					return false; //sai do loop
				}
			})
			return oCons;
		}

		selfer.getSomaEstoque = function(o) {
			dEstoque = 0;
			//vEstoque = aFlatLotes["#"+o.idprodservformulains];
			vEstoque = o.estoque;

			if (tamanho(vEstoque) == 0) {
				return 0;
			} else {
				$.each(vEstoque, function(i, oe) {
					oe.qtd = (oe.qtd) ? oe.qtd : 0; //Evita undefined||null oriundo da tabela
					dEstoque += parseFloat(oe.qtd);
				})
				return dEstoque;
			}
		};

		//Separa os insumos que estão contidos em Atividades
		selfer.getInsumosAtividade = function(inIdprativ) {

			vIdprativ = inIdprativ;

			oInsumosAtividade = {};
			//Primeiro nà­vel de Insumos
			$.each(jInsumos, function(i, p) {
				var bExiste = false;
				$.each(jAtividadeInsumos[vIdprativ] || {}, function(i, oi) {
					if (p.idprodservformulains == oi.idprodservformulains) {
						bExiste = true;
					}
				})
				//Separa somente os produtos da fórmula selecionada pelo usuário
				if (bExiste && p.idprodservformula == selfer.idprodservformula) {
					oInsumosAtividade[p.idprodserv] = p;
				}
			});

			return oInsumosAtividade;
		};

		selfer.getHtmlInsumosAtividade = function(inIdprativ, idloteativ) {

			if (!jAtividadeInsumos[inIdprativ]) {
				var strret = "selfer.getHtmlInsumosAtividade: Nenhum insumo vinculado à  Atividade";
				selfer.debug && console.warn(strret);
				return "<!-- " + strret + " -->";
			} else {

				var hTr = "";

				$.each(selfer.getInsumosAtividade(inIdprativ), function(i, o) {
					hTr += selfer.getHtmlInsumo(o, "loteativ", idloteativ);
				})

				hTbInsumos = `
					<table class="table tab_insumo" >
					${selfer.htmlCabinsumo}
					${hTr}
					</table>`;

				return hTbInsumos;
			}
		};

		selfer.getInsumosFormalizacao = function() {

			oInsumosProduto = {};
			//Primeiro nà­vel de Insumos
			$.each(jInsumos, function(i, p) {
				bExiste = false;
				$.each(jAtividadeInsumos, function(i, a) {
					$.each(a, function(i, oi) {
						if (p.idprodservformulains == oi.idprodservformulains) {
							bExiste = true;
						}
					})
				})
				//Separa somente os produtos da fórmula selecionada pelo usuário
				if (!bExiste && p.idprodservformula == selfer.idprodservformula) {
					oInsumosProduto[p.idprodserv] = p;
				}
			});

			return oInsumosProduto;
		};

		//Para cada insumo, deve-se verificar a existência de autógenas em terceiro nà­vel, para duplicar o produto e mostrar a autógena para o 'solicitadopor'
		selfer.getInsumosSolfab = function(inObjProduto) {

			oProduto = inObjProduto;

			jPartidas = {};

			vLotes = evalJson(`jClientesSolfab[$_2_u_lote_idpessoa][$_2_u_lote_idsolfab].lotes`);
			if (vLotes) {
				$.each(vLotes, function(isfi, osfi) {
					if (tamanho(oProduto.insumos) == 0) { //se for semente não tem insumo
						if (oProduto.idprodserv == osfi.idprodserv) {
							jPartidas[isfi] = osfi;
						}
					} else {
						//se tiver insumo so aparece se o mesmo for insumo produto
						$.each(oProduto.insumos || {}, function(i, o) {
							if (o.idprodserv == osfi.idprodserv || o.idprodserv == null) {
								//Caso se deseje limitar conforme o status dos lotes das partidas de produtos especiais, pode-se limitar aqui usando o objeto osfi
								jPartidas[isfi] = osfi;
							};
						});
					}
				});
			}

			return jPartidas;
		};

		//Separa os Testes disponíveis para seleção
		selfer.getTestes = function(inPrAtivObjetos) {

			//Separa os tipo de objeto Testes
			$otestes = {}; //Abre novo objeto
			$.each(inPrAtivObjetos, function(i, o) {
				if (o.o_tipoobjeto && o.o_tipoobjeto == "prodserv") {
					//Recupera os testes disponíveis
					$otestes[i] = o;
				}
			});
			//console.log($osalas);
			return $otestes;
		};

		selfer.getHtmlResultadosBioensaio = function(inResultadosbioterio) {
			var strAtalhoRegistrobioensaio = "";
			if (tamanho(inResultadosbioterio.amostrasRelacionadasbioterio) == 0) {
				strAtalhoRegistrobioensaio = `<div class="checkbox checked"><span class="vermelho">Vincular Estudo!!!</span></div>`;
			} else {
				$.each(inResultadosbioterio.amostrasRelacionadasbioterio, function(i, o) {
					var cor = o.status == "ASSINADO" ? "verde !important" : "vermelho !important";
					strAtalhoRegistrobioensaio += `
						<div class="checkbox checked">
							<span class="${cor}">${o.descr}</span>
							<a target="_blank" href="?_modulo=bioensaio&_acao=u&idbioensaio=${o.idbioensaio}" title="B${o.idregistro}/${o.exercicio}">B${o.idregistro}</a>
						</div>`;
				});
			}
			return strAtalhoRegistrobioensaio;
		}

		selfer.getHtmlTestes = function(inPrAtiv) {
			prAtiv = inPrAtiv;
			$testes = selfer.getTestes(prAtiv.objetos);

			//Monta o Html de cada Teste
			sOpcoes = "";
			sResultado = "";
			sDivisor = "";
			ii = 0;
			if (jLoteObj.prodserv) 
			{
				$.each($testes, function(i, o) {
					ii++;
					vidloteobj = "";
					vacao = "i";
					vchecked = "";

					if (jLoteObj.prodserv[o.o_idobjeto] &&
						jLoteObj.prodserv[o.o_idobjeto][prAtiv.idloteativ]) {

						selfer.debug && console.warn("Teste " + o.o_idobjeto + " selecionado");
						vidloteobj = jLoteObj.prodserv[o.o_idobjeto][prAtiv.idloteativ]["idloteobj"];
						vacao = "u";
						vchecked = "checked";
					}

					//Verifica se existe alguma amostra relacionada à  atividade
					var strAtalhoRegistro = "";
					var strImpRegistro = "";
					var cor = "";
					var vConformidade = "";
					var figura = "";
					console.log(prAtiv.amostrasRelacionadas);
					if (prAtiv.amostrasRelacionadas && prAtiv.amostrasRelacionadas[o.o_idobjeto]) 
					{
						var vIdresultado = prAtiv.amostrasRelacionadas[o.o_idobjeto].idresultado;
						var vIdregistro = prAtiv.amostrasRelacionadas[o.o_idobjeto].idregistro;
						var vExercicio = prAtiv.amostrasRelacionadas[o.o_idobjeto].exercicio;
						var vStatus = prAtiv.amostrasRelacionadas[o.o_idobjeto].status;
						var vConformidade = prAtiv.amostrasRelacionadas[o.o_idobjeto].conformidade;
						var vModulo = prAtiv.amostrasRelacionadas[o.o_idobjeto].modulo;
						if (vStatus == "ASSINADO") {
							cor = "verde !important"
						} else if (vStatus == "FECHADO") {
							cor = "azul !important"
						} else {
							cor = "vermelho !important"
						}
						if (vConformidade == "CONFORME") {
							figura = " fa-thumbs-up verde"
						} else if (vConformidade == "NAO CONFORME") {
							figura = "fa-thumbs-down vermelho "
						} else {
							figura = " "
						}
						strAtalhoRegistro = `<a target="_blank" href="?_modulo=${vModulo}&_acao=u&idresultado=${vIdresultado}" title="${vIdregistro}/${vExercicio}">${vIdregistro}</a>`;
						strImpRegistro = `<td><div style="align-self: center;cursor:pointer;"><i class="fa fa-print cinza" onclick="imprimeetiquetateste(${vIdresultado},${inPrAtiv.idloteativ})"></i></div></td>`;
					}

					if (prAtiv.status == 'CONCLUIDO') {
						statusATiv = 'disabled';
					} else {
						statusATiv = '';
					}

					sOpcoes += `
						<div class="checkbox ${vchecked}" title="" style="display: inline-flex; width:100%;">
							<table style = "width:100%;">
								<tr>
									<td>
										<div style="width: 100%; display: flex; margin-left: -15px;">
											<label class="">
												<span style="width: 7%;float: left;"><i class="fa ${figura}"></i></span>
												<span style="width: 7%;float: left;"><input type="checkbox" style="position: relative; margin-left: 0px;" ${statusATiv} name="_${prAtiv.idloteativ}teste${o.o_idobjeto}" value="${o.o_idobjeto}" tipoobjeto="prodserv" idprativ="${prAtiv.idprativ}" idloteativ="${prAtiv.idloteativ}" idlote="${$_2_u_lote_idlote}" idloteobj="${vidloteobj}" ${vchecked} onchange="toggleCheckbox(this,'${prAtiv.status}')"></span>
												<span style="width: 86%;float: left; padding-left: 10px;">
													<span class="${cor}">${o.descr}</span>
													${strAtalhoRegistro}
												</span>
											</label>
										</div>
									</td>
									${strImpRegistro}
								</tr>
							</table>
						</div>
					`;
					sResultado += `
						<div class='print'>
							<div>
								<label>Registro:&nbsp;${strAtalhoRegistro}&nbsp;&nbsp;Teste:&nbsp;${o.descr}</label>
							</div>
						</div>`;
				});
			}

			if (ii > 0) {
				sOpcoes = `<div class="col-md-3">
								<div class='papel hover screen quadroOpcoes' id=formTra>
									<h6 class="cinza bold">Testes:</h6>
									<hr>
									${sOpcoes}
								</div>
								${sResultado}
							</div>`;

				return sOpcoes;
			} else {
				return "";
			}
		};

		selfer.getHtmlQuadroOpcoes = function(inAtiv, inOpcao) {
			var hListaOpcoes = "";
			var hTitulo = "";

			if (inOpcao == "insumos") {
				hTitulo = "Insumos utilizados"
				$.each(selfer.getAtividadesInsumos(), function(ia, oa) {
					$.each(oa.insumos || {}, function(ii, oi) {
						var hpartida = "";
						var sp = " - ";
						var un = oi.un;
						$.each(jLotecons[oi.idprodserv] || {}, function(il, pc) {
							var qtdd = recuperaExpoente(pc.qtdd, pc.qtdd_exp);
							hpartida += sp + pc.partida + " " + qtdd + " " + un;
							sp = "&nbsp;&nbsp;&nbsp;";
						})

						hListaOpcoes += `<div>${oi.descr} ${hpartida}</div>`;
					})
				})

			} else if (inOpcao == "sementes") {
				hTitulo = "Sementes utilizadas"
				$.each(selfer.getAtividadesInsumos(), function(ia, oa) {
					$.each(oa.insumos || {}, function(ii, oi) {
						if (oi.especial == "Y") {
							hListaOpcoes += `<div>${oi.descr}</div>`;
						}
					})
				})
			} else if (inOpcao == "fp") {
				hTitulo = "Formula Padrão"
				
				$arrInsumosFom = {};
				//Primeiro loop separando os insumos e ordenando corretamente conforme configuração (ord). A ordenação será feita utilizando-se a "key" do json
				$.each(jInsumos || {}, function(ii, oi) {
					if (oi.qtdpd !== null) {
						var descprod = oi.descr;
						if (oi.descrgenerica !== null) {
							descprod = oi.descrgenerica;
						}
						//Monta um novo objeto{} seguindo a ordenação de: Insumos > ord
						$arrInsumosFom[oi.ord] = `\n<tr><td nowrap>${oi.qtdpd} ${oi.un}</td><td nowrap>${oi.codprodserv}</td><td>${descprod}</td></tr>`;
					}
				});
				//Segundo loop para utilizar os insumos já ordenados
				hListaOpcoes = "<table class='fonte08'>";
				$.each($arrInsumosFom, function(iif, insf) {
					hListaOpcoes += insf;
				})
				hListaOpcoes += "</table>";
			}

			hListaOpcoes = `
					<div class="col-md-3 print">
						<div class='papel hover quadroOpcoes' id=formTra>
							<h6 class="cinza bold">${hTitulo}:</h6>
							<hr class="screen">
							${hListaOpcoes}
						</div>
					</div>`;

			return hListaOpcoes;
		};

		//Separa os Controles disponíveis para seleção
		selfer.getControlesProcesso = function(inPrAtivObjetos) {

			//Separa os tipo de objeto Controle Processo
			let ob = [];
			for (let i in inPrAtivObjetos) {
				if (inPrAtivObjetos[i]['o_tipoobjeto'] && inPrAtivObjetos[i]['o_tipoobjeto'] == "ctrlproc") {
					ob.push(inPrAtivObjetos[i]);
				}
			}

			return (ob.length > 0) ? ob.sort((a, b) => a.ord - b.ord) : ob;
		};

		selfer.getHtmlContrProc = function(inPrAtiv) {
			prAtiv = inPrAtiv;
			$controlesProc = selfer.getControlesProcesso(prAtiv.objetos);

			//Monta o Html de cada controle processo
			sOpcoes = "";
			ii = 0;
			for (let o of $controlesProc) {
				ii++;
				vidloteobj = "";
				vacao = "i";
				vchecked = "";
				var vidloteobjqtd = "";
				var vidloteobjqtd_exp = "";
				let vescondeCheckbox = "escondeCheckbox";
				if (jLoteObj.ctrlproc &&
					jLoteObj.ctrlproc[o.idprativobj] &&
					jLoteObj.ctrlproc[o.idprativobj][prAtiv.idloteativ]) {

					selfer.debug && console.warn("Informações específicas " + o.idprativobj + " selecionado");
					vidloteobj = jLoteObj.ctrlproc[o.idprativobj][prAtiv.idloteativ]["idloteobj"];
					vidloteobjqtd = jLoteObj.ctrlproc[o.idprativobj][prAtiv.idloteativ]["qtd"];
					vidloteobjqtd_exp = jLoteObj.ctrlproc[o.idprativobj][prAtiv.idloteativ]["qtd_exp"];
					vacao = "u";
					vchecked = "checked";
					vescondeCheckbox = '';
				}

				var statusATiv = '';
				if (prAtiv.status == 'CONCLUIDO') {
					statusATiv = 'disabled';
					classAtvi = 'desabilitado';
				} else {
					statusATiv = '';
					classAtvi = '';
				}

				if (prAtiv.status == 'CONCLUIDO') {
					statusATiv = 'disabled';
				} else {
					statusATiv = '';
				}

				if (o.inputmanual == "text") {
					vQtdInput = recuperaExpoente(vidloteobjqtd, vidloteobjqtd_exp) || "";
					var valueInputManualText;
					if (jLoteObj.ctrlproc[o.idprativobj] != undefined) {
						if (jLoteObj.ctrlproc[o.idprativobj][prAtiv.idloteativ]["qtd"] == null) {
							valueInputManualText = '';
						} else {
							valueInputManualText = jLoteObj.ctrlproc[o.idprativobj][prAtiv.idloteativ]["qtd"];
						}
					} else {
						valueInputManualText = '';
					}

					sOpcoes += `
						<div class="checkbox ${vchecked} ${vescondeCheckbox}" title="" inputmanual="${o.inputmanual||""}">
							<label>
								${o.descr}
								<input size="5" ${statusATiv}  class="inputmanualtext ${classAtvi}" inputmanual="${o.inputmanual}" type="text" name="_${prAtiv.idloteativ}cproc${o.idprativobj}" value="${valueInputManualText}" idprativobj="${o.idprativobj}" tipoobjeto="ctrlproc" idprativ="${prAtiv.idprativ}" idloteativ="${prAtiv.idloteativ}" idlote="${$_2_u_lote_idlote}" idloteobj="${vidloteobj}" ${vchecked} onchange="toggleText(this,'${prAtiv.status}')">				
								<div class="print" inputmanual="${o.inputmanual||""}"></div>
							</label>
						</div>`;

				} else {
					sOpcoes += `
						<div class="checkbox ${vchecked} ${vescondeCheckbox}" title="" inputmanual="${o.inputmanual||""}">
							<label>
								<input type="checkbox" ${statusATiv} name="_${prAtiv.idloteativ}cproc${o.idprativobj}" value="${o.idprativobj}" tipoobjeto="ctrlproc" idprativ="${prAtiv.idprativ}" idloteativ="${prAtiv.idloteativ}" idlote="${$_2_u_lote_idlote}" idloteobj="${vidloteobj}" ${vchecked} onchange="toggleCheckbox(this,'${prAtiv.status}')">
								${o.descr}
								<div class="print" inputmanual="${o.inputmanual||""}"></div>
							</label>
						</div>`;
				}
			}

			if (ii > 0) {
				sOpcoes = `<div class="col-md-3">
							<div class='papel hover quadroOpcoes' id=formTra>
								<h6 class="cinza bold">Informações específicas:</h6>
								<hr>
								${sOpcoes}
							</div>
						</div>`;

				return sOpcoes;
			} else {
				return "";
			}
		};

		//MATERIAIS Separa os Controles disponíveis para seleção
		selfer.getMateriais = function(inPrAtivObjetos) {

			//Separa os tipo de objeto materiais
			$omat = {}; //Abre novo objeto
			$.each(inPrAtivObjetos, function(i, o) {
				if (o.o_tipoobjeto && o.o_tipoobjeto == "materiais") {
					//Recupera os materiais disponíveis
					$omat[i] = o;
				}
			});

			return $omat;
		};

		selfer.getHtmlMateriais = function(inPrAtiv) {
			prAtiv = inPrAtiv;
			$Materiais = selfer.getMateriais(prAtiv.objetos);

			//Monta o Html de cada Material
			sOpcoes = "";
			ii = 0;

			if (prAtiv.status == 'CONCLUIDO') {
				statusATiv = 'disabled';
			} else {
				statusATiv = '';
			}

			$.each($Materiais, function(i, o) {
				ii++;
				vidloteobj = "";
				vacao = "i";
				vchecked = "";
				let vescondeCheckbox = 'escondeCheckbox';
				if (jLoteObj.materiais &&
					jLoteObj.materiais[o.idprativobj] &&
					jLoteObj.materiais[o.idprativobj][prAtiv.idloteativ]) {

					selfer.debug && console.warn("Material " + o.idprativobj + " selecionado");
					vidloteobj = jLoteObj.materiais[o.idprativobj][prAtiv.idloteativ]["idloteobj"];
					vacao = "u";
					vchecked = "checked";
					vescondeCheckbox = '';
				}

				sOpcoes += `
						<div class="checkbox ${vchecked} ${vescondeCheckbox} title="" inputmanual="${o.inputmanual||""}">
							<label>
								<input type="checkbox" ${statusATiv} name="_${prAtiv.idloteativ}cproc${o.idprativobj}" value="${o.idprativobj}" tipoobjeto="materiais" idprativ="${prAtiv.idprativ}" idloteativ="${prAtiv.idloteativ}" idlote="${$_2_u_lote_idlote}" idloteobj="${vidloteobj}" ${vchecked} onchange="toggleCheckbox(this,'${prAtiv.status}')">
									${o.descr}
								<div class="print" inputmanual="${o.inputmanual||""}"></div>
							</label>
						</div>
				`;
			});


			if (ii > 0) {
				sOpcoes = `<div class="col-md-3">
								<div class='papel hover quadroOpcoes' id=formTra>
									<h6 class="cinza bold">Materiais e Utensílios:</h6>
									<hr>
									${sOpcoes}
								</div>
							</div>`;

				return sOpcoes;
			} else {
				return "";
			}
		};

		//Opções da atividade (prativopcao)
		selfer.getPrativopcoes = function(inPrAtivObjetos) {

			$oOpcao = {}; //Abre novo objeto
			$obio = {}; //Abre novo objeto
			$.each(inPrAtivObjetos, function(i, o) {
				if (o.o_tipoobjeto == "prativopcao") {
					//Recupera os prativopcao 5
					$obio[o.opcao] = o;
					$obio[o.opcao]["idprativobj"] = i;
				}
			});

			return $obio;
		};
	} //var Formalizacao

	if (CB.acao == "u") 
	{
		//Inicializa autocomplete
		autoCompleteSolfab();

		//SE STATUS ABERTO NAO MONTA O CORPO DA FORMALIZACAO
		if ($_2_u_lote_idprodservformula) 
		{
			//Verifica seleção de fórmulas
			vFormulaselecionada = $_2_u_lote_idprodservformula;

			//Caso o produto da formalização possua mais de 1 fórmula para produção, mostrar tela de seleção
			if (vFormulaselecionada) 
			{
				if (jprodForm.especial == "Y" && $_2_u_lote_idpessoa) {
					//somente inicializa se alguma Solicitação de Fabricação foi selecionada
					if ($_2_u_lote_idsolfab !== "" && $_2_u_lote_idpessoa && $_2_u_lote_idpessoa !== "") {
						formalizacao = new Formalizacao();
						$("#corpoFormalizacao").append(formalizacao.getHtml());
					}
				} else {
					formalizacao = new Formalizacao();
					$("#corpoFormalizacao").append(formalizacao.getHtml());
				}
			}
		} 
	}

	CB.on('posLoadUrl',function(data){
		var order = 0;
		var qtd = 0;
		var qtd_loteAtiv = $(".loteativ_order").length;
		$($(".loteativ_order").get().reverse()).each(function(ia, at) {
			if(order != $(at).attr('loteativ_order') && $(".loteativ_order").length != qtd_loteAtiv && $(at).attr('loteativ_order') != 0)
			{
				$(at).addClass('quebraPagina');				
			} 
			order = $(at).attr('loteativ_order');
			qtd_loteAtiv--;
		});

		$($(".loteativ_order").get()).each(function(ia, at) {
			if(order != $(at).attr('loteativ_order') && $(".loteativ_order").length != qtd_loteAtiv && $(at).attr('loteativ_order') != 0)
			{		
				qtd = 1;
			} else {
				qtd = qtd + 1;
				if(qtd == 2)
				{
					$(at).addClass('quebraPagina');
					qtd = 0;
				}
			}
			order = $(at).attr('loteativ_order');
			qtd_loteAtiv--;
		});	
	});

	CB.on('posPost',function(data){
		if($_2_u_lote_idpessoa.length > 0)
		{
			$('.div_cliente').show();	
			$('.div_sol_fab').show();
		}
	});

	function autoCompleteSolfab() 
	{
		idpessoa = $("[name=_2_u_lote_idpessoa]").cbval();
		idpessoa = idpessoa.length == 0 ? $("#_lote_idpessoa").attr('cbvalue') : idpessoa;
		jAcSolfab = null;
		if (!idpessoa) {
			return false;
		}

		if (!jClientesSolfab[idpessoa]) {
			console.error("autoCompleteSolfab: Cliente não possui Solicitação de Fabricação");
			return false;
		}

		jAcSolfab = jQuery.map(jClientesSolfab[idpessoa], function(o, id) {
			return {
				"label": id,
				"value": id + "",
				"status": o.statussolfab,
				"exercicio": o.exercicio,
				"codprodserv": o.codprodserv,
				"rotulosolfab": o.rotulosolfab,
				"criadoem": o.criadoem
			}
		});

		campoSolfab = $("[name=_2_u_lote_idsolfab]").length == 0 ? '#_lote_idsolfab' : '[name=_2_u_lote_idsolfab]';
		$(campoSolfab).autocomplete({
			source: jAcSolfab,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.rotulosolfab + " - " + item.criadoem + "</a>").appendTo(ul);
				};
			},
			select: function(event, ui) {
				resetFormalizacao();
			}
		});
	}

	function resetFormalizacao() 
	{
		$("#corpoFormalizacao").css("opacity", "0.3").find(":input").attr("disabled", "disabled");
	}

	function toggleCheckbox(inObj, statusAtividade) 
	{
		$objCheck = $(inObj);

		var inidloteobj = $objCheck.attr('idloteobj');
		let divInput = $objCheck.parent().parent();

		divInput.toggleClass('checked');
		divInput.toggleClass('escondeCheckbox');

		if (statusAtividade == 'PENDENTE' || statusAtividade == 'PROCESSANDO') 
		{
			if (inidloteobj) {
				CB.post({
					objetos: "_1_d_loteobj_idloteobj=" + inidloteobj,
					refresh: false,
					parcial: true,
					posPost: function() {
						$objCheck.removeAttr('idloteobj');
					}
				});
			} else {
				var idloteativ = $objCheck.attr('idloteativ');
				var idlote = $objCheck.attr('idlote');
				var idprativ = $objCheck.attr('idprativ');
				var idobjeto = $objCheck.val();
				var tipoobjeto = $objCheck.attr('tipoobjeto');
				if (idloteativ && idlote && idprativ && idobjeto && tipoobjeto) {
					CB.post({
						objetos: "_1_i_loteobj_idloteativ=" + idloteativ + "&_1_i_loteobj_idlote=" + idlote + "&_1_i_loteobj_idprativ=" + idprativ + "&_1_i_loteobj_idobjeto=" + idobjeto + "&_1_i_loteobj_tipoobjeto=" + tipoobjeto,
						refresh: false,
						parcial: true,
						posPost: function(data, textStatus, jqXHR) {
							$objCheck.attr('idloteobj', jqXHR.getResponseHeader("x-cb-pkid"));
						}
					});
				} else {
					console.error(`toggleCheckbox: idloteativ[${idloteativ}] &&  idlote[${idlote}] && idprativ[${idprativ}] && idobjeto[${idobjeto}] && tipoobjeto[${tipoobjeto}]`);
				}

			}
		} else {
			alertAtencao("Não é possível alterar dados da atividade Concluída.");
			console.log("togglecheckbox: status=" + $_1_u_formalizacao_status + " - Nenhuma alteração");
		}
		console.log(inObj);
	}

	function toggleText(inObj, statusAtividade) 
	{
		if (statusAtividade == 'PENDENTE' || statusAtividade == 'PROCESSANDO') 
		{
			var inidloteobj = $(inObj).attr('idloteobj');
			var valor = $(inObj).val();

			if (inidloteobj) {
				CB.post({
					objetos: `_2_u_loteobj_idloteobj=${inidloteobj}&_2_u_loteobj_qtd=${valor}`,
					refresh: false,
					parcial: true
				});
			} else {
				var idloteativ = $(inObj).attr('idloteativ');
				var idlote = $(inObj).attr('idlote');
				var idprativ = $(inObj).attr('idprativ');

				var idobjeto = $(inObj).attr('idprativobj');
				var tipoobjeto = $(inObj).attr('tipoobjeto');
				if (idloteativ && idlote && idprativ && idobjeto && tipoobjeto) {
					CB.post({
						objetos: "_1_i_loteobj_idloteativ=" + idloteativ + "&_1_i_loteobj_idlote=" + idlote + "&_1_i_loteobj_idprativ=" + idprativ + "&_1_i_loteobj_idobjeto=" + idobjeto + "&_1_i_loteobj_tipoobjeto=" + tipoobjeto + "&_1_i_loteobj_qtd=" + valor,
						refresh: false,
						parcial: true,
						posPost: function(data, textStatus, jqXHR) {
							$(inObj).attr('idloteobj', jqXHR.getResponseHeader("x-cb-pkid"));
						}
					});
				} else {
					console.error(`toggleText: idloteativ[${idloteativ}] &&  idlote[${idlote}] && idprativ[${idprativ}] && idobjeto[${idobjeto}] && tipoobjeto[${tipoobjeto}]`);
				}
			}
		} else {
			alertAtencao("Não é possível alterar dados da atividade Concluída.");
			console.log("togglecheckbox: status=" + $_1_u_formalizacao_status + " - Nenhuma alteração");
		}
	}

	function gerarFormalizacao(inQtdprod, inQtdprod_exp, inIdprodserv, inIdloteprodpara, inDescr, inIdprodservformulains, inIdobjetosolipor, fabricado) 
	{
		//Quando houver và­nculo com outro lote
		vSolipor = (inIdobjetosolipor && inIdobjetosolipor != "") ? "&_x_i_lote_tipoobjetosolipor=lote&_x_i_lote_idobjetosolipor=" + inIdobjetosolipor : "";
		//Quando tiver idpessoa informado no lote
		var idpessoa = '<?=$_2_u_lote_idpessoa ?>';
		vIdpessoa = (idpessoa && idpessoa != "") ? "&_x_i_lote_idpessoa=" + idpessoa : "";

		//Retorna o idfluxo do lote
		var idfluxostatus = getIdFluxoStatus('<?=$modulolote ?>', 'TRIAGEM');
		strobjetos = `_x_i_lote_qtdpedida=${inQtdprod}&_x_i_lote_qtdpedida_exp=${inQtdprod_exp}&_x_i_lote_idunidade=${idunidade}&_x_i_lote_qtddisp=${inQtdprod}&_x_i_lote_qtddisp_exp=${inQtdprod_exp}&_x_i_lote_tipoobjetoprodpara=lote&_x_i_lote_idobjetoprodpara=${inIdloteprodpara}&_x_i_lote_idprodserv=${inIdprodserv}&_x_i_lote_idprodservformulains=${inIdprodservformulains}${vIdpessoa}&_x_i_lote_status=TRIAGEM${vSolipor}&_x_i_lote_idfluxostatus=${idfluxostatus}&idprproc=${idprproc}`;

		if (confirm("Deseja gerar uma NOVA PARTIDA para\n\n" + inDescr + "?\n\n")) {
			CB.post({
				objetos: strobjetos,
				parcial: true,
				msgSalvo: false,
				posPost: function(data, textStatus, jqXHR) {
					var idlote = jqXHR.getResponseHeader("X-CB-PKID"); //Pega o último idlote inserido
					//Caso o Produto não seja formulado não gerará Formalização e abrirá o link do próprio lote
					if (fabricado == 'Y') {
						var idfluxostatusFormalizacao = getIdFluxoStatus(moduloformalizacao, 'ABERTO', idprproc);
						if (idlote && jqXHR.getResponseHeader("X-CB-PKFLD") == "idlote") {
							strObjetosFormalizacao = `_f_i_formalizacao_idunidade=${idunidade}&_f_i_formalizacao_idlote=${idlote}&_f_i_formalizacao_idprproc=${idprproc}&_f_i_formalizacao_exercicio=${exercicio}&_f_i_formalizacao_status=ABERTO&_f_i_formalizacao_idfluxostatus=${idfluxostatusFormalizacao}`;
							CB.post({
								objetos: strObjetosFormalizacao,
								parcial: true,
								posPost: function(data, textStatus, jqXHR) {
									var idformalizacao = jqXHR.getResponseHeader("X-CB-PKID"); //Pega o último idformalizacao inserido
									janelamodal("?_modulo=" + CB.modulo + "&_acao=u&idformalizacao=" + idformalizacao);
								}
							});
						} else {
							alert("js: gerarFormalizacao: A resposta de inserção não retornou a coluna `idlote` ou Autoincremento.");
						}
					} else {
						<? $modulo = getModuloPadrao('lote', $_1_u_formalizacao_idunidade); ?>
						janelamodal("?_modulo=<?=$modulo ?>&_acao=u&idlote=" + idlote);
					}
				}
			});
		}
	}

	/*
	 * Criado por Daniel em 05-jun-17
	 */
	function calculaEstoqueProduzir(inInsumo) 
	{
		var oInsumo = inInsumo;
		var vQtdpedidaFloat = parseFloat(("" + $_2_u_lote_qtdajust).replace(",", "."));
		pedFilho = (vQtdpedidaFloat * oInsumo.qtdi) / jprodFormula.qtdpadrao
		estFilho = formalizacao.getSomaEstoque(obj); //todo: alterar para escopo superior, para evitar referencia ao objeto da formalizcao

		if ((pedFilho - estFilho) >= oInsumo.qtdpadrao) {
			qtdProduzir = pedFilho - estFilho;
			formalizacao.debug && console.log("(pedFilho-estFilho) > qtdpadrao");

		} else if ((pedFilho - estFilho) < oInsumo.qtdpadrao) {
			qtdProduzir = oInsumo.qtdpadrao;
			formalizacao.debug && console.log("(pedFilho-estFilho) < qtdpadrao");
		} else {
			qtdProduzir = 0;
		}

		//Em caso de quantidade a produzir == 0, tenta recuperar Estoque Mà­nimo
		qtdProduzir = (qtdProduzir == 0) ? oInsumo.qtdpadrao : qtdProduzir;
		formalizacao.debug && console.warn(oInsumo.codprodserv + ": qtdProduzir: " + qtdProduzir);

		return qtdProduzir;
	}

	function alteracaoSala(inObj) 
	{
		$o = $(inObj);
		$equipamentosAtiv = $o.closest("[idloteativ]").find(".equipamentos").find("[type=checkbox]");
		$.each($equipamentosAtiv, function(i, eq) {
			$eq = $(eq);
			//if($eq.attr("idtagpai")==){

			if ($eq.attr("idtag_pais").split("#").indexOf($o.val()) >= 0) {
				$eq.closest(".radio").removeClass("hidden");
			} else {
				$eq.closest(".radio").addClass("hidden");
			}
		})
	}

	function toggleLotesOcultos(inObj) 
	{
		$obj = $(inObj);
		$oTd = $obj.closest("td");
		$excedentesOcultos = $oTd.find("span.excedenteOculto");

		if ($excedentesOcultos.length > 0) {
			//Mostra excedentes
			$oTd.find("span.excedenteOculto").removeClass("excedenteOculto").addClass("excedenteVisivel");
		} else {
			//Oculta excedentes
			$oTd.find("span.excedenteVisivel").removeClass("excedenteVisivel").addClass("excedenteOculto");
		}
	}

	//trocar a cor da atividade de acordo com seu status
	function statusloteativ(vstatus) 
	{
		var status = $("[name=_1_u_formalizacao_status]").val(); 
		if (vstatus == "CONCLUIDO") {
			return "ativconcluida";
		} else if (vstatus == "PENDENTE") {
			return "ativpendente";
		} else if (vstatus == "PROCESSANDO") {
			return "ativaprovada";
		} else {
			return "ativpendente";
		}
	}

	function normalizaQtd(inValor) 
	{
		var sVlr = "" + inValor;
		var $arrExp;
		var fVlr;
		if (sVlr.toLowerCase().indexOf("d") > -1) {
			$arrExp = sVlr.toLowerCase().split('d');
			fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
			fVlr = parseFloat(fVlr);
		} else if (sVlr.toLowerCase().indexOf("e") > -1) {
			$arrExp = sVlr.toLowerCase().split('e');
			fVlr = $arrExp[0] * Math.pow(10, $arrExp[1]);
		} else {
			fVlr = parseFloat(sVlr).toFixed(2);
		}

		return parseFloat(fVlr);
	}

	function esgotarlote(inIdlote, inescondeobj, vThis) 
	{
		if (confirm("Deseja realmente esgotar o lote?")) {
			if (inescondeobj == 'Y') {
				vThis.parentNode.remove();
			}
			CB.post({
				"objetos": `_x_u_lotefracao_idlotefracao=${inIdlote}&_x_u_lotefracao_status=ESGOTADO&_x_u_lotefracao_qtd=0&&_x_u_lotefracao_qtd_exp=0`,
				parcial: true,
				refresh: false
			});
		}
	}

	function imprimir() 
	{
		window.print();
		gravaimpressao();
	}

	function showEtiquetas(grupo, idlote) 
	{
		_controleImpressaoModulo({
			modulo: getUrlParameter("_modulo"),
			grupo: grupo,
			idempresa: idempresa,
			objetos: {
				idlote: idlote,
				idmodulo: idFormalizacao,
				exercicio: exercicioLote,
				partida: partidaLote,
			}
		});
	}

	function gravaimpressao() 
	{
		if ($_1_u_formalizacao_status == 'PROCESSANDO') {
			var str = '';
			$("div[class*='loteativ ']").not("div[class*='opacity04']").each(function(index) {
				console.log(index + ": " + $(this).attr("idloteativ"));
				str = str + "_" + index + "x_u_loteativ_idloteativ=" + $(this).attr("idloteativ") + "&_" + index + "x_u_loteativ_impresso=Y&";
			});
			console.log(str);

			CB.post({
				objetos: str,
				parcial: true,
				refresh: false
			});
		}
	}

	function imprimeetiquetateste(inIdresultado = 0, inIdloteativ = 0) 
	{
		var inIdlote = $("input[name='_2_u_lote_idlote']").val() || 0;
		var imprimir = true;
		if (!confirm("Deseja realmente enviar para a impressora?")) {
			imprimir = false;
		}
		if (imprimir) {
			if (inIdresultado == 0 || inIdloteativ == 0 || inIdlote == 0) {
				alertAtencao("Não foi possível enviar o teste para impressão");
			} else {
				$.ajax({
					type: "get",
					url: "ajax/impetiquetaproducaotestes.php?idresultado=" + inIdresultado + "&idloteativ=" + inIdloteativ + "&idlote=" + inIdlote,
					success: function(data) {
						alertAzul("Enviado para impressão", "", 1000);
					}
				});
			}
		}
	}

	function imprimeEtiqueta(inIdlote, inTipo) 
	{
		var imprimir = true;
		CB.imprimindo = true;

		if (!confirm("Deseja realmente enviar para a impressora?")) {
			imprimir = false;
		}

		if (imprimir) {
			switch (inTipo) {
				case 'tipo1':
					var tipo = "lote";
					break;
				case 'tipo2':
					var tipo = "tipo2";
					break;
				case 'tipo3':
					var tipo = "tipo3";
					break;
				case 'tipo4':
					var tipo = "lotecons";
					break;
				case 'tipo5':
					var tipo = "tipo5";
					break;
				case 'tipo5b':
					var tipo = "tipo5b";
					break; // Tipo 3 para impressora zebra
				case 'tipo6':
					var tipo = "tipo6";
					break; // Tipo 3 para impressora zebra
				case 'tipo7':
					var tipo = "tipo7";
					break; // Tipo 3 para impressora zebra
				case 'tipo8':
					var tipo = "tipo8";
					break;
				case 'tipo9':
					var tipo = "tipo9";
					break;
				case 'tipo10':
					var tipo = "tipo10";
					break;
				case 'tipo11':
					var tipo = "tipo11";
					break;
				case 'tipo12':
					var tipo = "tipo12";
					break;
				case 'tipo13':
					var tipo = "tipo13";
					break;
				case 'tipo14':
					var tipo = "tipo14";
					break;
				case 'tipo15':
					var tipo = "tipo15";
					break;
				default:
					var tipo = "";
					break;
			}

			if (tipo != "") {
				var qtd = $("#imp_qtd").val() || 1;
				$.ajax({
					type: "get",
					url: "ajax/impetiquetaproducao.php?idlote=" + inIdlote + "&intipo=" + tipo + "&qtd=" + qtd,
					success: function(data) {
						console.log(data);
						alertAzul("Enviado para impressão", "", 1000);

					}
				});
			}
		}
	}

	function verificaTagReserva(inidloteativ, iatividade, travasala)
	{
		var idsala = $("[name=_" + inidloteativ + "sala_u_loteobj_idobjeto]").val();
		if (idsala == undefined) {
			var idsala = $("[name=_" + inidloteativ + "sala_i_loteobj_idobjeto]").val();
			if (idsala == undefined) {
				alert("Favor selecionar a sala da atividade.");
			}
		}

		var execucao = $("[name=_" + iatividade + "_u_loteativ_execucao]").val();
		if (execucao == "") {
			alert("Favor preenher o início da atividade.");
		}

		var execucaofim = $("[name=_" + iatividade + "_u_loteativ_execucaofim]").val();
		if (execucaofim == "") {
			alert("Favor preenher o fim da atividade.");
		}

		if (idsala != "" && execucao != "" && execucaofim != "") { //validar somente se o campo contiver algum valor

			$.ajax({
				type: 'get',
				url: 'ajax/verificatagreserva.php',
				data: {
					idsala: idsala,
					execucao: execucao,
					execucaofim: execucaofim,
					inidloteativ: inidloteativ,
					travasala: travasala
				},
				/*********************************************************************/
				success: function(data) {

					if (data == "false") //se retornar false não tem reserva
					{
						$("#msgbox" + iatividade).fadeTo(100, 0.9, function() //mostra o messagebox OK
							{
								$('#msgbox' + iatividade).html('Ok!').removeClass('messageboxerror').addClass('messageboxok').fadeTo(100, 1);
								document.getElementById("cbSalvar").style.display = "";
							});

					} else if (data == "true") //se a pagina retornou erro
					{
						$("[name=_" + inidloteativ + "sala_i_loteobj_idobjeto]").val(''); // limpar o campo sala
						$("#msgbox" + iatividade).fadeTo(100, 0.9, function() //mostra mensagem de sala ocupada
							{
								$('#msgbox' + iatividade).html('Sala ocupada!').addClass('messageboxerror').fadeTo(100, 1);
								document.getElementById("cbSalvar").style.display = "none";
								$("[name=_" + iatividade + "_u_loteativ_duracao]").val('');

							});

					} else {
						$("#msgbox" + iatividade).fadeTo(100, 0.9, function() //mostra qualquer condicao diferente. Ex: erro de php
							{
								$('#msgbox' + iatividade).html('Erro:<br>' + data).addClass('messageboxerror').fadeTo(100, 1);
								document.getElementById("cbSalvar").style.display = "none";
								$("[name=_" + iatividade + "_u_loteativ_duracao]").val('');

							});
					}
				},

				/*********************************************************************/
				error: function(objxmlreq) {
					$("#msgbox" + iatividade).fadeTo(100, 0.1, function() {
						$('#msgbox' + iatividade).html('Erro geral:<br>' + objxmlreq.status).addClass('messageboxerror').fadeTo(100, 1);
						document.getElementById("cbSalvar").style.display = "none";
						$("[name=_" + iatividade + "_u_loteativ_duracao]").focus();
					});
				}
			}) //$.ajax
		} else {
			//se o campo nao contiver valor, esconder a msgbox
			$('#msgbox' + iatividade).fadeOut();
			document.getElementById("cbSalvar").style.display = "";
		}
	} //function verificaTagReserva(inidloteativ,iatividade){

	function setdataproducao(indate) 
	{
		CB.post({
			objetos: `_2_u_lote_idlote=${$("[name=_2_u_lote_idlote]").val()}&_2_u_lote_producao=${indate}&oldstatus=${$("[name=oldstatus]").val()}&lote_producaoold=${$("[name=lote_producaoold]").val()}`,
			parcial: true
		});
	}

	function alteraStatusAtiv(idloteativ, status, bloquearstatus) 
	{
		CB.post({
			objetos: `_statusla_u_loteativ_idloteativ=${idloteativ}&_statusla_u_loteativ_status=${status}&_statusla_u_loteativ_bloquearstatus=${bloquearstatus}`
		});
	}

	function finalizaop(status, inidloteativ, concluirTodos = false) 
	{
		if (status == 'CONCLUIDO') 
		{
			$oModal = $(`<div id="modaletiqueta">
						<div class="row">
							<div class="col-md-12" id="imp_content">
								<table style="width:100%;" id="imp_table"></table>
								<hr>
								<table style="width:100%;">
									<tr>
										<td style="text-align: end;">
											<button onclick="concluiop(${inidloteativ},'APROVADO', ${concluirTodos})" type="button" class="btn btn-success fa fa-circle pointer" title="Aprovado"><span style="margin-left:5px;">Aprovado</span></button>
										</td>
										<td>
											<button onclick="concluiop(${inidloteativ},'REPROVADO', ${concluirTodos})" type="button" class="btn btn-danger fa fa-circle pointer" title="Reprovado"><span style="margin-left:5px;">Reprovado</span></button>
										</td>

									</tr>
								</table>
							</div>
						</div>
					</div>
				`);

			CB.modal({
				titulo: "</strong>Status da Ordem de Produção</strong>",
				corpo: [$oModal],
				classe: 'trinta',
				aoFechar: function(oParam) {
					if ($("#_clickAprovarReprovarOp_").length == 0) {
						$($("select.statusativ")[$("select.statusativ").length - 1]).val('PENDENTE')
					} else {
						$("#_clickAprovarReprovarOp_").remove();
					}
				}
			});
		}
	}

	function concluiop(inidloteativ, instatus, concluirTodos) 
	{
		if (instatus == 'APROVADO') {
			var analise = 'ACEITO';
		} else {
			var analise = 'RECUSADO';
		}

		//LTM - 21-05-2021 - Retorna o idFluxoStatus Selecionado
		var idfluxostatusFormalizacao = getIdFluxoStatus(moduloformalizacao, instatus, idprproc);
		if(concluirTodos){
			var objcbpost = "";
			let $ol = $("div[title*='idloteativ:']");

			//Carrega todas as atividades Pendentes para que sejam concluídas
			$("div[title*='idloteativ:'] label.alert-warning").each((i, o)=> {				
				if(o.innerText == 'PENDENTE' || o.innerText == 'PROCESSANDO'){
					var idloteativol = $ol[i].title.replace('idloteativ: ','');
					objcbpost += `_statusla${i}_u_loteativ_idloteativ=${idloteativol}&_statusla${i}_u_loteativ_status=CONCLUIDO`;
					objcbpost += "&";
				} 
			});

			trobjetosFinalizacao = `${objcbpost}&_1_u_formalizacao_idformalizacao=${$("[name=_1_u_formalizacao_idformalizacao]").val()}&_1_u_formalizacao_status=${instatus}&_1_u_formalizacao_idfluxostatus=${idfluxostatusFormalizacao}&_1_u_formalizacao_analise=${analise}&&_1_u_formalizacao_idprproc=${idprproc}&_2_u_lote_idlote=${$_2_u_lote_idlote}`;
		} else {
			trobjetosFinalizacao = `_1_u_formalizacao_idformalizacao=${$("[name=_1_u_formalizacao_idformalizacao]").val()}&_1_u_formalizacao_status=${instatus}&_1_u_formalizacao_idfluxostatus=${idfluxostatusFormalizacao}&_1_u_formalizacao_analise=${analise}&&_1_u_formalizacao_idprproc=${idprproc}&_3_u_loteativ_idloteativ=${inidloteativ}&_3_u_loteativ_status=CONCLUIDO&_2_u_lote_idlote=${$_2_u_lote_idlote}`;
		}

		CB.post({
			objetos: trobjetosFinalizacao,
			parcial: true,
			posPost: function(resp, status, ajax) {
				if (status = "success") {
					$('#cbModuloForm').append(`<input type="hidden" id="_clickAprovarReprovarOp_">`);
					$("#cbModalCorpo").html("");
					$('#cbModal').modal('hide');
				} else {
					alert(resp);
				}
			}
		})
	}

	atualizaPlaceholderFormalizacao();

	//Realiza o cálculo de sugestão de qtdd para toda a formalização 
	function atualizaPlaceholderFormalizacao() 
	{
		//Recupera as atividades
		$.each($(".loteativ[idloteativ]"), function(ia, at) {
			$at = $(at);
			//Recupera os insumos
			$trinsumos = $at.find(".trInsumo");
			$.each($trinsumos, function(ii, trins) {
				$trins = $(trins);
				//Passar o objeto jquery com o TR referente ao insumo para ser processado pelo cálculo
				atualizaPlaceholderSugestao($trins);
			});
		});
	}

	//O parâmetro inTr deve ser um objeto jquery
	function atualizaPlaceholderSugestao(inTr) 
	{
		//Objeto jquery com o TR
		$ins = inTr;

		//Encontrar as quantidades para cálculo
		vIdprodserv = $ins.attr("idprodserv");
		vQPadrao = $ins.find("#iqtdpadrao" + vIdprodserv).html();
		vUtil = $ins.find("#iqtdutilizando" + vIdprodserv).html();
		vRestante = $ins.find("#iqtdrestante" + vIdprodserv).html();

		//Loop nos insumos
		ic = 0;
		// exemplo para mais seletores: $.each($ins.find(".itemestoque[idlote].especialvisivel, .itemestoque[idlote].outroslotesvisiveis"), function(ie,iEst){
		$.each($ins.find(".itemestoque[idlote]"), function(ie, iEst) {
			//Elemento span do item de estoque
			$iEst = $(iEst);

			//Elemento input com o estoque utilizdo
			$iQtdd = $(iEst).find("input[name*=lotecons_qtdd]");

			var vQtdExp = $iEst.attr("qtddispexp") || "";
			//Filtrar somente quem possui qtdexp
			if (vQtdExp !== "") {
				//gera array
				var vExp = vQtdExp.split(/d|e/)[1];

				if (vQtdExp.toLowerCase().indexOf("d") > 0) 
				{
					//multiplica restante pelo expoente
					var vSugest = vRestante / vExp;
					//transforma em string
					var numero = vSugest + ""
					//verificar se a string tem . se decimal
					var decimal = numero.indexOf(".");
					//se for transforma 0.545555 em 0.54
					if (decimal !== -1) {
						var vSugest = vSugest.toFixed(2);
					}
					var vSugestF = vSugest + "d" + vExp;

				} else {
					//multiplica restante pelo expoente
					var vSugest = vRestante / Math.pow(10, vExp);

					//transforma em string
					var numero = vSugest + ""
					//verificar se a string tem . se decimal
					var decimal = numero.indexOf(".");
					//se for transforma 0.545555 em 0.54
					if (decimal !== -1) {
						var vSugest = vSugest.toFixed(2);
					}
					var vSugestF = vSugest + "e" + vExp;
				}

				$iQtdd.attr("placeholder", vSugestF);
				//Calcula sugestão
			}
		});
	}
	
	if(getIdprodserv)
	{
		preencherFormula(getIdprodserv);
	}

	function preencherFormula(idprodserv)
	{
		$.ajax({
			type: "get",
			url : "ajax/formalizacao.php",
			data: { 
				idprodserv: idprodserv, 
				tipo: 'buscarFormula'
			},
			success: function(data){
				$("#lote_idprodservformula").html(data);		
				
				$.ajax({
					type: "get",
					url : "ajax/formalizacao.php",
					data: { 
						idprodserv: idprodserv, 
						tipo: 'dadosProdserv'
					},
					success: function(data){
						prodserv = jQuery.parseJSON(data)
						if(prodserv.especial == 'Y')
						{
							$('.div_cliente').show();	
							$('.div_sol_fab').show();	
						} else {
							$('.div_cliente').remove();	
							$('.div_sol_fab').remove();	
						}	
						
						if(prodserv.tipo != 'SERVICO')
						{
							$('.div_observacao').show();	
						} else {
							$('.div_observacao').remove();	
						}
							
					},
					error: function(objxmlreq){
						alert('Erro:<br>'+objxmlreq.status); 
					}
				});
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
        });
	}

	function preencherSolfab(idtabela, tipo)
	{
		if(tipo == 'cliente')
		{
			idpessoa = idtabela;
			idprodservformula = $('#lote_idprodservformula').val();
		} else {
			idpessoa = $('#_lote_idpessoa').attr('cbvalue');
			idprodservformula = idtabela;
		}

		$.ajax({
			type: "get",
			url : "ajax/formalizacao.php",
			data: { 
				idprodservformula: idprodservformula, 
				idpessoa: idpessoa, 
				status: $_1_u_formalizacao_status, 
				idprodserv: $('#_lote_idprodserv').attr('cbvalue'), 
				tipo: 'buscarSolfab'
			},
			success: function(data){
				jClientesSolfab = jQuery.parseJSON(data);
				autoCompleteSolfab();		
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
        });
	}
	//------- Funções Módulo -------
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>
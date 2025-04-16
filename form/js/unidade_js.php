<script type="text/Javascript">
		var idUnidade = <?= $_1_u_unidade_idunidade ?? 'false' ?>;

		var conselhosAreasDepsSetoresDisponiveisParaVinculo = <?= json_encode($conselhosAreasDepsSetoresDisponiveisParaVinculo) ?>;
		var conselhosAreasDepsSetoresSelecionado = <?= json_encode($conselhosAreasDepsSetoresSelecionado) ?>;

		var conselhos = <?= json_encode($conselhos) ?>;
		var areas = <?= json_encode($areas) ?>;
		var departamentos = <?= json_encode($departamentos) ?>;
		var setores = <?= json_encode($setores) ?>;
		var pessoas = <?=json_encode($pessoa) ?>;

		var tagsDisponiveisParaVinculo = <?= json_encode($tagsDisponiveisParaVinculo) ?>;
		var documentosDisponiveisParaVinculo = <?= json_encode($documentosDisponiveisParaVinculo) ?>;
		var modulosDisponiveisParaVinculo = <?= json_encode($modulosDisponiveisParaVinculo) ?>;
		var produtoDisponiveisParaVinculo = <?= json_encode($produtoDisponiveisParaVinculo) ?>;
		var planteisDisponiveisParaVinculo = <?= json_encode($planteisDisponiveisParaVinculo) ?>;

		var acao = '<?= $_acao ?>';
		var usuario = '<?= $_SESSION['SESSAO']['USUARIO'] ?>';
		var dataAtual = '<?= date('Y-m-d H:i:s') ?>';

		(function()
		{
			filter();
		})();

		// Filtar a consulta de areas, departamentos ou setores
		function filter(tipoobjeto = 'sgsetor', clear = false)
		{
			let valoresDisponiveisParaVinculo = conselhosAreasDepsSetoresDisponiveisParaVinculo,
				selectedValue = conselhosAreasDepsSetoresSelecionado;

			if(typeof selectedValue != 'object' || !selectedValue.length)
			{
				selectedValue = '* ERRO: VALOR [X] NÃO ENCONTRADO! *';
			} else 
			{
				selectedValue = selectedValue[0].label;
			}

			if(clear)
			{
				selectedValue = null;
			}

			if(tipoobjeto != undefined && tipoobjeto != "undefined" && tipoobjeto)
			{
				switch(tipoobjeto)
				{
					case 'sgconselho':
						valoresDisponiveisParaVinculo = conselhos;
						break;
					case 'sgarea':
						valoresDisponiveisParaVinculo = areas;
						break;
					case 'sgdepartamento':
						valoresDisponiveisParaVinculo = departamentos;
						break;
					case 'sgsetor':
						valoresDisponiveisParaVinculo = setores;
						break;
					case 'pessoas':
						valoresDisponiveisParaVinculo = pessoas;
						break;
					default:
						valoresDisponiveisParaVinculo = null
				}
			}

			if(valoresDisponiveisParaVinculo)
			{
				$("#area-dep-setor").autocomplete({
					source: valoresDisponiveisParaVinculo,
					delay: 0,
					create: function() {
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							lbItem = item.label;

							return $(`<li id="item-${item.id}">`)
								.append('<a>' + lbItem + '</a>')
								.appendTo(ul);
						};
					},
					select: function(event, ui) {
						CB.post({
							objetos: {
								"_x_i_unidadeobjeto_idempresa": ui.item.idempresa,
								"_x_i_unidadeobjeto_idunidade": idUnidade,
								"_x_i_unidadeobjeto_idobjeto": ui.item.id,
								"_x_i_unidadeobjeto_tipoobjeto": `${ui.item.tipo}`
							},
							parcial: true
						});
					}
				});
			} else {
				$("#area-dep-setor").val('ERRO: SQL NÃO RETORNOU NENHUM VALOR getJsonConselhoAreasDepSetores');
			}
		}

		if(acao != 'i')
		{
			$("#vinculo_tags").autocomplete({
				source: tagsDisponiveisParaVinculo
				,delay: 0
				,create: function(){
					$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					return $('<li>').append(`<a>${item.sigla}-${item.tag} ${item.descricao}</a>`).appendTo(ul);
					};
				}
				,select: function(event, ui){
					CB.post({
						objetos : {
							"_x_u_tag_idtag": ui.item.idtag
							,"_x_u_tag_idunidade": idUnidade
						}
						,parcial: true
					});
				}
			});

			$("#vinculo_planteis").autocomplete({
				source: planteisDisponiveisParaVinculo
				,delay: 0
				,create: function(){
					$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					return $('<li>').append(`<a>${item.sigla}-${item.plantel}</a>`).appendTo(ul);
					};
				}
				,select: function(event, ui){
					vincularUnidade(ui.item.idplantel, 'plantel');
				}
			});

			$("#vinculo_documentos").autocomplete({
				source: documentosDisponiveisParaVinculo
				,delay: 0
				,create: function(){
					$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					return $('<li>').append(`<a>${item.sigla}-${item.titulo} (${item.rotulo})</a>`).appendTo(ul);
					};
				}
				,select: function(event, ui){
					CB.post({
						objetos : {
							"_x_u_sgdoc_idsgdoc": ui.item.idsgdoc,
							"_x_u_sgdoc_idunidade": idUnidade
						}
						,parcial: true
					});
				}
			});

			$("#vinculo_modulos").autocomplete({
				source: modulosDisponiveisParaVinculo
				,delay: 0
				,create: function(){
					$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					return $('<li>').append(`<a>${item.sigla}-${item.rotulomenu}</a>`).appendTo(ul);
					};
				}
				,select: function(event, ui){
					vincularUnidadeObjeto(ui.item.modulo, 'modulo');
				}
			});

			$("#vinculo_produtos").autocomplete({
				source: produtoDisponiveisParaVinculo
				,delay: 0
				,create: function(){
					$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					return $('<li>').append(`<a>${item.sigla}-${item.descr}</a>`).appendTo(ul);
					};
				}
				,select: function(event, ui){
					vincularUnidadeObjeto(ui.item.idprodserv, 'prodserv');
				}
			});

			function vincularUnidadeObjeto(idObjeto, tipoObjeto)
			{
				CB.post({
					objetos : {
						"_x_i_unidadeobjeto_idempresa": gIdEmpresa,
						"_x_i_unidadeobjeto_idunidade": idUnidade,
						"_x_i_unidadeobjeto_idobjeto": idObjeto,
						"_x_i_unidadeobjeto_tipoobjeto": tipoObjeto,
						"_x_i_unidadeobjeto_criadopor": usuario,
						"_x_i_unidadeobjeto_criadoem": dataAtual,
						"_x_i_unidadeobjeto_alteradopor": usuario,
						"_x_i_unidadeobjeto_alteradoem": dataAtual,
					},
					parcial: true
				});
			}

			function desvincularUnidadeObjeto(idUnidadeObjeto)
			{
				if(!confirm('Deseja remover este vínculo?')) return false ;

				CB.post({
					objetos : {
						"_x_d_unidadeobjeto_idunidadeobjeto": idUnidadeObjeto,
					}
					,parcial: true
				});
			}

			function vincularUnidade(idObjeto, tipoObjeto)
			{
				let objetos = {};
				objetos[`_x_u_${tipoObjeto}_id${tipoObjeto}`] = idObjeto;
				objetos[`_x_u_${tipoObjeto}_idunidade`] = idUnidade;

				CB.post({
					objetos : objetos
					,parcial: true
				});
			}

			function desvincularUnidade(idObjeto, tipoObjeto)
			{
				if(!confirm('Deseja remover este vínculo?')) return false ;

				let objetos = {};
				objetos[`_x_u_${tipoObjeto}_id${tipoObjeto}`] = idObjeto;
				objetos[`_x_u_${tipoObjeto}_idunidade`] = null;

				CB.post({
					objetos : objetos
					,parcial: true
				});
			}
		}
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
	</script>
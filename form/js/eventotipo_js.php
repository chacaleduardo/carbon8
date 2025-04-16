<script language="javascript">

	var idEventoTipo = <?= $_1_u_eventotipo_ideventotipo ?? 'null' ?>;
	var unidadesDisponiveisParaVinculo = <?= JSON_ENCODE($unidadeDisponiveisParaVinculo) ?>;

	var acao 				= "<?= $_acao ?>";
	var ideventotipo        = <?= $_1_u_eventotipo_ideventotipo  ?? 'null' ?>;

	var $jconfig 	= $(`[name=_1_${CB.acao}_eventotipo_jconfig]`);
	var $jsonconfig = $(`[name=_1_${CB.acao}_eventotipo_jsonconfig]`);

	var count 			= 0;
	var ordem 			= 0;
	var optionCount 	= 0;
	var optionsBackup 	= [];
	var tipotag 		= $("#tipotag");
	var tipodocumento 	= $("#tipodocumento");
	var tipopessoa 		= $("#tipopessoa");
	var idPessoa    	= <?= $idPessoa ?? 'null' ?>;
	var config;

	var jsonconfig = {
		rnc: false,
		alerta: false,
		assinar: false,
		arquivo: false,
		calendario: false,
		permissoes: {
			setores: [],
			funcionarios: []
		},
		configprazo: false,
		configstatus: false,
		tags: [],
		pessoas: [],
		statuses: [],
		documentos: [],
		personalizados: []
	};

	var objectConfigVal = JSON.parse($jsonconfig.val() || null) || {},
		objectCamposObrigatorios = {};

	$('.selectpicker').selectpicker('render');

	// Fechar div
	$('.input-block').on('click', '#close-options', function()
	{
		let eventoTipoCamposId = $('.active-input').data('ideventocampo'),
			eventoTipoCamposCode = $(`#campo-code-${eventoTipoCamposId}`).val()
			eventoTipoCamposCodeDeletado = $(`#campo-codedeletado-${eventoTipoCamposId}`).val()
			eventoTipoCamposCodeVinculo = $(`#campo-codevinculo-${eventoTipoCamposId}`).val();

		CB.post({
			objetos: {
				"_200_u_eventotipocampos_ideventotipocampos": eventoTipoCamposId,
				"_200_u_eventotipocampos_code": eventoTipoCamposCode,
				"_200_u_eventotipocampos_codedeletado": eventoTipoCamposCodeDeletado,
				"_200_u_eventotipocampos_codevinculo": eventoTipoCamposCodeVinculo
			},
			parcial: true,
			refresh: false
		});

		esconderDivDeEscolhaCampo();
	});

	$('.input-block').on('blur', '.option', atualizarValoresDasOpcoesDoCampoAtivo);

	function atualizarValoresDasOpcoesDoCampoAtivo()
	{
		let arrValores = [],
			JQactiveInput = $('.active-input'),
			JQoptions = $('.active-input + div input');

		let $stringVal = "";

		JQoptions.each((key, element) => {
			let chave = removerCaracteresEspeciais($(element).val()).toLowerCase(),
				valor = $(element).val();
			if(valor)
			{
				if(key == 0)
				{
					return $stringVal += `SELECT "${chave}", "${valor}"`;
				}
				
				$stringVal += ` UNION SELECT "${chave}", "${valor}" `;
			}
		});

		$(`#campo-code-${JQactiveInput.data('ideventocampo')}`).val(`${$stringVal}`);
	}

	function atualizarValoresDasOpcoesDoCampodeletado(id, val)
	{
		let arrValores = [],
			JQactiveInput = $('.active-input'),
			JQoptions = $('.active-input + div input');

		let $stringVal = $(`#campo-codedeletado-${JQactiveInput.data('ideventocampo')}`).val();
	
		if($stringVal.length == 0)
		{
			$stringVal += `SELECT "${id}", "${val}"`;
		}else{
			$stringVal += ` UNION SELECT "${id}", "${val}" `;
		}

		$(`#campo-codedeletado-${JQactiveInput.data('ideventocampo')}`).val(`${$stringVal}`);
	}

	
	function atualizarValoresDasOpcoesDoCampoAtivoVinculo()
	{
		let arrValores = [],
			JQactiveInput = $('.active-input'),
			JQoptions = $('.active-input + div .campovinculo');

		let $stringVal = "";

		JQoptions.each((key, element) => {
			let chave = removerCaracteresEspeciais($(element).val()).toLowerCase(),
				campo = $(element).attr('campo');
			if(chave)
			{
				if(key == 0)
				{
					return $stringVal += `SELECT "${campo}-${chave}", "${campo}-${chave}"`;
				}
				
				$stringVal += ` UNION SELECT "${campo}-${chave}", "${campo}-${chave}" `;
			}
		});

		$(`#campo-codevinculo-${JQactiveInput.data('ideventocampo')}`).val(`${$stringVal}`);
	}

	function removerCaracteresEspeciais(string)
	{
		var find = ["ã","à","á","ä","â","è","é","ë","ê","ì","í","ï","î","ò","ó","ö","ô","ù","ú","ü","û","ñ","ç"];
 		var replace = ["a","a","a","a","a","e","e","e","e","i","i","i","i","o","o","o","o","u","u","u","u","n","c"];

		for (var i = 0; i < find.length; i++) {
			string = string.replace(new RegExp(find[i], 'gi'), replace[i]);
		}
		
		return string.replace(/[^a-z0-9]/gi,'');
	}

	// Montar div para adicionar campos
	$('.choose-input-type-btn').on('click', function()
	{
		let JQelement = $(this);

		esconderDivDeEscolhaCampo();

		JQelement.addClass('active-input');

		$(this).next().removeClass('hidden');
	});

	$('.input-block').on('click', '.add-option', function()
	{
		let JQoptionsElement = $('.active-input + div .options');

		JQoptionsElement.append(montarCampoInput());
	});

	$('.input-block').on('click', '.remove-option', function()
	{

		debugger;
		//dados para salvar na tabela como deletado
		// $(this).parent().parent().find('input').attr('id'); 
		// $(this).parent().parent().find('input').val()

		if($(this).parent().parent().find('input').val())
		{
			atualizarValoresDasOpcoesDoCampodeletado($(this).parent().parent().find('input').attr('id'), $(this).parent().parent().find('input').val() );
		};


		$(this).parent().parent().remove();

		atualizarValoresDasOpcoesDoCampoAtivo();
	});

	function montarCampoInput(id = false, name = false, placeholder = "Descrição")
	{
		let JQoptions = $(".option"),
			idExistentes = [];

		if(!id)
		{
			id = `option-1`;

            JQoptions.each((key, element) => {
                idExistentes.push(element.id);
            });

            while(idExistentes.includes(id))
            {
                id = `option-${parseInt(Math.random() * 50)}`;
            }	
		}

		if(!name)
		{
			name = id.replaceAll('-', '_');
		}

		return `<div class="w-100 d-flex flex-wrap">
					<div class="col-sm-10 px-0">
						<input id="${id}" type="text" class="form-control mb-2 option mb-2" placeholder="${placeholder}" />
					</div>
					<div class="col-sm-2 d-flex align-items-center">
						<i class="fa fa-trash pointer remove-option" title='Remover opção'></i>
					</div>
				</div>`;
	}

	function esconderDivDeEscolhaCampo()
	{
		$('.active-input + div').addClass('hidden');
		$('.active-input').removeClass('active-input');
	}

	// Inserir unidades
	if(idEventoTipo) 
	{
		$("#unidades").autocomplete({
			source: unidadesDisponiveisParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					lbItem = item.unidade;
					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_unidadeobjeto_idempresa": ui.item.idempresa,
						"_x_i_unidadeobjeto_idunidade": ui.item.idunidade,
						"_x_i_unidadeobjeto_idobjeto": idEventoTipo,
						"_x_i_unidadeobjeto_tipoobjeto": `eventotipo`
					},
					parcial: true
				});
			}
		});

		function desvincularUnidade(inid)
		{
			CB.post({
				objetos: {
					"_x_d_unidadeobjeto_idunidadeobjeto": inid
				},
				parcial: true,
			});
		}
	}

	function novoadd(inideventotipo){
		CB.post({
			objetos: "_x_i_eventotipoadd_ideventotipo="+inideventotipo,
			parcial: true
		});
	}	

	if(objectConfigVal.campos_obrigatorios && Object.keys(objectConfigVal.campos_obrigatorios).length)
	{
		objectCamposObrigatorios = objectConfigVal.campos_obrigatorios;

		for(const property in objectCamposObrigatorios) {
			let title = objectCamposObrigatorios[property] ? 'Tornar opcional' : 'Tornar obrigatório';

			$(`#obrigatorio-${property}`).attr('checked', objectCamposObrigatorios[property]);
			$(`#obrigatorio-${property} + label`).attr('title', title);
		}
	}

	$('.selecttipo').selectpicker({
		liveSearch: true
	});

	function criaOption(nome, count) 
	{
		if (nome === undefined) {
			nome = '';
		}

		let novaOption = '<div class="row novooption option'+count+'" data-campo="'+count+'">\
				<div class="col-lg-3">\
					<label>Descrição:</label>\
					<input type="text" placeholder="Nome da opção" id="descricaoOption'+count+'" value="'+nome+'" onchange="atualizaOption(this, '+count+');">\
				</div>\
				<div class="col-lg-3">\
					<span style="margin-top: 18px"\
						class="btn btn-sm btn-danger size3" id="deletarOption'+count+'" onclick="deletarOption(this, '+count+');"\
						title="Excluir"><i class="fa fa-minus pointer"></i></span>\
				</div>\
			</div>';

		return novaOption;
	}

	function criaPermissao(item, title, modulo, status) {
		
		return '<tr id="'+modulo+item.value+'">\
					<td style="min-width: 10px;" id="statuses">\
						<span class="circle button-blue"></span>\
					</td>\
					<td >'+item.label+'</td>\
					<td>\
						<a class="fa fa-bars fa-1x pointer hoverazul" title="'+title+'"\
							onclick="janelamodal(\'?_modulo='+modulo+'&_acao=u&id'+modulo+'='+item.value+'\')"></a>\
					</td>\
					<td align="center">\
						<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable"\
							onclick="removePermissao(this, '+item.value+', \''+modulo+'\')" title="Excluir"></a>\
					</td>\
				</tr>';
	}

	function toggle(inId,inChk){

		var vYN = (inChk.checked)?"Y":"N";
		
		var strPost = "_ajax_u_eventotipocampos_ideventotipocampos="+inId
					+ "&_ajax_u_eventotipocampos_visivel="+vYN;

		CB.post({
			objetos: strPost
			,refresh: false
		});
	}

	function atualizavalor(vthis,inideventotipo){		
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipo_ideventotipo":inideventotipo
				,"_x_u_eventotipo_tagtipoobj":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizavalordoc(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipo_ideventotipo":inideventotipo
				,"_x_u_eventotipo_sgdoctipoobj":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizaValorEmpresa(element, ideventotipo)
	{
		let strval= $(element).val();
		CB.post({
			objetos: {
				"_x_u_eventotipo_ideventotipo":ideventotipo
				,"_x_u_eventotipo_empresaobj":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizaValorSetor(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipo_ideventotipo":inideventotipo
				,"_x_u_eventotipo_setor_idempresa":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizaValorDepartamento(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipo_ideventotipo":inideventotipo
				,"_x_u_eventotipo_departamento_idempresa":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizaValorArea(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipo_ideventotipo":inideventotipo
				,"_x_u_eventotipo_area_idempresa":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizavalorpessoa(vthis,inideventotipo)
	{	
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipo_ideventotipo":inideventotipo
				,"_x_u_eventotipo_tipopessoaobj":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizavalorad(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipoadd_ideventotipoadd":inideventotipo
				,"_x_u_eventotipoadd_tagtipoobj":strval
			}
			,parcial: true
			,refresh:false
		});
	}
	function atualizavalorprodservad(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipoadd_ideventotipoadd":inideventotipo
				,"_x_u_eventotipoadd_prodservtipoobj":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizavalordocad(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipoadd_ideventotipoadd":inideventotipo
				,"_x_u_eventotipoadd_sgdoctipoobj":strval
			}
			,parcial: true
			,refresh:false
		});
	}

	function atualizavalorpessoaad(vthis,inideventotipo)
	{
		var strval= $(vthis).val();
		CB.post({
			objetos: {
				"_x_u_eventotipoadd_ideventotipoadd":inideventotipo
				,"_x_u_eventotipoadd_tipopessoaobj":strval

			}
			,parcial: true
			,refresh:false
		});
	}

	function atcampo(inideventotipo,incampo,invalor)
	{	
		CB.post({
			objetos: `_1_u_eventotipo_ideventotipo=${inideventotipo}&_1_u_eventotipo_${incampo}=${invalor}`
			,parcial: true
			,refresh:false
		});
	}

	function eventotipocampos(vthis, ideventotipocampos, invis, nord, tipo, tipocamposobj, ideventotipoadd)
	{
		if(invis == "N"){
			nord = '';
		}
			
		if(tipo == 'eventotipocampos'){
			CB.post({
				objetos: `_1_u_eventotipocampos_ideventotipocampos=${ideventotipocampos}&_1_u_eventotipocampos_visivel=${invis}&_1_u_eventotipocampos_ord=${nord}`
				,parcial: true
			});
		}

		if(tipo == 'eventotipoadd'){
			if($(vthis).is(':checked') == true){
				//Não pode deixar espaço na vírgula por causa da consulta FIND_IN_SET
				tipocamposobj = (tipocamposobj.length == 0) ? ideventotipocampos : tipocamposobj + "," + ideventotipocampos;
			} else {
				var numeros = tipocamposobj.split(',');
				var index = numeros.indexOf(`${ideventotipocampos}`);
				if (index > -1) {
					numeros.splice(index, 1);
				}
				tipocamposobj = numeros.toString();
			}

			CB.post({
				objetos: `_1_u_eventotipoadd_ideventotipoadd=${ideventotipoadd}&_1_u_eventotipoadd_tipocamposobj=${tipocamposobj}`, 
				parcial: true
			});
		}
	}

	function atcampoad(inideventotipoadd,incampo,invalor){
		CB.post({
			objetos: "_1_u_eventotipoadd_ideventotipoadd="+inideventotipoadd+"&_1_u_eventotipoadd_"+incampo+"="+invalor 
			,parcial: true	
			,refresh:false
		});
	}

	function equipamentocheckedad(event,inideventotipo) 
	{
		if (event.checked) {
			$('#tagtipoobjad'+inideventotipo).show();			
			atcampoad(inideventotipo,'tag','Y');
		} else {
			$('#tagtipoobjad'+inideventotipo).hide();			
			$('option[value="tag"]').remove();
			CB.post({			
				objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_tagtipoobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_tag=N" 
				,parcial: true				
			});
		}
	}

	function prodservcheckedad(event,inideventotipo) 
	{
		if (event.checked) {			
			$('#prodservtipoobjad'+inideventotipo).show();
			atcampoad(inideventotipo,'prodserv','Y');
		} else {
			
			$('[data-id="prodserv"]').hide();		
			$('#prodservtipoobjad'+inideventotipo).remove();
			CB.post({			
				objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_prodservtipoobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_prodserv=N" 
				,parcial: true				
			});
		}
	}

	function documentocheckedad(event,inideventotipo) 
	{
		if (event.checked) {
			
			$('#sgdoctipoobjad'+inideventotipo).show();
			atcampoad(inideventotipo,'sgdoc','Y');
		} else {
			
			$('[data-id="tipodocumento"]').hide();
		
			$('#sgdoctipoobjad'+inideventotipo).remove();
			CB.post({			
				objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_sgdoctipoobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_sgdoc=N" 
				,parcial: true				
			});
		}
	}

	function pessoacheckedad(event,inideventotipo)
	{
		if (event.checked) {
			
			$('#tipopessoaobjad'+inideventotipo).show();
			atcampoad(inideventotipo,'pessoa','Y');
		} else {
			
			$('#tipopessoaobjad'+inideventotipo).hide();
		
			$('option[value="pessoa"]').remove();
			CB.post({			
				objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_tipopessoaobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_pessoa=N" 
				,parcial: true				
			});
		}
	}

	function equipamentochecked(event,inideventotipo) 
	{
		if (event.checked) {
			$('#tagtipoobj').show();			
			atcampo(inideventotipo,'tag','Y');
		} else {
			$('#tagtipoobj').hide();	
			$('option[value="tag"]').remove();
			CB.post({			
				objetos:"_x_u_eventotipo_ideventotipo="+inideventotipo+"&_x_u_eventotipo_tagtipoobj=''&_1_u_eventotipo_ideventotipo="+inideventotipo+"&_1_u_eventotipo_tag=N" 
				,parcial: true				
			});
		}
	}

	function documentochecked(event,inideventotipo) 
	{
		if (event.checked) {			
			$('#sgdoctipoobj').show();
			atcampo(inideventotipo,'sgdoc','Y');
		} else {			
			$('[data-id="tipodocumento"]').hide();		
			$('#sgdoctipoobj').remove();
			CB.post({			
				objetos:"_x_u_eventotipo_ideventotipo="+inideventotipo+"&_x_u_eventotipo_sgdoctipoobj=''&_1_u_eventotipo_ideventotipo="+inideventotipo+"&_1_u_eventotipo_sgdoc=N" 
				,parcial: true				
			});
		}
	}

	function pessoachecked(event,inideventotipo) 
	{
		if (event.checked) {			
			$('#tipopessoaobj').show();
			atcampo(inideventotipo,'pessoa','Y');
		} else {			
			$('#tipopessoaobj').hide();		
			$('option[value="pessoa"]').remove();
			CB.post({			
				objetos:"_x_u_eventotipo_ideventotipo="+inideventotipo+"&_x_u_eventotipo_tipopessoaobj=''&_1_u_eventotipo_ideventotipo="+inideventotipo+"&_1_u_eventotipo_pessoa=N" 
				,parcial: true				
			});
		}
	}

	function eventotipochecked(event,inideventotipo, incampo)
	{
		if (event.checked) {
			atcampo(inideventotipo,incampo,'Y');
		} else {
			atcampo(inideventotipo,incampo,'N');
		}
	}

	function removePermissao(elemento, id, modulo) 
	{	
		let removed = false;

		if (modulo == 'pessoa') {

			let i = 0, len = jsonconfig.permissoes.funcionarios.length;
			
			for (i = 0; i < len; i++) {

				if (jsonconfig.permissoes && jsonconfig.permissoes.funcionarios && jsonconfig.permissoes.funcionarios[i] && jsonconfig.permissoes.funcionarios[i].value == id) 
				{					
					jsonconfig.permissoes.funcionarios.splice(i, 1);
					removed = true;
					break;
				}
			}
		} else {
			
			let i = 0, len = jsonconfig.permissoes.setores.length;
			for (i; i < len; i++) 
			{	
				if (jsonconfig.permissoes && jsonconfig.permissoes.setores && jsonconfig.permissoes.setores[i] && jsonconfig.permissoes.setores[i].value == id) {					
					jsonconfig.permissoes.setores.splice(i, 1);
					removed = true;
					break;
				}
			}
		
		}

		if (removed) {
			$(elemento).closest('tr').remove();
		}
	}

	function loadPermissoes() 
	{
		return new Promise(function(resolve, reject) {
			$.ajax({
				type: "get",
				url: "ajax/eventotipo.php?vopcao=carregaparticipantes&videventotipo=" + ideventotipo,
				success: function(data) {
					if(data.error)
                    {
                        return alertAtencao(data.error);
                    }
					
					let dataformat = data.replace(/\\/g, '');
					let permissoes = JSON.parse(dataformat);
			
					if (permissoes !== undefined) {
						resolve(permissoes);
					} else {
						resolve("error");
					}
				}
			});
		});
	}

	function setConf(){

		let cnf = jsonStr2Object($jconfig.val())||[{}];
		let k = moment().format("DDMMYYHHmm");
		let ncnf = {[k]:jsonconfig};
		cnf.push(ncnf);
		$jconfig.val(JSON.stringify(cnf));
	}

	tipotag.selectpicker("refresh");
	tipopessoa.selectpicker("refresh");
	tipodocumento.selectpicker("refresh");

	if ($("[name=_1_u_evento_idevento]").val()) {
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_evento_idevento]").val(),
			tipoObjeto: 'evento',
			idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
		});
	}

	function getProxes()
	{
		//a variável $tr vem do contexto superior
		var $proxes = $tr.find(".proxes .iproxstatus");
		var oProxes={};
		if(!$proxes || $proxes.length==0){
			return false;
		}else{
			//Loop nas opções selecionadas para recuperar somente as que estão marcadas
			$proxes.each(function(ipt,opt){
				$opt=$(opt);
				if($(opt).hasClass("selecionado")){
					oProxes[$opt.attr("token")]={};
				}
			});
			return oProxes;
		}
	}

	function getStatuses(){

		var statuses=[];
		var chinicial = '';
		var chfinal = '';	
		var chdono = '';
		var chndono = '';
		var chocultaind = '';
		var chdesocultaind = '';
		var chocultacri = '';
		var chdesocultacri = '';
		var chexclui = '';
		var chassina = '';
		var chrestaura = '';
		var choculta = '';
		var chdesoculta = '';
		$.each($("#statusDisponiveis tr"),function(i,tr){
			$tr=$(tr);
			$status=$tr.find("input.statuses");
			$acao=$tr.find("input.acoes");
			var token=$tr.attr("token");
			var prioridade=$tr.find("input.prioridade");
			var inicial=$tr.find("input.inicial");
			var acao=$tr.find("input.acao");
			var final=$tr.find("input.final");
			var dono=$tr.find("input.dono");
			var ndono=$tr.find("input.ndono");
			var ocultaind=$tr.find("input.ocultaind");
			var desocultaind=$tr.find("input.desocultaind");
			var ocultacri=$tr.find("input.ocultacri");
			var desocultacri=$tr.find("input.desocultacri");
			var assina=$tr.find("input.assina");
			var exclui=$tr.find("input.exclui");
			var restaura=$tr.find("input.restaura");
			var oculta=$tr.find("input.oculta");
			var desoculta=$tr.find("input.desoculta");
			var color=$tr.find(".colorpicker").attr("color");
			var proxes=getProxes();
			

			//Verifica se já existia um status tokenizado (sem acentos ou espaços)
			if(!$status.val()||$status.val()==""){
				alertAtencao("Informe corretamente o Status!","Campo vazio",1000);
				$status.focus().addClass("highlight");
				statuses=false;
				return false;//break loop

			}else if(!color || color.lenght==0){
				alertAtencao("Informe corretamente uma cor para o Status!","Status sem cor",1000);
				$tr.find(".colorpicker").addClass("highlight");
				statuses=false;
				return false;//break loop
			} /*else if(!proxes){
				alertAtencao("Informe corretamente os Próximos Status!","Status incompleto",1000);
				$tr.find("td.proxes").addClass("highlight");
				statuses=false;
				return false;//break loop
			}*/ else{
				var sNovoToken=(!token||token.length==0)?$status.val().replace(/[^a-zA-Z]+/g, '').toLowerCase():token;
				var sColor=color;
				console.log(inicial[0]);
				if (inicial[0] !== 'undefined'){
					if (inicial[0].checked){
						chinicial = true;
					}else{
						chinicial = false;
					}	
				}
				if (final[0] !== 'undefined'){
					if (final[0].checked){
						chfinal = true;
					}else{
						chfinal = false;
					}	
				}
				if (dono[0] !== 'undefined'){
					if (dono[0].checked){
						chdono = true;
					}else{
						chdono = false;
					}	
				}
				if (ndono[0] !== 'undefined'){
					if (ndono[0].checked){
						chndono = true;
					}else{
						chndono = false;
					}	
				}
				if (ocultaind[0] !== 'undefined'){
					if (ocultaind[0].checked){
						chocultaind = true;
					}else{
						chocultaind = false;
					}	
				}
				if (desocultaind[0] !== 'undefined'){
					if (desocultaind[0].checked){
						chdesocultaind = true;
					}else{
						chdesocultaind = false;
					}	
				}
				if (ocultacri[0] !== 'undefined'){
					if (ocultacri[0].checked){
						chocultacri = true;
					}else{
						chocultacri = false;
					}	
				}
				if (desocultacri[0] !== 'undefined'){
					if (desocultacri[0].checked){
						chdesocultacri = true;
					}else{
						chdesocultacri = false;
					}	
				}
				if (assina[0] !== 'undefined'){
					if (assina[0].checked){
						chassina = true;
					}else{
						chassina = false;
					}	
				}
				if (exclui[0] !== 'undefined'){
					if (exclui[0].checked){
						chexclui = true;
					}else{
						chexclui = false;
					}	
				}
				if (restaura[0] !== 'undefined'){
					if (restaura[0].checked){
						chrestaura = true;
					}else{
						chrestaura = false;
					}	
				}
				if (oculta[0] !== 'undefined'){
					if (oculta[0].checked){
						choculta = true;
					}else{
						choculta = false;
					}	
				}
				if (desoculta[0] !== 'undefined'){
					if (desoculta[0].checked){
						chdesoculta = true;
					}else{
						chdesoculta = false;
					}	
				}
				statuses.push({
					"status":$status.val()
					,"token":sNovoToken
					,"color":sColor
					,"proxes":proxes
					,"prioridade":prioridade.val()
					,"inicial":chinicial
					,"final":chfinal
					,"dono":chdono
					,"ndono":chndono
					,"ocultaind":chocultaind
					,"desocultaind":chdesocultaind
					,"ocultacri":chocultacri
					,"desocultacri":chdesocultacri
					,"assina":chassina
					,"exclui":chexclui
					,"restaura":chrestaura
					,"oculta":choculta
					,"desoculta":chdesoculta
					,"acao":$acao.val()
					});

			}
		});
		return statuses;
	}

	function atualizaToken(e){
		$(e).closest("tr").attr("token",e.value);
	}

	function deletarOption(element, index) 
	{
		let indiceCampo = localStorage.getItem('lastIndiceCampo');
		let excluido = false;

		for(var j = 0; j < camposPersonalizados.length; j++) {
			if (camposPersonalizados[j].indice === parseInt(indiceCampo)) {
				for(var i = 0; i < camposPersonalizados[j].options.length; i++) {

					$("#descricaoOption"+i).closest('.novocampo').removeClass("option"+i);
					$("#descricaoOption"+i).closest('.novocampo').addClass("option"+(i-1));
					$("#descricaoOption"+i).closest('.novocampo').attr('data-campo', (i-1));

					$("#descricaoOption"+i).attr('onchange', 'atualizaOption(this, '+(i-1)+')');
					$("#descricaoOption"+i).attr('id', 'descricaoOption'+(i-1));

					$("#deletarOption"+i).attr('onclick', 'deletarOption(this, '+(i-1)+')');
					$("#deletarOption"+i).attr('id', 'deletarOption'+(i-1));
				}
			}
		}

		$(element).closest('.novooption').remove();

		for(var j = 0; j < camposPersonalizados.length; j++) {
			if (camposPersonalizados[j].indice === parseInt(indiceCampo)) {
				for(var i = 0; i < camposPersonalizados[j].options.length; i++) {

					if (camposPersonalizados[j].options[i].indice === index) {
						camposPersonalizados[j].options.splice(i, 1);
						excluido = true;
					}
					
					if (excluido && i != camposPersonalizados[j].options.length) {
						camposPersonalizados[j].options[i].indice--;
					}

				}
				optionCount = camposPersonalizados[j].options.length;
			}
		}
	}

	function atualizaOption(element, index) {

		let indiceCampo = localStorage.getItem('lastIndiceCampo');
		
		for(var j = 0; j < camposPersonalizados.length; j++) {
			if (camposPersonalizados[j].indice === parseInt(indiceCampo)) {
			
				for(var i = 0; i < camposPersonalizados[j].options.length; i++) {
					if (camposPersonalizados[j].options[i].indice === index) {
						camposPersonalizados[j].options[i].nome = $(element).val();
					}
				}
			}
		}
	}

	function atualizaCor() {
		console.log($("#color").val());
		$("#color").attr("value", $("#color").val());
	}

	function novoStatus()
	{
		$statusDisp=$("#statusDisponiveis");

		$statusDisp.append(
			skelStatus
				.replace(/%itr%/g,($("[itr]").length))
				.replace(/%status%/g,'x" onkeyup="atualizaToken(this)' )
				.replace(/%icocor%/g,'fa-circle-o cinza blink')
				.replace(/%prioridade%/g,($("[itr]").length)+1)
				.replace(/%.*%/g,'')
				
		);
	}
	
	contador = 0;
	config=jsonconfig;
    loadPermissoes().then((permissions) => {
        if (permissions) 
		{
            jsonconfig.permissoes = { 
                "setores": [], 
                "funcionarios": []
            };

            let i = 0;
            let len = permissions.length;
            let setores = [];
            let automaticos = [];
            
            for (i = 0; i < len; i++) {                
                if (permissions[i].tipo == 'pessoa') {

                    if (permissions[i].inseridomanualmente == 'N') {
                        automaticos.push(permissions[i]);
                    } else {
                        let row = criaPermissao(permissions[i], 'Funcionário', 'pessoa', permissions[i].status);
                        $('#tblPermissao').append(row);
                        jsonconfig.permissoes.funcionarios.push(permissions[i]);
                    }

                } else {

                    jsonconfig.permissoes.setores.push(permissions[i]);
                    let row = criaPermissao(permissions[i], 'Setor', 'sgsetor', permissions[i].status);
                    setores.push(permissions[i]);
                    $('#tblPermissao').append(row);                    
                }               
            }

            automaticos.forEach(function(pessoa) {              
                let row = criaPermissao(pessoa, 'Pessoa', 'pessoa', pessoa.status);
               
                $(row).insertAfter("#sgsetor"+pessoa.idobjetoext);
                
                setores.forEach(function(setor) {
                    if (setor.value == pessoa.idobjetoext) {
                        let nomePessoa = $("#pessoa"+pessoa.value).find('td')[1];
                        $(nomePessoa).text($(nomePessoa).text()+' - '+setor.label);
                    }
                });
            });
        }
    });

	if (config.personalizados &&
		config.personalizados.length > 0) {		
		config.personalizados.forEach(function(campo) {
			if (campo.vinculo != '') {
				$(".vinculo"+contador+" option[value='" + campo.vinculo +"']").attr("selected","selected");
			}

			contador++;
		});		
	}

	function montaJsonconf() {
		statuses  = getStatuses();
		if(statuses){
			jsonconfig.statuses=statuses;
			setConf();
			return true;
		}else{
			console.warn("Erro ao recuperar os status!");
			return false
		}
	}

	CB.prePost = function()
	{
		objectConfigVal.campos_obrigatorios = objectCamposObrigatorios;

		$jsonconfig.val(JSON.stringify(objectConfigVal));

		if(!montaJsonconf()){
			return false;
		}
	};

	//Estende o método selectColor padrão, para atualizar as cores dos ícones de próximo status, relacionados
	$(".colorpalette").on("selectColor",function(e) {
		sToken = $(e.target).attr("token");
		if(sToken && sToken!==""){
			$("i[token="+sToken+"]").css("color",e.color);
		}
	});

	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape

	$("#statusDisponiveis").sortable({
	update:function(e,o){
		$('tr').find('.prioridade').each(function( k, v ) {
		// alert( "Key: " + k + ", Value: " + v.value );
		v.value= k;
		});
	}});
   
	function toggledataprazo(inColuna,inRadio){
		if(inColuna=='prazo'){
			var prazo='Y';
		}else{
			var prazo='N';
		}
		if(inColuna && inRadio.checked){
			CB.post({
				objetos: "_x_u_eventotipo_ideventotipo="+$(":input[name=_1_"+CB.acao+"_eventotipo_ideventotipo]").val()+"&_x_u_eventotipo_prazo="+prazo
			});
		}
	}

	function toggleassinatura(inColuna,inRadio)
	{
		if($("#assinart").is(":checked") === true){
			var btat='Y';
		}else{
			var btat='N';
		}
		if($("#assinarp").is(":checked") === true){
			var btap='Y';
		}else{
			var btap='N';
		}

		CB.post({
			objetos: "_x_u_eventotipo_ideventotipo="+$(":input[name=_1_"+CB.acao+"_eventotipo_ideventotipo]").val()+"&_x_u_eventotipo_assinarp="+btap+"&_x_u_eventotipo_assinart="+btat
		});

	}
	$('.show-tick').css('width','100%');
	$('.show-tick').css('padding','0px');

	$('button.btn.dropdown-toggle.btn-default').css('width','170px');

	function minieventocheckedad(event,inideventotipo) {
		if(event.checked) {
			$('#planilhagrade'+inideventotipo).hide();
			$('.equipamento'+inideventotipo).attr('disabled', true);
			$('.documento'+inideventotipo).attr('disabled', true);
			$('.pessoa'+inideventotipo).attr('disabled', true);
			$('.prodserv'+inideventotipo).attr('disabled', true);
			CB.post({			
				objetos:"_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_minievento=Y&_1_u_eventotipoadd_criasolmat=N&_1_u_eventotipoadd_tag=N&_1_u_eventotipoadd_sgdoc=N&_1_u_eventotipoadd_pessoa=N&_1_u_eventotipoadd_prodserv=N" 
				,parcial: true				
			});
		} else {
			$('#planilhagrade'+inideventotipo).show();
			$('.equipamento'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.documento'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.pessoa'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.prodserv'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			atcampoad(inideventotipo,'minievento','N');	
		}
	}

	function eventoTipoCamposCheckedad(event,inideventotipo) {
		if(event.checked) {
			$('#planilhagrade'+inideventotipo).hide();
			$('.equipamento'+inideventotipo).attr('disabled', true);
			$('.documento'+inideventotipo).attr('disabled', true);
			$('.pessoa'+inideventotipo).attr('disabled', true);
			$('.prodserv'+inideventotipo).attr('disabled', true);
			CB.post({			
				objetos: `_1_u_eventotipoadd_ideventotipoadd=${inideventotipo}&_1_u_eventotipoadd_tipocampos=Y&_1_u_eventotipoadd_tag=N&_1_u_eventotipoadd_sgdoc=N&_1_u_eventotipoadd_pessoa=N&_1_u_eventotipoadd_prodserv=N`
				,parcial: true				
			});
		} else {
			$('#planilhagrade'+inideventotipo).show();
			$('.equipamento'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.documento'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.pessoa'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.prodserv'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			atcampoad(inideventotipo,'tipocampos','N');	
		}
	}

	function geraSolmatCheck(element, inideventotipo) {
		if(element.checked) {
			$('#planilhagrade'+inideventotipo).hide();
			$('.equipamento'+inideventotipo).attr('disabled', true);
			$('.documento'+inideventotipo).attr('disabled', true);
			$('.pessoa'+inideventotipo).attr('disabled', true);
			$('.prodserv'+inideventotipo).attr('disabled', true);
			CB.post({			
				objetos:"_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_criasolmat=Y&_1_u_eventotipoadd_minievento=N&_1_u_eventotipoadd_tag=N&_1_u_eventotipoadd_sgdoc=N&_1_u_eventotipoadd_pessoa=N&_1_u_eventotipoadd_prodserv=N" 
				,parcial: true				
			});
		} else {
			$('#planilhagrade'+inideventotipo).show();
			$('.equipamento'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.documento'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.pessoa'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			$('.prodserv'+inideventotipo).removeAttr('disabled').attr('readonly', true);
			atcampoad(inideventotipo,'criasolmat','N');	
		}
	}

	function setObrigatorio(element, campo)
	{
		let title = element.checked ? 'Tornar opcional' : 'Tornar obrigatório';

		$(`#${element.id} + label`).attr('title', title);

		if(!Object.keys(objectCamposObrigatorios).length)
		{
			objectCamposObrigatorios[campo] = element.checked;

			return true;
		}

		for(let property in objectCamposObrigatorios)
		{
			if(property != campo)
			{
				objectCamposObrigatorios[campo] = element.checked;

				continue;
			}

			objectCamposObrigatorios[property] = element.checked;
		}

		CB.post();
	}

	$('.larguracoluna-item').on('click', function()
	{
		let tooltip = $(`[data-idinput='${$(this).attr('for')}']`),
			tooltipVisiveis = $(`.width-tooltip:not(.hide)`);

		tooltipVisiveis.addClass('hide');
		$(`.width-tooltip.hide.show-tooltip`).removeClass('show-tooltip');

		if(!tooltip.hasClass('show-tooltip'))
		{
			tooltip.addClass('show-tooltip');
			return tooltip.removeClass('hide');
		}

		tooltip.addClass('hide');
		tooltip.removeClass('show-tooltip');
	});

	$('.width-tooltip').on('click', '.layout-item', function(e)
	{
		let JQelement = $(this),
			JQtooltip = $(e.delegateTarget),
			JQvaloresAtivos = JQtooltip.find('.active'),
			larguraColuna = JQelement.data('value'),
			campoLayout = $(`#${JQtooltip.data('idinput')}`);

		JQvaloresAtivos.removeClass('active');
		// campoLayout.val(larguraColuna);	
		
		if(!JQelement.hasClass('active'))
		{
			CB.post({
				objetos: {
					_x_u_eventotipocampos_ideventotipocampos: JQtooltip.data('ideventotipocampos'),
					_x_u_eventotipocampos_larguracoluna: larguraColuna,
				},
				parcial: true,
				refresh: false
			});
		}

		JQelement.addClass('active');
	});

	$(document).on('click', function(e) {
		let JQtooltip = $('.width-tooltip');

		if(!$(e.target).parentsUntil('.input-block').parent().find('> .width-tooltip').length && !$(e.target).parentsUntil('.input-block').parent().find(' > .width-tooltip').length)
			$('.width-tooltip').addClass('hide');
	});

	function atualizaOptionVinculo(vthis)
	{	
		ideventotipocamposvinculo = $(vthis).val();
		ideventotipocampos = $(vthis).attr('ideventotipocampos');
		
		if(ideventotipocamposvinculo.length == 0){
			CB.post({
				objetos: {
					"_200_u_eventotipocampos_ideventotipocampos": ideventotipocampos,
					"_200_u_eventotipocampos_ideventotipocamposvinculo": null,
					"_200_u_eventotipocampos_codevinculo": ''
				},
				parcial: true,
				refresh: false
			});
		} else {
			CB.post({
				objetos: {
					"_200_u_eventotipocampos_ideventotipocampos": ideventotipocampos,
					"_200_u_eventotipocampos_ideventotipocamposvinculo": ideventotipocamposvinculo
				},
				parcial: true,
				refresh: false
			});
		}
	}

	function addRelacionamento(ideventotipo){
		inputs = '';
		$('.relacionamentoclass').each(function(key, element){
			inputs += `&_${$(element).attr('name')}=${$(element).val()}`;
		});

		CB.post({
			objetos:`_ajax_i_eventorelacionamento_ideventotipo=${ideventotipo}${inputs}`, 
			parcial: true
		});
	}
</script>
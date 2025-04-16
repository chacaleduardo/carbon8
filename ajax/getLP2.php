<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");

if (validaTokenReduzido()["sucesso"] === false)
{
	die("");
}

require_once(__DIR__ . "/../form/controllers/_lp_controller.php");

$_idempresa = d::b()->real_escape_string($_GET['_idempresa']);
$_modulo = d::b()->real_escape_string($_GET['_modulo']);

if ($_POST['idlp'])
{
	$rsemp = _LpController::buscarLpEEmpresa($_POST["idlp"], null)[0];
	$_1_u__lp_idempresa =  $rsemp['idempresa'];
	$_sigla = $rsemp['sigla'];
	$_corsistema = $rsemp['corsistema'];

	function buscarPessoasSetorDepartamentoArea($id, $tp)
	{
		return _LpController::buscarPessoasSetorDepartamentoArea($tp, $id);
	}

	function retArrModSelecionados($inIdLp)
	{
		global $_sigla;
		global $_1_u__lp_idempresa;
		return _LpController::buscarModulosSelecionados2($_1_u__lp_idempresa, $inIdLp, $_sigla);
	}

	function retArrModFilho($inIdLp, $inmod, $inidempLP)
	{
		global $_sigla;
		return _LpController::buscarModulosFilhos($inidempLP, $inIdLp, getidempresa('u.idempresa', 'unidade'), $inmod, $_sigla);
	}

	function retArrModFilho2($inIdLp, $inmod, $inidempLP)
	{
		global $_sigla;

		if ($modulovinc = getModReal($inmod)) {
			$inmod = $modulovinc;
		}

		return _LpController::buscarModulosFilhosDosFilhos($inidempLP, $inIdLp, $inmod, $_sigla);
	}

	function retArrModRep($inIdLp, $inmod)
	{
		return _LpController::buscarRepsDoModulo($inIdLp, $inmod);
	}

	$modulos = retArrModSelecionados($rsemp['idlp']);

	function printaAtalhoModulo($mod)
	{
		$link = '?_modulo=_modulo&_acao=u&idmodulo=' . $mod;
		return '<a target="_blank" onclick="janelamodal(\'' . $link . '\')">
			<i class="fa fa-navicon azul pointer"></i>
		</a>';
	}

	function printaAtalhoRelatorio($mod)
	{
		$link = '?_modulo=_rep&_acao=u&idrep=' . $mod;
		echo '<a target="_blank" onclick="janelamodal(\'' . $link . '\')">
			<i class="fa fa-navicon azul pointer"></i>
		</a>';
	}

	function printaModuloitem($modulos, $colapse)
	{
		echo '<div class="row"><div class="w-100">';
		foreach ($modulos as $modf => $valf) {
			echo '<div class="modulos">' . $valf['rotulomenu'] . '</div>';
		}
		echo '</div>
		</div>';
	}
	
	function printModuloRelatorio($idlp,$modf, $modulof, $mod)
	{
		$checkboxR = printaCheckboxRelatorio($modulof, $idlp, 'r', 'leitura');
		$checkboxW = printaCheckboxRelatorio($modulof, $idlp, 'w', 'escrita');
		echo '<div class="modulos '.$modulof["permissao"].' modulos-filho">
					<div class="row">
						<div class="modulo-title col-sm-6">
							<span>' . $modulof["rep"] . '</span>';
							printaAtalhoRelatorio($modf);
				echo '</div>
					<div class="modulo-permissao col-sm-4 d-flex">
						<div class="mr-3">
							'.$checkboxW.'
						</div>
						<div>
							'.$checkboxR.'
						</div>
					</div>
				</div>
			</div>';
	}

	function printModulofilho($idlp, $modf, $modulof, $mod, $arrmodrep=null, $arrmodfilho2=null)
	{
		$checkboxR = printaCheckbox($modulof, $idlp, 'r', 'leitura');
		$checkboxW = printaCheckbox($modulof, $idlp, 'w', 'escrita');
		$atalho = printaAtalhoModulo($modf);
		$caret = count($arrmodfilho2) > 0 || count($arrmodrep) > 0? '<div data-toggle="collapse" 
		data-target="#modulo-filho-' . $modf . '" class="modulo-colapse col-sm-2 d-flex justify-end">
		<span class="caret"></span></div>' : '';
		echo '<div class="modulos-inline permissao-'.$modulof["permissao"].'">
				<div class="modulos">
					<div class="row">
						<div class="modulo-title col-sm-6">
							<span>' . $modulof["rotulomenu"] . '</span>
							'.$atalho.'
						</div>
						<div class="modulo-permissao col-sm-4 d-flex">
							<div class="mr-3">
								'.$checkboxW.'
							</div>
							<div>
								'.$checkboxR.'
							</div>
						</div>
						'.$caret.' 
					</div>
				</div>';
			
			echo '<div class="collapse" id="modulo-filho-'. $modf .'" aria-expanded="false">';
			if (count($arrmodrep) > 0 || count($arrmodfilho2) > 0)
					{                
						if (count($arrmodfilho2) > 0 ){ //funcionalidades
							echo '<div class="px-3">';
								echo '<h5>Funcionalidades</h5>';
								foreach ($arrmodfilho2 as $modf2 => $valf2)
								{
									printModulofilho($idlp, $modf2, $valf2, $modf);
								}
							echo '</div>';
						}

						if (count($arrmodrep) > 0 ){ //relatorios
							echo '<div class="px-3">';
								echo '<h5>Relatórios</h5>';
								foreach ($arrmodrep as $modrep => $valrep)
								{
									printModuloRelatorio($idlp,$modrep, $valrep, $modf);
								}
							echo '</div>';
						}
					}
			echo '</div>';
		echo '</div>';
	}
	function printaCheckbox($modulo,$idlp, $permissao, $label){

		$checked = $modulo["permissao"] == $permissao ? 'checked' : '';
		$idlpmodulo = $checked?' data-idlpmodulo='.$modulo['idlpmodulo']:'';
		
		return '
			<div class="checkbox">
				<label>
					<input class="lpmodulo mr-2 '.$label.'" type="checkbox" ' . $checked . ' 
					data-idmodulo="'.$modulo['idmodulo'].'" data-modulo="'.$modulo['modulo'].'"
					'.$idlpmodulo.' 
					data-idlp="'.$idlp.'"
					id="_per'.$permissao.$modulo['idmodulo'].'" value="'.$permissao.'"/>
					<span>'.$label.'</span>
				</label>
			</div>
		';
	}
	function printaCheckboxRelatorio($modulo,$idlp, $permissao, $label){

		$checked = $modulo["permissao"] == $permissao ? 'checked' : '';
		$idlprep = $checked?' data-idlprep='.$modulo['idlprep']:'';
		
		return '
			<div class="checkbox">
				<label>
					<input class="lpmodulo mr-2 '.$label.'" type="checkbox" ' . $checked . ' 
					data-idmodulo="'.$modulo['idmodulo'].'" data-idrep="'.$modulo['idrep'].'"
					'.$idlprep.' 
					data-idlp="'.$idlp.'"
					id="_per'.$permissao.$modulo['idmodulo'].'" value="'.$permissao.'"/>
					<span>'.$label.'</span>
				</label>
			</div>
		';
	}
	function printaModulos($snipets, $_1_u__lp_idlp, $_1_u__lp_idempresa)
	{
		foreach ($snipets as $mod => $modulo) {
			$arrmodfilho = retArrModFilho($_1_u__lp_idlp, $mod, $_1_u__lp_idempresa);
			$checkboxR = printaCheckbox($modulo,$_1_u__lp_idlp,'r', 'leitura');
			$checkboxW = printaCheckbox($modulo,$_1_u__lp_idlp,'w', 'escrita');
			$atalho = printaAtalhoModulo($mod);
			$caret = count($arrmodfilho) > 0 ? '<div data-toggle="collapse" data-target="#modulo-filho-' . $mod . '" class="modulo-colapse col-sm-2 d-flex justify-end"><span class="caret"></span></div>' : '';
			
			echo '<div class="modulos-inline permissao-'.$modulof["permissao"].'">
						<div class="modulos '.$modulo["permissao"].'">
							<div class="row">
								<div class="modulo-title col-sm-6">
									<span>'.$modulo["rotulomenu"].'</span>
									'.$atalho .'								
								</div>
								<div class="modulo-permissao col-sm-4 d-flex">
									<div class="mr-3">
										'.$checkboxW.'
									</div>
									<div>
										'.$checkboxR.'
									</div>
								</div>'
							.$caret.
						'</div>
					</div>
			<div class="collapse p-3" id="modulo-filho-'. $mod .'" aria-expanded="false">';
				foreach ($arrmodfilho as $modf => $modulof){
					$arrmodrep = retArrModRep($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
					$arrmodfilho2 = retArrModFilho2($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
					printModulofilho($_1_u__lp_idlp,$modf, $modulof, $mod, $arrmodrep, $arrmodfilho2);
				}
			echo '</div>
			</div>';
		}
	}
	?>
	<div>
		<ul id="lptabs" class="nav nav-pills panel mb-0 border-0 items-end d-flex bg-transparent flex-wrap">
			<li role="presentation" class="">
				<a href="#participantes" class="" aria-controls="participantes" tab="participantes" role="presentation" data-toggle="tab">
					Participantes
				</a>
			</li>
			<li role="presentation">
				<a href="#snipets" class="" aria-controls="snipets" tab="snipets" role="presentation" data-toggle="tab">
					Permissão Snipets
				</a>
			</li>
			<li role="presentation">
				<a href="#modulos" class="" aria-controls="modulos" tab="modulos" role="presentation" data-toggle="tab">
					Permissão Módulos
				</a>
			</li>

			<li role="presentation">
				<a href="#dashboards" class="" aria-controls="dashboards" tab="dashboards" role="presentation" data-toggle="tab">
					Dashboards
				</a>
			</li>
		</ul>
		<div class="panel mt-0 col-sm-12">
			<div class="panel-body">
				<div id="lptabs-content" class="tab-content">
					<!-- participantes -->
					<div class="tab-pane fade w-100" id="participantes" role="tabpanel">
						<div id="participantes_lp" class="w-100">
							<div class="row p-0">
								<div class="col-md-12 col-lg-4">
									<div class="input-custom input-group input-group-lg w-100">
										<i class="fa fa-search"></i>
										<input class="funcsetdeptvinc" type="text" type="text" class="form-control" placeholder="Adicionar participantes" aria-describedby="sizing-addon1">
									</div>
								</div>
							</div>
							<div class="divider"></div>
							<div class="row">
								<div class="col-md-12">
									<?
									$re = _LpController::buscarPessoasPorTipoPessoa($remp['idtipopessoa']);
									if (count($re) > 0) { ?>
										<div class="col-md-12">
											<?= traduzid('tipopessoa', 'idtipopessoa', 'tipopessoa', $remp['idtipopessoa'], false) ?>
										</div>
										<? foreach ($re as $k => $rws) { ?>
											<div class="col-md-12">
												<div class="col-md-11">
													<?= empty($rws['nomecurto']) ? $rws['nome'] : $rws['nomecurto'] ?>
												</div>
												<div class="col-md-1"></div>
											</div>
										<? }
									}
									$rs = _LpController::buscarObjetosVinculadosALp($_POST["idlp"]);
									foreach ($rs as $k => $rw) {
										if ($rw['tipoobjeto'] == 'pessoa') { ?>
											<div class="col-md-12">
												<div class="col-md-11">
													<?= traduzid('empresa', 'idempresa', 'sigla', traduzid('pessoa', 'idpessoa', 'idempresa', $rw['idobjeto'])) . ' - ' . traduzid('pessoa', 'idpessoa', 'IFNULL(nomecurto,nome)', $rw['idobjeto']) ?>
												</div>
												<div class="col-md-1">
													<i class="fa fa-trash hoververmelho pointer" style="float:right" onclick="desvincularpessoaSetDeptArea(<?= $rw['idlpobjeto'] ?>)"></i>
												</div>
											</div>

											<?
										} else {
											$pessoas = buscarPessoasSetorDepartamentoArea($rw['idobjeto'], $rw['tipoobjeto']);
											if ($pessoas and count($pessoas) > 0) { ?>
												<div class="col-md-12">
													<fieldset class="scheduler-border">
														<legend class="scheduler-border">
															<?= $pessoas['nome'] ?>
															<i class="fa fa-trash hoververmelho pointer" style="float:right" onclick="desvincularpessoaSetDeptArea(<?= $rw['idlpobjeto'] ?>)"></i>
														</legend>
														<?
														foreach ($pessoas['pessoas'] as $k => $v) { ?>
															<div class="col-md-12"><a href=""><?= $v['pessoa'] ?></a> </div>
														<? } ?>
													</fieldset>
												</div>
											<?	
											}
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<!-- snipets -->
					<div class="tab-pane fade" id="snipets" role="tabpanel">
						<div class="row mb-3">
							<div class="col-sm-2">
								<select class='selectpicker snipets' id='permissao-snipets' title="Permissão" onchange="searchAndFilter('','snipets', '.modulos-inline')">
									<option value="" selected hidden></option>
									<option value="escrita" col="status" rot="Status">ESCRITA</option>
									<option value="leitura" col="status" rot="Status">LEITURA</option>
								</select>
							</div>
							<div class="col-sm-4">
								<div class="input-custom input-group input-group-lg w-100">
									<i class="fa fa-search"></i>
									<input class="searchLP" id="searchLpSnipet" onkeyup="searchAndFilter(this.value,'snipets', '.modulos-inline')" type="text" class="form-control" placeholder="Pesquisar" aria-describedby="sizing-addon1">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="w-100" id="listaSnipets">
								<? printaModulos($modulos[1], $rsemp['idlp'], $rsemp['idempresa']); //snipets ?>
							</div>
						</div>
					</div>
					<!-- modulos -->
					<div class="tab-pane fade" id="modulos" role="tabpanel">
						<div class="row mb-3">
							<div class="col-sm-2">
								<select class='selectpicker snipets' id='permissao-modulos' title="Permissão" onchange="searchAndFilter('','modulos', '.modulos-inline')">
									<option value="" selected hidden></option>
									<option value="escrita" col="status" rot="Status">ESCRITA</option>
									<option value="leitura" col="status" rot="Status">LEITURA</option>
								</select>
							</div>
							<div class="col-sm-4">
								<div class="input-custom input-group input-group-lg w-100">
									<i class="fa fa-search"></i>
									<input class="searchLP" id="searchLpSnipet" onkeyup="searchAndFilter(this.value,'modulos', '.modulos-inline')" type="text" class="form-control" placeholder="Pesquisar" aria-describedby="sizing-addon1">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="w-100">
								<? printaModulos($modulos[2], $rsemp['idlp'], $rsemp['idempresa']); //modulos ?>
							</div>
						</div>
					</div>
					<!-- dashboards -->
					<div class="tab-pane fade w-100" id="dashboards" role="tabpanel">
						<div class="panel-heading col-md-12 pointer" onclick="abreModalDash(<?=$_1_u__lp_idlp?>,<?=$_1_u__lp_idlpgrupo?>,<?=$_1_u__lp_idempresa?>)">Dashboards</div>
					</div>
				</div>

				<div id="circularProgressIndicator" style="display: none;"></div>
			</div>
		</div>
	</div>
	<script>
		$('.selectpicker').selectpicker('render').on("change",(event)=>{debugger
			var icon = $(event.target).parent().parent().find('.btn-warning');
			if(icon.length <= 0){
				$(event.target).parent().parent().find('button.btn-warning').remove();
				$(event.target).parent().after(`&nbsp;<button class="btn btn-warning pointer" onclick="alteraobjetovinculo(${idlp},'lp','${$(event.target).val()}','${$(event.target).attr('cbpost')}',this)"> <i class="fa fa-warning "></i> Salvar alterações </button>`)
			}else{
				$(event.target).parent().parent().find('button.btn-warning').remove();
				$(event.target).parent().after(`&nbsp;<button class="btn btn-warning pointer" onclick="alteraobjetovinculo(${idlp},'lp','${$(event.target).val()}','${$(event.target).attr('cbpost')}',this)"> <i class="fa fa-warning "></i> Salvar alterações </button>`)
			}
		});
		function searchAndFilter(value, container, selector) {
			console.log(value, container, selector);
			// Todas as tags que deseja buscar (por exemplo, p, div, span, etc.)
			const tags = document.querySelectorAll(' #' +container + ' .modulos-inline');
			const permissao = document.querySelectorAll('#permissao-'+container);
			
			//debugger;
			let permissaoValue = $(permissao).val()?'.'+$(permissao).val():'';

			tags.forEach(element => {
				// Encontra o checkbox dentro do elemento
				let checkbox = true;
				let inputCheckbox = element.querySelector('input[type="checkbox"]'+permissaoValue);

				if(permissaoValue){
					checkbox = inputCheckbox && inputCheckbox.checked?true:false;
				}

				if (element.textContent.toLowerCase().includes(value.toLowerCase()) && checkbox) {
					// Mantém visível o elemento se o checkbox estiver marcado ou se não houver checkbox
					element.style.display = '';
				} else {
					// Oculta o elemento se o checkbox não estiver marcado
					element.style.display = 'none';
				}
			});
		}
		
		var checkbox = document.querySelectorAll(".lpmodulo");
		checkbox.forEach((el)=>{
			el.addEventListener('change', function() {

				if(this.checked && !this.dataset.idlpmodulo) {
					//debugger;

					input = document.createElement("input");
					input.type = "hidden";
					input.dataset.idmodulo = this.dataset.idmodulo;
					input.value = this.value;
					input.className = "lpmodulo-change";
					input.name = "_per"+this.dataset.idmodulo+'_i__lpmodulo_permissao'; 
					this.parentNode.appendChild(input);					
					
					input = document.createElement("input");
					input.type = "hidden";
					input.dataset.idmodulo = this.dataset.idmodulo;
					input.value = this.dataset.modulo;
					input.className = "lpmodulo-change";
					input.name = "_per"+this.dataset.idmodulo+'_i__lpmodulo_modulo'; 
					this.parentNode.appendChild(input);
					
					input = document.createElement("input");
					input.type = "hidden";
					input.dataset.idmodulo = this.dataset.idmodulo;
					input.value = this.dataset.idlp;
					input.className = "lpmodulo-change";
					input.name = "_per"+this.dataset.idmodulo+'_i__lpmodulo_idlp'; 
					this.parentNode.appendChild(input); 
				}
				else{
					//debugger;
					if(this.dataset.idlpmodulo && !$(".lpmodulo-change").length){
						var input = document.createElement("input");
						input.type = "hidden";
						input.dataset.idlpmodulo = this.dataset.idlpmodulo;
						input.value = this.dataset.idlpmodulo;
						input.className = "lpmodulo-change";
						input.name = "_per"+this.dataset.idmodulo+'_d__lpmodulo_idlpmodulo'; 
						this.parentNode.appendChild(input);
					}
					else{
						this.parentNode.querySelectorAll('.lpmodulo-change').forEach(
							(el)=>{
								el.remove()
							}
						);
					}
				}
			});
		});

		var jFuncionario = <?=_LpController::jsonFuncionariosSetoresDepartamentosAreaConselho($_POST['idlp'])?>;
		if(jFuncionario){
        $(".funcsetdeptvinc").autocomplete({
            source: jFuncionario
            ,delay: 0
            ,create: function(){
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                    lbItem = item.rot;			
                    return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
                };
            },select: function(e,ui){
                let id = ui.item.idobjeto;
                let tipoobj = ui.item.tipoobj;

                CB.post({
                    objetos:{
                        [`_${tipoobj}_i_lpobjeto_idlp`] : idlp,
                        [`_${tipoobj}_i_lpobjeto_idobjeto`] : id,
                        [`_${tipoobj}_i_lpobjeto_tipoobjeto`] : tipoobj,
                    },
                    parcial: true
                });
            }
        });
    }
	</script>
	<style>
		#lptabs-content .bootstrap-select {
			height: 100%;
			width: 100% !important;
		}

		#lptabs-content .bootstrap-select button.bs-placeholder .filter-option,
		#lptabs-content .bootstrap-select button.bs-placeholder .bs-caret {
			color: #000000b5 !important;
		}

		#lptabs-content .bootstrap-select button.bs-placeholder {
			height: 100%;
			width: 100% !important;
			background: transparent;
			border: none;
			font-size: 18px;
			outline: none !important;
		}

		#lptabs-content .bootstrap-select button {
			height: 100%;
			width: 100% !important;
			background: orange;
			color: white !important;
			border: none;
			font-size: 18px;
			outline: none !important;
		}

		#lptabs-content .bootstrap-select button:focus {
			outline: none !important;
		}

		.modulos-inline {
			border-radius: 6px;
			border: 1px solid #e6e6e6;
			margin-bottom: 10px;
		}

		.modulos {
			list-style: none !important;
			border-left: 8px solid gray;
			border-radius: 6px;
			text-transform: uppercase;
			background-color: #e6e6e6;
		}

		.modulos.modulos-filho {
			margin: 10px;
		}

		.modulos .modulo-title {
			font-size: 1rem;
			display: flex;
			align-items: center;
			align-content: center;
			flex-direction: row;
			gap: 6px;
		}

		.modulos i {
			font-size: 1.5rem;
		}

		.modulos.w {
			border-left: 8px solid red;
		}

		.modulos.r {
			border-left: 8px solid blue;
		}

		.modulos .modulo-permissao {
			font-size: 1.5rem;
		}
		.modulos .modulo-permissao .checkbox label{
			display: flex;
		}

		.modulos .modulo-colapse {
			align-items: center;
		}

		.black-text {
			color: black;
		}

		.nav.nav-pills>li.active>a {
			color: #555555;
		}

		.nav.nav-pills li {
			margin: 2px 2px 0px 0px;
			font-weight: bold;
			background-color: #AAAAAA;
			color: #ffffff !important;
			font-size: 1rem !important;
			border-bottom: none;
			border-radius: 5px 5px 0px 0px;
		}

		.nav-pills>li:not(.active)>a {
			position: relative;
			color: white;
		}

		.nav.nav-pills li a {
			padding: 10px 20px;
		}

		.nav.nav-pills li.active,
		.nav.nav-pills li a:hover {
			background-color: #cccccc;
			color: #555555;
		}

		.divider {
			margin: 16px 10px;
			border-top: 2px solid #989898;
		}

		#lptabs-content.tab-content>.active {
			display: flex;
			flex-direction: column;
		}

		.input-custom {
			display: flex;
			align-items: center;
		}

		.input-custom i {
			font-size: 1.5rem;
			position: absolute;
			left: 10px;
			padding: 0px 10px;
		}

		.input-custom input {
			text-indent: 40px;
			display: inline-block;
			width: 100%;
			height: 45px !important;
			padding: 0px 5px;
			font-size: 1.5rem !important;
			line-height: 1.428571429;
			color: #555555;
			vertical-align: middle;
			background-color: #ffffff;
			border: 1px solid #cccccc;
			border-radius: 4px;
			-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
			box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
			-webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
			transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
		}
	</style>
<? } ?>
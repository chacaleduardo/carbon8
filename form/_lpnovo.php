<?php
require_once "../inc/php/validaacesso.php";

require_once __DIR__ . "/controllers/_lp_controller.php";

if ($_POST){
	require_once "../inc/php/cbpost.php";
}
//Parámetros mandatários para o carbon
$pagvaltabela = "_lp";
$pagvalcampos = array(
	"idlp" => "pk"
);

//Select que inicializa as variáveis que preenchem os campos da tela em caso de update
$pagsql = "select * from carbonnovo._lp where idlp = '#pkid'";

//controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e criacao das variáveis 'variáveis' para a página
require_once "../inc/php/controlevariaveisgetpost.php";

$remp = _LpController::buscarLpEEmpresa($_1_u__lp_idlp, null)[0];
$_idempresa = d::b()->real_escape_string($_GET['_idempresa']);
$_modulo = d::b()->real_escape_string($_GET['_modulo']);
$_1_u__lp_idempresa = $remp['idempresa'];
$_sigla = $remp['sigla'];
$_corsistema = $remp['corsistema'];

function buscarPessoasSetorDepartamentoArea($id, $tp)
{
	return _LpController::buscarPessoasSetorDepartamentoArea($tp, $id);
}

function retArrModSelecionados($inIdLp)
{
	global $_sigla;
	global $_1_u__lp_idempresa;
	return _LpController::buscarModulosSelecionados2($_1_u__lp_idempresa, $inIdLp,getidempresa('u.idempresa', 'unidade'), $_sigla);
}

function buscarLpPorIdbi($inIdLp, $idempresa)
{
	return _LpController::buscarLpPorIdbi($inIdLp, $idempresa);
}

?>

<div class="row lps">
	<div class="col-md-12">
		<div class="panel panel-default" style="margin-bottom: 0;">
			<div class="panel-heading">
				<div class="d-flex w-100 justify-space-between px-3 mt-2">
					<? if($_acao=='u'){ ?>
						<div class="flex-direction-row d-flex gap-6">
							<div class="form-group flex-direction-collums d-flex">
								<label class="text-white">ID</label>
								<span class="alert-warning id-label" ondblclick="copiaLink()"><b><?= $_1_u__lp_idlp ?></b></span>
								<input type="hidden" name="_1_<?= $_acao ?>__lp_idlp" value="<?= $_1_u__lp_idlp ?>">
							</div>
							<div class="form-group flex-direction-collums d-flex">
								<label class="text-white">Empresa</label>
								<input class="size20" type="text" value="<?= $remp['empresa'] ?>" vnulo disabled>
							</div>
							<div class="form-group flex-direction-collums d-flex">
								<label class="text-white">LP</label>
								<input class="size20" type="text" name="_1_<?= $_acao ?>__lp_descricao" value="<?= $_1_u__lp_descricao ?>" vnulo>
							</div>
						</div>
						<div class="form-group flex-direction-collums d-flex">
							<label class="text-white">Status</label>
							<select name="_1_<?= $_acao ?>__lp_status" class="size8">
								<!--<?= $_1_u__lp_status; ?> -->
								<? fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u__lp_status); ?>
							</select>
						</div>
					<? } ?>
					<? if($_acao=='i'){ ?>
					<div class="form-group flex-direction-collums d-flex">
						<label class="text-white">LP</label>
						<input class="size20" type="text" name="_1_i__lp_descricao" value="" vnulo placeholder="Informe o nome da LP">
						<input type="hidden" name="_1_i__lp_grupo" value="N">
					</div>
					<? } ?>
				</div>
			</div>
			<? if($_acao=='u'){ ?>
			<div class="panel-body">
				<div class="row">
					<div class="col col-xs-12 col-sm-6 col-md-6 col-lg-4">
						<div>
							<div class="form-group">
								<div>
									Gera grupos
								</div>
								<div>
									<select class="selectpicker w-100" onchange="alteraStatusGrupo(this,<?= $remp['idlp'] ?>)">
										<? fillselect(_LpController::$ArrayYN, $remp['grupo']) ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div>
									Tipo pessoa
								</div>
								<div>
									<select class="selectpicker w-100" name="_1_u__lp_idtipopessoa">
										<option></option>
										<? fillselect(TipoPessoaQuery::buscarTodosTipoPessoa(), $remp['idtipopessoa']) ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div>
									Empresas
								</div>
								<div>
									<select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" idlp='<?= $remp['idlp'] ?>' cbpost='empresa' class="selectpicker btn-salvar w-100" multiple="multiple">
									<?
									$resjidempresa = _LpController::buscarLpobjetoPorEmpresa($remp['idlp'], $remp['idempresa']);
									$selected = '';
										foreach ($resjidempresa as $k1 => $rowempresa){
											$selected = (($rowempresa['idlpobjeto'])) ? 'selected' : '';
											echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowempresa['empresa']) . '" value="' . $rowempresa['idempresa'] . '" >' . $rowempresa['empresa'] . '</option>';
										} 
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div>
									Categoria
								</div>
								<div>
									<select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='contaitem' idlp='<?= $remp['idlp'] ?>' class="selectpicker btn-salvar w-100" multiple="multiple" data-actions-box="true" data-live-search="true">
										<? $resCi = _LpController::buscarContaitem($remp['idlp'], $_1_u__lp_idempresa, $remp['habilitarmatriz']);
										foreach ($resCi as $k => $rowCi){
											$selected = (($rowCi['idobjetovinculo'])) ? 'selected' : '';
											echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowCi['contaitem']) . '" value="' . $rowCi['idcontaitem'] . '" >' . $rowCi['sigla'] . ' - ' . $rowCi['contaitem'] . '</option>';
										} ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div>Un. negócio(s) obrigatório </div>
								<div>
									<select class="selectpicker w-100" onchange="alttipocontato('flagobrigatoriofiltro',this.value,<?= $remp['idlp'] ?>);">
										<? fillselect("select 'Y','Sim' union select 'N','Não'", $remp['flagobrigatoriofiltro']); ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6 col-md-6 col-lg-4">						
						<div class="form-group">
							<div>Un. negócio(s)</div>
							<div>
								<select <?= $remp['flagobrigatoriofiltro'] == "Y"?'':'disabled' ?>  id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" idlp='<?= $remp['idlp'] ?>' cbpost='plantelobjeto' class="selectpicker btn-salvar  w-100" multiple="multiple">
									<? $resu = _LpController::buscarPlanteis($remp['idlp'], getidempresa('u.idempresa', 'plantel'));
									foreach ($resu as $k => $rowu){
										$selected = (($rowu['idplantelobjeto'])) ? 'selected' : '';
										echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowu['plantel']) . '" value="' . $rowu['idplantel'] . '" >' . $rowu['plantel'] . '</option>';
									} ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div>
								Contato obrigatório
							</div>
							<div>
								<select  class="selectpicker w-100" <?= $remp['flagobrigatoriofiltro'] == "Y"?'':'disabled' ?> onchange="alttipocontato('flagobrigatoriocontato',this.value,<?= $remp['idlp'] ?>);"  title="Nada selecionado">
									<? if($remp['flagobrigatoriofiltro'] == "Y") fillselect("select 'Y','Sim' union select 'N','Não'", $remp['flagobrigatoriocontato']); ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div>
								Tipo contato
							</div>
							<div>
								<select <?= $remp['flagobrigatoriofiltro'] == "Y"?'':'disabled' ?> id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='tipopessoa' idlp='<?= $remp['idlp'] ?>' class="selectpicker w-100" multiple="multiple">
									<? $resAg = _LpController::buscarTipopessoaVinculadoALp($remp['idlp']);
									foreach ($resAg as $k => $rowAg){
										$selected = (($rowAg['idobjetovinculo'])) ? 'selected' : '';
										echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowAg['tipopessoa']) . '" value="' . $rowAg['idtipopessoa'] . '" >' . $rowAg['tipopessoa'] . '</option>';
									} ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div>
								Agência
							</div>
							<div>
								<select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='agencia' idlp='<?= $remp['idlp'] ?>' class="selectpicker btn-salvar  w-100" multiple="multiple">
									<? $resAg = _LpController::buscarAgencias($remp['idlp'], $_1_u__lp_idempresa, $remp['habilitarmatriz']);
									foreach ($resAg as $k => $rowAg){
										$selected = (($rowAg['idobjetovinculo'])) ? 'selected' : '';
										echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowAg['agencia']) . '" value="' . $rowAg['idagencia'] . '" >' . $rowAg['sigla'] . ' - ' . $rowAg['agencia'] . '</option>';
									} ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<div>
								Forma de pagamento
							</div>
							<div>
								<select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='formapagamento' idlp='<?= $remp['idlp'] ?>' class="selectpicker btn-salvar  w-100" multiple="multiple" data-actions-box="true" data-live-search="true">
									<? $resCi = _LpController::buscarFormaPagamento($remp['idlp'], $_1_u__lp_idempresa, $remp['habilitarmatriz']);
									foreach ($resCi as $k => $rowCi){
										$selected = !is_null($rowCi['idobjetovinculo']) && $rowCi['idobjetovinculo'] ?
											'selected' :
											'';
										echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowCi['descricao']) . '" value="' . $rowCi['idformapagamento'] . '" >' . $rowCi['sigla'] . ' - ' . $rowCi['descricao'] . '</option>';
									} ?>
								</select>
							</div>
						</div>
						<div>
							<div class="hidden" id="lps_adicionar_<?= $remp['idlp'] ?>">
								<div class="plantel hidden">

								</div>
								<div class="tipopessoa hidden">

								</div>
								<div class="agencia hidden">

								</div>
								<div class="contaitem hidden">

								</div>
								<div class="empresa hidden">

								</div>
							</div>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-12 col-md-12 col-lg-4 d-flex  flex-direction-collums">
						<div class="d-flex w-100 justify-content-end">
							<div class="form-group flex-direction-collums d-flex">
								<label class="text-white">&nbsp;</label>
								<button style="float:right;background-color:#337AB7;color:white" onclick="showModalSincronizarLp(<?= $remp['idlp'] ?>)" class="btn btn-sm"><i class="fa fa-upload"></i>Importar Configurações</button>
							</div>
						</div>
						<div class="w-100 form-group form-group-obs">
							<div for="obs">Observação</div>
							<textarea id="obs" class="h-100 p-2" style="margin-bottom: 14px;" name="_1_u__lp_obs"><?= nl2br($remp['obs']) ?></textarea>
						</div>
					</div>
				</div>
			</div>
			<? } ?>
		</div>
	</div>
	<? if($_acao=='u'){ ?>
	<div class="col-sm-12">
		<div idempresa="<?= $remp["idempresa"] ?>" id="lp_<?= $remp['idlp'] ?>" role="tabpanel" aria-labelledby="pills-home-tab">
			<div>
				<ul id="lptabs" class="nav nav-pills panel mb-0 items-end d-flex bg-transparent flex-wrap">
					<li role="presentation">
						<a href="#participantes" aria-controls="participantes" tab="participantes" data-toggle="tab">
							Participantes
						</a>
					</li>
					<li role="presentation">
						<a href="#snipets" aria-controls="snipets" tab="snipets" data-toggle="tab">
							Permissão Snipets
						</a>
					</li>
					<li role="presentation">
						<a href="#modulos" aria-controls="modulos" tab="modulos" data-toggle="tab">
							Permissão Módulos
						</a>
					</li>

					<li role="presentation">
						<a href="#bi" aria-controls="bi" tab="bi" data-toggle="tab">
							Permissão BI
						</a>
					</li>

					<li role="presentation">
						<a href="#dashboards" aria-controls="dashboards" tab="dashboards" data-toggle="tab">
							Dashboards
						</a>
					</li>
				</ul>
				<div id="painel-lptabs" class="panel mt-0 col-sm-12">
					<div class="panel-body">
						<div id="lptabs-content" class="tab-content">
							<!-- participantes -->
							<div class="tab-pane fade w-100" id="participantes" role="tabpanel">
								<div id="participantes_lp" class="w-100">
									<div class="row p-0">
										<div class="col-xs-12 col-lg-4">
											<div class="input-custom input-group input-group-lg w-100">
												<i class="fa fa-search"></i>
												<input class="funcsetdeptvinc" type="text" type="text" class="form-control" placeholder="Adicionar participantes" aria-describedby="sizing-addon1">
											</div>
										</div>
										
									</div>
									<div class="divider"></div>
									<div class="row">
										<?
										$re = _LpController::buscarPessoasPorTipoPessoa($remp['idtipopessoa']);
										if (!empty($re)){ ?>
											<div class="col-xs-12 col-md-4">
												<fieldset class="scheduler-border">
													<legend class="scheduler-border legend-departamentos-pessoas">
														TIPO PESSOA - <?= traduzid('tipopessoa', 'idtipopessoa', 'tipopessoa', $remp['idtipopessoa'], false) ?>
													</legend>
										
											<? foreach ($re as $k => $rws){ ?>
												<div class="row pessoa-departamento">
													<div class="col-md-11">
														<a href="/?_modulo=confacessocolaborador&_acao=u&idpessoa=<?= $rws['idpessoa'] ?>" target="_blank"><?= empty($rws['nomecurto']) ? $rws['nome'] : $rws['nomecurto'] ?></a>
													</div>
													<div class="col-md-1"></div>
												</div>
											<? } ?>
										</div>
										<? }
										$objetosVinculadosLps = _LpController::buscarObjetosVinculadosALp($_1_u__lp_idlp);
										if (!empty($objetosVinculadosLps)){
											$pessoasHtml = '';
											$departamentosHtml='';
											foreach ($objetosVinculadosLps as $objetoVinculado){
												if ($objetoVinculado['tipoobjeto'] == 'pessoa'){
													$nome = traduzid('empresa', 'idempresa', 'sigla', traduzid('pessoa', 'idpessoa', 'idempresa', $objetoVinculado['idobjeto'])) . ' - ' . traduzid('pessoa', 'idpessoa', 'IFNULL(nomecurto,nome)', $objetoVinculado['idobjeto']);
													$statuspessoa=traduzid('pessoa', 'idpessoa', 'status', $objetoVinculado['idobjeto']);
													if($statuspessoa!='INATIVO'){
														$pessoasHtml .=
														'<div class="row pessoa-departamento">
															<div class="col-md-11">
																<a href="/?_modulo=confacessocolaborador&_acao=u&idpessoa='.$objetoVinculado['idobjeto'].'" target="_blank">'.strtoupper($nome).'</a>
															</div>
															<div class="col-md-1">
																<a href="#" onclick="desvincularpessoaSetDeptArea('.$objetoVinculado["idlpobjeto"].')"><i class="fa fa-trash hoververmelho pointer" style="float:right"></i></a>
															</div>
														</div> ';
													}

												} else {
													$departamento = buscarPessoasSetorDepartamentoArea($objetoVinculado['idobjeto'], $objetoVinculado['tipoobjeto']);
													if (!empty($departamento)){
														$pessoasNesseDepartamento = '';
														foreach ($departamento['pessoas'] as $pessoaNoDepartamento){
															$pessoasNesseDepartamento .= 
															'<div class="col-md-12 pessoa-departamento">
																<a href="/?_modulo=confacessocolaborador&_acao=u&idpessoa='.$pessoaNoDepartamento['idpessoa'].'" target="_blank">
																	'.strtoupper($pessoaNoDepartamento['pessoa']).'
																</a>
															</div>';
														}
														$departamentosHtml .= '
														<div class="col-xs-12 col-md-4">
															<fieldset class="scheduler-border">
																<legend class="scheduler-border legend-departamentos-pessoas">
																	<a href="/?_modulo='.$objetoVinculado['tipoobjeto'].'&_acao=u&id'.$objetoVinculado['tipoobjeto'].'='.$objetoVinculado['idobjeto'].'" target="_blank">'.strtoupper($departamento["nome"]).'</a>
																	<a href="#" onclick="desvincularpessoaSetDeptArea('.$objetoVinculado['idlpobjeto'].')"><i class="fa fa-trash hoververmelho pointer" style="float:right"></i></a>
																</legend>
																'.$pessoasNesseDepartamento.'
															</fieldset>
														</div>'
														;
													}
												}
											}
										}

										if($pessoasHtml!=""){
										?>
										
										<div class="col-xs-12 col-md-4">
											<fieldset class="scheduler-border">
												<legend class="scheduler-border legend-departamentos-pessoas">
													PESSOAS
												</legend>
												<? echo $pessoasHtml;?>
											</fieldset>
										</div>
										
										<?
										}
										echo $departamentosHtml; ?>
									</div>
								</div>
							</div>
							<!-- snipets -->
							<div class="tab-pane fade" id="snipets" role="tabpanel">
								<div class="d-flex justify-space-between">
									<div class="d-flex gap-6">
										<select class='selectpicker snipets' id='permissao-listaSnipets' title="Permissão" onchange="buscarModulos('','listaSnipets', '.modulos-inline')">
											<option value="" selected hidden></option>
											<option value="SemAcesso">SEM ACESSO</option>
											<option value="Visualização">VISUALIZAÇÃO</option>
											<option value="Total">TOTAL</option>
										</select>
									
										<div class="input-custom input-group w-100">
											<i class="fa fa-search"></i>
											<input class="searchLP" id="searchLpSnipet" onkeyup="buscarModulos(this.value,'listaSnipets', '.modulos-inline')" type="text" class="form-control" placeholder="Pesquisar" aria-describedby="sizing-addon1">
										</div>
									</div> 
									<div class="col">
										<button class="btn btn-default dropdown-toggle expandir" onclick="expandirModulos(this, 'snipets')" state="Y">
											<span class="text">Expandir todos</span><i class="glyphicon glyphicon-chevron-down"></i>
										</button>
									</div>
								</div>
								<div class="divider"></div>
								<div class="row">
									<div class="w-100" id="listaSnipets"><!-- modulos snipets --></div>
								</div>
							</div>
							<!-- modulos -->
							<div class="tab-pane fade" id="modulos" role="tabpanel">
								<div class="d-flex justify-space-between">
									<div class="d-flex gap-6">
										<select class='selectpicker snipets' id='permissao-listaModulos' title="Permissão" onchange="buscarModulos('','listaModulos', '.modulos-inline')">
											<option value="" selected hidden></option>
											<option value="SemAcesso">SEM ACESSO</option>
											<option value="Visualização">VISUALIZAÇÃO</option>
											<option value="Total">TOTAL</option>
										</select>
									
										<div class="input-custom input-group w-100">
											<i class="fa fa-search"></i>
											<input class="searchLP" id="searchLpSnipet" onkeyup="buscarModulos(this.value,'listaModulos', '.modulos-inline')" type="text" class="form-control" placeholder="Pesquisar" aria-describedby="sizing-addon1">
										</div>
									</div>
									<div class="col">
										<button class="btn btn-lg btn-outline expandir" onclick="expandirModulos(this, 'modulos')" state="Y">
											<span class="text">Expandir todos</span><i class="glyphicon glyphicon-chevron-down"></i>
										</button>
									</div>
								</div>
								<div class="divider"></div>
								<div class="row">
									<div class="w-100" id="listaModulos"><!-- modulos modulos --></div>									
								</div>
							</div>

							<!-- bi -->
							<div class="tab-pane fade" id="bi" role="tabpanel">
								<div class="d-flex justify-space-between">
									<div class="d-flex gap-6">
										<select class='selectpicker snipets' id='permissao-listaBI' title="Permissão" onchange="buscarModulos('','listaModulos', '.modulos-inline')">
											<option value="" selected hidden></option>
											<option value="SemAcesso">SEM ACESSO</option>
											<option value="Visualização">VISUALIZAÇÃO</option>
										</select>
									
										<div class="input-custom input-group w-100">
											<i class="fa fa-search"></i>
											<input class="searchLP" id="searchLpSnipet" onkeyup="buscarModulos(this.value,'listaBI', '.modulos-inline')" type="text" class="form-control" placeholder="Pesquisar" aria-describedby="sizing-addon1">
										</div>
									</div>
									<div class="col">
										<button class="btn btn-lg btn-outline expandir" onclick="expandirModulos(this, 'bi')" state="Y">
											<span class="text">Expandir todos</span><i class="glyphicon glyphicon-chevron-down"></i>
										</button>
									</div>
								</div>
								<div class="divider"></div>
								<div class="row">
									<div class="w-100" id="listaBI"><!-- modulos modulos --></div>									
								</div>
							</div>
							<!-- dashboards -->
							<div class="tab-pane fade w-100" id="dashboards" role="tabpanel">
								<a hfef="#" class="panel-heading col-md-12 pointer" onclick="abreModalDash(<?= $remp['idlp'] ?>,null,<?= $_1_u__lp_idempresa ?>)">
									<h5 class="text-center">Dashboards <i class="glyphicon glyphicon-new-window"></i></h5>
								</a>
							</div>
						</div>

						<div id="circularProgressIndicator" style="display: none;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<? } ?>
</div>

<link rel="stylesheet" href="<?= "form/css/_lp_css.css?v=".microtime() ?>" />

<? if($_acao=='u'){ 
	// Função auxiliar para gerar a string de IDs
	function gerarStringIdEmpresa($array, $idempresa) {
		// Filtra os valores de idempresa, removendo valores nulos e duplicados
		$ids = [];
		$ids = array_unique(array_filter(array_map(function($item) {
			if($item["idlpobjeto"]!==NULL) return $item["idempresa"];
		}, $array)));
		array_push($ids,$_GET['_idempresa']);

		// Transforma o array em uma string separada por vírgulas
		return implode(", ", $ids);
	}
	?>
	<script>
		// importação de dados para o javascript de idlp, idempresa, modulos (evitando temporariamente uso de requisição extra)
		var idlp = <?= $_1_u__lp_idlp ?>;
		var idlpempresa = <?= $_1_u__lp_idempresa ?>;
		var listaModulos = <?= json_encode(retArrModSelecionados($_1_u__lp_idlp)) ?>;
		var listaBI = <?= json_encode(buscarLpPorIdbi($_1_u__lp_idlp, gerarStringIdEmpresa($resjidempresa, cb::getidempresa()))) ?>;
		var jFuncionario = <?= _LpController::jsonFuncionariosSetoresDepartamentosAreaConselho($_1_u__lp_idlp) ?>;
		
		// Tipos de modulos
		const tipoModulo = {
			'DROP' : 'Menu Superior',
			'LINK' : 'Módulo',
			'LINKUSUARIO' : 'Menu Usuário',
			'LINKHOME' : 'Homepage',
			'BTINV' : 'Funcionalidade',
			'BTPR' : 'Padrão',
			'MODVINC' : 'Módulo Vinculado',
			'EMAIL' : 'Módulo Email',
			'POPUP' : 'PopUp',
			'SNIPPET' : 'Snippet'
		};

		// Copiar link da lp ao clicar no idlp no header da tela
		function copiaLink(){
			const input = document.createElement('input');
			document.body.appendChild(input);
			input.value = `${window.location.origin}/?_modulo=lpnovo&_acao=u&idlp=${idlp}`;
			input.select();
			const isSuccessful = document.execCommand('copy');
			input.style.display = 'none';
			if (!isSuccessful){
				console.error('Failed to copy text.');
			} else {
				alertAzul("Link Copiado","",1000);
			}
			document.body.removeChild(input);
		}

		// Função de expandir/recolher os itens collapse da lista de modulos e scripts
		function expandirModulos(button, content){
			let state = $(button).attr('state');
			//
			let stateTo = (state == 'Y') ? 'N' : 'Y';
			if(state == 'Y'){
				$(button).html(`<span class="text">Recolher todos</span><i class="glyphicon glyphicon-chevron-up"></i>`);
				$("#lptabs-content #"+content+" .collapse").each( function(index,el){
					$(el).removeClass("collapse").addClass("collapse in").css('height','100%');
				});
			} else {
				$(button).html(`<span class="text">Expandir todos</span><i class="glyphicon glyphicon-chevron-down"></i>`);
				$("#lptabs-content #"+content+" .collapse").each( function(index,el){
					$(el).removeClass("collapse in").addClass("collapse").css('height','0');
				});
			}
			$(button).attr('state', stateTo);
		}

		// Atalho para as configurações dos modulos na lista de modulos
		function atalhoModulo(mod){
			let link = `?_modulo=_modulo&_acao=u&idmodulo=${mod}`;
			return `<a target="_blank" onclick="janelamodal('${link}')">
						<i class="fa fa-navicon azul pointer"></i>
					</a>`;
		}

		// Atalho para as configurações dos relatorios na lista de modulos
		function atalhoRelatorio(mod){
			let link = `?_modulo=_rep&_acao=u&idrep=${mod}`;
			return `<a target="_blank" onclick="janelamodal('${link}')">
						<i class="fa fa-navicon azul pointer"></i>
					</a>`;
		}

		// Botoẽs de flag de restrição em relatorios (financeiro, unidade, organograma)
		function permissaoRepFLG(el, flag, idlp){
			console.log(el, flag, idlp);
			if($(el).attr('idlprep')!='null'){
				$("[name^=_lprep"+$(el).attr('idlprep')+"_u]").remove();
				let flagValue = $(el)[0].classList.contains('active')?"N":"Y";
				if(flagValue=="Y"){$(el)[0].classList.add('active')}else{$(el)[0].classList.remove('active');}
				let inputs = `
					<input type="hidden" name="_lprep${$(el).attr('idlprep')}_u__lprep_idlprep" value="${$(el).attr('idlprep')}" />
					<input type="hidden" name="_lprep${$(el).attr('idlprep')}_u__lprep_${flag}" value="${flagValue}" />
				`;
				$(el).parent().append(inputs);
			}else{
				alert('Necessário dar permissão ao relatório');
			}
		}

		// Botão de assinar (configuração do modulo)
		function botaoAssinar(modulo){
			if((modulo.botaoassinar == 'Y') && modulo.idlpmodulo){
				active = (modulo.solassinatura == 'Y') ? 'active' : '';
				novoSolAssinar = (modulo.solassinatura == 'Y') ? 'N' : 'Y';
				return `<button type="button" class="btn btn-xs ${active}" onclick="solicitarAssinatura('${modulo.idlpmodulo}', '${novoSolAssinar}')">
							<i class="fa fa-edit"></i>
						</button>`;
			}
			return '';
		}

		// Radio buttons para modulos principais e filhos
		function radioButton(modulo, idlp, permissao, label){
			
			if (modulo.permissao == null){modulo.permissao = 'n';}
			
			let idlpmodulo = ` data-idlpmodulo=${modulo.idlpmodulo}`;

			let checked = modulo.permissao == permissao ? 'checked' : '';

			return `
			<div class="radio">
				<label>
					<input
						class="lpmodulo mr-2 ${label.replace(/\s+/g, '')}" name="optionsRadios${modulo.idmodulo}" type="radio" ${checked} data-permissao="${label}"
						data-idmodulo="${modulo.idmodulo}" data-modulo="${modulo.modulo}"
						${idlpmodulo} data-idlp="${idlp}" id="_per${permissao}${modulo.idmodulo}" value="${permissao}"/>
					${label}
				</label>
			</div>`;
		}
		
		// Radio buttons para modulos principais e filhos BI
		function radioButtonBI(bi, idlp, permissao, label){
			
			if (bi.permissao == null){bi.permissao = 'n';}
			
			let idlpbi = ` data-idlpbi=${bi.idlpbi}`;
			//debugger;
			let checked = bi.permissao == permissao ? 'checked' : '';

			return `
			<div class="radio">
				<label>
					<input
						class="lpmodulo mr-2 ${label.replace(/\s+/g, '')}" name="optionsRadios${bi.idbi}" type="radio" ${checked} data-permissao="${label}"
						data-idbi="${bi.idbi}"
						${idlpbi} data-idlp="${idlp}" id="_per${permissao}${bi.idbi}" value="${permissao}"/>
					${label}
				</label>
			</div>`;
		}

		// Radio buttons para relatorios
		function radioButtonRelatorio(mod, modulof, idlp, permissao, label){
			
			let permissaoRep = modulof.idlprep?'w':'n';
			let checked = (permissaoRep == permissao) ? 'checked' : '';

			return `
			<div class="radio">
				<label>
					<input name="optionsRadiosRep_${mod}_${modulof.modulo}" class="lpmodulo mr-2 " type="radio" ${checked}
						data-idmodulo="${modulof.modulo}" data-idrep="${modulof.modulo}"
						data-idlprep=${modulof.idlprep} data-idlp="${idlp}" id="_per${permissao}${modulof.modulo}" value="${permissao}"/>
					${label}
				</label>
			</div>`;
		}

		// Componente/conteiner de collapse
		function collapseContainer(modulo){

			//debugger;
			if($(`#modulo-filho-${modulo}`).length == 0){
			
				$('#modulo-'+ modulo).append(`<div class="collapse p-3 collapse-filho" id="modulo-filho-${modulo}">
					<div class="px-4 modulos-inline hidden funcionalidades"><h5 class="filhos-label">Funcionalidades</h5></div>
					<div class="px-4 modulos-inline hidden relatorios"><h5 class=" filhos-label">Relatórios</h5></div>
				</div>`);
			}

		}

		function collapseContainerBI(modulo){

			//debugger;
			if($(`#bi-filho-${modulo}`).length == 0){
				$('#bi-'+ modulo).append(`<div class="collapse p-3 collapse-filho" id="bi-filho-${modulo}"></div>`);
			}

		}

		// Imprime relatórios na lista da modulos
		function moduloRelatorio(idlp, modf, modulof, mod){
			modulof.permissao = (modulof.idlprep)?'w':'n';
			const checkActive = (flag)=>{return flag=='Y'?'active':''}
			//botoes de restrição
			let btnrep = modulof.btnrep?`
				<button class="ml-2 btn btn-xs ${checkActive(modulof.flgcontaitem)} btn-contaitem" flgcontaitem="${modulof.flgcontaitem}"
				title="Restringe filtrar as formas de pagamento selecionadas na LP"
				idlprep='${modulof.idlprep}' idrep="${modulof.modulo}" onclick="permissaoRepFLG(this,'flgcontaitem',${idlp})">
					<i class="contaitem fa fa-credit-card-alt pointer pull-left " flgcontaitem="${modulof.flgcontaitem}" idlprep='${modulof.idlprep}'></i>
				</button>`:'';
			let btnreporg = modulof.btnreporg?`
				<button class="ml-2 btn btn-xs ${checkActive(modulof.flgidpessoa)} btn-idpessoa" flgidpessoa="${modulof.flgidpessoa}"
				title="Restringe filtrar os dados de pessoas abaixo do nível do organograma do usuário"
				idlprep='${modulof.idlprep}' idrep="${modulof.modulo}" onclick="permissaoRepFLG(this,'flgidpessoa',${idlp})">
					<i class="organograma fa fa-sitemap pointer pull-left " flgidpessoa="${modulof.flgidpessoa}" idlprep='${modulof.idlprep}'></i>
				</button>`:'';
			let btnrepcti = modulof.btnrepcti?`
				<button class="ml-2 btn btn-xs ${checkActive(modulof.flgunidade)} btn-un" flgunidade="${modulof.flgunidade}"
				title="Restringe filtrar os dados das unidades associadas ao setor, departamento que o usuário está vinculado"
				idlprep='${modulof.idlprep}' idrep="${modulof.modulo}" onclick="permissaoRepFLG(this,'flgunidade',${idlp})">
					<i class="unidade fa fa-building pointer pull-left " flgunidade="${modulof.flgunidade}" idlprep='${modulof.idlprep}'></i>
				</button>`:'';

			collapseContainer(mod);
			$($('#modulo-filho-' + mod + ' .relatorios')[0]).removeClass('hidden').append(`
				<div class="modulos-inline">
					<div class="modulos permissao-${modulof.permissao} d-flex justify-space-between ">
						<div class="modulo-title col">
							<div class="col-1"></div>
							<span>${modulof.rotulomenu}<span class="label ml-2">${modulof.reptipo}</span></span>
							${atalhoRelatorio(modulof.modulo)}
						</div>
						<div class="permissao-div">
							<div class="modulo-permissao col d-flex mr-5">
								<div class="mr-3">
									${radioButtonRelatorio(mod,modulof, idlp, 'n', 'Sem acesso')}
								</div>
								<div class="mr-3">
									${radioButtonRelatorio(mod,modulof, idlp, 'w', 'Total')}
								</div>
							</div>
						</div>
				
						<div class="botoes-rep col d-flex justify-content-end p-2">
							${btnrep}
							${btnreporg}
							${btnrepcti}
						</div>
					</div>
				</div>
			`);
		}

		// Imprime modulos filhos e funcionalidades na lista da modulos
		function modulofilho(idlp, modf, modulof, mod, arrmodrep = [], arrmodfilho2 = []){
			
			let collapse = `data-toggle="collapse" href="#modulo-filho-${modulof.modulo}" data-target="#modulo-filho-${modulof.modulo}"`;
			
			let output = `<div class="modulos-inline" id="modulo-${modulof.modulo}">
							<div class="modulos permissao-${modulof.permissao}">
								<div class="modulo-filho d-flex justify-space-between" ${collapse}>
									<div class="modulo-title">
										<div class="col-1"></div>
										<span>${modulof.rotulomenu}</span>
										<span class="label ml-2">${tipoModulo[modulof.tipo]}</span>
										${atalhoModulo(modulof.modulo)}
									</div>
									<div class="modulo-permissao col d-flex mr-5">
										<div class="mr-3">
											${radioButton(modulof, idlp, 'n', 'Sem acesso')}
										</div>
										<div class="mr-3">
											${radioButton(modulof, idlp, 'r', 'Visualização')}
										</div>
										<div>
											${radioButton(modulof, idlp, 'w', 'Total')}
										</div>
									</div>
									<div>${botaoAssinar(modulof)}</div>
								</div>
							</div>
						<div>
						`;

			collapseContainer(mod);
			
			if(modulof.tipo == 'BTINV'){
				//debugger
				$($('#modulo-filho-' + mod + ' .funcionalidades')[0]).removeClass('hidden').append(output);
			}else{
				$('#modulo-filho-'+mod).append(output);
			}
		}

		// Imprime modulos filhos BI e funcionalidades na lista da modulos
		function modulofilhoBI(idlp, modf, modulof, mod){
			
			modulof.permissao = modulof.idlpbi==null?'n':'r';
			
			let collapse = `data-toggle="collapse" href="#bi-filho-${modulof.idbi}" data-target="#bi-filho-${modulof.idbi}"`;
			
			let output = `<div class="modulos-inline" id="bi-${modulof.idbi}">
							<div class="modulos permissao-${modulof.permissao}">
								<div class="modulo-filho d-flex justify-space-between" ${collapse}>
									<div class="modulo-title">
										<div class="col-1"></div>
										<span>${modulof.sigla} - ${modulof.nome}</span>
									</div>
									<div class="modulo-permissao col d-flex mr-5">
										<div class="mr-3">
											${radioButtonBI(modulof, idlp, 'n', 'Sem acesso')}
										</div>
										<div class="mr-3">
											${radioButtonBI(modulof, idlp, 'r', 'Visualização')}
										</div>
									</div>
								</div>
							</div>
						<div>
					`;
			collapseContainerBI(mod);
			
			$('#bi-filho-'+mod).append(output);
		}

		// Imprime modulos principais na lista da modulos
		function modulos(modulo, _1_u__lp_idlp, _1_u__lp_idempresa, container){
			
			if(modulo.permissao==null) modulo.permissao='n';

			let collapse = `data-toggle="collapse" data-target="#modulo-filho-${modulo.modulo}" href="#modulo-filho-${modulo.modulo}"`;
					
			$(container).append(`
				<div class="modulos-inline permissao-${modulo.permissao}" id="modulo-${modulo.modulo}">
					<div class="modulos permissao-${modulo.permissao}" >
						<div class="modulo-pai d-flex justify-space-between" ${collapse}>
							<div class="modulo-title col">
								<div class="col-1"></div>
								<span>${modulo.rotulomenu}</span>
								<span class="label ml-2">${tipoModulo[modulo.tipo]}</span>
								${atalhoModulo(modulo.modulo)}
							</div>
							<div class="modulo-permissao col d-flex mr-5">
								<div class="mr-3">
									${radioButton(modulo, _1_u__lp_idlp, 'n', 'Sem Acesso')}
								</div>
								<div class="mr-3">
									${radioButton(modulo, _1_u__lp_idlp, 'r', 'Visualização')}
								</div>
								<div>
									${radioButton(modulo, _1_u__lp_idlp, 'w', 'Total')}
								</div>
							</div>
							<div class="botoes-acao col d-flex justify-content-end p-2">
								${botaoAssinar(modulo)}
							</div>
						</div>
					</div>
				</div>
			`);
		}

		// Imprime modulos principais na lista da modulos
		function moduloBI(modulo, idlp, idempresa, container){
			
			modulo.permissao = modulo.idlpbi==null?'n':'r';

			let collapse = `data-toggle="collapse" data-target="#bi-filho-${modulo.idbi}" href="#bi-filho-${modulo.idbi}"`;
					
			$(container).append(`
				<div class="modulos-inline permissao-${modulo.permissao}" id="bi-${modulo.idbi}">
					<div class="modulos permissao-${modulo.permissao}" >
						<div class="modulo-pai d-flex justify-space-between" ${collapse}>
							<div class="modulo-title col">
								<div class="col-1"></div>
								<span>${modulo.sigla} - ${modulo.nome}</span>
							</div>
							<div class="modulo-permissao col d-flex mr-5">
								<div class="mr-3">
									${radioButtonBI(modulo, idlp, 'n', 'Sem Acesso')}
								</div>
								<div class="mr-3">
									${radioButtonBI(modulo, idlp, 'r', 'Visualização')}
								</div>
							</div>
						</div>
					</div>
				</div>
			`);
		}

		// Busca na lista de modulo para texto e valor de permissão
		// (oculta os itens que não correspondem com a busca)
		function buscarModulos(value, container, selector){
			//debugger
			// Todas as tags que deseja buscar (por exemplo, p, div, span, etc.)
			const rootElements = document.querySelectorAll('#' + container + '>.modulos-inline');
			const permissao = document.querySelectorAll('#permissao-' + container);
			
			let permissaoValue = $(permissao).val() ? '.' + $(permissao).val() : '';
			
			if(value=='' && permissaoValue==''){ $('.modulos-inline').show(); return}
			
			// Função para normalizar strings removendo acentos e cedilha
			function normalizeString(str){
				return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
			}

			let normalizedValue = normalizeString(value);
			
			// Função recursiva para fazer a busca na árvore de elementos
			function recursiveSearch(element){

				let foundMatch = false;

				// Encontra o input radio dentro do elemento
				let radio = true;
				let inputRadio = element.querySelector('input[type="radio"]' + permissaoValue.trim());

				if (permissaoValue){
					radio = inputRadio && inputRadio.checked ? true : false;
				}

				// Normaliza o texto do elemento para comparar com o valor normalizado
				let normalizedText = normalizeString(element.textContent);

				// Verifica se o elemento atual corresponde ao valor de busca e o radio está marcado (se aplicável)
				if (normalizedText.includes(normalizedValue) && radio){
					foundMatch = true;
				}

				// Procura nos filhos do elemento
				const childElements = element.querySelectorAll('.modulos-inline');
				childElements.forEach(child => {
					if (recursiveSearch(child)){
						foundMatch = true;
					}
				});

				// Se encontrou correspondência no elemento ou em algum filho, mantém o elemento visível
				if (foundMatch){
					$(element).show();
					//element.style.display = '';
					let collapse = $(element).find('.collapse')[0];
					if (collapse){
						$(collapse).removeClass('collapse').addClass('collapse in');
						$(collapse).css('height', '100%');
					}
				} else {
					// Caso contrário, oculta o elemento
					$(element).hide();
					let collapse = $(element).find('.collapse')[0];
					if (collapse){
						$(collapse).removeClass('collapse in').addClass('collapse');
						$(collapse).css('height', '0');
					}
				}

				return foundMatch;
			}

			// Inicia a busca recursiva para cada elemento raiz
			rootElements.forEach(element => {
				recursiveSearch(element);
			});
		}

		// Função auxiliar para criar inputs ocultos para salvamento via carbon
		function createHiddenInput(parent, datasetKey, datasetValue, classNamePrefix, nameSuffix){
			var input = document.createElement("input");
			input.type = "hidden";
			input.dataset[datasetKey] = datasetValue;
			input.value = datasetValue;
			input.className = classNamePrefix + datasetValue;
			input.name = nameSuffix;
			parent.appendChild(input);
		}
		
		// Imprime os modulos na lista da modulos
		$(document).ready(function(){
			let arrMod = {
				"snippets":{},
				"modulos":{}
			};
			
			listaModulos.map(function(modulo){
				if(modulo.modulopar=='' && modulo.nivel==0){
					if(modulo.divisao=='snippets'){
						modulos(modulo, idlp, idlpempresa, document.getElementById('listaSnipets'));
					}
					
					if(modulo.divisao=='modulos'){
						modulos(modulo, idlp, idlpempresa, document.getElementById('listaModulos'));
					}
				}
				if(modulo.modulopar!='' && modulo.nivel>0){
					//
					$('[data-target=#modulo-filho-'+modulo.modulopar+'] .modulo-title .col-1').addClass('collapse-button d-flex justify-content-center glyphicon glyphicon-chevron-down');
					if(modulo.tipo == 'REPTIPO'){
						moduloRelatorio(idlp, modulo.modulo, modulo, modulo.modulopar);
					}else{
						modulofilho(idlp, modulo.modulo, modulo, modulo.modulopar);
					}
				}
			});

			for (const [key, bi] of Object.entries(listaBI)) {
				if(!bi.bipai){
					moduloBI(bi, idlp, idlpempresa, document.getElementById('listaBI'))
					if(bi.filhos.length){
						for (const [key, bif] of Object.entries(bi.filhos)) {
							$('[data-target=#bi-filho-'+bif.bipai+'] .modulo-title .col-1').addClass('collapse-button d-flex justify-content-center glyphicon glyphicon-chevron-down');
							//debugger
							modulofilhoBI(idlp, bif.idbi, bif, bif.bipai);
						}
					}
				}
			}

			$(".collapse-button").on('click', function(e){
				e.stopPropagation();
				$(e.target.closest("[data-toggle=collapse]")).trigger('click')
			});

			$('.selectpicker').selectpicker('render');
			$('.selectpicker.btn-salvar').on("change", (event) => {
				var icon = $(event.target).parent().parent().find('.btn-warning');
				if (icon.length <= 0){
					$(event.target).parent().parent().find('button.btn-warning').remove();
					$(event.target).parent().after(`&nbsp;<button class="btn btn-warning pointer" onclick="alteraobjetovinculo(${idlp},'lp','${$(event.target).val()}','${$(event.target).attr('cbpost')}',this)"> <i class="fa fa-warning "></i> Salvar alterações </button>`)
				} else {
					$(event.target).parent().parent().find('button.btn-warning').remove();
					$(event.target).parent().after(`&nbsp;<button class="btn btn-warning pointer" onclick="alteraobjetovinculo(${idlp},'lp','${$(event.target).val()}','${$(event.target).attr('cbpost')}',this)"> <i class="fa fa-warning "></i> Salvar alterações </button>`)
				}
			});
		
			
			 document.querySelectorAll('.modulos-inline:not(.glyphicon)').forEach(function(modulo,index){
				modulo.addEventListener('click', function(ev){
					ev.stopPropagation();
					const glyphicon = ev.target.querySelector('.glyphicon');
					$(glyphicon).trigger('click');
					ev.stopPropagation();
				});
			});
			/*
			document.querySelectorAll('.collapse-button.glyphicon').forEach(function(modulo,index){
				modulo.addEventListener('click', function(ev){
					ev.stopPropagation();
					const glyphicon = ev.target.querySelector('.modulos-inline');
					
					$(glyphicon).trigger('click');
					ev.stopPropagation();
				});
			}); */

			document.querySelectorAll("#lptabs-content .collapse").forEach(function(el){
				// Cria um MutationObserver para monitorar mudanças na classe
				const observer = new MutationObserver(function(mutationsList){
					mutationsList.forEach(function(mutation){
						if (mutation.attributeName === "class"){
							
							const glyphicon = el.parentElement.querySelector('.glyphicon');

							if (el.classList.contains('in')){
								glyphicon.classList.remove('glyphicon-chevron-down');
								glyphicon.classList.add('glyphicon-chevron-up');
							} else {
								glyphicon.classList.remove('glyphicon-chevron-up');
								glyphicon.classList.add('glyphicon-chevron-down');
							}
						}
					});
				});

				// Configura o observer para observar mudanças nos atributos de classe
				observer.observe(el, { attributes: true });
			});
		
			var checkbox = document.querySelectorAll(".lpmodulo");

			checkbox.forEach((el) => {
				el.addEventListener('change', function(){
					// Remove elementos existentes
					if (this.dataset.modulo !== undefined){
						$('.lpmodulo-change-' + this.dataset.idmodulo).remove();
					}
					if (this.dataset.modulo == undefined && this.dataset.idrep){
						$('.lpmodulo-change-' + this.dataset.idrep).remove();
					}

					// Verificação de `modulo`
					if (this.dataset.modulo !== undefined){
						if (this.checked && this.dataset.idlpmodulo === 'null' && this.value != 'n'){
							createHiddenInput(this.parentNode, 'idmodulo', this.value, "lpmodulo-change-", "_per" + this.dataset.idmodulo + '_i__lpmodulo_permissao');
							createHiddenInput(this.parentNode, 'idmodulo', this.dataset.modulo, "lpmodulo-change-", "_per" + this.dataset.idmodulo + '_i__lpmodulo_modulo');
							createHiddenInput(this.parentNode, 'idmodulo', this.dataset.idlp, "lpmodulo-change-", "_per" + this.dataset.idmodulo + '_i__lpmodulo_idlp');
						}

						if (this.checked && this.dataset.idlpmodulo !== 'null' && this.value != 'n'){
							createHiddenInput(this.parentNode, 'idmodulo', this.value, "lpmodulo-change-", "_per" + this.dataset.idmodulo + '_u__lpmodulo_permissao');
							createHiddenInput(this.parentNode, 'idmodulo', this.dataset.idlpmodulo, "lpmodulo-change-", "_per" + this.dataset.idmodulo + '_u__lpmodulo_idlpmodulo');
						}

						if (this.checked && this.dataset.idlpmodulo !== 'null' && this.value == 'n'){
							createHiddenInput(this.parentNode, 'idlpmodulo', this.dataset.idlpmodulo, "lpmodulo-change-", "_per" + this.dataset.idmodulo + '_d__lpmodulo_idlpmodulo');
						}
					}

					// Verificação de `idrep`
					if(this.dataset.idlprep !== undefined){
						if (this.checked && this.dataset.idlprep !== 'null' && this.value == 'n'){
							createHiddenInput(this.parentNode, 'idrep', this.dataset.idlprep, "lpmodulo-change-", "_per" + this.dataset.idrep + '_d__lprep_idlprep');
						} else {
							createHiddenInput(this.parentNode, 'idrep', this.dataset.idlp, "lpmodulo-change-", "_per" + this.dataset.idrep + '_i__lprep_idlp');
							createHiddenInput(this.parentNode, 'idrep', this.dataset.idrep, "lpmodulo-change-", "_per" + this.dataset.idrep + '_i__lprep_idrep');
						}
					}

					if(this.dataset.idlpbi !== undefined){
						if (this.checked && this.dataset.idlpbi !== 'null' && this.value == 'n'){
							createHiddenInput(this.parentNode, 'idlpbi', this.dataset.idlpbi, "lpmodulo-change-", "_per" + this.dataset.idlpbi + '_d__lpbi_idlpbi');
						} else {
							createHiddenInput(this.parentNode, 'idlpbi', this.dataset.idlp, "lpmodulo-change-", "_per" + this.dataset.idbi + '_i__lpbi_idlp');
							createHiddenInput(this.parentNode, 'idlpbi', this.dataset.idbi, "lpmodulo-change-", "_per" + this.dataset.idbi + '_i__lpbi_idbi');
						}
					}
				});
			});
			
			if (jFuncionario){
				$(".funcsetdeptvinc").autocomplete({
					source: jFuncionario,
					delay: 0,
					create: function(){
						$(this).data('ui-autocomplete')._renderItem = function(ul, item){
							lbItem = item.rot;
							return $('<li>')
								.append('<a>' + lbItem + '</a>')
								.appendTo(ul);
						};
					},
					select: function(e, ui){
						let id = ui.item.idobjeto;
						let tipoobj = ui.item.tipoobj;

						CB.post({
							objetos: {
								[`_${tipoobj}_i_lpobjeto_idlp`]: idlp,
								[`_${tipoobj}_i_lpobjeto_idobjeto`]: id,
								[`_${tipoobj}_i_lpobjeto_tipoobjeto`]: tipoobj,
							},
							parcial: true
						});
					}
				});
			}
		});

		function showModalSincronizarLp(idLp){

			$oModal = $(`
				<div id="nova_lp">
					<div class="col-md-12">
						<label for="idlporigem">LP de Origem:</label>
						<select id="idlporigem">
							<option value="" disabled selected>Selecione uma opção</option>
							<?fillselect(" SELECT
												l.idlp, concat( e.sigla, ' - ',l.descricao ) as asas
											FROM
													"._DBCARBON."._lp l
											JOIN
													empresa e on e.idempresa = l.idempresa and e.status = 'ATIVO'
											where
													l.status = 'ATIVO'
											order by
													e.sigla, l.descricao;
											
											")?>
						</select>
					</div>
					<div class="col-md-12">
						<input type="checkbox" id="sincronizaconfig" name="sincronizaconfig" checked>
						<label for = "subscribeNews">Sincroniza Configurações</ label>
					</div>
					<div class="col-md-12">
						<input type="checkbox" id="sincronizaparticipantes" name="sincronizaparticipantes">
						<label for = "subscribeNews">Sincroniza Participantes</ label>
					</div>
					<div class="col-md-12">
						<input type="checkbox" id="sincronizalp" name="sincronizalp" checked>
						<label for = "subscribeNews">Sincroniza Módulos</ label>
					</div>
					<div class="col-md-12">
						<input type="checkbox" id="sincronizadash" name="sincronizadash" checked>
						<label for = "subscribeNews">Sincroniza Dashboard</ label>
					</div>
					
					<div class="col-md-12">
						<div class="alert alert-warning" role="alert">
							<div class="row">
								<div class="col-md-12">
									<p>Ao importar, as configurações de acesso aos módulos poderão ser modificadas e o Dashboard também será substituído.</p>
									<p>Em outras palavras, as permissões e a aparência do sistema serão atualizadas de acordo com as configurações da importação.</p>
									<p>Essa alteração não poderá ser restaurada.</p>
								</div>	
							
							</div>
						</div>

					</div>
					<div class="col-md-12" style="text-align: right;margin: 10px 0px 10px 0px;">
						<button onclick="SincronizarLp(${idLp})" class="btn btn-success btn-sm">
							<i class="fa fa-upload"></i>Importar
						</button>
					</div>
				</div>
			`);


			CB.modal({
				titulo: "</strong>Importar Configurações</strong>",
				corpo: [$oModal],
				classe: 'trinta',
			});
		}

	</script>
<? }

	// Imprimir no footer dados de criação e alteração
	$tabaud = "_lp"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<script src="/inc/js/lp.js?v=2.1"></script>
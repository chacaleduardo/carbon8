<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "sgdepartamento";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idsgdepartamento" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from " . _DBAPP . ".sgdepartamento where idsgdepartamento = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__."/./controllers/sgdepartamento_controller.php");
require_once(__DIR__."/./controllers/sgarea_controller.php");
require_once(__DIR__ ."/controllers/imgrupo_controller.php");
require_once(__DIR__ ."/controllers/unidade_controller.php");
require_once(__DIR__ ."/controllers/sgdocumento_controller.php");

$area = false;

if($_1_u_sgdepartamento_idsgdepartamento)
{
	$grupos = ImGrupoController::buscarGruposPorIdObjetoVincETipoObjetoVinc($_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento');
	$lps = SgDepartamentoController::carregarLps($_1_u_sgdepartamento_idsgdepartamento);
	$setores = SgDepartamentoController::carregarSetores($_1_u_sgdepartamento_idsgdepartamento);
	$funcionarios = SgDepartamentoController::carregarFuncionarios($_1_u_sgdepartamento_idsgdepartamento);
	$grupoES = SgDepartamentoController::carregarGrupoES($_1_u_sgdepartamento_idsgdepartamento);
	//$unidades = SgDepartamentoController::carregarUnidades($_1_u_sgdepartamento_idsgdepartamento, cb::idempresa());
 
	$unidadesDpt = SgDepartamentoController:: buscarUnidadeDoIdSgDepartamentoEIdEmpresa($_1_u_sgdepartamento_idsgdepartamento, cb::idempresa());
	$unidadesVincDpt = SgDepartamentoController::buscarUnidadeVinculadaIdSgDepartamentoEIdEmpresa($_1_u_sgdepartamento_idsgdepartamento, cb::idempresa());

	$historico = SgDepartamentoController::carregarHistoricoColaborador($_1_u_sgdepartamento_idsgdepartamento);
	$setoresDisponiveisParaVinculo = SgDepartamentoController::carregarSetoresDisponiveisParaVinculo($_1_u_sgdepartamento_idsgdepartamento);
	$pessoasDisponiveisParaVinculo = SgDepartamentoController::carregarPessoasDisponiveisParaVinculo(cb::idempresa());
	$contaItensDisponiveisParaVinculo = SgDepartamentoController::buscarContaItensDisponiveisParaVinculo($_1_u_sgdepartamento_idsgdepartamento, cb::idempresa());
	$unidadesDisponiveisParaVinculo = SgDepartamentoController::buscarUnidadesDisponiveisParaVinculo($_1_u_sgdepartamento_idsgdepartamento, cb::idempresa());
	$fillUnidadesDisponiveisParaVinculo = SgDepartamentoController::listarUnidadesDisponiveisParaVinculo($_1_u_sgdepartamento_idsgdepartamento, cb::idempresa());

	$lpsDisponiveisParaVinculo = SgDepartamentoController::buscarLpsDisponiveisParaVinculo($_1_u_sgdepartamento_idsgdepartamento,cb::idempresa());
	$areasDisponiveisParaVinculo = SgDepartamentoController::carregarAreasDisponiveisParaVinculo(cb::idempresa());
	$area = SgDepartamentoController::buscarSgArea($_1_u_sgdepartamento_idsgdepartamento);
	$possuiVinculoComUnidade = UnidadeController::verificarSeExisteVinculoComUnidadePorIdObjetoETipoObjeto($_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento');
	$gruposDisponiveisParaVinculo = ImGrupoController::buscarGruposDisponiveisParaVinculoPorIdObjetoVincTipoObjetoVincEGetIdEmpresa($_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento');
	$documentosVinculados = SgdocumentoController::buscarDocumentosVinculadosPorIdSgSetor($_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento');

	$unidadesDisponiveisParaVinculoEnvio=SgDepartamentoController::buscarUnidadesDisponiveisParaVinculoEnvio(cb::idempresa());

	if($_1_u_sgdepartamento_grupo == 'Y')
	{
		$idImGrupo = ImGrupoController::buscarGrupoPorIdObjetoExtETipoObjetoExt($_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento')['idimgrupo'];
	}

	
}

function listaSetores()
{
	GLOBAL $setores;

	foreach($setores as $setor)
	{
		echo "	<tr>
					<td>
						<a title='Editar Setor' target='_blank' href='?_modulo=sgsetor&_acao=u&idsgsetor={$setor["idsgsetor"]}'>{$setor["setor"]}</a>
					</td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincular({$setor['idobjetovinculo']})' title='Excluir!'></i>
					</td>
				</tr>";
	}
}

function listaFucionario()
{
	GLOBAL $funcionarios;

	foreach($funcionarios as $funcionario)
	{
		$modulo = "pessoa";

		if($funcionario["idtipopessoa"] == 1)
		{
			$modulo = "funcionario";
		}

		echo "	<tr>
					<td>
						<a title='Editar Funcionario' target='_blank' href='?_modulo={$modulo}&_acao=u&idpessoa={$funcionario["idpessoa"]}'>{$funcionario["nome"]}</a>
					</td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularPessoa({$funcionario['idpessoaobjeto']})' title='Excluir!'></i>
					</td>
                </tr>";
	}
}

function listaGrupoEs()
{
	GLOBAL $grupoES;

	foreach($grupoES as $item)
	{
		echo "	<tr>
					<td><a title='Editar Categoria' target='_blank' href='?_modulo=contaitem&_acao=u&idcontaitem={$item["idcontaitem"]}'>{$item["contaitem"]}</a></td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincular({$item['idobjetovinculo']})' title='Excluir!'></i>
					</td>
				</tr>";
	}
}

//Lista as Lp's inseridas no Departamento (Lidiane - 13-03-2020)
function listarLps()
{
	GLOBAL $lps;

	foreach($lps as $key => $lp)
	{
		if($key === 'error')
		{
			echo "	<tr>
						<td> Ocorreu um erro ao buscar Lp's </td>
					</tr>";
			break;
		}

		echo "	<tr>
					<td>
						<a title='{$lp['descricao']}' target='_blank' href='?_modulo=_lp&_acao=u&idlpgrupo={$lp['idlpgrupopai']}'>{$lp['descricao']}</a>
					</td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularLp({$lp['idlpobjeto']})' title='Excluir!'></i>
					</td>
				</tr>";
	}
}

function listarUnidades($unidadesDpt= null, $idobjeto = null, $tipoobjeto = null)
{
	$title = 'Editar Unidade';
	$trStart = '<tr>';
	$trEnd   = '</tr>';

	if(count($unidadesDpt['centrocusto']))
	{
		foreach($unidadesDpt['centrocusto'] as $unidade)
		{
			if(isset($unidade['error']))
			{
				echo "  <tr>
							<td class='py-3'>{$unidade['error']}</td>
						</tr>";

				break;
			}

			$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>";

			if(($idobjeto == null && $tipoobjeto == null) || (($idobjeto && $tipoobjeto) && ($unidade['idobjeto'] == $idobjeto && $unidade['tipoobjeto'] == $tipoobjeto)) || ($tipoobjeto && $tipoobjeto == 'sgsetor'))
			{
				$trContent .= " <td align='right'>
									<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidade(" . $unidade['idunidadeobjeto'] . ")' title='Excluir!'></i>
								</td>";
				
				$trContent = str_replace("colspan='2'", '', $trContent);
			}

			$tr = " $trStart
						$trContent
					$trEnd";

			echo $tr;
		}
	}	


}

function mostrarUnidades($unidades= null, $idobjeto = null, $tipoobjeto = null,$input='Y')
{
	$title = 'Editar Unidade';
	$trStart = '<tr>';
	$trEnd   = '</tr>';

	if(count($unidades)> 0)
	{
		$i=99;
		foreach($unidades as $unidade)
		{ 
			$i=$i+1;
			if(isset($unidade['error']))
			{
				echo "  <tr>
							<td class='py-3'>{$unidade['error']}</td>
						</tr>";

				break;
			}
			if($input=='Y'){
				$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>
				<td title='Percentual'>
					<input type='hidden' name='_".$i."_idunidaderateio' value='".$unidade['idunidaderateio']."' >
					<input title='Percentual' type='text' name='_".$i."_rateio' class='size5 valorrateio' value='".$unidade['rateio']."' onchange='atualizarateio(this,".$unidade['idunidaderateio'].")' > %
				</td>";
	
			}else{
				$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>
				<td title='Percentual'>
				".number_format(tratanumero($unidade['rateio']), 2, ',', '.')." % </td>";
			}

		
			if($input=='Y'){
				$trContent .= " <td align='right'>
									<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidadeRateio(" . $unidade['idunidaderateio'] . ")' title='Excluir!'></i>
								</td>";
				
				$trContent = str_replace("colspan='2'", '', $trContent);
			}

			$tr = " $trStart
						$trContent
					$trEnd";

			echo $tr;
		}
	}	


}



function listarUnidadesVinc($unidadesVincDpt = null, $idobjeto = null, $tipoobjeto = null){

	$title = 'Editar Unidade';
	$trStart = '<tr>';
	$trEnd   = '</tr>';

	
	if(count($unidadesVincDpt['centrocusto']))
	{

	
		foreach($unidadesVincDpt['centrocusto'] as $unidade)
		{
			if(isset($unidade['error']))
			{
				echo "  <tr>
							<td class='py-3'>{$unidade['error']}</td>
						</tr>";

				break;
			}

			$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>";

			if(($idobjeto == null && $tipoobjeto == null) || (($idobjeto && $tipoobjeto) && ($unidade['idobjeto'] == $idobjeto && $unidade['tipoobjeto'] == $tipoobjeto)) || ($tipoobjeto && $tipoobjeto == 'sgsetor'))
			{
				$trContent .= " <td align='right'>
									<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidade(" . $unidade['idunidadeobjeto'] . ")' title='Excluir!'></i>
								</td>";
				
				$trContent = str_replace("colspan='2'", '', $trContent);
			}

			$tr = " $trStart
						$trContent
					$trEnd";

			echo $tr;
		}
	}
}

function listarGruposVinculados()
{
	global $grupos;

	

	foreach ($grupos as $grupo) {
		echo "	<tr>
					<td>
						<a title='Editar Grupo' target='_blank' href='?_modulo=imgrupo&_acao=u&idimgrupo={$grupo["idimgrupo"]}'>{$grupo["grupo"]}</a>
					</td>
					<td align='right' width='70px'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='inativavinculo({$grupo['idobjetovinculo']})' title='Excluir!'></i>
					</td>
				</tr>";
	}
}
?>

<link href="/form/css/sgdepartamento_css.css?_<?=date("dmYhms")?>" rel="stylesheet" />
<style>
.cabitem {
	background-color: #e6e6e6 !important;
}	
.cabitemAcao {
	background-color: #e6e6e6 !important;
	text-align-last: right;
}
.panel-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.left-content {
    display: flex;
    align-items: center;
}

.left-content a {
    margin-right: 10px; /* Espaçamento entre o ícone e o texto */
}
</style>
<div class="col-xs-12 px-0">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="w-100 d-flex flex-wrap flex-between">
				<!-- ID -->
				<input name="_1_<?= $_acao ?>_sgdepartamento_idsgdepartamento" type="hidden" value="<?= $_1_u_sgdepartamento_idsgdepartamento ?>" readonly='readonly'>
				<? if($_1_u_sgdepartamento_idsgdepartamento) { ?>	
					<div class="col-xs-6 col-sm-1 form-group">
						<label class="text-white">ID</label>
						<div class="form-control alert-warning">
							<label>
								<?= $_1_u_sgdepartamento_idsgdepartamento ?>
							</label>
						</div>
					</div>
				<? } ?>
				<!-- DEPARTAMENTO -->
				<div class="col-xs-6 col-sm-2 form-group">
					<label class="text-white">Departamento</label>
					<input class="form-control" name="_1_<?= $_acao ?>_sgdepartamento_departamento" type="text" value="<?= $_1_u_sgdepartamento_departamento ?>" vnulo>
				</div>
				<!-- AREA -->
				<?if($_1_u_sgdepartamento_idsgdepartamento) { ?>
					<div class="col-xs-6 col-sm-2 form-group">
						<label class="text-white">Área</label>
						<div class="form-group alert-warning form-control d-flex flex-between align-items-center">
							<label>
								<?= $area ? $area['area'] : "[SEM VÍNCULO]" ?>
							</label>
							<? if($area){ ?>
								<a href="?_modulo=sgarea&_acao=u&idsgarea=<?= $area['idsgarea'] ?>" class="fa fa-bars pointer hoverazul" title="Área" target="_blank"></a>
							<? } ?>
						</div>
					</div>

					<div class="col-xs-6 col-sm-2 form-group">
						<!-- Coordenadores do Departamento -->
						<label class="text-white">Coordenador</label>
<?
					$qtdCord=count($funcionarios);
					if($qtdCord>0){
						foreach($funcionarios as $funcionario){
							$modulo = "pessoa";

							if($funcionario["idtipopessoa"] == 1)
							{
								$modulo = "funcionario";
							}
?>

						<div class="form-group alert-warning form-control d-flex flex-between align-items-center">
							<div class="col-xs-10 py-0  px-0">
								<a title='Editar Funcionario' target='_blank' href='?_modulo=<?=$modulo?>&_acao=u&idpessoa=<?=$funcionario["idpessoa"]?>'><?=$funcionario["nome"]?></a>									
							</div>
							<div class="col-xs-1 px-0 text-center">
								<i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' onclick="desvincularPessoa(<?=$funcionario['idpessoaobjeto']?>)" title='Excluir!'></i>
							</div>
							<div class="col-xs-1 px-0 text-center">
								<label><a class="fa fa-search azul pointer hoverazul" title="Histórico" onClick="departamentoPessoa();"></a></label>
							</div>
						</div>
<?

						}
					}else{
	?>
						<div class="form-group alert-warning form-control d-flex flex-between align-items-center">
							<div class="col-xs-11 py-0  px-0">
								<input class="form-control" id="pessoaobjeto" class="compacto" <? if ($_1_u_sgdepartamento_status == 'INATIVO') { ?> disabled <? } else { ?> type="text" autocomplete="new-password" cbvalue placeholder="Selecione" <? } ?>>
							</div>
							<div class="col-xs-1 px-0 text-center">
								<label><a class="fa fa-search azul pointer hoverazul" title="Histórico" onClick="departamentoPessoa();"></a></label>
							</div>						
						</div>
<?
					}
		?>	
					
					</div>
				<?}?>
				<div class="col-xs-6 col-sm-1 form-group">
					<label class="text-white">Grupo</label>
					<div class='nowrap'>
					<select id="status" name="_1_<?= $_acao ?>_sgdepartamento_grupo" class="form-control">
						<? fillselect(SgDepartamentoController::$grupo, $_1_u_sgdepartamento_grupo); ?>
					</select>
					<? if($_1_u_sgdepartamento_grupo=='Y' && $_1_u_sgdepartamento_status == 'ATIVO') { ?>
							<? if($idImGrupo) { ?>
								<a href="?_modulo=imgrupo&_acao=u&idimgrupo=<?= $idImGrupo ?>&_idempresa=1" class="fa fa-bars pointer hoverazul" title="Grupo" target="_blank"></a>
							<? } else {?>
								<i class="fa fa-warning text-warning" title="Grupo não gerado! (Atualizar bim)"></i>
							<? } ?>
						<? } ?>
					</div>
				</div>
				
				<!-- STATUS -->
				<div class="col-xs-6 col-sm-1 form-group">
					<label class="text-white">Status</label>
					<select id="status" name="_1_<?= $_acao ?>_sgdepartamento_status" class="form-control">
						<? fillselect(SgDepartamentoController::$status, $_1_u_sgdepartamento_status); ?>
					</select>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<?/* if ($_1_u_sgdepartamento_idsgdepartamento) { ?>
				<!-- GRUPO -->
				<div class="w-100">
					<div class="d-flex flex-wrap align-items-center justify-around col-xs-4 col-sm-2 col-lg-1 ml-auto text-right float-none">
						<label for="">Grupo?</label>
						<?
						$checked = '';
						$vchecked = 'Y';

						if ($_1_u_sgdepartamento_grupo == 'Y') {
							$checked = 'checked';
							$vchecked = 'N';
						}
						?>
						<input title="grupo" type="checkbox" <?= $checked ?> name="namegrupo" onclick="altcheck('sgdepartamento','grupo',<?= $_1_u_sgdepartamento_idsgdepartamento ?>,'<?= $vchecked ?>')" class="m-0">
						<? if($checked && $_1_u_sgdepartamento_status == 'ATIVO') { ?>
							<? if($idImGrupo) { ?>
								<a href="?_modulo=imgrupo&_acao=u&idimgrupo=<?= $idImGrupo ?>&_idempresa=1" class="fa fa-bars pointer hoverazul" title="Grupo" target="_blank"></a>
							<? } else {?>
								<i class="fa fa-warning text-warning" title="Grupo não gerado! (Atualizar bim)"></i>
							<? } ?>
						<? } ?>
					</div>
				</div>
			<? }*/ ?>	
			<div class="d-flex flex-wrap w-100">
				
				<div class="col-xs-12 col-sm-12 form-group">
					<!-- Descrição -->
					<label>Descrição</label>
					<textarea placeholder="Insira as informações necessárias do departamento" name="_1_<?= $_acao ?>_sgdepartamento_desc"><?= $_1_u_sgdepartamento_desc ?></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
<? if ($_1_u_sgdepartamento_idsgdepartamento) { ?>
	<div class="col-md-4">
		<div class="col-xs-12 col-sm-6 col-md-12 px-0">
			<!-- Inserir unidades -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="unidadepadrao()"></a>	
							Unidade Padrão
						</div>
						<span data-toggle="collapse" href="#listarUn" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
					</div>
					<div class="panel-body" id="listarUn">
						<table class="table-hover w-100 mt-3">
							<tbody id="unidades-vinculadas">
								<?= listarUnidades($unidadesDpt, $_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento') ?>
							</tbody>
						</table>
						<div id="unidadepadrao" style="display: none">								
									<div class="row">
										<div class="col-md-12">
										<?if(!$possuiVinculoComUnidade) { ?>
											<table class="w-100">
												<tr>
													<td>Selecione as unidades autorizadas a solicitar custos deste departamento.</td>
												</tr>
												<tr>
													
													<td>
														<input class="compacto unidadesdepartamento" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a unidade">
													</td>
												</tr>
											</table>
										<?}
										if(count($unidadesDpt['centrocusto'])){
										?>
											<div class="panel panel-default">
												<div class="panel-body">
													<table class="table-hover w-100 mt-3 table table-striped">
													<tbody id="unidades-vinculadas">
													<tr>
														<th class="py-3 cabitem">Unidade</th> 
														<th class="cabitemAcao" >
															Ações
														</th>
													</tr>
														<?=listarUnidades($unidadesDpt, $_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento') ?>
													</tbody>
													</table>
												</div>
											</div>
										<?}?>
										</div>
									</div>								
							</div>

				<div class="panel panel-default">				
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novaunidade()"></a>
							Unidades solicitadoras de custo [<?=count($unidadesVincDpt['centrocusto'])?>]
						</div>
						<span data-toggle="collapse" href="#listarUnidadeV" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
					</div>	
					<div class="panel-body" id="listarUnidadeV">
						<table class="table-hover w-100 mt-3">
							<tbody id="unidades-vinculadas">
						<?=listarUnidadesVinc($unidadesVincDpt, $_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento') ?>
						</tbody>
						</table>
					</div>
				</div>
	
							<div id="novaunidade" style="display: none">

								
									<div class="row">
										<div class="col-md-12">
											<table class="w-100">
												<tr>
													<td>Selecione as unidades autorizadas a solicitar custos deste setor.</td>
												</tr>
												<tr>
													
													<td>
														<input class="compacto inidunidade" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a unidade">
													</td>
												</tr>
											</table>
											<?
											if(count($unidadesDpt['centrocusto'])){
											?>
											<div class="panel panel-default">
													<div class="panel-body">
													<table class="table-hover w-100 mt-3">
													<tbody id="unidades-vinculadas">
													<tr>
														<th class="py-3 cabitem">Unidade</th> 
														<th class="cabitemAcao" >
															Ações
														</th>
													</tr>
														<?=listarUnidadesVinc($unidadesVincDpt, $_1_u_sgdepartamento_idsgdepartamento, 'sgdepartamento') ?>
													</tbody>
													</table>
												</div>
											</div>
											<?}?>
										</div>
									</div>
								
							</div>
													
					</div>
				</div>
			</div>
		</div>

		<!-- Setores do departamento -->
		<div class="col-xs-12 col-sm-6 col-md-12 px-0">
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novosetor()"></a>
							Setores do Departamento [<?=count($setores)?>]
						</div>	
						<span data-toggle="collapse" href="#listarSetor" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
					</div>
					<div class="panel-body overflow-x-auto" id="listarSetor">
						<table class="table-hover w-100">
							<tbody>
								<?= listaSetores() ?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>
		</div>

		<div id="novosetor" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Selecione os setores abaixo para adicionar ao departamento.</td>
							</tr>
							<tr>
								
								<td>
									<input id="sgdepartamentovinc2" class="compacto sgdepartamentovinc2" type="text" autocomplete="new-password" cbvalue placeholder="Selecione um setor">
								</td>
							</tr>
						</table>				
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">Setor</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
									<?=listaSetores()?>
								</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>			
		</div>
	</div>


	


		<div class="col-md-4">
			<?
			if(count($unidadesDpt['centrocusto']))
			{
				reset($unidadesDpt);
				foreach($unidadesDpt['centrocusto'] as $unidade)
				{
					$_idunidade=$unidade["idunidade"];
				}
				reset($unidadesDpt);
			}	
			if(!empty($_idunidade)){

				$unidadesEv = SgDepartamentoController:: buscarUnidadeEnviocusto($_idunidade);
				$unidadesRecebe = SgDepartamentoController:: buscarUnidadeRecebecusto($_idunidade);

			?>

		<!-- Envio de Custo  -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="enviocusto()"></a>	
							Envio de Custo [<?=count($unidadesEv)?>]
							<input type="hidden" value="<?=$_idunidade?>" id="valorunidadepadrao">
						</div>
						<span data-toggle="collapse" href="#listarEnvioCusto" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
					</div>
					<div class="panel-body" id="listarEnvioCusto">
						<table class="table-hover w-100 mt-3">
							<tbody id="trunidadesenviocusto">
								<?= mostrarUnidades($unidadesEv, $_idunidade, 'unidade','Y') ?>
							</tbody>
						</table>
						<div id="enviocusto" style="display: none">								
									<div class="row">
										<div class="col-md-12">
										
											<table class="w-100">
												<tr>
													<td>Selecione as unidades autorizadas a enviar custos.</td>
												</tr>
												<tr>
													
													<td>
														<input class="compacto unidadesenviocusto" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a unidade">
													</td>
												</tr>
											</table>
										<?
										if(count($unidadesEv)>0){
										?>
											<div class="panel panel-default">
												<div class="panel-body">
													<table class="table-hover w-100 mt-3 table table-striped">
													<tbody >
													<tr>
														<th class="py-3 cabitem">Unidade</th> 
														<th class="py-3 cabitem">Percentual</th> 
														<th class="cabitemAcao" >
															Ações
														</th>
													</tr>
														<?=mostrarUnidades($unidadesEv, $_idunidade, 'unidade','N') ?>
													</tbody>
													</table>
												</div>
											</div>
										<?}?>
										</div>
									</div>								
							</div>	
					</div>
				</div>
			</div>
	
			<!-- Recebimento de Custo  -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							Recebimento de Custo [<?=count($unidadesRecebe)?>]
						</div>
						<span data-toggle="collapse" href="#listarRecebCusto" aria-expanded="false" >
							<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
						</span>
					</div>					
					<div class="panel-body overflow-x-auto" id="listarRecebCusto">
						<table class="table-hover w-100 mt-3">
							<tbody id="unidades-vinculadas">
								<?= mostrarUnidades($unidadesRecebe, $_idunidade, 'unidade','N') ?>
							</tbody>
						</table>	
					</div>
				</div>
			</div>
		
			<?}// if(!empty($_idunidade)){?>
	
			<!-- Grupos Vinculados -->
			<div class="col-xs-12">
				<div class="panel panel-default">
				<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novogrupo()"></a>
							Grupos Vinculados [<?=count($grupos)?>]
						</div>
						<span data-toggle="collapse" href="#listargrupo" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>
					</div>
					<div class="panel-body overflow-x-auto" id="listargrupo">
						<table class='table-hover w-100'>
							<tbody>
								<?= listarGruposVinculados() ?>
							</tbody>
						</table>
						<hr>
					</div>
					</div>
			</div>
			<div id="novogrupo" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Por favor, escolha os grupos disponíveis para o departamento abaixo.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto vincular-grupo" type="text" autocomplete="new-password" cbvalue placeholder="Selecione o grupo">
								</td>
							</tr>
						</table>
						<?
						if(count($grupos)> 0){
						?>
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">Grupo</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
								<?= listarGruposVinculados() ?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>
		</div>
			
	</div>
	<div class="col-md-4">
		<div class="col-xs-12 col-sm-6 col-md-12 px-0">
		
			<?
			
			//Lista, Insere e Exclui as Lp's do Departamento (13-03-2020) 
			?>
			<div class="col-xs-12" id="listadelps">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novalp()"></a>
							LP's do Departamento [<?=count($lps)?>]
						</div>						
						<span data-toggle="collapse" href="#listarLps" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>
					</div>
					<div class="panel-body" id="listarLps">
						<table class="table-hover w-100">
							<tbody>
								<?= listarLps() ?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>

			<div id="novalp" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Por favor, escolha as LPs disponíveis para o departamento abaixo.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto lpdepartamento" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a LP">
								</td>
							</tr>
						</table>
						<?
						if(count($lps)> 0){
						?>
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">LP</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
								<?= listarLps() ?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>

			<!-- Categoria -->
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novacategoria()"></a>
								Categoria [<?=count($grupoES)?>]
						</div>
						<span data-toggle="collapse" href="#listarCat" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>
					</div>
					<div class="panel-body" id="listarCat">
						<table class="table-hover w-100">
							<tbody>
								<?= listaGrupoEs() ?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>

			<div id="novacategoria" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Selecione as categorias abaixo para adicionar ao departamento.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto grupo-es-objeto" <? if ($_1_u_sgdepartamento_status == 'INATIVO') { ?> disabled <? } else { ?> type="text" autocomplete="new-password" cbvalue placeholder="Selecione a categoria" <? } ?>>
								</td>
							</tr>
						</table>
						<?
						if(count($grupoES)> 0){
						?>
						<div class="panel panel-default">
								<div class="panel-body">
								<table class="table-hover w-100 mt-3">
								<tbody id="unidades-vinculadas">
								<tr>
									<th class="py-3 cabitem">Categoria</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
								<?= listaGrupoEs() ?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>

			<!-- Documentos vinculados -->
			<div class="col-xs-12">
				<div class="panel panel-default">
				<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							 Documentos vinculados[<?=count($documentosVinculados)?>]
						</div>	
						<span data-toggle="collapse" href="#listarDocs" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>	
					</div>
					<div class="panel-body overflow-x-auto" id="listarDocs">
						<table class="table-hover w-100">
							<? foreach ($documentosVinculados as $documento) { ?>
								<tr>
									<td><?= $documento["titulo"] ?></td>
									<td>
										<a class="fa fa-bars pointer hoverazul" title="Documento" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?= $documento['idsgdoc'] ?>')"></a>
									</td>
								</tr>
							<? } ?>
						</table>
					</div>
				</div>
			</div>

		</div>
		<div id="historico" style="display: none">
			<table class="table table-hover">
				<?
				if (count($historico))
				{ ?>
					<thead>
						<tr>
							<th scope="col">Pessoa</th>
							<th scope="col">Departamento</th>
							<th scope="col">Alteração</th>
							<th scope="col">Por</th>
							<th scope="col">Em</th>
						</tr>
					</thead>
					<tbody>
						<?
						foreach ($historico as $item)
						{
							if ($item['acao'] == "i") {
								$acao = "Inserção";
							}
							if ($item['acao'] == "d") {
								$acao = "Remoção";
							}
							if ($item['acao'] == "u") {
								$acao = "Atualização";
							}
						?>
							<tr>
								<td><?= $item['nomecurto'] ?></td>
								<td><?= $item['departamento'] ?></td>
								<td><?= $acao ?></td>
								<td><?= $item['alteradopor'] ?></td>
								<td><?= dmahms($item['alteradoem']) ?></td>
							</tr>
					</tbody>
				<?		}
					}
			?>
			</table>
		</div>
	</div>
	<? } ?>
	<?
	if (!empty($_1_u_sgdepartamento_idsgdepartamento)) { // trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $_1_u_sgdepartamento_idsgdepartamento; // trocar p/ cada tela o id da tabela
		require 'viewAssinaturas.php';
	}
	$tabaud = "sgdepartamento"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';

	require (__DIR__.'/js/sgdepartamento_js.php');
?>
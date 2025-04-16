<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou nã
 */
$pagvaltabela = "sgsetor";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idsgsetor" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from " . _DBAPP . ".sgsetor where idsgsetor = '#pkid' ";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__ . "/controllers/sgdepartamento_controller.php");
require_once(__DIR__ . "/controllers/sgsetor_controller.php");
require_once(__DIR__ . "/controllers/imgrupo_controller.php");
require_once(__DIR__ ."/controllers/sgdocumento_controller.php");

$departamento = false;

if ($_1_u_sgsetor_idsgsetor) {
	$grupos = ImGrupoController::buscarGruposPorIdObjetoVincETipoObjetoVinc($_1_u_sgsetor_idsgsetor, 'sgsetor');
	$lps = SgSetorController::buscarLpsPorIdSgSetor($_1_u_sgsetor_idsgsetor);
	$lpsSetor = SgSetorController::buscarLpsDisponiveisParaVinculoPorIdSgSetor($_1_u_sgsetor_idsgsetor);
	$hitorico = SgSetorController::buscarHistoricoPorIdSgsetor($_1_u_sgsetor_idsgsetor);
	$unidadesVinculadasAoSetorPadrao = SgSetorController::buscarUnidadesPorIdSgsetorEIdEmpresa($_1_u_sgsetor_idsgsetor, cb::idempresa(),'Y');
	$unidadesVinculadasAoSetor = SgSetorController::buscarUnidadesPorIdSgsetorEIdEmpresa($_1_u_sgsetor_idsgsetor, cb::idempresa(),'N');
	$documentosVinculadosAoSetor = SgdocumentoController::buscarDocumentosVinculadosPorIdSgSetor($_1_u_sgsetor_idsgsetor, 'sgsetor');
	$pessoasDisponiveisParaVinculo = SgSetorController::buscarPessoasDisponiveisParaVinculoPorIdEmpresa(cb::idempresa());
	$gruposDisponiveisParaVinculo = ImGrupoController::buscarGruposDisponiveisParaVinculoPorIdObjetoVincTipoObjetoVincEGetIdEmpresa($_1_u_sgsetor_idsgsetor, 'sgsetor');
	$unidadesDisponiveisParaVinculo = SgSetorController::buscarUnidadesDisponiveisParaVinculoPorIdSgsetorEIdEmpresa($_1_u_sgsetor_idsgsetor, cb::idempresa());
	$departamento = SgSetorController::buscarSgDepartamento($_1_u_sgsetor_idsgsetor);
	$cargosvinculados = ImGrupoController::buscarCargosVinculadosAoSetor($_1_u_sgsetor_idsgsetor);

	$unidadesDisponiveisParaVinculoEnvio=SgSetorController::buscarUnidadesDisponiveisParaVinculoEnvio(cb::idempresa());

	if($_1_u_sgsetor_grupo == 'Y')
	{
		$idImGrupo = ImGrupoController::buscarGrupoPorIdObjetoExtETipoObjetoExt($_1_u_sgsetor_idsgsetor, 'sgsetor')['idimgrupo'];
	}
}

function listarFucionario()
{
	global $_1_u_sgsetor_idsgsetor;

	$funcionarios = SgSetorController::buscarFuncionariosPorIdSgSetor($_1_u_sgsetor_idsgsetor);

	foreach ($funcionarios as $funcionario) {
		$modulo = "pessoa";

		if ($funcionario["idtipopessoa"] == 1) {
			$modulo = "funcionario";
		}

		$title = "Editar Funcionario";
		echo 	"<tr>
					<td>
						<a title='$title' target='_blank' href='?_modulo=$modulo&_acao=u&idpessoa={$funcionario["idpessoa"]}'>{$funcionario["nome"]}</a>
					</td>
					<td align='right' width='70px'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='inativaobjeto({$funcionario['idpessoaobjeto']})' title='Excluir!'></i>
					</td>
                </tr>";
	}
}

function listarCoordenador()
{
	global $_1_u_sgsetor_idsgsetor;

	$coordernadores = SgSetorController::buscarCoordenadoresPorIdSgSetor($_1_u_sgsetor_idsgsetor);

	foreach ($coordernadores as $coordenador) {
		$modulo = "pessoa";

		if ($coordenador["idtipopessoa"] == 1) {
			$modulo = "funcionario";
		}

		echo "	<tr>
					<td>
						<a title='Editar Funcionario' target='_blank' href='?_modulo={$modulo}&_acao=u&idpessoa={$coordenador["idpessoa"]}'>{$coordenador["nome"]}</a>
					</td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularCoordenadorSetor({$coordenador['idpessoaobjeto']})' title='Excluir!'></i>
					</td>
                </tr>";
	}
}

function listarPessoas()
{
	global $_1_u_sgsetor_idsgsetor;

	$pessoas = SgSetorController::buscarPessoasPorIdSgSetor($_1_u_sgsetor_idsgsetor);

	foreach ($pessoas as $pessoa) {
		$modulo = "pessoa";

		if ($pessoa["idtipopessoa"] == 1) {
			$modulo = "funcionario";
		}

		echo "<tr><td><a title='Editar' target='_blank' href='?_modulo={$modulo}&_acao=u&idpessoa={$pessoa["idpessoa"]}'>{$pessoa["nome"]}</a></td>
                </tr>";
	}
}

function listarGruposDeSetoresVinculados()
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

//Lista as Lp's inseridas no Departamento (Lidiane - 13-03-2020)
function listaLps()
{
	global $lps;



	foreach ($lps as $lp) {
		echo "	<tr>
					<td>
						<a title='Editar LP' target='_blank' href='?_modulo=_lp&_acao=u&idlpgrupo=" . $lp["idlpgrupopai"] . "'>" . $lp["descricao"] . "</a>
					</td>
					<td align='right'>	
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularLp(" . $lp['idlpobjeto'] . ")' title='Excluir!'></i>
					</td>
                </tr>";
	}
}

function listaGruposDeChat()
{
	global $_1_u_sgsetor_idsgsetor;

	$gruposDeChat = SgsetorController::buscarGruposDeChatPorIdSgsetor($_1_u_sgsetor_idsgsetor);

	echo "<table class='table-hover' style='width:100%'><tbody>";
	foreach ($gruposDeChat as $key => $grupo) {
		$opacity = 'opacity';
		$cor = 'vermelho hoververmelho ';
		$bg = '#eee';

		if ($grupo["status"] == 'ATIVO') {
			$opacity = '';
			$cor = 'verde hoververde';
			$bg = '#eee';
		}

		if ($grupo["grupodestino"] != $_grupoatual) {
			if ($key > 0) {
				echo "</tr>";
			}
			echo "<tr class='" . $opacity . "'><td>" . $grupo["grupodestino"] . "</td>";
			$_grupoatual = $grupo["grupodestino"];
		}

		if ($grupo["tiporegra"]) {
			if ($grupo["tiporegra"] == 'GRUPO') {
				$mostrar = 'PESSOA<br>GRUPO';
			}
			if ($grupo["tiporegra"] == 'GRUPOGRUPO') {
				$mostrar = 'GRUPO<br>GRUPO';
			}
			if ($grupo["tiporegra"] == 'MENSAGEMDIRETA') {
				$mostrar = 'PESSOA<br>PESSOA';
			}

			echo "<td align='right' style='font-size:8px;padding-right: 0px;'><div style='border:1px solid #ccc; padding-left:3px; padding-right:3px; background:" . $bg . "'>" . $mostrar . "</div></td><td style='padding-left: 0px;'><div style='background:#ccc; height: 24px; width: 18px; padding-left: 3px; padding-right: 3px'><i class='fa fa-check-circle-o $cor' status='" . $grupo["status"] . "' idimregra='" . $grupo["idimregra"] . "' idobjetodestino='" . $grupo["idobjetodestino"] . "'  idobjetoorigem='" . $grupo["idobjetoorigem"] . "'  tiporegra='" . $grupo["tiporegra"] . "' onclick='AlteraComunicacao(this)'></i></div></td>";
		} else {
			echo "<td align='right' style='font-size:8px'>HABILITAR</td><td><i class='fa fa-plus ' style='color:silver' status='" . $grupo["status"] . "' idimregra='" . $grupo["idimregra"] . "' idobjetodestino='" . $grupo["idobjetodestino"] . "'  idobjetoorigem='" . $grupo["idobjetoorigem"] . "'  tiporegra='" . $grupo["tiporegra"] . "' onclick='AlteraComunicacao(this)'></i></td>";
		}
	}
	echo "</tr></tbody></table>";
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

function listarUnidadesapagar($unidades, $idobjeto = null, $tipoobjeto = null)
{
	$title = 'Editar Unidade';
	$trStart = '<tr>';
	$trEnd   = '</tr>';

	echo "$trStart
				<td colspan='12' class='bold' style='background-color: #e6e6e6;color: #666;'>Centro de custo</td>
			$trEnd";

	if(count($unidades['centrocusto']))
	{
		foreach ($unidades['centrocusto'] as $unidade) {
			$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>";
	
			if (($idobjeto == null && $tipoobjeto == null) || (($idobjeto && $tipoobjeto) && ($unidade['idobjeto'] == $idobjeto && $unidade['tipoobjeto'] == $tipoobjeto)) || ($tipoobjeto && $tipoobjeto == 'sgsetor')) {
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

	if(!count($unidades['solicitacoes'])) return;
	
	echo "$trStart
				<td colspan='12' class='bold' style='background-color: #e6e6e6;color: #666;'>Solicitações em geral</td>
			$trEnd";

	foreach ($unidades['solicitacoes'] as $unidade) {
		$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>";

		if (($idobjeto == null && $tipoobjeto == null) || (($idobjeto && $tipoobjeto) && ($unidade['idobjeto'] == $idobjeto && $unidade['tipoobjeto'] == $tipoobjeto)) || ($tipoobjeto && $tipoobjeto == 'sgsetor')) {
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

?>
<link href="/form/css/sgsetor_css.css?_<?=date("dmYhms")?>" rel="stylesheet" />
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
				<? if($_1_u_sgsetor_idsgsetor)
				{?>
					<input name="_1_<?= $_acao ?>_sgsetor_idsgsetor" type="hidden" value="<?= $_1_u_sgsetor_idsgsetor ?>" readonly='readonly'>
					<div class="col-xs-6 col-sm-1 form-group">
						<label for="" class="text-white">ID</label>
						<div class="form-control alert-warning">
							<label for="">
								<?= $_1_u_sgsetor_idsgsetor ?>
							</label>
						</div>
					</div>
				<?}?>
				<!-- SETOR -->
				<div class="col-xs-6 col-sm-3 form-group">
					<label for="" class="text-white">Setor</label>
					<input class="form-control" name="_1_<?= $_acao ?>_sgsetor_setor" type="text" value="<?= $_1_u_sgsetor_setor ?>" vnulo>
				</div>
				<!-- DEPARTAMENTO -->
				<? if (!empty($_1_u_sgsetor_idsgsetor)) { ?>						
					<div class="col-xs-6 col-sm-3 form-group">
						<label for="" class="text-white">Departamento</label>
						<div class="form-group alert-warning form-control d-flex flex-between align-items-center">
							<label>
								<?= $departamento ? $departamento['departamento'] : '[SEM VINCULO]' ?>
							</label>
							<? if($departamento) { ?>
								<a href="?_modulo=sgdepartamento&_acao=u&idsgdepartamento=<?= $departamento['idsgdepartamento'] ?>" class="fa fa-bars pointer hoverazul" title="Departamento" target="_blank"></a>
							<? } ?>
						</div>
					</div>
				<? } ?>
				<!-- COORDENADOR -->
				<div class="col-xs-6 col-sm-2 form-group">
						<!-- Coordenadores do setor -->
						<label class="text-white">Coordenador</label>
<?
		$coordernadores = SgSetorController::buscarCoordenadoresPorIdSgSetor($_1_u_sgsetor_idsgsetor);
		$qtdCord=count($coordernadores);
		if($qtdCord>0){
			foreach($coordernadores as $coordenador){
				$modulo = "pessoa";
				if($coordenador["idtipopessoa"] == 1)
				{
					$modulo = "funcionario";
				}
?>
					<div class="form-group alert-warning form-control d-flex flex-between align-items-center">
						<div class="col-xs-10 py-0  px-0">
							<a title='Editar Funcionario' target='_blank' href='?_modulo=<?=$modulo?>&_acao=u&idpessoa=<?=$coordenador["idpessoa"]?>'><?=$coordenador["nome"]?></a>		
						</div>
						<div class="col-xs-1 px-0 text-center">	
							<i class='fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable' onclick="desvincularCoordenadorSetor(<?=$coordenador['idpessoaobjeto']?>)" title='Excluir!'></i>
						</div>
						<div class="col-xs-1 px-0 text-center">
							<label><a class="fa fa-search azul pointer hoverazul" title="Histórico" onClick="setorPessoa();"></a></label>
						</div>	
					</div>
<?
			}
		}else{
	?>
					<div class="form-group alert-warning form-control d-flex flex-between align-items-center">
						<div class="col-xs-11 py-0  px-0">
							<input id="coordenadorsetor" class="compacto" <? if ($_1_u_sgsetor_status == 'INATIVO') { ?> disabled <? } else { ?> type="text" autocomplete="new-password" cbvalue placeholder="Selecione" <? } ?>>
						</div>									
					</div>
<?
		}
?>				
				</div>
					<!-- GRUPO -->
				<div class="col-xs-6 col-sm-1 form-group">
					<label class="text-white">Grupo</label>
					<div class='nowrap'>
					<select id="status" name="_1_<?= $_acao ?>_sgsetor_grupo" class="form-control">
						<? fillselect(SgSetorController::$grupo, $_1_u_sgsetor_grupo); ?>
					</select>
					<? if($_1_u_sgsetor_grupo=='Y' && $_1_u_sgsetor_status == 'ATIVO') { ?>
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
					<label for="" class="text-white">Status</label>
					<select id="status" name="_1_<?= $_acao ?>_sgsetor_status" class="form-control" vnulo>
						<? fillselect(SgSetorController::$status, $_1_u_sgsetor_status); ?>
					</select>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<div class="d-flex flex-wrap w-100">
			
				<!-- Observacao -->
				<div class="col-xs-12 col-sm-12 form-group">
					<label>Descrição</label>
					<textarea placeholder="Insira as informações necessárias do setor" class="form-control" name="_1_<?= $_acao ?>_sgsetor_desc"><?= $_1_u_sgsetor_desc ?></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
<? if (!empty($_1_u_sgsetor_idsgsetor)) { ?>
	<div class="d-flex flex-wrap col-xs-12 px-0">
	<div class="col-xs-12 col-sm-6 col-md-4 px-0">
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
					<div class="panel-body overflow-x-auto" id="listarUn">
						<table class='table-hover w-100 mt-3'>
							<tbody id="unidades-vinculadas">
								<?= listarUnidades($unidadesVinculadasAoSetorPadrao, null, 'sgsetor') ?>
							</tbody>
						</table>
						<div id="unidadepadrao" style="display: none">								
									<div class="row">
										<div class="col-md-12">
										<?if(count($unidadesVinculadasAoSetorPadrao['centrocusto'])<1){ ?>
											<table class="w-100">
												<tr>
													<td>Selecione as unidades autorizadas a solicitar custos deste setor.</td>
												</tr>
												<tr>
													
													<td>
														<input class="compacto unidade-setor" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a unidade">
													</td>
												</tr>
											</table>
										<?}
										if(count($unidadesVinculadasAoSetorPadrao['centrocusto'])){
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
													<?= listarUnidades($unidadesVinculadasAoSetorPadrao, null, 'sgsetor') ?>
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
										Unidades solicitadoras de custo [<?=count($unidadesVinculadasAoSetor['centrocusto'])?>]
									</div>
									<span data-toggle="collapse" href="#listarUnidadeV" aria-expanded="false" >
										<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
									</span>
								</div>	
								<div class="panel-body" id="listarUnidadeV">
									<table class="table-hover w-100 mt-3">
										<tbody id="unidades-vinculadas">
									<?=listarUnidades($unidadesVinculadasAoSetor, null, 'sgsetor') ?>
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
											if(count($unidadesVinculadasAoSetor['centrocusto'])){
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
													<?=listarUnidades($unidadesVinculadasAoSetor, null, 'sgsetor') ?>
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
			<div class="col-xs-12">		
				<div class="panel panel-default">
					<div class="panel-heading d-flex justify-content-between align-items-center">
							<div class="left-content d-flex align-items-center">
								Cargos Vínculados[<?=count($cargosvinculados)?>]
							</div>
							<span data-toggle="collapse" href="#listarCargo" aria-expanded="false" >
								<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
							</span>		
						</div>
						<div class="panel-body overflow-x-auto" id="listarCargo">
							<table class="table-hover w-100">
								<? foreach ($cargosvinculados as $cargo) { ?>
									<tr>
										<td><?= $cargo["cargo"] ?></td>
										<td>
											<a class="fa fa-bars pointer hoverazul" title="cargo" onclick="janelamodal('?_modulo=sgcargo&_acao=u&idsgcargo=<?= $cargo['idsgcargo'] ?>')"></a>
										</td>
									</tr>
							<? } ?>
						</table>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xs-12 col-sm-6 col-md-4 px-0">
			<div class="col-xs-12">
				<div class="panel panel-default">
				<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
							<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novopessoa()"></a>
								Pessoas do setor
								<a class="fa fa-search btn-lg azul pointer hoverazul" title="Histórico" onClick="setorPessoa();"></a>
						</div>
						<span data-toggle="collapse" href="#listarpessoas" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>
					</div>
					<div class="panel-body overflow-x-auto" id="listarpessoas">
						<table class='table-hover w-100'>
							<tbody>
								<?= listarFucionario() ?>
								<?= listarPessoas() ?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>
			<div id="novopessoa" style="display: none">								
				<div class="row">
					<div class="col-md-12">
						<table class="w-100">
							<tr>
								<td>Por favor, escolha grupos disponíveis para o setor abaixo.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto sgsetorvinc" type="text" autocomplete="new-password" cbvalue placeholder="Selecione">
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
									<th class="py-3 cabitem">Pessoa</th> 
									<th class="cabitemAcao" >
										Ações
									</th>
								</tr>
								<?= listarFucionario() ?>
								<?= listarPessoas() ?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>





			<?
			
			if(count($unidadesVinculadasAoSetorPadrao['centrocusto'])){

				reset($unidadesVinculadasAoSetorPadrao);
				foreach($unidadesVinculadasAoSetorPadrao['centrocusto'] as $unidade)
				{
					$_idunidade =$unidade["idunidade"];
				}
				reset($unidadesVinculadasAoSetorPadrao);

			}	
				
			if(!empty($_idunidade)){

			$unidadesEv = SgSetorController:: buscarUnidadeEnviocusto($_idunidade);
			$unidadesRecebe = SgSetorController:: buscarUnidadeRecebecusto($_idunidade);
			
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
								<?=mostrarUnidades($unidadesEv, $_idunidade, 'unidade','Y') ?>
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
								<?=mostrarUnidades($unidadesRecebe, $_idunidade, 'unidade','N') ?>
							</tbody>
						</table>	
					</div>
				</div>
			</div>
		
			<?}// if(!empty($_idunidade)){?>


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
								<?= listarGruposDeSetoresVinculados() ?>
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
								<td>Por favor, escolha grupos disponíveis para o setor abaixo.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto objetovinculo" type="text" autocomplete="new-password" cbvalue placeholder="Selecione o grupo">
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
								<?= listarGruposDeSetoresVinculados() ?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4" style="display:none">
			<div class="panel panel-default">
				<div class="panel-heading">Comunicação via Chat</div>
				<div class="panel-body overflow-x-auto">
					<table class='table-hover' style='width:100%'>
						<tbody>
							<? if (!empty($_1_u_sgsetor_idsgsetor)) { ?>
								<?= listaGruposDeChat() ?>
							<? } ?>
						</tbody>
					</table>
					<hr>
				</div>
			</div>
		</div>


		<div class="col-xs-12 col-sm-6 col-md-4 px-0">
		<? //Lista, Insere e Exclui as Lp's do Setor (13-03-2020) ?>
			<div class="col-xs-12">				
				<div class="panel panel-default">
				<div class="panel-heading d-flex justify-content-between align-items-center">
						<div class="left-content d-flex align-items-center">
						<a class='fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul' title='Adicionar' onclick="novalp()"></a>
							LP's do Setor[<?=count($lps)?>]
						</div>	
						<span data-toggle="collapse" href="#listarLps" aria-expanded="false" >
                            <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                        </span>	
					</div>
					<div class="panel-body overflow-x-auto" id="listarLps">
						<table class='table-hover w-100'>
							<tbody>
								<?=listaLps() ?>
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
								<td>Por favor, escolha as LPs disponíveis para o setor abaixo.</td>
							</tr>
							<tr>
								
								<td>
									<input class="compacto lpsetor" type="text" autocomplete="new-password" cbvalue placeholder="Selecione a LP">
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
								<?=listaLps()?>
								</tbody>
								</table>
							</div>
						</div>
						<?}?>
					</div>
				</div>
			</div>

			<div class="col-xs-12">		
				<div class="panel panel-default">
						<div class="panel-heading d-flex justify-content-between align-items-center">
							<div class="left-content d-flex align-items-center">
								Documentos do setor[<?=count($documentosVinculadosAoSetor)?>]
							</div>
							<span data-toggle="collapse" href="#listarDocs" aria-expanded="false" >
								<i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
							</span>	
					</div>
					<div class="panel-body overflow-x-auto" id="listarDocs">
						<table class="table-hover w-100">
							<? foreach ($documentosVinculadosAoSetor as $documento) { ?>
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
	</div>
<? } ?>
<div id="historico" style="display: none">
	<table class="table table-hover">
		<thead>
			<tr>
				<th scope="col">Pessoa</th>
				<th scope="col">Setor</th>
				<th scope="col">Alteração</th>
				<th scope="col">Por</th>
				<th scope="col">Em</th>
			</tr>
		</thead>
		<tbody>
			<?
			foreach ($hitorico as $item) {
				if ($item['acao'] == "i") {
					$acao = "Inserção";
				}
				if ($item['acao'] == "d") {
					$acao = "Remoção";
				}
				if ($item['acao'] == "u") {
					$acao = "Atualização";
				} ?>
				<tr>
					<td><?= $item['nomecurto'] ?></td>
					<td><?= $item['setor'] ?></td>
					<td><?= $acao ?></td>
					<td><?= $item['alteradopor'] ?></td>
					<td><?= dmahms($item['alteradoem']) ?></td>
				</tr>
		</tbody>
	<? } ?>
	</table>
</div>
<?
if (!empty($_1_u_sgsetor_idsgsetor)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_sgsetor_idsgsetor; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}

$tabaud = "sgsetor"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

require(__DIR__ . "/js/sgsetor_js.php");
?>
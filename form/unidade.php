<?
require_once("../inc/php/functions.php");
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
$pagvaltabela = "unidade";
$pagvalcampos = array(
	"idunidade" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from unidade where idunidade = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

require_once(__DIR__ . "/controllers/unidade_controller.php");
require_once(__DIR__ . "/controllers/tag_controller.php");
require_once(__DIR__ . "/controllers/_modulo_controller.php");
require_once(__DIR__ . "/controllers/plantel_controller.php");
require_once(__DIR__ . "/controllers/prodserv_controller.php");
require_once(__DIR__ . "/controllers/sgdocumento_controller.php");

if ($_1_u_unidade_idunidade) {
	$conselhosAreasDepsSetoresDisponiveisParaVinculo = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($_1_u_unidade_idunidade);
	$conselhosAreasDepsSetoresSelecionado = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($_1_u_unidade_idunidade, $_1_u_unidade_idobjeto, $_1_u_unidade_tipoobjeto);

	if(!$conselhosAreasDepsSetoresSelecionado)
	{
		$conselhosAreasDepsSetoresSelecionado[0]['tipo'] = 'sgsetor';
	}

	$conselhos = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($_1_u_unidade_idunidade, null, 'sgconselho');
	$areas = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($_1_u_unidade_idunidade, null, 'sgarea');
	$departamentos = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($_1_u_unidade_idunidade, null, 'sgdepartamento');
	$setores = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($_1_u_unidade_idunidade, null, 'sgsetor');
	$pessoa = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($_1_u_unidade_idunidade, null, 'pessoas');

	$produtoDisponiveisParaVinculo = ProdServController::buscarProdServDisponivelParaVinculoEmUnidades($_1_u_unidade_idunidade, cb::idempresa());
	$tagsDisponiveisParaVinculo = TagController::buscarTagsDisponiveisParaVinculoEmUnidades(cb::idempresa());
	$documentosDisponiveisParaVinculo = SgdocumentoController::buscarSgDocDisponiveisParaVinculoEmUnidades($_1_u_unidade_idunidade, cb::idempresa());
	$modulosDisponiveisParaVinculo = _moduloController::buscarModulosDisponiveisParaVinculoEmUnidades($_1_u_unidade_idunidade, cb::idempresa());
	$planteisDisponiveisParaVinculo = PlantelController::buscarPlanteisDisponiveisParaVinculoEmUnidades(cb::idempresa());

	$vinculos = [];
	$vinculos['tags'] = TagController::buscarTagsPorIdunidadeEIdEmpresa($_1_u_unidade_idunidade, cb::idempresa());
	$vinculos['produtos'] = ProdServController::buscarprodServPorIdUnidadeEIdEmpresa($_1_u_unidade_idunidade, cb::idempresa());
	$vinculos['modulos'] = _moduloController::buscarModuloPorUnidadeEIdEmpresa($_1_u_unidade_idunidade, cb::idempresa());
	$vinculos['planteis'] = PlantelController::buscarPlantelPorIdUnidadeEIdEmpresa($_1_u_unidade_idunidade, cb::idempresa());
	$vinculos['documentos'] = SgdocumentoController::buscarSgDocPorIdUnidadeEIdEmpresa($_1_u_unidade_idunidade, cb::idempresa());
}

function listarConselhoAreaDepSetores()
{
	global $_1_u_unidade_idunidade;

	$unidades = UnidadeController::buscarConselhosAreasDepsSetoresDisponiveisParaVinculoPorIdUnidade($_1_u_unidade_idunidade);


	foreach ($unidades as $unidade) {
		$modulo = $unidade['tipoobjeto'];
		$idColuna = "id$modulo";

		echo "<tr>
				<td>
					<a title='Editar' target='_blank' href='?_modulo=" . $modulo . "&_acao=u&$idColuna=" . $unidade['idobjeto'] . "'>" . $unidade["label"] . "</a>
				</td>
                <td align='right'>	
                    <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidadeObjeto(" . $unidade['idunidadeobjeto'] . ")' title='Excluir!'></i>
                </td>
             </tr>";
	}
}

?>
<!-- CSS -->
<link href="/form/css/unidade_css.css" rel="stylesheet" />

<div class="w-100">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<!-- HEADER -->
			<div class="panel-heading">
				<div class="w-100 d-flex flex-wrap flex-between">
					<!-- ID -->
					<input name="_1_<?= $_acao ?>_unidade_idunidade" type="hidden" value="<?= $_1_u_unidade_idunidade ?>" readonly='readonly'>
					<div class="col-xs-6 col-sm-3 form-group">
						<label for="" class="text-white">Unidade</label>
						<input class='form-control' name="_1_<?= $_acao ?>_unidade_unidade" type="text" value="<?= $_1_u_unidade_unidade ?>" vnulo>
					</div>
					<div class="col-xs-6 col-sm-3 form-group">
						<label for="" class="text-white">Status</label>
						<select name="_1_<?= $_acao ?>_unidade_status" class="form-control">
							<? fillselect(UnidadeController::$status, $_1_u_unidade_status); ?>
						</select>
						<input type="hidden" name="statusant" value="<?=$_1_u_unidade_status?>">
					</div>
				</div>
			</div>
			<!-- BODY -->
			<div class="panel-body">
				<div class="w-100 d-flex flex-wrap">
					<!-- Dados cadastrais -->
					<div class="col-xs-12 col-md-6 d-flex flex-wrap h-100">
						<div class="col-xs-12 mb-2">
							<h4 class="text-uppercase text-gray-80 font-bold">Dados cadastrais</h4>
							<hr class="my-4 border-gray-50"/>
						</div>
						<!-- Consome na transferência: -->
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">
								Consome na transferência
							</label>
							<select class='form-control' name="_1_<?= $_acao ?>_unidade_consomeun" title="Consome na transferência">
								<option value="">Selecionar</option>
								<? fillselect(UnidadeController::$valorSimNao, $_1_u_unidade_consomeun); ?>
							</select>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">Tipo</label>
							<select name="_1_<?= $_acao ?>_unidade_idtipounidade" class="form-control" title="Tipo">
								<option value="">Selecionar tipo</option>
								<? fillselect(UnidadeController::buscarTipos(), $_1_u_unidade_idtipounidade); ?>
							</select>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">Centro de Custo</label>
							<select name="_1_<?= $_acao ?>_unidade_idcentrocusto" class="form-control" title="Centro de Custo" vnulo>
								<option value="">Selecionar Centro de Custo</option>
								<? fillselect(UnidadeController::buscarCentroCusto(), $_1_u_unidade_idcentrocusto); ?>
							</select>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">Ordem</label>
							<input class='form-control' name="_1_<?= $_acao ?>_unidade_ord" type="number" value="<?= $_1_u_unidade_ord ?>" placeholder="Ordem" title="Ordem">
						</div>
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">
								Solicitação de Materiais / Prodserv
							</label>
							<select class='form-control' name="_1_<?= $_acao ?>_unidade_requisicao">
								<? fillselect(UnidadeController::$valorSimNao, $_1_u_unidade_requisicao); ?>
							</select>
						</div> 
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">
								Ocultar
							</label>
							<select class='form-control' name="_1_<?= $_acao ?>_unidade_terceirizado" title="Terceirizado">
								<option value="">Selecionar</option>
								<? fillselect(UnidadeController::$valorSimNao, $_1_u_unidade_terceirizado); ?>
							</select>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">
								Mostrar Estoque Convertido
							</label>
							<select class='form-control' name="_1_<?= $_acao ?>_unidade_convestoque">
								<? fillselect(UnidadeController::$valorSimNao, $_1_u_unidade_convestoque); ?>
							</select>
						</div> 
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">
								Centro de Distribuição
							</label>
							<select class='form-control' name="_1_<?= $_acao ?>_unidade_cd" title="Centro de Distribuição">
								<option value="">Selecionar</option>
								<? fillselect(UnidadeController::$valorSimNao, $_1_u_unidade_cd); ?>
							</select>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-4 form-group">
							<label for="">
								Tipo do Custos
							</label>
							<select class='form-control' name="_1_<?= $_acao ?>_unidade_tipocusto" title="Tipo do Custo">
								<? fillselect(UnidadeController::$tipocusto, $_1_u_unidade_tipocusto); ?>
							</select>
						</div>
					</div>
					<!-- OGRANOGRAMA -->
					<? if ($_1_u_unidade_idunidade) { ?>
						<div class="col-xs-12 col-md-6 d-flex flex-wrap">
							<div class="col-xs-12 mb-2">
								<h4 class="uppercase text-gray-80 font-bold">ORGANOGRAMA</h4>
								<hr class="my-4 border-gray-50"/>
								<!-- Departamento / Área / Setor -->
								<div class="w-100 form-group">
									<label for="area-dep-setor" class="mt-2">
										Setor / Pessoa / Departamento / Área / Conselho
									</label>
									<div class="w-100 d-flex flex-wrap">
										<div class="col-xs-12 col-sm-6 col-lg-8 px-0">
											<input id="area-dep-setor" name="area_dep_setor" type="text" class="form-condiv clasol" placeholder="Setor / Pessoas / Departamento / Área / Conselho" title="Setor / Pessoas / Departamento / Área / Conselho" />
										</div>
										<?
										$tipounidade = '';

										if (stripos($_1_u_unidade_unidade, 'CEO') !== false) {
											$tipounidade = 'CEO';
										}
										?>
										<div class="col-xs-12 col-sm-6 col-lg-4 d-flex flex-between btn-group button px-0 px-md-3">
											<button onclick="filter('sgsetor', true)" type="button" class="btn btn-default fa fa-users hoverlaranja pointer floatright ml-2 <?= $conselhosAreasDepsSetoresSelecionado[0]['tipo'] == 'sgsetor' ? 'selecionado' : '' ?>" title="Selecionar Setor"></button>
											<?if($tipounidade == 'CEO'){?>
												<button onclick="filter('pessoas', true)" type="button" class="btn btn-default fa fa-user hoverlaranja pointer floatright ml-2 <?= $conselhosAreasDepsSetoresSelecionado[0]['tipo'] == 'pessoas' ? 'selecionado' : '' ?>" title="Selecionar Pessoa"></button>
											<?}?>
											<button onclick="filter('sgdepartamento', true)" type="button" class="btn btn-default fa fa-building hoverlaranja pointer floatright ml-2 <?= $conselhosAreasDepsSetoresSelecionado[0]['tipo'] == 'sgdepartamento' ? 'selecionado' : '' ?>" title="Selecionar Departamento"></button>
											<button onclick="filter('sgarea', true)" type="button" class="btn btn-default fa fa-cubes hoverlaranja pointer floatright ml-2 <?= $conselhosAreasDepsSetoresSelecionado[0]['tipo'] == 'sgarea' ? 'selecionado' : '' ?>" title="Selecionar Área"></button>
											<button onclick="filter('sgconselho', true)" type="button" class="btn btn-default fa fa-institution hoverlaranja pointer floatright ml-2 <?= $conselhosAreasDepsSetoresSelecionado[0]['tipo'] == 'sgconselho' ? 'selecionado' : '' ?>" title="Selecionar Conselho"></button>
										</div>
										<div class="col-xs-12 overflow-x-auto">
											<table class="table-hover col-xs-12 mt-3">
												<tbody id="lista-area-dep-setores">
													<?= listarConselhoAreaDepSetores() ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					<? } ?>
				</div>
			</div>
		</div>
	</div>
	<? if($_1_u_unidade_idunidade) { ?>
		<div class="w-100 d-flex flex-wrap">
			<!-- Coluna 1 -->
			<div class="d-flex flex-col flex-wrap col-xs-12 col-md-6 col-lg-4">
				<!-- Produtos -->
				<div class="w-full mb-3">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#produtosvinculados">
							<span class="text-gray-90">Produtos</span>
							<span class="text-gray-60">[<?= count($vinculos['produtos']) ?>]</span>
						</div>
						<div class="panel-body collapse" id="produtosvinculados" style="padding-top: 8px !important;">
							<div class="w-100 d-flex flex-wrap">
								<div class="w-100 d-flex flex-wrap border-b-1 border-gray-50 py-1 flex-between">
									<div class="col-xs-12">
										<span class="text-gray-100 font-bold">Produto</span>
									</div>
									<div class="col-xs-12 form-group">
										<label for=""></label>
										<input id="vinculo_produtos" type="text" class="form-control" />
									</div>
									<div class="col-xs-10 font-bold">
										Descrição
									</div>
									<div class="col-xs-2 text-center font-bold">
										Ações
									</div>
								</div>
								<? if(count($vinculos['produtos'])) {?>
									<? foreach($vinculos['produtos'] as $produto) {?>
										<div class="w-100 d-flex border-b-1 border-gray-50 py-1 flex-between items-center">
											<div class="col-xs-10">
												<span class="text-gray-100"><?= "{$produto['sigla']} - {$produto['descr']}" ?></span>
											</div>
											<div class="col-xs-2 text-center">
												<a href="?_modulo=prodserv&_acao=u&idprodserv=<?= $produto['idprodserv'] ?>&_idempresa=<?= $produto['idempresa'] ?>" target="_blank">
													<i class="fa fa-bars pointer text-primary"></i>
												</a>
												<a onclick="desvincularUnidadeObjeto(<?= $produto['idunidadeobjeto'] ?>)" class="text-lg">
													<i class="fa fa-trash text-danger pointer ml-2"></i>
												</a>
											</div>
										</div>
									<? } ?>
								<?}?>
							</div>
						</div>
					</div>
				</div>
				<!-- Modulos -->
				<div class="w-full">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#modulossvinculados">
							<span class="text-gray-90">Modulos</span>
							<span class="text-gray-60">[<?= count($vinculos['modulos']) ?>]</span>
						</div>
						<div class="panel-body collapse" id="modulossvinculados" style="padding-top: 8px !important;">
							<div class="w-100 d-flex flex-wrap">
								<div class="w-100 d-flex flex-wrap border-b-1 border-gray-50 py-1 flex-between">
									<div class="col-xs-12">
										<span class="text-gray-100 font-bold">Modulo</span>
									</div>
									<div class="col-xs-12 form-group">
										<label for=""></label>
										<input id="vinculo_modulos" type="text" class="form-control" />
									</div>
									<div class="col-xs-10">
										<span class="text-gray-100 font-bold">Rótulo</span>
									</div>
									<div class="col-xs-2">
										<span class="text-gray-100 font-bold">Ações</span>
									</div>
								</div>
								<? if(count($vinculos['modulos'])) {?>
									<? foreach($vinculos['modulos'] as $modulo) {?>
										<div class="w-100 d-flex border-b-1 border-gray-50 py-1 flex-between items-center">
											<div class="col-xs-10">
												<span class="text-gray-100"><?= "{$modulo['rotulomenu']}" ?></span>
											</div>
											<div class="col-xs-2 text-center">
												<a href="?_modulo=_modulo&_acao=u&idmodulo=<?= $modulo['modulo'] ?>" target="_blank">
													<i class="fa fa-bars pointer text-primary"></i>
												</a>
												<a onclick="desvincularUnidadeObjeto(<?= $modulo['idunidadeobjeto'] ?>)" class="text-lg">
													<i class="fa fa-trash text-danger pointer ml-2"></i>
												</a>
											</div>
										</div>
									<? } ?>
								<?}?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Coluna 2 -->
			<div class="d-flex flex-col flex-wrap col-xs-12 col-md-6 col-lg-4">
				<!-- Tags -->
				<div class="w-100 mb-3">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#tagsvinculadas">
							<span class="text-gray-90">Tags</span>
							<span class="text-gray-60">[<?=count($vinculos['tags'])?>]</span>
							<?
							if(count($vinculos['tags']) >= 1){?>
								<input type="hidden" name="bloqueiainativar" value="Y">
							<?}?>
						</div>
						<div class="panel-body collapse" id="tagsvinculadas" style="padding-top: 8px !important;">
							<div class="w-100 d-flex flex-wrap">
								<div class="w-100 d-flex flex-wrap border-b-1 border-gray-50 py-1">
									<div class="col-xs-3">
										<span class="text-gray-100 font-bold">Tag</span>
									</div>
									<div class="col-xs-12 form-group">
										<label for="vinculo_tags"></label>
										<input id="vinculo_tags" type="text" class="form-control" />
									</div>
									<div class="col-xs-2">
										<span class="text-gray-100 font-bold">Tag</span>
									</div>
									<div class="col-xs-8">
										<span class="text-gray-100 font-bold">Descrição</span>
									</div>
									<div class="col-xs-2 text-center">
										<span class="text-gray-100 font-bold">Ações</span>
									</div>
								</div>
								<? if(count($vinculos['tags'])) { ?>
									<? foreach($vinculos['tags'] as $tag) {?>
										<div class="w-100 d-flex border-b-1 border-gray-50 py-1">
											<div class="col-xs-2">
												<span class="text-gray-100"><?= "{$tag['sigla']} - {$tag['tag']}" ?></span>
											</div>
											<div class="col-xs-8">
												<span class="text-gray-100"><?= $tag['descricao'] ?></span>
											</div>
											<div class="col-xs-2 text-center">
												<a href="?_modulo=tag&_acao=u&idtag=<?= $tag['idtag'] ?>&_idempresa=<?= $tag['idempresa'] ?>" target="_blank">
													<i class="fa fa-bars pointer text-primary"></i>
												</a>
												<a onclick="desvincularUnidade(<?= $tag['idtag'] ?>, 'tag')" class="text-lg">
													<i class="fa fa-trash text-danger pointer ml-2"></i>
												</a>
											</div>
										</div>
									<? } ?>
								<? } ?>
							</div>
						</div>
					</div>
				</div>
				<!-- Divisão (Plantel) -->
				<div class="w-100">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#divisoesvinculadas">
							<span class="text-gray-90">Divisão(Plantel)</span>
							<span class="text-gray-60">[<?=count($vinculos['planteis'])?>]</span>
						</div>
						<div class="panel-body collapse" id="divisoesvinculadas" style="padding-top: 8px !important;">
							<div class="w-100 d-flex flex-wrap">
								<div class="w-100 d-flex flex-wrap border-b-1 border-gray-50 py-1 flex-between">
									<div class="col-xs-3">
										<span class="text-gray-100 font-bold">Divisão(Plantel)</span>
									</div>
									<div class="col-xs-12 form-group">
										<label for="vinculo_planteis"></label>
										<input id="vinculo_planteis" type="text" class="form-control" />
									</div>
									<div class="col-xs-10 font-bold">Descrição</div>
									<div class="col-xs-2 font-bold">Ações</div>
								</div>
								<? if(count($vinculos['planteis'])) { ?>
									<? foreach($vinculos['planteis'] as $plantel) {?>
										<div class="w-100 d-flex border-b-1 border-gray-50 py-1 flex-between items-center">
											<div class="col-xs-11">
												<span class="text-gray-100"><?= "{$plantel['sigla']} - {$plantel['plantel']}" ?></span>
											</div>
											<div class="col-xs-2 text-center">
												<a href="?_modulo=plantel&_acao=u&idplantel=<?= $plantel['idplantel'] ?>&_idempresa=<?= $plantel['idempresa'] ?>" target="_blank">
													<i class="fa fa-bars pointer text-primary"></i>
												</a>
												<a onclick="desvincularUnidade(<?= $plantel['idplantel'] ?>, 'plantel')" class="text-lg">
													<i class="fa fa-trash text-danger pointer ml-2"></i>
												</a>
											</div>
										</div>
									<?}?>
								<? } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Coluna 3 -->
			<div class="d-flex flex-col flex-wrap col-xs-12 col-md-6 col-lg-4">
				<!-- Documentos -->
				<div class="w-full mb-3">
					<div class="panel panel-default">
						<div class="panel-heading" data-toggle="collapse" href="#documentossvinculados">
							<span class="text-gray-90">Documentos</span>
							<span class="text-gray-60">[<?= count($vinculos['documentos']) ?>]</span>
						</div>
						<div class="panel-body collapse" id="documentossvinculados" style="padding-top: 8px !important;">
							<div class="w-100 d-flex flex-wrap">
								<div class="w-100 d-flex flex-wrap border-b-1 border-gray-50 py-1 flex-between">
									<div class="col-xs-3">
										<span class="text-gray-100 font-bold">Documento</span>
									</div>
									<div class="col-xs-12 form-group">
										<label for="vinculo_documentos"></label>
										<input id="vinculo_documentos" type="text" class="form-control" />
									</div>
									<div class="col-xs-8 font-bold"><span>Descrição</span></div>
									<div class="col-xs-2 font-bold"><span>Tipo</span></div>
									<div class="col-xs-2 font-bold"><span>Ações</span></div>
								</div>
								<? if(count($vinculos['documentos'])) { ?>
									<? foreach($vinculos['documentos'] as $documento) {?>
										<div class="w-100 d-flex border-b-1 border-gray-50 py-1 flex-between items-center">
											<div class="col-xs-8">
												<span class="text-gray-100"><?= "{$documento['sigla']} - {$documento['titulo']}" ?></span>
											</div>
											<div class="col-xs-2">
												<span class="text-gray-100"><?= $documento['rotulo'] ?></span>
											</div>
											<div class="col-xs-2 text-center">
												<a href="?_modulo=documento&_acao=u&idsgdoc=<?= $documento['idsgdoc'] ?>&_idempresa=<?= $documento['idempresa'] ?>" target="_blank">
													<i class="fa fa-bars pointer text-primary"></i>
												</a>
												<a onclick="desvincularUnidade(<?= $documento['idsgdoc'] ?>, 'sgdoc')" class="text-lg">
													<i class="fa fa-trash text-danger pointer ml-2"></i>
												</a>
											</div>
										</div>
									<?}?>
								<? } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?}?>
</div>

<?
if (!empty($_1_u_unidade_idunidade)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_unidade_idunidade; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}

$tabaud = "unidade"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

if ($_1_u_unidade_idunidade) {
	require_once(__DIR__ . "/js/unidade_js.php");
}
?>
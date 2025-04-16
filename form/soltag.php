<?

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");

// CONTROLLERS
require_once(__DIR__."/controllers/solmat_controller.php");
require_once(__DIR__."/controllers/soltag_controller.php");
require_once(__DIR__."/controllers/prodserv_controller.php");
require_once(__DIR__."/controllers/unidade_controller.php");
require_once(__DIR__."/controllers/fluxo_controller.php");
require_once(__DIR__."/controllers/_modulo_controller.php");
require_once(__DIR__."/controllers/tag_controller.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

$pagvaltabela = "solmat";
$pagvalcampos = array(
	"idsolmat" => "pk"
);


$pagsql = "SELECT * from solmat where idsolmat = '#pkid'";

//controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
if (!empty($_GET['idsolmatcp']))
{
	$solMatCopia = SolmatController::buscarPorChavePrimaria($_GET['idsolmatcp']);

	if($solMatCopia)
	{
		$_1_u_solmat_tipo = $solMatCopia['tipo'];
		$_1_u_solmat_unidade = $solMatCopia['unidade'];
		$_1_u_solmat_idunidade = $solMatCopia['idunidade'];
	}
}

include_once("../inc/php/controlevariaveisgetpost.php");

//Chama a Classe prodserv
// $prodservclass = new PRODSERV();
//Recuperar a unidade padrão conforme módulo pré-configurado
$_idempresa = !empty($_GET['_idempresa']) ? $_GET['_idempresa'] : $_SESSION['SESSAO']['IDEMPRESA'];
// $unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], $_idempresa);
$unidadepadrao = UnidadeController::buscarUnidadePadraoDoModulo($_GET["_modulo"], $_idempresa);
if ($_acao == 'i') 
{
	$_1_u_solmat_unidade = $unidadepadrao;
}

//LTM (28/05/2021) - Caso a amostra não tenha status, seta o primeiro quando for no insert o caso
if (empty($_1_u_solmat_status)) {
	$_1_u_solmat_status = 'ABERTO';
}

if ($_1_u_solmat_status == 'CONCLUIDO' ||  $_1_u_solmat_status == 'CANCELADO' || $_1_u_solmat_status == 'EXECUCAO') {
	$readonly2 = 'readonly';
}

$readonly = '';
$disable = '';

if ($_1_u_solmat_status == 'CONCLUIDO' || $_1_u_solmat_status == 'CANCELADO' || $_1_u_solmat_status == 'EXECUCAO' || $_1_u_solmat_status == 'SOLICITADO') {
	$readonly = 'readonly';
	$disable = 'disabled';
}
//Recupera os contato as serem selecionados
if (!empty($_1_u_solmat_idsolmat) or !empty($_GET['idsolmatcp'])) {
	$jprodservtemp = ProdServController::buscarProdServPorIdSolmatUnidadePadraoEIdEmpresa($_1_u_solmat_idsolmat, $unidadepadrao, $_idempresa);
}

$rotulo = FluxoController::buscarStatusDoFluxo('solmat', 'idsolmat', $_1_u_solmat_idsolmat);

$i = 99;

$arrayPessoa = SolTagController::listarPessoaPorIdTipoPessoa(1);
?>

<link href="/form/css/soltag_css.css" rel="stylesheet" />

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="sigla-empresa"></div>
				<div class="d-flex flex-wrap align-items-center flex-between w-100">
					<!-- ID -->
					<? if($_1_u_solmat_idsolmat) { ?>
						<div class="col-xs-6 col-sm-4 col-md-2 form-group">
							<input name="_1_<?= $_acao ?>_solmat_idsolmat" id="idsolmat" type="hidden" value="<?= $_1_u_solmat_idsolmat ?>" readonly='readonly'>
							<input name="_1_<?= $_acao ?>_solmat_status" id="status" type="hidden" value="<?= $_1_u_solmat_status ?>">
							<label for="" class="text-white">⠀ID</label>
							<div class="alert-warning form-control w-100 d-flex align-items-center">
								<label><?= $_1_u_solmat_idsolmat ?></label>
							</div>
						</div>
					<?}?>
					<!-- Tipo -->
					<div class="col-xs-6 col-sm-4 col-md-2 form-group">
						<label class="block text-white">Tipo</label>
						<select name="_1_<?= $_acao ?>_solmat_tipo" class="form-control" vnulo <?= $readonly ?>>
							<option value=""></option>
							<? fillselect(SoltagController::$tipo, $_1_u_solmat_tipo); ?>
						</select>
					</div>
					<!-- Origem -->
					<div class="col-xs-6 col-sm-4 col-md-2 form-group">
						<label class="text-white">Origem</label>
						<div class="alert-warning form-control w-100 d-flex align-items-center">
							<label>Estoque (Almoxarifado)</label>
						</div>
						<input type="hidden" value="<?=$_1_u_solmat_unidade?>"  name="_1_<?= $_acao ?>_solmat_unidade"/>
						
					</div>
					<!-- Destino -->
					<div class="col-xs-6 col-sm-4 col-md-2 form-group">
						<label class="block text-white">Destino</label>
						<?
						if (empty($_1_u_solmat_idsolmat)) {
							// $union = unionLPsUnidade(); ?>
							<select name="_1_<?= $_acao ?>_solmat_idunidade" class="form-control" <?= $readonly2 ?> vnulo<? if ($_acao == "u") {
																										echo "onchange='mudarunidade(this)'";
																									} ?>>
								<?
								
								fillselect(SolTagController::buscarUnidadesDeDestino(true), $_1_u_solmat_idunidade);
								?>
							</select>
						<? } else { ?>
							<? if (empty($_1_u_solmat_idunidade)) {
								$rotun = traduzid('unidade', 'idunidade', 'unidade', traduzid("pessoa", 'usuario', 'idunidade', $_1_u_solmat_criadopor));
							} else {
								$rotun = traduzid('unidade', 'idunidade', 'unidade', $_1_u_solmat_idunidade);
							} ?>
							<div class="alert-warning form-control w-100 d-flex align-items-center">
								<label><?= $rotun ?></label>
							</div>
							<input type="hidden" name="_1_<?= $_acao ?>_solmat_idunidade" value="<?= $_1_u_solmat_idunidade ?>" readonly>
						<? } ?>
					</div>
					<!-- Local de Entrega -->
					<? if ($_acao == "u") { ?> 
						<div class="col-xs-6 col-sm-4 col-md-2 form-group">
							<label class="block text-white">Local de Entrega</label>
							<select name="_1_<?= $_acao ?>_solmat_idtag" class="form-control" <?= $_1_u_solmat_idtag ?> <?= $readonly ?>>
								<option value=""></option>								
								<? fillselect(UnidadeController::buscarTagsPorIdTagClassIdTagTipoEIdUnidade(2, '145,297', $_1_u_solmat_idunidade, true), $_1_u_solmat_idtag); ?>
							</select>
						</div>
					<? } ?>
					<!-- Status -->
					<? if($rotulo['rotulo']) {?>
						<div class="col-xs-6 col-sm-4 col-md-2 form-group">
							<label class="text-white">Status</label>
							<div class="alert-warning form-control w-100 d-flex align-items-center">
								<label title="<?= $_1_u_solmat_status ?>" id="statusButton">
									<?= $rotulo['rotulo'] ?>
								</label>
							</div>
						</div>
					<?}?>
				</div>
			</div>
			<div class="panel-body">
				<div class="w-100 d-flex">
					<? if ($_acao != "i") { ?>
						<div class="dropdown ml-auto" id="cbDaterangeCol">
							<a href="#" class="btn btn-link dropdown-toggle cinza pointer hoverazul mt-3" type="button" id="dropdownMenuDateCol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								<span class="fa fa-lg fa-print">
							</a>
							<ul class="dropdown-menu" aria-labelledby="dropdownprint">
								<li class="dropdown-item" onclick="showModal()"><i title="Etiqueta da solicitação" class="fa fa-print pull-right fa-lg cinza pointer hoverazul"></i> Zebra</li>
								<li class="dropdown-item" onclick="janelamodal('report/relsolmat.php?idsolmat=<?= $_1_u_solmat_idsolmat ?>')"><i title="Imprimir A4" class="fa fa-print pull-right fa-lg cinza pointer hoverazul"></i> A4</li>
							</ul>
						</div>
					<? } ?>
				</div>
				<div class="row" style="margin-bottom: 30px;">
				<!-- faz itens -->
				<? if (!empty($_1_u_solmat_idsolmat) or !empty($_GET['idsolmatcp'])) 
				{ 
					if (empty($_1_u_solmat_idsolmat)) {
						$_1_u_solmat_idsolmat = $_GET['idsolmatcp'];
					}

					$prodServESolMatItem = SolmatController::buscarProdServESolMatItemPorIdSolMat($_1_u_solmat_idsolmat);
					$quantidadeTotalDeItens = count($prodServESolMatItem);

					$solMatItemSemCadastro = SolmatController::buscarSolMatItemSemCadastroPorIdSolMat($_1_u_solmat_idsolmat);
					$quantidadeTotalDeItens += count($solMatItemSemCadastro);

					$unidadesDoModuloLote = _ModuloController::buscarUnidadesPorModuloTipoGetIdEmpresaEIdTipoUnidade('lote', getidempresa("ui.idempresa", $_GET['_modulo']), 3);
					$modulos = _ModuloController::buscarModulosComUnidadesVinculadasPorGetIdEmpresa(3, getidempresa("u.idempresa", $_GET['_modulo']));
					?>
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading 2" style="background: none;padding: 8px;">
								Equipamento(s)
								<i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodos" state="<?= $userPref['collapse']['solcomitemtotal'] ?>" title="Esconder Todos" onclick="esconderMostrarTodos('expandir')" style="float: right; padding: 2px 10px 0 10px;"></i>
							</div>
							<div class="panel-body overflow-x-auto" style="background: none;padding: 8px !important;">
								<? if ($_acao == "u" && $_1_u_solmat_status == 'ABERTO') { ?>
									<div class="panel panel-default">
										<div class="panel-heading">
											<div class="row">
												<div class="cabitem">
													<div class="col-xs-3">Descrição</div>
													<div class="col-xs-3">Colaborador</div>
													<div class="col-xs-3">Local Equipamento</div>
													<div class="col-xs-3">Observação</div>
												</div>
											</div>
										</div>
										<div class="panel-body striped" style="padding:0 !important;">
											<? listaitemAberto($prodServESolMatItem, 1, true); ?>
										</div>
									</div>
								<? } else {
									listarItens($prodServESolMatItem, 1);
								} ?>
							</div>
						</div>
					</div>
				<? }
					if ($_acao == 'u') {
						listarComentarios();
					} ?>
					
				</div>
			</div>
		</div>
	</div>
</div>
<?
function listaitemAberto($prodServESolMatItem, $countInicio, $cadastrados)
{
	global 	$readonly,
			$disable, 
			$_1_u_solmat_status, 
			$_acao, 
			$_1_u_solmatitem_qtdc, 
			$_1_u_solmatitem_idtag, 
			$_1_u_solmat_idunidade,
			$arrayPessoa;

	$i = $countInicio;

	foreach($prodServESolMatItem as $item)
	{
		$atributoName = "_x{$i}_u_solmatitem";
		$excluir = "excluir({$item['idsolmatitem']})";
		
		if (!empty($_GET['idsolmatcp']) and $_acao == "i") 
		{
			$atributoName = "duplicar_{$i}";
			$excluir = "$('#_iten_{$i}').remove();";
			// $it = "_iten_" . $i;

			if (!empty($item['idprodserv']))
			{
				$excluir = "$('#_item_{$i}').remove();";
				// $it = "_item_" . $i;
			}
		}
	?>
		<div class="row tagrow">
			<input name="<?= $atributoName ?>_idsolmatitem" type="hidden" value="<?= $item['idsolmatitem'] ?>">
				<? 
				$_1_u_solmatitem_qtdc = $item['qtdc'];
				$_1_u_solmatitem_idtag = $item['idtag'];
				?>
			<input name="<?= $atributoName ?>_qtdc" type="hidden" value="<?= $item['qtdc'] ?>">
			<? if (!empty($item['idprodserv']) && $_GET['_modulo'] != 'soltag') { ?>
				<div class="col-xs-1" id="<?= $item['idsolmatitem'] ?>">
					<input name="<?= $atributoName ?>_un" type="hidden" value="<?= $item['un'] ?>">
					<?= $item['un'] ?>
				</div>
			<? } ?>

			<div class="col-xs-3">
				<?
				if ($item['idprodserv']) {?>

					<input name="<?= $atributoName ?>_idprodserv" type="hidden" value="<?= $item['idprodserv'] ?>">
					<input name="<?= $atributoName ?>_descr" type="hidden" value="<?= $item['descr'] ?>">
					<a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $item['idprodserv'] ?>')" title="<?= $item['descr'] ?>"><span class="bold"><?= $item['descr'] ?></span></a>
				<?} else {
					echo $item['descr'];
				} ?>
			</div>

			<!-- Colaborador -->
			<div class="col-xs-3">
				<input style="width: 95%;" class="insidpessoa" id="<?=$item['idsolmatitem']?>" name="<?=$atributoName?>_idpessoa" type="type" cbvalue="<?=$item['idpessoa'] ?>" value="<?=$arrayPessoa[$item['idpessoa']]["nome"] ?>">
				<? if($item['idpessoa']) { ?>
					<a title="Fornecedor" class="fa fa-bars fade pointer hoverazul" href="?_modulo=pessoa&_acao=u&idpessoa=<?=$item['idpessoa'] ?>" target="_blank"></a>
				<? } ?>
			</div>

			<? if ($cadastrados) 
			{?>
				<div class="col-xs-3">
					<select name="<?= $atributoName ?>_idtag" <?= $readonly ?>>
						<? 
						$idtagtipo = traduzid('prodserv','idprodserv','idtagtipo',$item['idprodserv']);
						if(is_numeric($idtagtipo))
						{?> 
							<option value="">Selecione onde o equipamento será utilizado</option> <?
							fillselect(UnidadeController::buscarTagsPorIdTagTipoEIdUnidade($idtagtipo, $_1_u_solmat_idunidade, true), $_1_u_solmatitem_idtag);
						} else { ?> 
							<option value="">Nenhuma opção disponível</option></option> <?
						}
						?>
					</select>
				</div>
			<? } ?>
			<div class="d-flex align-items-center <?= $cadastrados ? 'col-xs-3' : 'col-xs-6' ?>">
				<input name="<?= $atributoName ?>_obs" <?= $readonly ?> value="<?= $item['obs'] ?>" placeholder="Observação:">
				<? if ($_1_u_solmat_status == 'ABERTO' || !empty($_GET['idsolmatcp'])) { ?>				
					<a class="btn btn-link"><i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable w" onclick="<?= $excluir ?>" alt="Excluir!"></i></a>
				<? } else { 
					if($cadastrados){ ?>
					<a class="btn btn-link expandir" title="Expandir" data-toggle="collapse" idnfitem="<?= $item['idsolmatitem'] ?>" href="#solmatitem<?= $item['idsolmatitem'] ?>">
						<i class="fa fa-arrows-v fa-1x cinzaclaro hoververmelho pointer ui-droppable w" alt="Expandir"></i>
					</a>
				<? } 
			} ?>
			</div>
			<?
			if (!empty($item['idprodserv'])) { // lista intens da solmatitem
				if ($_1_u_solmat_status == 'EXECUCAO' || $_1_u_solmat_status == 'CONCLUIDO' || $_1_u_solmat_status == 'CANCELADO') {
				?>
					<? listarTags($item['idsolmatitem'], $item['idprodserv'], $item['qtdc']); ?>
				<?
				}
			}
			?>
		</div>
		<?
		$i++;
	}
	if($cadastrados)
	{?>	
		<div class="row tagrow">
			<div class="col-xs-12 col-md-6">
				<input id="qtdcadastrado" type="hidden" value="1" />
				<input style=" border: 1px solid silver;" type="text" id="insidprodserv" placeholder="Selecione o equipamento" cbvalue="produtos" <?= $readonly ?> <?= $disable ?> title="Utilize uma linha para cada equipamento">
			</div>
		</div>
	<? } else 
	{ ?> 
		<div class="row tagrow">			
			<div class="col-xs-12 col-md-6">
				<input name="_10#quantidade" title="Qtd" placeholder="Qtd" type="hidden" value="1">
				<input type="text" style=" border: 1px solid silver;width: 100%;" name="_10#prodservdescr" class="idprodserv" placeholder="Informe o produto" <?= $readonly ?>>
			</div>
		</div>
	<? }
}
function listarItens($prodServESolMatItem, $con)
{
	global $readonly, $_1_u_solmat_status, $_acao, $_1_u_solmat_tipo, $_1_u_solmatitem_qtdc, $_1_u_solmatitem_idprodservformula, $_1_u_solmatitem_idtag;
	$i = $con;
	// while ($rowqr = mysqli_fetch_assoc($qr1)) 
	foreach($prodServESolMatItem as $item)
	{
		$atributoName = "_x{$i}_u_solmatitem";
		$excluir = "excluir({$item['idsolmatitem']})";

		if (!empty($_GET['idsolmatcp']) and $_acao == "i") 
		{
			$atributoName = "duplicar_{$i}";
			$excluir = "$('#_iten_" . $i . "').remove();";

			if (!empty($item['idprodserv'])) 
			{
				$excluir = "$('#_item_{$i}').remove();";
				// $it = "_item_" . $i;
			}
		}
		?>

	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="cabitem">
					<div class="col-xs-3">Descrição</div>
					<div class="col-xs-3">Colaborador</div>
					<div class="col-xs-3">Local Equipamento</div>
					<div class="col-xs-3">Observação</div>
				</div>
			</div>
		</div>
		<div class="panel-body" style="padding: 0px !important;">
			<div class="row" style="background:white;display:flex;align-items: center; height:40px">
				<input name="<?= $atributoName ?>_idsolmatitem" type="hidden" value="<?= $item['idsolmatitem'] ?>">
				<? 
					$_1_u_solmatitem_qtdc = $item['qtdc'];
					$_1_u_solmatitem_idtag = $item['idtag'];
				?>
				<input name="<?= $atributoName ?>_qtdc" type="hidden" value="<?= $item['qtdc'] ?>">
				<input name="<?= $atributoName ?>_un" type="hidden" value="<?= $item['un'] ?>">

				<!-- Descrição -->
				<div class="col-xs-3">
					<?
					if ($item['idprodserv']) {?>
						<input name="<?= $atributoName ?>_idprodserv" type="hidden" value="<?= $item['idprodserv'] ?>">
						<input name="<?= $atributoName ?>_descr" type="hidden" value="<?= $item['descr'] ?>">
						<a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $item['idprodserv'] ?>')" title="<?= $item['descr'] ?>"><span class="bold"><?= $item['descr'] ?></span></a>
						<?
					} else {
						echo $item['descr'];
					} ?>
				</div>

				<!-- Colaborador -->
				<div class="col-xs-3">
					<input name="<?= $atributoName ?>_descr" type="hidden" value="<?= $item['descr'] ?>">
					<a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $item['idprodserv'] ?>')" title="<?=$item['idpessoa'] ?>"><span class="bold"><?= $item['descr'] ?></span></a>
				</div>
				<?
				// Novo campo inserido para trazer fórmulas/processos de acordo com a descricao do produto selecionado - ID460480 - ALBT 27/04/2021
				if (!empty($item['idprodserv']) && $_1_u_solmat_tipo != "EQUIPAMENTOS") 
				{
					$fabricado = ProdServController::buscarPorChavePrimaria($item['idprodserv'])['fabricado'];
					if ($fabricado == 'Y')
					{?>
						<div class="col-xs-3">
							<input name="_pf_<?= $_acao ?>_solmatitem_idsolmatitem" id="idsolmatitem" type="hidden" style="width: 0%; text-align: center;" readonly='readonly' value="<?= $item['idsolmatitem'] ?>">
							<select name="_pf_<?= $_acao ?>_solmatitem_idprodservformula" value="<?= $_1_u_solmatitem_idprodservformula ?>" onchange="formula(this,<?= $i ?>)" <?= $readonly ?> <? if ($readonly) { ?>style="width: 149px; background-color: #f5f5f5;" <? } else { ?> style="width: 149px;" <? } ?>>
								<option value="">Local de utilização do equipamento</option>
								<? fillselect(							
									ProdservformulaController::buscarProdServFormulaPorIdProdServEStatus($item['idprodserv'], 'ATIVO', true), $item['idprodservformula']
								); ?>
							</select>
						</div>
					<? } else { ?>
						<div class="col-xs-1"> - </div>
					<? }
				} ?>
				<? if ($_GET['_modulo'] == 'soltag') { ?>
					<div class="col-xs-3">
						<select name="<?= $atributoName ?>_idtag" <?= $readonly ?>>
							<option value="">Selecione onde o equipamento será utilizado</option>
							<? fillselect(TagController::buscarTodasTags(true), $_1_u_solmatitem_idtag); ?>
						</select>
					</div>
				<? } ?>
				<div class="col-xs-3" style="display:flex;align-items:center;">
					<input name="<?= $atributoName ?>_obs" <?= $readonly ?> value="<?= $item['obs'] ?>" placeholder="Observação:">
					<? if ($_1_u_solmat_status == 'ABERTO' || !empty($_GET['idsolmatcp'])) { ?>				
						<a class="btn btn-link"><i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable w" onclick="<?= $excluir ?>" alt="Excluir!"></i></a>
					<? } else {  ?>
						<a class="btn btn-link expandir" title="Expandir" data-toggle="collapse" idnfitem="<?= $item['idsolmatitem'] ?>" href="#solmatitem<?= $item['idsolmatitem'] ?>">
							<i class="fa fa-arrows-v fa-1x cinzaclaro hoververmelho pointer ui-droppable w" alt="Expandir"></i>
						</a>
					<? } ?>
				</div>
			</div>

			<?
			if (!empty($item['idprodserv'])) { // lista intens da solmatitem
				if ($_1_u_solmat_status == 'EXECUCAO' || $_1_u_solmat_status == 'CONCLUIDO' || $_1_u_solmat_status == 'CANCELADO') {
					listarTags($item['idsolmatitem'], $item['idprodserv'], $item['qtdc']);
				}
			}
			?>
		</div>
	</div>
	<?
		$i++;
	}
}

function listarComentarios()
{
	global $_1_u_solmat_idsolmat;
	$comentarios = SolmatController::buscarComentariosPorIdSolMat($_1_u_solmat_idsolmat);
	?>
	<div hidden>
		<div id="comentarioopoup">
			<table class="w-100">
				<tbody>
					<tr>
						<td>Comentário:</td>
					</tr>
					<tr>
						<td>
							<input type="hidden" id="_99_i_modulocom_idmodulo" name="_99_i_modulocom_idmodulo" value="<?= $_1_u_solmat_idsolmat ?>" />
							<input type="hidden" id="_99_i_modulocom_modulo" name="_99_i_modulocom_modulo" value="solmat" />
							<textarea rows="3" onkeyup="atualizaCampo(this)" style="width: 100%; height: 80px;" name="_99_i_modulocom_descricao" id="_99_i_modulocom_descricao"></textarea>
						</td>
					</tr>
					<tr>
						<td>
							<table class="table table-striped planilha display">
								<tbody>
									<?
									if (count($comentarios))
									{
										// while ($rowc = mysqli_fetch_assoc($qrco)) 
										foreach($comentarios as $comentario)
										{ ?>
											<tr>
												<td style="line-height: 14px; padding: 8px; color:#666;">
													<div>
														<div style="margin-left: 1px; word-break: break-word;line-height: 14px; padding: 8px; font-size: 11px;color:#666;">
															<?= dmahms($comentario['criadoem']) ?> - <?= $comentario['criadopor'] ?>: <?= nl2br($comentario['descricao']) ?>
														</div>
													</div>
												</td>
											</tr>
										<?}
									} ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?
}

function listarTags($idsolmatitem, $idprodserv, $con)
{
	global 	$_1_u_solmat_status, 
			$_1_u_solmatitem_qtdc, 
			$_1_u_solmat_unidade, 
			$_1_u_solmatitem_idtag;
	$i = 1;
	?>
	<div class="collapse in" id="solmatitem<?= $idsolmatitem ?>">
		<table class="table table-striped planilha">
			<tr class="cabitem" style="height:40px;border-top: 1px solid #d9d9d9;">
				<th class="cp_qtd" title="Transferir TAG">Transferir</th>
				<th class="cp_un" title="TAG">Tag</th>
				<th class="cp_descricao">Descrição</th>
				<th>Local equipamento</th>
				<th>Unidade</th>
				<th>Info</th>
				<th>Status</th>
			</tr>
			<?
			$tagsVinculadas = SolTagController::buscarTagsVinculadasPorIdSolMatItem($idsolmatitem);
			$numTagsAtivadas = count($tagsVinculadas);
			if ($numTagsAtivadas) 
			{
				foreach($tagsVinculadas as $tag)
				{?>
					<tr class="" style="background:#c5e0b4;">
						<td class="cp_qtd">
							<input type="hidden" id="<?= $tag['idsolmatitemobj'] ?>_idtagpaianterior" value="<?= $tag['idtagpaianterior'] ?>">
							<input type="hidden" id="<?= $tag['idsolmatitemobj'] ?>_idunidadeanterior" value="<?= $tag['idunidadeanterior'] ?>">
							<? if ($_1_u_solmat_status == 'EXECUCAO' || $_1_u_solmat_status == 'DIVERGENCIA') { ?>
								<input type="checkbox" value="<?= $tag['descricao'] ?>" checked onclick="retornarTag(<?= $tag['idtag'] ?>,<?= $idsolmatitem; ?>,<?= $tag['idsolmatitemobj']; ?>)" />
							<? } else { ?>
								<input type="checkbox" value="<?= $tag['descricao'] ?>" checked disabled readonly />
							<? } ?>
						</td>
						<td>
							<label class="alert-warning tag"><?= $tag['tag'] ?>
								<a title="Abrir TAG" class="fa fa-bars fade pointer hoverazul right" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $tag['idtag'] ?>')" ; style="margin: 0 4px;"></a>
							</label>
						</td>
						<td>
							<input type="hidden" value="<?= $tag['idtag'] ?>">
							<input type="hidden" value="<?= $tag['descricao'] ?>">
							<?= $tag['descricao'] ?>
						</td>
						<td>
							<?
							$sala = TagController::buscarTagPaiPeloIdTagFilho($tag['idtag']);
							if ($sala)
							{
								echo $sala['descricao'];
							}
							?>
						</td>
						<td>
							<?= ($rotun = traduzid('unidade', 'idunidade', 'unidade', $tag['idunidade'])) ? '<label class="alert-warning">' . $rotun . '</label>' : '' ?>
						</td>
						<td>
							<i class="fa fa-info" title="<?= $tag['tagclass'] ?>,<?= $tag['tagtipo'] ?>,<?= $tag['fabricante'] ?>,<?= $tag['modelo'] ?>"></i>
						</td>
						<td><label class="alert-warning"><?= $tag['status'] ?></td>
						</td>
					</tr>

				<? }
			} else if ($numTagsAtivadas == 0 && $_1_u_solmat_status == 'CONCLUIDO') 
			{?>
				<tr style="background-color: #FFF47A;height:40px !important">
					<td colspan="9" class="text-center">
						Nada foi transferido.
					</td>
				</tr>
				<?
			}
			//trazer as tags disponiveis para transferencia
			if ($_1_u_solmat_status == 'EXECUCAO') 
			{
				$idtagtipo=traduzid('prodserv','idprodserv','idtagtipo',$idprodserv);
				$tagsEmEstoque = TagController::buscarTagsEmEstoquePorIdUnidadeIdProdServEIdTagTipo($_1_u_solmat_unidade, $idprodserv, $idtagtipo);

				if (count($tagsEmEstoque))
				{
					// while ($tag = mysqli_fetch_assoc($tags)) 
					foreach($tagsEmEstoque as $key => $tag)
					{ 
						$atributoName = "_x{$i}_u_tag";
						?>
						<tr class="tag">
							<td class="cp_qtd">
								<? if (intval($numTagsAtivadas) < intval($_1_u_solmatitem_qtdc)) { ?>
									<input type="checkbox" onclick="transfereTag(<?= $tag['idempresa'] ?>, <?= $tag['idtag'] ?>, <?= $idsolmatitem; ?>, <?= $tag['idunidade'] ?>, <?= $_1_u_solmatitem_idtag ?>)" />
								<? } else { ?>
									<input type="checkbox" onclick="alert('A quantidade da solicitação já foi alcançada.');event.preventDefault();return" disabled readonly />
								<? } ?>
							</td>
							<td>
								<label class="alert-warning tag"> <?= $tag['tag'] ?>
									<a title="Abrir TAG" class="fa fa-bars fade pointer hoverazul right" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $tag['idtag'] ?>')" ; style="margin: 0 4px;"></a>
								</label>
							</td>
							<td class="prodserv-descr">
								<input name="<?= $atributoName ?>_idtag" type="hidden" value="<?= $tag['idtag'] ?>">
								<input name="<?= $atributoName ?>_descr" type="hidden" value="<?= $tag['descricao'] ?>">
								<?= $tag['descricao'] ?>
							</td>
							<td>
								<?
								$sala = TagController::buscarTagPaiPeloIdTagFilho($tag['idtag']);
								if ($sala) {
									echo $sala['descricao'];
								}
								?>
							</td>
							<td>
								<?= ($rotun = traduzid('unidade', 'idunidade', 'unidade', $tag['idunidade'])) ? '<label class="alert-warning">' . $rotun . '</label>' : '' ?>
							</td>
							<td>
								<i class="fa fa-info" title="
										<?= $tag['tagclass'] ?>,\n<?= $tag['tagtipo'] ?>,\n<?= $tag['fabricante'] ?>,\n<?= $tag['modelo'] ?>">
								</i>
							</td>
							<td><label class="alert-warning"><?= $tag['status'] ?></td>
							</td>
						</tr>
					<?
						$i++;
					}
				} else {
					?>
					<tr>
						<td></td>
						<td colspan="7" style="height: 40px;"><label class="alert-warning">Nenhuma TAG disponível nesta empresa</label></td>
					</tr>
					<?
				}
				if (array_key_exists("soltagmaster", getModsUsr("MODULOS")) == 1) {
					$tagsEmEstoque2 = TagController::buscarTagsEmEstoquePorIdUnidadeIdProdServEIdTagTipo($_1_u_solmat_unidade, $idprodserv, $idtagtipo, true);
					if (count($tagsEmEstoque2)) {
						foreach($tagsEmEstoque2 as $tag)
						{ 
							$atributoName = "_x{$i}_u_tag";
							?>
							<tr class="tag">
								<td></td>
								<td>
									<label class="alert-warning tag"> <?= $tag['tag'] ?>
										<a title="Abrir TAG" class="fa fa-bars fade pointer right" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $tag['idtag'] ?>')" ; style="margin: 0 4px;"></a>
									</label>
								</td>
								<td class="prodserv-descr">
									<input name="<?= $atributoName ?>_idtag" type="hidden" value="<?= $tag['idtag'] ?>">
									<input name="<?= $atributoName ?>_descr" type="hidden" value="<?= $tag['descricao'] ?>">
									<?= $tag['descricao'] ?>
								</td>
								<td>
									<?
									$sala = TagController::buscarTagPaiPeloIdTagFilho($tag['idtag']);
									if (count($sala)) {
										echo $sala['descricao'];
									}
									?>
								</td>
								<td>
									<?= ($rotun = traduzid('unidade', 'idunidade', 'unidade', $tag['idunidade'])) ? '<label class="alert-warning">' . $rotun . '</label>' : '' ?>
								</td>
								<td>
									<i class="fa fa-info" title="<?= $tag['tagclass'] ?>,<?= $tag['tagtipo'] ?>,<?= $tag['fabricante'] ?>,<?= $tag['modelo'] ?>"></i>
								</td>
								<td><label class="alert-warning"><?= $tag['status'] ?></td>
								</td>
							</tr>
						<? $i++;
						}
					} else
					{ ?>
						<tr>
							<td></td>
							<td colspan="7" style="height: 40px;"><label class="alert-warning">Nenhuma TAG disponível em outras empresas</label></td>
						</tr>
					<?}
				}
			}
			?>
		</table>
	</div>
	<?
}
$tabaud = "solmat";
require 'viewCriadoAlterado.php';
require_once(__DIR__."/../form/js/soltag_js.php");
?>
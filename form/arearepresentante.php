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
$pagvaltabela = "arearepresentante";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idarearepresentante" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from " . _DBAPP . ".arearepresentante where idarearepresentante = '#pkid' ";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__."/controllers/arearepresentante_controller.php");
require_once(__DIR__."/controllers/pessoa_controller.php");

$representantes  = [];

if($_1_u_arearepresentante_idarearepresentante) {
	$representantes = AreaRepresentanteController::buscarGestoresERepresentantes($_1_u_arearepresentante_idarearepresentante, cb::idempresa());
	$representantesDisponiveisParaVinculo = PessoaController::buscarPessoasPorIdTipoPessoa('1,12',cb::idempresa(), true);
}

?>
<link href="/form/css/sgsetor_css.css?_<?=date("dmYhms")?>" rel="stylesheet" />

<div class="col-xs-12 px-0">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="w-100 d-flex flex-wrap flex-between">
				<!-- ID -->
				<? if($_1_u_arearepresentante_idarearepresentante)
				{?>
					<input name="_1_<?= $_acao ?>_arearepresentante_idarearepresentante" type="hidden" value="<?= $_1_u_arearepresentante_idarearepresentante ?>" readonly='readonly'>
					<div class="col-xs-6 col-sm-2 form-group">
						<label for="" class="text-white">ID</label>
						<div class="form-control alert-warning">
							<label for="">
								<?= $_1_u_arearepresentante_idarearepresentante ?>
							</label>
						</div>
					</div>
				<?}?>
				<!-- Área -->
				<div class="col-xs-6 col-sm-3 form-group">
					<label for="" class="text-white">Área:</label>
					<input class="form-control" name="_1_<?= $_acao ?>_arearepresentante_area" type="text" value="<?= $_1_u_arearepresentante_area ?>" vnulo>
				</div>
				<!-- Classificaçao -->
				<div class="col-xs-6 col-sm-3 form-group">
					<label for="" class="text-white">Classificação:</label>
					<input class="form-control" name="_1_<?= $_acao ?>_arearepresentante_classificacao" type="text" value="<?= $_1_u_arearepresentante_classificacao ?>" vnulo>
				</div>
				<!-- STATUS -->
				<div class="col-xs-6 col-sm-2 form-group">
					<label for="" class="text-white">Status</label>
					<select id="status" name="_1_<?= $_acao ?>_arearepresentante_status" class="form-control" vnulo>
						<? fillselect(AreaRepresentanteController::$status, $_1_u_arearepresentante_status); ?>
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<? foreach($representantes as $idResponsavel => $representante) { ?>
			<div class="col-xs-12 col-sm-3">
				<div class="panel panel-default">
					<div class="panel-heading d-flex flex-between w-100 align-items-center">
						<!-- <h5><?= $representante['responsavel'] ?></h5> -->
						<select id="responsavel" class="w-100 selectpicker" onchange="atualizaContato(<?= $representante['idpessoacontato'] ?>, this)" data-search>
							<?= fillselect($representantesDisponiveisParaVinculo, $idResponsavel) ?>
						</select>
						<a target="_blank" class="fa fa-bars hoverazul pointer col-xs-2" href="?_modulo=pessoa&_acao=u&idpessoa=<?= $idResponsavel ?>"></a>
						<!-- <span class="fa fa-trash pointer" onclick="removeVinculo(<?= $representante['idpessoaobjeto'] ?>)"></span> -->
					</div>
					<? if ($_1_u_arearepresentante_idarearepresentante) { ?>
						<div class="panel-body">
							<!-- <div class="row">
								<div class="col-xs-12 px-0">
									<h5>Representante Ademilson</h5>
								</div>
							</div> -->
							<table class="table-hover w-100">
								<thead>
									<tr>
										<th>Cliente</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
									<? foreach($representante['clientes'] as $cliente) { ?>
										<tr>
											<td><?= $cliente['cliente'] ?></td>
											<td><a target="_blank" href="/?_modulo=pessoa&_acao=u&_idempresa=2&idpessoa=<?= $cliente['idcliente'] ?>" class="fa fa-bars pointer hoverazul"></a></td>
										</tr>
									<?}?>
								</tbody>
							</table>
							<!-- <div class="d-flex flex-wrap w-100 mt-3">
								Adicionar cliente
								<div class="col-xs-12 col-sm-6 form-group px-0">
									<label>Adicionar cliente</label>
									<div class="d-flex flex-wrap align-items-center w-100">
										<div class="col-xs-11 py-0 px-0">
											<input id="clientes" class="compacto" <? if ($_1_u_arearepresentante_status == 'INATIVO') { ?> disabled <? } else { ?> type="text" cbvalue placeholder="Selecione" <? } ?>>
										</div>
										<div class="w-100 overflow-x-auto">
											<table class="table-hover w-100">
												<tbody>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div> -->
						</div>
					<? } ?>
				</div>
			</div>
		<?}?>
	</div>
</div>
<?

$tabaud = "arearepresentante"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once __DIR__.'/js/arearepresentante_js.php';
?>
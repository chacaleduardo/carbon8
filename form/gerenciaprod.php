<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/gerenciaprod_controller.php");
if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$idpessoa = $_GET["idpessoa"];
$idprodserv = $_GET["idprodserv"];
$status = $_GET["status"];
$idplantel = $_GET['idplantel'];
$validacao = $_GET['validacao'];

//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli = GerenciaProdController::listarPessoaVinculadaLote();

//Recupera os produtos a serem selecionados para uma nova Formalização
$arrProd = GerenciaProdController::buscarProdservVinculadoAoLote();

?>
<link href="../form/css/gerenciaprod_css.css?_<?= date("dmYhms") ?>" rel="stylesheet">
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Pesquisar </div>
			<div class="panel-body">
				<div class="row sem_margin">
					<div class="form-group col-xs-6 col-md-6 ml15">
						<label>Cliente:</label>
						<br />
						<input id="idpessoa" type="text" name="idpessoa" cbvalue="<?= $idpessoa ?>" value="<?= $arrCli[$idpessoa]["nome"] ?>" style="width: 50em;" vnulo>
					</div>
					<div class="form-group col-xs-4 col-md-4 ml15">
						<label>Status:</label>
						<br />
						<select class='size10' name="status" id="status">
							<? fillselect("select 'ATIVO','Ativo' union select 'TODAS','Todas'", $status); ?>
						</select>
					</div>
				</div>
				<div class="row sem_margin">
				<div class="form-group col-xs-6 col-md-6 ml15">
						<label>Produto:</label>
						<br />
						<input id="idprodserv" type="text" name="idprodserv" cbvalue="<?= $idprodserv ?>" value="<?= $arrProd[$idprodserv]["descr"] ?>" style="width: 50em;" vnulo>
					</div>
					<div class="form-group col-xs-2 col-md-2 ml15">
						<label>Tipo/Especie:</label>
						<br />
						<select class='size10' name="idplantel" id="idplantel">
							<option value=""></option>
							<? fillselect("select idplantel,plantel from plantel where status='ATIVO' and idplantel in(3,2,4,6) order by plantel", $idplantel); ?>
						</select>
					</div>
					<div class="form-group col-xs-2 col-md-2 ml15">
						<label>Validação:</label>
						<br />
						<select class='size10' name="validacao" id="validacao">
							<option value=""></option>
							<? fillselect("select 'O','Validado' union select 'V','Pendente'  union select 'I','Inativo'", $validacao); ?>
						</select>
					</div>
				</div>				
				<div class="row">
					<div class="col-md-9"></div>
					<div class="col-md-1 nowrap">
						<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
							<span class="fa fa-search"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?
require_once('../form/js/gerenciaprod_js.php');
?>
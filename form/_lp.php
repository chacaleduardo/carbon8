<?
require_once("../inc/php/validaacesso.php");

require_once(__DIR__."/controllers/_lp_controller.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}
//Parámetros mandatários para o carbon
$pagvaltabela = "_lpgrupo";
$pagvalcampos = array(
	"idlpgrupo" => "pk"
);

//Select que inicializa as variáveis que preenchem os campos da tela em caso de update
$pagsql = "select * from "._DBCARBON."._lpgrupo where idlpgrupo = '#pkid'";

//controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e criacao das variáveis 'variáveis' para a página
require_once("../inc/php/controlevariaveisgetpost.php");
?>
<link rel="stylesheet" href="<?= "form/css/_lp_css.css"?>" />
<div class="row">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
			<div class="panel-heading">
					<table>
					<tr>
						<td align="right">LP:</td>
						<td >
							<input type="hidden" name="_1_<?=$_acao?>__lpgrupo_idlpgrupo"  value="<?=$_1_u__lpgrupo_idlpgrupo?>" >
							<input class="size20" type="text" name="_1_<?=$_acao?>__lpgrupo_descricao"  value="<?=$_1_u__lpgrupo_descricao?>" vnulo>                                       
						</td>
						<td align="right" >Status:</td>
						<td>
							<select name="_1_<?=$_acao?>__lpgrupo_status" onchange="inativagrupo('grupo',this)" class="size8" >
								<!--<?=$_1_u__lpgrupo_status;?> -->
								<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u__lpgrupo_status);?>
							</select>
						</td>
					</tr>
				

					</table>
				</div>
				<div class="panel-body">
					<table style="width:100%">            
						<tr>
							<td>
								<table style="width: 100%;">
									<tr><td>Observação:</td></tr>
									<tr>
										<td>
											<textarea name="_1_<?=$_acao?>__lpgrupo_observacao" rows="4" ><?=$_1_u__lpgrupo_observacao?></textarea>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
<?if(!empty($_1_u__lpgrupo_idlpgrupo)){?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel-default" id="mainPanel">
				<ul class="nav nav-tabs panel border-0 mb-0 d-flex items-end bg-transparent flex-wrap" id="Tab_lp" role="tablist">
					<?
					$rs = _LpController::buscarGruposPorLpgrupopar($_1_u__lpgrupo_idlpgrupo);
					$arrLpGrupo = array();
					foreach ($rs as $k =>$row) {
						$arrLpGrupo[$row['idlpgrupo']] = $row;
						?>
						<li role="presentation panel-heading" ondblclick="editarNomeLp(this, <?=$row['idlpgrupo']?>,'<?=$row['descricao']?>','<?=$row['status']?>')">
							<a href="#lpgrupo_<?=$row['idlpgrupo']?>" class="<?=$row['status'] == 'ATIVO'?'':'cinzaclaro'?> bg-ccc border-0" tab="<?=$row['idlpgrupo']?>" role="tab" data-toggle="tab">
								<?=$row['descricao']?>
							</a>
						</li>
					<?}?>
					<li>
						<button onclick="showModalLp()" class="btn btn-success btn-sm w-100"><i class="fa fa-plus"></i>Adicionar LP</button>
					</li>
				</ul>
				<div class="tab-content bg-ccc">
				<?
				foreach ($arrLpGrupo as $idlpgrupo => $row) {?>
					<div role="tabpanel" class="tab-pane fade" role="tab" id="lpgrupo_<?=$idlpgrupo?>">
						<input type="hidden" id="lpgrupo_<?=$idlpgrupo?>_descricao" value="<?=$row['descricao']?>">
						<ul class="nav nav-pills panel mb-0 border-0 items-end d-flex bg-transparent flex-wrap">
							<?
							$rs = _LpController::buscarLPsPorIdLprupo($row["idlpgrupo"],$_SESSION['SESSAO']['IDPESSOA']);
							$arrLp = array();
							foreach($rs as $k1 => $rw){
								$arrLp[$rw['idlp']] = $rw;
								?>
								<li role="presentation" idlp="<?=$rw['idlp']?>" id="lpgrupo_<?=$idlpgrupo?>_<?=$rw['idempresa']?>" class="tab-content">
									<a href="#lp_<?=$rw['idlp']?>" class="<?=$rw['status'] == 'ATIVO'?'':'cinzaclaro'?> bg-ddd" tab="<?=$rw['idlp']?>" title="#<?=$rw['idlp']?>" role="tab" data-toggle="pill" onclick="carregaLP(this, <?=$rw['idlp']?>, <?=$row['idlpgrupo']?>)">
										<?=$rw['empresa']?>
									</a>
								</li>
							<?}?>
							<li>
								<button onclick="showModalEmpresa(<?=$idlpgrupo?>)" class="btn btn-success btn-sm"><i class="fa fa-plus"></i>Adicionar Empresa</button>
							</li>
						</ul>
						<div class="tab-content panel-body bg-ddd">
							<?foreach ($arrLp as $idlp => $row) {?>
								<div class="tab-pane fade w-100 init" idempresa="<?=$row["idempresa"]?>" id="lp_<?=$idlp?>" role="tabpanel" aria-labelledby="pills-home-tab"></div>
							<?}
							unset($arrLp);
							?>
						</div>
					</div>
				<?}?>
				</div>
				<div id="circularProgressIndicator" style="display: none;"></div>
			</div>
		</div>
	</div>
<?}?>
<?
$tabaud = "_lpgrupo"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once(__DIR__."/js/_lp_js.php");
?>

<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/tipoamostra_controller.php");
if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "subtipoamostra";
$pagvalcampos = array(
	"idsubtipoamostra" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from subtipoamostra where idsubtipoamostra = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>

<div class="row">
    <div class="col-md-12" >
		<div class="panel panel-default" >
			<div class="panel-heading">Tipo de Amostra</div>
			<div class="panel-body"> 
				<table>
					<tr>
						<td></td>
						<td><input id="idsubtipoamostra" name="_1_<?=$_acao?>_subtipoamostra_idsubtipoamostra" type="hidden" readonly value="<?=$_1_u_subtipoamostra_idsubtipoamostra?>" ></td>
					</tr>
					<tr>
						<td>Descrição:</td>
						<td><input name="_1_<?=$_acao?>_subtipoamostra_subtipoamostra" type="text" id="tipoamostra" vnulo value="<?=$_1_u_subtipoamostra_subtipoamostra?>" ></td>
					</tr>
					<tr>
						<td>Status:</td>
						<td>
							<select name="_1_<?=$_acao?>_subtipoamostra_status">
								<?fillselect("select 'ATIVO', 'ATIVO' union select 'INATIVO','INATIVO'",$_1_u_subtipoamostra_status);?>
							</select>
						</td>
					</tr>             
					<tr>
						<td>Exige Conferência:</td>
						<td>
							<select name="_1_<?=$_acao?>_subtipoamostra_conferencia">
								<?fillselect("SELECT 'N', 'Não' union select 'S','Sim'",$_1_u_subtipoamostra_conferencia);?>
							</select>
						</td>
					</tr>             
				</table>
				<table>
					<?if(!empty($_1_u_subtipoamostra_idsubtipoamostra)){
						$resu = TipoAmostraController::buscarUnidadesPorTipoAmostra('subtipoamostra',$_1_u_subtipoamostra_idsubtipoamostra,getidempresa('u.idempresa','unidade'));?>
							<tr>
								<td  colspan="5">
									<div class="panel panel-default"> 
										<?foreach ($resu as $k => $rowu){
											if(!empty($rowu['idunidadeobjeto'])){?>                   
												<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="retiraund(<?=$rowu['idunidadeobjeto']?>);" alt="Retirar Unidade">&nbsp;&nbsp;<?echo($rowu['unidade']);?></i>
											<?}else{?>                    
												<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer"  onclick="inseriund(<?=$rowu['idunidade']?>);" alt="Inserir Unidade">&nbsp;&nbsp;<?echo($rowu['unidade']);?></i>
											<?}
										}?>		
									</div>
								</td>	
							</tr>
					<?}?>
				</table>
			</div>
		</div>
    </div>
</div>

<?

if(!empty($_1_u_subtipoamostra_idsubtipoamostra)){ 
	$res =TipoAmostraController::buscarAmotraCamposPorTipoAmostra(cb::idempresa(),$_1_u_subtipoamostra_idsubtipoamostra); 
	$qtdrows= count($res);?>
	<div class="row">
		<div class="col-md-12" >
			<div class="panel panel-default" >
				<div class="panel-heading">Campos da Amostra</div>
				<div class="panel-body"> 
					<table class="table table-striped planilha"> 
						<tr class="header">
							<th>Campo</th>
							<th>Unidade</th>
							<th>Retirar</th>
						</tr>
						<?$i=9999;
						foreach($res as $k => $row){
							$i=$i+1;?>	
							<tr class="respreto">
								<td><?=strtoupper($row["campo"])?></td>
								<td>
									<input  name="_<?=$i?>_u_amostracampos_idamostracampos" type="hidden" readonly value="<?=$row['idamostracampos']?>" >
									<select class="size15" name="_<?=$i?>_u_amostracampos_idunidade" vnulo>
										<option></option>
											<?fillselect(TipoAmostraController::toFillSelect(TipoAmostraController::buscarUnidadesPorTipoAmostra('subtipoamostra',$_1_u_subtipoamostra_idsubtipoamostra,getidempresa('u.idempresa','unidade'))),$row['idunidade']);?>
									</select>
								</td>
								<td >
									<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="CB.post({objetos:'_ajax_d_amostracampos_idamostracampos=<?=$row["idamostracampos"]?>'})" title="Excluir"></i>
								</td>				
							</tr>
						<?}?>		
						<tr>
							<td >
								<select  class="size30" name="amostracampos_campo"  onchange="inserircampo(this,<?=$_1_u_subtipoamostra_idsubtipoamostra?>,'campo');" >
									<option value="">Inserir novo Campo</option>
									<?fillselect(TipoAmostraController::toFillSelect(TipoAmostraController::buscarTodosOsCamposDeUmaEmpresa('1')));?>
								</select>
							</td>
							<td>
								<select class="size15" name="amostracampos_idunidade" onchange="inserircampo(this,<?=$_1_u_subtipoamostra_idsubtipoamostra?>,'idunidade');">
								<option value="">Inserir Unidade</option>
									<?fillselect(TipoAmostraController::toFillSelect(TipoAmostraController::buscarUnidadesPorTipoAmostra('subtipoamostra',$_1_u_subtipoamostra_idsubtipoamostra,getidempresa('u.idempresa','unidade'))));?>
								</select>
							</td>
							<td></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
<?}?>
<?
if(!empty($_1_u_subtipoamostra_idsubtipoamostra)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_subtipoamostra_idsubtipoamostra; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "subtipoamostra"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';

	require_once(__DIR__."/js/tipoamostra_js.php");
?>

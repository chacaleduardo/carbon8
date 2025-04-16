<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "_droplet";
$pagvalcampos = array(
	"iddroplet" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from ". getDbTabela("_droplet")."._droplet where iddroplet = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<input type="hidden" name="_1_<?=$_acao?>__droplet_iddroplet" value="<?=$_1_u__rep_iddroplet?>">
		Id: <label class='alert-warning'><?=$_1_u__droplet_iddroplet?></label>
		<label>Descr:</label><input type="text" name="_1_<?=$_acao?>__droplet_descr" class="bold" vnulo="" value="<?=$_1_u__droplet_descr?>">
	</div>
	<div class="panel-body">
		<div class="col-md-4">
			<table>
			<tr>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td></td>
			</tr>
			</table>
		</div>
	</div>
</div>
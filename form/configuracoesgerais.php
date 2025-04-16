<?
require_once("../inc/php/validaacesso.php");

if($_POST) require_once("../inc/php/cbpost.php");

//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "_paraplweb";
$XXpagvalcampos = array(
	"idparaplweb" => "pk"
);

//Select que inicializa as variáveis que preenchem os campos da tela em caso de update
$pagsql = "select * from "._DBCARBON."._paraplweb where parametro = 'avisoTelaInicial'";

//Validacao do GET e criacao das variáveis 'variáveis' para a página
require_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="panel panel-default">
	<div class="panel-heading">Aviso Tela Inicial</div>
	<div class="panel-body">

		<table class="normal">
		<tr>
			<td>Status:</td>
			<td>
				<input type="hidden" name="_1_u__paraplweb_idparaplweb" value="<?=$_1_u__paraplweb_idparaplweb?>">
				<input type="hidden" name="_1_u__paraplweb_parametro" value="<?=$_1_u__paraplweb_parametro?>">
				<select name="_1_u__paraplweb_status">
	<?
		fillselect(array("A"=>"Ativo","I"=>"Inativo"),$_1_u__paraplweb_status);
	?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Conteúdo:</td>
			<td style="border:0px solid silver;">
				<textarea style="width:570px;height:170px;" name="_1_u__paraplweb_valor"><?=$_1_u__paraplweb_valor?></textarea>
			</td>
		</tr>
		</table>
		<br>
		<button class="btn btn-danger pull-right" onclick="CB.login()" style="margin-top:20px">
			<i class="fa fa-circle"></i>Salvar
		</button>
	</div>
</div>
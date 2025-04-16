<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "feriado";
$pagvalcampos = array(
	"idferiado" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from feriado where idferiado = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Feriado</div>
        <div class="panel-body">
		<table>
		<tr> 
			<td></td> 
			<td><input name="_1_<?=$_acao?>_feriado_idferiado" type="hidden" value="<?=$_1_u_feriado_idferiado?>" readonly='readonly'></td> 
		</tr>
		<tr> 
			<td align="right">Data:</td> 
			<td><input  class="calendario size8" size="8" name="_1_<?=$_acao?>_feriado_dataferiado" type="text" value="<?=$_1_u_feriado_dataferiado?>" vnulo></td> 
		</tr>
		<tr> 
			<td align="right">Descr.</td> 
			<td><textarea name="_1_<?=$_acao?>_feriado_obs"  style=" width: 433px; height: 35px;"><?=$_1_u_feriado_obs?></textarea></td> 
		</tr>
		<tr> 
			<td align="right">Status:</td> 
			<td>
                            <select class="size8" name="_1_<?=$_acao?>_feriado_status">
                                    <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_feriado_status);?>		
                            </select>						
			</td> 
		</tr>
		</table>		
        </div>
    </div>
    
    </div>
</div>
<p>
<?
if(!empty($_1_u_feriado_idferiado)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_feriado_idferiado; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "feriado"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>


<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}


$idpessoa=$_GET['idpessoa'];
$dmadataponto=$_GET['data'];
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "pontoobsdia";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idpontoobsdia" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from pontoobsdia where idpontoobsdia = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


if($_acao=="i"){
	if(empty($idpessoa) or empty($dmadataponto)){
		die("à necessário informar o dia e o funcionário para esta operação");
	}
	$_1_u_pontoobsdia_idpessoa=$idpessoa;
	$_1_u_pontoobsdia_data=dma($dmadataponto);
}


?>



<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Obs - Dia</div>
        <div class="panel-body">

<table>
<tr> 
	<td></td> 
	<td>
		<input 
			name="_1_<?=$_acao?>_pontoobsdia_idpontoobsdia" 
			type="hidden" 
			value="<?=$_1_u_pontoobsdia_idpontoobsdia?>" 
			readonly='readonly'					>
	</td> 
</tr>
<tr> 
	<td></td> 
	<td>
		<input 
			name="_1_<?=$_acao?>_pontoobsdia_idpessoa" 
			type="hidden" 
			value="<?=$_1_u_pontoobsdia_idpessoa?>" 
								>
	</td> 
</tr>
<tr> 
	<td>Data:</td> 
	<td>
		<input 
			name="_1_<?=$_acao?>_pontoobsdia_data" 
			type="text" class="calendario" readonly='readonly'	
			value="<?=$_1_u_pontoobsdia_data?>" 
								>
	</td> 
</tr>
<tr> 
	<td align="right">Obs.</td> 
	<td><textarea name="_1_<?=$_acao?>_pontoobsdia_obs"  	rows="3"  cols="40"  style=font-size:medium; ><?=$_1_u_pontoobsdia_obs?></textarea></td> 
</tr>

</table>
        </div>
    </div>
    </div>
</div>
<p>
    <?
if(!empty($_1_u_pontoobsdia_idpontoobsdia)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_pontoobsdia_idpontoobsdia; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "pontoobsdia"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "prodservforn";
$pagvalcampos = array(
	"idprodservforn" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from prodservforn where idprodservforn = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if(empty($_1_u_prodservforn_idprodserv) and !empty($_GET['idprodserv'])){
	$_1_u_prodservforn_idprodserv=$_GET['idprodserv'];
}

if(empty($_1_u_prodservforn_idpessoa) and !empty($_GET['idpessoa'])){
	$_1_u_prodservforn_idpessoa=$_GET['idpessoa'];
}


if(empty($_1_u_prodservforn_idprodservformula) and !empty($_GET['idprodservformula'])){
	$_1_u_prodservforn_idprodservformula=$_GET['idprodservformula'];
}

if(empty($_1_u_prodservforn_idpessoa) or empty($_1_u_prodservforn_idprodserv)){
	die("É necessario informar o cliente e o produto para o cadastro...");
}
?>
<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
    <div class="panel-heading">	
                    <table>
                    <tr>		
                        <td align="right"></td> 
                        <td>
                            <input	name="_1_<?=$_acao?>_prodservforn_idprodservforn" type="hidden"	value="<?=$_1_u_prodservforn_idprodservforn?>"	readonly='readonly'>
							<input	name="_1_<?=$_acao?>_prodservforn_idprodserv" type="hidden"	value="<?=$_1_u_prodservforn_idprodserv?>"	readonly='readonly'>
							<input	name="_1_<?=$_acao?>_prodservforn_idpessoa" type="hidden"	value="<?=$_1_u_prodservforn_idpessoa?>"	readonly='readonly'>
							<input	name="_1_<?=$_acao?>_prodservforn_idprodservformula" type="hidden"	value="<?=$_1_u_prodservforn_idprodservformula?>"	readonly='readonly'>
                        
						</td>
                        <td>Status:</td>
                        <td>
                        <?
                           /// if($_1_u_lote_statusao=='ABERTO'){$_1_u_lote_statusao='PENDENTE';}
                        ?>
                            <select   name="_1_<?=$_acao?>_prodservforn_status" >
                            <?fillselect("select 'ATIVO','Ativo'
                                union select 'INATIVO','Inativo'",$_1_u_prodservforn_status);?>		
                            </select>
                        </td>
                    </tr>

                    </table>
    </div>
    <div class="panel-body">
		<table>
			<tr>
                <td>Descr.</td>
                <td><textarea style="margin: 0px; width: 783px; height: 71px;" name="_1_<?=$_acao?>_prodservforn_obs"><?=$_1_u_prodservforn_obs?></textarea></td>
            </tr>
		</table>
	</div>
</div>
</div>
</div>
<?
if(!empty($_1_u_prodservforn_idprodservforn)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_prodservforn_idprodservforn; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "prodservforn"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
		
<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tabela principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "cartao";
$pagvalcampos = array(
	"idcartao" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from cartao where idcartao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
            <table>
                <tr>
                    <td>Cartão:</td>
                    <td>
                        
                        <input name="_1_<?=$_acao?>_cartao_idcartao"
                               type="hidden"
                               value="<?=$_1_u_cartao_idcartao?>"
                               >
							   
                         <input name="_1_<?=$_acao?>_cartao_cartao"
                               type="text"
                               value="<?=$_1_u_cartao_cartao?>"
                               >
                            <!-- input name="_1_<?=$_acao?>_cartao_valor"
                               type="text"
                               value="<?=$_1_u_cartao_valor?>"
                              -->
                         
                        
                    </td>
               
                    <td>Status:</td>
                    <td>
                       <select name="_1_<?=$_acao?>_cartao_status">
			<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_cartao_status);?>		
		    </select>
                    </td>
                </tr>
            </table>
            
        </div>   
            <div class="panel-body"> 
                
            <table>
                
                <tr>
                <td>Agência:</td>
                    <td>
                        <select name="_1_<?=$_acao?>_cartao_idagencia">
                        <option></option>
                            <?=getAgencia($idagencia, $_SESSION['SESSAO']['IDEMPRESA'])?>
                         </select>
                        
                    </td>
          </table>
        </div>
    </div>
  </div>


<?
if(!empty($_1_u_cartao_idcartao)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_cartao_idcartao; // trocar p/ cada tela o id da tabela
	require '../form/viewAssinaturas.php';
}
	$tabaud = "cartao"; //pegar a tabela do criado/alterado em antigo
	require '../form/viewCriadoAlterado.php';
?>
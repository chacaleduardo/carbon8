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
$pagvaltabela = "_status";
$pagvalcampos = array(
	"idstatus" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBCARBON.".$pagvaltabela where idstatus = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" >
            <div class="panel-heading">Status do Evento</div>
                <div class="panel-body">
                <table>
                    <tr> 
                        <td></td> 
                        <td><input name="_1_<?=$_acao?>__status_idstatus" type="hidden" value="<?=$_1_u__status_idstatus?>" readonly='readonly'></td> 
                    </tr>                    
                            <tr> 
                        <td align="right">Rótulo Usuário:</td> 
                        <td><input   name="_1_<?=$_acao?>__status_rotuloresp" type="text" value="<?=$_1_u__status_rotuloresp?>" vnulo></td> 
                    </tr>                

                    <tr> 
                        <td align="right">Rótulo Evento:</td> 
                        <td><input   name="_1_<?=$_acao?>__status_rotulo" type="text" value="<?=$_1_u__status_rotulo?>" vnulo></td> 
                    </tr>
                    <tr> 
                        <td align="right">Botão:</td> 
                        <td><input   name="_1_<?=$_acao?>__status_botao" type="text" value="<?=$_1_u__status_botao?>" vnulo></td> 
                    </tr>
                    <tr>
                        <td align="right">Cor:</td> 
                        <td>
                            <input name="_1_<?= $_acao ?>__status_cor" id="color" type="color" aria-label="..." value="<?=$_1_u__status_cor?>">
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Texto Cor:</td> 
                        <td>
                            <input name="_1_<?= $_acao ?>__status_cortexto"  id="color" type="color" aria-label="..." value="<?=$_1_u__status_cortexto?>">
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Status:</td> 
                        <td>
                            <select name="_1_<?=$_acao?>__status_status" >
                                <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u__status_status);?>		
                            </select>
                        </td> 
                    </tr>
                    <tr>
                        <td align="right">Tipo:</td> 
                        <td>
                            <select name="_1_<?=$_acao?>__status_statustipo" class="selectpicker" data-live-search="true">      
                                <option value=""></option>                 
                                <?fillselect("SELECT statustipo as id, descricao as valor FROM "._DBCARBON."._statustipo ORDER BY statustipo", $_1_u__status_statustipo);?>		
                            </select>
                        </td> 
                    </tr>
                    <tr>
                        <td align="right">Selecione Início/Fim:</td> 
                        <td>  
                            <select name="_1_<?=$_acao?>__status_tipobotao" >  
                                <option value=""></option>                  
                                <?fillselect("SELECT 'INICIO','Início' UNION SELECT 'FIM','Fim'",$_1_u__status_tipobotao);?>		
                            </select>
                        </td> 
                    </tr>
                </table>		
            </div>
        </div>    
    </div>
</div>
<?
if(!empty($_1_u__status_idstatus)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u__status_idstatus; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "_status"; //pegar a tabela do criado/alterado em antigo
	require __DIR__.'/js/eventostatus_js.php';
	require 'viewCriadoAlterado.php';
?>

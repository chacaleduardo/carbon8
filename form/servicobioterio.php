<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tabela principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "servicobioterio";
$pagvalcampos = array(
	"idservicobioterio" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from servicobioterio where idservicobioterio = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<div class="row ">
 <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
             <table>
                <td align="right" class="nowrap">Rótulo curto:</td>
                <td>
                    <input name="_1_<?=$_acao?>_servicobioterio_idservicobioterio" type="hidden"	value="<?=$_1_u_servicobioterio_idservicobioterio?>">
                    <input name="_1_<?=$_acao?>_servicobioterio_servico" type="text"	value="<?=$_1_u_servicobioterio_servico?>">
                </td>
                <td align="right">Amostra:</td>
                <td> 
                    <select name="_1_<?=$_acao?>_servicobioterio_idsubtipoamostra">
                        <option value=""></option>
                        <?fillselect("select t.idsubtipoamostra,t.subtipoamostra 
                                        from subtipoamostra t join unidadeobjeto o on (o.idunidade =2 and tipoobjeto='subtipoamostra' and o.idobjeto = t.idsubtipoamostra)
                                        where t.status='ATIVO' 
                                    order by subtipoamostra",$_1_u_servicobioterio_idsubtipoamostra);?>		
                    </select>
                </td>                       
                <td align="right">Status:</td>
                <td>
                    <select   name="_1_<?=$_acao?>_servicobioterio_status">
                        <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo' ",$_1_u_servicobioterio_status);?>		
                    </select>
                </td>
            </table>
        </div>
        <div class="panel-body">
        <table>
            <tr>
                <td align="right">Serviço:</td>
                <td>
                    <input name="_1_<?=$_acao?>_servicobioterio_rotulo" type="text" value="<?=$_1_u_servicobioterio_rotulo?>">
                </td>    
            </tr>
            <tr>
                <td align="right">Padrão:</td>
                <td> 
                    <select class="size5" name="_1_<?=$_acao?>_servicobioterio_padrao">
                        <?fillselect(" select 'N','Não' union  select 'S','Sim' ",$_1_u_servicobioterio_padrao);?>		
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right" class="nowrap">Sai na Etiqueta:</td>
                <td> 
                    <select class="size5" name="_1_<?=$_acao?>_servicobioterio_impetiqueta">
                        <?fillselect(" select 'N','Não' union  select 'S','Sim' ",$_1_u_servicobioterio_impetiqueta);?>		
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Procedimento:</td>
                <td colspan="5"><textarea name="_1_<?=$_acao?>_servicobioterio_procedimento" style="width: 600px; height: 50px;"><?=$_1_u_servicobioterio_procedimento?></textarea></td>
            </tr>
        </table>
        </div>
    </div>
 </div>
</div>
<?
if(!empty($_1_u_servicobioterio_idservicobioterio)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_servicobioterio_idservicobioterio; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "servicobioterio"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
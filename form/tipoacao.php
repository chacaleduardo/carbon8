<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "tipoacao";
$pagvalcampos = array(
	"idtipoacao" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".tipoacao where idtipoacao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
?>
<script type="text/javascript" src="../inc/js/jscolor/jscolor.js"></script>

<div class="row">
    <div class="col-md-8" >
    <div class="panel panel-default" >
        <div class="panel-heading">Tipo de Ação</div>
        <div class="panel-body">
        <table>
	<tr> 
            <td></td> 
            <td><input name="_1_<?=$_acao?>_tipoacao_idtipoacao" type="hidden"value="<?=$_1_u_tipoacao_idtipoacao?>"	readonly='readonly'></td> 
	</tr>
	<tr> 
            <td align="right">Ação:</td> 
            <td><input name="_1_<?=$_acao?>_tipoacao_tipoacao" type="text" size="70"	value="<?=$_1_u_tipoacao_tipoacao?>"></td> 
	</tr>
	<tr> 
            <td class="nowrap" align="right">Tipo Campo:</td> 
            <td>
                <select name="_1_<?=$_acao?>_tipoacao_idcadtipoacao" vnulo>
                    <option value=""></option>
                    <?fillselect("SELECT idcadtipoacao,acao FROM cadtipoacao where status= 'ATIVO'",$_1_u_tipoacao_idcadtipoacao)?>
		</select>	
            </td>		
	</tr>
	<tr> 
            <td align="right">Unidade:</td> 
            <td><input name="_1_<?=$_acao?>_tipoacao_unidade"  size="2" type="text" value="<?=$_1_u_tipoacao_unidade?>"></td> 
	</tr>
        <!--
	<tr> 
            <td>Monitorável:</td> 
            <td>
                <select name="_1_<?=$_acao?>_tipoacao_agendavel">
                        <?fillselect("select 'SIM','Sim' union select 'NAO','Não'",$_1_u_tipoacao_agendavel);?>		
                </select>
            </td> 
	</tr>	
        -->
	<tr> 
            <td class="nowrap" align="right">Vinculado á:</td> 
            <td>
                <select name="_1_<?=$_acao?>_tipoacao_vinculo">
                    <?fillselect("select 'EQUIPAMENTO','TAG' union	select 'SGDOC','Documento' union	select 'PESSOA','Fornecedor'",$_1_u_tipoacao_vinculo);?>
                </select>
            </td> 
	</tr>
        <!--
	<tr> 
            <td>Contr. Eficácia</td> 
            <td>
                <select name="_1_<?=$_acao?>_tipoacao_eficacia">
                        <?fillselect("select 'N','Não' union select 'S','Sim'",$_1_u_tipoacao_eficacia);?>		
                </select>
            </td> 
	</tr>
        -->
        <tr> 
            <td class="nowrap" align="right">Gera Tarefa?</td> 
            <td>
                <select name="_1_<?=$_acao?>_tipoacao_geraimmsg">
                    <?fillselect("select 'N','Não' union select 'Y','Sim'",$_1_u_tipoacao_geraimmsg);?>		
                </select>
            </td> 
	</tr>
        <tr>
            <td align="right">Cor:</td>
            <td>
                <input name="_1_<?=$_acao?>_tipoacao_cor" type="text" value="<?=$_1_u_tipoacao_cor?>" size="6"  class="color" style="cursor:pointer;" >
            </td>
        </tr>
	<tr> 
            <td align="right">Status:</td> 
            <td>
                <select name="_1_<?=$_acao?>_tipoacao_status">
                    <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_tipoacao_status);?>		
                </select>
            </td> 		 
	</tr> 
        </table>
        </div>
    </div>
    </div>
</div>
<?
if(!empty($_1_u_tipoacao_idtipoacao)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_tipoacao_idtipoacao; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "tipoacao"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
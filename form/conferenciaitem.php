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
$pagvaltabela = "conferenciaitem";
$pagvalcampos = array(
	"idconferenciaitem" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from conferenciaitem where idconferenciaitem = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>
                        <input name="_1_<?=$_acao?>_conferenciaitem_idconferenciaitem" type="hidden" value="<?=$_1_u_conferenciaitem_idconferenciaitem?>" readonly='readonly'>
                        <?
                        if(!empty($_1_u_conferenciaitem_idconferenciaitem)){
                            $tiposnf = array("danfe"=>["Danfe",$_1_u_conferenciaitem_danfe],
                                            "servico"=>["Serviço",$_1_u_conferenciaitem_servico],
                                            "cte"=>["CTe",$_1_u_conferenciaitem_cte],
                                            "concessionaria"=>["Concessionária",$_1_u_conferenciaitem_concessionaria],
                                            "manualcupom"=>["Manual/Cupom",$_1_u_conferenciaitem_manualcupom],
                                            "recibo"=>["Recibo",$_1_u_conferenciaitem_recibo],
                                            "fatura"=>["Fatura",$_1_u_conferenciaitem_fatura],
											"socios"=>["Sócios",$_1_u_conferenciaitem_socios],
											"rh"=>["RH",$_1_u_conferenciaitem_rh]);
                            foreach ($tiposnf as $key => $tiponf) {?>
                                <td>
                                    <?=$tiponf[0]?>
                                </td>
                                <td style='padding-right: 25px;'>
                                    <input type='checkbox' <?if($tiponf[1]=="Y"){?> onclick="altcheck('N','<?=$key?>')" checked <?}else{?> onclick="altcheck('Y','<?=$key?>')" <?}?>>
                                </td>
                            <?}
                        }?>
                        <td style="width: 75%;"></td>
                        <td class="lbr">Status:</td> 
                        <td>
                            <select  name="_1_<?=$_acao?>_conferenciaitem_status">
                                <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_conferenciaitem_status);?>		
                            </select>
                        </td>            
                    </tr>
                </table>
            </div>
            <div class="panel-body">
                <table>
                    <tr>
                        <td>Questão.</td>
                        <td><textarea cols="100" rows="3" name="_1_<?=$_acao?>_conferenciaitem_qst"><?=$_1_u_conferenciaitem_qst?></textarea></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?
if(!empty($_1_u_conferenciaitem_idconferenciaitem)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_conferenciaitem_idconferenciaitem; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "conferenciaitem"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<script>
    <?if(!empty($_1_u_conferenciaitem_idconferenciaitem)){?>
    function altcheck(valor,campo){
        CB.post({
            objetos: "_x_u_conferenciaitem_idconferenciaitem="+$("[name=_1_u_conferenciaitem_idconferenciaitem]").val()+"&_x_u_conferenciaitem_"+campo+"="+valor
            ,parcial:true
        });
    }
    <?}?>
</script>
<?
require_once '../inc/php/readonly.php';
?>
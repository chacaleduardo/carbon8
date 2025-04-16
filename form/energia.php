<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$medicaoenergia = $_GET["medicaoenergia"];

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "medicaoenergia";
$pagvalcampos = array(
    "idmedicao" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from medicaoenergia where idmedicao = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<div class="row ">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <table>
                    <tr>
                        <td>
                            <?
                            if (empty($_1_u_medicaoenergia_idmedicao)) {
                                $_1_u_medicaoenergia_idmedicao = $idmedicao;
                            }
                            ?><input name="_1_<?= $_acao ?>_medicaoenergia_idmedicao" id="idmedicao" type="hidden" readonly value="<?= $_1_u_medicaoenergia_idmedicao ?>">

                        </td>
                    </tr>
                    
                    <tr>           
                        <td align="right">Medidor:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_medidor" type="text" id="medidor" value="<?= $_1_u_medicaoenergia_medidor?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">Data:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_data" type="text" id="data" value="<?= $_1_u_medicaoenergia_data?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">DPTDCT:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_DPTDCT" type="text" id="ua" value="<?= $_1_u_medicaoenergia_DPTDCT?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">DPQSING:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_DPQSING" type="text" id="DPQSING" value="<?= $_1_u_medicaoenergia_DPQSING?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">Ua:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_ua" type="text" id="ua" value="<?= $_1_u_medicaoenergia_ua?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">Ub:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_ub" type="text" id="ub" value="<?= $_1_u_medicaoenergia_ub?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr> 

                    <tr>           
                        <td align="right">Uc:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_uc" type="text" id="uc" value="<?= $_1_u_medicaoenergia_uc?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">Uab:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_uab" type="text" id="uab" value="<?= $_1_u_medicaoenergia_uab?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">Ubc:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_ubc" type="text" id="ubc" value="<?= $_1_u_medicaoenergia_ubc?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>

                    <tr>           
                        <td align="right">Uca:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_uca" type="text" id="uca" value="<?= $_1_u_medicaoenergia_uca?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr> 

                    <tr>           
                        <td align="right">La:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_la" type="text" id="la" value="<?= $_1_u_medicaoenergia_la ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Lb:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_lb" type="text" id="lb" value="<?= $_1_u_medicaoenergia_lb ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Lc:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_lc" type="text" id="lc" value="<?= $_1_u_medicaoenergia_lc ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Pa:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_pa" type="text" id="pa" value="<?= $_1_u_medicaoenergia_pa ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Pb:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_pb" type="text" id="pb" value="<?= $_1_u_medicaoenergia_pb ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Pc:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_pc" type="text" id="pc" value="<?= $_1_u_medicaoenergia_pc ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Ps:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_ps" type="text" id="ps" value="<?= $_1_u_medicaoenergia_ps ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Qa:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_qa" type="text" id="qa" value="<?= $_1_u_medicaoenergia_qa ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Qb:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_qb" type="text" id="qb" value="<?= $_1_u_medicaoenergia_qb ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Qc:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_qc" type="text" id="qc" value="<?= $_1_u_medicaoenergia_qc ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Qs:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_qs" type="text" id="qs" value="<?= $_1_u_medicaoenergia_qs ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Pfa:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_pfa" type="text" id="pfa" value="<?= $_1_u_medicaoenergia_pfa ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Pfb:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_pfb" type="text" id="pfb" value="<?= $_1_u_medicaoenergia_pfb ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Pfc:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_pfc" type="text" id="pfc" value="<?= $_1_u_medicaoenergia_pfc ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Sa:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_sa" type="text" id="sa" value="<?= $_1_u_medicaoenergia_sa ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Sb:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_sb" type="text" id="sb" value="<?= $_1_u_medicaoenergia_sb ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">Sc:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_Sc" type="text" id="sc" value="<?= $_1_u_medicaoenergia_Sc?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Ss:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_ss" type="text" id="ss" value="<?= $_1_u_medicaoenergia_ss ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">F:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_f" type="text" id="la" value="<?= $_1_u_medicaoenergia_f?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">WPP:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_wpp" type="text" id="wpp" value="<?= $_1_u_medicaoenergia_wpp ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">WPN:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_wpn" type="text" id="wpn" value="<?= $_1_u_medicaoenergia_wpn ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">WQP:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_wqp" type="text" id="wqp" value="<?= $_1_u_medicaoenergia_wqp ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">WQN:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_wqn" type="text" id="wqn" value="<?= $_1_u_medicaoenergia_wqn ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">EPP:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_epp" type="text" id="epp" value="<?= $_1_u_medicaoenergia_epp ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">EPN:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_epn" type="text" id="epn" value="<?= $_1_u_medicaoenergia_epn ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">EQP:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_medicaoenergia_eqp" type="text" id="eqp" value="<?= $_1_u_medicaoenergia_eqp ?>" size="20" vnulo style="background-color: #8080802e;">
                        </td>

                        <td align="right">QEN:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_medicaoenergia_qen" type="text" id="qen" value="<?= $_1_u_medicaoenergia_qen ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                </table>
            </div>
        </div>
    </div>
</div>

<?
if (!empty($_1_u_medicaoenergia_idmedicao)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_medicaoenergia_idmedicao; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "medicaoenergia"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>


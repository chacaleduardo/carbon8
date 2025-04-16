<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");



$idnfentradaxml = $_GET["idnfentradaxml"];

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "vwdanfenaovinculado";
$pagvalcampos = array(
    "idnfentradaxml" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from vwdanfenaovinculado where idnfentradaxml = '#pkid'";

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
                            if (empty($_1_u_vwdanfenaovinculado_idnfentradaxml)) {
                                $_1_u_vwdanfenaovinculado_idnfentradaxml = $idnfentradaxml;
                            }
                            ?><input name="_1_<?= $_acao ?>_nfentradaxml_idnfentradaxml" id="idnfentradaxml" type="hidden" readonly value="<?= $_1_u_vwdanfenaovinculado_idnfentradaxml ?>">
                           
                        </td>
                    </tr>

                    <tr>
                        <td align="right">Tipo:</td>
                        <td>
                            <label class="alert-warning"><?= $_1_u_vwdanfenaovinculado_tipo ?></label>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                           
                        </td>
                    </tr>

                    <tr>
                        <td align="right">Chave:</td>
                        <td>
                        <label class="alert-warning"><?= $_1_u_vwdanfenaovinculado_chave ?></label>
                        </td>

                    </tr>

                    <tr>
                        <td align="right">Nome:</td>
                        <td><input readonly="readonly" type="text" id="nome" value="<?= $_1_u_vwdanfenaovinculado_nome ?>" size="20" vnulo style="background-color: #8080802e;"></td>
                    </tr>

                    <tr>           
                        <td align="right">Emissão:</td>
                            <td colspan='3'>
                                <input disabled="disabled" name="_1_<?= $_acao ?>_nfentradaxml_dtemissao" type="text" id="dtemissao" value="<?= $_1_u_vwdanfenaovinculado_dtemissao ?>" size="45" style="background-color: #8080802e;">
                        </td>
                    </tr>    
                       
                    <tr>
                        <td align="right">Valor:</td>
                        <td><input readonly="readonly" name="_1_<?= $_acao ?>_nfentradaxml_valor" type="text" id="valor" value="<?= $_1_u_vwdanfenaovinculado_valor ?>" size="20" vnulo style="background-color: #8080802e;"></td>

                    </tr>

                    <tr>                
                        <td align="right">Observações:</td>
                        <td>
                            <textarea placeholder="Adicione comentário para editar o status..." class="caixa mw-100" name="_1_<?= $_acao ?>_nfentradaxml_obs" id="obs" style="width: 100%; height: 80px;"></textarea>
                        </td> 
                    </tr>

                    <tr>
                    <td align="right">Status:</td>
                        <td>
                            <select id="status" title="Recusar" name="_1_<?= $_acao ?>_nfentradaxml_status"> 
                            <?fillselect("select 'AUTORIZADO', 'Autorizado' union select 'CANCELADO', 'Cancelado' union select 'RECUSADO','Recusado'", $_1_u_vwdanfenaovinculado_status);?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    var originalCBPost = CB.post;

    CB.post = function() {
        var textarea = $('#obs');
        if ($.trim(textarea.val()) === '') {
            alert('O campo de observação está vazio. Por favor, preencha-o antes de alterar o status!');
            return;
        }
        originalCBPost.apply(this, arguments);
    };
});

</script>


<?
if (!empty($_1_u_vwdanfenaovinculado_idnfentradaxml)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_vwdanfenaovinculado_idnfentradaxml; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "vwdanfenaovinculado"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>


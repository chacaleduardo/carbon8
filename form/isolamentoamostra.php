<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "isolamentoamostra";
$pagvalcampos = array(
    "idisolamentoamostra" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from isolamentoamostra where idisolamentoamostra = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table style="width: 100%;">
                    <tbody>
                        <tr>
                            <td align="right">
                                <strong>Descrição:</strong>
                            </td>
                            <td>
                            <input   type="hidden" name="_1_<?=$_acao?>_isolamentoamostra_idisolamentoamostra"  value="<?=$_1_u_isolamentoamostra_idisolamentoamostra?>">
                            <input   type="text" name="_1_<?=$_acao?>_isolamentoamostra_descricao"  value="<?=$_1_u_isolamentoamostra_descricao ?>" autocomplete="off" vnulo>
                            </td>

                            <td align="right">
                                <strong>Status:</strong>
                            </td>
                            <td align="right">
                                <select name="_1_<?=$_acao?>_isolamentoamostra_status" class="form-control" style="width: 100px;">
                                    <?
                                    fillselect(["ATIVO" => 'Ativo', "INATIVO"=> 'Inativo'], $_1_u_isolamentoamostra_status)
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
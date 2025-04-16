<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parámetros GET que devem ser validados para compor o select principal
 *                pk: indica parámetro chave para o select inicial
 *                vnulo: indica parámetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "prodservformularotulo";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idprodservformularotulo" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".prodservformularotulo where idprodservformularotulo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="panel panel-default" >
        <div class="panel-heading">Texto
            <? if($_1_u_prodservformularotulo_idprodservformula) { ?>
                <a title="Imprimir" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('form/rotulovac.php?acao=u&idprodservformula=<?=$_1_u_prodservformularotulo_idprodservformula?>')"></a>
            <?}?>
        </div>
        <div  class="panel-body">
            <table>
            <input type="hidden" name="_1_<?=$_acao?>_prodservformularotulo_idprodservformularotulo" value="<?= $_1_u_prodservformularotulo_idprodservformularotulo ?>" readonly>
            <tr>
                <td>Rótulo:</td>
                <td>
                    <input name="_1_<?=$_acao?>_prodservformularotulo_titulo" id="rotulo" value="<?= $_1_u_prodservformularotulo_titulo?>">
                </td>
            </tr>
            <tr>
                <td>Indicações:</td>
                <td>
                    <textarea  name="_1_<?=$_acao?>_prodservformularotulo_indicacao" style="width: 365px; height: 79px;" vnulo><?=$_1_u_prodservformularotulo_indicacao?></textarea>
                </td>
            </tr>
            <tr>
                <td>Fórmula:</td>
                <td>
                    <textarea  name="_1_<?=$_acao?>_prodservformularotulo_formula" style="height: 128px; margin: 0px; width: 366px;" vnulo><?=$_1_u_prodservformularotulo_formula?></textarea>
                </td>
           
                <td>Cepas:</td>
                <td>
                    <textarea  name="_1_<?=$_acao?>_prodservformularotulo_cepas" style="width: 254px; height: 138px;" vnulo><?=$_1_u_prodservformularotulo_cepas?></textarea>
                </td>
            </tr>
            <tr>
                <td>Modo de usar:</td>
                <td>
                    <textarea  name="_1_<?=$_acao?>_prodservformularotulo_modousar" style="height: 135px; margin: 0px; width: 362px;" vnulo><?=$_1_u_prodservformularotulo_modousar?></textarea>
                </td>
                <td>Descrição:</td>
                <td>
                    <textarea  name="_1_<?=$_acao?>_prodservformularotulo_descricao" style="width: 254px; height: 42px;" ><?=$_1_u_prodservformularotulo_descricao?></textarea>
                </td>
            </tr>
            <tr>
                <td>Programa de utilização:</td>
                <td>
                    <textarea  name="_1_<?=$_acao?>_prodservformularotulo_programa" style="height: 135px; margin: 0px; width: 362px;" vnulo><?=$_1_u_prodservformularotulo_programa?></textarea>
                </td>
                <td>Conteúdo:</td>
                <td>
                    <textarea  name="_1_<?=$_acao?>_prodservformularotulo_conteudo" style="width: 254px; height: 42px;" ><?=$_1_u_prodservformularotulo_conteudo?></textarea>
                </td>
            </tr>        
        </table>
        </div>
    </div>
<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("controllers/rheventofolha_controller.php");

$idcliente = $_GET["idpessoa"];

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "rheventofolha";
$pagvalcampos = array(
    "idrheventofolha" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rheventofolha where idrheventofolha = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<div class="row ">
    <div class="col-md-12">
        <div class="panel panel-default">
        <div class="panel-heading">
            <table>
                <tr>
                    <td align="right">Título:</td>
                    <td>
                       <input name="_1_<?= $_acao?>_rheventofolha_idrheventofolha" id="idrheventofolha" type="hidden" readonly value="<?=$_1_u_rheventofolha_idrheventofolha ?>">
                       <input name="_1_<?= $_acao?>_rheventofolha_titulo" class="size30" type="text" value="<?=$_1_u_rheventofolha_titulo?>">
                    </td>
                    <td align="right">Status:</td>
                    <td>
                        <select name="_1_<?= $_acao?>_rheventofolha_status" id="status" vnulo>
                            <?
                            fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_rheventofolha_status);
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div class="panel-body">
            <?
            if(!empty($_1_u_rheventofolha_idrheventofolha)){
            $res=RheventofolhaController::buscarRheventofolhaItem($_1_u_rheventofolha_idrheventofolha); 
            ?>
            <table class="table table-striped">
                <tr>
                    <th>Evento</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Inativar</th>
                </tr>
            <?$i=1;
            foreach($res as $row){   
                $i++;
            ?>
                <tr>
                    <td>
                        <input name="_<?=$i?>_u_rheventofolhaitem_idrheventofolhaitem" type="hidden"  value="<?=$row['idrheventofolhaitem']?>">
                        <select class="size30" name="_<?=$i?>_u_rheventofolhaitem_idrhtipoevento" vnulo>
                                <option value=""></option>
                            <? fillselect($rhtipoevento=RheventofolhaController::buscarRhtipoeventoConf(),
                               $row['idrhtipoevento']
                            ); ?>
                        </select>
                    </td>
                    <td>
                        <select id="idcontaitem<?=$row['idrheventofolhaitem']?>" class='size30' name="_<?=$i?>_u_rheventofolhaitem_idcontaitem" vnulo onchange="preencheti(<?=$row['idrheventofolhaitem']?>)">
                            <option value=""></option>
                            <? fillselect(getContaItemSelect(), $row['idcontaitem']); ?>
                        </select>
                    </td>
                    <td>
                    <?
                    if ($row['idcontaitem']) {
                    ?>
                    <select id="idtipoprodserv<?= $row["idrheventofolhaitem"] ?>" class='size30' name="_<?=$i?>_u_rheventofolhaitem_idtipoprodserv" vnulo>
                        <option value=""></option>
                        <? fillselect(RheventofolhaController::buscarContaItemTipoProdservTipoProdServ($row['idcontaitem']), $row['idtipoprodserv']); ?>
                    </select>
                    <?
                    } else {
                        
                        ?>
                        <select id="idtipoprodserv<?= $row["idrheventofolhaitem"] ?>" class='size30' name="_<?=$i?>_u_rheventofolhaitem_idtipoprodserv" vnulo>
                            <option value=""></option>
                            <? fillselect(RheventofolhaController::$ArrayVazio, $row['idtipoprodserv']); ?>
                        </select>
                        <?
                    }
                    ?>

                    </td>
                    <td>
                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="drheventofolha(<?=$row['idrheventofolhaitem']?>)" alt="Inativar!"></i>
                    </td>
                </tr>

            <?
                }
            ?>
                <tr>
                    <td colspan="4">
                        <a class="fa fa-plus-circle verde fa-2x btn-lg pointer" onclick="irheventofolha(<?=$_1_u_rheventofolha_idrheventofolha?>)" title="Adicionar mais uma configuração"></a>
                    </td>
                </tr>
            </table>                
        <?
            }
        ?>
        </div>
        </div>
    </div>
</div>
<?
if (!empty($_1_u_rheventofolha_idrheventofolha)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_rheventofolha_idrheventofolha; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "rheventofolha"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once('../form/js/rheventofolha_js.php');
?>
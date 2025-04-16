<?

require_once("../inc/php/validaacesso.php");
require_once("controllers/solcom_controller.php");
require_once("controllers/pedido_controller.php");
require_once("controllers/prodservformula_controller.php");



if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "prodservformula";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idprodservformula" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from prodservformula where idprodservformula = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if(empty($_GET['idsolcom']))
{
 die("Não informada solicitação de compras.");
}else{
    $_idsolcom=$_GET['idsolcom'];
}

 $_row = PedidoController::buscarRotuloFormulaPorId($_1_u_prodservformula_idprodservformula);
 $prodserv = getObjeto("prodserv", $_1_u_prodservformula_idprodserv, "idprodserv")
?>
    <div class="panel panel-default">
        <div class="panel-heading">           
            <table>					
                <td align="right"><strong>Quantidade:</strong></td>
                <td>
                <input type="text" onkeyup="calculaproduto(this)"  valor="<?=$_1_u_prodservformula_qtdpadraof?>" name="qtdpadrao" class="form-control size7 pd-right-10" value="<?=$_1_u_prodservformula_qtdpadraof?>" placeholder="Qtd" title="Qtd Padrão" vnulo="">
                <?=$prodserv['un']?>
                </td>
                <td align="right"><strong>Produto:</strong></td>
                <td nowrap="">
                    <label class="alert-warning">
                        <?=$_row['codprodserv']?> - <?=$_row['descr']?> - <?=$_row['rotulo']?>
                    </label>
                    <a title="Abrir Produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&idprodserv=<?=$_1_u_prodservformula_idprodserv?>" target="_blank" style="margin: 0 4px;"></a>
                </td>
                </tr>
            
            </table>			
        </div>   
        <div class="panel-body">
        <table  class="table table-striped planilha">
            <tr>
                <th>#</th>
                <th style="width: 15%;">Qtd</th>
                <th>Un.</th>
                <th>Insumo</th>
                <th></th>
                <th></th>
            </tr>
        <?
        $arrins=ProdservformulaController::listarProdservFormulaInsComprados($_1_u_prodservformula_idprodservformula);

        $ifi=0;
        foreach ($arrins as $formins) 
        {//print_r($formins);
            $ifi++;
            $insprodserv = getObjeto("prodserv", $formins["idprodserv"], "idprodserv");
            if($insprodserv['comprado']!='Y'){
                $cor="color: #3c763d;";
            }else{
                $cor="";
            }
            ?>
            <tr id="tr<?=$formins["idprodserv"]?>" style="<?=$cor?>">
                <td><?=$ifi?></td>
                <td>
                    <?if($insprodserv['comprado']=='Y'){?>
                    <input type="hidden" name="_ifi<?=$ifi ?>_i_solcomitem_idsolcom" value="<?=$_idsolcom?>"  class="fonte11 size7">
                    <input type="hidden" name="_ifi<?=$ifi ?>_i_solcomitem_descr" value="<?=$insprodserv["descr"] ?>"  class="fonte11 size7">
                    <input type="hidden" name="_ifi<?=$ifi ?>_i_solcomitem_status" value="PENDENTE"  class="fonte11 size7">
                    <input type="hidden" name="_ifi<?=$ifi ?>_i_solcomitem_fabrica" value="Y"  class="fonte11 size7">
                    <input type="hidden" name="_ifi<?=$ifi ?>_i_solcomitem_idprodserv" value="<?=$formins["idprodserv"] ?>"  class="fonte11 size7">                    
                    <input type="text"  readonly='readonly' valor="<?=$formins["qtdi"]?>" name="_ifi<?=$ifi ?>_i_solcomitem_qtdc" value="<?=$formins["qtdi"]?>"  class="fonte11 size7 valor">
                <?}else{
?>
                    <input type="text"  readonly='readonly' valor="<?=$formins["qtdi"]?>" name="qtdc" value="<?=recuperaExpoente($formins["qtdi"], $formins["qtdi_exp"])?>"  class="fonte11 size7 valor">
<?
                }?>
                </td>
                <td>
                <?if($insprodserv['comprado']=='Y'){?>
                    <input type="hidden" name="_ifi<?=$ifi ?>_i_solcomitem_un" value="<?=$insprodserv["un"] ?>"  class="fonte11 size7">
                <?}?>
                    <?
                    echo ($insprodserv["un"]);
                    ?>
                </td>
                <td>
                   <?if($insprodserv['comprado']=='N'){ echo " (FORMULADO) "; }?> <?=$insprodserv["codprodserv"].' - '.$insprodserv["descr"]?> 
                </td>
                <td>
                    <i class="fa fa-bars fa-1x pointer cinzaclaro hoverazul" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$formins['idprodserv']?>')" title="Editar insumo #<?=$formins["idprodserv"]?>"></i>
                </td>
                <td>                  
                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="retirarinsumo(<?=$formins['idprodserv']?>)" title="Retirar este insumo da lista"></i>
                </td>
            </tr>
            <?
        }
        ?>
        </table>
        </div>
    </div>
<script>

CB.posPost = function() {
    $("#cbModalCorpo").html("");
    $('#cbModal').modal('hide');
}
</script>
    <?require_once('../form/js/solcom_js.php'); ?>
<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");
require_once("../form/controllers/prodservfornecedor_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "prodserv";
$pagvalcampos = array(
    "idprodserv" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM prodserv WHERE idprodserv = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$jsonProd = ProdservFornecedorController::buscarProdservPorVendaMaterialIdTipoProdserv("172, 192, 190, 191");

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>
                        <td style="font-size: 13px;">Fornecedor</td>
                        <td style="padding-left: 80px;">Descrição:</td>
                        <td>
                            <label class="alert-warning">
                                <?=$_1_u_prodserv_descr ?>
                                <a title="Abrir Prodserv" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&idprodserv=<?=$_1_u_prodserv_idprodserv ?>" target="_blank" style="margin: 0 4px;"></a>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="panel-body">
                <? //listar fornecedores
                if (ProdservFornecedorController::buscarQtdProdservFornPorIdprodserv($_1_u_prodserv_idprodserv) > 0) {
                    $classrot = "";
                } else {
                    $classrot = "hide";
                }

                $_listarProdservForn = ProdservFornecedorController::buscarProdservFornPorIdprodserv($_1_u_prodserv_idprodserv);
                ?>
                <table class="table table-striped planilha">
                    <tr style="font-size:11px;">
                        <th style="width:25%">Nome</th>
                        <th style="width:30%">Descrição</th>
                        <th style="width:12%">Código</th>
                        <? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>
                            <th style="width:2%"><i class="fa fa-wrench"></i></th>
                            <th style="width:10%">UN Compra</th>
                            <th style="width:5%">Conversão</th>
                        <? } ?>
                        <th style="width:4%" class="nowrap">UN Padrão</th>
                        <th style="width:3%; text-align:center">Cert</th>
                        <th style="width:4%; text-align: center;">Alterado</th>
                        <th style="width:5%; text-align: center;">Ação</th>
                    </tr>
                    <?
                    $inativo = 0;
                    foreach($_listarProdservForn['dados'] as $prodservForn) 
                    {
                        $i++;
                        if (!empty($prodservForn['idpessoa'])) {
                            $desabilitar = "disabled='disabled'";
                            $fundo = "background-color: #8080801c;";
                            $funcao = "Não é possivel alterar o fornecedor para isto e necessário excluir e criar outro";
                        } else {
                            $desabilitar = '';
                            $fundo = "";
                            $funcao = "Selecione o fornecedor";
                        }
                        ?>
                        <tr style="font-size:11px;">
                            <?
                            if ($prodservForn['status'] == 'INATIVO') 
                            {
                                if ($inativo == 0) 
                                {
                                    ?>
                                    <tr>
                                        <td colspan="20" style="height:40px;" data-toggle="collapse" href="#inativo" aria-expanded="false" class="collapsed">
                                            Fornecedores Inativos
                                            <i class="fa fa-arrows-v cinzaclaro pointer cotacao_todos_item" title="Produto"></i>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="inativo">
                                        <td colspan="20">
                                            <table style="width: 100%;">
                                                <?
                                }
                                $inativo++;
                            }
                            ?>
                            <td style="width:25%">
                                <input title="<?=$funcao ?> &#013; <?=$prodservForn['nome'] ? $prodservForn['sigla'] . ' - ' . $prodservForn['nome'] : ''; ?>" <?=$desabilitar?> idprodservforn="<?=$prodservForn['idprodservforn']?>" class="size50 fornecedor" name="fornecedor<?=$prodservForn['idprodservforn']?>" value="<?=$prodservForn['nome'] ? $prodservForn['sigla'] . ' - ' . $prodservForn['nome'] : ''; ?>" type="text" id="fornecedor<?=$prodservForn['idprodservforn']?>" style="width:90% !important;font-size:11px; <?=$fundo ?>">
                                <? if (!empty($prodservForn['idpessoa'])) { ?>
                                    <a class="fa fa-bars pointer hoverazul" title="Fornecedor" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$prodservForn['idpessoa']?>')"></a>
                                <? } ?>
                            </td>
                            <td align="center" style="width:30%">
                                <input size="6" type="hidden" value="">
                                <input title="<?=$prodservForn["codforn"]?>" onchange="condforn(this,<?=$prodservForn['idprodservforn']?>)" size="50" type="text" value="<?=$prodservForn["codforn"]?>" style="width:100% !important;font-size:11px;">
                            </td>
                            <td style="width:12%">
                                <input type="text" title="Código Fornecedor" value="<?=$prodservForn['cprodforn']?>" onchange="cprodforn(this,<?=$prodservForn['idprodservforn']?>)">
                            </td>
                            <? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>
                                <td style="width:2%">
                                    <?
                                    if ($prodservForn['converteest'] == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                        $classattr = "";
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                        $classattr = "hide";
                                    }
                                    ?>
                                    <input title="Converter" type="checkbox" <?=$checked ?> name="converteforn" idprodservforn="<?=$prodservForn['idprodservforn']?>" onclick="converteforn(this,<?=$prodservForn['idprodservforn']?>)">
                                </td>
                                <td style="width:10%">
                                    <div id="unforn<?=$prodservForn["idprodservforn"]?>" class="<?=$classattr ?>">
                                        <select id="iunforn<?=$prodservForn["idprodservforn"]?>" onchange="unforn(this,<?=$prodservForn['idprodservforn']?>)" title="<?=$prodservForn["unforn"]?>" style="width:100% !important;font-size:11px;">
                                            <option value=""></option>
                                            <? fillselect(ProdservFornecedorController::buscarUnidadeVolume(), $prodservForn['unforn']) ?>
                                        </select>
                                    </div>
                                </td>
                                <td style="width:5%">
                                    <div id="valconv<?=$prodservForn["idprodservforn"]?>">
                                        <input id="ivalconv<?=$prodservForn["idprodservforn"]?>" class="<?=$classattr ?>" onchange="valconv(this,<?=$prodservForn['idprodservforn']?>)" class="size5" type="text" value="<?=$prodservForn["valconv"]?>" style="width:100% !important;font-size:11px;">
                                    </div>
                                </td>
                            <? } ?>
                            <td style="width:5%"><?=traduzid('unidadevolume', 'un', 'descr', $_1_u_prodserv_un) ?></td>
                            <td align="center" style="width:3%;">
                                <? if (!empty($prodservForn["idpessoa"])) { ?>
                                    <a class="fa fa-list-ol hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=analiseqst&_acao=u&idprodserv=<?=$_1_u_prodserv_idprodserv ?>&idprodservforn=<?=$prodservForn['idprodservforn']?>&idpessoa=<?=$prodservForn['idpessoa']?>')" title="Cert. Análise"></a>
                                <? } ?>
                            </td>
                            <td style="width:4%">
                                <i class="fa btn-sm fa-info-circle cinza hoverazul mostrarAlterado"></i>
                                <div class="webui-popover-content">
                                    <br />
                                    <table class="table table-striped planilha">
                                        <tr>
                                            <td>Alterado Por</td>
                                            <td>Alterado Em</td>
                                        </tr>
                                        <tr>
                                            <td><?=$prodservForn["alteradopor"]?></td>
                                            <td><?=dmahms($prodservForn["alteradoem"]) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                            <td style="width:5%; text-align: center;">
                                <?
                                if ($prodservForn["status"] != 'INATIVO') 
                                {
                                    $qtdrowsQtdNfitem = ProdservFornecedorController::buscarQtdFornecedorNfItem($prodservForn["idprodservforn"]);
                                    if($qtdrowsQtdNfitem == 0) 
                                    {
                                        ?>
                                        <i title="Excluir" style="padding: 10px 5px;" class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirFornecedor(<?=$prodservForn['idprodservforn']?>, 'prodservforn')" alt="Excluir Forncedor!"></i>
                                        <?
                                    }
                                    ?>
                                    <i title="Inativar" style="padding: 10px 5px;" class="fa fa-times fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="inativaobjeto(<?=$prodservForn['idprodservforn']?>, 'prodservforn')" alt="Inativar Forncedor!"></i>
                                    <?
                                } else {
                                    ?>
                                    <i title="Restaurar" style="padding: 10px 5px;" class="fa fa-warning cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="habilitarFornecedor(<?=$prodservForn['idprodservforn']?>, 'prodservforn')" alt="Habilitar Forncedor!"></i>
                                    <?
                                }
                                ?>
                            </td>
                        </tr>
                        <?
                        if ($_listarProdservForn['qtdLinhas'] == $i && $prodservForn['status'] == 'INATIVO') 
                        {
                            ?>
                                    </tr>
                                </td>
                            </table>
                            <?
                        }
                    } 
                    ?>
                    <tr>
                        <td colspan="10">
                            <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novoobjeto('prodservforn','N')" alt="Inserir novo fornecedor!"></i>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Fornecedor Multi-empresa</div>
            <div class="panel-body">
                <? //listar fornecedores 
                if (ProdservFornecedorController::buscarQtdProdservFornPorIdprodserv($_1_u_prodserv_idprodserv, " AND f.status = 'ATIVO'")  > 0) {
                    $classrot = "";
                } else {
                    $classrot = "hide";
                }

                $_listarProdservfornProdserv = ProdservFornecedorController::buscarProdservFornProdservPorIdprodserv($_1_u_prodserv_idprodserv);
                ?>
                <table class="table table-striped planilha">
                    <tr>
                        <th style="width:25%">Nome</th>
                        <th style="width:38%">Produto-Origem</th>
                        <? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>
                            <th style="width:2%"><i class="fa fa-wrench"></i></th>
                            <th style="width:10%">UN Compra</th>
                            <th style="width:5%">Conversão</th>
                        <? } ?>
                        <th style="width:5%" class="nowrap">UN Padrão</th>
                        <th style="width:4%; text-align:center">Cert</th>
                        <th style="width:5%; text-align: center;">Alterado</th>
                        <th style="width:6%; text-align: center;">Ação</th>
                    </tr>
                    <?
                    $inativo = 0;
                    $i = 0;
                    foreach($_listarProdservfornProdserv['dados'] as $prodservfornProdserv) 
                    {
                        $i++;
                        ?>
                        <tr style="font-size:11px;">
                            <?
                            if ($prodservfornProdserv['status'] == 'INATIVO') 
                            {
                                if ($inativo == 0) 
                                {
                                    ?>
                                    <tr>
                                        <td colspan="20" style="height:40px;" data-toggle="collapse" href="#inativomulti" aria-expanded="false" class="collapsed">
                                            Fornecedores Inativos
                                            <i class="fa fa-arrows-v cinzaclaro pointer cotacao_todos_item" title="Produto"></i>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="inativomulti">
                                        <td colspan="20">
                                            <table style="width: 100%;">
                                                <?
                                }
                                $inativo++;
                            }
                            ?>
                            <td style="width:25%">
                                <select title="<?=$prodservfornProdserv["nome"]?>" onchange="idpessoa(this,<?=$prodservfornProdserv['idprodservforn']?>)" style="float: left; width: 84% !important;">
                                    <option value=""></option>
                                    <? fillselect(ProdservFornecedorController::listarPessoaIdempresaGrupoNulo(), $prodservfornProdserv['idpessoa']); ?>
                                </select>
                                <? if (!empty($prodservfornProdserv['idpessoa'])) { ?>
                                    <a class="fa fa-bars pointer hoverazul" title="Fornecedor" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$prodservfornProdserv['idpessoa']?>')" style="float: right; text-align: center; padding-top: 12px;"></a>
                                <? } ?>
                            </td>
                            <td align="center" style="width:38%">
                                <input idempresa="<?=$prodservfornProdserv['idempresa']?>" onclick="autoCompleteProduto()" class="prodempresa" size="50" idprodservforn="<?=$prodservfornProdserv['idprodservforn']?>" value="<?=$prodservfornProdserv['descr']?>" type="text" >
                            </td>                                        
                            <? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>
                                <td style="width:2%">
                                    <?
                                    if ($prodservfornProdserv['converteest'] == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                        $classattr = "";
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                        $classattr = "hide";
                                    }
                                    ?>
                                    <input title="Converter" type="checkbox" <?=$checked ?> name="converteforn" idprodservforn="<?=$prodservfornProdserv["idprodservforn"]?>" onclick="converteforn(this,<?=$prodservfornProdserv["idprodservforn"]?>)">
                                </td>
                                <td style="width:7%">
                                    <div id="unforn<?=$prodservfornProdserv["idprodservforn"]?>" class="<?=$classattr ?>">
                                        <select id="iunforn<?=$prodservfornProdserv["idprodservforn"]?>" onchange="unforn(this,<?=$prodservfornProdserv["idprodservforn"]?>)" title="<?=$prodservfornProdserv["unforn"]?>">
                                            <option value=""></option>
                                            <? fillselect(ProdservFornecedorController::buscarUnidadeVolume(), $prodservfornProdserv['unforn']) ?>
                                        </select>
                                    </div>
                                </td>
                                <td style="width:5%">
                                    <div id="valconv<?=$prodservfornProdserv["idprodservforn"]?>">
                                        <input id="ivalconv<?=$prodservfornProdserv["idprodservforn"]?>" class="<?=$classattr ?>" onchange="valconv(this,<?=$prodservfornProdserv["idprodservforn"]?>)" type="text" value="<?=$prodservfornProdserv["valconv"]?>">
                                    </div>
                                </td>
                            <? } ?>
                            <td style="width:5%"><?=traduzid('unidadevolume', 'un', 'descr', $_1_u_prodserv_un) ?></td>
                            <td style="width:4%; text-align:center">
                                <? if (!empty($prodservfornProdserv["idpessoa"])) { ?>
                                    <a class="fa fa-list-ol hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=analiseqst&_acao=u&idprodserv=<?=$_1_u_prodserv_idprodserv ?>&idprodservforn=<?=$prodservfornProdserv["idprodservforn"]?>&idpessoa=<?=$prodservfornProdserv["idpessoa"]?>')" title="Cert. Análise"></a>
                                <? } ?>
                            </td>
                            <td style="width:5%">
                                <i class="fa btn-sm fa-info-circle cinza hoverazul mostrarAlterado"></i>
                                <div class="webui-popover-content">
                                    <br />
                                    <table class="table table-striped planilha">
                                        <tr>
                                            <th>Alterado Por</th>
                                            <th>Alterado Em</th>
                                        </tr>
                                        <tr>
                                            <td><?=$prodservfornProdserv["alteradopor"]?></td>
                                            <td><?=dmahms($prodservfornProdserv["alteradoem"]) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                            <td style="width:5%; text-align: center;">
                                <?
                                if ($prodservfornProdserv["status"] != 'INATIVO') 
                                {
                                    $qtdrowsQtdNfitem = ProdservFornecedorController::buscarQtdFornecedorNfItem($prodservForn["idprodservforn"]);
                                    if ($qtdrowsQtdNfitem == 0) 
                                    {
                                        ?>
                                        <i title="Excluir" style="padding: 10px 5px;" class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirFornecedor(<?=$prodservfornProdserv['idprodservforn']?>, 'prodservforn')" alt="Excluir Forncedor!"></i>
                                        <?
                                    }
                                    ?>
                                    <i title="Inativar" style="padding: 10px 5px;" class="fa fa-times fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="inativaobjeto(<?=$prodservfornProdserv['idprodservforn']?>, 'prodservforn')" alt="Inativar Forncedor!"></i>
                                    <?
                                }
                                ?>
                            </td>
                        </tr>
                        <?
                        if ($_listarProdservfornProdserv['qtdLinhas'] == $i && $prodservfornProdserv['status'] == 'INATIVO') 
                        {
                            ?>
                                    </tr>
                                </td>
                            </table>
                            <?
                        }
                    }
                    ?>
                    <tr>
                        <td colspan="10">
                            <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novoobjeto('prodservforn','Y')" alt="Inserir novo fornecedor!"></i>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<? require_once('../form/js/prodservfornecedor_js.php'); ?>

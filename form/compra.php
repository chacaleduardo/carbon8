<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/nfentrada_controller.php");
require_once("../form/controllers/rateioitemdest_controller.php");
require_once("../model/nf.php");
require_once(__DIR__ . "/controllers/fluxo_controller.php");
require_once("../model/prodserv.php");
require_once("../api/nf/index.php");

//Chama a Classe prodserv
$prodservclass = new PRODSERV();

//LTM - 31-03-2021: Retorna o IdFluxoStatus do Lote ABERTO, utlizado na função do javaScritp novolote
if($_POST){
    include_once("../inc/php/cbpost.php");
}

if(!empty($_GET["idnfcp"]) && empty($_GET["idnf"])){
    $_GET["idnf"] = $_GET["idnfcp"];
    $_GET["_acao"] = 'u';
    $idnfcp = 'Y';
}


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nf";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idnf" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM nf WHERE idnf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


if($idnfcp == 'Y'){
    $_1_u_nf_nnfe = '';
    $_acao = 'i';
    $_1_u_nf_status = 'INICIO';
}


if($_GET['_modulo'] == 'nfrdv'){
    $tipoorc = 'RDV';
}

/*
if(empty($_1_u_nf_prazo)){
    $_1_u_nf_prazo = date('d/m/Y');
}
*/

if(!empty($_1_u_nf_tipoorc) && $_GET['_modulo'] == 'nfrdv' && $_1_u_nf_tipoorc != $tipoorc){
    die("Modulo RDV esta entrada não se trata de um  RDV");
}

$idunidadelote = getUnidadePadraoModulo('lotealmoxarifado', $_GET['_idempresa']);
$arrayCliente = NfEntradaController::buscarClientesCompras($pagvalmodulo);

$idpessoa = $_GET['idpessoa'];
if(empty($_1_u_nf_idpessoa) and !empty($idpessoa)){
    $_1_u_nf_idpessoa = $idpessoa;
}

if($_GET['_modulo'] == 'comprasrh' and empty($_1_u_nf_idobjetosolipor) and !empty($_GET['idobjetosolipor'])){
    $_1_u_nf_idobjetosolipor = $_GET['idobjetosolipor'];
    $_1_u_nf_tipoobjetosolipor = $_GET['tipoobjetosolipor'];
}

$_1_u_nf_objeto = $_GET['objeto'];

if($pagvalmodulo == 'nfentrada'){
    if($_1_u_nf_tiponf == 'R' && $pagvalmodulo != 'comprasrh'){
        die("Dados disponíveis no modulo de RH");
    }
    if($_1_u_nf_tiponf == 'D' && $pagvalmodulo != 'comprassocios'){
        die("Dados disponíveis no modulo de Sócios");
    }

    if($pagvalmodulo == 'comprassocios' || $_1_u_nf_tiponf == 'D'){
        $flgdiretor = NfEntradaController::buscarSePessoaESocio($_SESSION["SESSAO"]["IDPESSOA"]);
        if($flgdiretor < 1){
            die("Não é permitido o acesso a este modulo.");
        }
    }
}

if($pagvalmodulo == 'comprasrhrestrito'){
    if($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15){
        $pessoaContato = NfEntradaController::buscarPessoaPorContato(12, $_SESSION["SESSAO"]["IDPESSOA"]);
        if($pessoaContato['qtdLinhas'] < 1){
            die("Contato representação sem empresa represetante vinculada");
        }

        if(empty($pessoaContato['dados']['idpessoa'])){
            die("Contato representação sem empresa represetante vinculada");
        }

        $_idpessoa = $pessoaContato['dados']['idpessoa'];
    } else {
        $pessoaContato = NfEntradaController::buscarPessoaPorContato(5, $_SESSION["SESSAO"]["IDPESSOA"]);
        if(!empty($pessoaContato['dados']['idpessoa'])){
            $_idpessoa = $pessoaContato['dados']['idpessoa'];
        } else {
            $_idpessoa = $_SESSION["SESSAO"]["IDPESSOA"];
        }
    }

    if(empty($_idpessoa)){
        die('Não foi possível verificar o vinculo com a NF');
    } elseif($_idpessoa != $_1_u_nf_idpessoa){
        die('Nota não condiz com seu login');
    }
}

$i = 1;
// CONDIÇÕES DA TELA
$mostrabotaocancel = true;
if($_1_u_nf_status == 'CONCLUIDO' || $_1_u_nf_status == 'CANCELADO'){
    $mostrabotaocancel = false;
    $disabled = "disabled='disabled'";
    $readonly = "readonly='readonly'";
}

if(empty($_1_u_nf_parcelas)){
    $_1_u_nf_parcelas = 1;
}
if(empty($_1_u_nf_intervalo)){
    $_1_u_nf_intervalo = 28;
}
if($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'O'){
    $controle = $_1_u_nf_idcotacao . "." . $_1_u_nf_controle;
}

$ctesVinculadas = [];
$comprasVinculadas = [];
$dataEmissaoOriginal = $_1_u_nf_dtemissao;

if ($_1_u_nf_idnf) {
    $ctesVinculadas = NfController::buscarCteVinculadasPorIdNf($_1_u_nf_idnf);
    $comprasVinculadas = NfController::buscarComprasVinculadasPorIdNf($_1_u_nf_idnf);
};

echo '<!--' . $_1_u_nf_tiponf . '-->';


if($_acao == 'i' and !empty($_GET['idnfentradaxml'])){
    include_once("../inc/php/nfentradaxml.php");
} elseif($_acao == 'u' and !empty($_GET['idnfentradaxml']) and empty($_1_u_nf_xmlret)){
    include_once("../inc/php/nfentradaxml.php");
}elseif($_acao == 'u' and $_1_u_nf_tiponf=='T' and !empty($_GET['idnfentradaxml']) ){
        //busca os itens sem cadastro
        $listarProdutoSemCadastro = NfEntradaController::listarItensSemCadastro($_1_u_nf_idnf);
        $qtdProdutosSemCadastro = count($listarProdutoSemCadastro);
        if($qtdProdutosSemCadastro<1){
?>
<script>
        vurl = "inc/php/gerainfcte.php?idnf=" + <?=$_1_u_nf_idnf?>;

        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                alert(data);
                document.location.reload();
            },
            error: function(objxmlreq) {
                alert('Erro:\n' + objxmlreq.status);
            }
        }) //$.ajax
</script>
<?
        }
}


if($_acao == 'i' and !empty($_GET['idnfentradaxml'])){  
   
    $arrayFormaPagamento = NfEntradaController::listarFormaPagamentoAtivoPorLP('nfentrada',$_1_u_nf_idempresa);

}else{
    $arrayFormaPagamento = NfEntradaController::listarFormaPagamentoAtivoPorLP('nfentrada');
}

$VinculadasNF = [];

if($_1_u_nf_idnf) {
    $VinculadasNF=PedidoController::buscarNfVinculadaPorId($_1_u_nf_idnf);
};

?>
<link href="../form/css/nfentrada_css.css?_<?=date("dmYhms") ?>" rel="stylesheet">
<link rel="stylesheet" href="../inc/css/datatables/ag-theme-balham.min.css">
<script src="../inc/js/datatables/ag-grid-community.min.js?_<?=date("dmYhms") ?>"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<div class="row">
    <div class="panel panel-default w-100" style="font-size:12px">
        <div class="panel-heading">
            <div class="sigla-empresa"></div>
            <div class="row d-flex flex-between flex-wrap">
                <!-- ID -->
                <div class="form-group col-xs-6 col-md-1">
                    <label class="text-white">ID</label>
                    <?
                    if(!empty($_1_u_nf_idnf)){
                        ?>
                        <div class="alert-warning form-control d-flex align-items-center">
                            <label for=""> <?=$_1_u_nf_idnf; ?></label>
                        </div>
                        <?
                    }
                    if(!empty($_1_u_nf_idempresa)){
                        ?>
                     <input name="_1_<?=$_acao?>_nf_idempresa" type="hidden" value="<?=$_1_u_nf_idempresa?>">

                        <?
                    }

                    if($idnfcp == 'Y'){
                        ?>
                        <input name="_1_<?=$_acao?>_nf_idnf" id="idnf" type="hidden" value="" readonly='readonly'>
                        <input name="_1_<?=$_acao?>_nf_idobjetosolipor" type="hidden" value="<?=$_GET["idnfcp"]?>" readonly='readonly'>
                        <input name="_1_<?=$_acao?>_nf_tipoobjetosolipor" type="hidden" value="nf" readonly='readonly'>
                        <input name="_nf_tipocontapagar" type="hidden" value="<?=$_1_u_nf_tipocontapagar?>">
                        <input name="_nf_idformapagamento" type="hidden" value="<?=$_1_u_nf_idformapagamento?>">
                        <?
                    } else {
                        ?>
                        <input name="_1_<?=$_acao?>_nf_idnf" id="idnf" type="hidden" value="<?=$_1_u_nf_idnf; ?>" readonly='readonly'>
                        <input name="_1_<?=$_acao?>_nf_idobjetosolipor" type="hidden" value="<?=$_1_u_nf_idobjetosolipor ?>">
                        <input name="_1_<?=$_acao?>_nf_tipoobjetosolipor" type="hidden" value="<?=$_1_u_nf_tipoobjetosolipor ?>">
                        <?
                    }
                    if($_acao == 'i' and !empty($_GET['idnfentradaxml'])){
                        ?>
                        <input name="_1_<?=$_acao?>_nf_idnfe" type="hidden" value="<?=$_1_u_nf_idnfe ?>" readonly='readonly'>
                        <?
                    }

                    if(!empty($_1_u_nf_objeto)){
                        ?>
                        <input name="_1_<?=$_acao?>_nf_objeto" type="hidden" value="<?=$_1_u_nf_objeto?>" readonly='readonly'>
                        <?
                    }
                    ?>
                </div>

                <!-- Orç. de Compra. / Folha / NF Origem. -->
                <? if((!empty($_1_u_nf_idobjetosolipor) && $_1_u_nf_tipoobjetosolipor == 'cotacao')
                    || (!empty($_1_u_nf_idobjetosolipor) and $_1_u_nf_tipoobjetosolipor == 'rhfolha')
                    || (!empty($_1_u_nf_idobjetosolipor) and $_1_u_nf_tipoobjetosolipor == 'nf')){ ?>
                    <div class="form-group col-xs-6 col-md-2">
                        <label class="text-white">
                            <?
                            if(!empty($_1_u_nf_idobjetosolipor) && $_1_u_nf_tipoobjetosolipor == 'cotacao'){
                                ?>
                                Orç. de Compra
                                <?
                            } elseif(!empty($_1_u_nf_idobjetosolipor) and $_1_u_nf_tipoobjetosolipor == 'rhfolha'){
                                ?>
                                Folha.:
                                <?
                            } elseif(!empty($_1_u_nf_idobjetosolipor) and $_1_u_nf_tipoobjetosolipor == 'nf'){
                                ?>
                                NF Origem
                                <?
                            }
                            ?>
                        </label>
                        <div class="d-flex align-items-center">
                            <?
                            if(!empty($_1_u_nf_idobjetosolipor) && $_1_u_nf_tipoobjetosolipor == 'cotacao'){
                                ?>
                                <label class="alert-warning d-flex align-items-center flex-between form-control">
                                    <?=$_1_u_nf_idobjetosolipor ?>
                                    <a title="Orçamento de Compra" class="fa fa-bars fade pointer hoverazul" href="?_modulo=cotacao&_acao=u&idcotacao=<?=$_1_u_nf_idobjetosolipor ?>" target="_blank"></a>
                                </label>
                                <?
                            } elseif(!empty($_1_u_nf_idobjetosolipor) and $_1_u_nf_tipoobjetosolipor == 'rhfolha'){
                                ?>
                                <label class="alert-warning d-flex align-items-center flex-between form-control">
                                    <?=$_1_u_nf_idobjetosolipor ?>
                                    <a title="Folha de Pagamento" class="fa fa-bars fade pointer hoverazul" href="?_modulo=rhfolha&_acao=u&idrhfolha=<?=$_1_u_nf_idobjetosolipor ?>" target="_blank"></a>
                                </label>

                                <?
                            } elseif(!empty($_1_u_nf_idobjetosolipor) and $_1_u_nf_tipoobjetosolipor == 'nf'){
                                $tiponfc = traduzid('nf', 'idnf', 'tiponf', $_1_u_nf_idobjetosolipor);
                                $nnfec = traduzid('nf', 'idnf', 'nnfe', $_1_u_nf_idobjetosolipor);
                                if(empty($nnfec)){
                                    $nnfec = $_1_u_nf_idobjetosolipor;
                                }
                                if($tiponfc == 'V'){
                                    $modc = "pedido";
                                } else {
                                    $modc = "nfentrada";
                                }
                                ?>
                                <label class="alert-warning d-flex align-items-center flex-between form-control">
                                    <?=$nnfec ?>
                                    <a title="NFe" class="fa fa-bars fade pointer hoverazul" href="?_modulo=<?=$modc ?>&_acao=u&idnf=<?=$_1_u_nf_idobjetosolipor ?>" target="_blank"></a>
                                </label>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                <? } ?>

                <!-- Número NF.: -->
                <div class="form-group col-xs-6 col-md-2">
                    <label for="" class="text-white">
                        Número NF
                    </label>
                    <input class="form-control" id="nnfe" <?=$readonly ?> name="_1_<?=$_acao?>_nf_nnfe" type="text" value="<?=$_1_u_nf_nnfe ?>">
                    <span id="msgbox" class="alert alert-danger numeroNf"></span>
                </div>
                <? if(!empty($_1_u_nf_serie)){ ?>
                    <div class="form-group col-xs-6 col-md-1">
                        <label for="" class="text-white">
                            Série.:
                        </label>
                        <div class="d-flex align-items-center form-control  alert-warning">
                            <label><?=$_1_u_nf_serie ?></label>
                        </div>
                    </div>
                <?
                    $colMdStatus = 1;
                } else {
                    $colMdStatus = 2;
                } ?>

                <!-- Tipo NF -->
                <div class="form-group relative col-xs-6 col-md-2">
                    <label for="" class="text-white">
                        Tipo NF
                    </label>
                    <? if($_GET['_modulo'] == 'nfrdv' && $_acao == 'i'){
                        $_1_u_nf_tipoorc = 'RDV';
                        ?>
                        <input name="_1_<?=$_acao?>_nf_tipoorc" type="hidden" value="<?=$_1_u_nf_tipoorc ?>" readonly='readonly'>
                    <? }
                    
                    if($_1_u_nf_tipoorc == 'RDV'){ ?>
                        <div class="rdv">
                            <label class="alert-warning">RDV</label>
                        </div>
                    <? }

                    if($pagvalmodulo == 'nfentrada'){
                        $fillTipoNf = NfEntradaController::$_nfentrada;
                    } elseif($_GET['_modulo'] == 'comprasrh' || $_GET['_modulo'] == 'comprasrhrestrito'){
                        $fillTipoNf = NfEntradaController::$_rh;
                    } elseif($_GET['_modulo'] == 'comprassocios'){
                        $fillTipoNf = NfEntradaController::$_socios;
                    } elseif($_1_u_nf_tiponf == 'F'){
                        $fillTipoNf = NfEntradaController::$_tipoNfF;
                    } else {
                        $fillTipoNf = NfEntradaController::$_outros;
                    }
                    ?>
                    <input type="hidden" name="_1_<?=$_acao?>_nf_tiponf" value="<?=$_1_u_nf_tiponf ?>">
                    <input type="hidden" name="_nf_tiponf_old" value="<?=$_1_u_nf_tiponf ?>">
                    <? $disabledTipoNf = in_array($_1_u_nf_status, ['CONCLUIDO', 'REPROVADO', 'CANCELADO']) ? 'disabled' : ''; ?>
                    <select name="_1_<?=$_acao?>_nf_tiponf" id="tiponf" vnulo onchange="atributonnfe(this)" class="form-control" <?=$disabledTipoNf?>>
                        <option value=""></option>
                        <? fillselect($fillTipoNf, $_1_u_nf_tiponf); ?>
                    </select>
                </div>

                <!-- Emissão -->
                <div class="form-group col-xs-6 col-md-2">
                    <label for="" class="text-white">
                        Emissão
                    </label>
                    <? if(empty($_1_u_nf_dtemissao)){
                        $_1_u_nf_dtemissao = date("d/m/Y H:i:s");
                    }

                    if(!empty($_1_u_nf_idnf)){
                        $resc100 = NfEntradaController::buscarSpedC100($_1_u_nf_idnf, "'ATIVO','CORRIGIDO'");
                        $qtdc100 = count($resc100);
                        $idSpedC100 = $resc100['idspedc100'];
                    } else {
                        $qtdc100 = 0;
                    }
                    if($qtdc100 > 0){
                        ?>
                        <div class="d-flex align-items-center form-control  alert-warning">
                            <label><?=$_1_u_nf_dtemissao?></label>
                        </div>
                        <input name="_1_<?=$_acao?>_nf_dtemissao" class="calendario form-control" id="fdata" type="hidden" value="<?=$_1_u_nf_dtemissao?>">
                        <?
                    } else {
                        ?>
                        <input name="_1_<?=$_acao?>_nf_dtemissao" class="calendario form-control" <? if($_1_u_nf_status == 'CONCLUIDO'){ ?> disabled="disabled" <? } ?> id="fdata" type="text" value="<?=$_1_u_nf_dtemissao?>">
                        <?
                        if($_1_u_nf_status == 'CONCLUIDO'){ ?>
                            <input name="_1_<?=$_acao?>_nf_dtemissao" type="hidden" value="<?=$_1_u_nf_dtemissao?>">
                            <?
                        }
                    }
                    ?>
                </div>

                <!-- Status -->
                <div class="form-group col-xs-6 col-md-<?=$colMdStatus ?>">
                    <label for="" class="text-white">
                        Status
                    </label>
                    <input name="statusant" type="hidden" value="<?=$_1_u_nf_status ?>" readonly='readonly'>
                    <? if($_acao == 'u'){
                        $_1_u_nf_status = $_1_u_nf_status;
                    } elseif(empty($_1_u_nf_status)) {
                        $_1_u_nf_status = 'INICIO';
                    }
                    ?>
                    <input name="_1_<?=$_acao?>_nf_status" type="hidden" value="<?=$_1_u_nf_status ?>">
                    <span>
                        <? $rotulo = getStatusFluxo($pagvaltabela, 'idnf', $_1_u_nf_idnf) ?>
                        <label class="d-flex align-items-center form-control alert-warning" title="<?=$_1_u_nf_status ?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
                        <input id="transportadora" type="hidden" value="<?=traduzid("pessoa", "idpessoa", " IFNULL(nomecurto, nome)", $_1_u_nf_idtransportadora, false) ?>">
                        <?
                        $contatpessoa = NfEntradaController::buscarContatoPessoa($_1_u_nf_idpessoa);
                        ?>
                        <input id="contatopessoa" type="hidden" value="<?=$contatpessoa ?>">
                        <?
                        if($_1_u_nf_idpessoa){
                            $endereco = NfEntradaController::listarEnderecosParaEntregaPessoa($_1_u_nf_idpessoa);
                        ?>
                            <input id="localretirada" type="hidden" value="<?=$endereco['endereco']?>">
                        <? } ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <? 
            if ($_1_u_nf_tiponf == 'T') { ?>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Filial:</label>
                        <div class="input-group d-flex w-100">                                    
                            <? if (!empty($_1_u_nf_idempresafat) and ($_1_u_nf_status == 'CONCLUIDO' || $_1_u_nf_status == 'CANCELADO' || $_1_u_nf_status == 'REPROVADO' || $_acao=='i') )  { ?>
                                <input name="_1_<?= $_acao ?>_nf_idempresafat" type="hidden" value="<?= $_1_u_nf_idempresafat ?>">
                                <label class="alert-warning"><?= traduzid('empresa', 'idempresa', 'nomefantasia', $_1_u_nf_idempresafat); ?></label>
                            <? } else {
                                ?>
                                <select class="size20" name="_1_<?= $_acao ?>_nf_idempresafat" >
                                    <option value=""></option>
                                    <? fillselect(PedidoController::buscarFilial(), $_1_u_nf_idempresafat); ?>
                                </select>
                            <? } ?>                                    
                        </div>
                    </div>
                </div>
                <? 
                if($_acao == 'i' and !empty($_GET['idnfentradaxml'])){        
                    ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Pagamento:</label>
                            <div class="input-group d-flex w-100">                                    
                            <input class="size20 ui-autocomplete-input" id="forma_pag" cbvalue='<?=$_1_u_nf_idformapagamento?>' name="_1_<?=$_acao?>_nf_idformapagamento" vnulo value="<?=$arrayFormaPagamento[$_1_u_nf_idformapagamento]['descricao']?>" vnulo>                                
                            </div>
                        </div>
                    </div>
                <? 
                }    
            } 
            ?>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Fornecedor:</label>
                    <div class="input-group d-flex w-100">
                        <?
                        if ($_1_u_nf_status == 'CONCLUIDO' || $_1_u_nf_status == 'CANCELADO' || $_1_u_nf_status == 'REPROVADO' || $_1_u_nf_status == 'REPROVADO' || $_1_u_nf_tipoorc == 'COBRANCA') { ?>
                            <input id="idpessoa" name="_1_<?= $_acao ?>_nf_idpessoa" type="hidden" value="<?= $_1_u_nf_idpessoa ?>" cbvalue="<?= $_1_u_nf_idpessoa ?>">
                            <? echo traduzid("pessoa", "idpessoa", "nome", $_1_u_nf_idpessoa); ?>
                            <a class="fa fa-bars pointer hoverazul pd-left-top" title="Cadastro de Fornecedores" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idpessoa ?>')"></a>
                        <?
                        } else {
                            if (empty($_1_u_nf_idpessoa)){
                                $dfat = "";
                            } else {
                                $dfat = "disabled='disabled'";
                            }
                            if($_acao == 'u'){
                                $desabilitado = "desabilitado";
                            }
                            ?>
                            <div class="col-xs-8 alinhamento-esquerda">
                                <input class="<?=$desabilitado?> size22" <?=$dfat ?> id="idpessoa" type="text" name="_1_<?=$_acao?>_nf_idpessoa" vnulo cbvalue="<?=$_1_u_nf_idpessoa ?>" value="<?=$arrayCliente[$_1_u_nf_idpessoa]["nome"]?>" vnulo>
                            </div>
                            <div class="col-xs-4">
                                <? if(!empty($_1_u_nf_idpessoa)){ ?>
                                    <a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_nf_idpessoa ?>')"></a>
                                <? }

                                if(!empty($_1_u_nf_idpessoa)){ ?>
                                    <a id="altcliente" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarclientefat(this);" title="Alterar cliente de faturamento."></a>
                                    <?
                                }

                                if(!empty($_1_u_nf_remcnpj) and ($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'O')){
                                    $cpfcnpj = traduzid('pessoa', 'idpessoa', 'cpfcnpj', $_1_u_nf_idpessoa);
                                    if($_1_u_nf_remcnpj != $cpfcnpj){
                                        ?>
                                        <i title='O CPF/CNPJ do cadastro não é igual ao da nota' class='fa fa-exclamation-triangle laranja btn-lg pointer'></i>
                                        <?
                                    }
                                }elseif(!empty($_1_u_nf_emitcnpj) and $_1_u_nf_tiponf == 'T'){
                                    $cpfcnpj = traduzid('pessoa', 'idpessoa', 'cpfcnpj', $_1_u_nf_idpessoa);
                                    if($_1_u_nf_emitcnpj != $cpfcnpj){
                                        ?>
                                        <i title='O CPF/CNPJ do cadastro não é igual ao do CTe' class='fa fa-exclamation-triangle laranja btn-lg pointer'></i>
                                        <?
                                    }
                                }
                                ?>
                            </div>
                        <? } ?>
                    </div>
                </div>
                <div class="col-xs-12 alinhamento-esquerda-20" style="font-size: 11px !important;">
                    <? if(isset($arrayCliente[$_1_u_nf_idpessoa]["razaosocial"]) && $arrayCliente[$_1_u_nf_idpessoa]["razaosocial"]){ ?>
                        <div class="form-group col-xs-6">
                            <label>Razão Social:</label>
                            <div class="text-uppercase razaosocial input-group">
                                <?=$arrayCliente[$_1_u_nf_idpessoa]["razaosocial"]?>
                            </div>
                        </div>
                    <? } ?>
                    <div class="form-group col-xs-6">
                        <label>E-mail:</label>
                        <div class="text-uppercase razaosocial input-group">
                            <?=$arrayCliente[$_1_u_nf_idpessoa]["email"]?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 alinhamento-esquerda-20" style="font-size: 11px !important;">
                    <? if(isset($arrayCliente[$_1_u_nf_idpessoa]["cpfcnpj"]) && $arrayCliente[$_1_u_nf_idpessoa]["cpfcnpj"]){ ?>
                        <div class="form-group col-xs-6">
                            <label>CPF/CNPJ:</label>
                            <div class="text-uppercase cpfcnpj input-group col-md-12">
                                <?=formatarCPF_CNPJ($arrayCliente[$_1_u_nf_idpessoa]["cpfcnpj"]) ?>
                            </div>
                        </div>
                    <?
                    }

                    if(!empty($arrayCliente[$_1_u_nf_idpessoa]["regimetrib"])){
                        $regime = $arrayCliente[$_1_u_nf_idpessoa]["regimetrib"];
                        if($regime == 1){
                            $sregime = 'Simples Nacional';
                        } elseif($regime == 2){
                            $sregime = 'Simples Nacional - Excesso de Sublimite da Receita Bruta';
                        } elseif($regime == 3){
                            $sregime = 'Regime Normal';
                        } elseif($regime == 4){
                            $sregime = 'Simples Nacional - MEI';
                        }
                    ?>
                        <div class="form-group col-md-6">
                            <label>Regime Tributário:</label>
                            <div class="input-group text-uppercase col-md-12">
                                <?=strtoupper($sregime) ?>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </div>

            <?
            if(!empty($_1_u_nf_idpessoa)){
                $observacaonfp = traduzid("pessoa", "idpessoa", "obs", $_1_u_nf_idpessoa);
                if(!empty($observacaonfp)){
                    ?>
                    <div class="col-md-6">
                        <?
                        if($_1_u_nf_tiponf == 'E'){
                            ?>
                            <div class="form-group">
                                <label>Tipo:</label>
                                <div class="input-group">
                                    <select name="_1_<?=$_acao?>_nf_conssecionaria" vnulo>
                                        <option value=""></option>
                                        <? fillselect(NfEntradaController::$_conssecionaria, $_1_u_nf_conssecionaria); ?>
                                    </select>
                                </div>
                            </div>
                        <?
                        }
                        ?>
                        <div class="form-group">
                            <label>Observação:</label>
                            <div class="input-group alert alert-warning text-uppercase" role="alert">
                                <?=str_replace(chr(13), "<br>", $observacaonfp) ?>
                            </div>
                        </div>
                    </div>
                    <?
                }
            }
            ?>
        </div>
    </div>
</div>

<? 
    if (!empty($_1_u_nf_idnf)) {
        $qtdcte = count($VinculadasNF);
        if ($qtdcte > 0) {
        ?>

    <div class="row">
        <div class="panel panel-default w-100" style="font-size:12px">
            <div class="panel-heading">Nota(s) Fiscal(is) Vinculada(s)</div>
            <div class="panel-body">
                <table class="table table-striped planilha col-md-6">
                 <tr>
                    <th>Nfe</th>
                    <th>Nome</th>
                    <th>Emissão</th>
                    <th>Status</th>
                    <th>Valor </th>
                    <th></th>
                </tr>
                <?
                $totalcte = 0;
                foreach($VinculadasNF as $item) {
                    if($item['tiponf']=='V'){
                        $modulonf='pedido';
                    }else{
                        $modulonf='nfentrada';
                    }
                ?>
                    <tr>
                        <td><?= $item['nnfe'] ?></td>
                        <td><?= $item['nome'] ?></td>
                        <td><?= $item['emissao'] ?></td>
                        <td><?= $item['status'] ?></td>
                        <td>
                            <?= number_format(tratanumero($item['total']), 2, ',', '.'); ?>
                        <td>
                            <a class="fa fa-bars pointer hoverazul" title="NF" onclick="janelamodal('?_modulo=<?=$modulonf?>&_acao=u&idnf=<?= $item['idnf'] ?>')"></a>
                        </td>                                                  
                    </tr>
                <?
                }
                ?>                
                </table>
            </div>
        </div>
    </div>
<?   
        } 
    } //if(!empty($_1_u_nf_idnfe)){
?>

<?
if($_acao == 'u'){
    $qtdConversaoMoeda = NfEntradaController::buscarSeExisteConversaoMoeda($_1_u_nf_idnf);
    $listarProdutosCadastrados = NfEntradaController::listarItensCadastrados($_1_u_nf_idnf, "$_1_u_nf_idobjetosolipor");
    $qtdProdutosCadastrados = count($listarProdutosCadastrados);

    //busca os itens sem cadastro
    $listarProdutoSemCadastro = NfEntradaController::listarItensSemCadastro($_1_u_nf_idnf);
    $qtdProdutosSemCadastro = count($listarProdutoSemCadastro);

    $qtdTotalProdutos = $qtdProdutosCadastrados + $qtdProdutosSemCadastro;
    ?>
    <div class="bckwh">
        <div class="panelAbas div-m" id="mainPanel">
            <ul class="pd-top nav nav-tabs" id="Tab_lp" role="tablist">
                <li role="presentation panel-heading" class="tabs-container li_nfentrada active" value="nfentrada_itens">
                    <a href="#nfentrada_itens" class="cinzaclaro define" role="tab" data-toggle="tab" style="gap: 6px; margin: 0;">
                        <span>Itens</span>
                        [ <?=$qtdTotalProdutos ?> ]
                    </a>
                </li>

                <li role="presentation panel-heading" class="tabs-container li_nfentrada" value="nfentrada_pagamento">
                    <a href="#nfentrada_pagamento" class="cinzaclaro define" role="tab" data-toggle="tab">
                        <? if($_1_u_nf_geracontapagar == 'Y'){
                            $rescx = PedidoController::buscarNfconfpagarPorIdnf($_1_u_nf_idnf);
                            $qtdparcelas = count($rescx);
                            if($qtdparcelas < 1 || empty($_1_u_nf_idformapagamento)){
                                ?>
                                <font color="red" style="padding : 0px 5px 0px 10px;">Pagamento <span class="btn fa fa-exclamation-circle" style="padding: 0px;"></span></font>
                                <?
                            } else {
                                ?>
                                Pagamento
                                <?
                            }
                        } else {
                            ?>
                            Pagamento
                            <?
                        }
                        ?>
                    </a>
                </li>

                <? if($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'T' || $_1_u_nf_tiponf == 'O'){ ?>
                    <li role="presentation panel-heading" class="tabs-container li_nfentrada" value="nfentrada_controlenf">
                        <a href="#nfentrada_controlenf" class="cinzaclaro define" role="tab" data-toggle="tab">
                            <?
                            if(empty($_1_u_nf_xmlret) && ($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'T' || $_1_u_nf_tiponf == 'O')){
                                ?>
                                <font color="red"> Controles NF <span class="btn fa fa-exclamation-circle" style="padding: 0px;"></span></font>
                                <?
                            } else {
                                ?>
                                Controles NF
                                <?
                            }
                            ?>
                        </a>
                    </li>
                    <?
                }

                if($_1_u_nf_geracontapagar == "Y"){ ?>
                    <input name="_nf_idformapagamentoant" type="hidden" value="<?=$_1_u_nf_idformapagamento?>">
                    <?
                }
                
                $arrTipoNf = array('C', 'S', 'T', 'R', 'D', 'M', 'B', 'O');
                if(in_array($_1_u_nf_tiponf, $arrTipoNf)){
                ?>
                    <li role="presentation panel-heading" class="tabs-container li_nfentrada" value="nfentrada_conferencia">
                        <a href="#nfentrada_conferencia" class="cinzaclaro define" role="tab" data-toggle="tab">Conferência</a>
                    </li>
                <? } ?>

                <? if($_1_u_nf_tiponf == 'T'){ ?>
                    <li role="presentation panel-heading" class="tabs-container li_nfentrada" value="nfentrada_recebimento">
                        <a href="#nfentrada_recebimento" class="cinzaclaro define" role="tab" data-toggle="tab">Recebimento</a>
                    </li>
                <? } ?>

                <li role="presentation panel-heading" class="tabs-container li_nfentrada" value="nfentrada_logistica">
                    <a href="#nfentrada_logistica" class="cinzaclaro define" role="tab" data-toggle="tab">Informação Logística</a>
                </li>

                <?
                if($_1_u_nf_moedainternacional == 'Y'){ ?>
                    <li role="presentation panel-heading" class="tabs-container li_nfentrada" value="nfentrada_impostos">
                        <a href="#nfentrada_impostos" class="cinzaclaro define" role="tab" data-toggle="tab">Impostos</a>
                    </li>
                <? } 
                
                if (!empty($_1_u_nf_idnf) && $_1_u_nf_tipocontapagar == 'D' && $_1_u_nf_tiponf != 'O') {
                    if ($_1_u_nf_tiponf != 'C') {
                        $listarRateio = NfEntradaController::buscarRateioNfItemProdserv($_1_u_nf_idnf);
                        $qtdrateio = count($listarRateio);
                        if ($qtdrateio > 0) {
                            $rateio = 'CONCLUIDO';
                            $color = "green";
                            foreach ($listarRateio as $_daddosRateio) {
                                if (empty($_daddosRateio['idrateioitemdest']) && !empty($_daddosRateio['idnfitem'])) {
                                    $rateio = 'PENDENTE';
                                    $color = "red";
                                }
                            }
                        }
                    } elseif ($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'O') {

                        if (NfEntradaController::buscarNfitemContaItem($_1_u_nf_idnf) > 0) {
                            $rateiopendente = "Y";
                        }

                        $listarContaItemRateio = NfEntradaController::buscarNfitemContaItemRateio($_1_u_nf_idnf);
                        $virg = '';

                        foreach ($listarContaItemRateio['dados'] as $cirateio) {
                            $listarNfItemSolcom = NfEntradaController::buscarNfItemSolcom($cirateio['idnfitem']);
                            $qtdcom = 0;
                            if ($listarNfItemSolcom['qtdLinhas'] > 0) // tem solicitação de compra
                            {
                                foreach ($listarNfItemSolcom['dados'] as $nfItemSolcom) {
                                    $qtdcom = $qtdcom + $nfItemSolcom['qtdcom'];
                                }
                            }

                            if ($qtdcom < $cirateio['qtd']) {
                                $consumodiasloterateio = $cirateio['tempoconsrateio'];
                                $listarLoteMeio = NfEntradaController::buscarRateio($cirateio['idprodserv'], $cirateio['idunidadeest'], $consumodiasloterateio);

                                if ($listarLoteMeio['qtdLinhas'] < 1 || (!empty($cirateio['idrateioitemdest']))) {
                                    $rateiopendente = "Y";
                                    $idprodservs .= $virg . $cirateio['idprodserv'];
                                    $virg = ',';
                                }
                            }
                        }
                        
                        if ($rateiopendente == "Y") {
                            $rateio = 'CONCLUIDO';
                            $color = "green";

                            if (!empty($idprodservs) and  $listarContaItemRateio['qtdLinhas'] > 0) {
                                $listarRateioProdserv = NfEntradaController::buscarNfitemContaItemRateio($_1_u_nf_idnf, $idprodservs);
                                foreach ($listarRateioProdserv['dados'] as $rateioProdserv) {
                                    if (empty($rateioProdserv['idrateioitemdest'])) {
                                        $rateio = 'PENDENTE';
                                        $color = "red";
                                    }
                                }
                            }

                            $listarContaItemRateio = NfEntradaController::buscarNfContaItemRateio($_1_u_nf_idnf);
                            foreach ($listarContaItemRateio['dados'] as $contaItemRateio) {
                                if (empty($contaItemRateio['idrateioitemdest'])) {
                                    $rateio = 'PENDENTE';
                                    $color = "red";
                                }
                            }
                        }
                    }
                    ?>
                    <li role="presentation panel-heading" class="tabs-container li_nfentrada" value="nfentrada_rateio">
                        <a href="#nfentrada_rateio" class="cinzaclaro define" role="tab" data-toggle="tab">
                            <? if($rateio == 'PENDENTE') { ?>
                                <font color="red" style="padding : 0px 5px 0px 10px;">Rateio <span class="btn fa fa-exclamation-circle" style="padding: 0px;"></span></font>
                            <? } else { ?>
                                Rateio
                            <? } ?>
                        </a>
                    </li>                    
                    <?
                }
                ?>

                <li role="presentation panel-heading" class="tabs-container li_anexo" value="nfentrada_anexo">
                    <a href="#nfentrada_anexo" class="cinzaclaro define" role="tab" data-toggle="tab">
                        Anexos
                    </a>
                </li>

                <li role="presentation panel-heading" class="tabs-container li_evento" value="nfentrada_evento">
                    <a href="#nfentrada_evento" class="cinzaclaro define" role="tab" data-toggle="tab">
                        Eventos
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!------------------------  Itens ------------------------>
                <div role="tabpanel" class="tab-pane fade active in" id="nfentrada_itens">
                    <div class="panel-body">
                        <div class="panel panel-default " style="margin-top: 0 !important; border: 0px !important">
                            <?
                            if(!empty($_1_u_nf_idnf) && $_1_u_nf_tipoorc == "COBRANCA" && $_1_u_nf_tipocontapagar == 'C'){
                                $cobranca = RateioItemDestController::listarRateioitemdestnfPorIdnfAgrupado($_1_u_nf_idnf);
                                if(!empty($cobranca)){ ?>
                                    <div class="d-flex align-items-center" style="float: right; width: 8%; margin-bottom: 2%;">
                                        <label class="alert-warning d-flex align-items-center flex-between form-control" style="color:green  !important;">COBRANÇA
                                            <span class="fa fa-bars pointer hoverazul" title="Valor R$ <?=number_format($cobranca['valor'], 2, '.', ''); ?>" onclick="editarcobranca(<?=$_1_u_nf_idnf ?>)"></span>
                                        </label>
                                    </div>
                                <?
                                }
                            } elseif(!empty($_1_u_nf_idobjetosolipor) && $_1_u_nf_tipoobjetosolipor == 'nf' && $_1_u_nf_tipoorc == "COBRANCA"){
                                $cobranca = RateioItemDestController::listarRateioitemdestnfPorIdnfAgrupado($_1_u_nf_idobjetosolipor);
                                if(!empty($cobranca)){ ?>
                                    <div class="d-flex align-items-center" style="float: right; width: 8%; margin-bottom: 2%;">
                                        <label class="alert-warning d-flex align-items-center flex-between form-control" style="color:green  !important;">COBRANÇA
                                            <span class="fa fa-bars pointer hoverazul" title="Valor R$ <?=number_format($cobranca['valor'], 2, '.', ''); ?>" onclick="editarcobranca(<?=$_1_u_nf_idobjetosolipor ?>)"></span>
                                        </label>
                                    </div>
                                    <?
                                }
                            }
                            ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-12  alinhamento-topo-body">
                                        <div class="panel panel-default topMarginPanel" style="margin-top: 0 !important;">
                                            <div class="panel-heading" style="padding: 10px;">
                                                Cadastrado [ <?=$qtdProdutosCadastrados ?> ] 
                                                <? if($_1_u_nf_idfinalidadeprodserv){ ?>
                                                    - Finalidade: <?=traduzid('finalidadeprodserv', 'idfinalidadeprodserv', 'finalidadeprodserv', $_1_u_nf_idfinalidadeprodserv)?>
                                                <? 
                                                }
                                                ?>
                                                <span style="float: right;margin-top: -4px;">
                                                    <? 
                                                    if(($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'T' || $_1_u_nf_tiponf == 'O') && !empty($_1_u_nf_xmlret)){ ?>
                                                        <button type="button" class="btn btn-secondary btn-sm atalhoIcone" title="Danfe" onclick="janelamodal('../inc/nfe/sefaz4/func/printDANFE.php?idnotafiscal=<?=$_1_u_nf_idnf ?>')">
                                                            <i class="fa fa-print fa-1x"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-secondary btn-sm atalhoIcone" title="Baixar xml NFe" onclick="janelamodal('../inc/nfe/sefaz3/func/geraarquivo.php?idnotafiscal=<?=$_1_u_nf_idnf ?>')">
                                                            <i class="fa fa-download"></i>
                                                        </button>

                                                        <?
                                                        if($_1_u_nf_envionfe == "CONCLUIDA"){
                                                            ?>
                                                            <button type="button" class="btn btn-secondary btn-sm atalhoIcone" onclick="devolvernf();" title="Devolver NF">
                                                                <i class="fa fa-mail-reply"></i>
                                                            </button>
                                                        <? } 
                                                    }

                                                    $anexos = NfEntradaController::buscarAnexo($_1_u_nf_idnf, "'nf', 'cotacaoforn'", 'ANEXO');
                                                    if(count($anexos) > 0) {
                                                        ?>
                                                        <button type="button" class="btn btn-secondary btn-sm atalhoIcone mostrarAnexos" title="Anexos" data-target="webuiPopover0">
                                                            <i class="fa fa-paperclip"></i>
                                                        </button>
                                                        <div class="webui-popover-content">
                                                            <br />
                                                            <table class="table table-striped planilha">
                                                                <tr>
                                                                    <th>Anexo - Proposta do Fornecedor</th>
                                                                </tr>                                                            
                                                                <?                                                                
                                                                foreach($anexos['cotacaoforn'] as $_anexo)
                                                                {
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <a onclick="janelamodal('upload/<?=$_anexo['caminho']?>');"><?=strtoupper($_anexo['nome'])?></a>                                                                            
                                                                        </td>
                                                                    </tr>   
                                                                <?
                                                                }
                                                                ?> 
                                                                <tr><td>&nbsp;</td></tr>
                                                                <tr>
                                                                    <th>Arquivos Anexos</th>
                                                                </tr>                                                            
                                                                <?
                                                                foreach($anexos['nf'] as $_anexo)
                                                                {
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <a onclick="janelamodal('upload/<?=$_anexo['caminho']?>');"><?=strtoupper($_anexo['nome'])?></a>                                                                            
                                                                        </td>
                                                                    </tr>   
                                                                <?
                                                                }
                                                                ?>
                                                            </table>
                                                        </div>
                                                    <? } ?>
                                                </span>                                                
                                            </div>
                                            <?
                                            $alturaCadastrado = ($qtdProdutosCadastrados > 0) ? 130 + (20 * $qtdProdutosCadastrados) + 'px' : '130px';
                                            $alturaNaoCadastrado = ($qtdProdutosSemCadastro > 0) ? 130 + (20 * $qtdProdutosCadastrados) + 'px' : '130px';

                                            if(($qtdProdutosCadastrados > 0 && $_1_u_nf_status == 'CONCLUIDO') || ($_1_u_nf_status <> 'CONCLUIDO')){
                                                $item1 = $listarProdutosCadastrados[0];
                                                $internacional = (in_array('USD', $item1) || in_array('EUR', $item1)) ? true : false;
                                                $lote2 = [];
                                                foreach ($listarProdutosCadastrados as $_lote){
                                                    if(!empty($_lote['idlote2'])){
                                                        array_push($lote2, $_lote['idlote2']);
                                                    }
                                                }

                                                $lote2 = implode(", ", $lote2);
                                                ?>
                                                <div class="panel-body tbItensCadastradosDiv">
                                                    <div class="ag-theme-balham" style="overflow-x: auto; height: <?=$alturaCadastrado?>; width: 100%;" id="tbItensCadastrado"></div>
                                                    <input type="hidden" id="subtotalCadastrado" value="">
                                                    <? $arrayMostrarBotaoAdicionar = ['INICIO', 'ENVIADO', 'RESPONDIDO', 'AUTORIZADA', 'AUTORIZADO', 'PREVISAO'];
                                                    if(in_array($_1_u_nf_status, $arrayMostrarBotaoAdicionar) && $_1_u_nf_app == 'N'){ ?>
                                                        <i id="addRowCadastrado" tipo="cadastrado" class="fa fa-plus-circle fa-2x verde btn-lg pointer addRow" title="Inserir novo Item" <?=$readonly ?>></i>
                                                    <? } ?>
                                                </div>
                                                <?
                                            }
                                            ?>
                                            <input type="hidden" class="addNovaLinhaCadastroContador" value="1">
                                        </div>
                                        <div class="panel panel-default topMarginPanel tbItensNaoCadastradosDiv">
                                            <div class="panel-heading" style="padding: 10px;" data-toggle="collapse" href="#naocadastradodivcollapse">Não Cadastrado [ <?=$qtdProdutosSemCadastro?> ]</div>
                                            <div id="naocadastradodivcollapse">
                                                <?
                                                if(($qtdProdutosSemCadastro > 0 && $_1_u_nf_status == 'CONCLUIDO') || ($_1_u_nf_status <> 'CONCLUIDO')){ ?>
                                                    <div class="panel-body">
                                                        <div class="ag-theme-balham" style="overflow-x: auto; height: <?=$alturaNaoCadastrado?>; width: 100%;" id="tbItensNaoCadastrados"></div>
                                                        <input type="hidden" id="subtotalNaoCadastrado" value="">
                                                        <? if($_1_u_nf_status <> 'CONCLUIDO' && $_1_u_nf_status <> 'CANCELADO' && $_1_u_nf_app == 'N'){ ?>
                                                            <i id="addRowNaoCadastrado" tipo="naocadastrado" class="fa fa-plus-circle fa-2x verde btn-lg pointer addRow" title="Inserir novo Item"></i>
                                                        <? } ?>
                                                    </div>
                                                <? } ?>
                                            </div>
                                            <input type="hidden" class="addNovaLinhaNaoCadastroContador" value="9000">
                                        </div>
                                        <div class="topMarginPanel">
                                            <table id="tbItens" class="table table-striped planilha">
                                                <tfoot>
                                                    <?
                                                    //Tota e Subtotal
                                                    if($qtdTotalProdutos > 0){
                                                        $vtotalicms = $vtotalicms + $_1_u_nf_vlricmscpl;
                                                        if(!empty($fretex)){
                                                            $_1_u_nf_frete = $fretex;
                                                        }

                                                        //Acrescentar o campo Frete para visualiação - Lidiane (30-04-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315257
                                                        $sqlmFrete = "select modfrete,frete from nf where idnf = " . $_1_u_nf_idnf;
                                                        $resmFrete = d::b()->query($sqlmFrete) or die("erro ao buscar notas vinculadas:\n" . mysqli_error(d::b()) . "\n" . $sqlm);

                                                        $rmFrete = mysqli_fetch_assoc($resmFrete);
                                                        $modfrete = $rmFrete['modfrete'];
                                                        $frete = $rmFrete['frete'];

                                                        if($_1_u_nf_tiponf == 'D' ||  $_1_u_nf_tiponf == 'E'){
                                                            $colspan = 11;
                                                        } elseif($_1_u_nf_tiponf == 'R'){
                                                            $colspan = 30;
                                                            $tdColspan = '';
                                                        } elseif($_1_u_nf_tiponf == 'S'){
                                                            $colspan = 9;
                                                        } elseif($_1_u_nf_tiponf == 'M'){
                                                            $colspan = 14;
                                                        } elseif($_1_u_nf_tiponf == 'T'){
                                                            $colspan = 12;
                                                        } elseif($_1_u_nf_tiponf == 'B'){
                                                            $colspan = 14;
                                                        } else {
                                                            $colspan = 11;
                                                        }

                                                        ?>
                                                        <tr style="background-color: #FFFFFF;">
                                                            <? if($qtdConversaoMoeda > 0){ ?>
                                                                <td colspan="<?=$colspan ?>" align="right" class="td-padding">Sub-Total:<b><?=$moeda ?> </b></td>
                                                            <? } else { ?>
                                                                <td colspan="<?=$colspan - 1 ?>" align="right" class="td-padding">Sub-Total:<b><?=$moeda ?> </b></td>
                                                            <? } ?>
                                                            <td class="alinhar-direita td-padding" id="totalSubTotal" style="width: 8% !important;">
                                                                <?
                                                                if($vnaocobrar > 0){
                                                                    echo (number_format(tratanumero($vsubtotal + $vnaocobrar), 2, ',', '.'));
                                                                } else {
                                                                    echo (number_format(tratanumero($vsubtotal), 2, ',', '.'));
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="td-padding"></td>
                                                            <td colspan="3" class="td-padding"></td>
                                                        </tr>
                                                        <? if(in_array($_1_u_nf_tiponf, ['C', 'B', 'O', 'M','F'])){ ?>
                                                            <tr>
                                                                <?
                                                                //O cliente conseguir mudar a opção de Frete - Lidiane - 02/06/2020
                                                                //sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=324500
                                                                if($_1_u_nf_status == 'CONCLUIDO'){
                                                                    $statusfrete = "disabled='disabled'";
                                                                }

                                                                $colspanFrete = ($qtdConversaoMoeda > 0) ? $colspan : $colspan - 1;
                                                                ?>
                                                                <td title="<?=NfEntradaController::buscarTituloFrete() ?>" align="right" colspan="<?=$colspanFrete ?>" class="td-padding">
                                                                    Frete:
                                                                    <select class="size8 height-18" name="_1_<?=$_acao?>_nf_modfrete" <?=$statusfrete ?>>
                                                                        <? fillselect(NfEntradaController::buscarTipoFrete(), $modfrete); ?>
                                                                    </select>

                                                                    <?
                                                                    if ($modfrete == '1') {
                                                                        if (!empty($_1_u_nf_idnfe)) {
                                                                            $ctesVinculadasNF = NfEntradaController::buscarCtePorIdNfe(substr($_1_u_nf_idnfe, 3), $_1_u_nf_idnf);
                                                                        } else {
                                                                            $ctesVinculadasNF = NfEntradaController::buscarCte($_1_u_nf_idnf);
                                                                        }
        
                                                                        $qtdcte = empty($ctesVinculadasNF) ? 0 : count($ctesVinculadasNF);
                                                                        if ($_1_u_nf_total > 0) {
                                                                            if ($qtdcte > 0) {
                                                                                $iconcte = 'fa-bars azul';
                                                                            } else {
                                                                                $iconcte = 'fa-plus-circle verde';
                                                                            }
                                                                            ?>
                                                                            <i id="btn-modal-frete" class="fa <?= $iconcte ?> fa-1x  pointer" title="Gerar Programação de CTe"></i>
                                                                            <?
                                                                        }
                                                                    }

                                                                    if(!empty($_1_u_nf_frete) && empty($frete)){
                                                                        $frete = $_1_u_nf_frete;
                                                                    } elseif(empty($frete)){
                                                                        $frete = 0.00;
                                                                    } else {
                                                                        $frete = $frete;
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td class="alinhar-direita td-padding">
                                                                    <input <?=$readonly ?> class="alinhar-direita height-18" name="_1_<?=$_acao?>_nf_frete" size="8" type="text" value="<?=$frete ?>" vdecimal onchange="atualizafrete(this, <?=$_1_u_nf_idnf; ?>)">
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                        <? }

                                                        if($internacional && $_1_u_nf_moedainternacional == 'Y'){
                                                            $rowCompraInt = NfEntradaController::buscarFreteInternacional($_1_u_nf_idnf);
                                                            ?>
                                                            <tr>
                                                                <td colspan="<?=$colspan ?>" class="alinhar-direita mt11mf5 td-padding">
                                                                    Frete Importação Nacional <b>BRL</b>:
                                                                </td>
                                                                <td class="td-padding">
                                                                    <div class="d-flex text-right">
                                                                        <input name="_freteimpnacional_old" type="hidden" value="<?=$_1_u_nf_freteimpnacional; ?>">
                                                                        <input <?=$readonly ?> class="alinhar-direita height-18" style="width: 70px;" name="_1_<?=$_acao?>_nf_freteimpnacional" size="8" type="text" value="<?=$_1_u_nf_freteimpnacional; ?>" vdecimal onchange="atualizarValorCompra(this, <?=$_1_u_nf_idnf; ?>, 'freteimpnacional')">
                                                                        <? if(empty($rowCompraInt['idnfimpnac'])){ ?>
                                                                            <i class="fa fa-plus-circle fa-1x verde pointer mt11mf5" onclick="criarNfentrada(<?=$_1_u_nf_idnf ?>, 'impnac', '<?=$_1_u_nf_freteimpnacional ?>')" title="Gerar Programação de Frete Importação Nacional"></i>
                                                                        <? } else { ?>
                                                                            <a title="Frete Importação Nacional" class="fa fa-bars fade pointer hoverazul mt11mf5" href="?_modulo=nfentrada&_acao=u&idnf=<?=$rowCompraInt['idnfimpnac']?>" target="_blank"></a>
                                                                        <? } ?>
                                                                    </div>
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="<?=$colspan ?>" class="alinhar-direita mt11mf5 td-padding">Frete Importação Internacional <b>BRL</b>:</td>
                                                                <td class="td-padding">
                                                                    <div class="d-flex text-right">
                                                                        <input name="_freteimpinternacional_old" type="hidden" value="<?=$_1_u_nf_freteimpinternacional; ?>">
                                                                        <input <?=$readonly ?> class="alinhar-direita height-18" style="width: 70px;" name="_1_<?=$_acao?>_nf_freteimpinternacional" size="8" type="text" value="<?=$_1_u_nf_freteimpinternacional ?>" vdecimal onchange="atualizarValorCompra(this, <?=$_1_u_nf_idnf; ?>, 'freteimpinternacional')">
                                                                        <? if(empty($rowCompraInt['idnfimpint'])){ ?>
                                                                            <i class="fa fa-plus-circle fa-1x verde  pointer mt11mf5" onclick="criarNfentrada(<?=$_1_u_nf_idnf ?>, 'impint', '<?=$_1_u_nf_freteimpinternacional ?>')" title="Gerar Programação de Frete Importação Internacional "></i>
                                                                        <? } else { ?>
                                                                            <a title="Frete Importação Internacional" class="fa fa-bars fade pointer hoverazul mt11mf5" href="?_modulo=nfentrada&_acao=u&idnf=<?=$rowCompraInt['idnfimpint']?>" target="_blank"></a>
                                                                        <? } ?>
                                                                    </div>
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="<?=$colspan ?>" class="alinhar-direita mt11mf5 td-padding">Armazenagem Aeroportuária <b>BRL</b>:</td>
                                                                <td class="td-padding">
                                                                    <div class="d-flex text-right">
                                                                        <input name="_aeroportuaria_old" type="hidden" value="<?=$_1_u_nf_aeroportuaria; ?>">
                                                                        <input <?=$readonly ?> class="alinhar-direita height-18" style="width: 70px;" name="_1_<?=$_acao?>_nf_aeroportuaria" size="8" type="text" value="<?=$_1_u_nf_aeroportuaria ?>" vdecimal onchange="atualizarValorCompra(this, <?=$_1_u_nf_idnf; ?>, 'aeroportuaria')">
                                                                        <? if(empty($rowCompraInt['idnfaerop'])){ ?>
                                                                            <i class="fa fa-plus-circle fa-1x verde  pointer mt11mf5" onclick="criarNfentrada(<?=$_1_u_nf_idnf ?>, 'aerop', '<?=$_1_u_nf_aeroportuaria ?>')" title="Gerar Programação de Armazenagem Aeroportuária "></i>
                                                                        <? } else { ?>
                                                                            <a title="Armazenagem Aeroportuária" class="fa fa-bars fade pointer hoverazul mt11mf5" href="?_modulo=nfentrada&_acao=u&idnf=<?=$rowCompraInt['idnfaerop']?>" target="_blank"></a>
                                                                        <? } ?>
                                                                    </div>
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="<?=$colspan ?>" class="alinhar-direita mt11mf5 td-padding">Honorário de Importação <b>BRL</b>:</td>
                                                                <td class="td-padding">
                                                                    <div class="d-flex text-right">
                                                                        <input name="_honorarioimportacao_old" type="hidden" value="<?=$_1_u_nf_honorarioimportacao; ?>">
                                                                        <input <?=$readonly ?> class="alinhar-direita height-18" style="width: 70px;" name="_1_<?=$_acao?>_nf_honorarioimportacao" size="8" type="text" value="<?=$_1_u_nf_honorarioimportacao?>" vdecimal onchange="atualizarValorCompra(this, <?=$_1_u_nf_idnf; ?>, 'honorarioimportacao')">
                                                                        <? if(empty($rowCompraInt['idnfhonimp'])){ ?>
                                                                            <i class="fa fa-plus-circle fa-1x verde pointer mt11mf5" onclick="criarNfentrada(<?=$_1_u_nf_idnf ?>, 'honimp', '<?=$_1_u_nf_honorarioimportacao?>')" title="Gerar Programação de Honorário de Importação "></i>
                                                                        <? } else { ?>
                                                                            <a title="Honorário de Importação" class="fa fa-bars fade pointer hoverazul mt11mf5" href="?_modulo=nfentrada&_acao=u&idnf=<?=$rowCompraInt['idnfhonimp']?>" target="_blank"></a>
                                                                        <? } ?>
                                                                    </div>
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="<?=$colspan ?>" class="alinhar-direita mt11mf5 td-padding">ICMS <b>BRL</b>:</td>
                                                                <td class="td-padding">
                                                                    <div class="d-flex text-right">
                                                                        <input name="_honorarioimportacao_old" type="hidden" value="<?=$_1_u_nf_honorarioimportacao; ?>">
                                                                        <input <?=$readonly ?> class="alinhar-direita height-18" style="width: 70px;" name="_1_<?=$_acao?>_nf_icms" size="8" type="text" value="<?=$_1_u_nf_icms ?>" vdecimal onchange="atualizarValorCompra(this, <?=$_1_u_nf_idnf; ?>, 'icms')">
                                                                        <? if(empty($rowCompraInt['idicms'])){ ?>
                                                                            <i class="fa fa-plus-circle fa-1x verde pointer mt11mf5" onclick="criarNfentrada(<?=$_1_u_nf_idnf ?>, 'icms', '<?=$_1_u_nf_icms ?>')" title="Gerar Programação de ICMS"></i>
                                                                        <? } else { ?>
                                                                            <a title="Honorário de Importação" class="fa fa-bars fade pointer hoverazul mt11mf5" href="?_modulo=nfentrada&_acao=u&idnf=<?=$rowCompraInt['idicms']?>" target="_blank"></a>
                                                                        <? } ?>
                                                                    </div>
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="<?=$colspan ?>" class="alinhar-direita mt11mf5 td-padding">Siscomex <b>BRL</b>:</td>
                                                                <td class="td-padding">
                                                                    <div class="d-flex text-right">
                                                                        <input name="_siscomex_old" type="hidden" value="<?=$_1_u_nf_siscomex; ?>">
                                                                        <input <?=$readonly ?> class="alinhar-direita height-18" style="width: 70px;" name="_1_<?=$_acao?>_nf_siscomex" size="8" type="text" value="<?=$_1_u_nf_siscomex ?>" vdecimal onchange="atualizarValorCompra(this, <?=$_1_u_nf_idnf; ?>, 'siscomex')">
                                                                        <? if(empty($rowCompraInt['idsiscomex'])){ ?>
                                                                            <i class="fa fa-plus-circle fa-1x verde  pointer mt11mf5" onclick="criarNfentrada(<?=$_1_u_nf_idnf ?>, 'siscomex', '<?=$_1_u_nf_siscomex ?>')" title="Gerar Siscomex"></i>
                                                                        <? } else { ?>
                                                                            <a title="Honorário de Importação" class="fa fa-bars fade pointer hoverazul mt11mf5" href="?_modulo=nfentrada&_acao=u&idnf=<?=$rowCompraInt['idsiscomex']?>" target="_blank"></a>
                                                                        <? } ?>
                                                                    </div>
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                        <?
                                                        }
                                                        ?>
                                                        <tr>
                                                            <? $colspanConversao = ($qtdConversaoMoeda > 0) ? $colspan : $colspan - 1; ?>

                                                            <td colspan="<?=$colspanConversao?>" align="right" class="td-padding">Desconto <?=($internacional) ? '<b>BRL</b>' : ''; ?>:</td>

                                                            <td class="alinhar-direita td-padding">
                                                                <input type="hidden" id="descCadastrado" value="">
                                                                <input type="hidden" id="descNaoCadastrado" value="">
                                                                <input type="hidden" id="nf_valipiTotalCadastrado" value="">
                                                                <input type="hidden" id="nf_valipiTotalNaoCadastrado" value="">
                                                                <input type="hidden" id="nf_cofinsTotalCadastrado" value="">
                                                                <input type="hidden" id="nf_cofinsTotalNaoCadastrado" value="">
                                                                <input type="hidden" id="nf_valicmsTotalCadastrado" value="">
                                                                <input type="hidden" id="nf_valicmsTotalNaoCadastrado" value="">
                                                                <input type="hidden" id="nf_imposotimportacaoTotalCadastrado" value="">
                                                                <input type="hidden" id="nf_imposotimportacaoTotalNaoCadastrado" value="">
                                                                <input type="hidden" id="nf_pisTotalCadastrado" value="">
                                                                <input type="hidden" id="nf_pisTotalNaoCadastrado" value="">
                                                                <input type="hidden" id="nf_vstTotalCadastrado" value="">
                                                                <input type="hidden" id="nf_vstTotalNaoCadastrado" value="">
                                                                <input name="nf_desconto" class="alinhar-direita nf_desconto height-18" size="8" type="text" value="<?=$valtotaldesconto?>" vdecimal onchange="atualizadesconto(this, <?=$_1_u_nf_idnf; ?>, '')">
                                                            </td>
                                                            <td class="td-padding"></td>
                                                            <td colspan="3" class="td-padding"></td>
                                                        </tr>

                                                        <tr style="background-color: #FFFFFF;">
                                                            <td colspan="<?=$colspanConversao?>" align="right" class="td-padding">Total NF:<b><?=$moeda ?> </b></td>
                                                            <td class="alinhar-direita td-padding"><span id="totalTotal"></span>
                                                                <input name="_1_<?=$_acao?>_nf_subtotal" id="vlrsubtotal" class="vlrsubtotal" size="8" type="hidden" value="<?=number_format(tratanumero($vsubtotal), 2, ',', '.') ?>" <?=$readonly ?> <?=$vreadonly ?> vdecimal>
                                                                <?
                                                                if($internacional && $_1_u_nf_moedainternacional == 'Y'){
                                                                    $vtotal = $vsubtotal + tratanumero($frete) + tratanumero($_1_u_nf_freteimpnacional) + tratanumero($_1_u_nf_freteimpinternacional)
                                                                        + tratanumero($_1_u_nf_aeroportuaria) + tratanumero($_1_u_nf_honorarioimportacao) + tratanumero($_1_u_nf_siscomex)
                                                                        + tratanumero($totalvalipi) + tratanumero($totalpis) + tratanumero($totalcofins) + tratanumero($totalimpostoImportacao)
                                                                        + tratanumero($_1_u_nf_icms);
                                                                } else {
                                                                    $vtotal =  $vsubtotal + tratanumero($frete);
                                                                }

                                                                if($_1_u_nf_tiponf == "S" || $_1_u_nf_tiponf == 'R' || $_1_u_nf_tiponf == 'D'){
                                                                    $imp_serv = (tratanumero($_1_u_nf_pis) + tratanumero($_1_u_nf_cofins) + tratanumero($_1_u_nf_csll) + tratanumero($_1_u_nf_ir) + tratanumero($_1_u_nf_issret) + tratanumero($_1_u_nf_inss));
                                                                    $imp_serv = ($imp_serv < 0) ? 0 : $imp_serv;
                                                                    $vtotal = $vtotal - $imp_serv;
                                                                }

                                                                ?>
                                                                <input <?=$readonly ?> <?=$vreadonly ?> name="vlrtotal" id="vlrtotal" class="vlrtotal" type="hidden" value="<?=$vtotal ?>">
                                                                <input name="_1_<?=$_acao?>_nf_total" id="vlrtotal" class="vlrtotal" type="hidden" value="<?=number_format(tratanumero($vtotal), 2, ',', '.'); ?>" vdecimal>
                                                            </td>
                                                            <td class="td-padding"></td>
                                                            <td colspan="3" class="td-padding"></td>
                                                        </tr>
                                                        <? if($vnaocobrar > 0){ ?>
                                                            <tr>
                                                                <td colspan="<?=$colspanConversao; ?>" align="right" class="td-padding">Fatura:</td>
                                                                <td class="alinhar-direita bold td-padding">
                                                                    <?=number_format(tratanumero($vtotal), 2, ',', '.'); ?>
                                                                </td>
                                                                <td class="td-padding"></td>
                                                                <td colspan="3" class="td-padding"></td>
                                                            </tr>
                                                            <?
                                                        }
                                                    }
                                                    ?>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="inputsAlterar"></div>
                                        <div class="inputsAlterarRateio"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!------------------------  Impostos Serviço   ------------------------>
                    <?
                    if($_1_u_nf_tiponf == 'S' || $_1_u_nf_tiponf == 'R' ||  $_1_u_nf_tiponf == 'D'){ ?>   
                        <div class=" row">
                            <div class="col-md-12" style="padding: 3px 30px;">
                                <div class="panel panel-default">
                                    <div class="panel-heading _1">Imposto Serviço</div>
                                    <div class="panel-body" style="overflow-x: auto;" id="imposto" class="collapse">
                                        <div class="col-md-12">
                                            <? if($_1_u_nf_tiponf == 'S' || $_1_u_nf_tiponf == 'R' ||  $_1_u_nf_tiponf == 'D'){ ?>
                                                <div class="form-group col-md-1">
                                                    <label>PIS (0,65%):</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="_1_<?=$_acao?>_nf_pis" id="pis_servico" onchange="atualizaTotal('<?=$vtotal ?>')" class="size8" type="text" value="<?=$_1_u_nf_pis ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>Cofins (3,00%):</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="_1_<?=$_acao?>_nf_cofins" id="pis_cofins" onchange="atualizaTotal('<?=$vtotal ?>')" class="size8" type="text" value="<?=$_1_u_nf_cofins ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>CSLL (1,00%):</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="_1_<?=$_acao?>_nf_csll" id="pis_csll" onchange="atualizaTotal('<?=$vtotal ?>')" class="size8" type="text" value="<?=$_1_u_nf_csll ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>IRRF (1,50%):</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="_1_<?=$_acao?>_nf_ir" id="pis_ir" onchange="atualizaTotal('<?=$vtotal ?>')" class="size8" type="text" value="<?=$_1_u_nf_ir ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>INSS:</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="_1_<?=$_acao?>_nf_inss" id="pis_inss" onchange="atualizaTotal('<?=$vtotal ?>')" class="size8" type="text" value="<?=$_1_u_nf_inss ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>ISS:</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="_1_<?=$_acao?>_nf_iss" id="pis_iss" class="size8" type="text" value="<?=$_1_u_nf_iss ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>ISS Retido:</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="_1_<?=$_acao?>_nf_issret" id="pis_issret" onchange="atualizaTotal('<?=$vtotal ?>')" class="size8" type="text" value="<?=$_1_u_nf_issret ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>Total:</label>
                                                    <div class="input-group col-md-12">
                                                        <label class="idbox valorTotalImpostoServico"><?=number_format(tratanumero($vtotal), 2, ',', '.'); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="panel-body" style="overflow-x: auto;">
                                                    <?
                                                    $listarNfitemPorNfe = NfEntradaController::buscarNfItemPorNfe($_1_u_nf_idnf, 'C');
                                                    $_qtdimp = $listarNfitemPorNfe['qtdLinhas'];
                                                    if($_qtdimp < 1){
                                                        $listarNfitemPorNfe = NfEntradaController::buscarNfItemContaPagar($_1_u_nf_idnf, 'nf');
                                                        $_qtdimp = $listarNfitemPorNfe['qtdLinhas'];
                                                    }
                                                    if($_qtdimp > 0){
                                                        ?>
                                                        <br>
                                                        <table class="table table-striped planilha">
                                                            <tr>
                                                                <th>Descrição</th>
                                                                <th>Categoria</th>
                                                                <th>Tipo</th>
                                                                <th>Vencimento</th>
                                                                <th>Valor</th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                            <? $vlritemp = 0;
                                                            foreach ($listarNfitemPorNfe['dados'] as $nfItem){
                                                                $i = $i + 1;
                                                                $vlritemp = $vlritemp + $nfItem['vlritem'];

                                                                if($nfItem["tiponota"] == 'R'){
                                                                    $link = "comprasrh";
                                                                } else {
                                                                    $link = "nfentrada";
                                                                }
                                                                ?>
                                                                <tr>
                                                                    <td <? if(empty($nfItem["prodservdescr"])){ ?>style="background-color: red" <? } ?>>
                                                                        <input name="_<?=$i ?>_u_nfitem_idnfitem" size="8" type="hidden" value="<?=$nfItem["idnfitem"]; ?>">
                                                                        <input name="_<?=$i ?>_u_nfitem_qtd" class="size5" type="hidden" value="<?=$nfItem["qtd"]; ?>" vdecimal>

                                                                        <input name="_<?=$i ?>_u_nfitem_prodservdescr" size="20" type="text" value="<?=$nfItem["prodservdescr"]?>">
                                                                    </td>
                                                                    <td>
                                                                        <select id="idcontaitem<?=$nfItem["idnfitem"]?>" class='size15' name="_<?=$i ?>_u_nfitem_idcontaitem" vnulo onchange="preencheti(<?=$nfItem["idnfitem"]?>)">
                                                                            <option value=""></option>
                                                                            <? fillselect(NfEntradaController::buscarContaItemAtivoShare(), $nfItem['idcontaitem']); ?>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <select id="idtipoprodserv<?=$nfItem["idnfitem"]?>" class='size15' name="_<?=$i ?>_u_nfitem_idtipoprodserv" vnulo>
                                                                            <option value=""></option>
                                                                            <? fillselect(NfEntradaController::listarTipoProdservCompra($_1_u_nf_status, $nfItem['idcontaitem']), $nfItem['idtipoprodserv']); ?>
                                                                        </select>
                                                                    </td>
                                                                    <td><?=$nfItem['datarecebimento']?></td>
                                                                    <td <? if(empty($nfItem["vlritem"])){ ?>style="background-color: red" <? } ?>>
                                                                        <?=number_format(tratanumero($nfItem["total"]), 2, ',', '.'); ?>
                                                                    </td>
                                                                    <? //Acrescentado para excluir impostos - LTM (03/08/2020 - 363864) 
                                                                    ?>
                                                                    <td>
                                                                        <a class="fa fa-bars pointer hoverazul" title="Nf" onclick="janelamodal('?_modulo=<?=$link ?>&_acao=u&idnf=<?=$nfItem['idnf']?>')"></a>
                                                                    </td>
                                                                    <td>
                                                                        <? if($nfItem['status'] != 'QUITADO'){ ?>
                                                                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="excluiritem(<?=$nfItem['idnfitem']?>)" alt="Excluir !"></i>
                                                                        <? } ?>
                                                                    </td>
                                                                </tr>
                                                            <? } ?>
                                                            <tr>
                                                                <td colspan="4" align="right"></td>
                                                                <td><?=number_format(tratanumero($vlritemp), 2, ',', '.'); ?></td>
                                                                <td colspan="2"></td>
                                                            </tr>
                                                        </table>
                                                    <? }

                                                    $rescom = PedidoController::buscarImpostosNf($_1_u_nf_idnf);
                                                    $qrcom = count($rescom);
                                                    if($qrcom > 0){
                                                        ?>
                                                        <br>
                                                        <table class="table table-striped planilha">
                                                            <?
                                                            $vlritemp = 0;
                                                            foreach ($rescom as $rowp2){
                                                                $vlritemp = $vlritemp + $rowp2['valor'];
                                                                ?>
                                                                <tr>
                                                                    <th>Fatura:</th>
                                                                    <td><? echo ($rowp2["parcela"]); ?></td>
                                                                    <th>Parcela:</th>
                                                                    <td><? echo ($rowp2["parcela"] . " de " . $rowp2["parcelas"]); ?></td>
                                                                    <td colspan="2"><?=$rowp2["nome"]?></td>
                                                                    <th>Recebimento:</th>
                                                                    <td><?=dma($rowp2["datareceb"]) ?></td>
                                                                    <th>Status:</th>
                                                                    <td><?=$rowp2["status_item"]?></td>
                                                                    <th>Valor:</th>
                                                                    <td>
                                                                        <?
                                                                        $resao = PedidoController::buscarRestaurarPorIdlp(getModsUsr("LPS"));
                                                                        $qtdao = count($resao);
                                                                        if($qtdao > 0 and $rowp2["status_item"] != "QUITADO"){ ?>
                                                                            <input name="contapagaritem_valor" class="size10" type="text" value="<?=$rowp2["valor"]?>" onchange="atualizavlitem(this,<?=$rowp2["idcontapagaritem"]?>)">
                                                                        <? } else { ?>
                                                                            <?=number_format(tratanumero($rowp2["valor"]), 2, ',', '.'); ?>
                                                                        <? } ?>
                                                                    </td>
                                                                    <td>
                                                                        <a class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp2["idcontapagar"]?>');"></a>
                                                                    </td>
                                                                </tr>
                                                            <? } //while ($rowp2 = mysqli_fetch_array($qrp2))
                                                            ?>
                                                            <tr>
                                                                <td colspan="11" align="right"></td>
                                                                <td><?=number_format(tratanumero($vlritemp), 2, ',', '.'); ?></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        </table>
                                                    <? } ?>
                                                </div>
                                                <?
                                                } //elseif($_1_u_nf_tiponf=='S'){
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>   
                        </div>
                    <? } ?>
                    <!------------------------  Impostos Serviço   ------------------------>
                    
                    <!----------------------------------------------------- Pagamento Itens ------------------------------------------------------------->
                    <?
                    if(!empty($_acao == 'u')){
                        $listarFormaPagamentoContaPagar = NfEntradaController::buscarContaPagarFormaPagamentoPorIdObejtoOrigem($_1_u_nf_idnf, 'nf', 'gnre', $_1_u_nf_idnf);
                        $qtdrx = $listarFormaPagamentoContaPagar['qtdLinhas'];
                        if($qtdrx > 0) //tem contapagaritem
                        {
                            foreach ($listarFormaPagamentoContaPagar['dados'] as $formaPagamentoContaPagar){
                                if($formaPagamentoContaPagar['agruppessoa'] == 'N' && $formaPagamentoContaPagar['agrupado'] == 'Y'){
                                    $habilitaFaturaAutomatica = true;
                                }
                            }
                            ?>
                            <div class="row">
                                <div class="col-md-12" style="padding: 3px 30px;">
                                    <div class="panel panel-default">
                                        <div class="panel-heading _1">Extrato</div>
                                        <div class="panel-body" style="overflow-x: auto;">
                                            <table class="table table-striped planilha">
                                                <th>Fatura:</th>
                                                <th>Parcela:</th>
                                                <th>Obs:</th>
                                                <? if($habilitaFaturaAutomatica == true){ ?>                                                                
                                                    <th>Fatura Automática:</th>
                                                <? } ?>
                                                <th>Recebimento:</th>
                                                <th style="width: 130px;">Valor:</th>
                                                <th>Status:</th>
                                                <th></th>
                                                <th></th>

                                                <? $totalcp = 0;
                                                foreach ($listarFormaPagamentoContaPagar['dados'] as $formaPagamentoContaPagar){
                                                    $totalcp = $totalcp + $formaPagamentoContaPagar["valor"]; 
                                                    if(!empty($formaPagamentoContaPagar['idcontapagar']) && (!empty($formaPagamentoContaPagar['status']) && $formaPagamentoContaPagar['status'] != 'INATIVO')){
                                                        $contaPagar = NfEntradaController::buscarFaturaPorId($formaPagamentoContaPagar['idcontapagar']);
                                                    } else {
                                                        $contaPagar = NfEntradaController::buscarContaPagarItem($formaPagamentoContaPagar['idcontapagaritem']);
                                                        if(empty($contaPagar['idcontapagar'])){
                                                            $contaPagar = NfEntradaController::buscarFaturaPorId($formaPagamentoContaPagar['idcontapagar']);
                                                        }
                                                    }
                                                    ?>
                                                    <tr>                                                            
                                                        <td><? echo ($contaPagar["parcela"]); ?></td>                                                            
                                                        <td class="nowrap"><?=$formaPagamentoContaPagar['parcela']?> de <?=$formaPagamentoContaPagar['parcelas']?> </td>                                                            
                                                        <td><?=$formaPagamentoContaPagar['obsi']; ?>
                                                        </td>
                                                        <? if($formaPagamentoContaPagar['agruppessoa'] == 'N' && $formaPagamentoContaPagar['agrupado'] == 'Y'){ ?>                                                                
                                                            <td><label class="alert-warning"><?=traduzid('formapagamento', 'idformapagamento', 'descricao', $formaPagamentoContaPagar['idformapagamento']) ?></label></td>
                                                        <? } ?>                                                            
                                                        <td><?=$contaPagar["datareceb"]?></td>                                                            
                                                        <td align="right"><?=number_format(tratanumero($formaPagamentoContaPagar["valor"]), 2, ',', '.'); ?></td>                                                            
                                                        <td>
                                                            <label class="alert-warning">
                                                                <? if(!empty($contaPagar["status"])){
                                                                    echo ($contaPagar["status"]);
                                                                } else {
                                                                    echo ($formaPagamentoContaPagar["status"]);
                                                                } ?>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <!-- consulta e verificacao da data fluxostatushist, se a data criadoem conta pagar for maior q data conclusao, mostra -->
                                                            <?
                                                            $listarNfContaPagar = NfEntradaController::buscarNfContaPagar($contaPagar['idcontapagar'], $pagvalmodulo, $_1_u_nf_idnf);
                                                            foreach ($listarNfContaPagar as $nfContaPagar){
                                                                if(($nfContaPagar['criadoemcp'] > $nfContaPagar['criadoemfs'])){
                                                                    ?>
                                                                    <a class="fa fa-info-circle tip" title="Informações de Criação" data-toggle="popover" href="#<?=$contaPagar['idcontapagar']?>" data-trigger="hover"></a>
                                                                    <div id="modalpopover_<?=$contaPagar['idcontapagar']?>" class="modal-popover hidden">
                                                                        <table>
                                                                            <tr>
                                                                                <td nowrap><b>Criado por:</b></td>
                                                                                <td><?=dmahms($nfContaPagar['criadopor']) ?></td>
                                                                            </tr>
                                                                            <tr style="margin-top: 10px;">
                                                                                <td nowrap><b>Criado em:</b> </td>
                                                                                <td><?=dmahms($nfContaPagar['criadoemcp']) ?></td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <?
                                                                }
                                                            }
                                                            ?>
                                                        </td>
                                                        <td id="contapagar_<?=$contaPagar["idcontapagar"]?>">
                                                            <a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$contaPagar['idcontapagar']?>');"></a>
                                                        </td>
                                                    </tr>
                                                <?
                                                } //while(mysqli_fetch_assoc($qrpx))                                    
                                                ?>
                                                <tr>
                                                    <td colspan="4" align="right"></td>
                                                    <td align="right">Total:</td>
                                                    <td align="right"><b><?=number_format(tratanumero($totalcp), 2, ',', '.'); ?></b></td>
                                                    <td colspan="5"><span class="valorDiferenteNota"></span></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="20">
                                                        <? if($_1_u_nf_status != "CONCLUIDO" ||  $_1_u_nf_status != "CANCELADO"){ ?>
                                                            <input value="<?=$qtdrx + 1 ?>" id="parcela_parcelas" type='hidden' name="parcela_parcelas">
                                                            <a class="fa fa-plus-circle verde pointer hoverazul" title="Nova Parcela" onclick="showModal()"></a>
                                                        <? } ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div> <!-- fim div faturamento -->
                                </div>
                                <?
                                $listarFArquivosFaturamento = NfEntradaController::buscarInfFormapagamentoPorIdObjetoOrigemEIdObjeto($_1_u_nf_idnf, 'nf', $_1_u_nf_idnf, 'nf');
                                $qtarq = $listarFArquivosFaturamento['qtdLinhas'];
                                if($qtarq > 0){
                                    ?>
                                    <div class="col-md-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading _4" data-toggle="collapse" href='#arqfat'>Arquivos de Faturamento Anexos</div>
                                            <div class="panel-body" id='arqfat' style="overflow-x: auto;">
                                                <table>
                                                    <? foreach ($listarFArquivosFaturamento as $arquivosFaturamento){ ?>
                                                        <tr>
                                                            <td align="center"><a title="Abrir arquivo" target="_blank" href="./upload/<?=$arquivosFaturamento["nome"]?>"><?=$arquivosFaturamento["nome"]?></a></td>
                                                        </tr>
                                                    <? } ?>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                            <?
                        }

                        if($qtdrowsp == 0 and $qtdrx == 0 and $_1_u_nf_geracontapagar == "Y" and $_1_u_nf_status == "CONCLUIDO"){
                            ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading _4">Extrato</div>
                                        <div class="panel-body">
                                            <table>
                                                <tr>
                                                    <td align="center">
                                                        <font style="font-size: 25px;" color="red">NF não gerou parcela(s)!!!</font>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?
                        }
                        //if($qtdrowsp>0){
                    }
                    ?>  
                </div>
                <!------------------------  Itens ------------------------>

                <!------------------------  CONTROLE NF ------------------------>
                <?
                /**********************************************************************************************************************
                 *												|							|										  *
                 *												|	Modal Controles Nf  	|										  *
                 *												V							V										  *
                 ***********************************************************************************************************************/
                ?>
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_controlenf">
                    <?
                    if($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'T' || $_1_u_nf_tiponf == 'O'){
                    ?>
                        <div class="row">
                            <? if(empty($_1_u_nf_xmlret) && ($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'T' || $_1_u_nf_tiponf == 'O')){ ?>
                                <div class="col-md-12">
                                    <span class="d-flex justify-content-center form-control alert-warning font-bold" style="margin-top: 15px;">
                                        <font color="red" class="alerta-informacao"><? echo "Não possui XML"; ?></font>
                                    </span>
                                </div>
                            <? } ?>
                            <div class="col-md-12">
                                <div class="panel-body">
                                    <div class="form-group pb25">
                                        <div class="col-md-12">
                                            <div class="col-md-2">
                                                <label>Emissão:</label>
                                                <div><?=$_1_u_nf_dtemissao?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Data Recebimento:</label>
                                                <div>
                                                    <? if(cb::idempresa() == 4){ ?>
                                                        <? if(array_key_exists("alteracampoentradanf", getModsUsr("MODULOS"))) {?>
                                                            <input 
                                                                name="_1_<?=$_acao?>_nf_prazo" disabled="disabled" <? !empty($_1_u_nf_xmlret) ? 'vnulo' : ''?> 
                                                                class="calendario size10 atualiza-recebimento" id="fdata1" type="text" 
                                                                value="<?= $_1_u_nf_prazo?>" autocomplete="off" />
                                                            <i class="fa fa-pencil btn-lg pointer" title='Editar Data de Entrada' onclick='alteraEdicaoDataEntrada()'></i>
                                                        <?} else {?>
                                                            <input 
                                                                name="_1_<?=$_acao?>_nf_prazo" type="hidden" 
                                                                value="<?= $_1_u_nf_prazo?>" autocomplete="off" />
                                                            <span><?= $_1_u_nf_prazo ?></span>
                                                        <?}?>      
                                                        <?/* if($_1_u_nf_status == 'CONCLUIDO'){ ?>
                                                            <input name="_1_<?=$_acao?>_nf_prazo" type="hidden" value="<?=$_1_u_nf_prazo?>">
                                                        <? } elseif((array_key_exists("dataentradacompras", getModsUsr("MODULOS"))) && !empty($_1_u_nf_prazo)){ ?>
                                                            <i class="fa fa-pencil btn-lg pointer" title='Editar Envio' onclick="alteravalor('prazo', '<?=dma($_1_u_nf_prazo) ?>', 'modulohistorico', <?=$_1_u_nf_idnf ?>,'Data Recebimento:')"></i>
                                                        <?
                                                            }*/
                                                    } else { ?>
                                                        <? if(array_key_exists("dataentradacompras", getModsUsr("MODULOS"))) {?>
                                                            <input 
                                                                name="_1_<?=$_acao?>_nf_prazo" <? !empty($_1_u_nf_xmlret) ? 'vnulo' : '' ?> 
                                                                class="calendario size10 atualiza-recebimento desabilitado" <? $_1_u_nf_status == 'CONCLUIDO' ? 'disabled="disabled"' : '' ?>  
                                                                id="fdata1" type="text" value="<?=$_1_u_nf_prazo?>" autocomplete="off" disabled
                                                            >
                                                            <i class="fa fa-pencil btn-lg pointer" title='Editar Data de Entrada' onclick='alteraEdicaoDataEntrada()'></i>
                                                        <?} else if(empty($_1_u_nf_prazo)) {?>
                                                            <input name="_1_<?=$_acao?>_nf_prazo" vnulo class="calendario size10 atualiza-recebimento" id="fdata1" type="text" value="<?=$_1_u_nf_prazo?>" autocomplete="off">
                                                        <?} else {?>
                                                            <input name="_1_<?=$_acao?>_nf_prazo" type="hidden" value="<?=$_1_u_nf_prazo?>">
                                                            <span><?= $_1_u_nf_prazo ?></span>
                                                        <?}?>
                                                        <? /*if($_1_u_nf_status == 'CONCLUIDO'){ ?>
                                                            <input name="_1_<?=$_acao?>_nf_prazo" type="hidden" value="<?=$_1_u_nf_prazo?>">
                                                            <? 
                                                        }*/
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <? if($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'O' || $_1_u_nf_tiponf == 'T'){ ?>
                                        <div class="form-group pb25">
                                            <label>Finalidade:</label>
                                            <div class="input-group">
                                                <select id="idfinalidadeprodserv" name="_1_<?=$_acao?>_nf_idfinalidadeprodserv" class='size30' vnulo <? if($_1_u_nf_envionfe == 'CONCLUIDA'){ ?> onchange="atualizafinalidade(this)" <? } ?>>
                                                    <option value=""></option>
                                                    <? fillselect(NfEntradaController::buscarFinalidadeProdserv(), $_1_u_nf_idfinalidadeprodserv); ?>
                                                </select>
                                            </div>
                                        </div>
                                    <? } ?>

                                    <div class="form-group pb25">
                                        <label>Upload XML?</label>
                                        <div class="input-group pd-left-top">
                                        <i class="fa fa-cloud-upload dz-clickable pointer azul" id="xmlnfe" onclick="uploadXml()" title="Clique para carregar um XML"></i>
                                            <? 
                                            if(!empty($_1_u_nf_xmlret)){ ?>
                                                <font color="green" class="pl7"><? echo ("<b>Esta NF possui XML Carregado</b>"); ?></font><?
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <? if(($_1_u_nf_tiponf == 'O' || $_1_u_nf_tiponf == 'C') and empty($_1_u_nf_xmlret)){ ?>
                                        <div class="form-group pb25">
                                            <label>Chave NFe:</label>
                                            <div class="input-group">
                                                <input id="idnfe" <?=$readonly ?> name="_1_<?=$_acao?>_nf_idnfe" size="40" type="text" value="<?=$_1_u_nf_idnfe ?>" onchange="manifestanf(this)" pattern="[0-9]+$">
                                            </div>
                                        </div>
                                        <?
                                    } elseif($_1_u_nf_tiponf == 'T' && empty($_1_u_nf_xmlret)){
                                        ?>
                                        <div class="form-group pb25">
                                            <label>Chave CTe:</label>
                                            <div class="input-group">
                                                <input id="idnfe" <?=$readonly ?> name="_1_<?=$_acao?>_nf_idnfe" size="40" type="text" value="<?=$_1_u_nf_idnfe ?>" onchange="salvachavecte(this)" pattern="[0-9]+$">
                                            </div>
                                        </div>
                                        <?
                                    } else {
                                        ?>
                                        <input id="idnfe" <?=$readonly ?> name="_1_<?=$_acao?>_nf_idnfe" type="hidden" value="<?=$_1_u_nf_idnfe ?>">
                                        <?
                                    }
                                    ?>

                                    <div class="form-group pb25">
                                        <label>Ações NF:</label>
                                        <div class="input-group col-md-12 alinhamento-esquerda-20">
                                            <div class="col-md-4">
                                                <div class="col-md-1 pl22">
                                                    <?
                                                    if($_1_u_nf_sped == 'Y'){
                                                        $checked = 'checked';
                                                        $vchecked = 'N';
                                                    } else {
                                                        $checked = '';
                                                        $vchecked = 'Y';
                                                    }
                                                    ?>
                                                    <input title="sped" type="checkbox" <?=$checked ?> name="namesped" onclick="altcheck('nf','sped',<?=$_1_u_nf_idnf ?>,'<?=$vchecked ?>')">
                                                </div>
                                                <div class="col-md-10 top-align-sete">
                                                    Sped?
                                                    <?
                                                    $idSpedC100 = "";
                                                    $spedC100 = NfEntradaController::buscarSpedC100($_1_u_nf_idnf, "'ATIVO','CORRIGIDO'");
                                                    $qtdc100 = count($spedC100);
                                                    if($qtdc100 > 0){
                                                        ?>
                                                        <a class="fa fa-bars pointer hoverazul" title="Editar" onclick="janelamodal('?_modulo=spedc100&_acao=u&idspedc100=<?=$spedC100['idspedc100']?>')"></a>
                                                        <?
                                                        $idSpedC100 = $spedC100['idspedc100'];
                                                    }

                                                    $spedD100 = NfEntradaController::buscarSpedD100($_1_u_nf_idnf, "'ATIVO','CORRIGIDO'");
                                                    $qtdd100 = mysqli_num_rows($resd100);
                                                    if($qtdd100 > 0){
                                                        $rowd100 = mysqli_fetch_assoc($resd100);
                                                    ?>
                                                        <a class="fa fa-bars pointer hoverazul" title="Editar" onclick="janelamodal('?_modulo=spedc100&_acao=u&idspedd100=<?=$spedD100['idspedd100']?>')"></a>
                                                    <?
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <? if($qtdc100 > 0 and !empty($spedC100['idspedc100'])){ ?>
                                                <div class="col-md-4">
                                                    <div class="col-md-1 alinhamento-esquerda">
                                                        <?
                                                        if($spedC100['status'] == 'CORRIGIDO'){
                                                            $checked = 'checked';
                                                            $vchecked = 'ATIVO';
                                                        } else {
                                                            $checked = '';
                                                            $vchecked = 'CORRIGIDO';
                                                        }
                                                        ?>
                                                        <input class="btn-lg pointer" title="Conferido pela contabilidade não atualiza as informações do sped ao mexer nas configurações da nota." type="checkbox" <?=$checked ?> name="namesped" onclick="altcheck('spedc100','status',<?=$spedC100['idspedc100']?>,'<?=$vchecked ?>')">
                                                    </div>
                                                    <div class="col-md-10 top-align-sete">
                                                        Conferido (Contabilidade)
                                                        <?
                                                        $listarHistoricoSped = NfEntradaController::buscarHistoricoSped($spedC100['idspedc100'], 'spedc100', 'status');
                                                        $qtdhi = count($listarHistoricoSped);
                                                        if($qtdhi > 0){
                                                            ?>
                                                            <i class="fa fa-info-circle pointer hoverazul tip">
                                                                <span>
                                                                    <? foreach ($listarHistoricoSped as $_historico){
                                                                        $validadopor = strtoupper($_historico['criadopor']);
                                                                        $validadoem = dmahms($_historico['criadoem']);
                                                                        ?>
                                                                        <ul>
                                                                            <li>Conferido por: <?=$validadopor ?> em: <?=$validadoem ?>
                                                                        </ul>
                                                                    <? } ?>
                                                                </span>
                                                            </i>
                                                        <? } ?>
                                                    </div>
                                                </div>
                                            <? } ?>
                                        </div>
                                    </div>
                                    <div class="form-group alinhamento-esquerda-20 pb25 pl22">
                                        <div class="input-group col-md-12">
                                            <?
                                            if($_1_u_nf_tiponf == "C"){
                                                if($_1_u_nf_envionfe == "PENDENTE" and !empty($_1_u_nf_idnfe)){
                                                    ?>
                                                    <div class="col-lg-2">
                                                        <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="enviomanifestacao();" title="Confirmar Recebimento">
                                                            <i class="fa fa-sign-out"></i>Confirmação
                                                        </button>
                                                    </div>
                                                    <?
                                                }
                                                if($_1_u_nf_envionfe == "MANIFESTADA" and !empty($_1_u_nf_idnfe)){
                                                    ?>
                                                    <div class="col-lg-2">
                                                        <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="getnfe();" title="Baixar XML">
                                                            <i class="fa fa-cloud-download"></i>Baixar XML
                                                        </button>
                                                    </div>
                                                    <?
                                                }
                                            }

                                            if(!empty($_1_u_nf_xmlret)){
                                                ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-secondary btn-sm custom-sidebar" title="Baixar xml NFe" onclick="janelamodal('../inc/nfe/sefaz3/func/geraarquivo.php?idnotafiscal=<?=$_1_u_nf_idnf ?>')">
                                                        <i class="fa fa-download"></i>XML
                                                    </button>
                                                </div>
                                                <?
                                            }

                                            if($_1_u_nf_envionfe == "CONCLUIDA" and ($_1_u_nf_tiponf == 'O' || $_1_u_nf_tiponf == 'C') and (!empty($_1_u_nf_idfinalidadeprodserv) || $_1_u_nf_faticms == 'Y' || $_1_u_nf_consumo == 'Y' || $_1_u_nf_imobilizado == 'Y' ||  $_1_u_nf_outro == 'Y' ||  $_1_u_nf_comercio == 'Y')){
                                                $tipoconsumo = traduzid('finalidadeprodserv', 'idfinalidadeprodserv', 'tipoconsumo', $_1_u_nf_idfinalidadeprodserv);
                                                ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-secondary btn-sm custom-sidebar" title="Danfe" onclick="janelamodal('../inc/nfe/sefaz4/func/printDANFE.php?idnotafiscal=<?=$_1_u_nf_idnf ?>')">
                                                        <i class="fa fa-print"></i>Danfe
                                                    </button>
                                                </div>
                                                <div class="col-lg-2">
                                                    <? if($_1_u_nf_status == 'CONCLUIDO'){
                                                        $atualizar = '';
                                                        $DesabilitarAtualizar = 'disabled';
                                                        $texto = 'Desabilitado';
                                                    } else {
                                                        $atualizar = 'onclick="altfimnfe(`'.$tipoconsumo.'`);"';
                                                        $DesabilitarAtualizar = '';
                                                        $texto = '';
                                                    }                                                 
                                                    ?>
                                                    <button type="button" class="btn btn-secondary btn-sm custom-sidebar" <?=$atualizar?> <?=$DesabilitarAtualizar?>  title="Confirmar Recebimento <?=$texto?>">
                                                        <i class="fa fa-refresh"></i>Atualizar Itens via XML
                                                    </button>
                                                </div>
                                                <?
                                                if($_1_u_nf_envionfe == "CONCLUIDA"){
                                                    ?>
                                                    <div class="col-lg-2">
                                                        <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="devolvernf();" title="Devolver NF">
                                                            <i class="fa fa-mail-reply"></i>Devolver NF
                                                        </button>
                                                    </div>
                                                    <?
                                                }
                                            } elseif($_1_u_nf_envionfe == "CONCLUIDA" and $_1_u_nf_tiponf == 'T'){
                                                ?>
                                                <div class="div col-lg-2">
                                                    <button type="button" class="btn btn-secondary btn-sm custom-sidebar" title="Danfe" onclick="janelamodal('../inc/cte/vendor/nfephp-org/sped-da/functions/printcte.php?idnf=<?=$_1_u_nf_idnf ?>')">
                                                        <i class="fa fa-print"></i>CTe
                                                    </button>
                                                </div>
                                                <div class="div col-lg-2">
                                                    <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="gerainfcte();" title="Atualizar CTE via XML">
                                                        <i class="fa fa-refresh"></i>Atualizar CTe via XML
                                                    </button>
                                                </div>
                                                <?
                                            }

                                            if(!empty($_1_u_nf_xmlret) && $_1_u_nf_status != 'CONCLUIDO'){
                                                ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="excluirxml(<?=$_1_u_nf_idnf ?>)" title="Excluir XML">
                                                        <i class="fa fa-trash"></i>Excluir XML
                                                    </button>
                                                </div>
                                                <?
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <?
                                    if($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'S' || $_1_u_nf_tiponf == 'O'){
                                        $listarXmlItem = NfEntradaController::buscarXmlNfItem($_1_u_nf_idnf);
                                        $qtdx = count($listarXmlItem);
                                        if($qtdx > 0){
                                            ?>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">Itens - XML</div>
                                                        <div class="panel-body">
                                                            <table class="table table-striped planilha">
                                                                <tr>
                                                                    <th>Cód</th>
                                                                    <th>Produto</th>
                                                                    <th style="text-align: right !important;">Qtd</th>
                                                                    <th style="text-align: right !important;">Un</th>
                                                                    <th style="text-align: right !important;">Valor Un</th>
                                                                    <th style="text-align: right !important;">Desconto</th>
                                                                    <th style="text-align: right !important;">CFOP</th>
                                                                    <th style="text-align: right !important;">BC</th>
                                                                    <th style="text-align: right !important;">ICMS ST</th>
                                                                    <th style="text-align: right !important;">ICMS %</th>
                                                                    <th style="text-align: right !important;">ICMS R$</th>
                                                                    <th style="text-align: right !important;">IPI</th>
                                                                    <th style="text-align: right !important;">Frete</th>
                                                                    <th style="text-align: right !important;">Outras Desp</th>
                                                                    <th style="text-align: right !important;">Valor</th>
                                                                </tr>
                                                                <?
                                                                $valorx = 0.00;
                                                                $fretex = 0.00;
                                                                $stx = 0.00;
                                                                $vipi = 0.00;
                                                                $desconto = 0.00;
                                                                $i = 1;
                                                                foreach ($listarXmlItem as $_itemXml){
                                                                    $i = $i + 1;
                                                                    $valorx = $valorx + $_itemXml['valor'];
                                                                    $valorx = $valorx + $_itemXml['vst'];
                                                                    $valorx = $valorx + $_itemXml['vipi'];
                                                                    $valorx = $valorx + $_itemXml['outro'];

                                                                    $stx = $stx + $_itemXml['vicms'];
                                                                    $fretex = $fretex + $_itemXml['frete'];
                                                                    $vipi = $vipi + $_itemXml['vipi'];
                                                                    $desconto = $desconto + ($_itemXml['desconto']);
                                                                    $voutro = $voutro + $_itemXml['outro'];
                                                                    $valoritem = $_itemXml['valor'] / $_itemXml['qtd'];                                                                    
                                                                    ?>
                                                                    <tr class="respreto" style="background-color: #FFFFFF;">
                                                                        <td class="respreto" align="center" style="font-size: 10px;">
                                                                            <select title="<?=$_itemXml['descricaoprod']?>" class="nfitemxml_idprodserv item_xml" idnfitemxml="<?=$_itemXml["idnfitemxml"]; ?>" cprodforn="<?=$_itemXml['cprod']?>" fnidpessoa="<?=$_1_u_nf_idpessoa ?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'idprodserv','<?=$_1_u_nf_consumo?>')">
                                                                                <option value=""></option>
                                                                                <? fillselect(NfEntradaController::buscarProdutoItemProdservQueNaoExisteXml($_1_u_nf_idnf, $_itemXml['idnfitemxml'], $_itemXml['idprodserv'], $_1_u_nf_consumo, $_itemXml['descr'], $valoritem), $_itemXml['idprodserv']); ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <?=$_itemXml['descr']?>
                                                                        </td>
                                                                        <td align="right">
                                                                            <?=number_format(tratanumero($_itemXml['qtd']), 2, ',', '.'); ?>
                                                                        </td>
                                                                        <td align="right">
                                                                            <?=$_itemXml['un']?>
                                                                        </td>
                                                                        <td align="right">
                                                                            <?=number_format(tratanumero($_itemXml['valor'] / $_itemXml['qtd']), 2, ',', '.'); ?>
                                                                        </td>
                                                                        <td align="right">
                                                                            <input class="size7" style="text-align: right;" type="text" value="<?=$_itemXml['desconto']; ?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'des')">
                                                                            <? if(!empty($_itemXml['descontopor'])){
                                                                                $descontoajuste = 'Y';
                                                                            ?>
                                                                                <i title="Ajuste: <?=$_itemXml['descontopor']?> <?=dmahms($_itemXml['descontoem']) ?>" class="fa fa-info-circle preto pointer hoverazul tip"></i>
                                                                            <? } ?>
                                                                        </td>
                                                                        <td align="right"><?=$_itemXml['cfop']?></td>
                                                                        <td align="right"><?=number_format(tratanumero($_itemXml['basecalc']), 2, ',', '.'); ?></td>
                                                                        <td align="right"><?=number_format(tratanumero($_itemXml['vst']), 2, ',', '.'); ?></td>
                                                                        <td align="right"><?=number_format(tratanumero($_itemXml['aliq_icms']), 2, ',', '.'); ?></td>
                                                                        <td align="right"><?=number_format(tratanumero($_itemXml['vicms']), 2, ',', '.'); ?></td>
                                                                        <td align="right"><?=number_format(tratanumero($_itemXml['vipi']), 2, ',', '.'); ?></td>
                                                                        <td align="right">
                                                                            <input class="size7" style="text-align: right;" type="text" value="<?=$_itemXml["frete"]; ?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'frete')">
                                                                            <? if(!empty($_itemXml['fretepor'])){
                                                                                $freteajuste = 'Y';
                                                                                ?>
                                                                                <i title="Ajuste: <?=$_itemXml['fretepor']?> <?=dmahms($_itemXml['freteem']) ?>" class="fa fa-info-circle preto pointer hoverazul tip"></i>
                                                                            <? } ?>
                                                                        </td>
                                                                        <td align="right">
                                                                            <input class="size7" style="text-align: right;" type="text" value="<?=$_itemXml["outro"]; ?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'outro')">
                                                                        </td>
                                                                        <td align="right">
                                                                            <?=number_format(tratanumero($_itemXml['valor']), 2, ',', '.'); ?>
                                                                        </td>
                                                                    </tr>
                                                                <? } ?>
                                                                <tr class="header">
                                                                    <td colspan="5" align="right">Total</td>
                                                                    <td align="right"><?=number_format(tratanumero($desconto), 2, ',', '.'); ?></td>
                                                                    <td colspan="2" align="right"></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td align="right"><?=number_format(tratanumero($stx), 2, ',', '.'); ?></td>
                                                                    <td align="right"><?=number_format(tratanumero($vipi), 2, ',', '.'); ?></td>
                                                                    <td align="right"><?=number_format(tratanumero($fretex), 2, ',', '.'); ?></td>
                                                                    <td align="right"><?=number_format(tratanumero($voutro), 2, ',', '.'); ?></td>
                                                                    <td align="right"><?=number_format(tratanumero($valorx), 2, ',', '.'); ?></td>
                                                                    <td></td>
                                                                </tr>
                                                                <?
                                                                if($desconto > 0){
                                                                    if($descontoajuste == 'Y'){
                                                                        $strdajuste = "Ajuste";
                                                                    } else {
                                                                        $strdajuste = "";
                                                                    }
                                                                    ?>
                                                                    <tr>
                                                                        <td colspan="6"></td>
                                                                        <td></td>
                                                                        <td colspan="2"></td>
                                                                        <td colspan=5 align="right">Desconto <?=$strdajuste ?></td>
                                                                        <td align="right">
                                                                            <font color="red">
                                                                                <?=number_format(tratanumero($desconto), 2, ',', '.'); ?>
                                                                            </font>
                                                                        </td>
                                                                        <td></td>
                                                                    </tr>
                                                                <?
                                                                }

                                                                if($fretex > 0){
                                                                    if($freteajuste == 'Y'){
                                                                        $strfajuste = "Ajuste";
                                                                    } else {
                                                                        $strfajuste = "";
                                                                    }
                                                                    ?>
                                                                    <tr>
                                                                        <td colspan="6"></td>
                                                                        <td></td>
                                                                        <td colspan="2"></td>
                                                                        <td colspan=5 align="right">Frete <?=$strfajuste ?></td>
                                                                        <td align="right">
                                                                            <font color="red">
                                                                                <?=number_format(tratanumero($fretex), 2, ',', '.'); ?>
                                                                            </font>
                                                                        </td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <?
                                                                }
                                                                ?>
                                                                <tr>
                                                                    <td colspan="6"></td>
                                                                    <td></td>
                                                                    <td colspan="2"></td>
                                                                    <td colspan=5 align="right">Total</td>
                                                                    <td align="right">
                                                                        <font color="red">
                                                                            <?
                                                                            $valorf = ($valorx + $fretex) - $desconto;
                                                                            ?>
                                                                            <?=number_format(tratanumero($valorf), 2, ',', '.'); ?>
                                                                        </font>
                                                                    </td>
                                                                    <td></td>
                                                                </tr>
                                                            </table>
                                                            <table>
                                                                <tr>
                                                                    <td colspan="" align="right">Alq. CPL. ICMS %</td>
                                                                    <td><input <?=$readonly ?> size="3" type="text" value="<?=$_1_u_nf_icmscpl ?>"></td>
                                                                    <td colspan="2" align="right">Vlr. CPL. ICMS R$</td>
                                                                    <td><input <?=$readonly ?> size="8" type="text" value="<?=$_1_u_nf_vlricmscpl ?>"></td>
                                                                    <td>
                                                                        <font color="red">Observar se existe na observação da NF aproveitamento de ICMS.</font>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?
                                        } //if($qtdx>0){
                                    } //tipo c e s
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?
                    }
                    ?>
                </div>
                <!------------------------  CONTROLE NF ------------------------>

                <!------------------------  Pagamento ------------------------>
                <?
                /**********************************************************************************************************************
                 *												|							|										  *
                 *												|	  Modal Pagamento   	|										  *
                 *												V							V										  *
                 ***********************************************************************************************************************/
                $bloquearNoConcluido = ($_1_u_nf_status == 'CONCLUIDO' || $_1_u_nf_status == 'CANCELADO') ? "readonly='readonly'" : "";
                $corInputBloqueado = ($_1_u_nf_status == 'CONCLUIDO' || $_1_u_nf_status == 'CANCELADO') ? "bloqueio-input" : "";
                ?>
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_pagamento">
                    <?
                    if(!empty($_1_u_nf_vnf)){
                        $vsubtotal = $_1_u_nf_vnf;
                    } elseif(!empty($valorx)){
                        $valorx = $valorx - $desconto;
                        $vsubtotal = $valorx;
                    }

                    if(!empty($fretex)){
                        $_1_u_nf_frete = $fretex;
                    }

                    if($_1_u_nf_tiponf == "S" or $_1_u_nf_tiponf == 'R' or $_1_u_nf_tiponf == 'D'){
                        $imp_serv = (tratanumero($_1_u_nf_pis) + tratanumero($_1_u_nf_cofins) + tratanumero($_1_u_nf_csll) + tratanumero($_1_u_nf_inss) + tratanumero($_1_u_nf_ir) + tratanumero($_1_u_nf_issret));
                        $imp_serv = ($imp_serv < 0) ? 0 : $imp_serv;
                        $vtotal = $vtotal - $imp_serv;
                    }
                    ?>
                    <input <?=$readonly ?> <?=$vreadonly ?> name="_1_<?=$_acao?>_nf_total" id="vlrtotal" class="size6 vlrtotal" type="hidden" value="<?=number_format(tratanumero($vtotal), 2, ',', '.') ?>" vdecimal>
                    <div class="panel-body">
                        <? if($qtdparcelas < 1){ ?>
                            <div class="col-md-12">
                                <span class="d-flex justify-content-center form-control alert-warning font-bold" style="margin-top: 15px;">
                                    <font color="red" class="alerta-informacao"><? echo "Venc(s) a conferir/salvar"; ?></font>
                                </span>
                            </div>
                        <? } ?>
                        <div class="col-md-6">
                            <? if($_1_u_nf_tiponf == "E"){ ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <div class="col-sm-1">
                                                <?
                                                if($_1_u_nf_sped == 'Y'){
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input title="sped" type="checkbox" <?=$checked ?> name="namesped" onclick="altcheck('nf','sped',<?=$_1_u_nf_idnf ?>,'<?=$vchecked ?>')">
                                                <label class="pl10"> Sped? </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <? } ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Emissão:</label>
                                    <div class="input-group col-md-12">
                                        <? echo ($_1_u_nf_dtemissao); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Data Recebimento:</label>
                                    <div class="input-group col-md-12">
                                        <? if(array_key_exists("alteracampoentradanf", getModsUsr("MODULOS"))) {?>
                                            <input name="_1_<?=$_acao?>_nf_prazo" disabled="disabled" autocomplete="off" id="fdata2" class="calendario atualiza-recebimento size8 desabilitado" <? $_1_u_nf_status == 'CONCLUIDO' or $qtdc100 > 0 ? 'disabled="disabled"' : '' ?> type="text" value="<?= $_1_u_nf_prazo ?>" />
                                                <i class="fa fa-pencil btn-lg pointer" title='Editar Data de Entrada' onclick='alteraEdicaoDataEntrada()'></i>
                                        <?} else if(empty($_1_u_nf_prazo)) {?>
                                            <input name="_1_<?=$_acao?>_nf_prazo" vnulo class="calendario size8 atualiza-recebimento" id="fdata1" type="text" value="<?=$_1_u_nf_prazo?>" autocomplete="off">
                                        <?} else {?>
                                            <input name="_1_<?=$_acao?>_nf_prazo" type="hidden" value="<?= $_1_u_nf_prazo?>" autocomplete="off" />                                             <span><?= $_1_u_nf_prazo ?></span>
                                        <?}?>
                                        <? /*if($_1_u_nf_status == 'CONCLUIDO'  or $qtdc100 > 0){ ?>
                                            <input name="_1_<?=$_acao?>_nf_prazo" type="hidden" value="<?=$_1_u_nf_prazo?>">
                                        <? }*/ ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group col-md-3">
                                    <label>Gera Parcela:</label>
                                    <div class="input-group col-md-12">
                                        <select class="size6" name="_1_<?=$_acao?>_nf_geracontapagar" vnulo <?=$bloquearNoConcluido?>>
                                            <? fillselect(NfEntradaController::$_simNao, $_1_u_nf_geracontapagar); ?>
                                        </select>
                                    </div>
                                </div>
                                <? if($_1_u_nf_geracontapagar == "Y"){ ?>
                                    <div class="form-group col-md-3">
                                        <label>Tipo:</label>
                                        <div class="input-group col-md-12">
                                            <select class="size8" name="_1_<?=$_acao?>_nf_tipocontapagar" id="tipo" <?=$bloquearNoConcluido?>>
                                                <? fillselect(NfEntradaController::$_debitoCredito, $_1_u_nf_tipocontapagar); ?>
                                            </select>
                                        </div>
                                    </div>
                                <? }

                                if($_1_u_nf_geracontapagar == "Y"){
                                    ?>
                                    <div class="form-group col-md-6">
                                        <label>Pagamento:</label>
                                        <div class="input-group col-md-12">
                                            <? if(empty($_1_u_nf_idformapagamento)){ ?>
                                                <input name="_nf_idformapagamentoant" type="hidden" value="<?=$_1_u_nf_idformapagamento?>">
                                                <input id="forma_pag" cbvalue='<?=$_1_u_nf_idformapagamento?>' name="_1_<?=$_acao?>_nf_idformapagamento" vnulo value="<?=$arrayFormaPagamento[$_1_u_nf_idformapagamento]['descricao']?>">
                                            <? } else { 
                                                $corLabel = ($_1_u_nf_status == 'CONCLUIDO' || $_1_u_nf_status == 'CANCELADO') ? 'label.alert-warning-concluido' : 'alert-warning';
                                                ?>
                                                <label class="<?=$corLabel?>"><?=traduzid('formapagamento', 'idformapagamento', 'descricao', $_1_u_nf_idformapagamento) ?></label>
                                                <? if($_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO') { ?>
                                                    <i onclick="mostraInputFormapagamento(this)" class="fa fa-pencil azul pl7"></i>
                                                <? } ?>
                                                <input name="_nf_idformapagamentoant" type="hidden" value="<?=$_1_u_nf_idformapagamento?>">
                                                <input style="display: none;" cbvalue='<?=$_1_u_nf_idformapagamento?>' id="forma_pag" name="_1_<?=$_acao?>_nf_idformapagamento" vnulo>
                                            <? } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group col-md-3">
                                        <label>Parcelas:</label>
                                        <div class="input-group col-md-12">
                                            <?
                                            for ($selparcelas = 1; $selparcelas <= 120; $selparcelas++){
                                                if($selparcelas == 1){
                                                    $select = "select " . $selparcelas . ",'" . $selparcelas . "x' ";
                                                } else {
                                                    $select .= "union select " . $selparcelas . ",'" . $selparcelas . "x' ";
                                                }
                                            }
                                            ?>
                                            <select class="size6" name="_1_<?=$_acao?>_nf_parcelas" id="parcelas" onchange="atualizaparc(this)" <?=$bloquearNoConcluido?>>
                                                <? fillselect($select, $_1_u_nf_parcelas); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <? if(!empty($_1_u_nf_idformapagamento)){
                                        $rowdias = NfEntradaController::buscarConfiguracoesFormaPagamento($_1_u_nf_idformapagamento);
                                        if(empty($_1_u_nf_diasentrada) and $_1_u_nf_diasentrada != 0){
                                            $_1_u_nf_diasentrada = $rowdias['campo'];
                                            if(empty($_1_u_nf_diasentrada)){
                                                $_1_u_nf_diasentrada = 0;
                                            }
                                        }
                                        ?>
                                        <div class="form-group col-md-3">
                                            <label>1º Vencimento:</label>
                                            <div class="input-group col-md-12">
                                                <input class="size3 <?=$corInputBloqueado?>" name="_1_<?=$_acao?>_nf_diasentrada" type="text" value="<?=$_1_u_nf_diasentrada ?>" <?=$bloquearNoConcluido?> onchange="atualizadiasentrada(this)" <?=$bloquearNoConcluido?>>&nbsp
                                                <select class="size6" name="_1_<?=$_acao?>_nf_tipointervalo" <?=$bloquearNoConcluido?>>
                                                    <? fillselect(NfEntradaController::$_periodo, $_1_u_nf_tipointervalo); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?
                                        if($_1_u_nf_parcelas > 1){
                                            $strdivtab = "style='display:block;'";
                                        } else {
                                            $strdivtab = "style='display:none;'";
                                        } ?>
                                        <div class="form-group col-md-3">
                                            <label>Intervalo:</label>
                                            <div class="input-group col-md-12">
                                                <input class="size4 <?=$corInputBloqueado?>" name="_1_<?=$_acao?>_nf_intervalo" type="text" value="<?=$_1_u_nf_intervalo?>" onchange="atualizaintervalo(this)" <?=$bloquearNoConcluido?>>
                                            </div>
                                        </div>
                                        <?
                                    }
                                }
                                ?>
                            </div>

                            <?
                            if($_1_u_nf_geracontapagar == "Y"){
                                ?>
                                <div class="col-md-12">
                                    <div class="form-group col-md-12">
                                        <div class="col-md-9">
                                            <div class="input-group col-md-12">
                                                <? if($_1_u_nf_proporcional == 'Y'){
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                } ?>
                                                <input title="Editar proporções" type="checkbox" <?=$checked ?> name="nameproporcional" onclick="altcheck('nf','proporcional',<?=$_1_u_nf_idnf ?>,'<?=$vchecked ?>')">
                                                <label class="pl10">Editar Proporção:</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div>
                                        <span class="divBotaoSalvar"></span>
                                    </div>
                                    <?
                                    // Calcula a data daqui 3 dias
                                    if(empty($_1_u_nf_diasentrada)){
                                        $_1_u_nf_diasentrada = 0;
                                    }

                                    $q = 10;
                                    if(!empty($_1_u_nf_idnf)){
                                        if($_1_u_nf_status=='CONCLUIDO'){
                                            $disableddata="disabled='disabled'";
                                        }else{
                                            $disableddata="";
                                        }
                                        $rescx = NfEntradaController::buscarIdNfConfPagar($_1_u_nf_idnf);
                                        $qtdpx = count($rescx);
                                        $v = 0;
                                        $tproporcao = 0;
                                        $count = 1;
                                        foreach ($rescx as $rowcx){
                                            $q++;
                                            $i++;
                                            if($_1_u_nf_tipointervalo == "M"){
                                                $strintervalo = 'MONTH';
                                            } elseif($_1_u_nf_tipointervalo == "Y"){
                                                $strintervalo = 'YEAR';
                                            } else {
                                                $strintervalo = 'days';
                                            }
                                            $q++;
                                            if($v == 0){
                                                $dias = $_1_u_nf_diasentrada;
                                            } else {

                                                $dias = $_1_u_nf_diasentrada + ($_1_u_nf_intervalo * $v);
                                            }
                                            $pvdate = $_1_u_nf_dtemissao;
                                            $pvdate = str_replace('/', '-', $pvdate);
                                            $timestamp = strtotime(date('Y-m-d', strtotime($pvdate)) . "+" . $dias . " " . $strintervalo . "");

                                            $eFeriado = 1;

                                            while ($eFeriado >= 1){
                                                $rowdia =  NFController::verificaFeriadoFds(date('Y-m-d', $timestamp));

                                                if($rowdia['eFeriado'] == 1){
                                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+1 days");
                                                    $eFeriado = 1;
                                                } else {
                                                    $eFeriado = 0;
                                                }
                                            }

                                            if(empty($rowcx['dmadatareceb'])){
                                                $rowcx['dmadatareceb'] = date('d/m/Y', $timestamp);
                                            }

                                            $proporcao = 100 / $_1_u_nf_parcelas;
                                            if(empty($rowcx['proporcao'])){
                                                $rowcx['proporcao'] = $proporcao;
                                            }
                                            $tproporcao = $tproporcao + $rowcx['proporcao'];
                                            // Exibe o resultado
                                            ?>
                                            <div class="col-md-6">
                                                <div class="col-md-12">
                                                    <div class="col-md-5">
                                                        <font color="red"><? echo (($v + 1) . "º"); ?>:</font>
                                                        <input style="width: 100px;" name="_nfc<?=$q ?>_u_nfconfpagar_idnfconfpagar" type="hidden" value="<?=$rowcx['idnfconfpagar']?>">
                                                        <input <?=$disableddata?> class="size8 calendario dataconfdate" id="dataconf<?=$rowcx['idnfconfpagar']?>" idnfconfpagar="<?=$rowcx['idnfconfpagar']?>" name="_nfc<?=$q ?>_u_nfconfpagar_datareceb" type="text" value="<?=$rowcx['dmadatareceb']?>">
                                                        <?
                                                        if($rowcx['dmadatareceb'] != date('d/m/Y', $timestamp)){
                                                        ?>
                                                            &nbsp;<?=date('d/m/Y ', $timestamp) ?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema"></i>
                                                        <?
                                                        }
                                                        ?>
                                                    </div>
                                                    <? if($_1_u_nf_proporcional == 'Y'){ ?>
                                                        <div class="col-md-2 alinhar-texto-cabecalho">
                                                            Proporção:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input class="size4" name="_nfc<?=$q ?>_u_nfconfpagar_proporcao" type="text" value="<?=round($rowcx['proporcao'], 2) ?>" onchange="atualizaproporcao(this,<?=$rowcx['idnfconfpagar']?>)">
                                                        </div>
                                                    <? } ?>
                                                    <div class="col-md-1">
                                                        <? if(empty($rowcx['obs'])){ ?>
                                                            <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde bnt-lg-sete pointer" onclick="nfconfpagar(<?=$rowcx['idnfconfpagar']?>,<?=$q ?>)" title="Inserir observação."></i>
                                                        <? } else { ?>
                                                            <div class="observacao">
                                                                <i data-target="webuiPopover0" class="fa fa-info-circle fa-1x azul pointer hoverpreto bnt-lg-sete tip" onclick="nfconfpagar(<?=$rowcx['idnfconfpagar']?>,<?=$q ?>)"></i>
                                                            </div>
                                                            <div class="webui-popover-content">
                                                                <b>Obs:</b> <?=$rowcx['obs']?> <br />
                                                                <b>Alterado em:</b> <?=dmahms($rowcx['alteradoem']) ?><br />
                                                                <b>Alterado por:</b> <?=$rowcx['alteradopor']?><br />
                                                            </div>
                                                        <? } ?>
                                                    </div>
                                                    <div class="col-md-2 alinhar-texto-cabecalho">
                                                        <font color="red"><?=round($tproporcao, 2) ?></font>
                                                    </div>

                                                    <div id="<?=$q ?>_editarnfconfpagar" class="hide">
                                                        <table>
                                                            <tr>
                                                                <td>
                                                                    <textarea name="<?=$q ?>_nfconfpagar_obs" id="<?=$q ?>_nfconfpagar_obs" style="width: 570px; height: 41px; margin: 0px;"><?=$rowcx['obs']?></textarea>
                                                                    <input id="<?=$q ?>_nfconfpagar_idnfconfpagar" name="<?=$q ?>_nfconfpagar_idnfconfpagar" type="hidden" value="<?=$rowcx['idnfconfpagar']?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="panel panel-default">
                                                                                <div class="panel-body">
                                                                                    <div class="row col-md-12">
                                                                                        <div class="col-md-2" style="text-align:right">Alterado Por:</div>
                                                                                        <div class="col-md-4" style="text-align:left"><?=$rowcx['alteradopor']?></div>
                                                                                        <div class="col-md-2" style="text-align:right">Alterado Em:</div>
                                                                                        <div class="col-md-4" style="text-align:left"><?=dmahms($rowcx['alteradoem']); ?></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <?
                                            $v++;
                                            if($count % 2 == 0){
                                                echo '</div><div class="col-md-12">';
                                            }
                                            $count++;
                                        } //for ($v = 0; $v < $_1_u_nf_parcelas; $v++){                            
                                    }
                                    ?>
                                </div>
                                <?
                            } //if($_1_u_nf_geracontapagar=="Y"){
                            ?>
                        </div>

                        <div class="col-md-6" style="margin-top: -20px;">
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" href="#Multiplicar">Multiplicar</div>
                                <div class="panel-body">
                                    <div id="Multiplicar" class="collapse">
                                        <div class="col-md-12">
                                            <div class="col-md-12">
                                                <div class="form-group col-md-2">
                                                    <label>Qtd. Vezes:</label>
                                                    <div class="input-group col-md-12">
                                                        <select name="qtdvezes" id="qtdvezes" class="size6">
                                                            <?
                                                            for ($selIntervalo = 1; $selIntervalo <= 120; $selIntervalo++){
                                                                if($selIntervalo == 1){
                                                                    $selectIntervalo = "select " . $selIntervalo . ",'" . $selIntervalo . "x' ";
                                                                } else {
                                                                    $selectIntervalo .= "union select " . $selIntervalo . ",'" . $selIntervalo . "x' ";
                                                                }
                                                            }
                                                            fillselect($selectIntervalo, $_1_u_nf_parcelas);
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Intervalo de:</label>
                                                    <div class="input-group col-md-12">
                                                        <input name="intervalo" type="text" class="size5" value="">&nbsp
                                                        <select name="tipointervalo" class="size5">
                                                            <?
                                                            fillselect(NfEntradaController::$_periodo);
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <div class="input-group col-md-12 pt25">
                                                        <button id="cbSalvar" type="button" class="btn btn-danger btn-xs" onclick="multiplicarnf(<?=$_1_u_nf_idnf ?>)" title="Multiplicar">
                                                            <i class="fa fa-circle"></i>Multiplicar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?
                                            $listarNfFluxo = NfEntradaController::buscarNfEFluxoStatusPorTipoObjetoSoliPor($_1_u_nf_idnf, 'nf');
                                            if($listarNfFluxo['qtdLinhas'] > 0){
                                                ?>
                                                <table class="table table-striped planilha" style="margin-top:10px;">
                                                    <tr>
                                                        <th>Cópia(s)</th>
                                                        <th>Nº NF</th>
                                                        <th>Emissão</th>
                                                        <th>Status</th>
                                                    </tr>
                                                    <? $z = 0;
                                                    foreach ($listarNfFluxo['dados'] as $_nfFluxo){
                                                        if($_nfFluxo["tiponf"] == 'C'){
                                                            $vtiponf = "Compra";
                                                            $link = "nfentrada";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'O'){
                                                            $vtiponf = "Compra";
                                                            $link = "nfentrada";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'S'){
                                                            $vtiponf = "Servi&ccedil;o";
                                                            $link = "nfentrada";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'T'){
                                                            $vtiponf = "Cte";
                                                            $link = "nfentrada";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'E'){
                                                            $vtiponf = "Consession&aacute;ria";
                                                            $link = "nfentrada";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'M'){
                                                            $vtiponf = "Manual/Cupom";
                                                            $link = "nfentrada";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'B'){
                                                            $vtiponf = "Recibo";
                                                            $link = "nfentrada";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'R'){
                                                            $vtiponf = "PJ";
                                                            $link = "comprasrh";
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'F'){
                                                            $vtiponf = "Fatura";
                                                            $link = "nfentrada";
                                                            $tipo = 'F';
                                                        }
                                                        if($_nfFluxo["tiponf"] == 'D'){
                                                            $vtiponf = "Sócios";
                                                            $link = "comprassocios";
                                                            $tipo = 'D';
                                                        }

                                                        $z = $z + 1;
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <a class="hoverazul pointer" title="Compra" onclick="janelamodal('?_modulo=<?=$link ?>&_acao=u&idnf=<?=$_nfFluxo['idnf']?>&_idempresa=<?=$_nfFluxo['idempresa']?>')"><?=$_nfFluxo["idnf"]?></a>
                                                            </td>
                                                            <td>
                                                                <input name="_l<?=$z ?>_u_nf_idnf" type="hidden" value="<?=$_nfFluxo["idnf"]?>">
                                                                <input name="_l<?=$z ?>_u_nf_nnfe" type="text" value="<?=$_nfFluxo["nnfe"]?>">
                                                            </td>
                                                            <td>
                                                                <input name="_l<?=$z ?>_u_nf_dtemissao" type="text" class="calendario" value="<?=dma($_nfFluxo["dtemissao"]) ?>">
                                                            </td>
                                                            <td>
                                                                <?=$_nfFluxo["status"]?>
                                                            </td>
                                                        </tr>
                                                        <?
                                                    }
                                                    ?>
                                                </table>
                                                <?
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" title="clique para transferir">
                                        Transferir
                                    </div>
                                </div>
                                <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                    <div class="panel-body">
                                        <? 
                                        $listarNf = NfEntradaController::buscarNfEFluxoStatusPorTipoObjetoSoliPor($_1_u_nf_idnf, 'nfentradatransferencia');                                        
                                        if($listarNf['qtdLinhas'] > 0){ ?>
                                            <button class="btn btn-default btn-xs" id="transferir" onclick="transferido('')">
                                                <i class="fa fa-circle"></i> Transferido
                                            </button>
                                        <? } else { ?>
                                            <button class="btn btn-warning btn-xs" onclick="transferir()">
                                                <i class="fa fa-circle"></i> Transferir
                                            </button>
                                        <? } ?>
                                    </div>
                                </div>
                            </div> -->

                        </div>
                        <?
                        if(!empty($_acao == 'u')){
                            $listarFormaPagamentoContaPagar = NfEntradaController::buscarContaPagarFormaPagamentoPorIdObejtoOrigem($_1_u_nf_idnf, 'nf', 'gnre', $_1_u_nf_idnf);
                            $qtdrx = $listarFormaPagamentoContaPagar['qtdLinhas'];
                            if($qtdrx > 0) //tem contapagaritem
                            {
                                ?>
                                <div class=" row">
                                    <div class="col-md-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading _1">Extrato</div>
                                            <div class="panel-body" style="overflow-x: auto;">
                                                <table class="table table-striped planilha">
                                                    <? $totalcp = 0;
                                                    foreach ($listarFormaPagamentoContaPagar['dados'] as $formaPagamentoContaPagar){
                                                        $totalcp = $totalcp + $formaPagamentoContaPagar["valor"];
                                                        if(!empty($formaPagamentoContaPagar['idcontapagar']) && (!empty($formaPagamentoContaPagar['status']) && $formaPagamentoContaPagar['status'] != 'INATIVO')){
                                                            $contaPagar = NfEntradaController::buscarFaturaPorId($formaPagamentoContaPagar['idcontapagar']);
                                                        } else {
                                                            $contaPagar = NfEntradaController::buscarContaPagarItem($formaPagamentoContaPagar['idcontapagaritem']);
                                                            if(empty($contaPagar['idcontapagar'])){
                                                                $contaPagar = NfEntradaController::buscarFaturaPorId($formaPagamentoContaPagar['idcontapagar']);
                                                            }
                                                        }
                                                        ?>
                                                        <tr>
                                                            <th>Fatura:</th>
                                                            <td><? echo ($contaPagar["parcela"]); ?></td>
                                                            <th>Parcela:</th>
                                                            <td class="nowrap"><?=$formaPagamentoContaPagar['parcela']?> de <?=$formaPagamentoContaPagar['parcelas']?> </td>
                                                            <th>Obs:</th>
                                                            <td>
                                                                <?
                                                                if(!empty($formaPagamentoContaPagar['obsi'])){
                                                                    echo ($formaPagamentoContaPagar['obsi']);
                                                                } else {
                                                                ?>
                                                                    <input name="<?=$i ?>idcontapagarobs" id="idcontapagarobs" style="width: 200px; font-size: 11px" type="text" onchange="atualizaobscp(this, <?=$contaPagar['idcontapagar']?>)" value="<?=$contaPagar["obs"]?>">
                                                                <?
                                                                }
                                                                ?>
                                                            </td>
                                                            <? if($formaPagamentoContaPagar['agruppessoa'] == 'N' and $formaPagamentoContaPagar['agrupado'] == 'Y'){ ?>
                                                                <th>Fatura Automática:</th>
                                                                <td>
                                                                    <? if(empty($formaPagamentoContaPagar['idformapagamento'])){ ?>
                                                                        <select <? if($formaPagamentoContaPagar['status'] == 'QUITADO'){ ?> disabled="disabled" <? } ?> class="size15" name="contapagaritem_idformapagamento" onchange="atualizacontaitem(this, <?=$formaPagamentoContaPagar['idformapagamento']; ?>, '<?=$formaPagamentoContaPagar['status']?>', '<?=$formaPagamentoContaPagar['idcontapagar']?>', '<?=$formaPagamentoContaPagar['idcontapagaritem']?>')">
                                                                            <option value=""></option>
                                                                            <? fillselect(NfEntradaController::listarFormapagamentoAgrupadoPorEmpresa(), $formaPagamentoContaPagar['idformapagamento']); ?>
                                                                        </select>
                                                                    <? } else { ?>
                                                                        <label class="alert-warning"><?=traduzid('formapagamento', 'idformapagamento', 'descricao', $formaPagamentoContaPagar['idformapagamento']) ?></label>
                                                                        <i onclick="mostraInputFormapagamento(this)" class="fa fa-pencil azul"></i>
                                                                        <select class="size15" style="display: none;" name="contapagaritem_idformapagamento" onchange="atualizacontaitem(this, <?=$formaPagamentoContaPagar['idformapagamento']; ?>, '<?=$formaPagamentoContaPagar['status']?>', '<?=$formaPagamentoContaPagar['idcontapagar']?>', '<?=$formaPagamentoContaPagar['idcontapagaritem']?>')">
                                                                            <option value=""></option>
                                                                            <? fillselect(NfEntradaController::listarFormapagamentoAgrupadoPorEmpresa(), $formaPagamentoContaPagar['idformapagamento']); ?>
                                                                        </select>
                                                                    <? } ?>
                                                                </td>
                                                            <? } ?>
                                                            <th>Recebimento:</th>
                                                            <td>
                                                                <input type="hidden" class="datarecebparc" value="<?=$contaPagar["datareceb"]?>" statusCI="<?=$contaPagar["status"]?>" parcela="<?=$formaPagamentoContaPagar['parcela']?>">
                                                                <?=$contaPagar["datareceb"]?>
                                                            </td>
                                                            <th>Valor:</th>
                                                            <td>
                                                                <?
                                                                if($formaPagamentoContaPagar["status"] != "QUITADO"){

                                                                    if($formaPagamentoContaPagar['tobj'] == 'contapagar'){
                                                                ?>
                                                                        <input name="contapagaritem_valor" class="size10" type="text" value="<?=$formaPagamentoContaPagar["valor"]?>" onchange="atualizavlcp(this,<?=$formaPagamentoContaPagar['idcontapagaritem']?>)">
                                                                    <?
                                                                    } else {
                                                                    ?>
                                                                        <input name="contapagaritem_valor" class="size10" type="text" value="<?=$formaPagamentoContaPagar["valor"]?>" onchange="atualizavlitem(this,<?=$formaPagamentoContaPagar['idcontapagaritem']?>)">
                                                                    <?
                                                                    }
                                                                } else {
                                                                    ?>
                                                                    <?=number_format(tratanumero($formaPagamentoContaPagar["valor"]), 2, ',', '.'); ?>
                                                                <?
                                                                }
                                                                ?>
                                                            </td>
                                                            <th>Status:</th>
                                                            <td>
                                                                <label class="alert-warning">
                                                                    <? if(!empty($contaPagar["status"])){
                                                                        echo ($contaPagar["status"]);
                                                                    } else {
                                                                        echo ($formaPagamentoContaPagar["status"]);
                                                                    } ?>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <!-- consulta e verificacao da data fluxostatushist, se a data criadoem conta pagar for maior q data conclusao, mostra -->
                                                                <?
                                                                $listarNfContaPagar = NfEntradaController::buscarNfContaPagar($contaPagar['idcontapagar'], $pagvalmodulo, $_1_u_nf_idnf);
                                                                foreach ($listarNfContaPagar as $nfContaPagar){
                                                                    if(($nfContaPagar['criadoemcp'] > $nfContaPagar['criadoemfs'])){
                                                                        ?>
                                                                        <a class="fa fa-info-circle tip" title="Informações de Criação" data-toggle="popover" href="#<?=$contaPagar['idcontapagar']?>" data-trigger="hover"></a>
                                                                        <div id="modalpopover_<?=$contaPagar['idcontapagar']?>" class="modal-popover hidden">
                                                                            <table>
                                                                                <tr>
                                                                                    <td nowrap><b>Criado por:</b></td>
                                                                                    <td><?=dmahms($nfContaPagar['criadopor']) ?></td>
                                                                                </tr>
                                                                                <tr style="margin-top: 10px;">
                                                                                    <td nowrap><b>Criado em:</b> </td>
                                                                                    <td><?=dmahms($nfContaPagar['criadoemcp']) ?></td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                        <?
                                                                    }
                                                                }
                                                                ?>
                                                            </td>
                                                            <td id="contapagar_<?=$contaPagar["idcontapagar"]?>">
                                                                <a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$contaPagar['idcontapagar']?>');"></a>
                                                            </td>
                                                        </tr>
                                                    <?
                                                    } //while(mysqli_fetch_assoc($qrpx))                                    
                                                    ?>
                                                    <tr>
                                                        <td colspan="10" align="right"></td>
                                                        <td>Total:</td>
                                                        <td><b><?=number_format(tratanumero($totalcp), 2, ',', '.'); ?> <span class="valorDiferenteNota"></span></b></td>
                                                        <td colspan="5"></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="20">
                                                            <? if($_1_u_nf_status != "CONCLUIDO" ||  $_1_u_nf_status != "CANCELADO"){ ?>
                                                                <input value="<?=$qtdrx + 1 ?>" id="parcela_parcelas" type='hidden' name="parcela_parcelas">
                                                                <a class="fa fa-plus-circle verde pointer hoverazul" title="Nova Parcela" onclick="showModal()"></a>
                                                            <? } ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div> <!-- fim div faturamento -->
                                    </div>
                                    <?
                                    $listarFArquivosFaturamento = NfEntradaController::buscarInfFormapagamentoPorIdObjetoOrigemEIdObjeto($_1_u_nf_idnf, 'nf', $_1_u_nf_idnf, 'nf');
                                    $qtarq = $listarFArquivosFaturamento['qtdLinhas'];
                                    if($qtarq > 0){
                                        ?>
                                        <div class="col-md-12">
                                            <div class="panel panel-default">
                                                <div class="panel-heading _4" data-toggle="collapse" href='#arqfat'>Arquivos de Faturamento Anexos</div>
                                                <div class="panel-body" id='arqfat' style="overflow-x: auto;">
                                                    <table>
                                                        <? foreach ($listarFArquivosFaturamento as $arquivosFaturamento){ ?>
                                                            <tr>
                                                                <td align="center"><a title="Abrir arquivo" target="_blank" href="./upload/<?=$arquivosFaturamento["nome"]?>"><?=$arquivosFaturamento["nome"]?></a></td>
                                                            </tr>
                                                        <? } ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <? } ?>
                                </div>
                                <?
                            }

                            if($qtdrowsp == 0 and $qtdrx == 0 and $_1_u_nf_geracontapagar == "Y" and $_1_u_nf_status == "CONCLUIDO"){
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading _4">Extrato</div>
                                            <div class="panel-body">
                                                <table>
                                                    <tr>
                                                        <td align="center">
                                                            <font style="font-size: 25px;" color="red">NF não gerou parcela(s)!!!</font>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?
                            }
                            //if($qtdrowsp>0){
                        }
                        ?>
                    </div>
                </div>
                <!------------------------  Pagamento ------------------------>

                <!------------------------  Conferência ------------------------>
                <?
                /**********************************************************************************************************************
                 *												|							|										  *
                 *												|	 Modal Conferência     	|										  *
                 *												V							V										  *
                 ***********************************************************************************************************************/
                ?>
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_conferencia">
                    <?
                    if(in_array($_1_u_nf_tiponf, $arrTipoNf)){
                        if(!empty($_1_u_nf_idnfe)){
                            $listarNfPorServDesc = NfEntradaController::buscarNfporServDesc($_1_u_nf_idnfe);
                            $listNfPorServDesc = $listarNfPorServDesc['dados'];
                            if($listarNfPorServDesc['qtdLinhas'] > 0){
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading cabecalho" style="font-size:12px">
                                                <div class="row">
                                                    <div class="col-sm-1 sigla-empresa"></div>
                                                    Registros de CTe
                                                </div>
                                            </div>
                                            <?
                                            foreach ($listNfPorServDesc as $nfPorServDesc){
                                                $listarNfPessoa = NfEntradaController::buscarNfPessoaPorIdNf($nfPorServDesc['idnf']);
                                                $qtdnfe = empty($listarNfPessoa) ? 0 : count($listarNfPessoa);
                                                if($qtdnfe > 0){
                                                    ?>
                                                    <div class="panel-body">
                                                        <table class="table table-striped planilha">
                                                            <tr>
                                                                <th>CTe</th>
                                                                <th>Transporte</th>
                                                                <th>Emissão</th>
                                                                <th>Status</th>
                                                                <th>Valor</th>
                                                                <th></th>
                                                            </tr>
                                                            <?
                                                            $totalnfe = 0;
                                                            foreach ($listarNfPessoa as $nfPessoa){
                                                            ?>
                                                                <tr>
                                                                    <td><?=$nfPessoa['nnfe']?></td>
                                                                    <td><?=$nfPessoa['nome']?></td>
                                                                    <td><?=$nfPessoa['emissao']?></td>
                                                                    <td><?=$nfPessoa['status']?></td>
                                                                    <td><?=$nfPessoa['total']?></td>
                                                                    <td>
                                                                        <a class="fa fa-bars pointer hoverazul" title="CTe" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$nfPessoa['idnf']?>')"></a>
                                                                    </td>
                                                                </tr>
                                                            <?
                                                                $totalnfe += $nfPessoa['total'];
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td colspan="4" align="right">Total</td>
                                                                <td colspan="2"><?=number_format(tratanumero($totalnfe), 2, ',', '.') ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <?
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?
                            } // if(!empty(['idnf'])){
                        } //$_1_u_nf_idnfe
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading" style="font-size:12px">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <strong>Conferência</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <?
                                        $listarNfConferenciaItem = NfEntradaController::buscarNfConferenciaItem($_1_u_nf_idnf);
                                        $qtdq = $listarNfConferenciaItem['qtdLinhas'];

                                        switch ($_1_u_nf_tiponf){
                                            case "C":
                                                $tiponf = "danfe";
                                                break;
                                            case "S":
                                                $tiponf = "servico";
                                                break;
                                            case "T":
                                                $tiponf = "cte";
                                                break;
                                            case "E":
                                                $tiponf = "concessionaria";
                                                break;
                                            case "M":
                                                $tiponf = "manualcupom";
                                                break;
                                            case "B":
                                                $tiponf = "recibo";
                                                break;
                                            case "F":
                                                $tiponf = "fatura";
                                                break;
                                            case "D":
                                                $tiponf = "socios";
                                                break;
                                            case "R":
                                                $tiponf = "rh";
                                                break;
                                            case "O":
                                                $tiponf = "manualcupom";
                                                break;
                                            default:
                                                die("Erro ao identificar tipo de NF");
                                                break;
                                        }

                                        $conferenciaItem = NfEntradaController::buscarConferenciaItem($tiponf);
                                        $qtdcq = $conferenciaItem['qtdLinhas'];

                                        if($qtdq == 0 && $qtdcq > 0 && $tiponf != "socios"){
                                            NfEntradaController::inserirNfConferenciaItem($_1_u_nf_idempresa, $_1_u_nf_idnf, $tiponf);
                                            $listarNfConferenciaItem = NfEntradaController::buscarNfConferenciaItem($_1_u_nf_idnf);
                                        }
                                        ?>
                                        <div class="col-md-12" style="padding: 0px !important;">                                            
                                            <div class="col-md-3">                                                
                                                <div class="col-md-10"><label>Previsão Entrega:</label></div>                                                  
                                                <div class="col-md-2">
                                                    <?
                                                    $ListarHistoricoModal = NfEntradaController::buscarHistoricoAlteracao($_1_u_nf_idnf, 'previsaoentrega');
                                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                                    if($qtdvh > 0){
                                                        ?>
                                                        <div class="historicoPrevisaoEntrega" idnfitem="<?=$_itens["idnfitem"]?>">
                                                            <i title="Histórico da Previsão Entrega" class="fa btn-sm fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
                                                        </div>
                                                        <div class="webui-popover-content">
                                                            <br />
                                                            <table class="table table-striped planilha">
                                                                <?
                                                                if($qtdvh > 0){
                                                                ?>
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">De</th>
                                                                            <th scope="col">Para</th>
                                                                            <th scope="col">Justificativa</th>
                                                                            <th scope="col">Por</th>
                                                                            <th scope="col">Em</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?
                                                                        foreach ($ListarHistoricoModal as $historicoModal){
                                                                            ?>
                                                                            <tr>
                                                                                <td><?=$historicoModal['valor_old']?></td>
                                                                                <td><?=$historicoModal['valor']?></td>
                                                                                <td>
                                                                                    <?
                                                                                    if($historicoModal['justificativa'] == 'ATRASO') echo 'Atraso na Entrega';
                                                                                    elseif($historicoModal['justificativa'] == 'PEDIDO FORNECEDOR') echo 'A Pedido do Fornecedor';
                                                                                    else echo $historicoModal['justificativa'];
                                                                                    ?>
                                                                                </td>
                                                                                <td><?=$historicoModal['nomecurto']?></td>
                                                                                <td><?=dmahms($historicoModal['criadoem']) ?></td>
                                                                            </tr>
                                                                        <?
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                    <?
                                                                }
                                                                ?>
                                                            </table>
                                                        </div>
                                                        <?
                                                    } else {
                                                        echo '&nbsp;';
                                                    }
                                                    ?>
                                                </div>
                                            
                                                <div class="form-group" style="padding-top: 0px;">
                                                    <div class="input-group col-md-12" style="top: -8px;">
                                                        <? if($_1_u_nf_status == 'APROVADO' or $_1_u_nf_status == 'RECEBIDO' or $_1_u_nf_status == 'CONCLUIDO'){
                                                            $vnulo = "vnulo";
                                                        } else {
                                                            $vnulo = "";
                                                        }

                                                        if(empty($_1_u_nf_previsaoentrega)){
                                                        ?>
                                                            <div class="col-md-12">
                                                                <input name="_1_<?=$_acao?>_nf_previsaoentrega" onkeydown="return false" class="calendario" type="text" <?=$vnulo?> value="<?=dma($_1_u_nf_previsaoentrega) ?>">
                                                            </div>
                                                            <?
                                                        } else {
                                                            ?>
                                                            <div class="col-md-10">
                                                                <input name="_1_<?=$_acao?>_nf_previsaoentrega" readonly="readonly" style="background-color: #f2f2f2;" type="text" <?=$vnulo?> value="<?=dma($_1_u_nf_previsaoentrega) ?>">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <i class="fa fa-pencil btn-lg pointer" title='Editar Previsão Entrega' onclick="alteravalor('previsaoentrega','<?=dma($_1_u_nf_previsaoentrega) ?>','modulohistorico',<?=$_1_u_nf_idnf ?>,'Previsão de Entrega')"></i>
                                                            </div>
                                                            <?
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--div class="form-group col-md-3">
                                                <label>Data Recebimento:</label>
                                                <div class="input-group col-md-12">
                                                    <? if(array_key_exists("alteracampoentradanf", getModsUsr("MODULOS"))) {?>
                                                        <input name="_1_<?=$_acao?>_nf_prazo" disabled="disabled" autocomplete="off" id="fdata4" class="calendario size25 atualiza-recebimento desabilitado" <? $_1_u_nf_status == 'CONCLUIDO' or $qtdc100 > 0 ? 'disabled="disabled"' : '' ?> type="text" value="<?= $_1_u_nf_prazo ?>" />
                                                            <i class="fa fa-pencil btn-lg pointer" title='Editar Data de Entrada' onclick='alteraEdicaoDataEntrada()'></i>
                                                    <?} else if(empty($_1_u_nf_prazo)) {?>
                                                        <input name="_1_<?=$_acao?>_nf_prazo" vnulo class="calendario atualiza-recebimento" id="fdata4" type="text" value="<?=$_1_u_nf_prazo?>" autocomplete="off">
                                                    <?} else {?>
                                                        <input name="_1_<?=$_acao?>_nf_prazo" type="hidden" value="<?= $_1_u_nf_prazo?>" autocomplete="off" />                                             <span><?= $_1_u_nf_prazo ?></span>
                                                    <?}?>
                                                    <? if($_1_u_nf_status == 'CONCLUIDO'  or $qtdc100 > 0){ ?>
                                                        <input name="_1_<?=$_acao?>_nf_prazo" type="hidden" value="<?=$_1_u_nf_prazo?>">
                                                    <? } ?>
                                                </div>
                                            </div-->
                                            <div class="form-group col-md-3">
                                                <label>Responsável Recebimento:</label>
                                                <div class="input-group col-md-12">
                                                    <select name="_1_<?=$_acao?>_nf_respinspecao" onchange="atualizaconf(this,'respinspecao');">
                                                        <option value=""></option>
                                                        <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(1, 'funcionarioCb', 'ATIVO'), $_1_u_nf_respinspecao); ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Transportador:</label>
                                                <div class="input-group col-md-12">
                                                    <select name="_1_<?=$_acao?>_nf_idtransportadora" onchange="atualizaconf(this,'idtransportadora');">
                                                        <option value=""></option>
                                                        <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(11, 'pessoasPorSession', 'ATIVO'), $_1_u_nf_idtransportadora); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 p0">
                                            <?
                                            $classPendencia = ($listarNfConferenciaItem['qtdLinhas'] == 0 && $tiponf != "socios" && $tiponf != "rh") ? 'col-md-12' : 'col-md-6';
                                            if($tiponf != "socios" && $tiponf != "rh"){
                                                ?>
                                                <div class="col-md-6 p0">
                                                    <?
                                                    $conf = 0;
                                                    foreach ($listarNfConferenciaItem['dados'] as $conferencia){
                                                        ?>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <div class="col-md-2">
                                                                    <input name="_conf<?=$conf?>_<?=$_acao?>_nfconferenciaitem_idnfconferenciaitem" type="hidden" value="<?=$conferencia['idnfconferenciaitem'] ?>">
                                                                    <select name="_conf<?=$conf?>_<?=$_acao?>_nfconferenciaitem_resultado">
                                                                        <option value=""></option>
                                                                        <? fillselect(NfEntradaController::$_simNaoNenhum, $conferencia['resultado']); ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-10 top-align-sete">
                                                                    <?=$conferencia['qst']?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?
                                                        $conf++;
                                                    }
                                                    ?>
                                                </div>
                                                <?
                                            }
                                            ?>
                                            <br />
                                            <div class="<?=$classPendencia?>" style="margin-top: -40px;">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-default">
                                                            <div class="panel-heading">
                                                                <div class="row">
                                                                    <?
                                                                    if(!($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO')){ ?>
                                                                        <a title="Sinalizar que houve pendência nesta compra." class="fa fa-1x btn-lg fa-plus-circle verde pointer hoverazul" onclick="conferencia('pendente')"></a>
                                                                    <? }  ?>                                                                    
                                                                    <strong>Divergência</strong>
                                                                    <span data-toggle="collapse" href="#listarDivergencia" aria-expanded="false" style="float: right;">
                                                                        <i class="fa fa-arrows-v pointer" style="padding: 5px 10px;"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="panel-body" id="listarDivergencia">
                                                                <?
                                                                $listarPendencia = NfEntradaController::buscarNfPendencia($_1_u_nf_idnf);
                                                                $qtdq = $listarPendencia['qtdLinhas'];
                                                                if($qtdq > 0){
                                                                    $l = 0;
                                                                    foreach ($listarPendencia['dados'] as $pendencia){
                                                                        $l = $l + 1;
                                                                        if($pendencia['status'] == 'PENDENTE'){
                                                                            $corresolv = "red";
                                                                            $corobspend = "#FF6A6A";
                                                                            $strreadonly = "";
                                                                        } else {
                                                                            $corresolv = "red";
                                                                            $corobspend = "#32CD32";
                                                                            $strreadonly = "readonly='readonly'";
                                                                        }
                                                                        if(!empty($pendencia['descr'])){
                                                                            $strreadonlyd = "readonly='readonly'";
                                                                        } else {
                                                                            $strreadonlyd = "";
                                                                        }
                                                                        ?>
                                                                        <div class="col-md-12">
                                                                            <div class="row borderdivergencia">
                                                                                <div class="col-md-12">
                                                                                    <label class="alert-warning"><?=$l ?></label>                                                                                

                                                                                    <span style="float: right;">
                                                                                        <span class="oEmailorc">
                                                                                            <a class="fa fa-search azul pointer hoverazul" title="Ver Log" data-placement="left" data-target="webuiPopover0"></a>
                                                                                        </span>
                                                                                        <div class="webui-popover-content">
                                                                                            <li class="nowrap">PENDENCIA: <?=$pendencia["criadopor"]?> <?=dmahms($pendencia["criadoem"]) ?></li>
                                                                                            <li class="nowrap">TRATATIVA: <?=$pendencia["alteradopor"]?> <?=dmahms($pendencia["alteradoem"]) ?></li>
                                                                                        </div>
                                                                                        <? if($pendencia['status'] == 'RESOLVIDO'){ ?>
                                                                                            <label class="alert-success"> RESOLVIDO</label>
                                                                                        <? } else { ?>
                                                                                            <a title="Clicar neste icone irá alterar pedência para resolvida" class="fas fa fa-check-circle fan-2x verde btn-lg pointer" onclick="conferenciaok(<?=$pendencia['idnfpendencia']?>)"></a>
                                                                                            <label class="alert-warning"> PENDENTE</label>
                                                                                        <? } ?>
                                                                                    </span>
                                                                                </div>                                                                            
                                                                                <div class="col-md-1" align="right"><font style="color:<?=$corresolv ?> ">Pendência: </font></div>
                                                                                <div class="col-md-11">
                                                                                    <textarea <?=$strreadonly ?> <?=$strreadonlyd ?> style="width: 100%; height: 40px;" style=font-size:medium; name="nfpendencia_descr" onchange="atualizapendencia(<?=$pendencia['idnfpendencia']?>,this,'descr')"><?=$pendencia['descr']?></textarea>
                                                                                
                                                                                </div>
                                                                                <div class="col-md-1" align="right"><font style="color:<?=$corresolv ?> ">Tratativa: </font></div>
                                                                                <div class="col-md-11">
                                                                                    <textarea <?=$strreadonly ?> style="width: 100%; height: 40px;" style=font-size:medium; name="nfpendencia_tratativa" onchange="atualizapendencia(<?=$pendencia['idnfpendencia']?>,this,'tratativa')"><?=$pendencia['tratativa']?></textarea>
                                                                                </div>
                                                                            </div>
                                                                        </div>                    
                    
                                                                        <?
                                                                    }
                                                                } //if($qtdq>0){

                                                                ?>
                                                            </div>
                                                        </div>                                        
                                                    </div>                                        
                                                </div>                                        
                                            </div>                                        
                                        </div>                                        

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Observação:</label>
                                                <div class="input-group col-md-12">
                                                    <textarea rows="2" style=font-size:medium; name="_1_<?=$_acao?>_nf_obs" onchange="atualizaconf(this,'obs');"><?=$_1_u_nf_obs ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Observação Interna:</label>
                                                <div class="input-group col-md-12">
                                                    <textarea rows="2" style=font-size:medium; name="_1_<?=$_acao?>_nf_obsinterna"><?=$_1_u_nf_obsinterna ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>
                <!------------------------  Conferência ------------------------>

                <!------------------------  Recebimento   ------------------------>
                <? if($_1_u_nf_tiponf == 'T'){ ?>
                    <div role="tabpanel" class="tab-pane fade" id="nfentrada_recebimento">
                        <?
                        /**********************************************************************************************************************
                         *												|							|										  *
                        *												|	    Recebimento     	|										  *
                        *												V							V										  *
                        ***********************************************************************************************************************/
                        ?>
                        <div class="panel-body">
                            <div class="col-md-12">
                                <div class="form-group col-md-3">
                                    <label>Envio:</label>
                                    <div class="input-group col-md-12">
                                        <input name="_1_<?=$_acao?>_nf_envio" class="calendario" value="<?=$_1_u_nf_envio?>" vnulo>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Entrega:</label>
                                    <div class="input-group col-md-12">
                                        <div class="col-md-5" style="margin-left: -10px;">
                                            <input name="_1_<?=$_acao?>_nf_entrega" class="calendario" value="<?=$_1_u_nf_entrega == '0000-00-00' ? '' : $_1_u_nf_entrega ?>" vnulo>
                                            <?
                                            //colocar previsão de entrega 
                                            if(empty($_1_u_nf_obsenvio)){
                                                if(!empty($_1_u_nf_idtransportadora)){
                                                    $previsaoEntrega = NfEntradaController::buscarTransportadorPorIdpessoa($_1_u_nf_idtransportadora, true);
                                                }
                                                if(!empty($previsaoEntrega['observacaonfp'])){
                                                    $_1_u_nf_obsenvio = $previsaoEntrega['observacaonfp'];
                                                } else {
                                                    $_1_u_nf_obsenvio = "De 2 à 3 dias úteis";
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="col-md-7">
                                            <input style="color: red;" name="_1_<?=$_acao?>_nf_obsenvio" size="20" type="text" value="<?=$_1_u_nf_obsenvio?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Inf. Frete:</label>
                                    <div class="input-group col-md-12">
                                        <input name="_1_<?=$_acao?>_nf_inffrete" size="20" type="text" value="<?=$_1_u_nf_inffrete ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group col-md-12">
                                    <label>Tipo de Frete:</label>
                                    <div class="input-group col-md-12">
                                        <select name="_1_<?=$_acao?>_nf_tipofrete" <?=$disablednf ?>>
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::$_tipofrete, $_1_u_nf_tipofrete); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group col-md-3">
                                    <label>Transportadora:</label>
                                    <div class="input-group col-md-12">
                                        <?
                                        if(!empty($_1_u_nf_idpessoa)){
                                            $transportadora = NfEntradaController::buscarTransportadorPorIdpessoa($_1_u_nf_idpessoa, true);
                                            if(empty($_1_u_nf_idtransportadora)){
                                                $_1_u_nf_idtransportadora = $transportadora['idtransportadora'];
                                            } else {
                                                $idtransportadorapr = $_1_u_nf_idtransportadora;
                                            }

                                            if(!empty($idtransportadorapr)){
                                                $fverificactransp = "verificatransp(this," . $idtransportadorapr . ",'" . $transportadora['nome'] . "');";
                                            }
                                        }
                                        ?>
                                        <input name="idtransportadora" value="<?=$_1_u_nf_idtransportadora ?>" type="hidden">
                                        <select <?=$disablednf ?> name="_1_<?=$_acao?>_nf_idtransportadora" onchange="<?=$fverificactransp ?>">
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(11, 'pessoasPorSession', 'ATIVO'), $_1_u_nf_idtransportadora); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Resp. Envio:</label>
                                    <div class="input-group col-md-12">
                                        <input name="respenvio" value="<?=$_1_u_nf_respenvio?>" type="hidden">
                                        <select name="_1_<?=$_acao?>_nf_respenvio" id="respenvi" vnulo style="display: <?=$strdados ?>">
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(1, 'pessoasPorSession', 'ATIVO'), $_1_u_nf_respenvio); ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Peso (KG):</label>
                                    <div class="input-group col-md-12">
                                        <input name="_1_<?=$_acao?>_nf_peso" size="20" type="text" value="<?=$_1_u_nf_peso?>" vdecimal>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Custo (R$):</label>
                                    <div class="input-group col-md-12">
                                        <input name="_1_<?=$_acao?>_nf_custoenvio" size="20" type="text" value="<?=$_1_u_nf_custoenvio?>" vdecimal>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12" style="margin-left: 10px;">
                                <div class="form-group">
                                    <label style="color: red !important;">Obs. Interna:</label>
                                    <div class="input-group col-md-12">
                                        <textarea class="caixa" style="width: 99%; height: 40px; " name="_1_<?=$_acao?>_nf_obsinterna" onchange="atualizaobsint(this)"><?=$_1_u_nf_obsinterna ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?
                        $listarIdNfe = NfEntradaController::buscarIdNfeNfItemPorObsNotNULLEIdNf($_1_u_nf_idnf);
                        $idNfe = $listarIdNfe['dados'];
                        $qtdidnfe = $listarIdNfe['qtdLinhas'];
                        if($listarIdNfe['idnfe'] != "" && $qtdidnfe > 0){
                            $listarNfPorIdNfe = NfEntradaController::buscarNfPessoaPorIdNfe($listarIdNfe['idnfe']);
                            $qtdnfe = $listarNfPorIdNfe['qtdLinhas'];
                            if($qtdnfe > 0){
                                ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">Registros de NFe</div>
                                    <div class="panel-body">
                                        <table class="table table-striped planilha">
                                            <tr>
                                                <th>NFe</th>
                                                <th>Cliente</th>
                                                <th>Emissão</th>
                                                <th>Status</th>
                                                <th>Valor</th>
                                                <th></th>
                                            </tr>
                                            <?
                                            $totalcte = 0;
                                            foreach ($listarNfPorIdNfe as $nfe){
                                                if($rowcte['status'] != 'CANCELADO'){
                                                    $totalnfe = $totalnfe + $nfe['total'];
                                                }
                                                if($nfe['tiponf'] == 'V'){
                                                    $modv = 'pedido';
                                                } else {
                                                    $modv = 'nfentrada';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?=$nfe['nnfe']?></td>
                                                    <td><?=$nfe['nome']?></td>
                                                    <td><?=$nfe['emissao']?></td>
                                                    <td><?=$nfe['status']?></td>
                                                    <td><?=$nfe['total']?></td>
                                                    <td>
                                                        <a class="fa fa-bars pointer hoverazul" title="CTe" onclick="janelamodal('?_modulo=<?=$modv ?>&_acao=u&idnf=<?=$nfe['idnf']?>')"></a>
                                                    </td>
                                                </tr>
                                                <?
                                            }
                                            ?>
                                            <tr>
                                                <td colspan="4" align="right">Total</td>
                                                <td colspan="2"><?=number_format(tratanumero($totalnfe), 2, ',', '.') ?> <? if($_1_u_nf_total > 10){ ?> (<font title="Custo Cte %." color="red"><?=round((($_1_u_nf_total * 100) / $totalnfe), 2) ?>%</font>)<? } ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <?
                            }
                        }
                        ?>
                    </div>
                <?}?>
                <!------------------------  Recebimento   ------------------------>

                <!------------------------  Informações Logística   ------------------------>
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_logistica">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <table class="table table-striped planilha" style="border: 2px solid #ddd; width: 30%; margin-left: 30%;">
                                <tr>
                                    <td>Local de retirada:</td>
                                    <td><?=$endereco['endereco']?></td>
                                </tr>
                                <tr>
                                    <td>Contato do responsavel pela venda:</td>
                                    <td><?=$contatpessoa ?></td>
                                </tr>
                                <tr>
                                    <td>Transportador:</td>
                                    <td><?=traduzid("pessoa", "idpessoa", " IFNULL(nomecurto, nome)", $_1_u_nf_idtransportadora, false) ?></td>
                                </tr>
                                <tr>
                                    <td>Valor do frete:</td>
                                    <td><?=$frete ?></td>
                                </tr>
                            </table>
                        </div>
                        <?
                        if($_1_u_nf_tiponf == 'T' && !empty($_1_u_nf_cnpjtomador)){
                            ?>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Remetente<? if($_1_u_nf_cnpjtomador == $_1_u_nf_remcnpj){ ?> -<font color="red"> TOMADOR </font><? } ?></div>
                                            <div class="panel-body">
                                                <table class="table table-striped planilha">
                                                    <tr>
                                                        <th>Razão Social:</th>
                                                        <td><?=$_1_u_nf_remnome ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>CNPJ:</th>
                                                        <td><?=formatarCPF_CNPJ($_1_u_nf_remcnpj, true) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cidade:</th>
                                                        <td><?=$_1_u_nf_remmun ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <hr>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Origem Prestação:</th>
                                                        <td><?=$_1_u_nf_munini ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Destinatário <? if($_1_u_nf_cnpjtomador == $_1_u_nf_destcnpj){ ?> -<font color="red"> TOMADOR </font><? } ?></div>
                                            <div class="panel-body">
                                                <table class="table table-striped planilha">
                                                    <tr>
                                                        <th>Razão Social:</th>
                                                        <td><?=$_1_u_nf_destnome ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>CNPJ:</th>
                                                        <td><?=formatarCPF_CNPJ($_1_u_nf_destcnpj, true) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cidade:</th>
                                                        <td><?=$_1_u_nf_destmun ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <hr>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Destino Prestação:</th>
                                                        <td><?=$_1_u_nf_munfim ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                </div>
                <!------------------------  Informações Logística   ------------------------>

                <!------------------------  Impostos   ------------------------>
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_impostos">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <table class="table planilha" style="border: 2px solid #ddd; width: 30%; margin-left: 30%;">
                                <thead>
                                    <tr>
                                        <th class="col-xs-3 alinhar-centro">Impostos</th>
                                        <th class="alinhar-centro">Recolhido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="alinhar-centro">SISCOMEX</td>
                                        <td class="alinhar-direita"><?=number_format($_1_u_nf_siscomex, 4, ',', '.') ?></td>
                                    </tr>
                                    <tr class="bc-cinza">
                                        <td class="alinhar-centro">IPI</td>
                                        <td class="alinhar-direita"><?=number_format($totalvalipi, 4, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="alinhar-centro">PIS</td>
                                        <td class="alinhar-direita"><?=number_format($totalpis, 4, ',', '.') ?></td>
                                    </tr>
                                    <tr class="bc-cinza">
                                        <td class="alinhar-centro">COFINS</td>
                                        <td class="alinhar-direita"><?=number_format($totalcofins, 4, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="alinhar-centro">ICMS</td>
                                        <td class="alinhar-direita"><?=number_format($_1_u_nf_icms, 4, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="alinhar-centro">II</td>
                                        <td class="alinhar-direita"><?=number_format($totalimpostoImportacao, 4, ',', '.') ?></td>
                                    </tr>
                                    <tr style="border-top: 2px solid #ddd;">
                                        <td class="alinhar-centro"><b>Total Impostos</b></td>
                                        <td class="alinhar-direita"><b><?=number_format($totalimpostoImportacao + $_1_u_nf_siscomex + $totalvalipi + $totalpis + $totalcofins + $_1_u_nf_icms, 4, ',', '.') ?></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!------------------------  Impostos   ------------------------>
                
                <!------------------------  Rateio   ------------------------>
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_rateio">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <?
                            if(!empty($_1_u_nf_idnf) && $_1_u_nf_tipocontapagar == 'D' && $_1_u_nf_tiponf != 'O'){
                                if($_1_u_nf_tiponf != 'C'){
                                    $qtdrateio = count($listarRateio);
                                    if($qtdrateio > 0){
                                        $rateio = 'CONCLUIDO';
                                        $color = "green";
                                        foreach ($listarRateio as $_daddosRateio){
                                            if(empty($_daddosRateio['idrateioitemdest']) && !empty($_daddosRateio['idnfitem'])){
                                                $rateio = 'PENDENTE';
                                                $color = "red";
                                            }
                                        }
                                    }
                                } elseif($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'O'){

                                    if(NfEntradaController::buscarNfitemContaItem($_1_u_nf_idnf) > 0){
                                        $rateiopendente = "Y";
                                    }

                                    $virg = '';
                                    foreach ($listarContaItemRateio['dados'] as $cirateio){
                                        $listarNfItemSolcom = NfEntradaController::buscarNfItemSolcom($cirateio['idnfitem']);
                                        $qtdcom = 0;
                                        if($listarNfItemSolcom['qtdLinhas'] > 0) // tem solicitação de compra
                                        {
                                            foreach ($listarNfItemSolcom['dados'] as $nfItemSolcom){
                                                $qtdcom = $qtdcom + $nfItemSolcom['qtdcom'];
                                            }
                                        }

                                        if($qtdcom < $cirateio['qtd']){
                                            $consumodiasloterateio = $cirateio['tempoconsrateio'];
                                            $listarLoteMeio = NfEntradaController::buscarRateio($cirateio['idprodserv'], $cirateio['idunidadeest'], $consumodiasloterateio);

                                            if($listarLoteMeio['qtdLinhas'] < 1 || (!empty($cirateio['idrateioitemdest']))){
                                                $rateiopendente = "Y";
                                                $idprodservs .= $virg . $cirateio['idprodserv'];
                                                $virg = ',';
                                            }
                                        }
                                    }

                                    if($rateiopendente == "Y"){
                                        $rateio = 'CONCLUIDO';
                                        $color = "green";

                                        if(!empty($idprodservs) and  $listarContaItemRateio['qtdLinhas'] > 0){
                                            $listarRateioProdserv = NfEntradaController::buscarNfitemContaItemRateio($_1_u_nf_idnf, $idprodservs);
                                            foreach ($listarRateioProdserv['dados'] as $rateioProdserv){
                                                if(empty($rateioProdserv['idrateioitemdest'])){
                                                    $rateio = 'PENDENTE';
                                                    $color = "red";
                                                }
                                            }
                                        }

                                        $listarContaItemRateio = NfEntradaController::buscarNfContaItemRateio($_1_u_nf_idnf);
                                        foreach ($listarContaItemRateio['dados'] as $contaItemRateio){
                                            if(empty($contaItemRateio['idrateioitemdest'])){
                                                $rateio = 'PENDENTE';
                                                $color = "red";
                                            }
                                        }

                                    }
                                }

                                $listarRateioNf = RateioItemDestController::buscarRateioItemNfItem($_1_u_nf_idnf, $_idnfitem);
                                ?>
                                <div class="row" id="formulario">
                                    <div class="col-md-8">
                                        <div class="panel panel-default">
                                            <div class="panel-heading cabecalho" style="height: 32px;">
                                                ITENS DO RATEIO
                                                <div style="float: right;">
                                                <?if($funcao!="COBRAR"){?>
                                                    <button id="cbrestaurar" type="button" class="btn btn-primary btn-xs" onclick="restaurartodos()" title="Limpar">
                                                    <i class="fa fa-arrow-circle-left"></i>Limpar
                                                    </button>
                                                <?}?>				
                                                </div>
                                            </div>
                                            <div class="panel-body">
                                                <div><i>Selecione os iten(s) para edição do rateio.</i></div>
                                                <div>
                                                    <input placeholder="Filtrar Itens do Rateio" class="size20" style="height: 22px;" type="text" id="inputFiltro2"> 
                                                </div>
                                                <div class="table table-striped planilha panel panel-default" style="width:100%;font-size:9px;">
                                                    <div class="col-md-12 row rowcab panel-heading" style="margin:0px;font-size:9px;">
                                                        <div class="col-md-6">
                                                            <div class="col-md-1"><input type="checkbox" name="marcardesmarcar" checked class="pointer" title="Marcar/Desmarcar todos" onclick="selecionar(this,'inputcheckbox')"></div>
                                                            <div class="col-md-2">QTD</div>
                                                            <div class="col-md-2">UN</div>
                                                            <div class="col-md-7">ITEM</div>
                                                        </div>
                                                        <div class="col-md-1" style="text-align: right;">VLR. R$</div>
                                                        <div class="col-md-3" style="text-align: center;">DESTINO</div>
                                                        <div class="col-md-1" style="text-align: right;">RATEIO %</div>
                                                        <div class="col-md-1" style="text-align: center;">DETALHES</div>
                                                    </div>
                                                    <?
                                                    $i = 0;
                                                    $semrateio = 'N';
                                                    foreach($listarRateioNf as $rateioNf) 
                                                    {
                                                        $rateiopendente = 'Y';
                                                        if ($rateioNf['tipo'] == 'PRODUTO' && !empty($rateioNf['idunidadeest']) && !empty($rateioNf['idprodserv'])) {

                                                            $sqlsol = cnf::buscaSqlsolcom($rateioNf['idnfitem']);
                                                            $ressol = d::b()->query($sqlsol) or die("Falha ao buscar informações da solcom:  " . $sqlsol);
                                                            $qtdsolcom = mysqli_num_rows($ressol);
                                                            $qtdcom = 0;
                                                            if ($qtdsolcom > 0) { // tem solicitação de compra

                                                                while ($rowsol = mysqli_fetch_assoc($ressol)) {
                                                                    $qtdcom = $qtdcom + $rowsol['qtdcom'];
                                                                }
                                                            }
                                                            if ($rateioNf['qtd'] <= $qtdcom) {
                                                                $reteiopendente = 'N';
                                                            } else {
                                                                $consumodiasloterateio = $rateioNf['tempoconsrateio'];
                                                                if (empty($consumodiasloterateio)) {
                                                                    $consumodiasloterateio = 30;
                                                                }

                                                                $sqlmeio = cnf::buscarSqlRateio($rateioNf['idprodserv'], $rateioNf['idunidadeest'], $consumodiasloterateio);
                                                                $_reslotemeio = d::b()->query($sqlmeio) or die("Falha ao buscar rateio do produto: " . $sqlmeio);
                                                                $numrowmeio = mysqli_num_rows($_reslotemeio);

                                                                if ($numrowmeio > 0) {
                                                                    $rateiopendente = "N";
                                                                    $idprodservs .= $virg . $rateioNf['idprodserv'];
                                                                    $virg = ',';
                                                                } else {
                                                                    $rateiopendente = 'Y';
                                                                }
                                                            }
                                                        }

                                                        $i = $i + 1;
                                                        if (!empty($rateioNf['idrateioitemdest'])) {
                                                            $acao = 'u';
                                                        } else {
                                                            $acao = 'i';
                                                        }
                                                        $total = $total + $rateioNf['rateio'];

                                                        if ($rateioNf['tipoobjeto'] == 'unidade') {										
                                                            $unidade = RateioItemDestController::buscarPorChavePrimariaUnidade($rateioNf['idobjeto']);
                                                            $rateio = '<a target="_blank" href="?_modulo=unidade&_acao=u&idunidade='.$unidade["idunidade"].'&_idempresa='.$unidade["idempresa"].'">'.$unidade["unidade"].'</a>';
                                                            $rateiostr=$unidade["unidade"];
                                                        } else {
                                                            $semrateio='Y';
                                                            $rateio = "<font color='red'>Sem Rateio</font>";
                                                            $rateiostr="Sem Rateio";
                                                        }
                                                        ?>
                                                        <div class="col-md-12 row rowitem itemrateio" style="margin:0px;" style="width:100%;" data-text="<?=$rateioNf['descr']?> <?=$rateiostr?>">
                                                            <div class="col-md-6 inputcheckbox">
                                                                <div class="col-md-1"> 
                                                                    <?if(!empty($rateioNf['idrateioitemdestnf']) and $rateioNf['status'] =='COBRADO' ){?>
                                                                        <i class="fa fa-money verde pointer" title="Rateio em Cobrança" onclick="editardestnf(<?=$rateioNf['idrateioitemdest']?>,'<?=$rateioNf['descr']?>','<?=$rateiostr?>')" ></i>										
                                                                        <div class="hide" id="destnf<?=$rateioNf['idrateioitemdest']?>">
                                                                            <table class="table table-striped planilha">
                                                                                <tr>
                                                                                    <th>Cobrança %</th>
                                                                                    <th>Valor R$</th>
                                                                                    <th>Nome</th>
                                                                                    <th>Status</th>
                                                                                </tr>
                                                                                <?
                                                                                $cobranca = RateioItemDestController::listarRateioitemdestnfPorIdrateioitemdest($rateioNf['idrateioitemdest']);
                                                                                $totalrt = 0;
                                                                                $deletar='Y';
                                                                                foreach($cobranca as $linha) {
                                                                                    $totalrt=$totalrt+$linha['valor'];
                                                                                    $rotulo = getStatusFluxo('nf', 'idnf', $linha['idnf']);
                                                                                    if( $linha['status'] != 'INICIO' ){
                                                                                        $deletar='N';
                                                                                    }
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?=number_format(tratanumero($linha['rateio']), 2, ',', '.');?></td>
                                                                                        <td><?=number_format(tratanumero($linha['valor']), 2, ',', '.');?></td>
                                                                                        <td><?=$linha['nome']?></td>
                                                                                        <td>
                                                                                            <a target="_blank" href="?_modulo=nfentrada&_acao=u&idnf=<?=$linha['idnf']?>"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?></a>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <?
                                                                                }
                                                                                ?>
                                                                                <tr>
                                                                                    <th>Total</th>
                                                                                    <th><?=number_format(tratanumero($totalrt), 2, ',', '.'); ?></th>
                                                                                    <th></th>
                                                                                    <th style="text-align-last: center;" >
                                                                                    <?if($deletar=='Y'){?>
                                                                                        <i title="Excluir os Itens" class="fa fa-trash vermelho hoverpreto pointer" onclick="excluirdestnf('<?=$rateioNf['idrateioitemdestnf']?>')"></i>
                                                                                    <?}?>
                                                                                    </th>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                        <?
                                                                    }elseif($rateioNf['status'] == 'PENDENTE' || empty($rateioNf['status'])){?>
                                                                        <input type="checkbox" checked class="changeacao" indice="<?=$i?>" acao="<?=$acao ?>" atname="checked[<?=$i ?>]" value="<?=$rateioNf['idrateioitemdest'] ?>" style="border:0px">                                                                
                                                                        <? if ($acao == 'u') { ?>
                                                                            <input class="rateioitem" name="_r<?=$i?>_<?=$acao?>_rateioitemdest_idrateioitemdest" type="hidden" value="<?=$rateioNf['idrateioitemdest'] ?>">
                                                                            <input class="rateioitem" name="_r<?=$i?>_<?=$acao?>_rateioitemdest_idrateioitem" type="hidden" value="<?=$rateioNf['idrateioitem'] ?>">
                                                                            <?
                                                                        } else {
                                                                            ?>
                                                                            <input class="rateioitem<?=$i?>" indice="<?=$i?>" campoName="_rateioitemdest_idobjeto" type="hidden" value="<?=$rateioNf['idnfitem'] ?>">
                                                                            <input class="rateioitem<?=$i?>" indice="<?=$i?>" campoName="_rateioitemdest_idrateioitemdest" type="hidden" value="<?=$rateioNf['idrateioitemdest'] ?>">
                                                                            <input class="rateioitem<?=$i?>" indice="<?=$i?>" campoName="_rateioitemdest_idrateioitem" type="hidden" value="<?=$rateioNf['idrateioitem'] ?>">
                                                                            <?
                                                                        }
                                                                    }else{
                                                                        ?>                                                                        
                                                                        <span title="Editado Por: <?=$rateioNf['alteradopor']?> &#013; Em:  <?=dmahms($rateioNf['alteradoem'])?> &#013; Status: <?=$rateioNf['status']?>">
                                                                            <i class="btn fa fa-info-circle" style="font-size: 1.4em; padding: 0px !important;"></i>
                                                                        </span>	
                                                                    <?}?>
                                                                </div>
                                                                <div class="col-md-2"><?=$rateioNf['qtd'] ?></div>
                                                                <div class="col-md-2"><?=$rateioNf['un'] ?> </div>
                                                                <div class="col-md-7"><?=$rateioNf['descr'] ?></div>
                                                            </div>
                                                            <div class="col-md-1 " style="text-align: right;">

                                                                <? if (!empty($rateioNf['idnf'])) { ?>
                                                                    <a class="hoverazul pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$rateioNf['idnf'] ?>')" title="Compra">
                                                                        <?=number_format(tratanumero($rateioNf['rateio']), 2, ',', '.'); ?>
                                                                    </a>
                                                                <? } else { ?>
                                                                    <?=number_format(tratanumero($rateioNf['rateio']), 2, ',', '.'); ?>
                                                                <? } ?>

                                                            </div>
                                                            <div class="col-md-3" style="text-align: center;">
                                                                <?
                                                                if (!empty($rateioNf['nome'])) {
                                                                    echo ($rateioNf['nome'] . '<br>');
                                                                }
                                                            
                                                                echo $rateio;
                                                                ?>
                                                            </div>
                                                            <div class="col-md-1" style="text-align: right;">
                                                                <?=$rateioNf['valorateio'] ?>%
                                                            </div>
                                                            <div  class="col-md-1" style="text-align: center;">
                                                                <?if( !empty($rateioNf['idnfitem']) ) { ?>											
                                                                    <a title="Compra" class="fa fa-search fa-1x hoverazul pointer" onclick="showhistoricoitem(<?=$rateioNf['idnfitem'] ?>);"></a>
                                                                <? } ?>
                                                            </div>

                                                        </div>
                                                        <?
                                                        $vtipo = $vtipo + $rateioNf['rateio'];
                                                    }
                                                    ?>
                                                    <div class="col-md-12 row rowitem"  style="margin:0px; font-size:9px;background:#ddd;font-weight:bold;">
                                                        <div class="col-md-6">TOTAL:</div>
                                                        <div class="col-md-1" style="text-align: right;"><?=number_format(tratanumero($total), 2, ',', '.'); ?></div>
                                                        <div class="col-md-3" style="text-align: right;"></div>
                                                        <div class="col-md-1" style="text-align: right;"></div>
                                                        <div class="col-md-1" style="text-align: right;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <? if ($semrateio=='N' AND $funcao=="COBRAR") { ?>
                                            <div class="panel panel-default">
                                                <div class="panel-heading cabecalho" style="height: 32px;">
                                                    COBRANÇA
                                                    <div style="float: right;">
                                                        <button id="cbalterar" type="button" class="btn btn-success btn-xs hidden" onclick="alterartodosNf('Y', '<?=$acao?>')" title="Salvar">
                                                            <i class="fa fa-circle"></i>Salvar
                                                        </button>
                                                        <button id="cbalterar2" style="border-color:#5cb85c63 !important; background-color:#5cb85c63 !important;" type="button" class="btn btn-success btn-xs" onclick="alert('É necessário completar o valor de 100% para o rateio.')" title="Salvar">
                                                            <i class="fa fa-circle"></i>Salvar
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="col-md-12">
                                                        <div><i>Insira o(s) percentual(ais) para o(s) destino(s) desejado(s) abaixo, mínimo 100%.</i></div>
                                                        <br>
                                                        <table id="tbrateio" style="width:100%;">
                                                            <thead>
                                                                <tr class="rowcab">
                                                                                                
                                                                    <td style="width:30%">
                                                                        Total Divisão: <span id="totalvalor" style="color: red;"></span>
                                                                    </td>	
                                                                    <td style="width:70%; text-align:right">									    
                                                                        
                                                                    </td>								
                                                                </tr>
                                                                <tr class="rowcab">
                                                                    <td colspan="2">
                                                                        <input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltroempresa"> 
                                                                    </td>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                        <table class="table table-striped planilha" style="font-size:9px;text-transform:uppercase;">
                                                            <tbody>
                                                                <?
                                                                
                                                                $listarEmpresa = EmpresaController::listarEmpresasAtivasComcobranca($idempresa);
                                                                $li = 10000;
                                                                ?>
                                                                <tr class="rowcab unidade" style="background:#ddd;">
                                                                    <th colspan="3" style="height: 40px;">
                                                                        <i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> Empresa(S)
                                                                    </th>
                                                                </tr>
                                                                <?
                                                                foreach($listarEmpresa as  $empresa) 
                                                                {
                                                                    $li = $li + 1;
                                                                    ?>
                                                                    <tr class="empresa" style="width:100%;" data-text="<?=$empresa['empresa']?>">
                                                                        <td style="width:20%">                                                                            
                                                                            <? if(!empty($empresa['idpessoaform']) and $empresa['conf']>0){
                                                                                $fontc = "";		
                                                                                $title = "";
                                                                                ?>
                                                                                <input type="text" nomecampo="_<?=$li?>#valor" class="valorrateio alterar-rateio-<?=$li?>" tipo="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this); guardarDadosNovosRateio(<?=$li?>, this);">
                                                                            <?}else{ 
                                                                                $fontc = "red";	
                                                                                $title = "Configurar no cadastro desta empresa a pessoa empresa relacionada, fatura automática, Categoria e Subcategoria";
                                                                                ?>
                                                                                <input title="<?=$title?>" type="text" disabled='disabled' nomecampo="_<?=$li?>#valor" tipo="valorrateio" class="valorrateio alterar-rateio-<?=$li?>" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this); guardarDadosNovosRateio(<?=$li?>, this);">
                                                                            <?}?>
                                                                        </td>
                                                                        <td style="width:80%" title=<?=$title?>>
                                                                            <font color="<?=$fontc?>">									
                                                                            <?=$empresa['empresa'] ?>
                                                                            </font>
                                                                        
                                                                            <input type="hidden" nomecampo="_<?=$li?>#idempresa" class="idempresa alterar-rateio-<?=$li?>" value="<?=$empresa['idempresa']?>,'empresa'">
                                                                            <span style="background: rgb(102, 102, 102);font-size: 9px;color: #fff;padding: 0px 6px;border-radius: 3px; display:none">
                                                                                Empresa
                                                                            </span>
                                                                        </td>
                                                                        <td class="nowrap">
                                                                        <?if(!empty($empresa['idnf'])){?>
                                                                                <a class="hoverazul pointer" onclick="janelamodal('?_modulo=rateioitemdestnf&_acao=u&idnf=<?=$empresa['idnf']?>')" title="Cobrança aberta">
                                                                                    R$ <?=number_format($empresa['valor'], 2, '.', '');?>
                                                                                </a>
                                                                            <?}else{echo("R$ 0.00");}
                                                                            ?>
                                                                        </td>	
                                                                    </tr>
                                                                <?
                                                                }
                                                                ?>								
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        <? } else { ?>
                                            <div class="panel panel-default">
                                                <div class="panel-heading cabecalho" style="height: 32px;">
                                                    RATEIO INTERNO
                                                    <div style="float: right;">
                                                        <button id="cbalterar" type="button" class="btn btn-success btn-xs hidden" onclick="alterartodosNf('N', '<?=$acao?>')" title="Salvar">
                                                            <i class="fa fa-circle"></i>Salvar
                                                        </button>
                                                        <button id="cbalterar2" style="border-color:#5cb85c63 !important; background-color:#5cb85c63 !important;" type="button" class="btn btn-success btn-xs" onclick="alert('É necessário completar o valor de 100% para o rateio.')" title="Salvar">
                                                            <i class="fa fa-circle"></i>Salvar
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="col-md-12">
                                                        <div><i>Insira o percentual de rateio de acordo com a(s) unidade(s) totalizando 100%.</i></div>
                                                        <br>
                                                        <table id="tbrateio" style="width:100%;">
                                                            <thead>
                                                                <tr class="rowcab">
                                                                                                
                                                                    <td style="width:30%">
                                                                        Total Rateio: <span id="totalvalor" style="color: red;"></span>
                                                                    </td>	
                                                                    <td style="width:70%; text-align:right" >
                                                                        <button  title="Ratear por numero de funcionários" type="button" class="btn btn-primary btn-xs" onclick="calculafunc(this)" >
                                                                            <i title="Ratear por numero de funcionários" class="fa fa-money pointer" ></i> Ratear por nº de colaboradores
                                                                        </button>                                      
                                                                    </td>								
                                                                </tr>
                                                                <tr class="rowcab">
                                                                    <td colspan="2">
                                                                        <input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltro"> 
                                                                        <button  title="Visualizar por colaborador" type="button" class="btn btn-primary btn-xs" onclick="mostrarpessoa(this)" >
                                                                            <i title="Visualizar por colaborador" class="fa fa-users group"></i>  
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                        <table class="table table-striped planilha" style="font-size:9px;text-transform:uppercase;">
                                                            <tbody>
                                                                <?
                                                                $condicaoEmpresa = TRUE;
                                                                $listarPessoaUnidadePorIdunidade = RateioItemDestController::buscarPessoaPorIdUnidadeFuncionario($condicaoEmpresa);
                                                                $li = 10000;
                                                                ?>
                                                                <tr class="rowcab unidade" style="background:#ddd;">
                                                                    <th colspan="3" style="height: 40px;">
                                                                        <i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> UNIDADE(S)
                                                                    </th>
                                                                </tr>
                                                                <?
                                                                foreach($listarPessoaUnidadePorIdunidade as $pessoaUnidadePorIdunidade) 
                                                                {
                                                                    $li = $li + 1;
                                                                    $porc = ($pessoaUnidadePorIdunidade['funidade'] / $pessoaUnidadePorIdunidade['totalf']) * 100;
                                                                    ?>
                                                                    <tr class="unidade" style="width:100%;" data-text="<?=$pessoaUnidadePorIdunidade['nome']?>">
                                                                        <td style="width:20%">
                                                                            <input type="text" nomecampo="_<?=$li?>#valor" tipo="valorrateio" class="valorrateio alterar-rateio-<?=$li?>" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this); guardarDadosNovosRateio(<?=$li?>, this);">
                                                                            <input type="hidden" class="fracaofunc" quant='<?=$pessoaUnidadePorIdunidade['funidade'] ?>' value="<?=$porc ?>">
                                                                        </td>
                                                                        <td style="width:80%">
                                                                            <? if ($pessoaUnidadePorIdunidade['funidade'] == 0) { ?>
                                                                                <font style="color: red;" title="NENHUM FUNCIONÁRIO NA UNIDADE"><?=$pessoaUnidadePorIdunidade['nome'] ?></font>
                                                                            <? } else { ?>
                                                                                <?=$pessoaUnidadePorIdunidade['nome'] ?>
                                                                            <? } ?>
                                                                            <input type="hidden" nomecampo="_<?=$li?>#idunidade" tipo="idunidade" class="idunidade alterar-rateio-<?=$li?>" value="<?=$pessoaUnidadePorIdunidade['id'] ?>,<?=$pessoaUnidadePorIdunidade['tipo'] ?>">
                                                                            <span style="background: rgb(102, 102, 102); font-size: 9px;color: #fff; padding: 0px 6px; border-radius: 3px; display:none">
                                                                                Unidade
                                                                            </span>
                                                                        </td>                                                                        

                                                                    </tr>

                                                                <?
                                                                }
                                                                ?>
                                                                <tr class="rowcab pessoa hide" style="background:#ddd;">
                                                                    <th colspan="3" style="height: 40px;">
                                                                        <i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> FUNCIONÁRIO(S)
                                                                    </th>
                                                                </tr>
                                                                <?
                                                                $listarFuncionarioUnidade = RateioItemDestController::buscarvw8FuncionarioUnidadePorIdTipoPessoa();
                                                                foreach($listarFuncionarioUnidade as $funcionarioUnidade) 
                                                                {
                                                                    $li = $li + 1;
                                                                    ?>
                                                                    <tr class="pessoa hide" data-text="<?=$funcionarioUnidade['nomecurto']?>">
                                                                        <td><input type="text" nomecampo="_<?=$li?>#valor" tipo="valorrateio" class="valorrateio alterar-rateio-<?=$li?>" style="width:60px; font-size:9px; border: 1px solid #cccccc !important; " vnumero="" onkeyup="calcular(this); guardarDadosNovosRateio(<?=$li?>, this);"></td>
                                                                        <td>
                                                                            <?=$funcionarioUnidade['nomecurto'] ?> 
                                                                            <input type="hidden" nomecampo="_<?=$li?>#idunidade" tipo="idunidade" class="idunidade alterar-rateio-<?=$li?>" value="<?=$funcionarioUnidade['idunidade'] ?>,unidade">
                                                                            <input type="hidden" nomecampo="_<?=$li?>#idpessoa" tipo="idpessoa" class="idpessoa alterar-rateio-<?=$li?>" value="<?=$funcionarioUnidade['idpessoa'] ?>">
                                                                            <span style="background: rgb(102, 102, 102);font-size: 9px;color: #fff;padding: 0px 6px;border-radius: 3px;">
                                                                                <?=$funcionarioUnidade['unidade'] ?>
                                                                            </span>
                                                                        </td>
                                                                        <td></td>
                                                                    </tr>

                                                                <?
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        <? } ?>
                                    </div>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <!------------------------  Rateio   ------------------------>

                <!------------------------  Anexo   ------------------------>                  
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_anexo">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <?
                            if(!empty($_1_u_nf_idnf)){
                                ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading" data-toggle="collapse" href="#ArqForn">Anexo - Proposta do Fornecedor</div>
                                    <div class="panel-body" id="ArqForn">
                                        <div id="arqforndrop" class="dz-clickable pointer azul dropz" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
                                            <i class="fa fa-cloud-upload fonte18"></i>
                                        </div>
                                    </div>
                                </div>
                            <?
                            }
                            $tabaud = "nf"; //pegar a tabela do criado/alterado em antigo
                            require_once('../form/viewAnexo.php');
                            ?>
                        </div>
                    </div>
                </div>
                <!------------------------  Anexo   ------------------------>
                
                <!------------------------  Evento  ------------------------>
                <div role="tabpanel" class="tab-pane fade" id="nfentrada_evento">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <? require_once('../form/viewEvento.php'); ?> 
                        </div>                       
                    </div>
                </div>
                <!------------------------  Evento  ------------------------>
                
            </div>
        </div>
    </div>
    <?
} //if(!empty($_acao='u')){

if(!empty($_1_u_nf_idnf)){
    $listarAssinatura = NfEntradaController::buscarAssinaturaPessoa("'ATIVO','PENDENTE'", 'nfentrada', $_1_u_nf_idnf);
    $existe = $listarAssinatura['qtdLinhas'];
    if($existe > 0){
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Assinaturas</div>
                    <div class="panel-body">
                        <table class="planilha grade compacto">
                            <tr>
                                <th>Funcionários</th>
                                <th>Data Assinatura</th>
                                <th>Status</th>
                            </tr>
                            <?
                            foreach ($listarAssinatura['dados'] as $assinatura){
                                ?>
                                <tr class="res">
                                    <td nowrap><?=$assinatura["nome"]?></td>
                                    <td nowrap><?=$assinatura["dataassinatura"]?></td>
                                    <td nowrap><?=$assinatura["status"]?></td>
                                </tr>
                                <?
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?
    } //if($existe>0){ 
} //if(!empty($_1_u_atendimento_idatendimento)){
?>
<div id="novaparcela" style="display: none;">
    <div class="row">
        <div class="col-md-12">
            <table style="margin-left: 26%;margin-bottom: 10px;">
                <tr>
                    <td style="width: 100px !important; "><input type="radio" id="checkcredito" name="_modalnovaparcelacontapagar_tipo_" value="C" style="margin-right: 5px;"> Crédito </td>
                    <td style="width: 100px !important; "><input type="radio" id="checkdebito" name="_modalnovaparcelacontapagar_tipo_" value="D" checked="yes" style="margin-right: 5px;"> Débito </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td align="right">Fatura Automática:</td>
                    <td>
                        <select id="formapagnovaparc" name="formapagnovaparc">
                            <option></option>
                            <? fillselect(NfEntradaController::buscarFormaPagamentoPorStatusEIdEmpresa('ATIVO'), $_1_u_nf_idformapagamento); ?>
                        </select>
                    </td>
                    <td align="right">Valor:</td>
                    <td><input type="text" id="valornovaparc" name="valornovaparc"></td>
                </tr>
                <tr>
                    <td align="right">Recebimento:</td>
                    <td><input type="date" id="vencnovapart" name="vencnovapart" placeholder="Ex: 00/00/0000"></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<? ///historico alteracao
if($_1_u_nf_idnf){
    //duplicar nf
    $listarItensDuplicar = NfEntradaController::listarItensNfParaDuplicar($_1_u_nf_idnf);
    $nomeforn = traduzid('pessoa', 'idpessoa', 'nome', $_1_u_nf_idpessoa);
    ?>
    <div id="compra<?=$_1_u_nf_idnf; ?>" style="display: none">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table class="table table-striped planilha">
                            <tr id="editforn">
                                <th>Qtd Sol</th>
                                <th colspan="2">Produto</th>
                                <th>Entrega</th>
                                <th>Retirar
                                    <input id="_f_nome<?=$_1_u_nf_idnf ?>" type="hidden" value="<?=$nomeforn ?>" disabled cbvalue="<?=$_1_u_nf_idpessoa ?>" name="x_f_nome" style="background-color: #e6e6e6; width: 35em;">
                                </th>
                            </tr>
                            <?
                            $b = 0;
                            foreach ($listarItensDuplicar as $itensDuplicar){
                                $b = $b + 1;
                                ?>
                                <tr id="tr<?=$itensDuplicar['idnfitem']?>">
                                    <td>
                                        <input id="quantidade<?=$itensDuplicar['idnfitem']?>" name="_<?=$b ?>__quantidade" type="text" class="size5" value="<?=$itensDuplicar['quant']?>">
                                        <input id="idnfitem<?=$itensDuplicar['idnfitem']?>" name="_<?=$b ?>__idnfitem" type="hidden" value="<?=$itensDuplicar['idnfitem']?>">
                                    </td>
                                    <td colspan="2">
                                        <? if(!empty($itensDuplicar['prodservdescr'])){ ?>
                                            <?=$itensDuplicar['prodservdescr']?>
                                        <? } else { ?>
                                            <?=$itensDuplicar['descr']?>
                                        <? } ?>
                                    </td>
                                    <td>
                                        <input id="previsaoentrega<?=$itensDuplicar['idnfitem']?>" name="_<?=$b ?>__previsaoentrega" class="calendario" style="width: 100px;" value="">
                                    </td>
                                    <td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluiritemtemp(this)" alt="Excluir item!"></i></td>
                                </tr>
                            <? } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?
}

require_once('../form/viewCriadoAlteradoNew.php');
require_once('../form/js/nfentrada_js.php');
require_once('../form/js/rateioitemdest_js.php');
?>
<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/pedido_controller.php");


require_once("../model/prodserv.php");
require_once("../inc/php/permissao.php");
require_once(__DIR__ . "/controllers/fluxo_controller.php");

//Chama a Classe prodserv
$prodservclass = new PRODSERV();


if (!empty($_GET["idnfcp"]) and empty($_GET["idnf"])) {
    $_GET["idnf"] = $_GET["idnfcp"];
    $_GET["_acao"] = 'u';
    $idnfcp = 'Y';
}
if ($_POST) {
    include_once("../inc/php/cbpost.php");
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
$pagsql = "select * from nf where idnf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli = PedidoController::listarClietenPedidoPorIdTipoPessoa('1,2,7,12,116');

if($arrCli[$_1_u_nf_idpessoa] == NULL && !empty($_1_u_nf_idpessoa))
{
    $cliente = PedidoController::buscarClientePedidoPorIdPessoa($_1_u_nf_idpessoa);
    $arrCli[$_1_u_nf_idpessoa]['nome'] = $cliente['nome'];
    $arrCli[$_1_u_nf_idpessoa]['tipo'] = $cliente['tipo'];
}

//print_r($arrCli); die;
$jCli = $JSON->encode($arrCli);


?>
<style>
      .desabilitado {
        background-color: #ece5e5 !important;
    }
</style>

<div class="col-sm-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Número do Pedido: <?= $_1_u_nf_idnf ?></h3>
            <input name="_1_<?= $_acao ?>_nf_idnf" value="<?= $_1_u_nf_idnf ?>" type="hidden">
            <input name="statusant" type="hidden" style="width: 10px;" value="<?= $_1_u_nf_status ?>">
            <input name="_1_<?= $_acao ?>_nf_status" id="status" type="hidden" style="width: 10px;" value="<?= $_1_u_nf_status ?>">
        </div>
    </div>
</div>
<div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">Faturamento/Entrega</div>
        <div class="panel-body ">
            <div class="col-xs-12">
                <div class="col-xs-12">
                    <b>O.C. Cliente:</b>
                </div>
                <div class="col-xs-12">
                    <input <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_nitemped" id="pedidoext" type="text" value="<?= $_1_u_nf_nitemped ?>" vnulo style="display: <?= $strdados ?>">
                </div>

                <div class="col-xs-12">
                    <b>Cliente:</b>
                </div>
                <div class="">
                    <? if ($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO') { ?>
                        <div class="col-xs-10">
                            <input name="_1_<?= $_acao ?>_nf_idpessoafat" type="hidden" value="<?= $_1_u_nf_idpessoafat ?>">
                            <? echo traduzid("pessoa", "idpessoa", "nome", $_1_u_nf_idpessoafat); ?>
                        </div>
                        <div class="col-xs-2">
                            <a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idpessoafat ?>')"></a>
                        </div>
                    <? } else { ?>
                        <? if (empty($_1_u_nf_idpessoafat)) {
                            $dfat = "";
                        } else {
                            $dfat = "disabled='disabled'";
                        } ?>
                        <div class="col-xs-10">
                            <input class="desabilitado" <?= $dfat ?> type="text" name="_1_<?= $_acao ?>_nf_idpessoafat" vnulo cbvalue="<?= $_1_u_nf_idpessoafat ?>" value="<?= $arrCli[$_1_u_nf_idpessoafat]["nome"] ?>" onchange="atualizaclientefat(this,<?= $_1_u_nf_idnf ?>);" vnulo>
                        </div>
                        <div class="col-xs-2 d-flex justify-content-center align-items-center">
                        <? if (!empty($_1_u_nf_idpessoafat)) { ?>
                                <a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idpessoafat ?>')"></a>
                            <? } ?>
                            <a id="altcliente" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarclientefat(this);" title="Alterar cliente de faturamento."></a>
                        </div>
                    <? } ?>
                </div>
                <div class="col-xs-12">
                    <b>Endereço:</b>
                </div>
                <div class="col-xs-12">
                    <? if (!empty($_1_u_nf_idenderecofat)) { ?>
                        <select class="desabilitado" disabled="disabled" style="font-size:  11px;" name="_1_<?= $_acao ?>_nf_idenderecofat" id="idenderecofat" onchange="CB.post()" vnulo>
                            <? fillselect(PedidoController::buscarEnderecoFaturamentoPorPessoa($_1_u_nf_idpessoafat), $_1_u_nf_idenderecofat); ?>
                        </select>
                    <? } elseif (empty($_1_u_nf_idenderecofat) and !empty($_1_u_nf_idpessoafat)) { ?>
                        <select style="font-size:  11px;" name="_1_<?= $_acao ?>_nf_idenderecofat" id="idenderecofat" vnulo>
                            <? fillselect(PedidoController::buscarEnderecoFaturamentoPorPessoa($_1_u_nf_idpessoafat), $_1_u_nf_idenderecofat); ?>
                        </select>
                    <? } ?>
                </div>
                <div class="col-xs-12">
                    <b>Razão Social:</b>
                </div>
                <div class="col-xs-12">
                    <?= traduzid("pessoa", "idpessoa", "razaosocial", $_1_u_nf_idpessoafat) ?>
                </div>
                <div class="col-xs-12">
                    <b>CPF/CNPJ:</b>
                </div>
                <div class="col-xs-12">
                    <? if (!empty($_1_u_nf_idpessoafat)) {
                        $rowic = PedidoController::buscarPessoa($_1_u_nf_idpessoafat);
                        $cnpj = formatarCPF_CNPJ($rowic['cpfcnpj'], true);
                        if (!empty($cnpj)) { ?>
                            <span style="color: red;"><b><?= $cnpj ?></b></span>
                            <? if (!empty($rowic['inscrest'])) { ?> / IE:<span style="color: red;"><b><? } ?><? if (!empty($rowic['inscrest'])) { ?> <?= $rowic['inscrest'] ?><? } ?></b></span>
                        <? }
                    } ?>
                </div>
                <div class="col-xs-12">
                    <b>Endereço:</b>
                </div>
                <div class="col-xs-12">
                    <? if (!empty($_1_u_nf_idenderecofat)) {
                        $resf = PedidoController::listarEnderecoFaturamentoPorId($_1_u_nf_idenderecofat);
                        foreach ($resf as $rowf) {
                            $cep = formatarCEP($rowf["cep"], true); ?>
                            <li style="display:inline-block;">
                                <div class=""><?= $rowf["logradouro"] ?> <?= $rowf["endereco"] ?> N.: <?= $rowf["numero"] ?> <?= $rowf["complemento"] ?></div>
                                <div class="">Bairro: <?= $rowf["bairro"] ?> CEP: <?= $cep ?> </div>
                                <div class="">Cidade: <?= $rowf["cidade"] ?> UF: <?= $rowf["uf"] ?></div>
                            </li>
                        <? } ?>
                    <? } ?>
                </div>
            </div>
            <div class="col-xs-12">
                <div class="col-xs-12">
                    <b>Endereço Entrega:</b>
                </div>
                <div class="">
                    <div class="col-xs-11">
                        <? if (!empty($_1_u_nf_idpessoafat)) {
                            $stridpessoa = $_1_u_nf_idpessoa . "," . $_1_u_nf_idpessoafat;
                        } else {
                            $stridpessoa = $_1_u_nf_idpessoa;
                        }
                        if (!empty($_1_u_nf_idendrotulo)) { ?>
                            <select <?= $disabled ?> <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_idendrotulo" id="idendrotulo" vnulo>
                                <option value=""></option>
                                <? fillselect(
                                    PedidoController::listarEnderecoPessoaPorTipo($stridpessoa, '2,3'),
                                    $_1_u_nf_idendrotulo
                                ); ?>
                            </select>
                        <? } elseif (empty($_1_u_nf_idendrotulo) and !empty($_1_u_nf_idpessoa)) { ?>
                            <select <?= $disabled ?> <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_idendrotulo" id="idendrotulo" vnulo>
                                <option value=""></option>
                                <? fillselect(PedidoController::listarEnderecoPessoaPorTipo($stridpessoa, '2,3'), $_1_u_nf_idendrotulo); ?>
                            </select>
                        <? } ?>
                    </div>
                    <div class="col-xs-1">
                        <? if (!empty($_1_u_nf_idendrotulo)) { ?>
                            <a id="endereco" style="margin-top: 8px;" class="fa fa-bars pointer hoverazul" title="Endereço" onclick="janelamodal('?_modulo=endereco&_acao=u&idendereco=<?= $_1_u_nf_idendrotulo ?>')"></a>
                        <? } ?>
                    </div>
                </div>
                <? if (!empty($_1_u_nf_idendrotulo)) { ?>
                    <div class="col-xs-12"><b>Endereço:</b></div>
                    <div class="col-xs-12">
                        <? if (!empty($_1_u_nf_idendrotulo)) {
                            $resf = PedidoController::listarEnderecoFaturamentoPorId($_1_u_nf_idendrotulo);
                            foreach ($resf as $rowf) {
                                $localizacao = $rowf["localizacao"];
                                $cep = formatarCEP($rowf["cep"], true); ?>
                                <li style="display:inline-block;">
                                    <div class=""><?= $rowf["logradouro"] ?> <?= $rowf["endereco"] ?> N.: <?= $rowf["numero"] ?> <?= $rowf["complemento"] ?></div>
                                    <div class="">Bairro: <?= $rowf["bairro"] ?> CEP: <?= $cep ?> </div>
                                    <div class="">Cidade: <?= $rowf["cidade"] ?> UF: <?= $rowf["uf"] ?></div>
                                </li>
                            <? } ?>
                        <? } ?>
                    </div>
                    <div class="col-xs-12"><b>Rota:</b></div>
                    <div class="col-xs-12">
                        <? if (empty($localizacao)) { ?>
                            <i>Não informada</i>
                            <? } else {
                            if (filter_var($localizacao, FILTER_VALIDATE_URL)) { ?>
                                <a href="<?= $localizacao ?>" target="_blank">Como chegar</a>
                        <? } else {
                                echo  $localizacao;
                            }
                        } ?>
                    </div>
                    <div class="col-xs-12"><b>Quantidade:</b></div>
                    <div class="col-xs-12">
                        <input name="_1_<?= $_acao ?>_nf_qvol" size="20" type="number" value="<?= $_1_u_nf_qvol ?>" vdecimal>
                    </div>
                <? } ?>
                <? if (!empty($_1_u_nf_idpessoa)) { ?>

                    <? $rowo = PedidoController::buscarPreferenciaCliente($_1_u_nf_idpessoa);
                    $observacaonfp = $rowo["observacaonfp"]; //traduzid("pessoa","idpessoa","observacaonfp",$_1_u_nf_idpessoa);
                    if (!empty($observacaonfp)) { ?>
                        <div class="col-xs-12">
                            <b>Observação:</b>
                        </div>
                        <div class="col-xs-12">
                            <span style="color: red;"><b><?= str_replace(chr(13), "<br>", $observacaonfp) ?></b></span>
                        </div>
                <?
                    }
                } ?>
            </div>
        </div>
    </div>
</div>

<div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">Comprovante de Entrega</div>
        <div class="panel-body" style="padding-top: 8px !important;">
            <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;">
                <i class="fa fa-cloud-upload fonte18"></i>
            </div>
        </div>
    </div>
</div>

<div class="col-xs-12" style="padding-top: 20px;padding-bottom: 35px;">
    <?
    $cArq = PedidoController::listarArquivosAnexosPorIdNf($_1_u_nf_idnf);
  
    ?>
    <button carq="<?=$cArq?>" onclick="concluirpedido(this)" class="col-xs-12 btn btn-success btn-lg">Concluir</button>
</div>

    <script>
            var jCli = <?=$jCli?> // autocomplete cliente

            jCli = jQuery.map(jCli, function(o, id) {
                return {
                    "label": o.nome,
                    value: id + "",
                    "tipo": o.tipo
                }
            });




        if ($("[name=_1_u_nf_idnf]").val()) {
            $(".cbupload").dropzone({
				url: "form/_arquivo.php"
				, idObjeto: $("[name=_1_u_nf_idnf]").val()
				, tipoObjeto: 'nfComprovante'
				,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
            });   
        }

        $("[name*=_nf_idpessoafat]").autocomplete({
        source: jCli,
        delay: 0,
        select: function(event, ui) {
            preencheenderecofat(ui.item.value);
        },
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });

    function preencheenderecofat() {
        vIdPessoa = $(":input[name=_1_" + CB.acao + "_nf_idpessoafat]").cbval();

        if (vIdPessoa) {
            $("#idenderecofat").html("<option value=''>Procurando....</option>");
           
            $.ajax({
                type: "get",
                url: "ajax/buscaendereco.php?idpessoa=" + vIdPessoa,
                success: function(data) {
                    $("#idenderecofat").html(data);
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            }) //$.ajax

        } else {
            console.warn("js: preencheendereco: Erro: idIdpessoa não informado;")
        }
    } //function preencheendereco(){

    function editarclientefat() {

        $("[name=_1_u_nf_idpessoafat]").removeClass("desabilitado");
        $("[name=_1_u_nf_idenderecofat]").removeClass("desabilitado");
        $("[name=_1_u_nf_idpessoafat]").removeAttr("disabled");
        $("[name=_1_u_nf_idenderecofat]").removeAttr("disabled");
    }

    function atualizaclientefat(vthis, inidnf) {
      
        CB.post({
            objetos: "_alt_u_nf_idnf=" + inidnf + "&_alt_u_nf_idpessoafat=" + $(vthis).attr("cbvalue"),
            parcial: true
        })
  
    }

    function concluirpedido() {debugger

        if($("[name=_1_u_nf_status]").val() == "CONCLUIDO"){
            alert("Pedido já concluído.")
            return false;
        }

        if($(".cbupload").find(".fa-trash").length == 0 ){
            alert("É necessário anexar um arquivo para concluir o pedido.")
            $(".fa-cloud-upload").click();
            return false;
        }
        
        if (confirm("Deseja realmente concluir o pedido?")) {

            // $("[name=_1_u_nf_status]").remove();
            $("[name=_1_u_nf_status]").val("CONCLUIDO");

            CB.post();
        }
    }


    </script>
<?
// require_once('../form/js/pedido_js.php');
?>
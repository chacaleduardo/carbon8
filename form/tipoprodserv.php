<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/tipoprodserv_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "tipoprodserv";
$pagvalcampos = array(
    "idtipoprodserv" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "select * from tipoprodserv where idtipoprodserv = '#pkid'";

/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");

function getContaItem()
{
    global $JSON, $_1_u_tipoprodserv_idtipoprodserv;

    $and = "AND NOT EXISTS(SELECT 1 FROM contaitemtipoprodserv ct WHERE ct.idcontaitem = c.idcontaitem AND ct.idtipoprodserv = " . $_1_u_tipoprodserv_idtipoprodserv . ")";
    $sq = getContaItemSelect($and);

    $rq = d::b()->query($sq) or die("Erro ao consultar Tipoprodserv");

    if (mysqli_num_rows($rq) > 0) {
        $arr = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rq)) {
            $arr[$i]["idcontaitem"] = $r["idcontaitem"];
            $arr[$i]["contaitem"] = $r["contaitem"];
            $i++;
        }
        $arr = $JSON->encode($arr);
    } else {
        $arr = 0;
    }

    return $arr;
}

?>
<!-- Ciar Paramêtros para vincular Produtos e Serviços. Segunda Div lista os produtos. Criado em 09/01/2020 Lidiane -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>
                        <td>
                            <input name="_1_<?= $_acao ?>_tipoprodserv_idtipoprodserv" type="hidden" value="<?= $_1_u_tipoprodserv_idtipoprodserv ?>" readonly='readonly'>
                        </td>
                        <td> Subcategoria</td>
                        <td>
                            <input colspan="1"
                                name="_1_<?= $_acao ?>_tipoprodserv_tipoprodserv"
                                type="text"
                                value="<?= $_1_u_tipoprodserv_tipoprodserv ?>"
                                class="size50"
                                <? echo ($_acao == 'u' ? 'disabled' : ''); ?>>
                        </td>
                        <td><i class="fa fa-pencil branco pointer hoverpreto" onclick="alteravalor('tipoprodserv','<?= $_1_u_tipoprodserv_tipoprodserv ?>','modulohistorico',<?= $_1_u_tipoprodserv_idtipoprodserv ?>,'Subcategoria:', '50', 'false')"></i></td>
                        <?
                        $ListarHistoricoModal = TipoProdServController::buscarHistoricoModuloAlteracao($_1_u_tipoprodserv_idtipoprodserv, 'tipoprodserv', 'tipoprodserv');
                        $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                        if ($qtdvh > 0) {
                        ?>
                            <td>
                                <i title="Histórico de alteração" class="fa btn-lg fa-info-circle branco pointer hoverpreto" onclick="modalhist('historico_subcategoria');"></i>
                            </td>

                            <td id="historico_subcategoria" style="display: none;">
                                <table class="table table-hover">
                                    <?
                                    if ($qtdvh > 0) {
                                    ?>
                                        <thead>
                                            <tr>
                                                <th scope="col">De</th>
                                                <th scope="col">Para</th>
                                                <th scope="col">Por</th>
                                                <th scope="col">Em</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?
                                            foreach ($ListarHistoricoModal as $historicoModal) {
                                            ?>
                                                <tr>
                                                    <td><?= $historicoModal['valor_old'] ?></td>
                                                    <td><?= $historicoModal['valor'] ?></td>
                                                    <td><?= $historicoModal['nomecurto'] ?></td>
                                                    <td><?= dmahms($historicoModal['criadoem']) ?></td>
                                                </tr>
                                            <?
                                            }
                                            ?>
                                        </tbody>
                                    <?
                                    }
                                    ?>
                                </table>
                            </td>
                        <?
                        }
                        ?>

                        <td>Conta</td>
                        <td>
                            <input vnulo name="_1_<?= $_acao ?>_tipoprodserv_conta" class="cnt" id="cnt" type="number" value="<?= $_1_u_tipoprodserv_conta ?>" style="width: 62px;">
                        </td>
                        <td>
                            <?
                            if ($_1_u_tipoprodserv_compra == "Y") {
                                $valorCompras = 'N';
                                $checked = 'fa-check-square-o';
                            } else {
                                $valorCompras = 'Y';
                                $checked = 'fa-square-o';
                            }
                            ?>
                            <i style="padding-right: 0px;" class="fa <?= $checked ?> fa-1x btn-lg pointer" onclick="alterarCheckbox('compra', '<?= $valorCompras ?>');" alt="Alterar para Sim"></i>
                        </td>
                        <td> Compras</td>
                        <td>
                            <?
                            if ($_1_u_tipoprodserv_app == "Y") {
                                $valorAPP = 'N';
                                $checkedAPP = 'fa-check-square-o';
                            } else {
                                $valorAPP = 'Y';
                                $checkedAPP = 'fa-square-o';
                            }
                            ?>
                        <td>
                            <i style="padding-right: 0px;" class="fa <?= $checkedAPP ?> fa-1x btn-lg pointer" onclick="alterarCheckbox('app', '<?= $valorAPP ?>');" alt="Alterar para Sim"></i>
                        </td>
                        <td>APP</td>
                        <td style="width: 20%;">
                            <?
                            if ($_1_u_tipoprodserv_app == "Y") {
                            ?>
                                <input class="form-control cinza" vnulo id="idpessoa" type="text" name="_1_<?= $_acao ?>_tipoprodserv_idpessoa" vnulo cbvalue="<?= $_1_u_tipoprodserv_idpessoa ?>" value="<?= traduzid('pessoa', 'idpessoa', 'nome', $_1_u_tipoprodserv_idpessoa) ?>" vnulo>
                            <?
                            }
                            ?>
                        </td>
                        <td style="width: 80%;"></td>
                        <td>Status</td>
                        <td>
                            <select name="_1_<?= $_acao ?>_tipoprodserv_status">
                                <? fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_tipoprodserv_status); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?
if (!empty($_1_u_tipoprodserv_idtipoprodserv)) {
?>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Categoria(s) Relacionado(s)</div>
                <div class="panel-body">
                    <?
                    $sqlh = "select e.idcontaitemtipoprodserv,t.contaitem,e.idcontaitem
                            from contaitemtipoprodserv e left join contaitem t on(t.idcontaitem=e.idcontaitem)
                            where e.idtipoprodserv=" . $_1_u_tipoprodserv_idtipoprodserv . " order by t.contaitem ";

                    $resh = d::b()->query($sqlh) or die("Erro ao buscar grupo es : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlh);
                    $qtdh = mysqli_num_rows($resh);
                    if ($qtdh < 1) {
                    ?>
                        <input id="contaitem" class="compacto" type="text" cbvalue placeholder="Selecione">
                    <?
                    }
                    ?>

                    <table class="table table-striped planilha">
                        <? if ($qtdh > 0) { ?>
                            <? $i = 9977;
                            while ($rowh = mysqli_fetch_assoc($resh)) {
                                $idcontaitematual = $rowh['idcontaitem'];
                                $i = $i + 1; ?>
                                <tr>
                                    <td>
                                        <input name="_<?= $i ?>_u_contaitemtipoprodserv_idcontaitemtipoprodserv" type="hidden" value="<?= $rowh['idcontaitemtipoprodserv'] ?>">
                                        <a title="Editar Teste" target="_blank" href="?_modulo=contaitem&_acao=u&idcontaitem=<?= $rowh['idcontaitem'] ?>"><?= $rowh['contaitem'] ?></a>
                                    </td>
                                    <td style="text-align: right;">
                                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contaitemtipoprodserv',<?= $rowh['idcontaitemtipoprodserv'] ?>)" alt="Excluir !"></i>
                                    </td>
                                </tr>
                        <? }
                        } ?>
                    </table>
                </div>
            </div>
        </div>
        <? if ($idcontaitematual) {
            $sql = "SELECT gp.idlpgrupo as idlpgrupo,
											gp.descricao as descgrupo,
											g.idlpgrupo as idgrupo,
											g.descricao,
											e.empresa,
											e.sigla,
											l.idlp,
											l.idempresa as idempresa,
											ov.*
									FROM carbonnovo._lp l
										JOIN empresa e ON (l.idempresa = e.idempresa)
										JOIN carbonnovo._lpobjeto o ON o.idlp = l.idlp AND o.tipoobjeto = 'lpgrupo'
										JOIN carbonnovo._lpgrupo g ON g.idlpgrupo = o.idobjeto
										JOIN carbonnovo._lpgrupo gp ON gp.idlpgrupo = g.lpgrupopar
										JOIN objetovinculo ov ON (ov.idobjeto = l.idlp AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'contaitem')
										JOIN contaitem ci on (ov.idobjetovinc = ci.idcontaitem)
									WHERE ci.idcontaitem = " . $idcontaitematual . "
											AND  l.status = 'ATIVO' 
											AND e.status = 'ATIVO' 
											AND g.status = 'ATIVO'
											AND gp.status = 'ATIVO'
									ORDER BY e.idempresa, gp.descricao, g.descricao ";
            $empresa = "";
            $res = d::b()->query($sql) or die("Erro ao buscar tipo de item : " . mysqli_error(d::b()) . "<p>SQL:" . $sql);
            if (mysqli_num_rows($res) > 0) { ?>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <!-- Cabeçalho como gatilho do collapse -->
                        <div class="panel-heading" data-toggle="collapse" href="#lpInfo">
                            LPs
                        </div>
                        <!-- Conteúdo colapsável -->
                        <div id="lpInfo" class="panel-collapse collapse">
                            <div class="panel-body">
                                <table class="table table-striped planilha">
                                    <?php
                                    while ($rw = mysqli_fetch_assoc($res)) {
                                        if ($empresa != $rw['empresa']) {
                                            $empresa = $rw['empresa']; ?>
                                            <tr style="background-color: #cccccc;">
                                                <td colspan="3" style="font-weight: bold; text-align:center;">
                                                    <?= $rw['empresa'] ?>
                                                </td>
                                            </tr>
                                        <? } ?>
                                        <tr>
                                            <td class="hoverazul">
                                                <a target="_blank" href="?_modulo=_lp&_acao=u&idlp=<?= $rw['idlp'] ?>"><?= $rw['descricao'] ?></a>
                                            </td>
                                        </tr>
                                    <? } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <? } ?>
        <? } ?>
    </div>
    <style>
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
        }

        input[type=number] {
            -moz-appearance: textfield;
            appearance: textfield;

        }
    </style>
    <?
    $sql = "SELECT PS.idempresa,PS.idprodserv, PS.tipo, PS.codprodserv, PS.descr, PS.descrcurta, PS.status, TPS.tipoprodserv,e.sigla
          FROM tipoprodserv TPS INNER JOIN prodserv PS ON (TPS.idtipoprodserv = PS.idtipoprodserv and PS.status='ATIVO')
          join empresa e on(e.idempresa=PS.idempresa)
         WHERE PS.idtipoprodserv = " . $_1_u_tipoprodserv_idtipoprodserv . " ORDER BY PS.descr";
    $res = d::b()->query($sql) or die("A Consulta falhou :" . mysqli_error() . "<br>Sql:" . $sql);
    $rownum1 = mysqli_num_rows($res);
    if ($rownum1 > 0) {
    ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <!-- Cabeçalho do painel como gatilho do collapse -->
                    <div class="panel-heading table-striped" data-toggle="collapse" href="#contatoInfo">
                        (<?= $rownum1 ?>)- Produtos / Serviços Vinculados <?= $arrpessoa['razaosocial'] ?>
                    </div>
                    <!-- Conteúdo colapsável -->
                    <div id="contatoInfo" class="panel-collapse collapse">
                        <div class="panel-body">
                            <table class="table planilha" style="width: 100%;">
                                <tr>
                                    <!-- <th>Empresa</th> -->
                                    <th>Tipo</th>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <!-- <th>Subcategoria</th>
                                    <th>Status</th>
                                    <th>Editar</th> -->
                                </tr>
                                <?php while ($row = mysqli_fetch_assoc($res)) { ?>
                                    <tr class="res">
                                        <!-- <td nowrap><?= $row["sigla"] ?></td> -->
                                        <td nowrap><?= $row["tipo"] ?></td>
                                        <td nowrap><?= $row["codprodserv"] ?></td>
                                        <td nowrap style="width: 400px"><a target="_blank" href="?_modulo=prodserv&_acao=u&idprodserv=<?= $row['idprodserv'] ?>"><?= $row["descr"] ?></a></td>
                                        <!-- <td nowrap><?= $row["tipoprodserv"] ?></td>
                                        <td nowrap><?= $row["status"] ?></td>
                                        <td>
                                            <i class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer"
                                                onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $row['idprodserv'] ?>&_idempresa=<?= $row['idempresa'] ?>');">
                                            </i>
                                        </td> -->
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<? }
}
$tabaud = "tipoprodserv";
require 'viewCriadoAlterado.php';
?>
<?
function getContaItemTipo()
{
    global $JSON, $_1_u_tipoprodserv_idtipoprodserv, $_1_u_tipoprodserv_idempresa;
    $sq = "SELECT * FROM (SELECT c.idcontaitem, c.contaitem
                                      FROM contaitem c 
                                     WHERE c.status='ATIVO' 
                                     and c.idempresa= " . $_1_u_tipoprodserv_idempresa . "
                                     and not exists (select 1 from contaitemtipoprodserv ct where ct.idcontaitem=c.idcontaitem and ct.idtipoprodserv= " . $_1_u_tipoprodserv_idtipoprodserv . ")) AS c
                  ORDER BY contaitem";

    $rq = d::b()->query($sq) or die("Erro ao consultar Tipoprodserv. " . $sq);

    if (mysqli_num_rows($rq) > 0) {
        $arr = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rq)) {
            $arr[$r["idcontaitem"]]['contaitem'] = $r["contaitem"];
        }
        $arr = $JSON->encode($arr);
    } else {
        $arr = 0;
    }

    return $arr;
}

function getPessoa()
{
    global $JSON;

    $sql = "SELECT idpessoa, nome FROM pessoa WHERE idtipopessoa = 5 AND status = 'ATIVO'";
    $rq = d::b()->query($sql) or die("Erro ao consultar Pessoa");

    if (mysqli_num_rows($rq) > 0) {
        $arr = array();
        $i = 0;
        while ($r = mysqli_fetch_assoc($rq)) {
            $arr[$r["idpessoa"]]['nome'] = $r["nome"];
            $i++;
        }
        $arr = $JSON->encode($arr);
    } else {
        $arr = 0;
    }

    return $arr;
}
?>
<script>
    <? if (!empty($_1_u_tipoprodserv_idtipoprodserv)) { ?>
        var jContaItem = <?= getContaItemTipo() ?> || 0;
        var jPessoa = <?= getPessoa() ?> || 0;

        if (jContaItem != 0) {
            jContaItem = jQuery.map(jContaItem, function(o, id) {
                return {
                    "label": o.contaitem,
                    value: id + ""
                }
            });

            $("#contaitem").autocomplete({
                source: jContaItem,
                delay: 0,
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        return $('<li>').append('<a>' + item.label + '</a>').appendTo(ul);
                    };
                },
                select: function(event, ui) {
                    CB.post({
                        objetos: {
                            "_x_i_contaitemtipoprodserv_idtipoprodserv": $("[name=_1_u_tipoprodserv_idtipoprodserv]").val(),
                            "_x_i_contaitemtipoprodserv_idcontaitem": ui.item.value,
                        },
                        parcial: true
                    });
                }
            });
        }

        if (jPessoa != 0) {
            jPessoa = jQuery.map(jPessoa, function(o, id) {
                return {
                    "label": o.nome,
                    value: id + ""
                }
            });

            $("#idpessoa").autocomplete({
                source: jPessoa,
                delay: 0,
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        return $('<li>').append('<a>' + item.label + '</a>').appendTo(ul);
                    };
                },
                select: function(event, ui) {
                    CB.post({
                        objetos: {
                            "_x_u_tipoprodserv_idtipoprodserv": $("[name=_1_u_tipoprodserv_idtipoprodserv]").val(),
                            "_x_u_tipoprodserv_idpessoa": ui.item.value,
                        },
                        parcial: true
                    });
                }
            });
        }

    <? } ?>


    function altcheck(vtab, vcampo, vid, vcheck) {
        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck
        });
    }

    function novo(inobj) {
        CB.post({
            objetos: "_x_i_" + inobj + "_idtipoprodserv=" + $("[name=_1_u_tipoprodserv_idtipoprodserv]").val()
        });
    }

    function excluir(tab, inid) {
        if (confirm("Deseja retirar está categoria?")) {
            CB.post({
                objetos: "_x_d_" + tab + "_id" + tab + "=" + inid
            });
        }
    }

    function alterarCheckbox(campo, inval) {
        CB.post({
            objetos: `_x_u_tipoprodserv_idtipoprodserv=${$("[name=_1_u_tipoprodserv_idtipoprodserv]").val()}&_x_u_tipoprodserv_${campo}=${inval}`,
            parcial: true
        });
    }

    $("#modalplanejamento").click(function() {
        var idtipoprodserv = $("[name=_1_u_tipoprodserv_idtipoprodserv]").val();
        var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

        CB.modal({
            url: "?_modulo=planejamentotipoprodserv&_acao=u&idtipoprodserv=" + idtipoprodserv + "" + idempresa,
            header: "Previsão de Gastos"
        });
    });

    function novoPlanejamento(inidunidade) {
        $('#mais_' + inidunidade).addClass('hide');
        $('#select_' + inidunidade).removeClass('hide');
    }
    /* 
        function inserirPlanejamento(vthis, idtipoprodserv, idempresa) {

            var exercicio = $(vthis).val();
            form = '';

            let str = '';

            for (let i = 1; i < 13; i++) {

                str += '&_' + i + '_i_tipoprodservempresa_idtipoprodserv=' + idtipoprodserv + '&_' + i + '_i_tipoprodservempresa_idempresaprev=' + idempresa + '&_' + i + '_i_tipoprodservempresa_exercicio=' + exercicio + '&_' + i + '_i_tipoprodservempresa_mes=' + i;
            }

            CB.post({
                objetos: str,
                parcial: true,
            });

        } */

    $('#cbModal').on('change', '.input-valor-previsao', function() {
        $(this).next().val($(this).val());
    })

    $('#cbModal').on('change', '.select-justificativa', function() {
        const selectJQ = $(this);
        $('.select-justificativa').each((indice, elemento) => {
            $(elemento).val(selectJQ.val())
        })
    })

    function alteravalor(campo, valor, tabela, inid, texto, size = '10', jutifica = 'true') {
        htmlTrModelo = "";
        htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>
                    <td>${texto}</td>
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="tipoprodserv" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                        <input name="_h1_i_${tabela}_valor" value="${valor}" class="size${size}" type="text">
                    </td>
                </tr>`;
        if (jutifica == 'true') {
            htmlTrModelo += `<tr>
                    <td>Justificativa:</td>
                    <td>
                        <select id="justificativa" name="_h1_i_${tabela}_justificativa" onchange="alteraoutros(this,'${tabela}')" vnulo class="size50">
                            <?= fillselect(TipoProdServController::$_justificativa) ?>
                        </select>
                    </td>
                </tr>`;
        }

        htmlTrModelo += `
            </table>
        </div>`;

        if (campo == 'previsaoentrega') {
            var objfrm = $(htmlTrModelo);
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        } else {
            var objfrm = $(htmlTrModelo);
            objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        }

        strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

        CB.modal({
            titulo: strCabecalho,
            corpo: "<table>" + objfrm.html() + "</table>",
            classe: 'sessenta',
            aoAbrir: function(vthis) {
                $(`[name="_h1_i_${tabela}_valor"]`).val(valor);
            }
        });
    }

    function alteraoutros(vthis, tabela) {
        valor = $(vthis).val();
        if (valor == 'OUTROS') {

            $(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_' + tabela + '_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
            $('#justificativa').remove();
        } else {
            $('#justificaticaText').remove();
        }
    }

    $(".historicoEnvio").webuiPopover({
        trigger: "click",
        placement: "right",
        width: 500,
        delay: {
            show: 300,
            hide: 0
        }
    });

    function modalhist(div) {
        debugger;

        var html = $(`#${div}`).html();

        CB.modal({
            titulo: "</strong>Histórico de Alteração:</strong>",
            corpo: html,
            classe: 'sessenta'
        });
    }

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape1
</script>
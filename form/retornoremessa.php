<?
require_once("../inc/php/validaacesso.php");
if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

$pagvaltabela = "retornoremessa";
$pagvalcampos = array(
    "idretornoremessa" => "pk"
);
$pagsql = "SELECT * FROM retornoremessa where idretornoremessa = '#pkid'";

$sqlr = "SELECT * FROM retornoremessa where idretornoremessa =" . $_1_u_retornoremessa_idretornoremessa;
$resr = d::b()->query($sqlr);
$rowr = mysqli_fetch_assoc($resr);

include_once("../inc/php/controlevariaveisgetpost.php");
$sqlroc = "select a.retornorm from agencia a join retornoremessa r on(r.idagencia = a.idagencia) where r.idretornoremessa =" . $_1_u_retornoremessa_idretornoremessa;
$resroc = d::b()->query($sqlroc);
$rowroc = mysqli_fetch_assoc($resroc);

$sqldia = "SELECT datapagamento FROM retornoremessaitem where idretornoremessa =" . $_1_u_retornoremessa_idretornoremessa;
$res1 = d::b()->query($sqldia);
$row1 = mysqli_fetch_assoc($res1);
$data = date($row1['datapagamento']);
$diasemana_numero = date('w', strtotime($data));
if ($diasemana_numero == 5) {
    $dias = 3;
} else {
    $dias = 1;
}
?>
<style>
    i.fa.fa-trash.hoververmelho {
        display: none;
    }

    .panel-default>.panel-heading {
        font-weight: normal !important;
        margin-top: 0px;
    }
</style>
<script>
    <? if ($_1_u_retornoremessa_status == "CONCLUIDO") { ?>
        $("#cbModuloForm").find('input').not('[name*="retornoremessa_idretornoremessa"]').prop("disabled", true);
        $("#cbModuloForm").find("select").prop("disabled", true);
        $("#cbModuloForm").find("textarea").prop("disabled", true);
        $("#cbModuloForm").find("button").not('[id*="tarifar"]').prop("disabled", true);
        $("#transferir").prop("disabled", false);

    <? } ?>
</script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default" style="border-bottom-width: 0px;padding-bottom: 1px;">
            <div class="panel-heading" style=" padding: 3px 1px 1px 0px;">
                <table>
                    <tr>
                        <td>
                            <? if ($_acao == 'u') { ?>
                                <input name="_1_<?= $_acao ?>_retornoremessa_idretornoremessa" id="idretornoremessat" type="hidden" style="width: 0%;" value="<?= $_1_u_retornoremessa_idretornoremessa ?>" readonly='readonly'>
                                <input name="_1_<?= $_acao ?>_retornoremessa_status" id="status" type="hidden" style="width: 0%;" value="<?= $_1_u_retornoremessa_status ?>">
                                <b>⠀ID: </b>
                                <label class="alert-warning"><?= $_1_u_retornoremessa_idretornoremessa ?></label>
                            <? } ?>
                        </td>
                        <td>
                            Data:
                        </td>
                        <td>
                            <input name="_1_<?= $_acao ?>_retornoremessa_dataremessa" class="calendario" size="10" autocomplete="off" value="<?= dma($_1_u_retornoremessa_dataremessa) ?>" vnulo>
                        </td>

                        <td>
                            Agencia:
                        </td>
                        <td>
                            <select name="_1_<?= $_acao ?>_retornoremessa_idagencia" vnulo>
                                <option value=""></option>
                                <? fillselect("SELECT idagencia,agencia,retornorm from agencia where idempresa=" . cb::idempresa() . "  and status = 'ATIVO';", $_1_u_retornoremessa_idagencia); ?>
                            </select>
                        </td>
                        <td>
                            Status:
                        </td>
                        <td>
                            <select name="_1_<?= $_acao ?>_retornoremessa_status" vnulo>
                                <option value=""></option>
                                <? fillselect("SELECT 'CONCLUIDO','Concluido' union SELECT 'PENDENTE','Pendente'", $_1_u_retornoremessa_status); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="panel-body" style="margin-bottom: 0px; padding-top: 1px !important">
                <? if ($_acao == 'u') {
                    //GSFE - monta os o retono na tela
                    $sql = "SELECT rti.idretornoremessaitem,p.nome,rti.pagador,c.valorant,rti.valor as valor1
                    ,rti.datapagamento,rti.seunumero,rti.pagador,rti.idcontapagar,rti.carteira
                    ,rti.datapagamento+interval " . $dias . " day as datapagamento_1
                    ,c.status,c.statusant,c.datapagto,c.valor,c.idcontapagar as nossonum,c.idobjeto,nf.idunidade
                    FROM retornoremessaitem rti 
                    left join contapagar c on(c.idcontapagar = rti.idcontapagar) 
                    left join pessoa p on(c.idpessoa = p.idpessoa)
                    left join nf nf on(nf.idnf = c.idobjeto)
                    where  rti.idretornoremessa =" . $_1_u_retornoremessa_idretornoremessa . " order by p.nome";
                    $res = d::b()->query($sql) or die("Falha ao consultar retornoremessa: " . mysqli_error(d::b()) . "<p>SQL: $sql");
                    $qtditem=mysqli_num_rows($res);

                    $sqlsum = "SELECT SUM(valor) as total FROM retornoremessaitem where  idretornoremessa =" . $_1_u_retornoremessa_idretornoremessa;
                    $resum = d::b()->query($sqlsum);
                    $rowsum = mysqli_fetch_assoc($resum);
                    $ip = 1;
                    $k = 0;
                ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">Lista Retorno - (<?=$qtditem?> - Faturas ) </div>
                                <div class="panel-body">
                                    <table class="table planilha display " id="inftable">
                                        <tr style="background-color: #F0F0F0;">
                                            <td>Nfe</td>
                                            <td>Pagador</td>
                                            <td>Data de Pagamento</td>
                                            <td align="center">Valor</td>
                                            <td align="center">Juros/Multa</td>
                                            <td align="center">Nosso Numero</td>
                                            <td>Status</td>
                                            <td align="center">
                                                <input type="checkbox" id="checkAll">
                                                Tudo
                                            </td>
                                        </tr>
                                        <? while ($row = mysqli_fetch_assoc($res)) {

                                            if ($k < 1) {
                                                $corlinha = 'white';
                                            } else {
                                                $k = -1;
                                                $corlinha = '#F0F0F0';
                                            }

                                            if ($row['carteira'] != 109 or empty($row['nome'])) {

                                                $nome = $row['pagador'];
                                                if (!empty($row['carteira']) or empty($row['seunumero'])) {
                                                    $cor = 'background-color: #d9534f; color: #F0F0F0';
                                                    $corlinha = '';
                                                }
                                            } else {
                                                $nome = $row['nome'];
                                                $cor = '';
                                            }
                                            if ($row['status'] != 'QUITADO' and $row['status'] != 'PENDENTE') {
                                                $cor = 'background-color: #d9534f; color: #F0F0F0';
                                                $corlinha = '';
                                            } else {
                                                $cor = '';
                                            }
                                            if (!empty($row['valorant'])) {
                                                if ($row['valorant'] != $row['valor']) {
                                                    $valors = $row['valorant'];
                                                } else {
                                                    $valors = $row['valor'];
                                                }
                                            } else {
                                                $valors = $row['valor'];
                                            }
                                            $jm = $row['valor1'] - $valors;
                                        ?>
                                            <? if ($nome != $row['pagador'] or $rowroc['retornorm'] == 'RETORNOSICREDI') { ?>
                                                <tr style="background-color:<?= $corlinha ?>; <?= $cor ?>">
                                                    <td>
                                                        <? if ($row['idunidade'] == 2) {
                                                            $modulo = 'pedido';
                                                            $idmodulo = 'nf';
                                                            $nfs = 'NF SAÍDA - <br>';
                                                            $zero = '0000';
                                                        } else {
                                                            $modulo = 'nfs';
                                                            $idmodulo = 'notafiscal';
                                                            $nfs = 'NFS-E - <br>';
                                                            $zero = '';
                                                        }
                                                        if (empty($row['idobjeto'])) {
                                                            echo $row['seunumero'];
                                                        } else { ?>
                                                            <a class="pointer" onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&id<?= $idmodulo ?>=<?= $row['idobjeto'] ?>');"><?= $nfs; ?><?= $zero; ?><?= $row['seunumero'] ?></a>
                                                        <? } ?>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" idretornoremessaitem="<?= $row['idretornoremessaitem'] ?>">
                                                        <?= $nome ?>
                                                    </td>
                                                    <td>
                                                        <?= dma($row['datapagto']); ?>
                                                    </td>
                                                    <td align="right">
                                                        <? if (!empty($row['valorant'])) {
                                                            if ($row['valorant'] != $row['valor']) {
                                                                $valors = $row['valorant'];
                                                            } else {
                                                                $valors = $row['valor'];
                                                            }
                                                        } else {
                                                            $valors = $row['valor'];
                                                        } ?>
                                                        <?= number_format(tratanumero($valors), 2, ',', '.'); ?>
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            <? } ?>
                                            <tr style="background-color:<?= $corlinha ?>; <?= $cor ?>">
                                                <td>
                                                    <?= $row['seunumero'] ?>
                                                </td>
                                                <td>
                                                    <?= $nome ?>
                                                </td>
                                                <td>
                                                    <?= dma($row['datapagamento']); ?>
                                                </td>
                                                <td align="right">
                                                    <?= number_format(tratanumero($row['valor1']), 2, ',', '.'); ?>
                                                </td>
                                                <td align="right">
                                                    <?= number_format(tratanumero($jm), 2, ',', '.'); ?></td>
                                                <td align="center">
                                                    <a class="pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?= $row['idcontapagar'] ?>');"><?= $row['idcontapagar'] ?></a>
                                                </td>
                                                <td>
                                                    <?= $row['status']; ?>
                                                </td>
                                                <td align="center">
                                                    <? if ($row['status'] != 'QUITADO' and $row['status'] != null) {
                                                        //if($rowroc['retornorm'] == 'RETORNOSICREDI'){
                                                        $dta = $row['datapagamento'];
                                                        /* }else{
                                                            $dta = $row['datapagamento_1'];
                                                        }*/
                                                    ?>
                                                        <span style="display:inline;">
                                                            <input type="hidden" value="<?= dma($dta) ?>">
                                                            <input name="_<?= $ip ?>_u_contapagar_idcontapagar" type="hidden" value="<?= $row["idcontapagar"] ?>">
                                                            <input type="hidden" value="<?= $row['valor1'] ?>">
                                                            <input valor="" style="background-color:#cccccc;" id="checkItem_<?= $ip ?>" name="chk_<?= $ip ?>" value="<?= $row["idcontapagar"] ?>" type="checkbox">
                                                        </span>
                                                    <? } ?>
                                                </td>
                                            </tr>
                                        <? $ip++;
                                            $k++;
                                        } ?>
                                        <tr style="background-color: #F0F0F0;">
                                            <td></td>
                                            <td></td>
                                            <td>Valor Total:</td>
                                            <td align="right">
                                                <?= number_format(tratanumero($rowsum['total']), 2, ',', '.'); ?>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </table>
                                    <? if (mysqli_num_rows($res) > 0) {
                                        $sqlt = "SELECT 
                                                    IFNULL(nnfe, idnf) AS nnfe, idnf
                                                FROM
                                                    nf
                                                WHERE
                                                    idobjetosolipor = " . $_1_u_retornoremessa_idretornoremessa . "
                                                    AND tipoobjetosolipor = 'retornoremessa'";
                                        $rest = d::b()->query($sqlt) or die("Falha ao consultar tarifa: " . mysqli_error(d::b()) . "<p>SQL: $sqlt");
                                        $qtdt = mysqli_num_rows($rest);
                                        $rowr = mysqli_fetch_assoc($rest); ?>
                                        <div class='pull-right' style="margin-right: 50px;">
                                            <? if ($qtdt > 0) { ?>
                                                Tarifa:<a class="pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $rowr['idnf'] ?>');"> <?= $rowr['nnfe'] ?> </a>
                                            <? } else { ?>
                                                <button id="tarifar" class="btn btn-success btn-xs" onclick="tarifar(<?= $_1_u_retornoremessa_idretornoremessa ?>)">
                                                    <i class="fa fa-circle"></i> Tarifar
                                                </button>
                                            <? } ?>
                                        </div>
                                        <div class='pull-right' style="margin-right: 50px;">
                                            <button class="btn btn-danger btn-xs" onclick="quitar(this,'QUITADO','906');">
                                                <i class="fa fa-circle"></i> Quitar
                                            </button>
                                        </div>
                                        <? $sqlar = "SELECT idarquivo
                                                    from arquivo  
                                                    where 
                                                    tipoobjeto = 'extrato'
                                                    and idobjeto = " . $_1_u_retornoremessa_idretornoremessa . "
                                                    and tipoarquivo = '" . $rowroc['retornorm'] . "'
                                                    order by idarquivo asc";
                                        $resar = d::b()->query($sqlar);
                                        $rowar = mysqli_fetch_assoc($resar);
                                        ?>
                                        <div class='pull-right' style="margin-right: 50px;">
                                            <button class="btn btn-primary btn-xs" onclick="limpar(<?= $rowar['idarquivo'] ?>)">
                                                <i class="fa fa-circle"></i> Limpar
                                            </button>
                                        </div>
                                        <? $sqlt = "SELECT idnf
                                                FROM
                                                    nf
                                                WHERE
                                                    idobjetosolipor = " . $_1_u_retornoremessa_idretornoremessa . "
                                                    AND tipoobjetosolipor = 'retornoremessatransferencia'";
                                        $rest = d::b()->query($sqlt) or die("Falha ao consultar tarifa: " . mysqli_error(d::b()) . "<p>SQL: $sqlt");
                                        $qtdt = mysqli_num_rows($rest);
                                        $rowr = mysqli_fetch_assoc($rest); ?>
                                        <div class='pull-right' style="margin-right: 50px;">
                                            <? if ($qtdt > 0) { ?>
                                                <button class="btn btn-default btn-xs" id="transferir" onclick="transferido('')">
                                                    <i class="fa fa-circle"></i> Transferido
                                                </button>
                                            <? } else { ?>
                                                <button class="btn btn-warning btn-xs" id="transferir" onclick="transferir('')">
                                                    <i class="fa fa-circle"></i> Transferir
                                                </button>
                                            <? } ?>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <? } ?>
            </div>
        </div>
    </div>
</div>

<? $tabaud = "retornoremessa";
require 'viewCriadoAlterado.php'; ?>
<script>
    $("#checkAll").click(function() {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });
    //FUNààO PARA QUITAR 
    function quitar(vthis, status, idfluxostatus) {

        //pega todos os inputs checkados 		
        var inputprenchido = $("#inftable").children().find("input:checkbox:checked").not("#checkAll");
        var vsubmit = {}

        //pega todos os input e os inputs checkados 	
        inputprenchido.each((i, o) => {
            let id = o.name.split('_')[1];
            vsubmit[$(o).siblings()[1].name] = $(o).siblings()[1].value;
            vsubmit[`_${id}_u_contapagar_status`] = status;
            vsubmit[`_${id}_u_contapagar_idfluxostatus`] = idfluxostatus;
            vsubmit[`_${id}_u_contapagar_datareceb`] = $(o).siblings()[0].value;
            vsubmit[`_${id}_u_contapagar_valor`] = $(o).siblings()[2].value;
        })

        if (Object.keys(vsubmit).length > 0) {
            CB.post({
                objetos: vsubmit,
                parcial: true
            })
        } else {
            alert('Selecione ao menos um item');
        }
    }

    function limpar(idarquivo) {
        if (confirm("Deseja realmente excluir o tudo?")) {
            var item = {
                '_x_d_arquivo_idarquivo': idarquivo
            }

            $('[idretornoremessaitem]').each((index, element) => {
                item['_del' + index + '_d_retornoremessaitem_idretornoremessaitem'] = $(element).attr('idretornoremessaitem');
            })
            CB.post({
                "objetos": item,
                parcial: true
            });
        }
    }

    function tarifar(idretornoremessa) {

        $.ajax({
            type: "get",
            url: "ajax/htmltarifa.php",
            data: {
                idretornoremessa: idretornoremessa
            },

            success: function(data) {

                CB.modal({
                    titulo: "</strong>Gerar Tarifa de Liquidação de Boleto <button type='button' class='btn btn-danger btn-xs' onclick='salvartarifa();'><i class='fa fa-circle'></i>Salvar</button></strong>",
                    corpo: data,
                    classe: 'sessenta',
                    aoAbrir: function(vthis) {
                        $(".calendario").daterangepicker({
                            "singleDatePicker": true,
                            "showDropdowns": true,
                            "linkedCalendars": false,
                            "opens": "left",
                            "locale": {
                                format: 'DD/MM/YYYY'
                            }
                        }).on('apply.daterangepicker', function(ev, picker) {
                            console.log(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                            $(this).val(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                        });
                    }
                });
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        })
    }

    function salvartarifa() {
        var allInputs = "_1_i_nf_idpessoa=" + $("[name=_1_i_nf_idpessoa]").attr('cbvalue') + "&_1_i_nf_idformapagamento=" + $("[name=_1_i_nf_idformapagamento]").attr('cbvalue') + "&_1_i_nf_dtemissao=" + $("[name=_1_i_nf_dtemissao]").val();

        CB.post({
            objetos: allInputs + "&idretornoremessa=" + $("[name=_1_u_retornoremessa_idretornoremessa]").val(),
            parcial: true,
            posPost: function(resp, status, ajax) {
                $('#cbModal').modal('hide');
            }
        })
    }

    <? if ($_acao == 'u') { ?>

        function transferir() {
            $.ajax({
                type: "post",
                url: "ajax/htmltransferirconta.php?valortotal=" + <?= $rowsum['total'] ?> + "&dtremessa=" + <?= $_1_u_retornoremessa_dataremessa ?>,
                data: {
                    idformapagamento: $("[name=_1_u_retornoremessa_idagencia").val(),
                },

                success: function(data) {
                    CB.modal({
                        titulo: "</strong>Transferência <button type='button' class='btn btn-warning btn-xs' onclick='transferirconta();'><i class='fa fa-circle'></i>Confirmar Transferência</button></strong>",
                        corpo: data,
                        classe: 'sessenta',
                        aoAbrir: function(vthis) {
                            $(".calendario").daterangepicker({
                                "singleDatePicker": true,
                                "showDropdowns": true,
                                "linkedCalendars": false,
                                "opens": "left",
                                "locale": {
                                    format: 'DD/MM/YYYY'
                                }
                            }).on('apply.daterangepicker', function(ev, picker) {
                                console.log(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                                $(this).val(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                            });
                        },
                    });
                },
            })
        }

        function transferirconta() {
            var dados = $('#dadostranferencia').find(':input').serialize();
            dados += "&_transf_u_retornoremessa_idretornoremessa=" + $("[name=_1_u_retornoremessa_idretornoremessa]").val()
            dados += "&_transf_u_retornoremessa_status=" + $("[name=_1_u_retornoremessa_status]").val();
            dados += "&_orig_i_nf_idpessoa=" + $("[name=_orig_i_nf_idpessoa]").attr('cbvalue');
            dados += "&_dest_i_nf_idpessoa=" + $("[name=_dest_i_nf_idpessoa]").attr('cbvalue');

            CB.post({
                objetos: dados,
                parcial: true,
                posPost: function(resp, status, ajax) {
                    $.ajax({
                        type: "post",
                        url: "ajax/htmlresultadotransferencia.php",
                        data: {
                            idretornoremessa: $("[name=_1_u_retornoremessa_idretornoremessa]").val(),
                        },

                        success: function(data) {
                            CB.modal({
                                titulo: "</strong>Resultado transferência</strong>",
                                corpo: data,
                                classe: 'sessenta',
                            });
                        },
                    })
                }
            })
        }

        function transferido() {
            $.ajax({
                type: "post",
                url: "ajax/htmlresultadotransferencia.php",
                data: {
                    idretornoremessa: $("[name=_1_u_retornoremessa_idretornoremessa]").val(),
                },
                success: function(data) {
                    CB.modal({
                        titulo: "</strong>Resultado transferência</strong>",
                        corpo: data,
                        classe: 'sessenta',
                    });
                },
            })
        }

    <? } ?>


    $(".cbupload").dropzone({
        idObjeto: '<?= $_1_u_retornoremessa_idretornoremessa; ?>',
        tipoObjeto: 'extrato',
        tipoArquivo: '<?= $rowroc['retornorm'] ?>',
        idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"]; ?>'
    });

    CB.montaLegenda({
        "#FF8491": "Divergencia, verificar manualmente."
    });
    CB.oPanelLegenda.css("zIndex", 901);
</script>
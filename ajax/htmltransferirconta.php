<?
require_once("../inc/php/functions.php");
require_once("../inc/php/permissao.php");
require_once("../form/controllers/nfentrada_controller.php");
require_once("../form/controllers/formapagamento_controller.php");
require_once("../form/controllers/empresa_controller.php");
require_once("../form/controllers/prodserv_controller.php");

function getClientesfilial()
{

    $sql = "SELECT
            p.idpessoa,
            concat( if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome), ' - ',e.sigla) as nome,
            CASE p.idtipopessoa
            WHEN 1 THEN 'FUNCIONARIO'
                WHEN 5 THEN 'FORNECEDOR'
                WHEN 2 THEN 'CLIENTE'	
                WHEN 7 THEN 'TERCEIRO'
                WHEN 12 THEN 'REPRESENTAÇÃO'					
            END as tipo
            FROM pessoa p	
            JOIN empresa e ON e.idempresa = p.idempresa	           
            WHERE p.status IN ('ATIVO','PENDENTE')
            AND p.idtipopessoa  IN (5,7)   
            " . share::otipo('cb::usr')::pessoaPorCbUserIdempresa("p.idpessoa") . "             
            ORDER BY p.nome";

    $res = d::b()->query($sql) or die("getClientes: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

    $arrret = array();
    while ($r = mysqli_fetch_assoc($res)) {
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"] = $r["nome"];
        $arrret[$r["idpessoa"]]["tipo"] = $r["tipo"];
    }
    return $arrret;
}

//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCliF = getClientesfilial();
$jCliF = $JSON->encode($arrCliF);

if (isset($_GET['idempresa'])) {
    $formasdepagamento = FormaPagamentoController::listarFormaPagamentoPorEmpresa($_GET['idempresa'], false);

    foreach ($formasdepagamento as $key => $dados) {
        if ($dados['formapagamento'] != 'C.CREDITO') {
            $retorno['pagamento'] .= '<option value="' . $dados['idformapagamento'] . '">' . $dados['descricao'] . '</option>';
        }
    }

    $item = ProdServController::listaProdservTranferencia($_GET['idempresa']);
    foreach ($item as $key => $dados) {
        $retorno['item'] .= '<option value="' . $dados['idprodserv'] . '">' . $dados['descr'] . '</option>';
    }

    echo json_encode($retorno);
    die();
}

if (isset($_POST['fornecedor'])) {
    $sql = "SELECT idpessoa, nome FROM pessoa where idpessoa = " . $_POST['fornecedor'];

    $res = d::b()->query($sql) or die("getpessoa: Erro: " . mysqli_error(d::b()) . "\n" . $sql);
    while ($r = mysqli_fetch_assoc($res)) {
        $fornecedororigem["id"] = $r["idpessoa"];
        $fornecedororigem["nome"] = $r["nome"];
    }
}
if (isset($_POST['idformapagamento '])) {
    $sql = "SELECT idformapagamento,descricao, formapagamento
                        FROM formapagamento 
                        WHERE status = 'ATIVO' 
                        AND idempresa = {$_GET['_idempresa']}
                        and idformapagamento = {$_POST['idformapagamento']}
                        ORDER BY descricao";

    $res = d::b()->query($sql) or die("getpessoa: Erro: " . mysqli_error(d::b()) . "\n" . $sql);
    while ($r = mysqli_fetch_assoc($res)) {
        $pagamentoorigem["id"]      = $r["idpessoa"];
        $pagamentoorigem["nome"]    = $r["nome"];
    }
}

//Vamos pegar a forma de pagamento
$formasdepagamento = FormaPagamentoController::listarFormaPagamentoPorEmpresa($_GET['_idempresa'], false);
$empresasativas = EmpresaController::buscarEmpresaPorIdEmpresa($_GET['_idempresa']);
$item = ProdServController::listaProdservTranferencia($_GET['_idempresa']);

?>
<div id="dadostranferencia">

    <div class="row">
        <div class="col-lg-6">
            <div style="text-align: center; font-size: 15px;">
                <strong>Débito</strong>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div style="text-align: center; font-size: 15px;">
                <strong>Crédito</strong>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Emissão:</span><br />
                <input name="_dest_i_nf_dtemissao" class="calendario form-control" id="fdata" type="text" value="<?= $_1_u_retornoremessa_dataremessa ?>">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Empresa de Crédito:</span><br />
                <select name="_dest_i_nf_idempresa" id="empresadestino" disabled>
                    <option value="<?= $empresasativas['idempresa'] ?>"><?= $empresasativas['nomefantasia'] ?></option>
                    <?/*  foreach ($empresasativas as $key => $dados) { ?>
                        <option value="<?= $dados['idempresa'] ?>"><?= $dados['nomefantasia'] ?></option>
                    <? } */ ?>
                </select>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Nota fiscal débito:</span><br />
                <input name="_orig_i_nf_nforigem" type="text" value="<?= $_POST['notafiscal'] ?>">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Nota fiscal crédito:</span><br />
                <input name="_dest_i_nf_nfdestino" type="text">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Fornecedor débito:</span><br />
                <? if (isset($_POST['fornecedor'])) { ?>
                    <select name="_orig_i_nf_idpessoa">
                        <option value="<?= $fornecedororigem["id"] ?>" selected cbvalue="<?= $fornecedororigem["id"] ?>"> <?= $fornecedororigem["nome"] ?></option>
                    </select>
                <? } else { ?>
                    <input type="text" name="_orig_i_nf_idpessoa" vnulo cbvalue="" value="">
                <? } ?>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Fornecedor crétido:</span><br />
                <input type="text" name="_dest_i_nf_idpessoa" vnulo cbvalue="" value="">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;" style="font-size: 12px;">Forma de pagamento débito:</span><br />
                <div class="dropdown">
                    <select name="_orig_i_nf_idformapagamento">
                        <? foreach ($formasdepagamento as $key => $dados) {
                            /* if ($dados['formapagamento'] != 'C.CREDITO') {*/ ?>
                            <option value="<?= $dados['idformapagamento'] ?>" <?= $dados['idformapagamento'] == $_POST["idformapagamento"] ? 'selected' : '' ?>><?= $dados['descricao'] ?></option>
                        <? /* } */
                        } ?>
                    </select>
                </div>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Forma de pagamento crédito:</span><br />
                <select name="_dest_i_nf_idformapagamento" id="formapagamentodestino">
                    <option>Selecione empresa de destino</option>
                </select>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Item débito:</span><br />
                <select name="orig_nfitem_idprodserv" id="">
                    <? foreach ($item as $key => $dados) { ?>
                        <option value="<?= $dados['idprodserv'] ?>"><?= $dados['descr'] ?></option>
                    <? } ?>
                </select>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Item crédito:</span><br />
                <select name="dest_nfitem_idprodserv" id="itemdestino">
                    <option>Selecione empresa de destino</option>
                </select>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>

            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Valor:</span><br />
                <input type="text" name="_orig_i_nf_total" value="<?= number_format(tratanumero($_GET['valortotal']), 2, ',', '.') ?>" required>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

</div>

<script>
    /* $("#empresadestino").on("change", function() { */
    $("#cbModal").on("shown.bs.modal", function() {
        $.ajax({
            type: "get",
            url: "ajax/htmltransferirconta.php",
            data: {
                idempresa: <?= $empresasativas['idempresa'] ?>
            },

            success: function(data) {
                var array = JSON.parse(data);

                $('#formapagamentodestino').empty(); // Remove o select
                $('#itemdestino').empty(); // Remove o select

                $('#formapagamentodestino').append(array.pagamento); //insere os dados options
                $('#itemdestino').append(array.item);
                $('#cbModalCarregando').hide();
            },
        })
    });

    //inserir valor da remessa na transferencia.
    $("[name=_dest_i_nf_dtemissao]").val($("[name=_1_u_retornoremessa_dataremessa]").val());

    jCliF = <?= $jCliF ?>; // autocomplete cliente
    //mapear autocomplete de clientes
    jCliF = jQuery.map(jCliF, function(o, id) {
        return {
            "label": o.nome,
            value: id + "",
            "tipo": o.tipo
        }
    });

    //autocomplete pessoa origin
    $("[name*=_orig_i_nf_idpessoa]").autocomplete({
        source: jCliF,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });

    $("[name*=_dest_i_nf_idpessoa]").autocomplete({
        source: jCliF,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });
</script>
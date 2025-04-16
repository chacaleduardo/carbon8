<?
require_once("../inc/php/functions.php");
require_once("../inc/php/permissao.php");
require_once("../form/controllers/nfentrada_controller.php");
require_once("../form/controllers/formapagamento_controller.php");
require_once("../form/controllers/empresa_controller.php");
require_once("../form/controllers/prodserv_controller.php");

$sql = "SELECT ni.idprodserv, ps.descr, nf.*, p.nome
        FROM nf
            JOIN pessoa p ON p.idpessoa = nf.idpessoa
            JOIN nfitem ni ON ni.idnf = nf.idnf
            JOIN prodserv ps on ps.idprodserv = ni.idprodserv
        WHERE
            nf.tipoobjetosolipor = '" ;
$sql .= isset($_POST["local"]) ? $_POST["local"] : 'retornoremessatransferencia' ;
$sql .= "' AND nf.idobjetosolipor = ";
$sql .= $_REQUEST['idretornoremessa'];

$res = d::b()->query($sql) or die("Erro: " . mysqli_error(d::b()) . "\n" . $sql);

$origem = array();
$destino = array();

while ($r = mysqli_fetch_assoc($res)) {
    //monta 2 estruturas json para finalidades (loops) diferentes

    $data = new DateTime($r['dtemissao']);
    if ($r['tipocontapagar'] == 'D') {
        $pagamentoorigem = FormaPagamentoController::buscarInfFormapagamentoPorId($r['idformapagamento']);
        //debito é origem
        $origem['idnf'] =  $r['idnf'];
        $origem['agencia'] =  $pagamentoorigem['descricao'];
        $origem['origemnf'] = $r['nnfe'];
        $origem['tiponf'] = $r['tiponf'];
        $origem['dtemissao'] = $data->format('d/m/Y');
        $origem['fornecedor'] = $r['nome'];
        $origem['itemdescricao'] = $r['descr'];
        $origem['formapagamento'] = 'Transferência';
        $origem['valor'] = number_format($r['total'], 2, ',', '.');
    } else if ($r['tipocontapagar'] == 'C') {
        $pagamentoorigem = FormaPagamentoController::buscarInfFormapagamentoPorId($r['idformapagamento']);
        //credito é destino
        $destino['idnf'] =  $r['idnf'];
        $destino['agencia'] =  $pagamentoorigem['descricao'];
        $destino['origemnf'] = $r['nnfe'];
        $destino['tiponf'] = $r['tiponf'];
        $destino['dtemissao'] = $data->format('d/m/Y');
        $destino['fornecedor'] = $r['nome'];
        $destino['itemdescricao'] = $r['descr'];
        $destino['formapagamento'] = 'Transferência';
        $destino['valor'] = number_format($r['total'], 2, ',', '.');
    } else {
        die('Erro ao identificar o tipo de conta a pagar');
    }
}
?>

<div id="dadostranferencia">

    <div class="row">
        <div class="col-lg-6">
            <div style="text-align: center; font-size: 15px;">
                <strong>Débito <a class="pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $origem['idnf'] ?>&_idempresa=2');"><?= $origem['idnf'] ?></a></strong>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div style="text-align: center; font-size: 15px;">
                <strong>Crédito <a class="pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $destino['idnf'] ?>&_idempresa=2');"><?= $destino['idnf'] ?></a></strong>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Agencia débito:</span><br />
                <input type="text" value="<?= $origem['agencia'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Agencia Crédito:</span><br />
                <input type="text" value="<?= $destino['agencia'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Nota fiscal débito:</span><br />
                <input type="text" value="<?= $origem['origemnf'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Nota fiscal Crédito:</span><br />
                <input type="text" value="<?= $destino['origemnf'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Data de emissão:</span><br />
                <input type="text" value="<?= $origem['dtemissao'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Data de emissão:</span><br />
                <input type="text" value="<?= $destino['dtemissao'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Fornecedor débito:</span><br />
                <input type="text" value="<?= $origem['fornecedor'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Fornecedor Crédito:</span><br />
                <input type="text" value="<?= $destino['fornecedor'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Item débito:</span><br />
                <input type="text" value="<?= $origem['itemdescricao'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Item Crédito:</span><br />
                <input type="text" value="<?= $destino['itemdescricao'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Valor débito:</span><br />
                <input type="text" value="<?= $origem['valor'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
        <div class="col-lg-6">
            <div>
                <span style="font-size: 12px;">Valor Crédito:</span><br />
                <input type="text" value="<?= $destino['valor'] ?>" disabled="disabled">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->

</div>
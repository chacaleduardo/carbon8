<?
require_once("../api/nf/index.php");
require_once("../form/controllers/nfentrada_controller.php");

$botaoStatus = '';
if (!empty($_idobjeto)) { // Não permitir concluir uma nota dos tipos do select sem rateio
    $i = 0;
    $statuspendente = 'N';
    $problema = array();
    $i = 0;
    $sqls = "SELECT 1
               FROM lote l JOIN nfitem ni ON ni.idnfitem = l.idnfitem
               JOIN nf n ON n.idnf = ni.idnf
              WHERE n.status <> 'CONCLUIDO'
                AND l.idlote = $_idobjeto";

    $ress = d::b()->query($sqls) or die("Erro ao buscar Lote: $sqls");
    $qtd = mysqli_num_rows($ress);
    if ($qtd > 0) {
        $statuspendente = 'Y';
        $problema[$i] = 'NOTANAOCONCLUIDA';
        $botaoStatusExec = array('TRIAGEM', 'PROCESSANDO', 'QUARENTENA', 'EMANALISE', 'APROVADO');
    }
}

$status['permissao']['modulo'] = 'lotealmoxarifado';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = $botaoStatusExec;
$status['permissao']['problema'] = $problema;

?>
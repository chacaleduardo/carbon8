<?
$_idfolhapagamento = $_SESSION['arrpostbuffer']['1']['u']['folhapagamento']['idfolhapagamento'];
$_idempresa = $_SESSION['arrpostbuffer']['1']['u']['folhapagamento']['idempresa'];
$codigoevento = $_POST['codigoevento'];
$_nnfe = $_POST['nnfe'];

if($codigoevento){
    $_idnf = FolhaPagamentoController::gerarNotaFerias($_idfolhapagamento, $codigoevento, $_idempresa, $_nnfe);
    if($_idnf == 'SEMLANCAMENTO'){
        die('NF Automática não configurada');
    } else {
        cnf::$idempresa = $_idempresa;
        cnf::atualizavalornf($_idnf);
        cnf::atualizafat($_idnf);
        cnf::geraRateio($_idnf); 
    }      
}

?>
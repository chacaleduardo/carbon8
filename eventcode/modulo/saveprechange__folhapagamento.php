<?
// Controllers
require_once(__DIR__ . "/../../form/controllers/folhapagamento_controller.php");

$_idfolhapagamento = $_SESSION['arrpostbuffer']['1']['u']['folhapagamento']['idfolhapagamento'];
$_idempresa = $_SESSION['arrpostbuffer']['1']['u']['folhapagamento']['idempresa'];
$dadosLancamentoJSON = $_POST['dataObjects'];

if(!empty($dadosLancamentoJSON)){
    $dadosLancamento = json_decode($dadosLancamentoJSON, true);
    $i = 1;
    $countDuplicado = 0;
    $error = false;
    $mensagemErro = '';
    $mensagemDuplicado = '';
    $empresa = FolhaPagamentoController::buscarEmpresaPorRazaoSocial($dadosLancamento[0]['empresa']);
    $periodo = end($dadosLancamento);
    array_shift($dadosLancamento); // Remove a primeira linha
    array_pop($dadosLancamento);   // Remove a última linha

    foreach($dadosLancamento as $dados){        
        $pessoa = FolhaPagamentoController::buscarFuncionarPorNome($dados['nome'], $empresa['idempresa']);

        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['idfolhapagamento'] = $_idfolhapagamento;
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['idempresa'] = $empresa['idempresa'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['idpessoa'] = $pessoa['idpessoa'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['datalancamento'] = $dados['datalancamento'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['descricaolancamento'] = $dados['lancamento'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['contadebito'] = $dados['contadebito'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['contacredito'] = $dados['contacredito'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['valorlancamento'] = number_format(intval($dados['valor']) / 100, 2, '.', '');
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['centrocusto'] = $dados['centrocusto'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['codigoevento'] = $dados['codhistorico'];
        $_SESSION['arrpostbuffer']['con'.$i]['i']['folhapagamentoitem']['historicoevento'] = $dados['historico'];

        $i++;
    }

    retarraytabdef('folhapagamentoitem');

    $_SESSION['arrpostbuffer']['1']['u']['folhapagamento']['periodo'] = str_replace(",", " a ", $periodo['periodo']);
}
?>
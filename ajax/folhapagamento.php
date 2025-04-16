<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();
$retorno = [];

if ($jwt["sucesso"] !== true) {
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/folhapagamento_controller.php");

$action = $_GET['action'] ?? $_POST['action'];

if ($action) {
    $params = $_GET['params'] ?? $_POST['params'];

    if (!isset($params['typeParam'])) {
        $params['typeParam'] = false;
    }

    if (is_array($params) && ($params['typeParam'] != 'array')) {
        return $action(implode(',', $params));
    }

    return $action($params);
}

function removerLancamentoFolhaPonto($idfolhapagamento)
{
    global $retorno;

    if (!$idfolhapagamento) {
        array_push($retorno, ['error' => true, 'mensagem' => 'ID não informado']);
        return;
    }

    $removendoConciliacoes = FolhaPagamentoController::removerLancamentoFolhaPonto($idfolhapagamento);
    $removerArquivo = FolhaPagamentoController::apagarArquivoPorTipoArquivoObjetoETipoObjeto($idfolhapagamento, 'folhapagamento', 'folhaponto');

    if ($removendoConciliacoes && $removerArquivo)    
        array_push($retorno, ['error' => true, 'mensagem' => 'Erro ao remover lançamentos']);
    elseif($removendoConciliacoes)
        array_push($retorno, ['error' => true, 'mensagem' => 'Erro ao remover arquivo']);
    else
        array_push($retorno, ['error' => false, 'mensagem' => 'Arquivo Removido']);
    
    echo json_encode($retorno);
}

function validarDadosArquivo($params){

    list($_idfolhapagamento, $dadosLancamentoJSON, $_idempresa) = explode('|', $params);

    if(!empty($dadosLancamentoJSON)){
        $dadosLancamento = json_decode($dadosLancamentoJSON, true);
        $i = 1;
        $countDuplicado = 0;
        $error = false;
        $mensagemErro = '';
        $mensagemDuplicado = '';
        $duplicadoLancamento = '';
        $historicos = '';
        $arrayHistoricos = [];
        $mensagemErroColaborador = '';
        $arrayPessoas = [];
        $empresa = FolhaPagamentoController::buscarEmpresaPorRazaoSocial($dadosLancamento[0]['empresa']);
        array_shift($dadosLancamento); // Remove a primeira linha
        array_pop($dadosLancamento);   // Remove a última linha

        if($empresa['idempresa'] != $_idempresa){
            $mensagemErro = "Inconsistência de dados: a empresa selecionada no sistema não corresponde à empresa registrada no arquivo carregado.";
            echo json_encode(['error' => true, 'mensagem' => $mensagemErro]);
            return;
        }

        if($empresa) {
            foreach($dadosLancamento as $dados){        
                $pessoa = FolhaPagamentoController::buscarFuncionarPorNome($dados['nome'], $empresa['idempresa']);
                if(count($pessoa) == 0 and !empty($dados['nome']) and !empty($empresa['idempresa'])) {
                    $error = true;
                    $coloaborador = true;
                    $mensagemErroColaborador .= '- '.$dados['nome'].' idempres='.$empresa['idempresa'].'<br>';
                    /*
                    if(!in_array($dados['nome'], $arrayPessoas)){
                        $mensagemErroColaborador .= '- '.$dados['nome'].' idempres='.$empresa['idempresa'].'<br>';
                     }
                 */

                    array_push($arrayPessoas, $dados['nome']);
                }

                if(count($pessoa) > 1) {
                    $error = true;
                    $mensagemErro .= 'Existem mais de um cadastro da Pessoa: '.$dados['nome'].'<br>';
                }
                
                $histDominio = FolhaPagamentoController::buscarHistoricoDominio($dados['codhistorico']);
                if(empty($histDominio['historicodominio'])){
                    $error = true;
                    $semEvento = true;

                    if(!in_array($dados['historico'], $arrayHistoricos)){
                        $historicos .= '- '.$dados['historico'].'<br>';
                     }
                    
                    array_push($arrayHistoricos, $dados['historico']);
                }   

                $valorLancamento = number_format(intval($dados['valor']) / 100, 2, '.', '');
                $dataLancamento = date("Y-m-d", strtotime(str_replace("/", "-", $dados['datalancamento'])));
                $registroDuplicado = FolhaPagamentoController::buscarLancamentosRepetidos($dataLancamento, $pessoa['idpessoa'], $valorLancamento, $dados['codhistorico']);
                if($registroDuplicado > 0){                
                    $error = true;
                    $duplicadoLancamento = true;
                    $countDuplicado++;
                    $mensagemDuplicado .= '- '.$dados['datalancamento'].' - '.$dados['nome'].': '.$dados['historico'].' - '.$valorLancamento.'.<br>';
                }

                $i++;
            }
        } else {
            $mensagemErro .= "A empresa '". $dadosLancamento[0]['empresa']." não foi encontrada. Verifique se o Nome está correto ou se ela existe no Sistema. <br>";
        }

        if($error){
            if($coloaborador){
                $mensagemErro .= 'Colaborador não encontrado: Verifique o cadastro no sistema.<br>';
                $mensagemErro .= "$mensagemErroColaborador <br>";
            }

            if($duplicadoLancamento){
                $mensagemErro .= "Importação parcial: [".$countDuplicado."] linhas foram ignoradas devido à existência de registros duplicados no banco de dados. Esses registros foram provavelmente importados em uma operação anterior: <br>";   
                $mensagemErro .= "$mensagemDuplicado <br>";
            }  
            
            if($semEvento){
                $mensagemErro .= 'Evento(s) de folha não encontrado: '.$dados['historico'].'. Verifique se o(s) evento(s) está(o) cadastrado(s) no sistema.<br>';
                $mensagemErro .= $historicos;
            }

            FolhaPagamentoController::apagarArquivoPorTipoArquivoObjetoETipoObjeto($_idfolhapagamento, 'folhapagamento', 'folhaponto');
            echo json_encode(['error' => true, 'mensagem' => $mensagemErro]);
        } else {
            echo json_encode(['error' => false, 'mensagem' => '']);
        }
    }
}
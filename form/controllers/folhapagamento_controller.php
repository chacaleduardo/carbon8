<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/arquivo_query.php");
require_once(__DIR__."/../querys/empresa_query.php");
require_once(__DIR__."/../querys/folhapagamentoitem_query.php");
require_once(__DIR__."/../querys/nfconfpagar_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/rhtipoevento_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/fluxo_controller.php");
require_once(__DIR__."/nf_controller.php");
require_once(__DIR__."/unidade_controller.php");


require_once("../api/rhfolha/index.php");
require_once("../api/nf/index.php");

class FolhaPagamentoController extends Controller
{
	// ----- FUNÇÕES -----
	public static function listarEmpresasAtivas()
    {
        $fillSelectEmpresaAtivo = SQL::ini(EmpresaQuery::listarEmpresasAtivas())::exec();

        if($fillSelectEmpresaAtivo->error()){
            parent::error(__CLASS__, __FUNCTION__, $fillSelectEmpresaAtivo->errorMessage());
            return "";
        } else {
            return parent::toFillSelect($fillSelectEmpresaAtivo->data);
        }
    }

    public static function buscarArquivoPorTipoObjetoEIdObjeto($idobjeto, $tipoobjeto)
    {
        $arquivo = SQL::ini(ArquivoQuery::buscarArquivoPorTipoObjetoEIdObjeto(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto
        ])::exec();

        if ($arquivo->error()) {
            parent::error(__CLASS__, __FUNCTION__, $arquivo->errorMessage());
            return [];
        }
        
        return $arquivo->data[0];
    }

    public static function buscarFuncionarPorNome($nome, $idempresa)
    {
        $pessoa = SQL::ini(PessoaQuery::buscarFuncionarPorNome(), [
            'nome' => $nome,
            'idempresa' => $idempresa
        ])::exec();

        if ($pessoa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $pessoa->errorMessage());
            return [];
        }
        
        return $pessoa->data[0];
    }

    public static function buscarEmpresaPorRazaoSocial($razaosocial)
    {
        $empresa = SQL::ini(EmpresaQuery::buscarEmpresaPorRazaoSocial(), [
            'razaosocial' => $razaosocial
        ])::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }
        
        return $empresa->data[0];
    }

    public static function buscarHistoricoDominio($codhistorico)
    {
        $empresa = SQL::ini(RhtipoeventoQuery::buscarHistoricoDominio(), [
            'historicodominio' => $codhistorico
        ])::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }
        
        return $empresa->data[0];
    }

    public static function buscarLancamentosRepetidos($datalancamento, $idpessoa, $valorLancamento, $codigoevento)
    {
        $empresa = SQL::ini(FolhapaPamentoItemQuery::buscarLancamentosRepetidos(), [
            'datalancamento' => $datalancamento,
            'idpessoa' => $idpessoa,
            'valorLancamento' => $valorLancamento,
            'codigoevento' => $codigoevento
        ])::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }
        
        return $empresa->data[0];
    }

    public static function buscarGruposConciliacao($_idfolhapagamento)
    {
        $empresa = SQL::ini(FolhapaPamentoItemQuery::buscarGruposConciliacao(), [
            'idfolhapagamento' => $_idfolhapagamento
        ])::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }

        return $empresa->data;
    }

    public static function buscarDetalhamentoLancamento($_idfolhapagamento, $codigoevento)
    {
        $empresa = SQL::ini(FolhapaPamentoItemQuery::buscarDetalhamentoLancamento(), [
            'idfolhapagamento' => $_idfolhapagamento,
            'codigoevento' => $codigoevento
        ])::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }

        return $empresa->data;
    }

    public static function apagarArquivoPorTipoArquivoObjetoETipoObjeto($idobjeto, $tipoobjeto, $tipoarquivo)
    {
        $empresa = SQL::ini(ArquivoQuery::apagarArquivoPorTipoArquivoObjetoETipoObjeto(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
            'tipoarquivo' => $tipoarquivo
        ])::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return true;
        }

        return false;
    }

    public static function removerLancamentoFolhaPonto($idfolhapagamento)
    {
        $empresa = SQL::ini(FolhapaPamentoItemQuery::removerLancamentoFolhaPonto(), [
            'idfolhapagamento' => $idfolhapagamento
        ])::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return true;
        }

        return false;
    }

    public static function buscarClassificacaoEvento($arrayNfConfPagar)
    {
        $empresa = SQL::ini(FolhapaPamentoItemQuery::buscarClassificacao(), $arrayNfConfPagar)::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }

        return $empresa->data[0];
    }

    public static function gerarNotaFerias($idfolhapagamento, $codigoevento, $idempresa, $_nnfe)
    {      
        $unidade = UnidadeController::buscarIdunidadePorTipoUnidade(14, $idempresa);
        $idfluxostatus = FluxoController::getIdFluxoStatus('comprasrh', 'PREVISAO');

        $lancamentos = self::buscarDetalhamentoLancamento($idfolhapagamento, $codigoevento);
        $tipoorc = $lancamentos[0]['tipoorc'];        
        $datalancamento = $lancamentos[0]['datalancamento'];
        $idfornecedor = $lancamentos[0]['idfornecedor'];
        $idformapagamento = $lancamentos[0]['idformapagamento'];
        
        if(!empty($lancamentos)){
            if(empty($lancamentos[0]['idnf'])){
                $arrayNf = [
                    "idpessoa" => $idfornecedor,
                    "dtemissao" => $datalancamento,
                    "tiponf" => 'R',
                    "idunidade" => $unidade['idunidade'],
                    "status" => 'PREVISAO',
                    "idfluxostatus" => $idfluxostatus,
                    "tipoorc" => $tipoorc,
                    "parcelas" => '1',
                    "idobjetosolipor" => $idfolhapagamento,
                    "tipoobjetosolipor" => 'folhapagamento',
                    "idempresa" => $idempresa,
                    "diasentrada" => 1,
                    "idformapagamento" => $idformapagamento,
                    "tpnf" => $lancamentos[0]['tpnf'],
                    "controle" => $codigoevento,
                    "nnfe" => $_nnfe,
                    "usuario" => $_SESSION["SESSAO"]["USUARIO"]
                ];

                $_idnf = self::inserirNfFolhaPagamento($arrayNf);
                FluxoController::inserirFluxoStatusHist('comprasrh', $_idnf, $idfluxostatus, 'PENDENTE');
                FluxoController::verificarInicio('comprasrh', 'idnf', $_idnf);

                $arrayNfConfPagar = [
                    "idnf" => $_idnf,                 
                    "idformapagamento" => $idformapagamento,
                    "idempresa" => $idempresa,
                    "proporcao" => 100,
                    "parcela" => 1,
                    "datareceb" => $lancamentos[0]['datalancamento']
                ];
                self::inserirIdNfContaPagarDataRecebFormaPagamento($arrayNfConfPagar);
            } else {
                $_idnf = $lancamentos[0]['idnf'];
            }

            crh::dnfitemrhfolha($_idnf);

            foreach($lancamentos AS $_lancamento){
                $idpessoa = $_lancamento['idpessoa'];
                $valorlancamento = $_lancamento['valorlancamento'];

                $arvalnfitem = self::buscaNfitem($_idnf, $idpessoa, $codigoevento);               

                if($valorlancamento > 0 && $arvalnfitem == 0){      
                    $arrnfitem = array();
                    $arrnfitem[1]['qtd'] = 1;
                    $arrnfitem[1]['vlritem'] = $valorlancamento;
                    $arrnfitem[1]['total'] = $valorlancamento;
                    $arrnfitem[1]['prodservdescr'] = $_lancamento['descricaoitem'];

                    if(!empty($_lancamento['idcontaitem']) && !empty($_lancamento['idtipoprodserv'])){
                        $arrnfitem[1]['idcontaitem'] = $_lancamento['idcontaitem'];
                        $arrnfitem[1]['idtipoprodserv'] = $_lancamento['idtipoprodserv'];
                    }

                    $arrnfitem[1]['idpessoa'] = $idpessoa;
                    $arrnfitem[1]['idobjetoitem'] = $idfolhapagamento;
                    $arrnfitem[1]['tipoobjetoitem'] = 'folhapagamento';
                    $arrnfitem[1]['statusitem'] = 'PENDENTE';
                    $arrnfitem[1]['idconfcontapagar'] = $_lancamento['idconfcontapagar'];
                    $arrnfitem[1]['dataitem'] = $datalancamento;
                    $arrnfitem[1]['idnf'] = $_idnf;
                    $arrnfitem[1]['nfe'] = 'Y';
                    $arrnfitem[1]['tiponf'] = 'R';
                    $arrnfitem[1]['idempresa'] = $idempresa;
                    $arrnfitem[1]['codigoitem'] = $codigoevento;
                    cnf::inseredb($arrnfitem, 'nfitem'); 

                }elseif($arvalnfitem['total'] != $valorlancamento && $arvalnfitem != 0){
                    cnf::atualizanfitem($arvalnfitem['idnfitem'], $valorlancamento, $_lancamento['idcontaitem'], $_lancamento['idtipoprodserv']);
                }
            }

            return $_idnf;
        } else {
            return 'SEMLANCAMENTO';
        }
    }

    public static function inserirNfFolhaPagamento($arrayInsertNf)
    {
        $results = SQL::ini(NfQuery::inserirNfFolhaPagamento(), $arrayInsertNf)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }
    public static function inserirIdNfContaPagarDataRecebFormaPagamento($arrayNfConfPagar)
    {
        $empresa = SQL::ini(NfConfPagar::inserirIdNfContaPagarDataRecebFormaPagamento(), $arrayNfConfPagar)::exec();

        if ($empresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresa->errorMessage());
            return [];
        }

        return $empresa->data;
    }

    public static function buscaNfitem($_idnf, $idpessoa, $codigoevento)
    {
        $nfitem = SQL::ini(NfItemQuery::buscaNfitem(), [
            'idnf' => $_idnf,
            'idpessoa' => $idpessoa,
            'codigoevento' => $codigoevento
        ])::exec();

        if ($nfitem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $nfitem->errorMessage());
            return [];
        }

        return $nfitem->data[0];
    }

	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE ----	
	//----- AUTOCOMPLETE ----	

	// ----- Variáveis de apoio -----
	// ----- Variáveis de apoio -----
}
?>
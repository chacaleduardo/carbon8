<?

require_once(__DIR__ . "/_controller.php");
require_once(__DIR__ . "/nfvolume_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/pedido_query.php");
require_once(__DIR__ . "/../querys/empresaemails_query.php");
require_once(__DIR__ . "/../querys/formapagamento_query.php");
require_once(__DIR__ . "/../querys/log_query.php");
require_once(__DIR__ . "/../querys/_modulo_query.php");
require_once(__DIR__ . "/../querys/mailfila_query.php");
require_once(__DIR__ . "/../querys/tipoprodserv_query.php");
require_once(__DIR__ . "/../querys/contapagar_query.php");
require_once(__DIR__ . "/../querys/remessa_query.php");
require_once(__DIR__ . "/../querys/nfitem_query.php");
require_once(__DIR__ . "/../querys/lote_query.php");
require_once(__DIR__ . "/../querys/aliqicms_query.php");
require_once(__DIR__ . "/../querys/natop_query.php");
require_once(__DIR__ . "/../querys/plantel_query.php");
require_once(__DIR__ . "/../querys/prodservformula_query.php");
require_once(__DIR__ . "/../querys/unidade_query.php");
require_once(__DIR__ . "/../querys/lotecons_query.php");
require_once(__DIR__ . "/../querys/nfitemcomissao_query.php");
require_once(__DIR__ . "/../querys/nf_query.php");
require_once(__DIR__ . "/../querys/nfconfpagar_query.php");
require_once(__DIR__ . "/../querys/prodservforn_query.php");
require_once(__DIR__ . "/../querys/confcontapagar_query.php");
require_once(__DIR__ . "/../querys/carimbo_query.php");
require_once(__DIR__ . "/../querys/nfplote_query.php");

// CONTROLLERS
require_once(__DIR__ . "/pessoa_controller.php");
require_once(__DIR__ . "/natop_controller.php");
require_once(__DIR__ . "/prodserv_controller.php");
require_once(__DIR__ . "/contapagar_controller.php");
require_once(__DIR__ . "/contrato_controller.php");
require_once(__DIR__ . "/empresa_controller.php");
require_once(__DIR__ . "/endereco_controller.php");
require_once(__DIR__ . "/lote_controller.php");
require_once(__DIR__ . "/nfentrada_controller.php");
require_once(__DIR__ . "/tag_controller.php");
require_once(__DIR__ . "/prodservformula_controller.php");
require_once(__DIR__ . "/solfab_controller.php");
require_once(__DIR__ . "/formapagamento_controller.php");

class PedidoController  extends Controller
{
    public static function gerarComissao($idnf, $idempresa)
    {
        return NfVolumeController::gerarComissao($idnf, $idempresa);
    }

    public static function buscarPreferenciaCliente($idpessoa, $idempresa = null)
    {
        return PessoaController::buscarPreferenciaCliente($idpessoa, $idempresa);
    }

    public static function listarClietenPedidoPorIdTipoPessoa($idtipopessoa)
    {
        $pessoaPorTipo =  PessoaController::listarClietenPedidoPorIdTipoPessoa($idtipopessoa);
        foreach ($pessoaPorTipo as $_pessoaPorTipo) {
            $listaPessoa[$_pessoaPorTipo['idpessoa']]['nome'] = $_pessoaPorTipo['nome'];
            $listaPessoa[$_pessoaPorTipo['idpessoa']]['tipo'] = $_pessoaPorTipo['tipo'];
        }
        return $listaPessoa;
    }

    public static function buscarClientePedidoPorIdPessoa($idPessoa)
    {
        return PessoaController::buscarClientePedidoPorIdPessoa($idPessoa);
    }

    public static function listarNatopPorEmpresa()
    {
        $natop =  NatopController::listarNatopPorEmpresa();
        $listaNatop = array();
        foreach ($natop as $_natop) {
            $listaNatop[$_natop['idnatop']]['natop'] = $_natop['natop'];
            $listaNatop[$_natop['idnatop']]['finnfe'] = $_natop['finnfe'];
        }
        return $listaNatop;
    }

    public static function buscarProdutoSaida($idpessoa)
    {
        $arrProd =  ProdservController::buscarProdutoSaida($idpessoa);
        $i = 0;
        foreach ($arrProd as $_Prod) {
            $listaNatop[$i]['value'] = $_Prod['idprodserv'];
            $listaNatop[$i]['label'] = $_Prod['descr'];
            $i++;
        }
        return $listaNatop;
    }

    public static function buscarCreditoVencidoPorPessoa($idpessoa)
    {
        $arrContapagar =  ContapagarController::buscarCreditoVencidoPorPessoa($idpessoa);
        $i = 0;
        foreach ($arrContapagar as $_arrContapagar) {
            $lista[$i] = $_arrContapagar['idcontapagar'];
            $i++;
        }
        return $lista;
    }

    public static function listarContratoPorPessoa($idpessoa)
    {
        return ContratoController::listarContratoPorPessoa($idpessoa);
    }

    public static function buscarEmpresaFilial($idpessoa)
    {
        return EmpresaController::buscarEmpresaFilial($idpessoa);
    }
    public static function buscarFilial()
    {
        return EmpresaController::buscarFilial();
    }


    public static function listarPedidoVinculado($idnf)
    {

        $results = SQL::ini(PedidoQuery::listarPedidoVinculado(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_campo) {

                $lista[$_campo['idnf']]['nnfe'] = $_campo['nnfe'];
                $lista[$_campo['idnf']]['tiponf']  = $_campo['tiponf'];
            }
            return $lista;
        }
    }

    public static function buscarContatoPessoa($idpessoa)
    {
        return PessoaController::buscarContatoPessoa($idpessoa);
    }

    public static function buscarResponavelCliente($idpessoa)
    {
        return PessoaController::buscarResponavelCliente($idpessoa);
    }

    public static function buscarEmailOrcamentoProduto($idempresa)
    {

        $results = SQL::ini(EmpresaEmailsQuery::buscarEmailOrcamentoProduto(), [
            "idempresa" => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return (count($results->data) > 0) ? $results->data : "";
        }
    }
    public static function buscarEmailOrcamentoProdutoPorNf($idempresa, $idnf)
    {

        $results = SQL::ini(EmpresaEmailsQuery::buscarEmailOrcamentoProdutoPorNf(), [
            "idempresa" => $idempresa,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return (count($results->data) > 0) ? $results->data : "";
        }
    }


    public static function buscarEmailfilaPorNf($idnf)
    {

        $results = SQL::ini(EmpresaEmailsQuery::buscarEmailfilaPorNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return (count($results->data) > 0) ? $results->data[0] : "";
        }
    }


    public static function buscarlog($idobjeto, $tipoobjeto, $tipolog)
    {
        $results = SQL::ini(LogQuery::buscarlog(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "tipolog" => $tipolog
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return (count($results->data) > 0) ? $results->data : "";
        }
    }

    public static function buscarDominio($idempresa, $tipo = NULL)
    {
        if (!empty($tipo)) {
            $and = "AND em.tipoenvio = '$tipo'";
        } else {
            $and = "";
        }
        $results = SQL::ini(EmpresaEmailsQuery::buscarDominioPorTipoenvio(), [
            "idempresa" => $idempresa,
            "and" => $and
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return (count($results->data) > 0) ? $results->data : "";
        }
    }
    public static function buscarNfitemGnre($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarNfitemGnre(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarEnderecoFaturamentoPorPessoa($idpessoa)
    {
        return EnderecoController::buscarEnderecoFaturamentoPorPessoa($idpessoa);
    }

    public static function buscarPessoa($idpessoa)
    {
        return PessoaController::buscarPessoa($idpessoa);
    }

    public static function listarEnderecoFaturamentoPorPessoa($idpessoa)
    {
        return EnderecoController::listarEnderecoFaturamentoPorPessoa($idpessoa);
    }

    public static function listarEnderecoFaturamentoPorId($idendereco)
    {
        return EnderecoController::listarEnderecoFaturamentoPorId($idendereco);
    }

    public static function buscarEnderecoFaturamentoPorId($idendereco)
    {
        return EnderecoController::buscarEnderecoFaturamentoPorId($idendereco);
    }


    public static function listarEnderecoPessoaPorTipo($idpessoa, $idtipoendereco)
    {
        return EnderecoController::listarEnderecoPessoaPorTipo($idpessoa, $idtipoendereco);
    }

    public static function buscarEnderecoPorIdEndereco($idendereco)
    {
        return EnderecoController::buscarEnderecoPorIdEndereco($idendereco);
    }

    public static function buscarNfitemPedido($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarNfitemPedido(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return  $results->data;
        }
    }
    public static function buscarListaCFOPporNatop($idnatop)
    {
        $results = SQL::ini(PedidoQuery::buscarListaCFOPporNatop(), [
            "idnatop" => $idnatop
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return  $results->data[0];
        }
    }

    public static function buscarTpnf()
    {
        $results = SQL::ini(PedidoQuery::buscarTpnf())::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['id']] = $_valor['valor'];
            }
            return $lista;
        }
    }

    public static function buscarMoeda()
    {

        $results = SQL::ini(PedidoQuery::buscarMoeda())::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['id']] = $_valor['valor'];
            }
            return $lista;
        }
    }

    public static function buscarNfentradaPorIdnfe($refnfe)
    {
        $results = SQL::ini(PedidoQuery::buscarNfentradaPorIdnfe(), [
            "refnfe" => $refnfe
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfdevolucaoPoridnfe($refnfe)
    {
        $results = SQL::ini(PedidoQuery::buscarNfdevolucaoPoridnfe(), [
            "refnfe" => $refnfe
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfitemMoedaEstrangeira($idnf)
    {

        $results = SQL::ini(PedidoQuery::buscarNfitemMoedaEstrangeira(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarLoteNfitemPorIdnf($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarLoteNfitemPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarValorContatoProdutoFomulado($idpessoa, $idprodserv, $idprodservformula)
    {
        return ContratoController::buscarValorContatoProdutoFomulado($idpessoa, $idprodserv, $idprodservformula);
    }

    public static function buscarDescontoContratoPorProduto($idpessoa, $idprodserv)
    {
        return ContratoController::buscarDescontoContratoPorProduto($idpessoa, $idprodserv);
    }



    public static function buscarValorProdutoFormulado($idprodservformula)
    {
        return ProdservController::buscarValorProdutoFormulado($idprodservformula);
    }

    public static function buscarLotePorNfitem($idnfitem)
    {
        $results = SQL::ini(PedidoQuery::buscarLotePorNfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarReservaLotePorNfitem($idnfitem)
    {
        return LoteController::buscarReservaLotePorNfitem($idnfitem);
    }

    public static function buscarLoteLoteativ($partida, $exercicio)
    {
        return LoteController::buscarLoteLoteativ($partida, $exercicio);
    }

    public static function buscarLoteAnaliseLote($partida, $exercicio)
    {
        return LoteController::buscarLoteAnaliseLote($partida, $exercicio);
    }

    public static function buscarPartidaLote($idlote)
    {
        return LoteController::buscarPartidaLote($idlote);
    }

    public static function buscarValorVendaFormula($idprodservformula)
    {
        return ProdservformulaController::buscarValorVendaFormula($idprodservformula);
    }

    public static function buscarFormulaPorProdserv($idprodserv)
    {
        return ProdservformulaController::buscarFormulaPorProdserv($idprodserv);
    }

    public static function buscarFormulaAtivaPorProdserv($idprodserv)
    {
        return ProdservformulaController::buscarFormulaAtivaPorProdserv($idprodserv);
    }

    public static function buscarRotuloFormulaPorId($idprodservformula)
    {
        return ProdservformulaController::buscarRotuloFormulaPorId($idprodservformula);
    }

    public static function bucarEnderecoPorId($idendereco)
    {
        return EnderecoController::bucarEnderecoPorId($idendereco);
    }

    public static function BuscarCfopPorOrigem($origem)
    {
        $results = SQL::ini(PedidoQuery::BuscarCfopPorOrigem(), [
            "origem" => $origem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['id']] = $_valor['cfop'];
            }
            return $lista;
        }
    }

    public static function BuscarNfitemImportacao($idnfitem)
    {
        $results = SQL::ini(PedidoQuery::BuscarNfitemImportacao(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function BuscarComissaoPorIdnfitem($idnfitem)
    {
        $results = SQL::ini(PedidoQuery::BuscarComissaoPorIdnfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function BuscarReservaConsumoLotePorIdnfitem($idnfitem)
    {
        $results = SQL::ini(PedidoQuery::BuscarReservaConsumoLotePorIdnfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarConsumoNfitemSelecionadosPorIdNf($idnf)
    {
        $results = SQL::ini(PedidoQuery::BuscarConsumoNfitemSelecionadosPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarModuloPorIdlote($idlote)
    {
        return LoteController::buscarModuloPorIdlote($idlote);
    }

    public static function BuscarItensPedido($idnfitem, $especial, $idpessoa, $tpnf, $idprodservformula, $status, $idempresafat, $loteproducao, $finnfe = null)
    {

        if ($especial == 'Y') {
            $strlp = " and l.idpessoa=" . $idpessoa . "  ";
            if ($tpnf == 1) {
                $strest = " AND l.status not in ('REPROVADO') and fr.status='DISPONIVEL' ";
            } else {
                $strest = " and l.exercicio >= year(now())-1 ";
            }
        } else {
            $strlp = " ";
            if ($tpnf == 1) {
                $strest = " AND l.status not in ('REPROVADO') AND fr.status='DISPONIVEL'  ";
                //$strest = " AND l.status  in ('APROVADO','LIBERADO','QUARENTENA') AND fr.status='DISPONIVEL'  ";
            } else {
                $strest = " and l.exercicio >= year(now())-1 ";
            }
        }
        if (!empty($idprodservformula)) {
            $strform = " and (l.idprodservformula = " . $idprodservformula . " or l.idprodservformula is null) ";
        } else {
            $strform = "";
        }

        if ($finnfe == 4) {
            $intipounidade = '21,3';
        } else {
            $intipounidade = '21';
        }

        if ($status == 'FATURAR') {
            $struni = " (u.idtipounidade in (" . $intipounidade . ") )"; //expedicao so logistica
        } elseif ($loteproducao == 'N') {
            $struni = " (u.idtipounidade in (" . $intipounidade . ") )"; //expedicao so logistica
        } else {
            $struni = " (u.idtipounidade in (" . $intipounidade . ") or u.idtipounidade=5)"; //demais almoxarifado e producao
        }

        if (!empty($idempresafat)) {
            $stidempresa = " and fr.idempresa in (" . cb::idempresa() . "," . $idempresafat . ")";
        } else {
            $stidempresa = " and fr.idempresa = " . cb::idempresa();
        }

        $results = SQL::ini(PedidoQuery::BuscarItensPedido(), [
            "idnfitem" => $idnfitem,
            "strlp" => $strlp,
            "strest" => $strest,
            "strform" => $strform,
            "struni" => $struni,
            "idempresa" => $stidempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarLotepedido($idnfitem)
    {
        $results = SQL::ini(PedidoQuery::buscarLotepedido(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarConsumoLotePedido($idnfitem, $idlote, $idlotefracao)
    {
        $results = SQL::ini(PedidoQuery::buscarConsumoLotePedido(), [
            "idnfitem" => $idnfitem,
            "idlote" => $idlote,
            "idlotefracao" => $idlotefracao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarLoteReservaPedido($idlote)
    {
        $results = SQL::ini(PedidoQuery::buscarLoteReservaPedido(), [
            "idlote" => $idlote
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarLoteReservaPorIdnfitem($idlote, $idnfitem, $inidlotereserva)
    {
        $results = SQL::ini(PedidoQuery::buscarLoteReservaPorIdnfitem(), [
            "idlote" => $idlote,
            "idnfitem" => $idnfitem,
            "inidlotereserva" => $inidlotereserva
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscaFormalizacaoLote($idlote)
    {
        return LoteController::buscaFormalizacaoLote($idlote);
    }

    public static function buscarTagTagdim($idobjeto)
    {
        return TagController::buscarTagTagdim($idobjeto);
    }

    public static function buscarLotelocalizacao($idlote)
    {
        return LoteController::buscarLotelocalizacao($idlote);
    }

    public static function buscarRotuloFormula($idprodservformula)
    {
        $results = SQL::ini(PedidoQuery::buscarRotuloFormula(), [
            "idprodservformula" => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }


    public static function buscarSolfabPorIds($stridsf)
    {
        return SolfabController::buscarSolfabPorIds($stridsf);
    }


    public static function buscarProservVendaMaterial()
    {
        return ProdservController::buscarProservVendaMaterial();
    }

    public static function buscarUnidadeVolume()
    {
        return ProdservController::buscarUnidadeVolume();
    }

    public static function buscarFinalidadeProdserv()
    {
        return ProdservController::$finalidade;
    }

    public static function buscarOrigemProdserv()
    {
        return ProdservController::$origem;
    }

    public static function buscarSTProdserv()
    {
        return ProdservController::$cst;
    }

    public static function buscarModbcProdserv()
    {
        return ProdservController::$modbc;
    }

    public static function buscarCstPisProdserv()
    {
        return ProdservController::$pisConfins;
    }

    public static function buscarCofinsPorNfitem($idnfitem)
    {
        $results = SQL::ini(PedidoQuery::buscarCofinsPorNfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarCstIpiProdserv()
    {
        return ProdservController::$ipi;
    }

    public static function buscarCstCofinsProdserv()
    {
        return ProdservController::$pisConfins;
    }

    public static function buscarPisPorNfitem($idnfitem)
    {
        $results = SQL::ini(PedidoQuery::buscarPisPorNfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFinnfePorNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarFinnfePorNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static $tipoFrete = array('0' => 'CIF', '1' => 'FOB', '2' => 'TER', '3' => 'TP REM', '4' => 'TP DEST', '9' => 'SEM FRETE');

    public static $varSimNao = array('Y' => 'Sim', 'N' => 'Não');

    public static $strObsFrete = "
    0=Contratação do Frete por Conta do Remetente (CIF);
    1=Contratação do Frete por Conta do Destinatário (FOB);
    2=Contratação do Frete por Conta de Terceiros (TER);
    3=Transporte Próprio por conta do Remetente(TP REM);
    4=Transporte Próprio por conta do Destinatário (TP DEST);
    9=Sem Ocorrência de Transporte (SEM FRETE);";

    public static function buscarMoedaNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarMoedaNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarInfEmpresaNF($idempresa)
    {
        $results = SQL::ini(PedidoQuery::buscarInfEmpresaNF(), [
            "idempresa" => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFormapagamentoPorEmpresa($idempresa)
    {
        $results = SQL::ini(PedidoQuery::buscarFormapagamentoPorEmpresa(), [
            "idempresa" => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idformapagamento']] = $_valor['descricao'];
            }
            return $lista;
        }
    }


    public static $parcelasNF = array(
        '1' => '1x',
        '2' => '2x',
        '3' => '3x',
        '4' => '4x',
        '5' => '5x',
        '6' => '6x',
        '7' => '7x',
        '8' => '8x',
        '9' => '9x',
        '10' => '10x',
        '11' => '11x',
        '12' => '12x',
        '13' => '13x',
        '14' => '14x',
        '15' => '15x',
        '16' => '16x',
        '17' => '17x',
        '18' => '18x',
        '19' => '19x',
        '20' => '20x',
        '21' => '21x',
        '22' => '22x',
        '23' => '23x',
        '24' => '24x',
        '25' => '25x',
        '26' => '26x',
        '27' => '27x',
        '28' => '28x',
        '29' => '29x',
        '30' => '30x',
        '31' => '31x',
        '32' => '32x',
        '33' => '33x',
        '34' => '34x',
        '35' => '35x',
        '36' => '36x',
        '37' => '37x',
        '38' => '38x',
        '39' => '39x',
        '40' => '40x',
        '41' => '41x',
        '42' => '42x',
        '43' => '43x',
        '44' => '44x',
        '45' => '45x',
        '46' => '46x',
        '47' => '47x',
        '48' => '48x',
        '49' => '49x',
        '50' => '50x',
        '51' => '51x',
        '52' => '52x',
        '53' => '53x',
        '54' => '54x',
        '55' => '55x',
        '56' => '56x',
        '57' => '57x',
        '58' => '58x',
        '59' => '59x',
        '60' => '60x'
    );

    public static function buscarConfpagarPorNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarConfpagarPorNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function retornaDiaSemanaPorData($vdata)
    {
        $results = SQL::ini(PedidoQuery::retornaDiaSemanaPorData(), [
            "vdata" => $vdata
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFormapagamentoAgenciaPorFormapagamento($idformapagamento)
    {
        return FormaPagamentoController::buscarFormapagamentoAgenciaPorFormapagamento($idformapagamento);
    }
    public static function buscarStPorNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarStPorNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function  buscarEnderecoPessoaPorTipo($idpessoa, $idtipoendereco)
    {
        return EnderecoController::buscarEnderecoPessoaPorTipo($idpessoa, $idtipoendereco);
    }

    public static function buscarNfcorrecaoPorIdnf($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarNfcorrecaoPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }


    public static function  buscarUfBr()
    {
        return EnderecoController::$ufBr;
    }

    public static function buscarTransportadorPorIdpessoa($idPessoa)
    {
        return PessoaController::buscarTransportadorPorIdpessoa($idPessoa);
    }

    public static function listarTransportadora()
    {
        return PessoaController::listarTransportadora();
    }

    public static function buscarRotaUfPorTransportadora($idpessoa, $uf, $codcidade)
    {
        $results = SQL::ini(PedidoQuery::buscarRotaUfPorTransportadora(), [
            "idpessoa" => $idpessoa,
            "uf" => $uf,
            "codcidade" => $codcidade
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarRotaPorEndereco($idendereco, $idpessoa)
    {
        return EnderecoController::buscarRotaPorEndereco($idendereco, $idpessoa);
    }

    public static function buscarCtePedidoPorIdOBS($idnfe, $idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarCtePedidoPorIdOBS(), [
            "idnfe" => $idnfe,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarCtePedidoPorId($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarCtePedidoPorId(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }
    public static function buscarNfVinculadaPorId($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarNfVinculadaPorId(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }


    public static function buscarEmailOrcamentoProdutoServicoPorEmpresa()
    {
        $results = SQL::ini(EmpresaemailsQuery::buscarEmailOrcamentoProdutoServicoPorEmpresa(), [
            "idempresa" => getidempresa('idempresa', 'empresa')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarEmailFilaNfPorId($idnf)
    {
        $results = SQL::ini(MailFilaQuery::buscarEmailFilaNfPorId(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFaturaBoletoPorNf($idnf)
    {
        $results = SQL::ini(ContapagarQuery::buscarFaturaBoletoPorNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarFormapagamentoCreditoPornota()
    {
        $results = SQL::ini(formapagamentoQuery::buscarFormapagamentoCreditoPornota(), [
            "idempresa" => getidempresa('idempresa', 'formapagamento')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idformapagamento']] = $_valor['descricao'];
            }
            return $lista;
        }
    }

    public static function buscarPessoaEmailNfePorId($idpessoa)
    {
        $results = SQL::ini(PessoaQuery::buscarPessoaEmailNfePorId(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }


    public static function buscarPessoaEmailNfeCc($idpessoa)
    {
        $results = SQL::ini(PessoaQuery::buscarPessoaEmailNfeCc(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarPessoaEmailMaterialNfe($idpessoa)
    {
        $results = SQL::ini(PessoaQuery::buscarPessoaEmailMaterialNfe(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarEmpresaemailobjPorTipoId($idnf, $tipo)
    {
        $results = SQL::ini(EmpresaemailsQuery::buscarEmpresaemailobjPorTipoId(), [
            "idnf" => $idnf,
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarEmpresaemailobjPorTipoIdempresa($idempresa, $tipo)
    {
        $results = SQL::ini(EmpresaemailsQuery::buscarEmpresaemailobjPorTipoIdempresa(), [
            "idempresa" => $idempresa,
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarTotalCofinsNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarTotalCofinsNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarTotalPisNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarTotalPisNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarImpostosGNENf($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarImpostosGNENf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarImpostosNf($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarImpostosNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }


    public static function buscarProdservTipoProdServ()
    {


        $results = SQL::ini(TipoProdServQuery::buscarProdservTipoProdServ(), [
            "idempresa" => getidempresa('idempresa', 'contaitem')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idtipoprodserv']] = $_valor['tipoprodserv'];
            }
            return $lista;
        }
    }

    public static function buscarContaItemTipoProdservTipoProdServ($idcontaitem)
    {
        $results = SQL::ini(TipoProdServQuery::buscarContaItemTipoProdservTipoProdServ(), ["idcontaitem" => $idcontaitem, "idempresa" => getidempresa('t.idempresa', 'contaitem')])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idtipoprodserv']] = $_valor['tipoprodserv'];
            }
            return $lista;
        }
    }

    public static $ArrayVazio = array('' => '');

    public static function buscarContapagaritemPorNf($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarContapagaritemPorNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarParcelasSemComissao($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarParcelasSemComissao(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarFaturaPorId($idcontapagar)
    {
        $results = SQL::ini(ContaPagarQuery::buscarFaturaPorId(), [
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarRemessaPorIdcontapagar($idcontapagar)
    {
        $results = SQL::ini(RemessaQuery::buscarRemessaPorIdcontapagar(), [
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFormapagamentoAgrupadoPorEmpresa()
    {

        $results = SQL::ini(FormapagamentoQuery::buscarFormapagamentoAgrupadoPorEmpresa(), [
            "idempresa" => cb::idempresa()
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idformapagamento']] = $_valor['descricao'];
            }
            return $lista;
        }
    }

    public static function buscarHistoricoStatusPedidoPorIdcontapagar($pagvalmodulo, $idcontapagar, $idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarHistoricoStatusPedidoPorIdcontapagar(), [
            "pagvalmodulo" => $pagvalmodulo,
            "idcontapagar" => $idcontapagar,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarFaturaPorPedido($idnf)
    {
        $results = SQL::ini(ContapagarQuery::buscarFaturaPorPedido(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }


    public static function buscarRestaurarPorIdlp($idlp)
    {
        $results = SQL::ini(_ModuloQuery::buscarRestaurarPorIdlp(), [
            "idlp" => $idlp
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarPessoasComissaoNf($idnf)
    {
        $results = SQL::ini(NfItemComissaoQuery::buscarPessoasComissaoNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarComissaoPorIdnf($idnf, $inidpessoa = null)
    {
        if (!empty($inidpessoa)) {
            $stridpessoa = ' and p.idpessoa in (' . $inidpessoa . ')';
        } else {
            $stridpessoa = ' ';
        }

        $results = SQL::ini(ContapagarQuery::buscarComissaoPorIdnf(), [
            "idnf" => $idnf,
            "stridpessoa" => $stridpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarTotalComissaoPorNf($idnf, $inidpessoa = null)
    {

        if (!empty($inidpessoa)) {
            $stridpessoa = ' and p.idpessoa in (' . $inidpessoa . ')';
        } else {
            $stridpessoa = ' ';
        }

        $results = SQL::ini(ContapagarQuery::buscarTotalComissaoPorNf(), [
            "idnf" => $idnf,
            "stridpessoa" => $stridpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfitemPorIdnfitem($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdnfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarReservaNfitemPorIdnf($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarReservaNfitemPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }



    public static function verificarLoteReservadoDisponivel($idlote)
    {
        $results = SQL::ini(LoteQuery::verificarLoteReservadoDisponivel(), [
            "idlote" => $idlote
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function liberarLotereservaPorId($idlotereserva)
    {
        $results = SQL::ini(LoteQuery::liberarLotereservaPorId(), [
            "idlotereserva" => $idlotereserva
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }


    public static function deletarNfitemComissaoPorId($idnfitem)
    {
        $results = SQL::ini(NfitemQuery::deletarNfitemComissaoPorId(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarConfPedidoFatPorIdnf($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarConfPedidoFatPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarAliqicmsUF($uf)
    {
        $results = SQL::ini(AliqicmsQuery::buscarAliqicmsUF(), [
            "uf" => $uf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarAliqicmsPorId($idaliqicms)
    {
        $results = SQL::ini(AliqicmsQuery::buscarAliqicmsPorId(), [
            "idaliqicms" => $idaliqicms
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarCfopPorNatop($idnatop, $origem)
    {
        $results = SQL::ini(NatopQuery::buscarCfopPorNatop(), [
            "idnatop" => $idnatop,
            "origem" => $origem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarCfopPorIdprodserv($origem, $idprodserv, $cfop)
    {
        $results = SQL::ini(ProdservQuery::buscarCfopPorIdprodserv(), [
            "idprodserv" => $idprodserv,
            "cfop" => $cfop,
            "origem" => $origem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarInfoProdserv($idprodserv)
    {

        $results = SQL::ini(ProdservQuery::buscarInfoProdserv(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarComissaoContatoProduto($idprodserv, $idpessoa)
    {
        $results = SQL::ini(ContratoQuery::buscarComissaoContatoProduto(), [
            "idprodserv" => $idprodserv,
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarResponsavelComissao($idpessoa)
    {
        $results = SQL::ini(PessoaQuery::buscarResponavelCliente(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarPlantelPessoa($idpessoa)
    {
        $results = SQL::ini(PlantelQuery::buscarPlantelPessoa(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarDivisaoPlantel($idprodserv, $idplantel)
    {
        $results = SQL::ini(PlantelQuery::buscarDivisaoPlantel(), [
            "idprodserv" => $idprodserv,
            "idplantel" => $idplantel
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarResponavelClienteComissaoProd($idpessoa)
    {
        $results = SQL::ini(PessoaQuery::buscarResponavelClienteComissaoProd(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function atualizaCollapseNfitem($tipo, $idnf)
    {
        $results = SQL::ini(PedidoQuery::atualizaCollapseNfitem(), [
            "tipo" => $tipo,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarUnidadePorIdtipoIdempresa($idtipounidade, $idempresa)
    {
        $results = SQL::ini(UnidadeQuery::buscarUnidadePorIdtipoIdempresa(), [
            "idtipounidade" => $idtipounidade,
            "idempresa" => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarLotesNaoConsumidosPedido($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarLotesNaoConsumidosPedido(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function deletaLoteconsPorId($idlotecons)
    {
        $results = SQL::ini(LoteconsQuery::deletaLoteconsPorId(), [
            "idlotecons" => $idlotecons
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function atualizaInfCorrecaoNF($correcao, $idnf)
    {
        $results = SQL::ini(PedidoQuery::atualizaInfCorrecaoNF(), [
            "correcao" => $correcao,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function deletarNfitemComissao($idnfitem)
    {
        $results = SQL::ini(NfitemComissaoQuery::deletarNfitemComissao(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }
    public static function buscarNfPorId($idnf)
    {
        $results = SQL::ini(NfQuery::buscarNfPorId(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    public static function buscarInfCteNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarInfCteNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarProporcaoNfConfPagar($idnf)
    {

        $results = SQL::ini(NfConfPagar::atualizarProporcaoNfConfPagar(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    public static function atualizarDatarecebNfConfPagar($idnf)
    {
        $results = SQL::ini(NfConfPagar::atualizarDatarecebNfConfPagar(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function apagarNfConfPagar($idnfconfpagar)
    {
        $results = SQL::ini(NfConfPagar::apagarNfConfPagar(), [
            "idnfconfpagar" => $idnfconfpagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarIdNfConfPagar($idnf)
    {
        $results = SQL::ini(NfConfPagar::buscarIdNfConfPagar(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function ArrayNfitemPorIdnfitemArray($idnfitem)
    {

        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdnfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $arrret = [];
            foreach ($results->data as $_nfitem) {
                foreach ($_nfitem as $_col => $_valor) {
                    $arrret[$_nfitem['idnfitem']][$_col] = $_valor;
                }
            }

            return $arrret;
        }
    }


    public static function ArrayNfPorId($idnf)
    {

        $results = SQL::ini(NfQuery::buscarNfPorId(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $arrret = [];
            foreach ($results->data as $_nf) {
                foreach ($_nf as $_col => $_valor) {
                    $arrret[$_nf['idnf']][$_col] = $_valor;
                }
            }

            return $arrret;
        }
    }


    public static function atualizaNftransferencia($idnf, $status)
    {
        $results = SQL::ini(NfQuery::atualizaNftransferencia(), [
            "idnf" => $idnf,
            "status" => $status
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarNfitemComConsumo($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemComConsumo(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $arrret = [];
            foreach ($results->data as $_nfitem) {
                foreach ($_nfitem as $_col => $_valor) {
                    $arrret[$_nfitem['idnfitem']][$_col] = $_valor;
                }
            }

            return $arrret;
        }
    }

    public static function buscarConsumoLotecons($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(LoteconsQuery::buscarConsumoLotecons(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $arrret = [];
            foreach ($results->data as $_lotecons) {
                foreach ($_lotecons as $_col => $_valor) {
                    $arrret[$_lotecons['idlotecons']][$_col] = $_valor;
                }
            }

            return $arrret;
        }
    }

    public static function buscarProdservfornPorIdprodservIdnf($idprodserv, $idnf)
    {
        $results = SQL::ini(ProdservfornQuery::buscarProdservfornPorIdprodservIdnf(), [
            "idprodserv" => $idprodserv,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }




    public static function buscarProdservfornPorId($idprodservforn)
    {
        $results = SQL::ini(ProdservfornQuery::buscarProdservfornPorId(), [
            "idprodservforn" => $idprodservforn
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarLotePorIdprodservIdunidade($idunidade, $idprodserv)
    {
        $results = SQL::ini(LoteQuery::buscarLotePorIdprodservIdunidade(), [
            "idprodserv" => $idprodserv,
            "idunidade" => $idunidade
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarLotefracaoPorIdloteIdunidade($idlote, $idunidade)
    {
        $results = SQL::ini(LotefracaoQuery::buscarLotefracaoPorIdloteIdunidade(), [
            "idlote" => $idlote,
            "idunidade" => $idunidade
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfitemDanfe($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemDanfe(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    public static function atualizaLoteSolfab($idsolfab, $idlote)
    {
        $results = SQL::ini(LoteQuery::atualizaLoteSolfab(), [
            "idsolfab" => $idsolfab,
            "idlote" => $idlote
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    public static function buscarConfpagarComissao($idformapagamento)
    {
        $results = SQL::ini(PedidoQuery::buscarConfpagarComissao(), [
            "idformapagamento" => $idformapagamento
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarInfFormapagamentoPorId($idformapagamento)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarInfFormapagamentoPorId(), [
            "idformapagamento" => $idformapagamento
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function atualizaDataEntregaCte($entrega, $idnfepedido)
    {
        $results = SQL::ini(PedidoQuery::atualizaDataEntregaCte(), [
            "idnfepedido" => $idnfepedido,
            "entrega" => $entrega
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function atualizaDataEntregaCtePorIdnf($entrega, $idnfepedido)
    {
        $results = SQL::ini(PedidoQuery::atualizaDataEntregaCtePorIdnf(), [
            "idnfepedido" => $idnfepedido,
            "entrega" => $entrega
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfconfpagarPorIdnf($idnf)
    {
        $results = SQL::ini(NfConfPagar::buscarNfconfpagarPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function somarFretePorIdnf($idnf)
    {
        $results = SQL::ini(NfItemQuery::somarFretePorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function somarProporcaoNfconfpagarPorIdnf($idnf)
    {
        $results = SQL::ini(NfConfPagar::somarProporcaoNfconfpagarPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static  function buscarNfitemPorIdobjetoTipoobjeto($idobjetoitem, $tipoobjetoitem)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdobjetoTipoobjeto(), [
            "idobjetoitem" => $idobjetoitem,
            "tipoobjetoitem" => $tipoobjetoitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarConfcontapagarInpostoServico($idconfcontapagar)
    {
        $results = SQL::ini(ConfcontapagarQuery::buscarConfcontapagarInpostoServico(), [
            "idconfcontapagar" => $idconfcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function deletarNfitemPorId($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::deletarNfitemPorId(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static  function buscarNfitemPorIdobjetoTipoobjetoIdconfcontapagar($idobjetoitem, $tipoobjetoitem, $idconfcontapagar)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdobjetoTipoobjetoIdconfcontapagar(), [
            "idobjetoitem" => $idobjetoitem,
            "tipoobjetoitem" => $tipoobjetoitem,
            "idconfcontapagar" => $idconfcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }


    public static function buscarDataVencimentoNoMes($dtemissao, $diavenc)
    {
        $results = SQL::ini(PedidoQuery::buscarDataVencimentoNoMes(), [
            "dtemissao" => $dtemissao,
            "diavenc" => $diavenc
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarDataVencimentoNoMesSequinte($dtemissao, $diavenc)
    {
        $results = SQL::ini(PedidoQuery::buscarDataVencimentoNoMesSequinte(), [
            "dtemissao" => $dtemissao,
            "diavenc" => $diavenc
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarNfitemImposto($dataitem, $prodservdescr, $total, $idnfitem)
    {

        $results = SQL::ini(NfItemQuery::atualizarNfitemImposto(), [
            "dataitem" => $dataitem,
            "prodservdescr" => $prodservdescr,
            "total" => $total,
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarNfitemValorFrete($frete, $idnf)
    {
        $results = SQL::ini(NfItemQuery::atualizarNfitemValorFrete(), [
            "frete" => $frete,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarNfValorFrete($frete, $idnf)
    {
        $results = SQL::ini(NfQuery::atualizarNfValorFrete(), [
            "frete" => $frete,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarQuantidadeParcelasPorStatusTipoObjeto($status, $tipoobjeto, $idobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarQuantidadeParcelasPorStatusTipoObjeto(), [
            "status" => $status,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarSeExisteItemFaturaPorObj($tipoobjeto, $idobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarSeExisteItemFaturaPorObj(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function  verificarSeExisteBoletoPorIdnf($idnf)
    {
        $results = SQL::ini(PedidoQuery::verificarSeExisteBoletoPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function verificarSeExisteParcelaQuitada($idnf)
    {

        $results = SQL::ini(ContaPagarItemQuery::verificarSeExisteParcelaQuitada(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function ArrayNfitemVendaPorIdnfArray($idnf)
    {

        $results = SQL::ini(NfItemQuery::buscarNfitemVendaPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $arrret = [];
            foreach ($results->data as $_nfitem) {
                foreach ($_nfitem as $_col => $_valor) {
                    $arrret[$_nfitem['idnfitem']][$_col] = $_valor;
                }
            }

            return $arrret;
        }
    }

    public static function buscarContapagarProgramadaPorIdobjeto($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarContapagarProgramadaPorIdobjeto(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarContapagaritemProgramadaPorIdobjeto($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarContapagaritemProgramadaPorIdobjeto(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarContapagaritemComissaoProgramadaPorIdobjeto($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarContapagaritemComissaoProgramadaPorIdobjeto(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function verificarSeExisteParcelaInStatusPorIdobjeto($idobjetoorigem, $instatus, $tipoobjetoorigem)
    {
        $results = SQL::ini(ContaPagarItemQuery::verificarSeExisteParcelaInStatusPorIdobjeto(), [
            "instatus" => $instatus,
            "idobjetoorigem" => $idobjetoorigem,
            "tipoobjetoorigem" => $tipoobjetoorigem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function deletaParcelaComissaoPendentePorIdobjeto($idobjeto, $tipoobjeto)
    {

        $results = SQL::ini(ContaPagarItemQuery::deletaParcelaComissaoPendentePorIdobjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }


    public static function deletaParcelaImpostoPendentePorIdobjeto($idobjeto, $tipoobjeto)
    {

        $results = SQL::ini(ContaPagarItemQuery::deletaParcelaImpostoPendentePorIdobjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function AtualizaParcelaPendentePorIdobjeto($idobjetoorigem, $tipoobjetoorigem, $status)
    {
        $results = SQL::ini(ContaPagarItemQuery::AtualizaParcelaPendentePorIdobjeto(), [
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "idobjetoorigem" => $idobjetoorigem,
            "status" => $status
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            //die($results->sql());
            return [];
        } else {
            //die($results->sql());
            return $results->data[0];
        }
    }

    public static function deletaParcelaPendentePorIdobjeto($idobjetoorigem, $tipoobjetoorigem)
    {
        $results = SQL::ini(ContaPagarItemQuery::deletaParcelaPendentePorIdobjeto(), [
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "idobjetoorigem" => $idobjetoorigem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFaturaSemComissaoPorIdobjeto($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarFaturaSemComissaoPorIdobjeto(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function deletarPorIdObjetoTipoObjetoEIdPessoa($idobjeto, $tipoobjeto, $idpessoa)
    {
        $results = SQL::ini(CarimboQuery::deletarPorIdObjetoTipoObjetoEIdPessoa(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function deletaFaturaSemComissaoPorIdobjeto($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::deletaFaturaSemComissaoPorIdobjeto(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function somarValorFaturaPorIdobjeto($idobjeto, $tipoobjeto)
    {

        $results = SQL::ini(ContaPagarQuery::somarValorFaturaPorIdobjeto(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    public static function ajustarFaturaMaisUmCentavo($idobjeto, $tipoobjeto, $parcela, $idempresa)
    {

        $results = SQL::ini(ContaPagarQuery::ajustarFaturaMaisUmCentavo(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "idempresa" => $idempresa,
            "parcela" => $parcela
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    public static function ajustarFaturaMenosUmCentavo($idobjeto, $tipoobjeto, $idempresa)
    {

        $results = SQL::ini(ContaPagarQuery::ajustarFaturaMenosUmCentavo(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "idempresa" => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarSeExisteComissaoPendetePorIdobjeto($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarSeExisteComissaoPendetePorIdobjeto(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarComissaoPorIdpessaoIdnf($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarComissaoPorIdpessaoIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarParcelaPorNf($idnf, $tipo)
    {
        $results = SQL::ini(ContaPagarItemQuery::buscarParcelaPorNf(), [
            "idnf" => $idnf,
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarFaturaNf($idnf, $tipo)
    {
        $results = SQL::ini(ContaPagarQuery::buscarFaturaNf(), [
            "idnf" => $idnf,
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function listaSolfabCliente($idprodservformula, $inidpessoa, $idprodserv = NULL)
    {
        return SolfabController::listaSolfabCliente($idprodservformula, $inidpessoa, $idprodserv = NULL);
    }

    public static function deletaLoteconsPorIdNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::deletaLoteconsPorIdNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }
    public static function deletaLotereservaPorIdNF($idnf)
    {
        $results = SQL::ini(PedidoQuery::deletaLotereservaPorIdNF(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }


    public static $_justificativa = array(
        '' => '',
        'PEDIDO CLIENTE' => 'A Pedido do Cliente',
        'LOGISTICA' => 'Alterado Pela Logistíca',
        'ATRASO' => 'Atraso no Envio',
        'PRAZO INCORRETO' => 'Prazo Incorreto',
        'OUTROS' => 'Outros'
    );


    public static  function buscarTipoNatPorIdnf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarTipoNatPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFormaPagamentoPorParcela($idcontapagaritem)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarFormaPagamentoPorParcela(), [
            "idcontapagaritem" => $idcontapagaritem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function somarValorParcelasPorFatura($idcontapagar)
    {
        $results = SQL::ini(ContaPagarQuery::somarValorParcelasPorFatura(), [
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }



    public static function AtualizaValorFatura($idcontapagar, $valor)
    {
        $results = SQL::ini(ContaPagarQuery::AtualizaValorFatura(), [
            "idcontapagar" => $idcontapagar,
            "valor" => $valor
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarIdRemessa($idformapagamento, $idcontapagar)
    {
        $results = SQL::ini(RemessaQuery::buscarIdRemessa(), [
            "idformapagamento" => $idformapagamento,
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarIdRemessaParaFormaPagamento($idformapagamento)
    {
        $results = SQL::ini(RemessaQuery::buscarIdRemessaParaFormaPagamento(), [
            "idformapagamento" => $idformapagamento
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function inserirRemessaItem($arrayInserirRemessaItem)
    {
        $results = SQL::ini(RemessaQuery::inserirRemessaItem(), $arrayInserirRemessaItem)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "Falha";
        }
    }

    public static function AtualizarStatusContaPagar($idcontapagar, $idfluxostatus)
    {
        $results = SQL::ini(ContaPagarQuery::AtualizarStatusContaPagar(), [
            "idcontapagar" => $idcontapagar,
            "idfluxostatus" => $idfluxostatus,
            "status" => "PENDENTE"
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "Falha";
        }
    }

    public static function permiteGerarDanfe($idpessoa)
    {
        $verificatipopessoa = SQL::ini(PessoaQuery::verificaTipoPessoa(), [
            "idpessoa" => $idpessoa,
            "intipopessoa" => '1,2',
        ])::exec();


        if ($verificatipopessoa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $verificatipopessoa->errorMessage());
            return false;
        }
        if ($verificatipopessoa->numRows() > 0) {
            $results = SQL::ini(PessoaQuery::verificaEmpresaPlantelEEmpresaPessoaSaoIguais(), [
                "idpessoa" => $idpessoa,
            ])::exec();

            if ($results->error()) {
                parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
                return false;
            }
            if ($results->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public static function verificarnftransf($idnf)
    {
        $results = SQL::ini(PedidoQuery::verificarnftransf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfconfpagarOrdenadoPorOrdemDescrescente($idnf)
    {
        return NfEntradaController::buscarNfconfpagarOrdenadoPorOrdemDescrescente($idnf);
    }

    public static function buscarIdContaPagarItem($idnf, $tipoobjetoorigem, $parcela = NULL, $idcontapagar = NULL)
    {
        $andParcela = empty($parcela) ? "" : "  AND parcela = $parcela ";
        $andIdcontapagar = empty($idcontapagar) ? "" : " AND idcontapagar = $idcontapagar";
        $results = SQL::ini(ContaPagarItemQuery::buscarIdContaPagarItem(), [
            "idobjetoorigem" => $idnf,
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "andParcela" => $andParcela,
            "andIdcontapagar" => $andIdcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function atualizarContaPagarPorIdContaPagarItem($parcelas, $valor, $datapagto, $idcontapagaritem)
    {
        $results = SQL::ini(ContaPagarItemQuery::atualizarContaPagarPorIdContaPagarItem(), [
            "parcelas" => $parcelas,
            "valor" => $valor,
            "datapagto" => $datapagto,
            "idcontapagaritem" => $idcontapagaritem,
            "alteradopor" => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
    }

    public static function atualizarStatusContaPagarItem($idcontapagaritem, $status)
    {
        $results = SQL::ini(ContaPagarItemQuery::atualizarStatusContaPagarItem(), [
            "idcontapagaritem" => $idcontapagaritem,
            "status" => $status,
            "alteradopor" => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
    }

    public static function buscarIdContaPagar($idnf, $tipoobjeto, $parcela = NULL)
    {
        $andParcela = empty($parcela) ? "" : "  AND parcela = $parcela";
        $results = SQL::ini(ContaPagarQuery::buscarIdContaPagar(), [
            "idobjeto" => $idnf,
            "tipoobjeto" => $tipoobjeto,
            "andParcela" => $andParcela
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarFormaPagamentoPorIdEmpresaETipo($idempresa, $tipo)
    {
        return ContaPagarController::buscarFormaPagamentoPorIdEmpresaETipo($idempresa, $tipo);
    }

    public static function buscarNotasDiferentesAtualContaPagar($idpessoa, $idempresa, $idformapagamento, $tipoobjeto, $idnf, $idcontapagar)
    {
        $results = SQL::ini(ContaPagarQuery::buscarIdContbuscarNotasDiferentesAtualContaPagaraPagar(), [
            "idpessoa" => $idpessoa,
            "idempresa" => $idempresa,
            "idformapagamento" => $idformapagamento,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idnf,
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->numRows();
        }
    }

    public static function atualizarContaPagarPorIdContaPagar($parcelas, $valor, $datapagto, $idcontapagar)
    {

        $results = SQL::ini(ContaPagarQuery::atualizarContaPagarPorIdContaPagar(), [
            "parcelas" => $parcelas,
            "valor" => $valor,
            "datapagto" => $datapagto,
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
    }

    public static function atualizarIdContaPagarPorIdContaPagarItemParaNull($idcontapagaritem)
    {
        $results = SQL::ini(ContaPagarItemQuery::atualizarIdContaPagarPorIdContaPagarItem(), [
            "idcontapagaritem" => $idcontapagaritem,
            "AndIdempresa" => "",
            "idcontapagar" => 'NULL'
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
    }

    public static function apagarContaPagarAPartirdaParcela($tipoobjeto, $idobjeto, $parcela)
    {
        $results = SQL::ini(ContaPagarQuery::apagarContaPagarAPartirdaParcela(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "parcela" => $parcela
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
    }

    public static function apagarContaPagarItemAPartirdaParcela($tipoobjetoorigem, $idobjetoorigem, $parcela)
    {
        $results = SQL::ini(ContaPagarItemQuery::apagarContaPagarItemAPartirdaParcela(), [
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "idobjetoorigem" => $idobjetoorigem,
            "parcela" => $parcela
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
    }
    public static function buscarRemssaEnvioPorIdnf($idnf)
    {
        $results = SQL::ini(RemessaQuery::buscarRemssaEnvioPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfpLote($idnf)
    {
        $results = SQL::ini(NfPLoteQuery::buscarNfpLote(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function listarArquivosAnexosPorIdNf($idnf)
    {
        $results = SQL::ini(ArquivoQuery::buscarArquivoPorTipoObjetoEIdObjeto(), [
            "idobjeto" => $idnf,
            'tipoobjeto' => 'nf'
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            return $results->numRows();
        }
    }

    public static function buscarConfiguracoesFormaPagamento($idformapagamento)
    {
        return FormaPagamentoController::buscarConfiguracoesFormaPagamento($idformapagamento);
    }

    public static function atualizarContaPagarFormaPagamentoPorIdContaPagar($idagencia, $idformapagamento, $idcontapagar)
    {
        return ContaPagarController::atualizarContaPagarFormaPagamentoPorIdContaPagar($idagencia, $idformapagamento, $idcontapagar);
    }

    public static function atualizarFormaPagamentoContaPagarItem($idformapagamento, $idcontapagaritem)
    {
        return ContaPagarController::atualizarFormaPagamentoContaPagarItem($idformapagamento, $idcontapagaritem);
    }

    public static function atualizarFormaPagamentoAgrupadoContaPagarItem($idformapagamento, $idcontapagaritem)
    {
        return ContaPagarController::atualizarFormaPagamentoAgrupadoContaPagarItem($idformapagamento, $idcontapagaritem);
    }


    public static function buscarCategoriaDevolucao($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarCategoriaDevolucao(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarCategoriaDevolucaoEntrada($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarCategoriaDevolucaoEntrada(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarCategoriaCancelado($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarCategoriaCancelado(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }
    public static function atualizarCategoriaSubcategoriaNfItem($idnf, $idcontaitem, $idtipoprodserv)
    {
        $results = SQL::ini(NfItemQuery::atualizarCategoriaSubcategoriaNfItem(), [
            "idnf" => $idnf,
            "idcontaitem" => $idcontaitem,
            "idtipoprodserv" => $idtipoprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFinalidadeNatop($idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarFinalidadeNatop(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscaSeloLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscaSeloLote(), [
            "idlote" => $idlote
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarvalorCtePedido($idnfcte, $idnf)
    {
        $results = SQL::ini(PedidoQuery::buscarvalorCtePedido(), [
            "idnfcte" => $idnfcte,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
}

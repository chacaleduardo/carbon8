<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prodserv_query.php");
require_once(__DIR__."/../querys/lote_query.php");
require_once(__DIR__."/../querys/nfitem_query.php");

//Controllers
require_once(__DIR__."/../controllers/inclusaoresultado_controller.php");
require_once(__DIR__."/../controllers/lote_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");
require_once(__DIR__."/../controllers/prodservformula_controller.php");
require_once(__DIR__."/../controllers/unidade_controller.php");

class CalculoEstoqueController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarIdprodservFormula($idprodserv)
    {   
		$prodservFormula = ProdservformulaController::buscarIdprodservFormula($idprodserv);
        return $prodservFormula['idprodservformula'];
    }

	public static function buscarCalculoEstoqueProdservComFormula($idprodservformula)
    {
        $results = SQL::ini(ProdservQuery::buscarCalculoEstoqueProdservComFormula(), [
            "idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

	public static function buscarQtdpaComFormula($idprodservformula, $idprodserv)
    {
        $results = SQL::ini(LoteQuery::buscarQtdpaComFormula(), [
            "idprodservformula" => $idprodservformula,
			"idprodserv" => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

	public static function buscarCalculoEstoqueProdserv($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarCalculoEstoqueProdserv(), [
			"idprodserv" => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

	public static function buscarQtdpa($idprodserv)
    {
        $results = SQL::ini(NfItemQuery::buscarQtdpa(), [
			"idprodserv" => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarRotuloProdservComRorulo($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarRotuloProdservComRorulo(), [
			"idprodserv" => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarRotuloProdserv($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarRotuloProdserv(), [
			"idprodserv" => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarMediaDiaria($idprodserv, $idunidade, $idprodservformula, $consumodias)
	{
        return ProdServController::buscarMediaDiaria($idprodserv, $idunidade, $idprodservformula, $consumodias);
    }

    public static function buscarUnidadesPorTipoObjeto($idprodserv, $tipoobjeto, $idempresa = NULL)
	{
        $arrUnidade = [];
        $listarUnidade = UnidadeController::buscarUnidadesPorTipoObjeto($idprodserv, $tipoobjeto, $idempresa);
        foreach($listarUnidade as  $_unidade)
        {
            $arrUnidade[$_unidade['idunidade']] = $_unidade['unidade'];
        }
        return $arrUnidade;
    }

    public static function buscarUnidadesPorTipoObjetoModulo($idprodserv, $idempresa = NULL)
	{
        $arrUnidade = [];
        $listarUnidade = UnidadeController::buscarUnidadesPorTipoObjetoModulo($idprodserv, $idempresa);
        foreach($listarUnidade as  $_unidade)
        {
            $arrUnidade[$_unidade['idunidade']] = $_unidade['unidade'];
        }
        return $arrUnidade;
    }

    public static function buscarHistoricoProdservPessoaPorIdProdservFormula($idprodservformula, $campo)
    {   
        return PessoaController::buscarHistoricoProdservPessoaPorIdProdservFormula($idprodservformula, $campo);
    }

    public static function buscarHistoricoProdservPessoaPorIdProdserv($idprodserv, $campo)
    {   
        return PessoaController::buscarHistoricoProdservPessoaPorIdProdserv($idprodserv, $campo);
    }

    public static function buscarLoteConsComSolmatItemPorIdUnidade($idprodserv, $idunidade, $idprodservformula, $consumodiaslote)
    {   
        return LoteController::buscarLoteConsComSolmatItemPorIdUnidade($idprodserv, $idunidade, $idprodservformula, $consumodiaslote);
    }

    public static function buscarUnidadeLotePorIdLote($idlote)
    {   
        return LoteController::buscarUnidadeLotePorIdLote($idlote);
    }

    public static function buscarUnidadeLotePorIdLoteFracao($idlotefracao)
    {   
        return LoteController::buscarUnidadeLotePorIdLoteFracao($idlotefracao);
    }

    public static function buscarNfePorIdNfItem($idnfitem)
    {   
        return NfController::buscarNfePorIdNfItem($idnfitem);
    }

    public static function buscarAmostraPorIdResultado($idresultado)
    {   
        return InclusaoResultadoController::buscarAmostraPorIdResultado($idresultado);
    }

    public static function buscarUnidadeModuloPorTipoObjetoParaLote($idunidadeest)
    {   
        return UnidadeController::buscarUnidadeModuloPorTipoObjetoParaLote($idunidadeest);
    }

    public static function buscarLoteMeios($idprodserv, $idunidade, $consumodiaslote)
    {   
        return LoteController::buscarRateio($idprodserv, $idunidade, $consumodiaslote);
    }

    public static function buscarUnidadeObjetoPorTipoObjetoEIdUnidade($idobjeto, $tipoobjeto, $modulotipo)
    {   
        return UnidadeController::buscarUnidadeObjetoPorTipoObjetoEIdUnidade($idobjeto, $tipoobjeto, $modulotipo);
    }

    public static function buscarDadosNfitemLote($idprodserv, $consumodiasgraf)
	{
		return NfController::buscarDadosNfitemLote($idprodserv, $consumodiasgraf);
	}

    public static function buscarFormuladosSemFormula($idprodserv, $consumodiasgraf)
	{
		return NfController::buscarFormuladosSemFormula($idprodserv, $consumodiasgraf);
	}

    public static function buscarProdServFormulaPorIdProdServEStatus($idprodserv, $status)
	{
		return ProdservformulaController::buscarProdServFormulaPorIdProdServEStatus($idprodserv, $status);
	}

    public static function buscarUnidadeLoteFracaoPorIdProdserv($idprodserv, $condicaoWhere)
	{
		return LoteController::buscarUnidadeLoteFracaoPorIdProdserv($idprodserv, $condicaoWhere);
	}

    public static function buscarLoteELoteFracaoPorIdProdservEIdUnidade($idprodserv, $idunidade, $conteudo, $condicaoWhere)
	{
		return LoteController::buscarLoteELoteFracaoPorIdProdservEIdUnidade($idprodserv, $idunidade, $conteudo, $condicaoWhere);
	}

    public static function buscarGrafico($idprodserv, $idprodservformula, $idunidade, $consumodiasgraf, $condicaoWhere)
    {
        if(!empty($idprodservformula))
        {
            $idobjeto2 = $idprodservformula;
            $idetlconf = 10;
            $objeto = 'vwLoteEstoqueFormulado';
        } else {
            $idobjeto2 = $idprodserv;
            $idetlconf = 9;
            $objeto = 'vwLoteEstoque';
        }
        $results = SQL::ini(LoteQuery::buscarGrafico(), [
			"idprodserv" => $idprodserv,
            "idunidade" => $idunidade,
            "condicaoWhere" => $condicaoWhere,
            "idobjeto1" => $idunidade,
            "idetlconf" => $idetlconf,
            "idobjeto2" => $idobjeto2,
            "consumodiasgraf" => $consumodiasgraf,
            "objeto" => $objeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarNaoFormuladosPorIdProdserv($idprodserv, $tipo)
	{
		return ProdservController::buscarNaoFormuladosPorIdProdserv($idprodserv, $tipo);
	}

    public static function buscarConsumoIntervalo60diasPorIdUnidadeEstIdProdservIdProdservFormula($idunidadeest, $idprodserv, $idprodservformula = FALSE)
	{
		return LoteController::buscarConsumoIntervalo60diasPorIdUnidadeEstIdProdservIdProdservFormula($idunidadeest, $idprodserv, $idprodservformula);
	}

    public static function buscarConvEstoque($idlotefracao)
    {
        return LoteController::buscarConvEstoque($idlotefracao);
    }

    public static function buscarCotacaoNfitem($idprodserv)
    {
        return NfController::buscarCotacaoNfitem($idprodserv);
    }

    public static function atualizarValoresCalculoEstoqueProdserv($arrayAtualizaProdserv)
    {
        return ProdservController::atualizarValoresCalculoEstoqueProdserv($arrayAtualizaProdserv);
    }
    
    public static function buscarFormuladosPorIdProdservFormula($idprodservformula)
    {
        $results = SQL::ini(ProdservQuery::buscarFormuladosPorIdProdservFormula(), [
			"idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function atualizarCalculoEstoqueProdservFormula($arrayAtualizaProdservFormula)
    {
        $results = SQL::ini(ProdservFormulaQuery::atualizarCalculoEstoqueProdservFormula(), $arrayAtualizaProdservFormula)::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----
	//----- AUTOCOMPLETE -----

	// ----- Variáveis de apoio -----
	public static $_tempoReposicao = array('3' => '3 dias',
								   		   '7' => '7 dias',
										   '10' => '10 dias',
										   '15' => '15 dias',
										   '21' => '21 dias',
										   '30' => '30 dias',
										   '35' => '35 dias',
										   '45' => '45 dias',
										   '60' => '60 dias',
										   '90' => '90 dias',
										   '120' => '120 dias');

    public static $_estoqueseguranca = array('0' => '0 dia',
                                             '1' => '1 dia',
                                             '3' => '3 dias',
                                             '5' => '5 dias',
                                            '10' => '10 dias',
                                            '20' => '20 dias',
                                            '30' => '30 dias',
                                            '40' => '40 dias',
                                            '45' => '45 dias',
                                            '50' => '50 dias',                                            
                                            '60' => '60 dias',                                            
                                            '70' => '70 dias',
                                            '80' => '80 dias',
                                            '90' => '90 dias',
                                            '100' => '100 dias');
    
    public static $_consumodias = array('30' => '30 dia',
                                        '60' => '60 dia',
                                        '90' => '90 dias',
                                        '120' => '120 dias',
                                        '150' => '150 dias',
                                        '180' => '180 dias',
                                        '210' => '210 dias',
                                        '250' => '250 dias',
                                        '280' => '280 dias',
                                        '310' => '310 dias',                                            
                                        '340' => '340 dias',                                            
                                        '365' => '365 dias');
                            
    public static $_consumodiasgraf = array('30' => '30 dia',
                                            '60' => '60 dia',
                                            '90' => '90 dias',
                                            '120' => '120 dias',
                                            '150' => '150 dias',
                                            '180' => '180 dias',
                                            '210' => '210 dias',
                                            '250' => '250 dias',
                                            '280' => '280 dias',
                                            '310' => '310 dias',                                            
                                            '340' => '340 dias',                                            
                                            '365' => '365 dias',
                                            '550' => '550 dias',
                                            '730' => '730 dias');

    public static $_tempocompra = array('0' => '0 dia',
                                        '1' => '1 dias',
                                        '3' => '3 dias',
                                        '5' => '5 dias',
                                        '10' => '10 dias',
                                        '20' => '20 dias',
                                        '30' => '30 dias',
                                        '40' => '40 dias',
                                        '45' => '45 dias',
                                        '50' => '50 dias',
                                        '60' => '60 dias',
                                        '70' => '70 dias',
                                        '80' => '80 dias',
                                        '90' => '90 dias',
                                        '100' => '100 dias');

    public static $_justificativa = array('' => '',
                                          'CONSUMO AUTOMATICO' => 'Alteração baseada no cálculo de consumo do sistema',
                                          'PROJECAO DE CONSUMO' => 'Alteração baseada na previsão de aumento/diminuição informada via evento',
                                          'OUTROS' => 'Outros');
	// ----- Variáveis de apoio -----
}
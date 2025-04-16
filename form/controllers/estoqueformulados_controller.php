<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_modulo_query.php");
require_once(__DIR__ . "/../querys/prodservformula_query.php");
require_once(__DIR__ . "/../querys/lote_query.php");
require_once(__DIR__ . "/../querys/lotefracao_query.php");

//Controllers
require_once(__DIR__."/../controllers/calculoestoque_controller.php");

class EstoqueFormuladosController extends Controller
{
    public static function buscarModuloTipoLoteViculadoAUnidade($idUnidadePadrao)
    {
        $results = SQL::ini(_ModuloQuery::buscarModuloTipoLoteViculadoAUnidade(), [
            'idUnidadePadrao' => $idUnidadePadrao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0]['modulo'];
        }
    }


    public static function buscarProdutosFormuladosEmAlertaDeProducao($clausulaVenda, $idUnidadePadrao, $estoqueminimo=false, $_modulo){

        if($estoqueminimo == true){
            $strwhere = " WHERE ((u2.estmin >= u2.total)) ";
            $strstatus = " n.status IN ('QUARENTENA', 'TRIAGEM','PROCESSANDO','LIBERADO') ";
        }else{
            $strwhere = "";
            $strstatus = " n.status IN ('QUARENTENA','PROCESSANDO','LIBERADO') ";
        }

        $shareUnidade = self::buscarRegrasShareModuloFiltrosPesquisa($_modulo);
        if(!empty($shareUnidade)){
            $idUnidadePadrao = $shareUnidade;
        }

        if (in_array($_modulo, ['lotealertapesqdes', 'lotesformuladosmeio'])) {
            $campoSomaqtd = "SUM(IF(lf.qtd = 0, n.qtdpedida, lf.qtd)) AS y";
            $joinProdserFormula = "";
            $condicaoUnidadePadrao = " AND n.idunidade IN ($idUnidadePadrao)";
            $strstatus = " n.status IN ('QUARENTENA','PROCESSANDO','LIBERADO', 'TRIAGEM') ";
            $modulotipo = "'lote'";
        }elseif(in_array($_modulo, ['estoqueformuladosvenda', 'estoqueformulados'])){
            $campoSomaqtd = "SUM(IFNULL(lf.qtd, 0)) AS y";
            $joinProdserFormula = "JOIN prodservformula f ON (f.idprodservformula = n.idprodservformula)";
            $condicaoUnidadePadrao = " AND (lf.idunidade = f.idunidadeest OR lf.idunidade = f.idunidadealerta)";
            $modulotipo = "'formalizacao'";
        }

        $results = SQL::ini(ProdservFormulaQuery::buscarProdutosFormuladosEmAlertaDeProducao(), [
            'clausulaVenda' => $clausulaVenda,
            'idUnidadePadrao' => $idUnidadePadrao,
            'whereminimo' => $strwhere,
            'wherestatus' => $strstatus,
            'campoSomaqtd' => $campoSomaqtd,
            'joinProdserFormula' => $joinProdserFormula,
            'condicaoUnidadePadrao' => $condicaoUnidadePadrao,
            'modulotipo' => $modulotipo,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['sql'] = $results->sql();
            $dados['data'] = $results->data;
            $dados['numRows'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarRegrasShareModuloFiltrosPesquisa($_modulo)
    {
        $listarShare = _moduloController::buscarRegrasShareModuloFiltrosPesquisa($_modulo);
        foreach($listarShare as $share)
        {
            $jclauswhere = json_decode($share['jclauswhere'], true);
            $idEmpresas = explode(",", $jclauswhere['idempresa']);
            $idUnidadePadrao = in_array(cb::idempresa(), $idEmpresas) ? $jclauswhere['idunidade'] : "";
            return $idUnidadePadrao;
        }
    }

    public static function buscarDadosConsumoFormula($idprodservformula){
        $results = SQL::ini(ProdservFormulaQuery::buscarDadosConsumoFormula(), [
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarLotesDeFormalizacaoEmandamento($idUnidadePadrao, $idprodservformula, $idprodserv){
        $results = SQL::ini(LoteQuery::buscarLotesDeFormalizacaoEmandamento(), [
            'idUnidadePadrao' => $idUnidadePadrao,
            'idprodservformula' => $idprodservformula,
            'idprodserv' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarLoteEmQuarentenaProdutoEmALerta($idUnidadePadrao, $idprodserv){
        $results = SQL::ini(LoteQuery::buscarLoteEmQuarentenaProdutoEmALerta(), [
            'idUnidadePadrao' => $idUnidadePadrao,
            'idprodserv' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarPrimeiroDiaConsumoLote($idprodserv, $idunidadeest, $idprodservformula)
    {
        $results = SQL::ini(LoteFracaoQuery::buscarPrimeiroDiaConsumoLote(), [
            'idprodserv' => $idprodserv,
            'idunidade' => $idunidadeest,
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    // ----- Variáveis de apoio -----
    public static function tempoReposicao()
	{
		return CalculoEstoqueController::$_tempoReposicao;
	}

    public static $statusEstoque = array('' => '', 
										 'CONSUMO AUTOMATICO' => 'Alteração baseada no cálculo de consumo do sistema', 
										 'PROJECAO DE CONSUMO' => 'Alteração baseada na previsão de aumento/diminuição informada via evento',
										 'OUTROS' => 'Outros');
    // ----- Variáveis de apoio -----

}

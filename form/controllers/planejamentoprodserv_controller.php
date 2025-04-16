<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../../form/querys/planejamentoprodserv_query.php");

//Controllers
require_once(__DIR__."/../controllers/_rep_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");

class PlanejamentoProdServController extends Controller
{
    // ----- FUNÇÕES -----
    public static function buscarUnidadesPorUnidadeObjeto($idobjeto, $tipoobjeto, $idempresa, $idtipounidade = NULL)
    {
        return UnidadeController::buscarUnidadesPorUnidadeObjeto($idobjeto, $tipoobjeto, $idempresa, $idtipounidade);
    }
    
    public static function buscarFormulasProdserv($idprodserv)
    {
        $data = ProdServController::buscarIdProdservFormulaPorIdProdservArray($idprodserv);
        if(empty($data)){
            return array(array("idprodservformula" => null, "rotulo" => "Geral"));
        }
        return $data;
    }

    public static function buscarExercicioPorId($idprodserv, $idunidade,$idprodservform = null)
    {
        if($idprodservform){
            $formula = "and p.idprodservformula =".$idprodservform;
        }else{
            $formula ="";
        }

        $results = SQL::ini(PlanejamentoprodservQuery::buscarExercicioPorId(), [
            "idprodserv" => $idprodserv,
            "idunidade" => $idunidade,
            "formula" => $formula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
			$arrval = [];
			foreach($results->data as $_valor)
			{	
				$arrval[$_valor['exercicio']] = $_valor['exercicio'];	
			}

            return $arrval;
        }	
    }
    
    public static function verificaProdservComFormulaEPlanejamentoSem($idprodserv)
    {

        $results = SQL::ini(PlanejamentoprodservQuery::verificaProdservComFormulaEPlanejamentoSem(), [ 
            "idprodserv" => $idprodserv,
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return 0;
		}else{
			return $results->numRows();
		}
    }
    public static function buscarPlanejamentoprodservExercicio($idprodserv, $idformula = null)
    {
        if($idformula){
            $formula = "and idprodservformula =".$idformula; 
        }else{
            $formula = "and idprodservformula is null"; 
        }

        $results = SQL::ini(PlanejamentoprodservQuery::buscarPlanejamentoprodservExercicio(), [ 
            "idprodserv" => $idprodserv,
            "formula" => $formula,
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
    }

    public static function buscarPlanejamentoprodservMes($idprodserv,$exercicio,$idformula = null)
    {
        if($idformula){
            $formula = "and idprodservformula =".$idformula; 
        }else{
            $formula = "and idprodservformula is null"; 
        }
        $results = SQL::ini(PlanejamentoprodservQuery::buscarPlanejamentoprodservMes(), [ 
            "idprodserv" => $idprodserv,
            "exercicio" => $exercicio,
            "formula" => $formula,
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
    }

    public static function buscarHistoricoModuloAlteracao($idobjeto,$tabela,$campo)
	{
		$results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
            "idobjeto" => $idobjeto,
			"tipoobjeto" => $tabela,
			"campo" => " AND h.campo = '$campo'"
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return (count($results->data) > 0) ? $results->data : "";
        }
	}

    public static function buscarPlanejamentoProdservAdicional($idplanejamentoprodserv)
    {
        $results = SQL::ini(PlanejamentoprodservQuery::buscarPlanejamentoProdservAdicional(), [
            "idplanejamentoprodserv" => $idplanejamentoprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return (count($results->data) > 0) ? $results->data : "";
        }
    }

    public static function buscarPlanejamentoPorIdProdservMesExercioUnidade($idunidade, $exercicio, $mes, $idsolmat)
    {
        $results = SQL::ini(PlanejamentoprodservQuery::buscarPlanejamentoPorIdProdservMesExercioUnidade(), [ 
            "andIdprodserv" => "",
            "idunidade" => $idunidade,
            "exercicio" => $exercicio,
            "mes" => $mes,
            "andIdsolmat" => " AND si.idsolmat = $idsolmat"
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
            $qtd = 0;
            foreach($results->data as $dados)
            {
                if($dados['planejado'] > 0 || $dados['adicional'] > 0)
                {
                    $dados['dados'][$dados['idprodserv']]['planejado'] = $dados['planejado'];
                    $dados['dados'][$dados['idprodserv']]['adicional'] = $dados['adicional'];
                    $qtd++;
                }
            }
            
            $dados['qtdLinhas'] = $qtd;
            return $dados;
		}
    }

    public static function buscarPlanejamentoPorIdProdservMesExercioUnidadePorProdserv($idprodserv, $idunidade, $exercicio, $mes)
    {
        $results = SQL::ini(PlanejamentoprodservQuery::buscarPlanejamentoPorIdProdservMesExercioUnidade(), [ 
            "andIdprodserv" => " AND pp.idprodserv = $idprodserv",
            "idunidade" => $idunidade,
            "exercicio" => $exercicio,
            "mes" => $mes,
            "andIdsolmat" => ""
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
            return $results->data[0];
		}
    }

    public static function buscarPlanejamentoPorIdProdservMesExercio($idprodserv,$exercicio, $mes)
    {
        $results = SQL::ini(PlanejamentoprodservQuery::buscarPlanejamentoPorIdProdservMesExercio(), [ 
            "idprodserv" => $idprodserv,          
            "exercicio" => $exercicio,
            "mes" => $mes           
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
            return $results->data;
		}
    }

    public static function buscaCategoria($idempresa, $idcategoria, $exercicio)
    {
        $results = SQL::ini(PlanejamentoprodservQuery::buscaCategoria(), [ 
            "idempresa" => $idempresa,
            "exercicio" => $exercicio,
            "categoria" => ($idcategoria ? " AND c.idcontaitem =". $idcategoria : "")
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}
        
        $categorias = [];
        foreach($results->data as $categoria){
            if(!isset($categorias[$categoria['idcontaitem']])){
                $categorias[$categoria['idcontaitem']]['contaitem'] = $categoria['contaitem'];
                $categorias[$categoria['idcontaitem']]['idcontaitem'] = $categoria['idcontaitem'];
                $categorias[$categoria['idcontaitem']]['subcategorias'] = [];
            }
            $categorias[$categoria['idcontaitem']]['subcategorias'][] = $categoria;
        }
        
        return $categorias;
    }
    public static function buscaInsumosCategoria($idcategoria)
    {
        $results = SQL::ini(PlanejamentoprodservQuery::buscaInsumosCategoria($idcategoria), [ 
            "categoria" => $idcategoria
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
            return $results->data;
		}
    }

    public static function buscaValorPorIdtipo($idempresa, $exercicio, $idtipoprodserv){
        $results = SQL::ini(PlanejamentoprodservQuery::buscaValorPorIdtipo($idempresa, $exercicio, $idtipoprodserv), [ 
            "idempresa" => $idempresa,
            "exercicio" => $exercicio,
            "idtipoprodserv" => $idtipoprodserv
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
            return $results->data;
		}

    }
    public static function buscaPrevisaoPorTipo($idempresa, $exercicio, $idtipoprodserv){
        $results = SQL::ini(PlanejamentoprodservQuery::buscaPrevisaoPorTipo($idempresa, $exercicio, $idtipoprodserv), [ 
            "idempresa" => $idempresa,
            "exercicio" => $exercicio,
            "idtipoprodserv" => $idtipoprodserv
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
            return $results->data;
		}

    }
    public static function listaIdtipoprodservPorContaitem($idcontaitem){
        $results = SQL::ini(PlanejamentoprodservQuery::listaIdtipoprodservPorContaitem($idcontaitem), [ 
            "idcontaitem" => $idcontaitem
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
            return $results->data;
		}

    }

    public static function BuscarHistorico($idforecastcompra, $versao)
    {
        $results = SQL::ini(PlanejamentoprodservQuery::buscaHistorico(), [ 
            "idforecastcompra" => $idforecastcompra,
            "versao" => $versao,
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}
        
        return $results->data;
    }

    // ----- FUNÇÕES -----

    //----- AUTOCOMPLETE -----
    //----- AUTOCOMPLETE -----

    // ----- Variáveis de apoio -----
    public static $_justificativa = array('' => '',
                                          'DEMANDA DA PRODUÇÃO' => 'Demanda da produção',
                                          'DEMANDA DO COMERCIAL' => 'Demanda do comercial',
                                          'OUTROS' => 'Outros');
    // ----- Variáveis de apoio -----
}
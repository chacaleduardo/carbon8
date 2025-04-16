<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/rateioitemdest_query.php");
require_once(__DIR__."/../querys/rateioitemdestori_query.php");
require_once(__DIR__."/../querys/empresa_query.php");

//Controllers

class RateioItemDestController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarNfSemRateio($tipo, $rtipoobjeto, $dttipoobjeto, $tiponf, $status, $clausula)
	{
		$results = SQL::ini(RateioItemDestQuery::buscarNfSemRateio(), [
            "tipo" => $tipo,
            "rtipoobjeto" => $rtipoobjeto,
			"dttipoobjeto" => $dttipoobjeto,
			"tiponf" => $tiponf,
			"status" => $status,
			"clausula" => $clausula
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
			$dados['sql'] = $results->sql();
            return $dados;
        }
	}

	public static function buscarRateioItemNfItem($_idnf, $_idnfitem = NULL)
	{
		if ($_idnfitem) {
			$clausula = " AND i.idnfitem in ($_idnfitem) ";
		} else {
			$clausula = " AND i.idnf in ($_idnf) ";
		}

		$results = SQL::ini(RateioItemDestQuery::buscarRateioItemNfItem(), [
            "clausula" => $clausula
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function inserirRateioItemDest($valor, $_idrateioitem, $_idempresa)
	{
		$destino = explode(',', $valor['idunidade']); 
		$nvalor = (100 * ($valor['valor'] / 100));
		$idpessoa = "";
		if(!empty($valor['idpessoa'])){
			$idpessoa = $valor['idpessoa'];  
		}
		$arrayRateio = [
			'idempresa' => $_idempresa,
			'idrateioitem' => $_idrateioitem,
			'idobjeto' => $destino[0],
			'tipoobjeto' => $destino[1],
			'valor' => $nvalor,
			'idpessoa' => $idpessoa,
			'usuario' => $_SESSION['SESSAO']['USUARIO']
		];
		$results = SQL::ini(RateioItemDestQuery::inserirRateioItemDest(), $arrayRateio)::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

	public static function inserirRateioItemDestOri($valor, $_idrateioitem, $_idempresa)
	{
		$destino = explode(',', $valor['idunidade']); 
		$nvalor = (100 * ($valor['valor'] / 100));
		$idpessoa = "";
		if(!empty($valor['idpessoa'])){
			$idpessoa = $valor['idpessoa'];  
		}
		$arrayRateio = [
			'idempresa' => $_idempresa,
			'idrateioitem' => $_idrateioitem,
			'idobjeto' => $destino[0],
			'tipoobjeto' => $destino[1],
			'valor' => $nvalor,
			'idpessoa' => $idpessoa,
			'usuario' => $_SESSION['SESSAO']['USUARIO'],
		'datacriacao' => 'NOW()'
		];
		$results = SQL::ini(RateioItemDestOriQuery::inserirRateioItemDestOri(), $arrayRateio)::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

	public static function buscarDepartamentoSgDepartamentoPorIdSgDepartamento($idsgdepartamento)
	{
		return SgDepartamentoQuery::buscarDepartamentoSgDepartamentoPorIdSgDepartamento($idsgdepartamento);
    }

	public static function buscarPessoaPorIdPessoa($idpessoa)
	{
		return PessoaController::buscarPessoaPorIdPessoa($idpessoa);
    }

	public static function buscarPessoaPorIdUnidadeFuncionario($condicaoEmpresa = NULL)
	{
		return PessoaController::buscarPessoaPorIdUnidadeFuncionario($condicaoEmpresa);
    }

	public static function buscarvw8FuncionarioUnidadePorIdTipoPessoa()
	{
		return PessoaController::buscarvw8FuncionarioUnidadePorIdTipoPessoa();
    }

	public static function buscarEmpresaPorIdEmpresa($idempresa)
	{
		return EmpresaController::buscarEmpresaPorIdEmpresa($idempresa);
    }

	public static function buscarPorChavePrimariaUnidade($idunidade)
	{
		return UnidadeController::buscarPorChavePrimaria($idunidade);
    }

	public static function buscarvalorRateioitemdest($idnfitem,$idrateioitemdest){
		$results = SQL::ini(RateioItemDestQuery::buscarvalorRateioitemdest(), [          
            "idnfitem"=>$idnfitem,
			"idrateioitemdest"=>$idrateioitemdest         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}

	public static function listarRateioitemdestnfPorIdrateioitemdest($idrateioitemdest){
		$results = SQL::ini(RateioItemDestQuery::listarRateioitemdestnfPorIdrateioitemdest(), [          
           "idrateioitemdest"=>$idrateioitemdest         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function listarRateioitemdestnfPorIdnf($idnf){
		$results = SQL::ini(RateioItemDestQuery::listarRateioitemdestnfPorIdnf(), [          
           "idnf"=>$idnf         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function verificaritemIdnf($idnf){
		$results = SQL::ini(RateioItemDestQuery::verificaritemIdnf(), [          
           "idnf"=>$idnf         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function listarRateioitemdestnfPorIdnfAgrupadoUnidade($idnf){
		$results = SQL::ini(RateioItemDestQuery::listarRateioitemdestnfPorIdnfAgrupadoUnidade(), [          
           "idnf"=>$idnf         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}
	public static function listarRateioitemdestnfPorIdnfAgrupado($idnf){
		$results = SQL::ini(RateioItemDestQuery::listarRateioitemdestnfPorIdnfAgrupado(), [          
           "idnf"=>$idnf         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}

	public static function buscarConfEmpresaCobranca($idempresa,$idempresad){
		$results = SQL::ini(RateioItemDestQuery::buscarConfEmpresaCobranca(), [          
           "idempresa"=>$idempresa,  
		   "idempresad"=>$idempresad      
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}

	public static function buscarRateioCusto($clausula)
	{
		$results = SQL::ini(RateioItemDestQuery::buscarRateioCusto(), [
           	"clausula" => $clausula
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
			$dados['sql'] = $results->sql();
            return $dados;
        }
	}
		
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----

	//----- AUTOCOMPLETE -----
}
?>
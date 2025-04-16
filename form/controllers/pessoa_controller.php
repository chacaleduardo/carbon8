<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/pessoacontato_query.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");

class PessoaController extends Controller
{
	public static function buscarPessoaPorId ( $idpessoa ) {
		$results = SQL::ini(PessoaQuery::buscarPorChavePrimaria(), [
			"pkval" => $idpessoa,
		])::exec();

		if($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data[0];
		}
	}

	// ----- FUNÇÕES -----
	public static function buscarPreferenciaPessoa($caminho, $idpessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarPreferenciaPessoa(), [
			"caminho" => $caminho,
			"idpessoa" => $idpessoa
		])::exec();

		if( !$results->error() ) 
			return (count($results->data) > 0 && !empty($results->data[0]['jsonpref'])) ? $results->data[0]['jsonpref'] : [];
		
		parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		return [];
	}

	public static function buscarPreferenciaCliente($idpessoa,$idempresa=null)
	{
		if($idempresa != null )
		{
			$strempresa = " and f.idempresa=".$idempresa ;  
		}else{
			$strempresa=" ";
		}

		$results = SQL::ini(PessoaQuery::buscarPreferenciaCliente(), [
			"idpessoa" => $idpessoa,
			"strempresa"=>$strempresa
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data[0];
		}
	}

	public static function buscarResultadoAvaliacaoFornecedor($idpessoas)
	{
		$results = SQL::ini(PessoaQuery::buscarResultadoAvaliacaoFornecedor(), [
			"idpessoas" => $idpessoas
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
	}

	public static function buscarPessoasPorIdSgcargo($idSgcargo)
	{
		$pessoas = SQL::ini(PessoaQuery::buscarPessoasPorIdSgcargo(), [
			'idsgcargo' => $idSgcargo
		])::exec();

		if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
	}

	public static function buscarPessoasDisponiveisParaVinculoPorIdSgcargoEGetIdEmpresa($idSgcargo, $getIdEmpresa)
	{
		$pessoas = SQL::ini(PessoaQuery::buscarPessoasDisponiveisParaVinculoPorIdSgcargoEGetIdEmpresa(), [
			'idsgcargo' => $idSgcargo,
			'getidempresa' => $getIdEmpresa
		])::exec();

		if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
	}

	public static function buscarPessoasDisponiveisParaVinculoPorIdEmpresa($idEmpresa)
    {
        $pessoas = SQL::ini(PessoaQuery::buscarPessoasDisponiveisParaVinculoPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }

	public static function buscarSePessoaESocio($idpessoa)
    {
        $pessoa = SQL::ini(PessoaQuery::buscarSePessoaESocio(), [
            'idpessoa' => $idpessoa
        ])::exec();

        if($pessoa->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoa->errorMessage());
            return [];
        } else {
			return $pessoa->numRows();
		}
    }

	public static function buscarPessoaPorContato($idtipopessoa, $idcontato)
	{
		$results = SQL::ini(PessoaContatoQuery::buscarPessoaPorContato(), [
			"idtipopessoa" => $idtipopessoa,
			"idcontato" => $idcontato
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			$dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
		}
	}

	public static function listarFuncionarioPessoaPorIdtipoPessoa($idtipopessoa, $tipo, $status)
	{
		if($tipo == 'funcionarioCb'){
			$share = share::otipo('cb::usr')::funcionarioCbUserIdempresa("p.idpessoa");
		} elseif ($tipo == 'pessoasPorSession'){
			$share = share::pessoasPorSessionIdempresa("idpessoa");
		} elseif($tipo == 'getidempresa') {
			$share = getidempresa('idempresa','pessoa');
		}

		$results = SQL::ini(PessoaQuery::listarFuncionarioPessoaPorIdtipoPessoa(), [
			"idtipopessoa" => $idtipopessoa,
			"share" => $share,
			"status" => $status
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
            return $results->data;
		}
	}

	public static function buscarPessoaObjetoAreaSetor($idpessoa, $tipoobjeto)
	{
		$results = SQL::ini(PessoaObjetoQuery::buscarPessoaObjetoAreaSetor(), [
			"idpessoa" => $idpessoa,
			"tipoobjeto" => $tipoobjeto
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
	}

	public static function buscarPessoaPorStatusIdTipoPessoaEIdEmpresa($status, $idtipopessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarPessoaPorStatusIdTipoPessoaEIdEmpresa(), [
			"status" => $status,
			"idtipopessoa" => $idtipopessoa,
			"getidempresa" => getidempresa('p.idempresa', 'pessoa')
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
	}

	public static function buscarAssinaturaPessoa($status, $tipoobjeto, $idobjeto)
	{
		$results = SQL::ini(CarimboQuery::buscarAssinaturaPessoa(), [
            "status" => $status,
			"tipoobjeto" => $tipoobjeto,
			"idobjeto" => $idobjeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarPessoasPorPlantel($condicaoAnd, $idtipopessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarPessoasPorPlantel(), [
			"condicaoAnd" => $condicaoAnd,
			"getidempresa" => getidempresa('p.idempresa', 'pessoa'),
			"idtipopessoa" => $idtipopessoa
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
            return $results->data;
		}
	}
	// ----- FUNÇÕES -----

	// ----- AUTOCOMPLETE -----
	public static function buscarPessoasPorIdTipoPessoa($idTipoPessoa, $idempresa = false, $toFillSelect = false, $coluna = 'nome', $orderBy = 'nome', $autocomplete = false)
	{
		$orderBy = " ORDER BY p.$orderBy";
		$clausulaIdEmpresa = '';

		if($idempresa) $clausulaIdEmpresa = " and p.idempresa = $idempresa ";

		$pessoas = SQL::ini(PessoaQuery::buscarPessoasPorIdTipoPessoa(), [
			'idtipopessoa' => $idTipoPessoa,
			'orderby' => $orderBy,
			'clausulaIdEmpresa' => $clausulaIdEmpresa
		])::exec();

		if($pessoas->error()){
			parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
			return [];
		}

		if($toFillSelect)
		{
			$arrRetorno = [];

			foreach($pessoas->data as $pessoa)
			{
				$arrRetorno[$pessoa['idpessoa']] = $pessoa[$coluna];
			}

			return $arrRetorno;
		}

		if($autocomplete)
		{
			$arrRetorno = [];

			foreach($pessoas->data as $key => $pessoa)
			{
				$arrRetorno[$key]['label'] = $pessoa['nome'];
				$arrRetorno[$key]['value'] = $pessoa['idpessoa'];
			}

			return $arrRetorno;
		}

		return $pessoas->data;
	}

	// ----- AUTOCOMPLETE -----

	public static function buscarContatoPessoa($idpessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarContatoPessoa(), [			
			"idpessoa" => $idpessoa,
		])::exec();

		if($results->error()){
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}

		$lista = [];

		foreach($results->data as $_contato)
		{	
			$lista[$_contato['idcontato']] = $_contato['nome'];                
		}

		return $lista;
	}

	public static function buscarResponavelCliente($idpessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarResponavelCliente(), [			
			"idpessoa" => $idpessoa,
		])::exec();

		if($results->error()){
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			foreach($results->data as $_contato)
			{	
				$lista[$_contato['idpessoa']] = $_contato['nome'];                
			}
			return $lista;
		}
	}

	public static function buscarPessoa($idpessoa)
	{
		
		$results = SQL::ini(PessoaQuery::buscarPessoa(), [
			"idpessoa" => $idpessoa			
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data[0];
		}
	}

	public static function buscarPessoasDisponiveisParaVincularNoEvento($idEvento, $idPessoa, $getIdEmpresa, $autocomplete = false)
	{
		$pessoas = SQL::ini(PessoaQuery::buscarPessoasDisponiveisParaVincularNoEvento(), [
			'getidempresa' => $getIdEmpresa,
			'idpessoa' => $idPessoa,
			'idevento' => $idEvento
		])::exec();

		if($pessoas->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
			return [];
		}

		if($autocomplete)
		{
			$arrRetorno = [];

            foreach($pessoas->data as $key => $pessoa)
            {
                $arrRetorno[$key]['label'] = $pessoa['nomecurto'];
                $arrRetorno[$key]['value'] = $pessoa['idpessoa'];
            }

            return $arrRetorno;
		}

		return $pessoas->data;
	}

	public static function buscarGruposDePessoasDisponiveisParaVinculoNoEvento($idModulo, $modulo, $idPessoa, $sharePessoa, $shareGrupo)
	{
		$pessoas = SQL::ini(PessoaQuery::buscarGruposDePessoasDisponiveisParaVinculoNoEvento(), [
			'idmodulo' => $idModulo,
			'modulo' => $modulo,
			'idpessoa' => $idPessoa,
			'sharepessoa' => $sharePessoa,
			'sharegrupo' => $shareGrupo
		])::exec();

		if($pessoas->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
			return [];
		}

		return $pessoas->data;
	}

	
	public static function listarPessoaPorIdTipoPessoa($idTipoPessoa)
	{
		$pessoaPorCbUserIdempresa = share::otipo('cb::usr')::pessoaPorCbUserIdempresa("p.idpessoa");

		$pessoaPorCbUserIdempresa = $pessoaPorCbUserIdempresa?$pessoaPorCbUserIdempresa:'AND p.idempresa = '.cb::idempresa();
		$results = SQL::ini(PessoaQuery::listarPessoaPorIdTipoPessoa(), [
			"status" => " AND p.status = 'ATIVO'",
			"idtipopessoa" => $idTipoPessoa,
			"pessoaPorCbUserIdempresa" => $pessoaPorCbUserIdempresa,
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
	}

	public static function buscarTransportadorPorIdpessoa($idPessoa, $idempresa = NULL)
	{
		if($idempresa)
		{
			$idempresa = getidempresa('p.idempresa', 'pessoa');
		}

	    $results = SQL::ini(PessoaQuery::buscarTransportadorPorIdpessoa(), [          
            "idpessoa" => $idPessoa,   
			"idempresa" => $idempresa       
        ])::exec();

         if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
          return $results->data[0];
        }
    }


	public static function listarTransportadora()
	{
		$transportadoraPorSessionIdempresa = share::transportadoraPorSessionIdempresa('idpessoa');

		$transportadoraPorSessionIdempresa = $transportadoraPorSessionIdempresa?$transportadoraPorSessionIdempresa:'AND idempresa = '.cb::idempresa();
		$results = SQL::ini(PessoaQuery::listarTransportadora(), [
			"transportadoraPorSessionIdempresa" => $transportadoraPorSessionIdempresa,
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			foreach($results->data as $_contato)
			{	
				$lista[$_contato['idpessoa']] = $_contato['nome'];                
			}
			return $lista;
		}
	}

	public static function listarPessoaVinculadaLote()
	{
		$compartilharCbUserGerConcentradoPessoa = share::otipo('cb::usr')::compartilharCbUserGerConcentradoPessoa("p.idpessoa");
		$results = SQL::ini(PessoaQuery::listarPessoaVinculadaLote(), [
			"idempresa" => $compartilharCbUserGerConcentradoPessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $pessoasVinculadasLote = $results->data;
			foreach($pessoasVinculadasLote as $_pessoas)
			{	
				$pessoas[$_pessoas['idpessoa']]['nome'] = $_pessoas['nome'];
			}

			return $pessoas;
        }
	}
	public static function listarClietenPedidoPorIdTipoPessoa($idTipoPessoa)
	{
		
		$results = SQL::ini(PessoaQuery::listarClietenPedidoPorIdTipoPessoa(), [
			"status" => " AND p.status IN ('ATIVO', 'PENDENTE')",
			"idtipopessoa" => $idTipoPessoa,
			"idempresa" => cb::idempresa()
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
	}

	public static function buscarClientePedidoPorIdPessoa($idPessoa)
	{	
		$results = SQL::ini(PessoaQuery::buscarClientePedidoPorIdPessoa(), [
			"idpessoa" => $idPessoa
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data[0];
		}
	}

	public static function buscarClientesPorIdTipoPessoa($idtipopessoa)
	{	
		$pessoaPorCbUserIdempresa = share::otipo('cb::usr')::pessoaPorCbUserIdempresa("p.idpessoa");
		$results = SQL::ini(PessoaQuery::buscarClientesPorIdTipoPessoa(), [
			"idtipopessoa" => $idtipopessoa,
			"pessoaPorCbUserIdempresa" => $pessoaPorCbUserIdempresa
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
	}

	public static function buscarTodosClientesETipoFuncionario()
	{	
		$pessoaPorCbUserIdempresa = share::otipo('cb::usr')::pessoaPorCbUserIdempresa("p.idpessoa");
		$results = SQL::ini(PessoaQuery::buscarTodosClientesETipoFuncionario(), [
			"pessoaPorCbUserIdempresa" => $pessoaPorCbUserIdempresa
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data;
		}
	}

	public static function buscarPessoaPorIdPessoa($idpessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarPessoaPorIdPessoa(), [
			"idpessoa" => $idpessoa			
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data[0];
		}
	}

	public static function buscarPessoaPorIdUnidadeFuncionario($condicaoEmpresa = NULL)
	{
		if($condicaoEmpresa)
		{
			$idempresaup = getidempresa('up.idempresa', 'unidade');
			$rateioexternoup="  and up.terceirizado ='N' ";
			$idempresau = getidempresa('u.idempresa', 'unidade');
			$rateioexterno="  and u.terceirizado ='N' ";
		}else// para rateio item externo tela rateioitemdest
		{
			$rateioexterno='  and u.idtipounidade=28 ';
		}

		$results = SQL::ini(PessoaQuery::buscarPessoaPorIdUnidadeFuncionario(), [
			"idempresaup" => $idempresaup,
			"idempresau" => $idempresau,
			"rateioexterno" =>$rateioexterno,
			"rateioexternoup" => $rateioexternoup
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarvw8FuncionarioUnidadePorIdTipoPessoa()
	{
		$idempresa = getidempresa('u.idempresa', 'unidade');
		$results = SQL::ini(PessoaQuery::buscarvw8FuncionarioUnidadePorIdTipoPessoa(),[
			"idempresa" => $idempresa
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarHistoricoProdservPessoaPorIdProdservFormula($idprodservformula, $campo)
	{
		$results = SQL::ini(PessoaQuery::buscarHistoricoProdservPessoaPorIdProdservFormula(),[
			"idprodservformula" => $idprodservformula,
			"campo" => $campo
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarHistoricoProdservPessoaPorIdProdserv($idprodserv, $campo)
	{
		$idempresa = getidempresa('u.idempresa', 'unidade');
		$results = SQL::ini(PessoaQuery::buscarHistoricoProdservPessoaPorIdProdserv(),[
			"idprodserv" => $idprodserv,
			"campo" => $campo
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarFornecedorPorSessionIdEmpresaEIdTipoPessoa($idtipopessoa)
	{	
		$pessoasPorSessionIdempresa = share::pessoasPorSessionIdempresa("p.idpessoa");
		$pessoas = SQL::ini(PessoaQuery::buscarFornecedorPorSessionIdEmpresaEIdTipoPessoa(), [
			'idtipopessoa' => $idtipopessoa,
			'pessoasPorSessionIdempresa' => $pessoasPorSessionIdempresa
		])::exec();

		if($pessoas->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
			return [];
		} else {
			$arrRetorno = [];
            foreach($pessoas->data as $key => $pessoa)
            {
				$arrRetorno[$pessoa["idpessoa"]]["descricao"] = $pessoa["sigla"]." - ".$pessoa["nome"];
            }
            return $arrRetorno;
		}
	}

	public static function listarPessoaIdempresaGrupoNulo()
	{
		$results = SQL::ini(PessoaQuery::listarPessoaIdempresaGrupoNulo())::exec();
		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			foreach($results->data as $_pessoas)
			{	
				$pessoas[$_pessoas['idpessoa']] = $_pessoas['nome'];
			}
			return $pessoas;
		}
	}

	public static function buscarPessoaPorIdTipoPessoaEGetIdEmpresa($idTipoPessoa, $getIdEmpresa, $autocomplete = false)
	{
		$pessoas = SQL::ini(PessoaQuery::buscarPessoaPorIdTipoPessoaEGetIdEmpresa(), [
			'idtipopessoa' => $idTipoPessoa,
			'getidempresa' => $getIdEmpresa
		])::exec();

		if($pessoas->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
			return [];
		}

		if($autocomplete)
		{
			$arrRetorno = [];

			foreach($pessoas->data as $key => $pessoa)
			{
				$arrRetorno[$key]['label'] = $pessoa['nome'];
				$arrRetorno[$key]['value'] = $pessoa['idpessoa'];
			}

			return $arrRetorno;
		}

		return $pessoas->data;
	}

	public static function buscarPessoasPorIdPessoa($idpessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarPessoaPorIdPessoa(), [
			"idpessoa" => $idpessoa			
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}

		return $results->data;
	}

	public static function verificarGestor($idPessoa, $idEmpresa) {
		$gestor = SQL::ini(PessoaobjetoQuery::verificarGestor(), [
			'idpessoa' => $idPessoa,
			'idempresa' => $idEmpresa
		])::exec();

		if($gestor->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $gestor->errorMessage());
			return false;
		}

		return count($gestor->data) > 0;
	}
	
	public static function buscarColaboradoresPorIdempresaEStatus($idEmpresa, $status, $toFillSelect = false) {
		$pessoas = SQL::ini(PessoaQuery::buscarColaboradoresPorIdempresaEStatus(), [
			'status' => $status,
			'idempresa' => $idEmpresa
		])::exec();

		if($pessoas->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
			return [];
		}
		$arrRetorno = [
			'' => 'Selecionar CEO'
		];

		if($toFillSelect) {
			foreach($pessoas->data as $item) {
				$arrRetorno[$item['idpessoa']] = $item['nome'];
			}

			return $arrRetorno;
		}

		return $pessoas->data;
	}

	public static function verficarPlantelPessoa($idpessoa,$idplantel) {
		$verificarPlantel = SQL::ini(PessoaQuery::verficarPlantelPessoa(), [
			'idpessoa' => $idpessoa,
			'idplantel' => $idplantel
		])::exec();

		if($verificarPlantel->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $verificarPlantel->errorMessage());
			return false;
		}

		return intval($verificarPlantel->data[0]['verificarplantel']) > 0 ? true : false;
	}
}
?>
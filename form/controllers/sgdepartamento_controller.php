<?

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/sgdepartamento_query.php");
require_once(__DIR__."/../querys/sgsetor_query.php");
require_once(__DIR__."/../querys/sgarea_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");
require_once(__DIR__."/../querys/contaitem_query.php");
require_once(__DIR__."/../querys/unidadeobjeto_query.php");
require_once(__DIR__."/../querys/_lp_query.php");
require_once(__DIR__."/../querys/colaboradorhistorico_query.php");
require_once(__DIR__."/../querys/unidade_query.php");
require_once(__DIR__."/../querys/objetovinculo_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class SgDepartamentoController extends Controller
{
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];

    public static $grupo = [
        'Y' => 'SIM',
        'N' => 'NÃO'
    ];


    public static function buscarPorChavePrimaria($idSgdepartamento, $status, $autocomplete = false)
    {
        $departamentos = SQL::ini(SgDepartamentoQuery::buscarPorChavePrimaria(), [
            'pkval' => $idSgdepartamento,
            'status' => $status
        ])::exec();

        if($departamentos->error()){
            parent::error(__CLASS__, __FUNCTION__, $departamentos->errorMessage());
            return [];
        }

        if($autocomplete)
		{
			$arrRetorno = [];

			foreach($departamentos->data as $key => $departamento)
			{
				$arrRetorno[$key]['label'] = $departamento['departamento'];
				$arrRetorno[$key]['value'] = $departamento['idsgdepartamento'];
			}

			return $arrRetorno;
		}

        return $departamentos->data[0];
    }

    public static function carregarLps($idSgDepartamento)
    {
        $lps = SQL::ini(SgDepartamentoQuery::buscarLpGrupoPorIdSgDepartamento(), [
            'idsgdepartamento' => $idSgDepartamento
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function carregarSetores($idSgDepartamento)
    {
        $setores = SQL::ini(SgsetorQuery::buscarSetoresPorIdSgDepartamento(), [
            'idsgdepartamento' => $idSgDepartamento
        ])::exec();

        if($setores->error()){
            parent::error(__CLASS__, __FUNCTION__, $setores->errorMessage());
            return [];
        }

        return $setores->data;
    }

    public static function carregarFuncionarios($idSgDepartamento)
    {
        $funcionarios = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjetoComPessoaResponsavel(), [
            'idobjeto' => $idSgDepartamento,
            'tipoobjeto' => 'sgdepartamento'
        ])::exec();

        if($funcionarios->error()){
            parent::error(__CLASS__, __FUNCTION__, $funcionarios->errorMessage());
            return [];
        }

        return $funcionarios->data;
    }

    public static function carregarGrupoES($idSgDepartamento)
    {
        $grupoES = SQL::ini(SgDepartamentoQuery::buscarGrupoESPorIdSgDepartamento(), [
            'idsgdepartamento' => $idSgDepartamento
        ])::exec();

        if($grupoES->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupoES->errorMessage());
            return [];
        }

        return $grupoES->data;
    }


    public static function buscarUnidadeDoIdSgDepartamentoEIdEmpresa($idSgDepartamento, $idEmpresa)
    {
        $idEmpresa = "AND u.idempresa = $idEmpresa";

        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadeDoIdSgDepartamentoEIdEmpresa(), [
            'idsgdepartamento' => $idSgDepartamento,
            'idempresa' => $idEmpresa
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        $idEmpresa = cb::idempresa();

        $unidadeArrRetorno = [];
        $unidadeArrRetorno['centrocusto'] = array_filter($unidades->data, function($item) use($idEmpresa) {
            return $item['idempresa'] == $idEmpresa;
        });
        $unidadeArrRetorno['solicitacoes'] = array_filter($unidades->data, function($item) use($idEmpresa) {
            return $item['idempresa'] != $idEmpresa;
        });

        return $unidadeArrRetorno;
    }

    public static function buscarUnidadeEnviocusto($idunidade){

      

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadeEnviocusto(), [
            'idunidade' => $idunidade
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

       
        return $unidades->data;
    }

    public static function buscarUnidadeRecebecusto($idunidade){

      

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadeRecebecusto(), [
            'idunidade' => $idunidade
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

       
        return $unidades->data;
    }
    

    public static function buscarUnidadeVinculadaIdSgDepartamentoEIdEmpresa($idSgDepartamento, $idEmpresa)
    {
        //$idEmpresa = "AND u.idempresa = $idEmpresa";
        $idEmpresa = "";

        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadeVinculadaIdSgDepartamentoEIdEmpresa(), [
            'idsgdepartamento' => $idSgDepartamento,
            'idempresa' => $idEmpresa
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        $idEmpresa = cb::idempresa();

        $unidadeArrRetorno = [];
        $unidadeArrRetorno['centrocusto'] = array_filter($unidades->data, function($item) use($idEmpresa) {
            return $item['idempresa'] == $idEmpresa;
        });
        $unidadeArrRetorno['solicitacoes'] = array_filter($unidades->data, function($item) use($idEmpresa) {
            return $item['idempresa'] != $idEmpresa;
        });

        return $unidadeArrRetorno;
    }


    public static function carregarUnidades($idSgDepartamento, $idEmpresa)
    {
        $idEmpresa = "AND u.idempresa = $idEmpresa";

        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadesPorIdSgDepartamentoEIdEmpresa(), [
            'idsgdepartamento' => $idSgDepartamento,
            'idempresa' => ''
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        $idEmpresa = cb::idempresa();

        $unidadeArrRetorno = [];
        $unidadeArrRetorno['centrocusto'] = array_filter($unidades->data, function($item) use($idEmpresa) {
            return $item['idempresa'] == $idEmpresa;
        });
        $unidadeArrRetorno['solicitacoes'] = array_filter($unidades->data, function($item) use($idEmpresa) {
            return $item['idempresa'] != $idEmpresa;
        });

        return $unidadeArrRetorno;
    }

    public static function carregarHistoricoColaborador($idSgDepartamento)
    {
        $historico = SQL::ini(ColaboradorHistoricoQuery::buscarHistoricoPorIdObjetoTipoObjeto(), [
            'idobjeto' => $idSgDepartamento,
            'tipoobjeto' => 'departamento'
        ])::exec();

        if($historico->error()){
            parent::error(__CLASS__, __FUNCTION__, $historico->errorMessage());
            return [];
        }

        return $historico->data;
    }

    public static function carregarSetoresDisponiveisParaVinculo($idSgDepartamento)
    {
        $setores = SQL::ini(SgsetorQuery::buscarSetoresDiponiveisParaVinculoPorIdSgDepartamento(), [
            'idsgdepartamento' => $idSgDepartamento,
            'getidempresa' => getidempresa('ss.idempresa', 'sgsetor')
        ])::exec();

        if($setores->error()){
            parent::error(__CLASS__, __FUNCTION__, $setores->errorMessage());
            return [];
        }

        return $setores->data;
    }

    public static function carregarPessoasDisponiveisParaVinculo($idEmpresa)
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

    public static function buscarContaItensDisponiveisParaVinculo($idSgDepartamento,$idEmpresa)
    {
        $contaItens = SQL::ini(ContaItemQuery::buscarContaItensDisponiveisParaVinculoPorIdSgDepartamento(), [
            'idsgdepartamento' => $idSgDepartamento,
            'idempresa' => $idEmpresa
        ])::exec();

        if($contaItens->error()){
            parent::error(__CLASS__, __FUNCTION__, $contaItens->errorMessage());
            return [];
        }

        return $contaItens->data;
    }

    public static function buscarUnidadesDisponiveisParaVinculo($idSgDepartamento, $idEmpresa)
    {
        $idEmpresa = "AND u.idempresa = $idEmpresa";
/*
        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }
*/
        if(cb::idempresa() == 8 || cb::idempresa() == 25){
            $filtroempresa=" ";
        }else{
            $filtroempresa= " AND NOT EXISTS (
                SELECT 1
                FROM unidadeobjeto uo 
                WHERE uo.idunidade = u.idunidade
                AND uo.tipoobjeto IN('sgsetor','sgdepartamento','sgarea','sgconselho')
            )";
        }
        

        $unidades = SQL::ini(UnidadeQuery::buscarUnidadesDisponiveisParaVinculo(), [
            'idempresa' => $idEmpresa,
            'filtroempresa' => $filtroempresa
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }

     public static function buscarLpsDisponiveisParaVinculo($idSgDepartamento,$idempresa)
    {
        $lps = SQL::ini(_LpQuery::buscarLpsDisponiveisParaVinculoPorIdSgDepartamento(), [
            'idsgdepartamento' => $idSgDepartamento,
            'idempresa' => $idempresa
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function carregarAreasDisponiveisParaVinculo($idEmpresa)
    {
        $arrRetorno = [];
        $areas = SQL::ini(SgAreaQuery::buscarSgAreaPorIdempresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        foreach($areas->data as $area)
        {
            $arrRetorno[$area['idsgarea']] = $area['area'];
        }

        return $arrRetorno;
    }

    public static function buscarDepartamentosPorIdEmpresa($idEmpresa, $autocomplete = false)
    {
        $departamentos = SQL::ini(SgDepartamentoQuery::buscarSgDepartamentoPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($departamentos->error()){
            parent::error(__CLASS__, __FUNCTION__, $departamentos->errorMessage());
            return [];
        }

        if($autocomplete)
		{
			$arrRetorno = [];

			foreach($departamentos->data as $key => $departamento)
			{
				$arrRetorno[$key]['label'] = $departamento['departamento'];
				$arrRetorno[$key]['value'] = $departamento['idsgdepartamento'];
			}

			return $arrRetorno;
		}

        return $departamentos->data;
    }

    public static function buscarSgArea($idSgDepartamento)
    {
        $area = SQL::ini(ObjetoVinculoQuery::buscarPaiPorIdObjetoETipoObjeto(), [
            'tipoobjetopai' => 'sgarea',
            'tipoobjeto' => 'sgdepartamento',
            'idobjeto' => $idSgDepartamento
        ])::exec();

        if($area->error()){
            parent::error(__CLASS__, __FUNCTION__, $area->errorMessage());
            return [];
        }

        return $area->data[0];
    }

    public static function buscarDepartamentoSgDepartamentoPorIdSgDepartamento($idsgdepartamento)
    {
        $area = SQL::ini(SgDepartamentoQuery::buscarDepartamentoSgDepartamentoPorIdSgDepartamento(), [
            'idsgdepartamento' => $idsgdepartamento
        ])::exec();

        if($area->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $area->errorMessage());
            return "";
        } else {
            return $area->data[0];
        }
    }

    public static function listarUnidadesDisponiveisParaVinculo($idSgDepartamento, $idEmpresa)
    {
        $idEmpresa = "AND u.idempresa = $idEmpresa";
/*
        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }
*/
        $unidades = SQL::ini(UnidadeQuery::listarUnidadesDisponiveisParaVinculo(), [
            'idempresa' => '',
            'idsgdepartamento'  =>$idSgDepartamento
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }
        //return parent::toFillSelect($unidades->data);
        return $unidades->data;
   }

   public static function buscarUnidadeVinculadaIdSgDepartamentoSetor($idsgsetor)
    {
        $results = SQL::ini(UnidadeObjetoQuery::buscarUnidadeVinculadaIdSgDepartamentoSetor(), [          
            "idsgsetor"=>$idsgsetor           
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return  $results->data;
        }

    }



   public static function buscarUnidadeVinculadaIdSgDepartamento($idunidade,$idsgdepartamento)
   {
      

       $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadeVinculadaIdSgDepartamento(), [
           'idunidade' => $idunidade,
           'idsgdepartamento' => $idsgdepartamento
       ])::exec();

       if($unidades->error()){
           parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
           return [];
       }

       return $unidades->data;
   }

   
   public static function deletaUnidadeobjeto($idunidadeobjeto)
   {
        $unidades = SQL::ini(UnidadeObjetoQuery::deletaUnidadeobjeto(), [
           'idunidadeobjeto' => $idunidadeobjeto
       ])::exec();

       if($unidades->error()){
           parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
           return [];
       }

       return $unidades->data;
   }
   
   public static function buscarUnidadesDisponiveisParaVinculoEnvio( $idEmpresa)
   {
       $idEmpresa = "AND u.idempresa = $idEmpresa";

       $unidades = SQL::ini(UnidadeQuery::buscarUnidadesDisponiveisParaVinculoEnvio(), [
           'idempresa' => $idEmpresa
       ])::exec();

       if($unidades->error()){
           parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
           return [];
       }

       return $unidades->data;
   }


}
?>
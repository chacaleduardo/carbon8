<?
// QUERYS
require_once(__DIR__."/../querys/lpobjeto_query.php");
require_once(__DIR__."/../querys/sgdepartamento_query.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");
require_once(__DIR__."/../querys/sgsetor_query.php");
require_once(__DIR__."/../querys/_lp_query.php");
require_once(__DIR__."/../querys/sgconselho_query.php");
require_once(__DIR__."/../querys/unidadeobjeto_query.php");
require_once(__DIR__."/../querys/colaboradorhistorico_query.php");
require_once(__DIR__ ."/../querys/log_query.php");
require_once(__DIR__ ."/../querys/objetovinculo_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class SgareaController extends Controller
{
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];
    public static $grupo = [
        'Y' => 'SIM',
        'N' => 'NÃO'
    ];

    public static function buscarPorChavePrimaria($idSgarea, $status, $autocomplete = false)
    {
        $areas = SQL::ini(SgareaQuery::buscarPorChavePrimaria(), [
            'pkval' => $idSgarea,
            'status' => $status
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        if($autocomplete)
		{
			$arrRetorno = [];

			foreach($areas->data as $key => $area)
			{
				$arrRetorno[$key]['label'] = $area['area'];
				$arrRetorno[$key]['value'] = $area['idsgarea'];
			}

			return $arrRetorno;
		}

        return $areas->data[0];
    }

    public static function buscarLpsPorIdSgarea($idSgarea)
    {
        $lps = SQL::ini(LpObjetoQuery::buscarLpsPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgarea,
            'tipoobjeto' => 'sgarea',
            'getidempresa' => getidempresa('lo.idempresa','lpobjeto')
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarCoordenadoresPorIdSgArea($idSgarea)
    {
        $coordenadores = SQL::ini(PessoaobjetoQuery::buscarCoodenadoresPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgarea,
            'tipoobjeto' => 'sgarea',
            'getidempresa' => getidempresa('f.idempresa','pessoaobjeto')
        ])::exec();

        if($coordenadores->error()){
            parent::error(__CLASS__, __FUNCTION__, $coordenadores->errorMessage());
            return [];
        }

        return $coordenadores->data;
    }

    public static function buscarDepartamentosPorIdSgarea($idSgarea)
    {
        $departamentos = SQL::ini(SgDepartamentoQuery::buscarSgDepartamentoPorIdSgareaEGetIdEmpresa(), [
            'idsgarea' => $idSgarea
        ])::exec();

        if($departamentos->error()){
            parent::error(__CLASS__, __FUNCTION__, $departamentos->errorMessage());
            return [];
        }

        return $departamentos->data;
    }

    public static function buscarSetoresDisponiveisParaVinculoPorIdSgarea($idSgarea)
    {
        $setores = SQL::ini(SgsetorQuery::buscarSetoresDiponiveisParaVinculoPorIdSgareaEGetIdEmpresa(), [
            'idsgarea' => $idSgarea,
            'getidempresa' => getidempresa('s.idempresa','sgsetor')
        ])::exec();

        if($setores->error()){
            parent::error(__CLASS__, __FUNCTION__, $setores->errorMessage());
            return [];
        }

        return $setores->data;
    }

    public static function buscarDepartamentosDisponiveisParaVinculoPorIdSgarea($idSgarea)
    {
       $departamentos = SQL::ini(SgdepartamentoQuery::buscarDepartamentosDisponiveisParaVinculoPorIdSgareaEGetIdEmpresa(), [
            'idsgarea' => $idSgarea,
            'getidempresa' => getidempresa('sgdep.idempresa','sgdepartamento')
       ])::exec();

       if($departamentos->error()){
            parent::error(__CLASS__, __FUNCTION__, $departamentos->errorMessage());
            return [];
        }

        return $departamentos->data;
    }

    public static function buscarLpsDisponiveisParaVinculoPorIdSgarea($idSgarea)
    {
        $idEmpresa = "AND lp.idempresa =".cb::idempresa();

        $lps = SQL::ini(_LpQuery::buscarLpsDisponiveisParaVinculoPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgarea,
            'tipoobjeto' => 'sgarea',
            'getidempresa' =>  $idEmpresa
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarUnidadesDisponiveisParaVinculoPorIdSgarea($idSgarea, $idEmpresa)
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
            'idempresa' => '',
            'filtroempresa' => $filtroempresa
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }

    public static function buscarConselhosPorIdEmpresa($idEmpresa)
    {
        $arrRetorno = [];
        $conselhos = SQL::ini(SgConselhoQuery::buscarConselhosPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($conselhos->error()){
            parent::error(__CLASS__, __FUNCTION__, $conselhos->errorMessage());
            return [];
        }

        foreach($conselhos->data as $conselho)
        {
            $arrRetorno[$conselho['idsgconselho']] = $conselho['conselho'];
        }

        return $arrRetorno;
    }

    public static function buscarUnidadesPorIdSgareaEIdempresa($idSgarea, $idEmpresa)
    {
       // $idEmpresa = "AND u.idempresa = $idEmpresa";

     /*   if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }
*/
        
        $idEmpresa = "";

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadesPorIdSgareaEIdempresa(), [
            'idsgarea' => $idSgarea,
            'idempresa' => ""
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

    public static function buscarUnidadesPorIdSgareaEIdempresaPadrao($idSgarea, $idEmpresa)
    {
        $idEmpresa = "AND u.idempresa = $idEmpresa";
  /*
        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }
*/
        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadesPorIdSgareaEIdempresaPadrao(), [
            'idsgarea' => $idSgarea,
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

    public static function buscarHistoricoPorIdSgarea($idSgarea)
    {
        $areas = SQL::ini(ColaboradorHistoricoQuery::buscarHistoricoPorIdObjetoTipoObjeto(), [
            'idobjeto' => $idSgarea,
            'tipoobjeto' => 'area'
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        return $areas->data;
    }

    public static function atualizarVinculoComSgconselhoPorIdSgareaEIdSgconselho($idSgarea, $idSgconselho)
    {
        $objetoVinculo = SQL::ini(ObjetoVinculoQuery::buscarPorTipoObjetOIdObjetoVincTipoObjetoVinc(), [
            'tipoobjeto' => 'sgconselho',
            'idobjetovinc' => $idSgarea,
            'tipoobjetovinc'=> 'sgarea'
        ])::exec();

        if($objetoVinculo->error()){
            parent::error(__CLASS__, __FUNCTION__, $objetoVinculo->errorMessage());

            return [];
        }

        if($objetoVinculo->numRows())
        {
            return $atualizandoObjetoVinculo = SQL::ini(ObjetoVinculoQuery::atualizarIdobjetoTipoObjetoPorIdObjetoVinculo(), [
                'idobjetovinculo' => $objetoVinculo->data[0]['idobjetovinculo'],
                'idobjeto' => $idSgconselho
            ])::exec();
        }

        $inserindoObjetoVinculo = SQL::ini(ObjetoVinculoQuery::inserirObjetoVinculo(), [
            'idobjeto' => $idSgconselho,
            'tipoobjeto' => 'sgconselho',
            'idobjetovinc' => $idSgarea,
            'tipoobjetovinc' => 'sgarea',
            'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
            'criadoem' => "NOW()",
            'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
            'alteradoem' => "NOW()",
        ])::exec();
    }

    public static function buscarAreasPorIdEmpresa($idEmpresa, $autocomplete = false)
    {
        $areas = SQL::ini(SgareaQuery::buscarAreasPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        if($autocomplete)
		{
			$arrRetorno = [];

			foreach($areas->data as $key => $area)
			{
				$arrRetorno[$key]['label'] = $area['area'];
				$arrRetorno[$key]['value'] = $area['idsgarea'];
			}

			return $arrRetorno;
		}

        return $areas->data;
    }

    public static function buscarSgConselho($idSgArea)
    {
        $conselho = SQL::ini(ObjetoVinculoQuery::buscarPaiPorIdObjetoETipoObjeto(), [
            'tipoobjetopai' => 'sgconselho',
            'tipoobjeto' => 'sgarea',
            'idobjeto' => $idSgArea
        ])::exec();

        if($conselho->error()){
            parent::error(__CLASS__, __FUNCTION__, $conselho->errorMessage());
            return [];
        }

        return $conselho->data[0];
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

  public static function buscarPessoasPorIdResponsavel($idPessoa)
    {
        $pessoas = SQL::ini(PessoaobjetoQuery::buscarPessoasPorIdResponsavel(), [
            'idpessoa' => $idPessoa
        ])::exec();

        if ($pessoas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }
}
?>
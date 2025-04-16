<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/lpobjeto_query.php");
require_once(__DIR__."/../querys/sgarea_query.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");
require_once(__DIR__."/../querys/unidade_query.php");
require_once(__DIR__."/../querys/unidadeobjeto_query.php");
require_once(__DIR__."/../querys/_lp_query.php");
require_once(__DIR__."/../querys/colaboradorhistorico_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class SgconselhoController extends Controller
{
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];
    public static $grupo = [
        'Y' => 'SIM',
        'N' => 'NÃO'
    ];
    public static function buscarPorChavePrimaria($idSgconselho)
    {
        $conselho = SQL::ini(SgConselhoQuery::buscarPorChavePrimaria(), [
            'pkval' => $idSgconselho
        ])::exec();

        if($conselho->error()){
            parent::error(__CLASS__, __FUNCTION__, $conselho->errorMessage());
            return [];
        }

        return $conselho->data[0];
    }

    public static function buscarLpsPorIdSgconselho($idSgconselho)
    {
        $lps = SQL::ini(LpObjetoQuery::buscarLpsPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgconselho,
            'tipoobjeto' => 'sgconselho',
            'getidempresa' => getidempresa('lo.idempresa','lpobjeto')
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarCoordenadoresPorIdSgconselho($idSgconselho)
    {
        $coordenadores = SQL::ini(PessoaobjetoQuery::buscarCoodenadoresPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgconselho,
            'tipoobjeto' => 'sgconselho',
            'getidempresa' => getidempresa('f.idempresa','pessoaobjeto')
        ])::exec();

        if($coordenadores->error()){
            parent::error(__CLASS__, __FUNCTION__, $coordenadores->errorMessage());
            return [];
        }

        return $coordenadores->data;
    }

    public static function buscarAreasPorIdSgconselho($idSgconselho)
    {
        $areas = SQL::ini(SgareaQuery::buscarSgareaPorIdSgconselho(), [
            'idsgconselho' => $idSgconselho
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        return $areas->data;
    }

    public static function buscarLpsDisponiveisParaVinculoPorIdSgconselho($idSgconselho)
    {
        $idEmpresa = "AND lp.idempresa =".cb::idempresa();

        $lps = SQL::ini(_LpQuery::buscarLpsDisponiveisParaVinculoPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgconselho,
            'tipoobjeto' => 'sgconselho',
            'getidempresa' => $idEmpresa
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarUnidadesDisponiveisParaVinculoPorIdSconselho($idSgconselho, $idEmpresa)
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

    public static function buscarUnidadesPorIdSgconselhoEIdEmpresa($idSgconselho, $idEmpresa)
    {
        //$idEmpresa = "AND u.idempresa = $idEmpresa";
        $idEmpresa = "";
/*
        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }
*/
        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadesPorIdSgconselhoEIdEmpresa(), [
            'idobjeto' => $idSgconselho,
            'tipoobjeto' => 'sgconselho',
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

    public static function buscarHistoricoPorIdSgconselho($idSgconselho)
    {
        $historico = SQL::ini(ColaboradorHistoricoQuery::buscarHistoricoPorIdObjetoTipoObjeto(), [
            'idobjeto' => $idSgconselho,
            'tipoobjeto' => 'conselho'
        ])::exec();

        if($historico->error()){
            parent::error(__CLASS__, __FUNCTION__, $historico->errorMessage());
            return [];
        }

        return $historico->data;
    }

    public static function buscarAreasDisponiveisParaVinculoPorIdSgconselho($idSgconselho)
    {
        $areas = SQL::ini(SgAreaQuery::buscarAreasDisponiveisParaVinculoPorIdSgconselho(), [
            'idsgconselho' => $idSgconselho,
            'getidempresa' => getidempresa('a.idempresa', 'sgarea')
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        return $areas->data;
    }

    public static function buscarUnidadesPorIdsgconselhoEIdempresaPadrao($idsgconselho, $idEmpresa)
    {
        $idEmpresa = "AND u.idempresa = $idEmpresa";
    /*
        if(cb::idempresa() == 8 || cb::idempresa() == 12)
        {
            $idEmpresa = "";
        }
    */
        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadesPorIdsgconselhoEIdempresaPadrao(), [
            'idsgconselho' => $idsgconselho,
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
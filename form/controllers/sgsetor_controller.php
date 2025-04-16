<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");
require_once(__DIR__."/../querys/sgsetor_query.php");
require_once(__DIR__."/../querys/_lp_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/unidade_query.php");
require_once(__DIR__."/../querys/unidadeobjeto_query.php");
require_once(__DIR__."/../querys/lpobjeto_query.php");
require_once(__DIR__."/../querys/sgdepartamento_query.php");
require_once(__DIR__."/../querys/vinculos_query.php");
require_once(__DIR__."/../querys/colaboradorhistorico_query.php");
require_once(__DIR__."/../querys/imgrupo_query.php");
require_once(__DIR__."/../querys/log_query.php");
require_once(__DIR__."/../querys/objetovinculo_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class SgSetorController extends Controller
{
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];

    public static $grupo = [
        'Y' => 'SIM',
        'N' => 'NÃO'
    ];

    public static function buscarPorChavePrimaria($idSgsetor, $status, $autocomplete = false)
    {
        $setores = SQL::ini(SgsetorQuery::buscarPorChavePrimaria(), [
            'pkval' => $idSgsetor,
            'status' => $status
        ])::exec();

        if($setores->error()){
            parent::error(__CLASS__, __FUNCTION__, $setores->errorMessage());
            return [];
        }

        if($autocomplete)
		{
			$arrRetorno = [];

			foreach($setores->data as $key => $setor)
			{
				$arrRetorno[$key]['label'] = $setor['setor'];
				$arrRetorno[$key]['value'] = $setor['idsgsetor'];
			}

			return $arrRetorno;
		}

        return $setores->data;
    }

    public static function buscarFuncionariosPorIdSgSetor($idSgSetor)
    {
        $funcionarios = SQL::ini(PessoaobjetoQuery::buscarFuncionariosPorIdSgSetor(), [
            'idsgsetor' => $idSgSetor
        ])::exec();

        if($funcionarios->error()){
            parent::error(__CLASS__, __FUNCTION__, $funcionarios->errorMessage());
            return [];
        }

        return $funcionarios->data;
    }

    public static function buscarCoordenadoresPorIdSgSetor($idSgSetor)
    {
        $coordenadores = SQL::ini(PessoaobjetoQuery::buscarCoodenadoresPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgSetor,
            'tipoobjeto' => 'sgsetor',
            'getidempresa' => getidempresa('f.idempresa','pessoaobjeto')
        ])::exec();

        if($coordenadores->error()){
            parent::error(__CLASS__, __FUNCTION__, $coordenadores->errorMessage());
            return [];
        }

        return $coordenadores->data;
    }

    public static function buscarPessoasPorIdSgSetor($idSgSetor)
    {
        $pessoas = SQL::ini(SgsetorQuery::buscarPessoasPorIdSgSetor(), [
            'idsgsetor' => $idSgSetor
        ])::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }

    public static function buscarGruposPorIdSgSetor($idSgSetor)
    {
        $grupos = SQL::ini(SgsetorQuery::buscarGruposPorIdSgSetor(), [
            'idsgsetor' => $idSgSetor
        ])::exec();

        if($grupos->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupos->errorMessage());
            return [];
        }

        return $grupos->data;
    }

    public static function buscarLpsDisponiveisParaVinculoPorIdSgSetor($idSgSetor)
    {

      
        $idEmpresaQuery = "AND p.idempresa =".cb::idempresa();

        $lps = SQL::ini(_LpQuery::buscarLpsDisponiveisParaVinculoPorIdSgSetorEGetIdEmpresa(), [
            //'getidempresa' => getidempresa('p.idempresa', _DBCARBON.'_lp'),
            'getidempresa' => $idEmpresaQuery,
            'idsgsetor' => $idSgSetor
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
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

    public static function buscarUnidadesDisponiveisParaVinculoPorIdSgsetorEIdEmpresa($idSgsetor, $idEmpresa)
    {
        $idEmpresaQuery = "AND u.idempresa = $idEmpresa";
        $where = "WHERE ov.idobjetovinc = $idSgsetor";
/*
        if(cb::idempresa() == 8 || cb::idempresa() == 12 || cb::idempresa() == 15 || cb::idempresa() == 20)
            $idEmpresaQuery = "";
*/
        if(cb::idempresa() == 22)
            $idEmpresaQuery = "AND u.idempresa in ($idEmpresa, 1)";

        $unidades = SQL::ini(UnidadeQuery::buscarUnidadesDisponiveisParaVinculoPorIdSgsetorEIdEmpresa(), [
            'where' => $where,
             'idempresa' => ''
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }
    
    public static function buscarLpsPorIdSgSetor($idSgsetor)
    {
        $lps = SQL::ini(LpObjetoQuery::buscarLpsPorIdObjetoTipoObjetoEGetIdEmpresa(), [
            'idobjeto' => $idSgsetor,
            'tipoobjeto' => 'sgsetor',
            'getidempresa' => getidempresa('lo.idempresa','lpobjeto')
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarGruposDeChatPorIdSgsetor($idSgsetor)
    {
        $gruposDeChat = SQL::ini(SgsetorQuery::buscarGruposDeChatPorIdSgsetor(), [
            'idsgsetor' => $idSgsetor
        ])::exec();

        if($gruposDeChat->error()){
            parent::error(__CLASS__, __FUNCTION__, $gruposDeChat->errorMessage());
            return [];
        }

        return $gruposDeChat->data;
    }

    public static function buscarSgdepartamentoPorGetIdEmpresa()
    {
        $arrRetorno = [];
        $departametos = SQL::ini(SgDepartamentoQuery::buscarSgDepartamentoPorGetIdEmpresa(), [
            'getidempresa' => getidempresa('idempresa','sgdepartamento')
        ])::exec();

        if($departametos->error()){
            parent::error(__CLASS__, __FUNCTION__, $departametos->errorMessage());
            return [];
        }

        foreach($departametos->data as $departamento)
        {
            $arrRetorno[$departamento['idsgdepartamento']] = $departamento['departamento'];
        }

        return $arrRetorno;
    }

    public static function buscarUnidadesPorIdSgsetorEIdEmpresa($idSgsetor, $idEmpresa,$padrao ='Y')
    {
        $idEmpresaQuery = "AND u.idempresa = $idEmpresa";
/*
        if(cb::idempresa() == 8 || cb::idempresa() == 12 || cb::idempresa() == 15 || cb::idempresa() == 20)
        {
            $idEmpresaQuery = "";
        }
*/
        if(cb::idempresa() == 22)
            $idEmpresaQuery = "AND u.idempresa in ($idEmpresa, 1)";

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarUnidadesPorIdSgsetorEIdEmpresa(), [
            'idsgsetor' => $idSgsetor,
            'idempresa' => '',
            'padrao'=>$padrao
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
    

    public static function buscarHistoricoPorIdSgsetor($idSgsetor)
    {
        $historico = SQL::ini(ColaboradorHistoricoQuery::buscarHistoricoPorIdObjetoTipoObjeto(), [
            'idobjeto' => $idSgsetor,
            'tipoobjeto' => 'setor'
        ])::exec();

        if($historico->error()){
            parent::error(__CLASS__, __FUNCTION__, $historico->errorMessage());
            return [];
        }

        return $historico->data;
    }

    public static function atualizarGrupos($status, $regra = false)
    {
        if($regra)
        {
        
            $sql="update imgrupo set status = '".$status."' where idobjetoext = ".$regra.";";
        
            $res=d::b()->query($sql) or die("[poschange_sgsetor] -Erro ao gerar LP : " . mysqli_error(d::b()) . "<p>SQL:".$sql); 

            $gerandoLp = SQL::ini(ImGrupoQuery::atualizarStatusPorIdObjetoExt(), [
                    'idobjetoext' => $regra,
                    'status' => $status
            ]);
        }
         
        $atualizandoStatusDosGruposDeSetores = SQL::ini(ImGrupoQuery::atualizarStatusDeGruposDeSetores())::exec();
        $inativarStatusDeGruposDesfeitosDeSetores = SQL::ini(ImGrupoQuery::inativarStatusDeGruposDesfeitosDeSetores())::exec();
        $atualizarNomeDosGruposDeAcordoComOSetor = SQL::ini(ImGrupoQuery::atualizarNomeDosGruposDeAcordoComOSetor())::exec();
        
        if($atualizandoStatusDosGruposDeSetores->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoStatusDosGruposDeSetores->errorMessage());
        }

        if($inativarStatusDeGruposDesfeitosDeSetores->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $inativarStatusDeGruposDesfeitosDeSetores->errorMessage());
        }

        if($atualizarNomeDosGruposDeAcordoComOSetor->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $atualizarNomeDosGruposDeAcordoComOSetor->errorMessage());
        }
    }

    public static function buscarSetoresPorIdEmpresa($idEmpresa, $autocomplete = false)
    {
        $setores = SQL::ini(SgsetorQuery::buscarSgSetorPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($setores->error()){
            parent::error(__CLASS__, __FUNCTION__, $setores->errorMessage());
            return [];
        }

        if($autocomplete)
		{
			$arrRetorno = [];

			foreach($setores->data as $key => $setor)
			{
				$arrRetorno[$key]['label'] = $setor['setor'];
				$arrRetorno[$key]['value'] = $setor['idsgsetor'];
			}

			return $arrRetorno;
		}

        return $setores->data;
    }

    public static function buscarSgDepartamento($idSgSetor)
    {
        $departamento = SQL::ini(ObjetoVinculoQuery::buscarPaiPorIdObjetoETipoObjeto(), [
            'tipoobjetopai' => 'sgdepartamento',
            'tipoobjeto' => 'sgsetor',
            'idobjeto' => $idSgSetor
        ])::exec();

        if($departamento->error()){
            parent::error(__CLASS__, __FUNCTION__, $departamento->errorMessage());
            return [];
        }

        return $departamento->data[0];
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
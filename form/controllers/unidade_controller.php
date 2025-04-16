<?

// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/unidade_query.php");
require_once(__DIR__."/../../form/querys/tipounidade_query.php");
require_once(__DIR__."/../../form/querys/centrocusto_query.php");
require_once(__DIR__."/../../form/querys/unidadeobjeto_query.php");
require_once(__DIR__."/../../form/querys/log_query.php");
require_once(__DIR__."/../../form/querys/rateioitemdest_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/tagtipo_controller.php");

class UnidadeController extends Controller
{   
    public static $status = [
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo'
    ];

    public static $valorSimNao = [
        'N' => 'Não',
        'Y' => 'Sim' 
    ];

    public static $tipocusto = [
        'CI' => 'Custo Indireto',
        'CD' => 'Custo Direto' 
    ];

    public static function buscarPorChavePrimaria($idUnidade)
    {
        $unidade = SQL::ini(UnidadeQuery::buscarPorChavePrimaria(), [
            'pkval' => $idUnidade
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        }

        return $unidade->data[0];
    }

    public static function buscarModuloDaUnidadePorIdunidade($idUnidade)
    {
        $modulo = SQL::ini(UnidadeQuery::buscarModuloDaUnidadePorIdunidade(), [
            'idunidade' => $idUnidade
        ])::exec();

        if($modulo->error()){
            parent::error(__CLASS__, __FUNCTION__, $modulo->errorMessage());
            return [];
        }

        return $modulo->data[0];
    }

    public static function buscarConselhosAreasDepsSetoresDisponiveisParaVinculo($idUnidade, $idObjeto = false, $tipoObjeto = false)
    {
        $where = '';
        $whereSetor = '';
        $getIdEmpresa = str_replace(' and ', '', getidempresa('e.idempresa', 'unidade'));

        if($getIdEmpresa)
        {
            $getIdEmpresa = " AND ($getIdEmpresa OR (e.idempresa IN(8, 12))) ";
        }

        if($idObjeto)
        {
            $where = "AND qry.id = $idObjeto ";
            $whereSetor = "AND idsgsetor = $idObjeto ";
        }

        if($tipoObjeto)
        {
            $where .= "AND qry.tipo = '$tipoObjeto'";
            $whereSetor .= "AND 'sgsetor' = '$tipoObjeto'";
        }

        $unidades = SQL::ini(UnidadeObjetoQuery::buscarConselhosAreasDepsSetoresDisponiveisParaVinculoPorIdUnidade(), [
            'where' => $where,
            'wheresetor' => $whereSetor,
            'idunidade' => $idUnidade,
            'getidempresa' => $getIdEmpresa
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }

    public static function buscarConselhosAreasDepsSetoresDisponiveisParaVinculoPorIdUnidade($idUnidade)
    {
        $unidades = SQL::ini(UnidadeQuery::buscarConselhosAreasDepsSetoresPorIdUnidade(), [
            'idunidade' => $idUnidade
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }

    public static function buscarTipos()
    {
        $arrRetorno = [];
        
        $tipos = SQL::ini(TipoUnidadeQuery::buscarTipos())::exec();

        if($tipos->error()){
            parent::error(__CLASS__, __FUNCTION__, $tipos->errorMessage());
            return [];
        }

        foreach($tipos->data as $tipo)
        {
            $arrRetorno[$tipo['idtipounidade']] = $tipo['tipounidade'];
        }

        return $arrRetorno;
    }

    public static function buscarCentroCusto()
    {
        $arrRetorno = [];
        
        $centrocusto = SQL::ini(CentroCustoQuery::buscarCentroCusto())::exec();

        if($centrocusto->error()){
            parent::error(__CLASS__, __FUNCTION__, $centrocusto->errorMessage());
            return [];
        }

        foreach($centrocusto->data as $cc)
        {
            $arrRetorno[$cc['idcentrocusto']] = $cc['centrocusto'];
        }

        return $arrRetorno;
    }
    public static function buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa)
    {
        $unidade = SQL::ini(UnidadeQuery::buscarIdunidadePorTipoUnidade(),[
            "idtipounidade" => $idtipounidade,
            "idempresa" => $idempresa
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        } else {
            return $unidade->data[0];
        }
    }

    public static function buscarIdunidadePorTipoUnidadeDescricao($idtipounidade, $idempresa,$unidade)
    {
        $unidade = SQL::ini(UnidadeQuery::buscarIdunidadePorTipoUnidadeDescricao(),[
            "idtipounidade" => $idtipounidade,
            "idempresa" => $idempresa,
            "unidade" => $unidade
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        } else {
            return $unidade->data[0];
        }
    }

    public static function buscarUnidadesAtivasPorIdEmpresa($idEmpresa, $toFillSelect = false)
    {
        $result = SQL::ini(UnidadeQuery::buscarUnidadesAtivasPorIdEmpresa(),['cbidempresa' => $idEmpresa])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }

        if($toFillSelect){
            $arrRetorno = [];

            foreach($result->data as $result){
                $arrRetorno[$result['idunidade']] = $result['unidade'];
            }

            return $arrRetorno;
        }

        return $result->data;
    }

    public static function deletarVinculoPorIdObjetoTipoObjeto($idObjeto, $tipoObjeto)
    {
        $deletandoUnidades = SQL::ini(UnidadeQuery::deletarUnidadesPorIdObjetoETipoObjeto(), [
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto
        ])::exec();

        if($deletandoUnidades->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $deletandoUnidades->errorMessage());

            return false;
        }

    }

    public static function listarFillSelectUnidadeAtivo($tipoobjetovinc, $tipoobjeto, $idobjeto, $idpessoa, $comprasMaster)
    {
        $grupoUnidades = self::buscarGruposConcatenadosUnidadeObjetoVinculo($tipoobjetovinc, $tipoobjeto, $idobjeto);
        $unidadePorIdUnidade = "";
        if(!empty($grupoUnidades['idunidades'])){
            $unidadePorIdUnidade = UnidadeQuery::buscarUnidadeAtivaPorIdUnidade()." UNION ";
        }
        
        $grupoConcatPessoaUnidade = self::buscarGrupoConcatPessoaUnidade($idpessoa);
        $whereUnidade = "";
        if($comprasMaster == 0 && !empty($grupoConcatPessoaUnidade['idunidade'])){
            $whereUnidade = "AND idunidade IN (".$grupoConcatPessoaUnidade['idunidade'].") AND u.idempresa = ".cb::idempresa();
        }

        $fillSelectUnidadeAtivo = SQL::ini($unidadePorIdUnidade.UnidadeQuery::listarFillSelectUnidadeAtivo(), [
            "idunidades" => $grupoUnidades['idunidades'],
            "whereUnidade" => $whereUnidade,
            "idempresa" => getidempresa("u.idempresa", "unidade"),
        ])::exec();

        if($fillSelectUnidadeAtivo->error()){
            parent::error(__CLASS__, __FUNCTION__, $fillSelectUnidadeAtivo->errorMessage());
            return "";
        } else {
            return parent::toFillSelect($fillSelectUnidadeAtivo->data);
        }
    }

    public static function buscarGruposConcatenadosUnidadeObjetoVinculo($tipoobjetovinc, $tipoobjeto, $idobjeto)
    {
        $grupoUnidades = SQL::ini(UnidadeQuery::buscarGruposConcatenadosUnidadeObjetoVinculo(),[
            "tipoobjetovinc" => $tipoobjetovinc,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "idempresa" => cb::idempresa()
        ])::exec();

        if($grupoUnidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupoUnidades->errorMessage());
            return [];
        } else {
            return $grupoUnidades->data[0];
        }
    }

    public static function buscarGrupoConcatPessoaUnidade($idpessoa)
    {
        $grupoUnidades = SQL::ini(UnidadeQuery::buscarGrupoConcatPessoaUnidade(),[
            "idpessoa" => $idpessoa
        ])::exec();

        if($grupoUnidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupoUnidades->errorMessage());
            return [];
        } else {
            return $grupoUnidades->data[0];
        }
    }

    public static function buscarGrupoUnidadePorTipoObjeto($idobjeto, $tipoobjeto)
    {
        $grupoUnidades = SQL::ini(UnidadeObjetoQuery::buscarGrupoUnidadePorTipoObjeto(),[
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if($grupoUnidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupoUnidades->errorMessage());
            return "";
        } else {
            return $grupoUnidades->data[0];
        }
    }

    public static function buscarUnidadesDisponiveisPorUnidadeObjeto($idobjeto, $tipoobjeto, $idempresa = NULL, $idtipounidade = NULL )
    {
        $result = (SQL::ini(UnidadeQuery::buscarUnidadesDisponiveisPorUnidadeObjeto(),[
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
            'andidempresa' => $idempresa,
            'andidtipounidade' => $idtipounidade
        ])::exec());
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarUnidadesPorUnidadeObjeto($idobjeto, $tipoobjeto, $idempresa = NULL, $idtipounidade = NULL )
    {
        $result = (SQL::ini(UnidadeQuery::buscarUnidadesPorUnidadeObjeto(),[
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
            'andidempresa' => $idempresa,
            'andidtipounidade' => $idtipounidade
        ])::exec());
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarUnidadeObjetoLoteModuloPorIdnfItem($idnfitem = NULL, $idlote = NULL)
    {
        if($idnfitem){
            $where = " WHERE l.idnfitem = $idnfitem ";
        } elseif($idlote){
            $where = " WHERE l.idlote = $idlote ";
        } 
        $result = (SQL::ini(UnidadeObjetoQuery::buscarUnidadeObjetoLoteModuloPorIdnfItem(),[
            'condicaoWhere' => $where
        ])::exec());
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarUnidadeObjetoLoteModuloPorIdnf($idnf = NULL, $idlote = NULL)
    {
        $result = null;

        if($idnf && $idlote == NULL){
            $where = " WHERE ni.idnf = $idnf ";

            $result = (SQL::ini(UnidadeObjetoQuery::buscarUnidadeObjetoLoteModuloPorIdnf(),[
                'condicaoWhere' => $where
            ])::exec());

        } elseif($idlote){
            $where = " WHERE l.idlote IN($idlote) AND ni.idnf = $idnf ";

            $result = (SQL::ini(UnidadeObjetoQuery::buscarUnidadeObjetoLoteModuloPorIdnfIdLote(),[
                'condicaoWhere' => $where
            ])::exec());
        }
        
        if($result && $result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }
        
        $arrNf = [];
        foreach($result->data as $lote)
        {
            $arrNf[$lote['idnfitem']][$lote['idlote']]['idlote'] = $lote['idlote'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['partida'] = $lote['partida'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['exercicio'] = $lote['exercicio'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['idobjeto'] = $lote['idobjeto'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['qtdprod'] = $lote['qtdprod'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['unlote'] = $lote['unlote'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['fabricante'] = $lote['fabricante'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['vencimento'] = $lote['vencimento'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['status'] = $lote['status'];
            $arrNf[$lote['idnfitem']][$lote['idlote']]['rotulo'] = $lote['rotulo'];
        }

        return $arrNf;
    }

    public static function buscarUnidadesPorTipoObjetoModulo($idprodserv, $idempresa = NULL)
    {
        $unidade = SQL::ini(UnidadeQuery::buscarUnidadesPorTipoObjetoModulo(), [
            "idprodserv" => $idprodserv,
            "idempresa" => $idempresa
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        } else {
            return $unidade->data;
        }        
    }


    public static function buscarUnidadesPorTipoObjeto($idobjeto, $tipoobjeto, $idempresa = NULL)
    {
        $unidade = SQL::ini(UnidadeQuery::buscarUnidadesPorTipoObjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "idempresa" => $idempresa
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        } else {
            return $unidade->data;
        }        
    }

    public static function buscarUnidadeObjetoPorTipoObjetoEIdUnidade($idunidade, $tipoobjeto, $modulotipo)
    {
        $unidade = SQL::ini(UnidadeObjetoQuery::buscarUnidadeObjetoPorTipoObjetoEIdUnidade(), [
            "tipoobjeto" => $tipoobjeto,
            "idunidade" => $idunidade,
            "modulotipo" => $modulotipo
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        } else {
            return $unidade->data[0];
        }        
    }

    public static function atualizarUnidadeNoRateioItemDest($idUnidade)
    {
        $unidade = self::buscarPorChavePrimaria($idUnidade);
        $unidadeDaEmpresa = self::buscarIdunidadePorTipoUnidade(28, $unidade['idempresa']);

        $atualizandoUnidadeNoRateioItem = SQL::ini(RateioItemDestQuery::atualizarUnidadeNoRateioItemDest(), [
            'idunidade' => $idUnidade,
            'novoidunidade' => $unidadeDaEmpresa['idunidade']
        ])::exec();

        if($atualizandoUnidadeNoRateioItem->error()){
            parent::error(__CLASS__, __FUNCTION__, $atualizandoUnidadeNoRateioItem->errorMessage());

            $dadosLog = [
                'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
                'sessao' => session_id(),
                'tipoobjeto' => 'unidade',
                'idobjeto' => $idUnidade,
                'tipolog' => 'saveprechange',
                'log' => 'Erro:' . $atualizandoUnidadeNoRateioItem->sql(),
                'status' => '',
                'info' => $atualizandoUnidadeNoRateioItem->errorMessage(),
                'criadoem' => "NOW()",
                'data' => "NOW()"
            ];
            
            $inserirLog = LogController::inserir($dadosLog);
        }
    }

    public static function buscarUnidadePadraoDoModulo($modulo, $idEmpresa = null)
    {
        // $idUnidades = "SELECT o.idunidade
        //         FROM unidadeobjeto o 
        //         JOIN unidade u on(u.idunidade = o.idunidade ".getidempresa('u.idempresa',$inmod)." and u.status='ATIVO')
        //         WHERE o.idobjeto='".$inmod."' and o.tipoobjeto = 'modulo'";

        $getIdEmpresa = getidempresa('u.idempresa',$modulo);

        if($idEmpresa)
        {
            $getIdEmpresa = "AND u.idempresa = $idEmpresa";
        }

        $unidade = SQL::ini(UnidadeQuery::buscarUnidadeDoModuloPorIdModuloGetIdEmpresa(), [
            'idobjeto' => $modulo,
            'getidempresa' => $getIdEmpresa
        ])::exec();

        // $resu = d::b()->query($sun);
    
        // $r = mysqli_fetch_assoc($resu);
        return !$unidade->numRows() ? "" : $unidade->data[0]["idunidade"];
    }

    public static function buscarTagsPorIdTagClassIdTagTipoEIdUnidade($idTagClass, $idTagTipo, $idUnidade, $toFillSelect = false)
    {
        $arrRetorno = [];
        $tags = SQL::ini(UnidadeQuery::buscarTagsPorIdTagClassIdTagTipoEIdUnidade(), [
            'idtagclass' => $idTagClass,
            'idtagtipo' => $idTagTipo,
            'idunidade' => $idUnidade
        ])::exec();

        if($tags->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
			return [];
		}

        if($toFillSelect)
        {
            foreach($tags->data as $tag)
            {
                $arrRetorno[$tag['idtag']] = $tag['descricao'];
            }

            return $arrRetorno;
        }

        return $tags->data;
    }

    public static function buscarTagsPorIdTagTipoEIdUnidade($idTagTipo, $idUnidade, $toFillSelect = false)
    {
        $tags = SQL::ini(UnidadeQuery::buscarTagsPorIdTagTipoEIdUnidade(), [
            'idtagtipo' => $idTagTipo,
            'idunidade' => $idUnidade
        ])::exec();

        if($tags->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
			return [];
		}

        if($toFillSelect)
        {
            foreach($tags->data as $tag)
            {
                $arrRetorno[$tag['idtag']] = $tag['descricao'];
            }

            return $arrRetorno;
        }

        return $tags->data;
    }

    public static function buscarUnidadeIdObjetoETipoObjeto($idObjeto, $tipoObjeto, $getIdEmpresa)
    {
        $unidade = SQL::ini(UnidadeObjetoQuery::buscarUnidadePorIdObjetoETipoObjeto(), [
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto,
            'getidempresa' => $getIdEmpresa
        ])::exec();

        if($unidade->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
			return [];
		}

        return $unidade->data;
    }

    public static function verificarSeExisteVinculoComUnidadePorIdObjetoETipoObjeto($idObjeto, $tipoObjeto)
    {
        $possuiVinculo = SQL::ini(UnidadeObjetoQuery::verificarSeExisteVinculoComUnidadePorIdObjetoETipoObjeto(),[
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto
        ])::exec();

        if($possuiVinculo->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $possuiVinculo->errorMessage());
			return [];
		}

        return ($possuiVinculo->numRows() > 0);
    }

    public static function vw8FuncionarioUnidadePorIdPessoaIdEmpresa($idpessoa, $idempresa)
    {
         $unidade = SQL::ini(UnidadeQuery::vw8FuncionarioUnidadePorIdPessoaIdEmpresa(),[
            'idpessoa' => $idpessoa,
            'idempresa' => $idempresa
        ])::exec();

        if($unidade->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
			return "";
		} else {
            return $unidade->data[0];
        }
    }
    
    public static function buscarUnidadeModuloPorTipoObjetoParaLote($idunidadeest)
    {
        $idempresa = getidempresa('u.idempresa', 'prodserv');

        $unidade = SQL::ini(UnidadeQuery::buscarUnidadeModuloPorTipoObjetoParaLote(),[
            'idunidadeest' => $idunidadeest,
            'idempresa' => $idempresa
        ])::exec();

        if($unidade->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
			return "";
		} else {
            return $unidade->data[0];
        }
    }

    public static function buscarUnidadeObjeto($idempresa, $idobjeto, $tipoobjeto)
    {
        $unidade = SQL::ini(UnidadeObjetoQuery::buscarUnidadeObjeto(),[
            "idempresa" => $idempresa,
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if($unidade->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
			return "";
		} else {
            return $unidade->data;
        }
    }

    public static function buscarUnidadePorIdtipoIdempresa($idtipounidade, $idempresa)
    {
        $results = SQL::ini(UnidadeQuery::buscarUnidadePorIdtipoIdempresa(), [            
            "idtipounidade"=>$idtipounidade,
            "idempresa"=>$idempresa
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data[0];
		}
    }

    public static function buscarUnidadeObjetoPorModuloTipoEIdUnidade($tipoobjeto, $modulotipo, $idunidade)
    {
        $results = SQL::ini(UnidadeObjetoQuery::buscarUnidadeObjetoPorModuloTipoEIdUnidade(), [            
            "tipoobjeto" => $tipoobjeto,
            "modulotipo" => $modulotipo,
            "idunidade" => $idunidade
        ])::exec();

        if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return $results->data[0];
		}
    }

    public static function buscarUnidadesPorIdEmpresa($idEmpresa, $idunidade)
    {
        $arrRetorno = [];

        $unidades = SQL::ini(UnidadeQuery::buscarUnidadesPorIdEmpresa(), [
            'idempresa' => $idEmpresa,
            'idunidade' => $idunidade
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return $arrRetorno;
        }

        foreach($unidades->data as $unidade)
        {
            $arrRetorno[$unidade['idunidade']] = $unidade['unidade'];
        }

        return $arrRetorno;
    }

    public static function buscarUnidadeObjetoPorModuloTipoEIdUnidadeEReady($modulotipo, $tipoobjeto, $idunidade)
    {
        $unidade = SQL::ini(UnidadeObjetoQuery::buscarUnidadeObjetoPorModuloTipoEIdUnidadeEReady(), [
            "tipoobjeto" => $tipoobjeto,
            "modulotipo" => $modulotipo,
            "idunidade" => $idunidade
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        } else {
            return $unidade->data[0];
        }        
    }

    public static function buscarUnidadeDeProducaoPorIdEmpresa($idEmpresa) {
        $unidade = SQL::ini(UnidadeQuery::buscarUnidadeDeProducaoPorIdEmpresa(), [
            "idempresa" => $idEmpresa
        ])::exec();

        if($unidade->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidade->errorMessage());
            return [];
        }

        return $unidade->data[0];
    }
}

?>
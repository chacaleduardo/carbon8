<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prativ_query.php");
require_once(__DIR__."/../querys/prativobj_query.php");
require_once(__DIR__."/../querys/prativobjlote_query.php");
require_once(__DIR__."/../querys/prativopcao_query.php");
require_once(__DIR__."/../querys/procprativinsumo_query.php");
require_once(__DIR__."/../querys/prproc_query.php");
require_once(__DIR__."/../querys/subtipoamostra_query.php");

//Controllers
require_once(__DIR__."/../controllers/amostra_controller.php");
require_once(__DIR__."/../controllers/formalizacao_controller.php");
require_once(__DIR__."/../controllers/formulaprocesso_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");

class PrativController extends Controller
{
	// ----- FUNÇÕES -----
    public static function buscarAtividadesDisponivesParaVinculoEmServico($idobjeto, $tipoobjeto, $tipo, $idempresa, $autocomplete = false)
    {
        $atividades = SQL::ini(PrativQuery::buscarAtividadesDisponivesParaVinculoEmServico(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
            'tipo' => $tipo,
            'idempresa' => $idempresa
        ])::exec();
        
        if($atividades->error()){
            parent::error(__CLASS__, __FUNCTION__, $atividades->errorMessage());
            return [];
        }

        if($autocomplete)
        {
            return array_map(function($item) {
                return [
                    'label' => $item['ativ'],
                    'value' => $item['idprativ']
                ];
            }, $atividades->data);
        }

        return $atividades->data;
    }

	public static function buscarInsumosEFormulasProdsev($idprativ)
	{
		$results = SQL::ini(ProcPrativInsumovQuery::buscarInsumosEFormulasProdsev(), [
            'idprativ' => $idprativ
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

	public static function buscarProcessosPorTipoEIdEmpresa($idprproc)
	{
		$results = SQL::ini(PrativQuery::buscarProcessosPorTipoEIdEmpresa(), [
            'idprproc' => $idprproc
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

    public static function buscarPrativOpcaoPorTipo($tipoobjeto, $idprativ, $tipo)
	{
		$results = SQL::ini(PrativObjQuery::buscarPrativOpcaoPorTipo(), [
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ,
            "tipo" => $tipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

    public static function buscarPrativObjPorTipoObjeto($tipoobjeto, $idprativ, $tipo)
	{
		$results = SQL::ini(PrativObjQuery::buscarPrativObjPorTipoObjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ,
            "tipo" => $tipo
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

    public static function buscarATividadesPorIdPrativETipoObjeto($tipoobjeto, $idprativ)
	{
		$results = SQL::ini(PrativObjQuery::buscarATividadesPorIdPrativETipoObjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ
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

    public static function buscarProcessosLigadosAtividade($idprativ)
	{
		$results = SQL::ini(PrProcQuery::buscarProcessosLigadosAtividade(), [
            "idprativ" => $idprativ
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

    public static function buscarSqlProcessosPorIdProdservPrProc($idprodservprproc)
	{
		$results = SQL::mount(PrativQuery::buscarProcessosPorIdProdservPrProc(), [
            'idprodservprproc' => $idprodservprproc
        ]);
        return $results;
	}

    public static function buscarSqlAtividadePorIdProProc($idprproc)
	{
		$results = SQL::mount(PrativQuery::buscarAtividadePorIdProProc(), [
            'idprproc' => $idprproc
        ]);
        return $results;
	}

	public static function inserirInsumo($idnovo, $idprodservformula, $idprodservformulains)
	{
		$results = SQL::ini(ProcPrativInsumovQuery::inserirInsumo(), [
            "idnovo" => $idnovo,
			"idprodservformula" => $idprodservformula,
			"idprodservformulains" => $idprodservformulains
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

    public static function buscarPrativObjPorTipoEIdPrativ($idtagclass, $tipoobjeto, $idprativ)
	{
		$results = SQL::ini(PrativObjQuery::buscarPrativObjPorTipoEIdPrativ(), [
            "idtagclass" => $idtagclass,
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ,
            "order" => ""
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

    public static function buscarPrativObjEEmpresaPorTipoEIdPrativ($tipoobjeto, $tipo, $idprativ)
	{
		$results = SQL::ini(PrativObjQuery::buscarPrativObjEEmpresaPorTipoEIdPrativ(), [
            "tipoobjeto" => $tipoobjeto,
            "tipo" => $tipo,           
            "idprativ" => $idprativ
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

    public static function buscarObjetoPorTipoObjeto($idobjeto, $tipoobjeto)
	{
		return FormulaProcessoController::buscarObjetoPorTipoObjeto($idobjeto, $tipoobjeto);
	}

    public static function apagarPrativObj($idprativobj)
	{
		$results = SQL::ini(PrativObjQuery::apagarPrativObj(), [
            "idprativobj" => $idprativobj
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

    public static function buscarObjetoPorIdPrativEIdObjetoEDescrNaoNulos($idprativ)
	{
		$results = SQL::ini(PrativObjQuery::buscarObjetoPorIdPrativEIdObjetoEDescrNaoNulos(), [
            "idprativ" => $idprativ
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
	}

    public static function buscarAtividadesLotePorIdLoteIdPrativEIdObjetoDescNaoNulos($idlote, $idprativ)
	{
		$results = SQL::ini(PrativObjLoteQuery::buscarAtividadesLotePorIdLoteIdPrativEIdObjetoDescNaoNulos(), [
            "idlote" => $idlote,
            "idprativ" => $idprativ
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
	}
    
    public static function buscarObjetoPorIdPrativobj($idprativobj, $grupo)
	{
		$results = SQL::ini(PrativObjQuery::buscarObjetoPorIdPrativobj(), [
            "idprativobj" => $idprativobj,
            "grupo" => $grupo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
	}

    public static function buscarOpcaoPorIdPrativopcao($idprativopcao)
	{
		$results = SQL::ini(PrativOpcaoQuery::buscarOpcaoPorIdPrativopcao(), [
            "idprativopcao" => $idprativopcao
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
	}

    public static function buscarAtividadesTagsPorIdEmpresaEIdPrativ($idprativ, $idtagtipo, $idlote)
	{   
        $tagPorSessionIdempresa = share::otipo('cb::usr')::tagPorSessionIdempresa("t.idtag");
		$results = SQL::ini(PrativObjQuery::buscarAtividadesTagsPorIdEmpresaEIdPrativ(), [
            "idprativ" => $idprativ,            
            "idtagtipo" => $idtagtipo,
            "idlote" => $idlote,
            "tagPorSessionIdempresa" => $tagPorSessionIdempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
	}

    public static function buscarSqlProcessos($idprproc)
	{
        return PrProcController::buscarSqlProcessos($idprproc);
    }

    public static function buscarSqlSubtipoamostraPorIdPrativ($idprativ)
	{
		$results = SQL::mount(SubtipoAmostraQuery::buscarSubtipoamostraPorIdPrativ(), [
            'idprativ' => $idprativ
        ]);
        return $results;
	}

    public static function buscarSqlObjetoPorTipoObjetoEIdPrativ($idtagclass, $tipoobjeto, $idprativ)
	{
		$results = SQL::mount(PrativObjQuery::buscarObjetoPorTipoObjetoEIdPrativ(), [
            'idtagclass' => $idtagclass,
            'tipoobjeto' => $tipoobjeto,
            'idprativ' => $idprativ,
            'getidempresa' => getidempresa('t.idempresa', 'tagtipo')
        ]);

        return $results;
	}

    public static function buscarSqlPrativOpcaoPorTipo($tipoobjeto, $idprativ, $tipo)
	{
		$results = SQL::mount(PrativObjQuery::buscarPrativOpcaoPorTipo(), [
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ,
            "tipo" => $tipo
        ]);

        return $results;
	}

    public static function buscarSqlPrativObjPorTipoEIdPrativ($idtagclass, $tipoobjeto, $idprativ)
	{
		$results = SQL::mount(PrativObjQuery::buscarPrativObjPorTipoEIdPrativ(), [
            "idtagclass" => $idtagclass,
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ,
            "order" => " ORDER BY o.ord"
        ]);

        return $results;
	}

    public static function buscarSqlPrativObjPorTipoObjeto($tipoobjeto, $idprativ, $tipo)
	{
		$results = SQL::mount(PrativObjQuery::buscarPrativObjPorTipoObjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ,
            "tipo" => $tipo
        ]);

        return $results;
	}

    public static function buscarSqlATividadesPorIdPrativETipoObjeto($tipoobjeto, $idprativ)
	{
		$results = SQL::mount(PrativObjQuery::buscarATividadesPorIdPrativETipoObjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ
        ]);

        return $results;
	}

    public static function apagarObjetoPorIdLote($idlote)
	{
        $results = SQL::ini(PrativObjLoteQuery::apagarObjetoPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function congelarAtividade($idlote, $idprativ, $idloteativ)
	{
        //congelar os itens da atividade
        self::inserirPrativObjetoPorSelect($idlote, $idprativ);

        /* Recuperar a sala
         * INSERÇÃO DE 1 sala por atividade
         */
        $listarSalaAtividade = self::buscarSalaAtividade($idprativ, 'tagtipo');
        foreach($listarSalaAtividade as $sala)
        {
            $listarEquipamento = self::buscarEquipamentoESala($idprativ, 'tagtipo', $sala['idtag']);
            foreach($listarEquipamento as $equipamento)
            {
                $arrayEquipamento = [
                    'idempresa' => cb::idempresa(),
                    'idlote' => $idlote,
                    'idprativ' => $idprativ,
                    'idloteativ' => $idloteativ,
                    'idobjeto' => $equipamento['idtag'],
                    'tipoobjeto' => 'tag-'.$equipamento['tagclass'],
                    'usuario' => $_SESSION['SESSAO']['USUARIO'],
                ];
                LoteController::inserirLoteObj($arrayEquipamento);
            }
        }

        // INSERIR NA ATIVOBJETOSEL DEMAIS TIPOS
        LoteController::inserirLoteObjPorSelect(cb::idempresa(), $idlote, $idprativ, $_SESSION['SESSAO']['USUARIO']);
    }

    public static function inserirPrativObjetoPorSelect($idlote, $idprativ)
	{
        $results = SQL::ini(PrativObjLoteQuery::inserirPrativObjetoPorSelect(), [
            "idlote" => $idlote,
            "usuario" => $_SESSION["SESSAO"]["USUARIO"],
            "idprativ" => $idprativ
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarSalaAtividade($idprativ, $tipoobjeto)
	{   
        $idempresa = getidempresa('t.idempresa', 'tag');
		$results = SQL::ini(PrativObjQuery::buscarSalaAtividade(), [
            "idprativ" => $idprativ,            
            "idempresa" => $idempresa,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
	}

    public static function buscarEquipamentoESala($idprativ, $tipoobjeto, $idtagpai)
	{   
        $idempresa = getidempresa('t.idempresa', 'tag');
		$results = SQL::ini(PrativObjQuery::buscarEquipamentoESala(), [
            "idprativ" => $idprativ,            
            "idempresa" => $idempresa,
            "tipoobjeto" => $tipoobjeto,
            "idtagpai" => $idtagpai
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
	}
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE ----
	public static function listarAtividadePorTamanhoAtivMaiorDois()
	{
		$results = SQL::ini(PrativQuery::listarAtividadePorTamanhoAtivMaiorDois(), [
            "getidempresa" => getidempresa('idempresa', 'prativ')
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $arrAtividades = [];
            foreach($results->data as $atividade)
            {
                //monta 2 estruturas json para finalidades (loops) diferentes
                $arrAtividades[$atividade["ativ"]] = $atividade["ativ"];
            }
            return $arrAtividades;
        }
	}

    public static function buscarTagTipoPorIdTagClassEShare($idtagclass) 
    {  
        return TagTipoController::buscarTagTipoPorIdTagClassEShare($idtagclass);
    }

    public static function listarProdservPorTipoEIdEmpresa($idtagclass) 
    {  
        return ProdservController::listarProdservPorTipoEIdEmpresa($idtagclass);
    }

    public static function listarAtividadesPorIdempresaEAtividadeNaoNulo()
	{
		$results = SQL::ini(PrativQuery::buscarAtividadePorIdempresaEAtividadeNaoNulo(), [
            "getidempresa" => getidempresa('a.idempresa', 'prativ')
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $arrAtividades = [];
            foreach($results->data as $atividade)
            {
                //monta 2 estruturas json para finalidades (loops) diferentes
                $arrAtividades[$atividade["idprativ"]]["ativ"] = $atividade["ativ"];
            }
            return $arrAtividades;
        }
	}

    public static function listarFillSelectTagPorIdTagClass($idtagclass)
	{
        return TagTipoController::listarFillSelectTagPorIdTagClass($idtagclass);
    }

    public static function buscarSubtipoamostraEmpresaPorIdEmpresa()
	{
        return AmostraController::buscarSubtipoamostraEmpresaPorIdEmpresa();
    }
	//----- AUTOCOMPLETE ----

    // ----- Variáveis de apoio -----
    public static $travasala = array("S" => "Simultânea",
                                     "C" => "Compartilhada",
                                     "E" => "Exclusiva");
    // ----- Variáveis de apoio -----
}
?>
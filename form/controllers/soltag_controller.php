<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/soltag_query.php");
require_once(__DIR__."/../querys/solmatitemobj_query.php");
require_once(__DIR__."/../querys/lotecons_query.php");
require_once(__DIR__."/../querys/rateio_query.php");
require_once(__DIR__."/../querys/rateioitem_query.php");
require_once(__DIR__."/../querys/rateioitemdest_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class SolTagController extends Controller 
{
    public static $tipo = [
        'EQUIPAMENTOS' => 'Equipamentos' 
    ];

    public static $impressoras = "'IMPRESSORA_SEMENTES','IMPRESSORA_ALMOXARIFADO_ZEBRA','IMPRESSORA_ALMOXARIFADO'";

    public static function buscarUnidadesDeDestino($toFillSelect = false)
    {
        $arrRetorno = [];
        // $sql = "SELECT group_concat(u.idunidade) as idunidades 
        //         from unidade u 
        //         join objetovinculo ov ON (ov.idobjetovinc = u.idunidade AND ov.tipoobjetovinc = 'unidade' AND ov.tipoobjeto = '_lp')
        //         where ov.idobjeto in  (" . getModsUsr("LPS") . ") 
        //         and u.idempresa = " . cb::idempresa() . ";";

        // $res = d::b()->query($sql) or die("Erro ao buscar unidades da LP do usuário: " . mysqli_error(d::b()) . "\n" . $sql);

        $idUnidades = SQL::ini(UnidadeQuery::buscarIdUnidadesPorIdObjetoTipoObjetoEIdEmpresa(), [
            'idobjeto' => getModsUsr("LPS"),
            'tipoobjeto' => '_lp',
            'idempresa' => cb::idempresa()
        ])::exec();

        if($idUnidades->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $idUnidades->errorMessage());
			return [];
		}

        // if (mysqli_num_rows($res) > 0) {
        //     $row = mysqli_fetch_assoc($res);
        //     if (!empty($row['idunidades'])) {
        //         return "";
        //     } else {
        //         return "";
        //     }
        // } else {
        //     return "";
        // }

        $union = '';

        if($idUnidades->data[0]['idunidades'])
        {
            $union = " UNION 
                        SELECT u.idunidade,u.unidade
                        from unidade u
                        where u.status='ATIVO' 
                        and idunidade in (" . $idUnidades->data[0]['idunidades'] . ")";
        }

        $clausulaUnidade = "AND EXISTS (
                                SELECT 1 
                                FROM vw8PessoaUnidade pu 
                                WHERE pu.idpessoa = {$_SESSION["SESSAO"]["IDPESSOA"]}
                                AND pu.idunidade = u.idunidade
                            )";

        if (array_key_exists("soltagmaster", getModsUsr("MODULOS")) == 1) 
        {
            $clausulaUnidade = "";
        }

        $unidades = SQL::ini(UnidadeQuery::buscarUnidadesPorClausulaUnidadeGetIdEmpresaEUnion(), [
            'clausulaunidade' => $clausulaUnidade,
            'getidempresa' => getidempresa("u.idempresa", "unidade"),
            'union' => $union
        ])::exec();

        if($unidades->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
			return [];
		}

        if($toFillSelect)
        {
            foreach($unidades->data as $unidade)
            {
                $arrRetorno[$unidade['idunidade']] = $unidade['unidade'];
            }

            return $arrRetorno;
        }

        return $unidades->data;
    }

    public static function buscarTagsVinculadasPorIdSolMatItem($idSolMatItem)
    {
        $tagsVinculadas = SQL::ini(SolMatItemObjQuery::buscarTagsVinculadasPorIdSolMatItem(), [
            'idsolmatitem' => $idSolMatItem
        ])::exec();

        if($tagsVinculadas->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $tagsVinculadas->errorMessage());
			return [];
		}

        return $tagsVinculadas->data;
    }

    public static function buscarIdTransacao()
    {
        return SQL::ini(SolTagQuery::buscarIdTransacao())::exec()->data[0]['idtransacao'];
    }

    public static function buscarLoteFracaoPorIdLoteEIdUnidade($idLote, $idUnidade)
    {
        $loteFracao = SQL::ini(LoteFracaoQuery::buscarLoteFracaoPorIdLoteEIdUnidade(), [
            'idlote' => $idLote,
            'idunidade' => $idUnidade
        ])::exec();

        if($loteFracao->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $loteFracao->errorMessage());
			return [];
		}

        return $loteFracao->data[0];
    }

    public static function buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idLoteFracao)
    {
        $informacoesUnidade = SQL::ini(LoteFracaoQuery::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao(), [
            'idlotefracao' => $idLoteFracao
        ])::exec();

        if($informacoesUnidade->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $informacoesUnidade->errorMessage());
			return [];
		}

        return $informacoesUnidade->data[0];
    }

    public static function inserirLoteCons($dados)
    {
        $inserindoLoteCons = SQL::ini(LoteConsQuery::inserir(), $dados)::exec();

        if($inserindoLoteCons->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $inserindoLoteCons->errorMessage());
			return [];
		}

        return $inserindoLoteCons->lastInsertId;
    }

    public static function inserirRateio($dados)
    {
        $inserindoRateio = SQL::ini(RateioQuery::inserir(), $dados)::exec();

        if($inserindoRateio->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $inserindoRateio->errorMessage());
			return [];
		}

        return $inserindoRateio->lastInsertId;
    }

    public static function inserirRateioItem($dados)
    {
        $inserindoRateioItem = SQL::ini(RateioItemQuery::inserir(), $dados)::exec();

        if($inserindoRateioItem->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $inserindoRateioItem->errorMessage());
			return [];
		}

        return $inserindoRateioItem->lastInsertId;
    }

    public static function inserirRateioItemDest($dados)
    {
        $inserirRateioItemDest = SQL::ini(RateioItemDestQuery::inserir(), $dados)::exec();

        if($inserirRateioItemDest->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $inserirRateioItemDest->errorMessage());
			return [];
		}

        return $inserirRateioItemDest->lastInsertId;
    }

    public static function inserirLoteFracao($dados)
    {
        $inserindoLoteFracao = SQL::ini(LoteFracaoQuery::inserir(), $dados)::exec();

        if($inserindoLoteFracao->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $inserindoLoteFracao->errorMessage());
			return [];
		}

        return $inserindoLoteFracao->lastInsertId;
    }

    
	public static function listarPessoaPorIdTipoPessoa($idTipoPessoa)
	{
		$colaborador =  PessoaController::listarPessoaPorIdTipoPessoa($idTipoPessoa);
		foreach($colaborador as $_colaborador)
		{
			$arrayColaborador[$_colaborador["idpessoa"]]["nome"] = $_colaborador["nomecurto"];
		}

		return $arrayColaborador;

	}
}

?>
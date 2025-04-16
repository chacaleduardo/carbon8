<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/lpobjeto_query.php");
require_once(__DIR__."/../querys/_lp_query.php");
require_once(__DIR__."/../querys/dashcardfiltros_query.php");
require_once(__DIR__."/../querys/dashcard_query.php");

// CONTROLLES
require_once(__DIR__."/_controller.php");

class DashCardController extends Controller
{
    public static $tipos = [
        'manual' => 'Manual',
        'fluxostatus' => 'Fluxostatus',
        'fixo' =>  'Fixo',
        'etapa' =>  'Etapa'
    ];

    public static $simNao = [
        'N' => 'Não',
        'Y' => 'Sim'
    ];

    public static $periodoEtl = [
        'LASTWEEK' =>'Últimos 7 dias',
        'THISWEEK' => 'Essa semana',
        'LASTMONTH' => 'Últimos 30 dias',
        'THISMONTH' => 'Esse mês',
    ];

    public static $intervaloDeTempo = [
        '1 HOUR' => '1 Hora',
        '2 HOUR' => '2 Horas',
        '3 HOUR' => '3 Horas',
        '4 HOUR' => '4 Horas',
        '5 HOUR' => '5 Horas',
        '6 HOUR' => '6 Horas',
        '7 HOUR' => '7 Horas' ,
        '8 HOUR' => '8 Horas' ,
        '9 HOUR' => '9 Horas' ,
        '10 HOUR' => '10 Horas' ,
        '11 HOUR' => '11 Horas' ,
        '12 HOUR' => '12 Horas' ,
        '1 DAY' => '1 Dia',
        '2 DAY' => '2 Dias',
        '3 DAY' => '3 Dias',
        '4 DAY' => '4 Dias',
        '5 DAY' => '5 Dias',
        '6 DAY' => '6 Dias' ,
        '1 WEEK' => '1 Semana' ,
        '2 WEEK' => '2 Semanas' ,
        '3 WEEK' => '3 Semanas' ,
        '4 WEEK' => '4 Semanas' ,
        '1 MONTH' => '1 Mes' ,
        '2 MONTH' => '2 Meses' ,
        '3 MONTH' => '3 Meses' ,
        '4 MONTH' => '4 Meses' ,
        '5 MONTH' => '5 Meses' ,
        '6 MONTH' => '6 Meses' ,
        '1 YEAR' => '1 Ano'
    ];

    public static $status = [
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo'
    ];

    public static $tipoCalculo = [
        'SUM' => 'sum',
        'COUNT' => 'count'
    ];

    public static $mascaraValor = [
        'R' => 'R$',
        'N' => 'Num'
    ];

    public static $sinaisSQL = [
        '=' => 'Igual',
        '>' => 'Maior que',
        '>=' => 'Maior igual a',
        '<' => 'Menor que' ,
        '<=' => 'Menor igual a',
        '=  cod' => 'Igual Código',
        '>  cod' => 'Maior que Código',
        '>=  cod' => 'Maior igual a Código',
        '<  cod' => 'Menor que Código' ,
        '<=  cod' => 'Menor igual a Código',
        'like' => 'Like',
        'in' => 'In',
        'not in' => 'Not In',
        'session' => 'session',
        'find_in_set' =>  'find_in_set'
    ];

    public static $valorsFiltro = [
        'now' => 'Hoje Datetime',
        'mais' => 'Hoje Datetime +',
        'menos' => 'Hoje Datetime -',
        'nowdate' => 'Hoje Date',
        'maisdate' => 'Hoje Date +',
        'menosdate' => 'Hoje Date -'
    ];

    public static $code = [
        'idunidade' => 'idunidade',
        '\$pessoas' => '\$pessoas',
        'estemes_criadoem' => 'estemes_criadoem',
        'estemes_fabricacao' => 'estemes_fabricacao',
        'groupby_idunidade' => 'groupby_idunidade'  ,
    ];

    public static function BuscarLpsVinculadasPorIdDashCard($idDashCard)
    {
        $lps = SQL::ini(LpObjetoQuery::buscarLpsVinculadasPorIdDashCard(), [
            'iddashcard' => $idDashCard
        ])::exec();

        if($lps->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarLpsDisponiveisParaVinculoPorIdDashCard($idDashCard, $autocomplete = false)
    {
        $lps = SQL::ini(_LpQuery::buscarLpsDisponiveisParaVinculoPorIdDashCard(), [
            'iddashcard' => $idDashCard
        ])::exec();

        if($lps->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        if($autocomplete)
        {
            $arrRetorno = [];

            foreach($lps->data as $key => $lp)
            {
                $arrRetorno[$key]["value"] = $lp["idlp"];
	            $arrRetorno[$key]["label"] = $lp["descricao"];
            }

            return $arrRetorno;
        }

        return $lps->data;
    }

    public static function buscarDashCardPorTabela($tabela)
    {
        $dashcard = SQL::ini(DashCardQuery::buscarDashCardPorTabela(), [
            'tabela' => $tabela
        ])::exec();

        if($dashcard->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $dashcard->errorMessage());
            return [];
        }

        return $dashcard->data[0];
    }

    public static function deletarFiltrosPorIdDashCardETabela($idDashCard, $tabela)
    {
        $deletandoFiltros = SQL::ini(DashCardFiltrosQuery::deletarFiltrosPorIdDashCardETabela(), [
            'iddashcard' => $idDashCard,
            'tabela' => $tabela
        ])::exec();

        if($deletandoFiltros->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $deletandoFiltros->errorMessage());
            return [];
        }

        return $deletandoFiltros->data;
    }

    public static function inserirObjetosVinculados($idDashCard, $tabela, $idEmpresa)
    {
        $inserindoObjetosVinculados = SQL::ini(DashCardFiltrosQuery::inserirObjetosVinculados(), [
            'idempresa' => $idEmpresa,
            'usuario' => $_SESSION["SESSAO"]["USUARIO"],
            'iddashcard' => $idDashCard,
            'tabela' => $tabela
        ])::exec();

        if($inserindoObjetosVinculados->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $inserindoObjetosVinculados->errorMessage());
            return [];
        }
    }
}


?>
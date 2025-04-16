<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/etlconf_query.php");
require_once(__DIR__."/../querys/etlconffiltros_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");


class EtlConfController extends Controller
{
    public static $horaDiaSemanaMesAno = [
        '1 HOUR' => '1 Hora',
        '2 HOUR' => '2 Horas',
        '3 HOUR' => '3 Horas',
        '4 HOUR' => '4 Horas',
        '5 HOUR' => '5 Horas',
        '6 HOUR' => '6 Horas',
        '7 HOUR' => '7 Horas',
        '8 HOUR' => '8 Horas',
        '9 HOUR' => '9 Horas',
        '10 HOUR' => '10 Horas',
        '11 HOUR' => '11 Horas',
        '12 HOUR' => '12 Horas',
        '1 DAY' => '1 Dia',
        '2 DAY' => '2 Dias',
        '3 DAY' => '3 Dias',
        '4 DAY' => '4 Dias',
        '5 DAY' => '5 Dias',
        '6 DAY' => '6 Dias',
        '1 WEEK' => '1 Semana' ,
        '2 WEEK' => '2 Semanas',
        '3 WEEK' => '3 Semanas',
        '4 WEEK' => '4 Semanas',
        '1 MONTH' => '1 Mes',
        '2 MONTH' => '2 Meses',
        '3 MONTH' => '3 Meses',
        '4 MONTH' => '4 Meses',
        '5 MONTH' => '5 Meses',
        '6 MONTH' => '6 Meses',
        '1 YEAR' => '1 Ano'
    ];

    public static $status = [
        'PENDENTE' => 'Pendente',
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo'
    ];

    public static $simNao = [
        'N' => 'N',
        'Y' =>  'Y'
    ];

    public static $sinais = [
        '=' => 'Igual',
        '>' => 'Maior que',
        '>=' => 'Maior igual a',
        '<' => 'Menor que',
        '<=' => 'Menor igual a',
        'like' => 'Like'
    ];

    public static $valores = [
        'now' => 'Hoje',
        'mais' => 'Hoje +',
        'menos' => 'Hoje -'
    ];

    public static $sinais2 = [
        '=' => 'Igual',
        '>' => 'Maior que',
        '>=' => 'Maior igual que',
        '<' => 'Menor que',
        '<=' => 'Menor igual que',
        'like' => 'Like',
        'is' => 'Is',
        '!=' => 'Diferente',
        'sql' => 'SQL'
    ];

    public static $grupoConcat = [
        'grp' => 'GROUP_CONCAT()',
        'grpdist' => 'GROUP_CONCAT(DISTINCT())'
    ];

    public static $somaF = [
        'semf' => 'Sem agrupador',
        'sum' => 'SUM()',
        'count' => 'COUNT()',
        'countdist' => 'COUNT(DISTINCT())'
    ];

    public static function buscarPorChavePrimaria($id)
    {
        $etlConf = SQL::ini(EtlConfQuery::buscarPorChavePrimaria() ,[
            'pkval' => $id
        ])::exec();

        if($etlConf->error()){
            parent::error(__CLASS__ ,  __FUNCTION__, $etlConf->errorMessage());
            return [];
        }

        return $etlConf->data[0];
    }

    public static function buscarTabelasDoBancoCarbonELaudo()
    {
        $tabelas = SQL::ini(EtlConfQuery::buscarTabelasDoBancoCarbonELaudo())::exec();
        
        $arrRetorno=array();

        if($tabelas->error()){
            parent::error(__CLASS__, __FUNCTION__, $tabelas->errorMessage());
            return [];
        }

        foreach($tabelas->data as $key => $tabela)
        {
            $arrRetorno[$key]["value"] = $tabela["tab"];
            $arrRetorno[$key]["label"] = $tabela["tab"];
            $arrRetorno[$key]["db"] = $tabela["db"];
        }

        return $arrRetorno;
    }

    public static function deletarFiltrosRemovidosDaTabela($tab, $idEtlConf)
    {
        $removendoFiltros = SQL::ini(EtlConfFiltrosQuery::deletarFiltrosRemovidosDaTabela(), [
            'tab' => $tab,
            'idetlconf' => $idEtlConf
        ])::exec();

        if($removendoFiltros->error()){
            parent::error(__CLASS__, __FUNCTION__, $removendoFiltros->errorMessage());
            return [];
        }
    }

    public static function inserirFiltrosAdicionadosNaTabela($idEtlConf, $tab, $idEmpresa, $usuario)
    {
        $inserindoFiltros = SQL::ini(EtlConfFiltrosQuery::inserirFiltrosAdicionadosNaTabela(), [
            'idetlconf' => $idEtlConf,
            'tab' => $tab,
            'idempresa' => $idEmpresa,
            'usuario' => $usuario
        ])::exec();

        if($inserindoFiltros->error()){
            parent::error(__CLASS__, __FUNCTION__, $inserindoFiltros->errorMessage());
            return false;
        }
    }

    public static function desabilitarTSumPorIdEtlConf($idEtlConf)
    {
        $desabilitandoTsum = SQL::ini(EtlConfFiltrosQuery::desabilitarTSumPorIdEtlConf(), [
            'idetlconf' => $idEtlConf
        ])::exec();
        
        if($desabilitandoTsum->error()){
            parent::error(__CLASS__, __FUNCTION__, $desabilitandoTsum->errorMessage());
            return false;
        }
    }

    public static function desabilitarSeparadorPorIdEtlConf($idEtlConf)
    {
        $desabilitandoSeparador = SQL::ini(EtlConfFiltrosQuery::desabilitarSeparadorPorIdEtlConf(), [
            'idetlconf' => $idEtlConf
        ])::exec();
        
        if($desabilitandoSeparador->error()){
            parent::error(__CLASS__, __FUNCTION__, $desabilitandoSeparador->errorMessage());
            return false;
        }
    }
}

?>
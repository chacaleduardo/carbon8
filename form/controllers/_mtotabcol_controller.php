<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/_mtotabcol_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");


class _MtoTabColController extends Controller
{
    public static function buscarPorTabECol($tab, $col)
    {
        $mtoTabCol = SQL::ini(_MtotabcolQuery::buscarPorTabECol(), [
            'tab' => $tab,
            'col' => $col
        ])::exec();

        if($mtoTabCol->error()){
            parent::error(__CLASS__, __FUNCTION__, $mtoTabCol->errorMessage());
            return [];
        }

        return $mtoTabCol->data[0];
    }

    public static function buscarFiltrosPorIdEtlConfEClausula($idEtlConf, $clausula)
    {
        $etlConfFiltros = SQL::ini(_MtotabcolQuery::buscarFiltrosPorIdEtlConfEClausula(), [
            'idetlconf' => $idEtlConf,
            'clausula' => $clausula
        ])::exec();

        if($etlConfFiltros->error()){
            parent::error(__CLASS__, __FUNCTION__, $etlConfFiltros->errorMessage());
            return [];
        }

        return $etlConfFiltros->data;
    }

    public static function buscarTabelas($montarArrayPorTabela = false, $toFillSelect = false)
    {
        $tabelas = SQL::ini(_MtotabcolQuery::buscarTabelas())::exec();

        if($tabelas->error()){
            parent::error(__CLASS__, __FUNCTION__, $tabelas->errorMessage());
            return [];
        }

        $arrRetorno = [];

        if($montarArrayPorTabela)
        {
            foreach($tabelas->data as $tabela)
                $arrRetorno[$tabela['tab']] = $tabela;

            return $arrRetorno;
        }

        if($toFillSelect)
        {
            foreach($tabelas->data as $tabela)
                $arrRetorno[$tabela['tab']] = $tabela['tab'];

            return $arrRetorno;
        }

        return $tabelas->data;
    }

    public static function buscarFiltrosPorIdDashCardEClausula($idDashCard, $clausula, $toFillSelect = false)
    {
        $filtros = SQL::ini(_MtotabcolQuery::buscarFiltrosPorIdDashCardEClausula(), [
            'iddashcard' => $idDashCard,
            'clausula' => $clausula
        ])::exec();

        if($filtros->error()){
            parent::error(__CLASS__, __FUNCTION__, $filtros->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($filtros->data as $filtro)
            {
                $arrRetorno[$filtro['col']] = $filtro['rot'];
            }

            return $arrRetorno;
        }

        return $filtros->data;
    }

    public static function buscarFiltrosPorIdDashCardEClausulaDaTabela($idDashCard, $clausula, $toFillSelect = false)
    {
        $filtros = SQL::ini(_MtotabcolQuery::buscarFiltrosPorIdDashCardEClausulaDaTabela(), [
            'iddashcard' => $idDashCard,
            'clausula' => $clausula
        ])::exec();

        if($filtros->error()){
            parent::error(__CLASS__, __FUNCTION__, $filtros->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($filtros->data as $filtro)
            {
                $arrRetorno[$filtro['col']] = $filtro['rot'];
            }

            return $arrRetorno;
        }

        return $filtros->data;
    }

    public static function buscarMtoTabColPorTabela($tabela)
    {
        $mtoTabCol = SQL::ini(_MtotabcolQuery::buscarMtoTabColPorTabela(), [
            'tabela' => $tabela
        ])::exec();

        if($mtoTabCol->error()){
            parent::error(__CLASS__, __FUNCTION__, $mtoTabCol->errorMessage());
            return [];
        }

        return $mtoTabCol->data;
    }

    public static function buscarChavePrimariaPorTabela($tabela)
    {
        $chavePrimaria = SQL::ini(_MtotabcolQuery::buscarChavePrimariaPorTabela(), [
            'tabela' => $tabela
        ])::exec();

         if($chavePrimaria->error()){
            parent::error(__CLASS__, __FUNCTION__, $chavePrimaria->errorMessage());
            return [];
        }

        return $chavePrimaria->data[0]['col'];
    }
}

?>
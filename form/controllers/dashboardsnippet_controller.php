<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/dashcard_query.php");
require_once(__DIR__."/../querys/dashcardfiltros_query.php");
require_once(__DIR__."/../querys/dashcard_query.php");
require_once(__DIR__."/../querys/evento_query.php");
require_once(__DIR__."/../querys/vw8eventopessoa_query.php");
require_once(__DIR__."/../querys/_modulorep_query.php");

// CONTROLLERS
require_once(__DIR__.'/_controller.php');

class DashboardSnippetController extends Controller
{
    public static function buscarDashCardPorIdDashCard($idDashCard)
    {
        $dashCards = SQL::ini(DashCardQuery::buscarDashCardPorIdDashCard(), [
            'iddashcard' => $idDashCard
        ])::exec();

        if($dashCards->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $dashCards->errorMessage());
            return [];
        }

        return $dashCards->data;
    }

    public static function buscarDashboardFixoPorIdDashCard($idDashCard)
    {
        $dashCardPanelGrupo = SQL::ini(DashCardQuery::buscarDashboardFixoPorIdDashCard(), [
            'iddashcard' => $idDashCard
        ])::exec();

        if($dashCardPanelGrupo->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $dashCardPanelGrupo->errorMessage());
            return [];
        }

        return $dashCardPanelGrupo->data[0];
    }

    public static function buscarTabelaFiltrosDashboard($idDashCard, $alias = "")
    {
        $ler = true;//Gerar logs de erro
        $rid = "\n".rand()." - Sislaudo: ";
        if($ler) error_log($rid.basename(__FILE__, '.php'));

        $clausula = "";
        $and = " and ";

        $filtrosDashCard = SQL::ini(DashCardFiltrosQuery::buscarTabelaFiltrosDashboard(), [
            'iddashcard' => $idDashCard
        ])::exec();

        if($filtrosDashCard->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $filtrosDashCard->errorMessage());
            if($ler) error_log($rid.' '.$filtrosDashCard->errorMessage().' não previsto');

            return [];
        }
    
        foreach($filtrosDashCard->data as $dashCard)
        {
            // GVT - 13/02/2020 - Alterado a ci=ondição para permitir valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
            if(($dashCard["valor"]!='null' and $dashCard["valor"]!=' ' and $dashCard["valor"]!='') or ($dashCard["valor"]=='null' and ($dashCard["sinal"]=='is' or $dashCard["sinal"]=='is not' or $dashCard["sinal"]=='session'))){
                if($dashCard["valor"]=='now'){
                    if(!empty($dashCard["nowdias"])){
                        $dashCard["nowdias"];
                        $date=date("Y-m-d H:i:s");
                        $valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$dashCard["nowdias"].' day'));
                    }else{
                        $valor=date("Y-m-d H:i:s"); 
                    }
                }else if($dashCard["valor"]=='mais'){
                    $date=date("Y-m-d H:i:s");
                    $valor=date('Y-m-d H:i:s', strtotime($date. ' + '.$dashCard["nowdias"].' day'));
                }else if($dashCard["valor"]=='menos'){
                    $date=date("Y-m-d H:i:s");
                    $valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$dashCard["nowdias"].' day'));
                }else{
                    $valor=$dashCard["valor"];
                }
    
    
                if($dashCard['sinal']=='in' || $dashCard['sinal']=='not in'){
                    $strvalor = str_replace(",","','",$valor);
                    $clausula.= $and." ".$alias.$dashCard["col"]." ".$dashCard['sinal']." ('".$strvalor."')";
                }elseif($dashCard['sinal']=='like'){
                    $clausula.= $and." ".$alias.$dashCard["col"]." like ('%".$valor."%')";
                }elseif($dashCard['sinal']=='is'){
                    $clausula.= $and." ".$alias.$dashCard["col"]." ".$dashCard['sinal']." ".$valor."";
                }elseif($dashCard['sinal']=='session'){
                    //$clausula.= $and." ".$dashCard["col"]." = '".$_SESSION["SESSAO"][$valor]."'";
                }elseif($dashCard['sinal']=='find_in_set'){
                        $clausula.= $and." find_in_set(".$valor." , ".$alias.$dashCard["col"].")";
                }else{
                    $clausula.= $and." ".$alias.$dashCard["col"]." ".$dashCard['sinal']." '".$valor."'";
                }
    
            } else
            {
                if($ler)error_log($rid.'dashCard[valor] não previsto');
            }
        }
    
        return $clausula;
    }

    public static function buscarClausulaCodigoDashboard($code, $cron, $alias = "")
    {
        $clausula = "";

        if($cron == "N"){
            if ($code == '$idpessoa'){
                $clausula.= " and ".$alias."idpessoa = '".$_SESSION["SESSAO"]["IDPESSOA"]."' ";
            }
            
            if (strpos($code,"idunidade") !== false) {
                $clausula.= " and exists (select 1 from vw8PessoaUnidade a where a.idpessoa  = '".$_SESSION["SESSAO"]["IDPESSOA"]."' and a.idunidade = ".$alias."idunidade) ";
            }
        }
    
        
        if(array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"]) ){
            $pessoas =  " and ".$alias."idpessoa in( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].") ";
        }else{
            $pessoas =  '';
        }
    
    
        if ($code == '$pessoas'){
            $clausula.= " ".$pessoas." ";
        }
        if (strpos($code,"estemes_criadoem") !== false) {
            $clausula.= " and ".$alias."criadoem between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
        }
        if (strpos($code,"filtra_criadopor") !== false) {
            $clausula.= " and ".$alias."criadopor = '{$_SESSION["SESSAO"]["USUARIO"]}'";
        }
        if (strpos($code,"estemes_dtemissao") !== false) {
            $clausula.= " and ".$alias."dtemissao between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
        }
        if (strpos($code,"estemes_datareceb") !== false) {
            $clausula.= " and ".$alias."datareceb between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
        }
        if (strpos($code,"esteeproximomes_datareceb") !== false) {
            $clausula.= " and ".$alias."datareceb between DATE_FORMAT(NOW() ,'%Y-%m-01') AND LAST_DAY(DATE_ADD(DATE_FORMAT(NOW() ,'%Y-%m-01'),interval 1 month)) ";
        }
    
        if (strpos($code,"estemes_fabricacao") !== false) {
            $clausula.= " and ".$alias."fabricacao between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
        }
        if (strpos($code,"estemes_fabricacao") !== false) {
            $clausula.= " and ".$alias."fabricacao between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
        }
        if (strpos($code,"groupby_idunidade") !== false) {
            $group = " group by ".$alias."idunidade ";
        }
    
        $arr['clausula']=$clausula;
        $arr['group']=$group;
    
        return $arr;
    }

    public static function queryBuscarDashFixo($idDashCard, $idEmpresa = null)
    {
        if(!$idEmpresa)
        {
            $idEmpresa = cb::idempresa();
        }

        $clausula="";
    
        $dashboardFixo = DashboardSnippetController::buscarDashboardFixoPorIdDashCard($idDashCard);

        //PEGA CLAUSULAS DA TABELA DO DASH
        $clausula .= DashboardSnippetController::buscarTabelaFiltrosDashboard($idDashCard, "s.");

        $orderBy = '';
        if($dashboardFixo['cardordenacao'])
        {
            $orderBy = "&_ordcol={$dashboardFixo['cardordenacao']}&_orddir={$dashboardFixo['cardsentido']}";

            preg_match('/[\']+([^-]+)[\']/', $dashboardFixo['cardurl'], $valoresEncontrados);
            $cardUrl = preg_replace('/[\']+([^-]+)[\']/', "'{$valoresEncontrados[1]}' ,'$orderBy'", $dashboardFixo['cardurl']);
            $dashboardFixo['cardurl'] = $cardUrl;
        }

        //PEGA CLAUSULAS DO INDICADOR DO DASH
        $clausulaCodigoDashboard = DashboardSnippetController::buscarClausulaCodigoDashboard($dashboardFixo['code'], "N", "s.");
        $clausula .= $clausulaCodigoDashboard['clausula'];
        $group = $clausulaCodigoDashboard['group'];

        $sqlDashFixo = " UNION ALL ";
        $sqlDashFixo .= SQL::mount(DashCardQuery::buscarDashCardFixo(), [
            'iddashgrupo' => $dashboardFixo['iddashgrupo'],
            'rotulo' => $dashboardFixo['rotulo'],
            'iddashcard' => $dashboardFixo['iddashcard'],
            'iddashpanel' => $dashboardFixo['iddashpanel'],
            'paneltitle' => $dashboardFixo['paneltitle'],
            'cardurl' => $dashboardFixo['cardurl'],
            'cardcolor' => $dashboardFixo['cardcolor'],
            'cardbordercolor' => $dashboardFixo['cardbordercolor'],
            'cardtitle' => $dashboardFixo['cardtitle'],
            'cardtitlesub' => $dashboardFixo['cardtitlesub'],
            'cardtitlemodal' => $dashboardFixo['cardtitlemodal'],
            'cardurlmodal' => $dashboardFixo['cardurlmodal'],
            'dashcardorder' => $dashboardFixo['dashcardorder'],
            'panelorder' => $dashboardFixo['panelorder'],
            'grouporder' => $dashboardFixo['grouporder'],
            'code' => $dashboardFixo['code'],
            'tab' => $dashboardFixo['tab'],
            'modulo' => $dashboardFixo['modulo'],
            'idempresa' => $idEmpresa,
            'clausula' => $clausula,
            'group' => $group
        ]);
        
        return $sqlDashFixo;
    }

    public static function buscarDashboardCardPanelGrupo($queryDashFixo, $idPessoa = null, $idEmpresa = null)
    {
        $dashboardCardPanelGrupo = SQL::ini(DashCardQuery::buscarDashboardCardPanelGrupo(), [
            'idpessoa' => $idPessoa ?? $_SESSION["SESSAO"]["IDPESSOA"],
            'idempresa' => $idEmpresa ?? cb::idempresa(),
            'querydashfixo' => $queryDashFixo
        ])::exec();        

        if($dashboardCardPanelGrupo->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $dashboardCardPanelGrupo->errorMessage());
            return [];
        }

        return $dashboardCardPanelGrupo->data;
    }

    public static function buscarDashCardPorTipoObjetoEStatus($tipoObjeto, $status = 'ATIVO')
    {
        $dashboardFixo = SQL::ini(DashCardQuery::buscarDashCardPorTipoObjetoEStatus(), [
            'tipoobjeto' => $tipoObjeto,
            'status' => $status
        ])::exec();

        if($dashboardFixo->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $dashboardFixo->errorMessage());
            return [];
        }

        return $dashboardFixo->data;
    }

    public static function buscarFiguraCabecalhoRelatorio($idEmpresa = null)
    {
        $figurasCabecalhosRelatorio = SQL::ini(EventoQuery::buscarFiguraCabecalhoRelatorio(), [
            'idobjeto' => $idEmpresa ?? $_SESSION["SESSAO"]["IDPESSOA"]
        ])::exec();

        if($figurasCabecalhosRelatorio->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $figurasCabecalhosRelatorio->errorMessage());
            return [];
        }

        return $figurasCabecalhosRelatorio->data;
    }

    public static function buscarEventosAlerta($idPesssoa)
    {
        $eventosAlerta = SQL::ini(Vw8EventoPessoaQuery::buscarEventosAlerta(), [
            'idpessoa' => $idPesssoa
        ])::exec();

        if($eventosAlerta->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $eventosAlerta->errorMessage());
            return [];
        }

        return $eventosAlerta->data;
    }

    public static function buscarRelatoriosPorModuloELp($modulos, $lps, $clausulaDashboard = null)
    {
        $relatorios = SQL::ini(_ModuloRepQuery::buscarRelatoriosPorModuloELp(), [
            'modulos' => $modulos,
            'lps' => $lps,
            'clausuladashboard' => $clausulaDashboard
        ])::exec();

        if($relatorios->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $relatorios->errorMessage());
            return [];
        }

        return $relatorios->data;
    }
}

?>
<?
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/pessoa_controller.php");

require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/_rep_query.php");
require_once(__DIR__."/../querys/_lprep_query.php");
require_once(__DIR__."/../querys/empresa_query.php");
require_once(__DIR__."/../querys/vw8loteconsinsumo_query.php");
require_once(__DIR__."/../querys/eventotipocampos_query.php");
require_once(__DIR__."/../querys/vwponto_query.php");
require_once(__DIR__."/../querys/rhevento_query.php");

class MenuRelatorioController extends Controller{

    public static function buscarRelatoriosPorLps( $modulosPai, $modulosFilhos, $modulosFilhosVinculados, $idempresa, $lps, $mostrarmenurelatorio = "'Y'", $clausula = "" ) {
        $results = SQL::ini(_LpRepQuery::buscarRepsMenuRelatorio(), [
            'modulosPai' => $modulosPai,
            'modulosFilhos' => $modulosFilhos,
            'modulosFilhosVinculados' => $modulosFilhosVinculados,
            'idempresa' => $idempresa,
            'lps' => $lps,
            'mostrarmenurelatorio' => $mostrarmenurelatorio,
            'clausula' => $clausula,
        ])::exec();

        echo "<!--".$results->sql()."-->";

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        $arr = array();
        foreach ($results->data as $k => $row){
            $arr[$row['modulopesq']]['ord'] = $row['ord'];
            $arr[$row['modulopesq']]['modulopesq'] = $row['modulopesq'];
            $arr[$row['modulopesq']]['idmodulopesq'] = $row['idmodulopesq'];
            $arr[$row['modulopesq']]['rotulomenupesq'] = $row['rotulomenupesq'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reptipo']                             = $row['reptipo'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['idreptipo']                           = $row['idreptipo'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reps'][$row['idrep']]['rep']          = $row['rep'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reps'][$row['idrep']]['url']          = $row['url'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reps'][$row['idrep']]['conf']         = null;
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reps'][$row['idrep']]['idrep']        = $row['idrep'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reps'][$row['idrep']]['tipograph']    = $row['tipograph'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reps'][$row['idrep']]['flgunidade']   = $row['flgunidade'];
            $arr[$row['modulopesq']]['tiporep'][$row['idreptipo']]['reps'][$row['idrep']]['titlebutton']  = $row['titlebutton'];
        }
        
        return $arr;
    }

    public static function buscarLogoRelatorioBase64 ($idempresa) {
        $results = SQL::ini(EmpresaQuery::buscarEmpresaPorIdEmpresa(), [
            'idempresa' => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }

        if($results->numRows() == 0) return "";
        
        $figrel = str_replace("../","", $results->data[0]["logosis"]);
        $imagedata = file_get_contents(_CARBON_ROOT.$figrel);
        return base64_encode($imagedata);
    }

    public static function buscarPreferenciaPessoa($caminho, $idpessoa){
		return PessoaController::buscarPreferenciaPessoa($caminho, $idpessoa);
	}
    
    public static function buscarRelatorioPorIdRepEColunaPrimaria($idRep)
    {
        $colunasRelatorio = SQL::ini(_RepQuery::buscarRelatorioPorIdRepEColunaPrimaria(), [
            'idrep' => $idRep
        ])::exec();

        if ($colunasRelatorio->error()) {
            parent::error(__CLASS__, __FUNCTION__, $colunasRelatorio->errorMessage());
            return "";
        }

        return $colunasRelatorio->data;
    }

    public static function buscarConfiguracaoRelatorioPorIdRep( $inIdrep ){
    
        // $sqlrep = "SELECT distinct
        //         r.idrep,
        //         r.rep,
        //         r.idreptipo,
        //         r.cssicone,
        //         r.url,
        //         r.showfilters,
        //         r.header,
        //         r.footer,
        //         r.tab,
        //         tc.code,
        //         r.newgrouppagebreak,
        //         r.pbauto,
        //         r.showtotalcounter,
        //         rc.col,
        //         rc.psqkey,
        //         rc.psqreq,
        //         if(tc.rotcurto>'',tc.rotcurto,rc.col) as rotulo,
        //         rc.idrepcol,
        //         rc.visres,
        //         rc.align,
        //         rc.grp,
        //         rc.ordseq,
        //         rc.ordtype,
        //         rc.tsum,
        //         rc.acsum,
        //         rc.acavg,
        //         rc.tavg,
        //         rc.mascara,
        //         rc.hyperlink,
        //         rc.entre,
        //         rc.inseridomanualmente,
        //         rc.calendario,
        //         rc.like,
        //         rc.inval,
        //         rc.in,
        //         rc.findinset,
        //         rc.json,
        //         tc.datatype,
        //         r.compl,
        //         rc.ordcol,
        //         rc.eixograph,
        //         r.tipograph,
        //         r.descr,
        //         r.rodape
        //     FROM
        //         "._DBCARBON."._rep r
        //         JOIN "._DBCARBON."._repcol rc ON (rc.idrep = r.idrep AND r.idrep = ".$inIdrep.")
        //         LEFT JOIN "._DBCARBON."._mtotabcol tc ON (tc.tab = r.tab AND rc.col = tc.col)
        //         ORDER BY rc.ordcol";
    
        // $rrep = d::b()->query($sqlrep) or die("[getConfRelatorio] Erro ao recuperar relatórios do módulo: ".mysql_error(d::b())); 
        $relatorio = SQL::ini(_RepQuery::buscarConfiguracaoRelatorioPorIdRep(), [
            'idrep' => $inIdrep
        ])::exec();

        $arrRepConf = array();

        // while ($r = mysql_fetch_assoc($rrep)){
        foreach($relatorio->data as $r)
        {
            $nomeColCan=strtolower(retira_acentos($r["col"]));
    
            $arrRepConf[$r["idrep"]]["rep"]                 = $r["rep"];
            $arrRepConf[$r["idrep"]]["valorposfixado"]      = $r["valorposfixado"];
            $arrRepConf[$r["idrep"]]["tab"]                 = $r["tab"];
            $arrRepConf[$r["idrep"]]["url"]                 = $r["url"];
            $arrRepConf[$r["idrep"]]["ord"]                 = $r["ord"];
            $arrRepConf[$r["idrep"]]["idrep"]               = $r["idrep"];
            $arrRepConf[$r["idrep"]]["compl"]               = $r["compl"];
            $arrRepConf[$r["idrep"]]["descr"]               = $r["descr"];
            $arrRepConf[$r["idrep"]]["header"]              = $r["header"];
            $arrRepConf[$r["idrep"]]["footer"]              = $r["footer"];
            $arrRepConf[$r["idrep"]]["rodape"]              = $r["rodape"];
            $arrRepConf[$r["idrep"]]["pbauto"]              = $r["pbauto"];
            $arrRepConf[$r["idrep"]]["cssicone"]            = $r["cssicone"];
            $arrRepConf[$r["idrep"]]["tipograph"]           = $r["tipograph"];
            $arrRepConf[$r["idrep"]]["showfilters"]         = $r["showfilters"];
            $arrRepConf[$r["idrep"]]["showtotalcounter"]    = $r["showtotalcounter"];
            $arrRepConf[$r["idrep"]]["newgrouppagebreak"]   = $r["newgrouppagebreak"];
            //Colunas
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["in"]                     = $r["in"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["col"]                    = $r["col"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["grp"]                    = $r["grp"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["like"]                   = $r["like"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"]                   = $r["code"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tsum"]                   = $r["tsum"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tavg"]                   = $r["tavg"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"]                  = $r["entre"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inval"]                  = $r["inval"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["align"]                  = $r["align"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acsum"]                  = $r["acsum"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acavg"]                  = $r["acavg"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["rotulo"]                 = $r["rotulo"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqreq"]                 = $r["psqreq"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqkey"]                 = $r["psqkey"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["visres"]                 = $r["visres"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["ordcol"]                 = $r["ordcol"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["mascara"]                = $r["mascara"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["datatype"]               = $r["datatype"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["hyperlink"]              = $r["hyperlink"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["eixograph"]              = $r["eixograph"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["findinset"]              = $r["findinset"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["calendario"]             = $r["calendario"];
            $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inseridomanualmente"]    = $r["inseridomanualmente"];
            
            if (($r["datatype"] == 'date' or $r["datatype"] == 'datetime') and $r["psqkey"] == 'Y'){
                $arrRepConf[$r["idrep"]]["_datas"][] = $nomeColCan;
            }
            //Colunas visíveis no relatório
            if($r["visres"]=="Y"){
                if ($r["ordcol"] == 0) $r["ordcol"] = '';
    
                $arrRepConf[$r["idrep"]]["_colvisiveis"][$r["ordcol"]] = $nomeColCan;
            }
            //Colunas para order by
            if(!empty($r["ordtype"])){
                if(empty($r["ordseq"]) and $r["ordseq"]!==0) 
                    die("[getConfRelatorio]: Erro: A coluna [".$nomeColCan."] está configurada para ORDER BY, mas não possui uma posição informada");
    
                $arrRepConf[$r["idrep"]]["_orderby"][$r["ordseq"]] = $nomeColCan." ".$r["ordtype"];
            }
    
            if($r["grp"]=="Y"){
                $arrRepConf[$r["idrep"]]["_groupby"][] = $nomeColCan;
            }
            
            if(strlen(trim($r["json"]))>0){
                $fp = fopen("php://temp/", 'w');
                fputs($fp, $r["json"]);
                rewind($fp);
                ob_start();
                require "data://text/plain;base64,". base64_encode(stream_get_contents($fp));
                $jsoncol= ob_get_clean();
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["json"]=$jsoncol;
            }
            
            if(strlen(trim($r["code"]))>0){
                $fp = fopen("php://temp/", 'w');
                fputs($fp, $r["code"]);
                rewind($fp);
                ob_start();
                require "data://text/plain;base64,". base64_encode(stream_get_contents($fp));
                $codecol= ob_get_clean();
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"]=$codecol;
            }
        }
    
        return $arrRepConf;
    }

    public static function buscarConfiguracaoDoModuloRelatorio($inMod,$inCompleto=false,$inIdrep=false, $ordercol='rc.ordcol')
    {
        $clausulaRep = $inIdrep?" and r.idrep=".$inIdrep:" ";
        
        // $sqlrep = "SELECT distinct
        // 		r.idrep,
        // 		r.rep,
        // 		r.idreptipo,
        // 		r.cssicone,
        // 		r.url,
        // 		r.showfilters,
        // 		r.header,
        // 		r.footer,
        // 		r.tab,
        // 		tc.code,
        // 		r.newgrouppagebreak,
        // 		r.pbauto,
        // 		r.showtotalcounter,
        // 		rc.col,
        // 		rc.psqkey,
        // 		rc.psqreq,
        // 		if(tc.rotcurto>'',tc.rotcurto,rc.col) as rotulo,
        // 		rc.idrepcol,
        // 		rc.idrep,
        // 		rc.visres,
        // 		rc.align,
        // 		rc.grp,
        // 		rc.ordseq,
        // 		rc.ordtype,
        // 		rc.tsum,
        // 		rc.acsum,
        // 		rc.acavg,
        // 		rc.tavg,
        // 		rc.mascara,
        // 		rc.hyperlink,
        // 		rc.entre,
        // 		rc.inseridomanualmente,
        // 		rc.calendario,
        // 		rc.entre,
        // 		rc.like,
        // 		rc.inval,
        // 		rc.in,
        // 		rc.findinset,
        // 		rc.json,
        // 		mr.ord,
        // 		tc.datatype,
        // 		r.compl,
        // 		rc.ordcol,
        // 		rc.eixograph,
        // 		r.tipograph,
        // 		r.descr,
        // 		m.tab as tabfull,
        // 		lr.flgunidade,
        //         CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts,
        // 		rodape
        // 	FROM
        // 		"._DBCARBON."._rep r
        // 		LEFT JOIN "._DBCARBON."._lprep lr on (lr.idrep = r.idrep and lr.flgunidade='Y')
        // 		JOIN "._DBCARBON."._repcol rc on rc.idrep=r.idrep ".$wrep."
        // 		JOIN "._DBCARBON."._modulorep mr ON mr.modulo='".$inMod."' and mr.idrep=r.idrep
        // 		LEFT JOIN "._DBCARBON."._mtotabcol tc on tc.tab = r.tab AND rc.col = tc.col 
        // 		LEFT JOIN "._DBCARBON."._modulo m ON m.modulo= mr.modulo
        // 		LEFT JOIN "._DBCARBON."._modulo mv ON (mv.modulo=m.modvinculado)
        // 		order by $ordercol";
        //die($sqlrep);
        // $rrep = d::b()->query($sqlrep) or die("Erro ao recuperar relatórios do módulo: ".mysql_error(d::b())); 
        $relatorios = SQL::ini(_RepQuery::buscarRelatoriosVinculadosEmLpPorModulo(), [
            'clausularep' => $clausulaRep,
            'modulo' => $inMod,
            'ordem' => $ordercol
        ])::exec();
    
        $arrRepConf=array();
        // while ($r = mysql_fetch_assoc($rrep)){
        foreach($relatorios->data as $r)
        {
            $nomeColCan=strtolower(retira_acentos(str_replace("", "", $r["col"])));
            //$nomeColCan=strtolower(retira_acentos(str_replace(" ", "", $r["col"])));
    
            //Caso seja necessário recuperar todos as colunas da consulta
            if($inCompleto){
                //Configurações do relatório
                $arrRepConf[$r["idrep"]]["header"]=$r["header"];
                $arrRepConf[$r["idrep"]]["footer"]=$r["footer"];
                $arrRepConf[$r["idrep"]]["rep"]=$r["rep"];
                $arrRepConf[$r["idrep"]]["tab"]=$r["tab"];
                $arrRepConf[$r["idrep"]]["compl"]=$r["compl"];
                $arrRepConf[$r["idrep"]]["descr"]=$r["descr"];
                $arrRepConf[$r["idrep"]]["rodape"]=$r["rodape"];
                $arrRepConf[$r["idrep"]]["newgrouppagebreak"]=$r["newgrouppagebreak"];
                $arrRepConf[$r["idrep"]]["pbauto"]=$r["pbauto"];
                $arrRepConf[$r["idrep"]]["showtotalcounter"]=$r["showtotalcounter"];
                $arrRepConf[$r["idrep"]]["tabfull"]=$r["tabfull"];
                $arrRepConf[$r["idrep"]]["chavefts"]=$r["chavefts"];
                $arrRepConf[$r["idrep"]]["tipograph"]=$r["tipograph"];
                $arrRepConf[$r["idrep"]]["flgunidade"]=$r["flgunidade"];
                //Colunas
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["rotulo"]= $r["rotulo"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["col"]= $r["col"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqreq"]=$r["psqreq"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["calendario"]=$r["calendario"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"]=$r["entre"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["like"]=$r["like"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inval"]=$r["inval"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["in"]=$r["in"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["findinset"]=$r["findinset"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqkey"]=$r["psqkey"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"]=$r["code"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["datatype"]=$r["datatype"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["visres"]=$r["visres"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["align"]=$r["align"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["hyperlink"]=$r["hyperlink"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["grp"]=$r["grp"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tsum"]=$r["tsum"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acsum"]=$r["acsum"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acavg"]=$r["acavg"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tavg"]=$r["tavg"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["mascara"]=$r["mascara"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"]=$r["entre"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inseridomanualmente"]=$r["inseridomanualmente"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["ordcol"]=$r["ordcol"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["eixograph"]=$r["eixograph"];
                
                if (($r["datatype"] == 'date' or $r["datatype"] == 'datetime') and $r["psqkey"] == 'Y'){
                    $arrRepConf[$r["idrep"]]["_datas"][] = $nomeColCan;
                }
                //Colunas visíveis no relatório
                if($r["visres"]=="Y" //and $r["inseridomanualmente"]=="N"
                ){
                    if ($r["ordcol"] ==0){
                        $r["ordcol"] = '';
                    }
                    $arrRepConf[$r["idrep"]]["_colvisiveis"][$r["ordcol"]]=$nomeColCan;
                }
                //Colunas para order by
                if(!empty($r["ordtype"])){
                    if(empty($r["ordseq"]) and $r["ordseq"]!==0)die("getConfRelatoriosModulo: Erro: A coluna [".$nomeColCan."] está configurada para ORDER BY, mas não possui uma posição informada");
                    $arrRepConf[$r["idrep"]]["_orderby"][$r["ordseq"]]=$nomeColCan." ".$r["ordtype"];
                }
                if($r["grp"]=="Y"){
                    $arrRepConf[$r["idrep"]]["_groupby"][]=$nomeColCan;
                }
            }
    
            //Montar colunas específicas para devolver ao browser. Isto evita enviar colunas desnecessárias por motivo de segurança e perormance
            $arrRepConf[$r["idrep"]]["idrep"]=$r["idrep"];
            $arrRepConf[$r["idrep"]]["rep"]=$r["rep"];
            $arrRepConf[$r["idrep"]]["cssicone"]=$r["cssicone"];
            $arrRepConf[$r["idrep"]]["showfilters"]=$r["showfilters"];
            $arrRepConf[$r["idrep"]]["ord"]=$r["ord"];
            $arrRepConf[$r["idrep"]]["url"]=$r["url"];
            if($r["psqkey"]=="Y"){
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["rotulo"]=$r["rotulo"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqreq"]=$r["psqreq"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["calendario"]=$r["calendario"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"]=$r["entre"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["like"]=$r["like"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inval"]=$r["inval"];
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["findinset"]=$r["findinset"];
            }
            
            if(strlen(trim($r["json"]))>0){
                //Cria arquivo em memória ram com o código php a ser executado, para evitar uso de eval e também evitar a necessidade de geração de arquivos externos na pasta "eventcode"
                $fp = fopen("php://temp/", 'w');
                fputs($fp, $r["json"]);
                rewind($fp);
                ob_start();	//Não gerar saída para o browser
                require "data://text/plain;base64,". base64_encode(stream_get_contents($fp));
                $jsoncol= ob_get_clean();//Limpa a saída antes que seja enviada para o browser
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["json"]=$jsoncol;//Recupera os valores gerados pelo include
            
            }if(strlen(trim($r["code"]))>0){
                //Cria arquivo em memória ram com o código php a ser executado, para evitar uso de eval e também evitar a necessidade de geração de arquivos externos na pasta "eventcode"
                $fp = fopen("php://temp/", 'w');
                fputs($fp, $r["code"]);
                rewind($fp);
                ob_start();	//Não gerar saída para o browser
                require "data://text/plain;base64,". base64_encode(stream_get_contents($fp));
                $codecol= ob_get_clean();//Limpa a saída antes que seja enviada para o browser
                $arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"]=$codecol;//Recupera os valores gerados pelo include
            }
        }
        if($_SESSION["SESSAO"]["USUARIO"]=="marcelo"){
        //	print_r($arrRepConf);die;
        }
        return $arrRepConf;
    }

    public static function urlAmigavel($string)
    {
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
        return strtolower(trim($string, '-'));
    }

    public static function contemDecimal($value)
    {
        return (strpos( $value, "." ) !== false);
    }
    public static function verificarData($data){
        //cria um array
        $array = explode('/', $data);
    
        //garante que o array possue tres elementos (dia, mes e ano)
        if(count($array) == 3){
            $dia = (int)$array[0];
            $mes = (int)$array[1];
            $ano = (int)$array[2];
    
            //testa se a data é válida
            if(checkdate($mes, $dia, $ano)){
                return true;
            }else{
            return false;
            }
        }else{
            return false;
        }
    }

    public static function verificarLpPorIdLpEIdRep($idLp, $idRep)
    {
        $verificacao = SQL::ini(_LpRepQuery::verificarLpPorIdLpEIdRep(), [
            'idlp' => $idLp,
            'idrep' => $idRep
        ])::exec();

        if ($verificacao->error()) {
            parent::error(__CLASS__, __FUNCTION__, $verificacao->errorMessage());
            return [];
        }

        return $verificacao->data;
    }

    public static function alterarSQLMode($mode)
    {
        $alteracao = SQL::ini(_RepQuery::alterarSQLMode(), [
            'mode' => $mode
        ])::exec();

        if ($alteracao->error()) {
            parent::error(__CLASS__, __FUNCTION__, $alteracao->errorMessage());
            return [];
        }
    }

    public static function buscarLpRepPorIdRepEIdLps($idRep, $idLp, $echoQuery = false)
    {
        $lpRep = SQL::ini(_LpRepQuery::buscarLpRepPorIdRepEIdLps(), [
            'idrep' => $idRep,
            'idlp' => $idLp
        ])::exec();

        if($echoQuery)
        {
            echo $lpRep->sql();
        }

        if ($lpRep->error()) {
            parent::error(__CLASS__, __FUNCTION__, $lpRep->errorMessage());
            return [];
        }

        return $lpRep->data;
    }

    public static function buscarRelatorioDinamico($colunas, $query)
    {
        $relatorios = SQL::ini(_RepQuery::buscarRelatorioDinamico(), [
            'colunas' => $colunas,
            'query' => $query
        ])::exec();

        echo "<!-- ".$relatorios->sql()." -->";	

        if ($relatorios->error()) {
            parent::error(__CLASS__, __FUNCTION__, $relatorios->errorMessage());
            return [];
        }

        $_SESSION["SEARCH"]["SQL"] = $relatorios->sql();

        return $relatorios->data;
    }

    public static function buscarVisualizacaograf($query)
    {
        $res = SQL::ini(_RepQuery::buscarVisualizacaograf(), [
            'query' => $query
        ])::exec();

        if ($res->error()) {
            parent::error(__CLASS__, __FUNCTION__, $res->errorMessage());
            return [];
        }

        return $res->data[0];
    }

    public static function buscarDadosgraf($query)
    {
        $res = SQL::ini(_RepQuery::buscarVisualizacaograf(), [
            'query' => $query
        ])::exec();

        if ($res->error()) {
            parent::error(__CLASS__, __FUNCTION__, $res->errorMessage());
            return [];
        }

        return $res->data;
    }


    public static function buscarRelatorioDinamicoEvento($ideventotipo, $colunas)
    {
        $colunasrep = SQL::ini(EventoTipoCamposQuery::buscarCamposVisiveisPorIdEventoTipoIn(), [
            'colunas' => $colunas,
            'ideventotipo' => $ideventotipo
        ])::exec();

     

        if ($colunasrep->error()) {
            parent::error(__CLASS__, __FUNCTION__, $colunasrep->errorMessage());
            return [];
        }
        $arraycolunas = array();
        foreach($colunasrep->data as $key => $value){
            $arraycolunas[$value['col']] = $value;
        }

        return $arraycolunas;
    }

    public static function buscarValorColunaRelatorioDinamicoEvento($code, $val)
    {
        $coderesp = SQL::ini($code)::exec();
        if ($coderesp->error()) {
            parent::error(__CLASS__, __FUNCTION__, $coderesp->errorMessage());
            return [];
        }
        $valresp = '';
        $chave = array_keys($coderesp->data[0]);
        foreach($coderesp->data as $key => $value){
            if($value[$chave[0]] == $val){
                $valresp = $value[$chave[1]];
            }
        }
        
        return $valresp;
    }

    public static function buscarColunasComCoordenadas($idRep)
    {
        $relatorio = SQL::ini(_RepQuery::buscarColunasComCoordenadas(), [
            'idrep' => $idRep
        ])::exec();

        if ($relatorio->error()) {
            parent::error(__CLASS__, __FUNCTION__, $relatorio->errorMessage());
            return [];
        }

        return $relatorio->data;
    }

    public static function buscarConsumoInsumoPorIdLoteENnfe($IdLote, $nnfe)
    {
        $loteConsumoInsumo = SQL::ini(Vw8LoteConsInsumoQuery::buscarConsumoInsumoPorIdLoteENnfe(), [
            'nnfe' => $nnfe,
            'idlote' => $IdLote
        ])::exec();

        if ($loteConsumoInsumo->error()) {
            parent::error(__CLASS__, __FUNCTION__, $loteConsumoInsumo->errorMessage());
            return [];
        }

        echo "<!-- ".$loteConsumoInsumo->sql()." -->";	//echo $_sqlresultado;

        return $loteConsumoInsumo->data;        
    }

    public static function buscarPontosPendentes($data1, $data2, $intervalo)
    {
        $pontosPendentes = SQL::ini(VwPontoQuery::buscarPontosPendentes(), [
            'data1' => $data1,
            'data2' => $data2,
            'intervalo' => $intervalo
        ])::exec();

        if ($pontosPendentes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $pontosPendentes->errorMessage());
            return [];
        }

        return $pontosPendentes->data;
    }

    public static function buscarHorasExtrasPendentesAnteriores($idPessoa, $data)
    {
        $horasExtrasPendentes = SQL::ini(RhEventoQuery::buscarHorasExtrasPendentesAnteriores(), [
            'data' => $data,
            'idpessoa' => $idPessoa
        ])::exec();

        if ($horasExtrasPendentes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $horasExtrasPendentes->errorMessage());
            return [];
        }

        return $horasExtrasPendentes->data[0];
    }

    public static function buscarHorasPendentesPorDataEventoEIdPessoaEStatus($idPessoa, $dataEvento, $status)
    {
        $horasPendentes = SQL::ini(RhEventoQuery::buscarHorasPendentesPorDataEventoEIdPessoaEStatus(), [
            'dataevento' => $dataEvento,
            'idpessoa' => $idPessoa,
            'status' => $status
        ])::exec();

        if ($horasPendentes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $horasPendentes->errorMessage());
            return [];
        }

        return $horasPendentes->data[0];
    }

    public static function buscarHorasExtras($idPessoa, $dataEvento)
    {
        $horasExtras = SQL::ini(RhEventoQuery::buscarHorasExtras(), [
            'dataevento' => $dataEvento,
            'idpessoa' => $idPessoa,
        ])::exec();

        if ($horasExtras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $horasExtras->errorMessage());
            return [];
        }

        return $horasExtras->data[0];
    }

    public static function buscarRelatorioFluxoDeCaixaPorClausula($clausula)
    {
        $relatorio = SQL::ini(_RepQuery::buscarRelatorioFluxoDeCaixaPorClausula(), [
            'clausula' => $clausula
        ])::exec();

        echo "<!-- ".array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"])." -->";	//echo $_sqlresultado;
        //Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
        $_SESSION["SEARCH"]["SQL"] = $relatorio->sql();

        echo "<!-- ".$relatorio->sql()." -->";	

        if ($relatorio->error()) {
            parent::error(__CLASS__, __FUNCTION__, $relatorio->errorMessage());
            return false;
        }
        

        return $relatorio->data;
    }

    public static function buscarRelatorioDespesasPorViewClausulaEClausulaUnidade($colunas, $view, $clausula, $clausulaUnidade)
    {
        $query = SQL::mount(Vw8DespesasQuery::buscarRelatorioDespesasPorViewClausulaEClausulaUnidade(), [
            'view' => $view,
            'clausula' => $clausula,
            'clausulaunidade' => $clausulaUnidade
        ]);

        return self::buscarRelatorioDinamico($colunas, "($query) as bb");
    }
}
?>
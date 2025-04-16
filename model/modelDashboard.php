<?
function inativarDashboarcomDashcardsInativos($_inspecionar_sql)
{
    $sql = "UPDATE dashcard dc JOIN dashboard d ON d.iddashcard = dc.iddashcard 
               SET d.status = 'INATIVO'
             WHERE NOT EXISTS(SELECT 1 
                                FROM dashcard c JOIN dashpanel AS p ON (c.iddashpanel = p.iddashpanel) JOIN dashgrupo AS g ON (g.iddashgrupo = p.iddashgrupo)
                               WHERE p.status = 'ATIVO'
                                 AND c.status = 'ATIVO'
                                 AND g.status = 'ATIVO'
                                 AND dc.iddashcard = c.iddashcard)";

    if($_inspecionar_sql){
        echo ('<hr><strong>Inativa dashboard com dashcards inativos</strong><br><pre>'.$sql.'</pre><hr>');
    }

    $res = d::b()->query($sql);

    if(!$res){
        echo "Falha na atualização dos dashboards INATIVOS: ".mysqli_error(d::b())."<p>SQL: $sql";
    }
}

function buscaDashcardsAtivosParaInsertUpdate($_inspecionar_sql, $_id, $ler, $rid, $idDashLp)
{
    $sql = "SELECT * FROM (SELECT em.idempresa,
                                    c.tab,
                                    c.modulo,
                                    c.calculo,
                                    c.cron,
                                    c.tipocalculo,
                                    c.colcalc,
                                    c.tipoobjeto,
                                    c.objeto,
                                    c.iddashcard,
                                    g.dashboard,
                                    c.iddashpanel,
                                    p.panelclasscol,
                                    p.paneltitle,
                                    c.code,
                                    c.cardclasscol,
                                    c.cardurl,
                                    c.cardurltipo,
                                    c.cardurljs,
                                    c.cardordenacao,
                                    c.cardsentido,
                                    c.cardnotificationbg,
                                    c.cardnotification,
                                    c.cardcolor,
                                    c.cardbordercolor,
                                    c.cardbgclass,
                                    c.cardtitle,
                                    c.cardtitlesub,
                                    c.cardicon,
                                    c.cardrow,
                                    c.cardtitlemodal,
                                    c.cardurlmodal,
                                    IF(c.ordem > 0, c.ordem, fs.ordem) AS card_ordem,
                                    c.executadoem,
                                    c.execucao,
                                    p.ordem AS panel_ordem,
                                    g.ordem AS grupo_ordem,
                                    g.rotulo AS grupo_rotulo,
                                    g.iddashgrupo,
                                    (SELECT col FROM carbonnovo._mtotabcol m WHERE m.tab = c.tab AND primkey = 'Y') AS col,
                                    f.colprazod
                            FROM dashpanel AS p JOIN dashcard AS c ON (c.iddashpanel = p.iddashpanel)
                       LEFT JOIN dashgrupo AS g ON (g.iddashgrupo = p.iddashgrupo)
                            JOIN fluxostatus fs ON fs.idfluxostatus = c.objeto
                            JOIN fluxo f ON f.idfluxo = fs.idfluxo AND f.status = 'ATIVO'
                            JOIN carbonnovo._status s ON s.idstatus = fs.idstatus AND NOT statustipo IN ('CANCELADO' , 'CONCLUIDO')
                            JOIN empresa em ON em.status = 'ATIVO'
                           WHERE p.status = 'ATIVO'
                             AND c.status = 'ATIVO'
                             AND g.status = 'ATIVO'
                             AND c.tipoobjeto = 'fluxostatus' 
                             AND c.iddashcard IN ($idDashLp)
                    UNION ALL 
                         SELECT em.idempresa,
                                c.tab,
                                c.modulo,
                                c.calculo,
                                c.cron,
                                c.tipocalculo,
                                c.colcalc,
                                c.tipoobjeto,
                                c.objeto,
                                c.iddashcard,
                                g.dashboard,
                                c.iddashpanel,
                                p.panelclasscol,
                                p.paneltitle,
                                c.code,
                                c.cardclasscol,
                                c.cardurl,
                                c.cardurltipo,
                                c.cardurljs,
                                c.cardordenacao,
                                c.cardsentido,
                                c.cardnotificationbg,
                                c.cardnotification,
                                c.cardcolor,
                                c.cardbordercolor,
                                c.cardbgclass,
                                c.cardtitle,
                                c.cardtitlesub,
                                c.cardicon,
                                c.cardrow,
                                c.cardtitlemodal,
                                c.cardurlmodal,
                                IF(c.ordem > 0, c.ordem, fs.ordem) AS card_ordem,
                                c.executadoem,
                                c.execucao,
                                p.ordem AS panel_ordem,
                                g.ordem AS grupo_ordem,
                                g.rotulo AS grupo_rotulo,
                                g.iddashgrupo,
                                (SELECT col FROM carbonnovo._mtotabcol m WHERE m.tab = c.tab AND primkey = 'Y') AS col,
                                f.colprazod
                           FROM dashpanel AS p JOIN dashcard AS c ON (c.iddashpanel = p.iddashpanel)
                      LEFT JOIN dashgrupo AS g ON (g.iddashgrupo = p.iddashgrupo)
                           JOIN etapa e ON e.idetapa = c.objeto
                           JOIN fluxostatus fs ON fs.idetapa = e.idetapa
                           JOIN fluxo f ON f.idfluxo = fs.idfluxo AND f.status = 'ATIVO'
                           JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
                           JOIN empresa em ON em.status = 'ATIVO'
                          WHERE p.status = 'ATIVO'
                            AND c.status = 'ATIVO'
                            AND g.status = 'ATIVO'
                            AND s.statustipo NOT IN ('CANCELADO' , 'CONCLUIDO')
                            AND c.tipoobjeto = 'etapa' 
                            AND c.iddashcard IN ($idDashLp)
                    UNION ALL 
                         SELECT c.idempresa AS idempresa,
                                c.tab,
                                c.modulo,
                                c.calculo,
                                c.cron,
                                c.tipocalculo,
                                c.colcalc,
                                c.tipoobjeto,
                                c.objeto,
                                c.iddashcard,
                                g.dashboard,
                                c.iddashpanel,
                                p.panelclasscol,
                                p.paneltitle,
                                c.code,
                                c.cardclasscol,
                                c.cardurl,
                                c.cardurltipo,
                                c.cardurljs,
                                c.cardordenacao,
                                c.cardsentido,
                                c.cardnotificationbg,
                                c.cardnotification,
                                c.cardcolor,
                                c.cardbordercolor,
                                c.cardbgclass,
                                c.cardtitle,
                                c.cardtitlesub,
                                c.cardicon,
                                c.cardrow,
                                c.cardtitlemodal,
                                c.cardurlmodal,
                                c.ordem AS card_ordem,
                                c.executadoem,
                                c.execucao,
                                p.ordem AS panel_ordem,
                                g.ordem AS grupo_ordem,
                                g.rotulo AS grupo_rotulo,
                                g.iddashgrupo,
                                (SELECT col FROM carbonnovo._mtotabcol m WHERE m.tab = c.tab AND primkey = 'Y') AS col,
                                '' AS colprazod
                            FROM dashpanel AS p JOIN dashcard AS c ON (c.iddashpanel = p.iddashpanel)
                            JOIN dashgrupo AS g ON (g.iddashgrupo = p.iddashgrupo)
                           WHERE p.status = 'ATIVO'
                             AND c.status = 'ATIVO'
                             AND g.status = 'ATIVO'
                             AND c.tipoobjeto = 'manual'
                             AND c.iddashcard IN ($idDashLp)) a
                $_id
                ORDER BY tipoobjeto DESC, tab, colprazod, a.iddashgrupo, a.iddashpanel, a.iddashcard, idempresa";

    if($_inspecionar_sql){
        echo ('<strong>Consulta Principal para dar insert no Dashboard</strong><br><pre>'.$sql.'</pre><hr>');
    }

    $res = d::b()->query($sql);

    if(!$res){
        $strerr = "A Consulta nos dashcards falhou : ".mysqli_error(d::b())."<p>SQL: $sql";
        if($ler) error_log($rid.$strerr);
    } else {
        if($ler) error_log($rid."Consulta dashcards ok: ".mysqli_num_rows($res)." registros");
    }

    return $res;
}


function insereAtaulizaIndicadoresManuais($row = [], $_inspecionar_sql = false, $clausula = "", $group = ""){

    if($row['calculo'] == 'Y' and $row['cron'] != 'N'){

        $calc = !empty($row['tipocalculo']) ? $row['tipocalculo']."(".$row['colcalc'].")" : '';

        $valor_calculo = "".$calc." as card_value,
        ".($row['cardcolor'])." as card_color,
        ".($row['cardbordercolor'])." as card_border_color,
        ".($row['cardurl'])." as card_url,";

        $from = "from ".addslashes($row['tab'])." WHERE 1 ";
    } else {

        $valor_calculo = "card_value as card_value,
        card_color as card_color,
        card_border_color as card_border_color,
        card_url as card_url,";

        $from = "from dashboard where iddashcard = ".addslashes($row['iddashcard'])." ";
        $clausula = "";
    }

    $calctab = array('vwevento', 'vweventoconselho', 'vweventoti');
    if(in_array($row['tab'], $calctab)){
        $sql_atraso = "SUM(
                        IFNULL(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(prazoamd,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), 1, NULL),IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(datafim,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), 1, 0))) AS card_atraso_value,          
                        GROUP_CONCAT(IFNULL(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(prazoamd,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), ".$row['colcalc'].", null),IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(datafim,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), ".$row['colcalc'].", null))) AS card_atraso_url";
    } else {
        $sql_atraso = "0 AS card_atraso_value,          
                      '' AS card_atraso_url";
    }

    //Dados para insert tipoobjeto = Manual
    $sql_nova = "SELECT '".addslashes($row['iddashcard'])."' as iddashboard, 
                        'Y' as cron, 
                        '".addslashes($row['dashboard'])."' as dashboard,
                        '".addslashes($row['rotulo'])."' as rotulo,
                        '".addslashes($row['iddashpanel'])."' as iddashpanel,
                        '".addslashes($row['panelclasscol'])."' as panel_class_col,
                        '".addslashes($row['paneltitle'])."' as panel_title,
                        '".addslashes($row['code'])."' as code,
                        '".addslashes($row['tab'])."' as tab,
                        '".addslashes($row['modulo'])."' as modulo,
                        '".addslashes($row['col'])."' as col,
                        '".addslashes($row['tipoobjeto'])."' as tipoobjeto,
                        '".addslashes($row['objeto'])."' as objeto,
                        '".addslashes($row['iddashcard'])."' as iddashcard,
                        '".addslashes($row['cardclasscol'])."' as card_class_col,
                        '".addslashes($row['cardurltipo'])."' as card_url_tipo,
                        '".($row['cardurljs'])."' as card_url_js,
                        '".addslashes($row['cardordenacao'])."' as ordenacao,
                        '".addslashes($row['cardsentido'])."' as sentido,
                        '".addslashes($row['cardnotificationbg'])."' as card_notification_bg,
                        '".addslashes($row['cardnotification'])."' as card_notification,			
                        '".addslashes($row['cardtitle'])."' as card_title,
                        '".addslashes($row['cardtitlesub'])."' as card_title_sub,	
                        $valor_calculo
                        '".addslashes($row['cardicon'])."' as card_icon,
                        '".addslashes($row['cardrow'])."' as card_row,
                        '".addslashes($row['cardtitlemodal'])."' as card_title_modal,
                        '".addslashes($row['cardurlmodal'])."' as card_url_modal,
                        $sql_atraso,
                        'ATIVO',
                        ".($row['card_ordem'] * 1)." as card_ordem,
                        ".($row['panel_ordem'] * 1)." as panel_ordem,
                        ".($row['grupo_ordem'] * 1)." as grupo_ordem,
                        '".addslashes($row['grupo_rotulo'])."' as grupo_rotulo,
                        ".($row['iddashgrupo'])." as iddashgrupo,
                        '".($row['idempresa'])."' as idempresa, 
                        'laudo', now(), 'laudo', now()
                        $from        
                        $clausula
                        $group;";

    if($_inspecionar_sql){
        echo ('dados para insert tipoobjeto manual <pre>'.$sql_nova.'</pre>');
    }

    $resNova = d::b()->query($sql_nova);

    if(!$resNova){
        //echo "Falha dados para insert tipoobjeto manual: ".mysqli_error(d::b())."<p>SQL: $sql_nova";
        $sqlLog = "INSERT INTO log (idempresa, tipoobjeto, idobjeto, tipolog, log, info, status, criadoem, data) 
                                VALUES ('".$row['idempresa']."', 'cron', 'testedash', '".$row['iddashcard']."', 'Atualização INSERT DASHBOARD ".addslashes(mysqli_error(d::b()))."</p>', '<p>SQL:".addslashes($sql_nova)."', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqlLog) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlLog);
    }

    while ($rowi = mysqli_fetch_assoc($resNova)){
        echo "Início: ".date("d/m/Y H:i:s", time()).'<br>';
        $datetime1 = new DateTime();

        //insert tipoobjeto manual
        $sqlx = "INSERT INTO `laudo`.`dashboard`
                        (`iddashcard`,
                        `cron`,
                        `dashboard`,
                        `dashboard_title`,
                        `panel_id`,
                        `panel_class_col`,
                        `panel_title`,
                        `code`,
                        `tab`,
                        `modulo`,
                        `col`,
                        `tipoobjeto`,
                        `objeto`,
                        `card_id`,
                        `card_class_col`,
                        `card_url`,
                        `card_url_tipo`,
                        `card_url_js`,
                        `ordenacao`,
                        `sentido`,
                        `card_notification_bg`,
                        `card_notification`,
                        `card_title`,
                        `card_title_sub`,
                        `card_value`,
                        `card_color`,
                        `card_border_color`,
                        `card_icon`,
                        `card_row`,
                        `card_title_modal`,
                        `card_url_modal`,
                        `card_atraso_value`,
                        `card_atraso_url`,
                        `status`,
                        `card_ordem`,
                        `panel_ordem`,
                        `grupo_ordem`,
                        `grupo_rotulo`,
                        `iddashgrupo`,
                        `idempresa`,
                        `criadopor`,
                        `criadoem`,
                        `alteradopor`,
                        `alteradoem`)
                        SELECT '".addslashes($rowi['iddashcard'])."', 
                        'Y', 
                        '".addslashes($rowi['dashboard'])."',
                        '".addslashes($rowi['rotulo'])."',
                        '".addslashes($rowi['iddashpanel'])."',
                        '".addslashes($rowi['panelclasscol'])."',
                        '".addslashes($rowi['paneltitle'])."',
                        '".addslashes($rowi['code'])."',
                        '".addslashes($rowi['tab'])."',
                        '".addslashes($rowi['modulo'])."',
                        '".addslashes($rowi['col'])."',
                        '".addslashes($rowi['tipoobjeto'])."',
                        '".addslashes($rowi['objeto'])."',
                        '".addslashes($rowi['iddashcard'])."',
                        '".addslashes($rowi['cardclasscol'])."',
                        '".$rowi['cardurl']."',
                        '".addslashes($rowi['cardurltipo'])."',
                        '".($rowi['cardurljs'])."',
                        '".addslashes($rowi['cardordenacao'])."',
                        '".addslashes($rowi['cardsentido'])."',
                        '".addslashes($rowi['cardnotificationbg'])."',
                        '".addslashes($rowi['cardnotification'])."',			
                        '".addslashes($rowi['cardtitle'])."',
                        '".addslashes($rowi['cardtitlesub'])."',	
                        '".addslashes($rowi['card_value'])."',
                        '".addslashes($rowi['card_color'])."',
                        '".addslashes($rowi['card_border_color'])."',
                        '".addslashes($rowi['cardicon'])."',
                        '".addslashes($rowi['cardrow'])."',
                        '".addslashes($rowi['cardtitlemodal'])."',
                        '".addslashes($rowi['cardurlmodal'])."',
                        '".addslashes($rowi['card_atraso_value'])."',
                        '_modulo=".$rowi['modulo']."&".$row['colcalc']."=[".addslashes($rowi['card_atraso_url'])."]',
                        'ATIVO',
                        ".($rowi['card_ordem'] * 1).",
                        ".($rowi['panel_ordem'] * 1).",
                        ".($rowi['grupo_ordem'] * 1).",
                        '".addslashes($rowi['grupo_rotulo'])."',
                        ".($rowi['iddashgrupo']).",
                        '".($rowi['idempresa'])."', 
                        'laudo', now(), 'laudo', now()
                        ON DUPLICATE KEY UPDATE `cron` = 'Y', 
                        `dashboard` ='".addslashes($rowi['dashboard'])."',
                        `dashboard_title` = '".addslashes($rowi['rotulo'])."',
                        `panel_id` = '".addslashes($rowi['iddashpanel'])."',
                        `panel_class_col` = '".addslashes($rowi['panel_class_col'])."',
                        `panel_title` = '".addslashes($rowi['panel_title'])."',
                        `code` ='".addslashes($rowi['code'])."',
                        `tab` = '".addslashes($rowi['tab'])."',
                        `modulo` = '".addslashes($rowi['modulo'])."',
                        `col` = '".addslashes($rowi['col'])."',
                        `tipoobjeto` = '".addslashes($rowi['tipoobjeto'])."',
                        `objeto` = '".addslashes($rowi['objeto'])."',
                        `card_id` = '".addslashes($rowi['iddashcard'])."',
                        `card_class_col` = '".addslashes($rowi['card_class_col'])."',
                        `card_url` = '".addslashes($rowi['card_url'])."',
                        `card_url_tipo` = '".addslashes($rowi['card_url_tipo'])."',
                        `card_url_js` = '".($rowi['card_url_js'])."',
                        `ordenacao` = '".addslashes($rowi['ordenacao'])."',
                        `sentido` = '".addslashes($rowi['sentido'])."',
                        `card_notification_bg` = '".addslashes($rowi['card_notification_bg'])."',
                        `card_notification` = '".addslashes($rowi['card_notification'])."',
                        `card_title` = '".addslashes($rowi['card_title'])."',
                        `card_title_sub` = '".addslashes($rowi['card_title_sub'])."',
                        `card_value` = '".addslashes($rowi['card_value'])."',
                        `card_color` = '".addslashes($rowi['card_color'])."',
                        `card_border_color` = '".addslashes($rowi['card_border_color'])."',
                        `card_icon` = '".addslashes($rowi['card_icon'])."',
                        `card_row` = '".addslashes($rowi['card_row'])."',
                        `card_title_modal` = '".addslashes($rowi['card_title_modal'])."',
                        `card_url_modal` = '".addslashes($rowi['card_url_modal'])."', 
                        `card_atraso_value` = '".addslashes($rowi['card_atraso_value'])."',
                        `card_atraso_url` = '_modulo=".$rowi['modulo']."&".$row['colcalc']."=[".addslashes($rowi['card_atraso_url'])."]',
                        `status` = 'ATIVO', 
                        `card_ordem` = ".($rowi['card_ordem']).", 
                        `panel_ordem` = ".($rowi['panel_ordem']).",
                        `grupo_ordem` = ".($rowi['grupo_ordem']).",
                        `grupo_rotulo` = '".addslashes($rowi['grupo_rotulo'])."',
                        `iddashgrupo` = ".($rowi['iddashgrupo']).",
                        `idempresa` = ".($rowi['idempresa']).",
                        `criadopor` = 'laudo', 
                        `criadoem` = now(), 
                        `alteradopor` = 'laudo', 
                        `alteradoem` = now();";

        if($_inspecionar_sql){
            echo ('<strong>insert tipoobjeto manual</strong><br><pre>'.$sqlx.'</pre><hr>');
        }

        $resX = d::b()->query($sqlx);    
        if(!$resX){
            echo "Falha na insert tipoobjeto manual: ".mysqli_error(d::b())."<p>SQL: $sqlx";
            $sqlLog = "INSERT INTO log (idempresa, tipoobjeto, idobjeto, tipolog, log, info, status, criadoem, data) 
                                VALUES ('".$row['idempresa']."', 'cron', 'testedash', '".$row['iddashcard']."', 'Atualização INSERT DASHBOARD ".addslashes(mysqli_error(d::b()))."</p>', '<p>SQL:".addslashes($sqlx)."', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
            d::b()->query($sqlLog) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlLog);
        }

        echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>';
        $datetime2 = new DateTime();
        $interval = $datetime1->diff($datetime2);

        echo "Execução: ".$interval->format('%H:%I:%S');

        $sqlupd = "UPDATE dashcard set executadoem = now() where iddashcard = ".$rowi['iddashcard'];
        d::b()->query($sqlupd) or die("Atualização dashcard .: ".mysqli_error(d::b())."<p>SQL: $sqlupd");
    }
}


function preparaIndicadoresFluxoEtapa($_inspecionar_sql, $row){
    global $i;
    global $cardurl;
    global $tipoobjeto;
    global $tab;
    global $cardcolor;
    global $col;
    global $colprazod;


    //atualiza os indicadores quando a tabela ou  a colprazod mudar
    if(($tab != $row['tab'] && $i > 0) || ($tab == $row['tab'] && $colprazod != $row['colprazod'])){

        if($_inspecionar_sql){
            echo '<br>Tabela: '.$tab.' $i: '.$i.' <br>';
        }

        insereAtaulizaIndicadoresFluxoEtapa($tipoobjeto, $cardurl, $tab, $cardcolor, $col, $_inspecionar_sql, $colprazod);
    }

    $i++;
    $cardurl = $row['cardurl'];
    $tipoobjeto = $row['tipoobjeto'];
    $tab = $row['tab'];
    $cardcolor = $row['cardcolor'];
    $col = $row['col'];
    $colprazod = $row['colprazod'];

    echo "Início: ".date("d/m/Y H:i:s", time()).'<br>';
    $datetime1 = new DateTime();

    $sqlx = "INSERT INTO `laudo`.`dashboard`
                (`iddashcard`,
                `cron`,
                `dashboard`,
                `dashboard_title`,
                `panel_id`,
                `panel_class_col`,
                `panel_title`,
                `code`,
                `tab`,
                `modulo`,
                `col`,
                `card_id`,
                `card_class_col`,
                `card_url_tipo`,
                `card_url_js`,
                ordenacao,
                sentido,
                `card_notification_bg`,
                `card_notification`,
                `card_color`,
                `card_border_color`,
                `card_bg_class`,
                `card_title`,
                `card_title_sub`,
                `card_value`,
                `card_atraso_value`,
                `card_icon`,
                `card_row`,
                `card_title_modal`,
                `card_url_modal`,
                `status`,
                `card_ordem`,
                `panel_ordem`,
                `grupo_ordem`,
                `grupo_rotulo`,
                `iddashgrupo`,
                `idempresa`,
                `criadopor`,
                `criadoem`,
                `alteradopor`,
                `alteradoem`)
                SELECT '".addslashes($row['iddashcard'])."' as iddashboard, 
                'Y', 
                '".addslashes($row['dashboard'])."',
                '".addslashes($row['rotulo'])."',
                '".addslashes($row['iddashpanel'])."' as iddashpanel,
                '".addslashes($row['panelclasscol'])."' as panel_class_col,
                '".addslashes($row['paneltitle'])."' as panel_title,
                '".addslashes($row['code'])."' as code,
                '".addslashes($row['tab'])."' as tab,
                '".addslashes($row['modulo'])."' as modulo,
                '".addslashes($row['col'])."' as col,
                '".addslashes($row['iddashcard'])."' as iddashcard,
                '".addslashes($row['cardclasscol'])."' as card_class_col,
                '".addslashes($row['cardurltipo'])."' as card_url_tipo,
                '".($row['cardurljs'])."' as card_url_js,
                '".addslashes($row['cardordenacao'])."' as ordenacao,
                '".addslashes($row['cardsentido'])."' as sentido,
                '".addslashes($row['cardnotificationbg'])."' as card_notification_bg,
                '".addslashes($row['cardnotification'])."' as card_notification,
                ".($row['cardcolor'])." as card_color,
                ".($row['cardbordercolor'])." as card_border_color,
                '".addslashes($row['cardbgclass'])."' as card_bg_class,
                '".addslashes($row['cardtitle'])."' as card_title,
                '".addslashes($row['cardtitlesub'])."' as card_title_sub,
                '0',
                '0',
                '".addslashes($row['cardicon'])."' as card_icon,
                '".addslashes($row['cardrow'])."' as card_row,
                '".addslashes($row['cardtitlemodal'])."' as card_title_modal,
                '".addslashes($row['cardurlmodal'])."' as card_url_modal,
                '".($row['idempresa'] > 1 ? 'ATIVO' : 'ATIVO')."',
                ".($row['card_ordem'] * 1)." as card_ordem,
                ".($row['panel_ordem'] * 1)." as panel_ordem,
                ".($row['grupo_ordem'] * 1)." as grupo_ordem,
                '".addslashes($row['grupo_rotulo'])."' as grupo_rotulo,
                ".($row['iddashgrupo'])." as iddashgrupo,
                ".($row['idempresa']).", 'laudo', now(), 'laudo', now()
                ON DUPLICATE KEY UPDATE `cron` = 'Y', 
                `dashboard` ='".addslashes($row['dashboard'])."',
                `dashboard_title` = '".addslashes($row['rotulo'])."',
                `panel_id` = '".addslashes($row['iddashpanel'])."',
                `panel_class_col` = '".addslashes($row['panelclasscol'])."',
                `panel_title` = '".addslashes($row['paneltitle'])."',
                `code` ='".addslashes($row['code'])."',
                `tab` = '".addslashes($row['tab'])."',
                `modulo` = '".addslashes($row['modulo'])."',
                `col` = '".addslashes($row['col'])."',
                `card_id` = '".addslashes($row['iddashcard'])."',
                `card_class_col` = '".addslashes($row['cardclasscol'])."',
                `card_url` = '',
                `card_url_tipo` = '".addslashes($row['cardurltipo'])."',
                `card_url_js` = '".($row['cardurljs'])."',
                `ordenacao` = '".addslashes($row['cardordenacao'])."',
                `sentido` = '".addslashes($row['cardsentido'])."',
                `card_notification_bg` = '".addslashes($row['cardnotificationbg'])."',
                `card_notification` = '".addslashes($row['cardnotification'])."',
                `card_bg_class` = '".addslashes($row['cardbgclass'])."',
                `card_title` = '".addslashes($row['cardtitle'])."',
                `card_title_sub` = '".addslashes($row['cardtitlesub'])."',
                `card_value` = '0',
                `card_atraso_value` = '0',
                `card_atraso_url` = null,
                `card_icon` = '".addslashes($row['cardicon'])."',
                `card_row` = '".addslashes($row['cardrow'])."',
                `card_title_modal` = '".addslashes($row['cardtitlemodal'])."',
                `card_url_modal` = '".addslashes($row['cardurlmodal'])."', 
                `status` = '".($row['idempresa'] > 1 ? 'ATIVO' : 'ATIVO')."',
                `card_ordem` = ".($row['card_ordem'] * 1).", 
                `panel_ordem` = ".($row['panel_ordem'] * 1).",
                `grupo_ordem` = ".($row['grupo_ordem'] * 1).",
                `grupo_rotulo` = '".addslashes($row['grupo_rotulo'])."',
                `iddashgrupo` = ".($row['iddashgrupo']).",
                `idempresa` = ".($row['idempresa']).",
                `criadopor` = 'laudo', 
                `criadoem` = now(), 
                `alteradopor` = 'laudo', 
                `alteradoem` = now();";

    if($_inspecionar_sql){
        echo ('<strong>insert tipoobjeto = fluxostatus/etapa (Loop consulta Principal i='.$i.')</strong><br><pre>'.$sqlx.'</pre><hr>');
    }

    $resX = d::b()->query($sqlx);
    if(!$resX){
        echo "Falha na INSERT DASHBOARD: ".mysqli_error(d::b())."<p>SQL: $sqlx";
        $sqlLog = "INSERT INTO log (idempresa, tipoobjeto, idobjeto, tipolog, log, info, status, criadoem, data) 
                                VALUES ('".$row['idempresa']."', 'cron', 'testedash', '".$row['iddashcard']."', 'Atualização INSERT DASHBOARD ".addslashes(mysqli_error(d::b()))."</p>', '<p>SQL:".addslashes($sqlx)."', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqlLog) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlLog);
    }

    echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>';
    $datetime2 = new DateTime();
    $interval = $datetime1->diff($datetime2);

    echo "$i. Execução: ".$interval->format('%H:%I:%S')."<hr>";
    $updcard = "UPDATE dashcard set executadoem = now() where iddashcard = ".$row['iddashcard'];
    d::b()->query($updcard) or die("Atualizar card executadoem.: ".mysqli_error(d::b())."<p>SQL: $updcard");
}


function insereAtaulizaIndicadoresFluxoEtapa($tipoobjeto, $cardurl, $tab, $cardcolor, $col, $_inspecionar_sql, $colprazod)
{
    echo "Início: ".date("d/m/Y H:i:s", time()).'<br>';
    $datetime1 = new DateTime();

    if($tab == 'vwlote'){
        $tab = 'vwlote_dash';
    }

    if($tipoobjeto == 'fluxostatus'){

        if($tab == 'vwevento'){

            $sql_atraso = "SUM(
                IFNULL(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.prazoamd,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), 1, NULL),IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.datafim,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), 1, 0))
                ) AS card_atraso_value,          
			GROUP_CONCAT(
            IFNULL(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.prazoamd,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), e.".$col.", null),IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.datafim,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), e.".$col.", null))
            ) AS card_atraso_url
			";


            //se for formalização - Pegar o prazoD dos processos , se no processo estiver vazio , pega o prazod do fluxo
        } else if($tab == 'vwformalizacao'){

            if($colprazod != ''){

                $sql_atraso = "COUNT( DISTINCT IF(LENGTH(pp.prazod) != 0,
				IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.".$colprazod.",
								INTERVAL pp.prazod +1 DAY),
							'%Y-%m-%d'),
					e.idformalizacao,
					NULL),
					IF(LENGTH(fs.prazod) != 0,
					IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.".$colprazod.",
									INTERVAL fs.prazod +1 DAY),
								'%Y-%m-%d'),
						e.idformalizacao,
						NULL),
					NULL))) AS card_atraso_value,

				GROUP_CONCAT(distinct (IF(LENGTH(pp.prazod) != 0,
					IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.".$colprazod.",
										INTERVAL pp.prazod +1 DAY),
									'%Y-%m-%d'),
									e.idformalizacao,
							null),
							IF(LENGTH(fs.prazod) != 0,
							IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.".$colprazod.",
												INTERVAL fs.prazod +1 DAY),
											'%Y-%m-%d'),
											e.idformalizacao,
									null),
								null)))) AS card_atraso_url";

                $getPrazoDprprocprativ = 'left join prprocprativ pp on (pp.idfluxostatus = fs.idfluxostatus and e.idprproc = pp.idprproc)';
            } else {

                $sql_atraso = "SUM(IF(LENGTH(fs.prazod) != 0,
				IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.criadoem,
								INTERVAL fs.prazod +1  DAY),
							'%Y-%m-%d'),
					1,
					0),
				0)) AS card_atraso_value,
				  GROUP_CONCAT((IF(LENGTH(fs.prazod) != 0,
			   IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.criadoem,
								INTERVAL fs.prazod + 1 DAY),
							'%Y-%m-%d'),
							e.".$col.",
					null),
				null))) AS card_atraso_url";
            }
        } else {

            if($colprazod != ''){

                $sql_atraso = "SUM(IF(LENGTH(fs.prazod) != 0,
				IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.".$colprazod.",
								INTERVAL fs.prazod +1 DAY),
							'%Y-%m-%d'),
					1,
					0),
				0)) AS card_atraso_value,
				  GROUP_CONCAT((IF(LENGTH(fs.prazod) != 0,
			   IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.".$colprazod.",
								INTERVAL fs.prazod +1 DAY),
							'%Y-%m-%d'),
							e.".$col.",
					null),
				null))) AS card_atraso_url";
            } else {

                $sql_atraso = "SUM(IF(LENGTH(fs.prazod) != 0,
				IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.criadoem,
								INTERVAL fs.prazod +1  DAY),
							'%Y-%m-%d'),
					1,
					0),
				0)) AS card_atraso_value,
				  GROUP_CONCAT((IF(LENGTH(fs.prazod) != 0,
			   IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.criadoem,
								INTERVAL fs.prazod + 1 DAY),
							'%Y-%m-%d'),
							e.".$col.",
					null),
				null))) AS card_atraso_url";
            }
        }

        $sqli = "SELECT  
		c.iddashcard as iddashcard, 
		(e.idempresa) as idempresa,
		GROUP_CONCAT(distinct e.".$col.") as card_url,		
		COUNT(distinct e.".$col.") as card_value,
		c.cardcolor as card_color,
		c.cardbordercolor as card_border_color,
		c.modulo,
		".$sql_atraso."
		from ".addslashes($tab)." e
		join dashcard c on c.tipoobjeto = '".$tipoobjeto."' and c.objeto = e.idfluxostatus
		JOIN fluxostatus fs ON fs.idfluxostatus = e.idfluxostatus
		join fluxo f on (f.idfluxo = fs.idfluxo and f.colprazod = '".$colprazod."')
		".$getPrazoDprprocprativ."
		where  c.status = 'ATIVO'  
		group by 
		e.idempresa,
		e.idfluxostatus;";
    } else {

        if($tab == 'vwevento'){

            $sql_atraso = "SUM(
                IFNULL(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.prazoamd,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), 1, NULL),IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.datafim,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), 1, 0))
                ) AS card_atraso_value,          
			GROUP_CONCAT(
            IFNULL(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.prazoamd,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), e.".$col.", null),IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(e.datafim,'%Y-%m-%d') and statustipo not in ('CONCLUIDO','CANCELADO','VALIDADO'), e.".$col.", null))
            ) AS card_atraso_url
			";
        } else {

            $sql_atraso = "SUM(IF(fs.prazod > 0,
			IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.criadoem,
							INTERVAL fs.prazod +1 DAY),
						'%Y-%m-%d'),
				1,
				0),
			0)) AS card_atraso_value,

			GROUP_CONCAT((IF(fs.prazod > 0,
			IF(DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') >= DATE_FORMAT(DATE_ADD(e.criadoem,
							INTERVAL fs.prazod + 1 DAY),
						'%Y-%m-%d'),
						e.".$col.",
				null),
			null))) AS card_atraso_url
			";
        }


        $sqli = "SELECT  
		c.iddashcard as iddashcard, 
		e.idempresa as idempresa,
		GROUP_CONCAT(distinct e.".$col.") as card_url,
		COUNT(distinct e.".$col.") as card_value,
		c.cardcolor as card_color,
		c.cardbordercolor as card_border_color,
		c.modulo,
		".$sql_atraso."

		from ".addslashes($tab)." e
		join fluxostatus fs on fs.idfluxostatus = e.idfluxostatus
		join dashcard c on c.tipoobjeto = '".$tipoobjeto."' and c.objeto = fs.idetapa
		join fluxo f on (f.idfluxo = fs.idfluxo and f.colprazod = '".$colprazod."')

		where  c.status = 'ATIVO'  
		group by 
		e.idempresa,
		fs.idetapa
		;";
    }



    if($_inspecionar_sql){
        echo ('<strong>Consulta atualizaIndicadores - Tabela: '.$tab.' - ColprazoD: '.$colprazod.'</strong><br><pre>'.$sqli.'</pre><hr>');
    }

    $resi = d::b()->query($sqli) or die("A inserção 1 dashboard falhou : ".mysqli_error(d::b())."<p>SQL: $sqli");

    echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>';
    $datetime2 = new DateTime();
    $interval = $datetime1->diff($datetime2);

    echo "Execução: ".$interval->format('%H:%I:%S');


    while ($rowi = mysqli_fetch_assoc($resi)){

        $sqlins = "INSERT INTO `laudo`.`dashboard` (iddashcard, idempresa, card_url, card_value, card_atraso_url, card_atraso_value)
		values ('".$rowi['iddashcard']."', '".$rowi['idempresa']."', '_modulo=".$rowi['modulo']."&".$col."=[".$rowi['card_url']."]', ".$rowi['card_value'].",
		'_modulo=".$rowi['modulo']."&".$col."=[".$rowi['card_atraso_url']."]', ".$rowi['card_atraso_value'].") 
		ON DUPLICATE KEY UPDATE card_url =  '_modulo=".$rowi['modulo']."&".$col."=[".$rowi['card_url']."]', card_value =  ".$rowi['card_value'].", alteradoem=NOW(), 
		card_atraso_url =  '_modulo=".$rowi['modulo']."&".$col."=[".$rowi['card_atraso_url']."]', card_atraso_value =  ".$rowi['card_atraso_value']."
		;";

        if($_inspecionar_sql){
            echo ('<strong>Insert Loop atualizaIndicadores</strong><br><pre>'.$sqlins.'</pre><hr>');
        }

        d::b()->query($sqlins) or die("A inserção 2 dashboard falhou : ".mysqli_error(d::b())."<p>SQL: $sqlins");
    }
}



function verificaSeConsultaDoIndicadorEstaNaHoraDeRodar($row){

    if(!empty($row['execucao'])){
        $sqltime = 'SELECT (TIMESTAMPDIFF(minute,DATE_ADD("'.$row['executadoem'].'", interval '.$row['execucao'].'),now())) as dif ';
        $restime = d::b()->query($sqltime) or die("Falha ao verificar tempo de execução do indicador ".mysqli_error(d::b())."<p>SQL: $sqltime");
        $rt = mysqli_fetch_assoc($restime);

        if($rt['dif'] >= 0 or $rt['dif'] == null){
            $rodarConsultadaCron = true;
        } else {
            $rodarConsultadaCron = false;
        }
    } else {
        $rodarConsultadaCron = true;
    }

    return $rodarConsultadaCron;
}
?>
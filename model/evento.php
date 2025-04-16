<?
require_once(__DIR__."/../inc/php/functions.php");
require_once(__DIR__."/../inc/php/laudo.php");
require_once(__DIR__."/../api/notifitem/notif.php");

class EVENTO 
{  
    //Busca Módulo Vinculado - Nativo
	function RetornaChaveModuloAlerta($inModulo, $inbypass = false)
    {
        if (empty($inModulo)) {
            die("retArrModuloConf: Parâmetro inModulo não informado");
        }

        //Permite reaproveitamento sem verificação de segurança. Ex: Tela de _modulo necessita recuperar informaçàµes do módulo mesmo que não estejam devidamente atribuà­das em alguma LP
        if ($inbypass !== true) {
            $joinLp = ($_SESSION["SESSAO"]["LOGADO"]) ? "left join " . _DBCARBON . "._lpmodulo l on (l.modulo=m.modulo and l.idlp='" . $_SESSION["SESSAO"]["IDLP"] . "')" : "";
            $whereMod = ($_SESSION["SESSAO"]["LOGADO"]) ? "and m.modulo in (" . getModsUsr("SQLWHEREMOD") . ")" : "";
            $ifrestaurar = (getModsUsr("SQLWHEREMOD")) ? ",IF(1=(select ('restaurar' in  (" . getModsUsr("SQLWHEREMOD") . "))),'Y','N') as oprestaurar" : "";
        }

        $smod = "SELECT
                    CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts
                FROM
                    " . _DBCARBON . "._modulo m
                    left join " . _DBCARBON . "._modulo mv on (mv.modulo=m.modvinculado)
                    left join " . _DBCARBON . "._modulo mpar on (mpar.modulo=m.modulopar)
                    " . $joinLp . "
                WHERE m.modulo = '" . $inModulo . "'
                    " . $whereMod;
        $rmod = d::b()->query($smod);

        if (!$rmod) {
            die("[model-evento] - : Erro ao recuperar Módulo " . mysqli_error(d::b()));
        }

        $rows = mysqli_fetch_assoc($rmod);
        return ($rows['chavefts']);
    }

    //Busca os eventos pendentes para listar no Alerta - Nativo
    function getListaEvento($ocultosFilter, $filterEventoTipo, $ord, $filtroMiniEvento = NULL, $tarefasFilter = NULL, $asyncLoad = NULL) 
    {
        //Alterado para pegar o nome do cliente (Representante) que não possui nomecurto - Lidiane (08/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=325306
		//Acrescentado o campo dataslaprazo para validar se o prazo é maior que a data de hoje para sla no JavaScript. (LTM - 20-07-2020 - 332052)
        if(!empty($ord)){
            $order = 'ORDER BY '.$ord;
        }

        $sql = "SELECT IFNULL(p.nomecurto,p.nome) AS nomecurto,
				   e.modulo,
				   e.idmodulo,
				   e.idevento,
				   e.idpessoa,
				   e.evento,
				   es.rotulo AS STATUS, 
                   DATE_FORMAT(e.inicio, '%d/%m/%Y') AS iniciodata,
				   e.inicio,
				   e.iniciohms,
				   e.fim,
				   e.fimhms,
				   e.criadoem,
				   e.prazo,
				   e.modulo,
				   e.idmodulo,
				   et.eventotipo,
				   et.cor,
				   et.sla,
				   er.visualizado,
				   e.descricao,
				   et.ideventotipo, 
                   es.statustipo AS posicaofim,
                   CONCAT(DATE_FORMAT(e.criadoem, '%m/%d/%Y %H:%i'),' - ',if(et.anonimo = 'Y','<b><i>ANÔNIMO</i></b>',p.nomecurto)) as criadoempor,
                   CONCAT(DATE_FORMAT(e.criadoem, '%m/%d/%Y %H:%i'),' - ',p.nomecurto) AS criadoempor, CONCAT(DATE_FORMAT(e.criadoem, '%m/%d/%Y %H:%i'),' - ',p.nomecurto) AS alteradoempor,
				   es.cor AS corstatus,
				   es.cortexto AS cortextostatus,
				   es.rotulo,
				   es.cor AS corstatusresp,
				   es.rotuloresp AS rotuloresp, 
                   ROUND(GREATEST(100-(fn_worktime(e.criadoem, if (NOW()> CONCAT(e.prazo,' 17:00:00'), CONCAT(e.prazo,' 17:00:00'), NOW()))*100)/ fn_worktime(e.criadoem, CONCAT(e.prazo,' 17:00:00')), 0),0) AS slaprazo,
				   es.statustipo AS posicao,
				   et.prazo AS configprazo,
				   if(et.prazo = 'Y', 'show', 'hide') AS mostraprazo,
				   if(et.prazo = 'Y', 'hide', 'show') AS mostradata,
				   et.travasala,
				   e.diainteiro,
				   e.duracaohms,
				   e.idequipamento,
				   er.alteradoem, 
                   DATE_ADD(er.alteradoem, INTERVAL HOUR(esla.sla) HOUR) AS dataslaprazo, 
				   TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem) 
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento), 
                        NOW())))), '%H:%i:%s')),
                    '%H:%i') AS datasla,
				   ROUND(((TIME_TO_SEC(TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento),
                        NOW())) * 60)), '%H:%i:%s')),
                    '%H:%i:%s')) * 100) / TIME_TO_SEC(esla.sla)), 0) AS percentual,	
				   CONCAT(TIMESTAMPDIFF(day,now(),if(et.prazo = 'N', CONCAT(e.fim, ' ',e.fimhms), CONCAT(e.prazo, ' 17:00:00'))) , 'd ',
                            MOD( TIMESTAMPDIFF(hour,now(),if(et.prazo = 'N', CONCAT(e.fim, ' ',e.fimhms), CONCAT(e.prazo, ' 17:00:00'))), 24), 'h ',
                            MOD( TIMESTAMPDIFF(minute,now(),if(et.prazo = 'N', CONCAT(e.fim, ' ',e.fimhms), CONCAT(e.prazo, ' 17:00:00'))), 60), 'm ') as prazorestante,
				   TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento),
                        NOW())) * 60)), '%H:%i:%s')),
                    '%H:%i:%s') AS temporeal,
				   TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem) 
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento), 
                        NOW())) * 60)), '%H:%i:%s')),
                    '%H:%i') AS slaprazo,
                   er.oculto,
				   et.anonimo
			  FROM evento e JOIN pessoa p on p.idpessoa = e.idpessoa
              JOIN eventotipo et on et.ideventotipo = e.ideventotipo
              JOIN fluxostatuspessoa er on er.idmodulo = e.idevento AND er.modulo = 'evento'
           
         LEFT JOIN fluxostatus ets on ets.idfluxostatus = e.idfluxostatus
         LEFT JOIN "._DBCARBON."._status es on es.idstatus = ets.idstatus
         LEFT JOIN eventosla esla ON esla.ideventotipo = e.ideventotipo AND e.prioridade = esla.prioridade AND e.servico = esla.servico
              ".$filtroMiniEvento."
             WHERE et.dashboard = 'Y'
               AND er.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
               AND er.tipoobjeto = 'pessoa'
               AND IF (e.ideventopai, e.inicio <= date_format(now(), '%Y-%m-%d'), 1) = 1
               AND repetirate is null
               ".$tarefasFilter."
               ".$ocultosFilter."
               ".$filterEventoTipo."
               ".$order."
               ".$asyncLoad.";";
        $res = d::b()->query($sql) or die("[model-evento] - Erro em getListaEvento: " . mysqli_error(d::b())."\n".$sql);
        return $res;
    }

    //Retorna a Quantidade de Eventos - LTM (22/06/2020)
    function getListaEventoQtde($ocultosFilter, $filterEventoTipo, $ord, $filtroMiniEvento, $tarefasFilter, $asyncLoad)
    {
        return mysql_num_rows($this->getListaEvento($ocultosFilter, $filterEventoTipo, $ord, $filtroMiniEvento, $tarefasFilter, $asyncLoad));
    }

    function getQuantidadeEvento()
    {
        $sqlcount = "SELECT count(e.idevento)
                       FROM evento e, pessoa p, eventotipo t
                      WHERE p.idpessoa = e.idpessoa and t.ideventotipo = e.ideventotipo
                        AND t.dashboard = 'Y'
                        AND e.idevento IN (
                            SELECT er.idmodulo
                              FROM fluxostatuspessoa er
                             WHERE er.idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
                               AND er.tipoobjeto = 'pessoa'
                               AND er.idmodulo = e.idevento
                               AND er.modulo = 'evento'
                               AND IF (e.ideventopai, e.inicio <= date_format(now(), '%Y-%m-%d'), 1) = 1
                               AND repetirate is null
                               AND er.oculto != 1)";

        $count = d::b()->query($sqlcount) or die("[model-evento] - getQuantidadeEvento: " . mysqli_error(d::b()));
        $totalResultados = (int) mysqli_fetch_assoc($count)["count(e.idevento)"];
        return $totalResultados;
    }

    function getBotoes($idevento, $tipo = NULL)
    {
        if($tipo == 'botao_menu_lateral'){
            $campo = ", etsf.ocultar";
            $where = "r.idmodulo = ".$idevento." AND r.tipoobjeto ='pessoa' ";
        } else {
            $where = "r.idmodulo = ".$idevento." 
                    AND r.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]." 
                    AND r.tipoobjeto ='pessoa'";
        }

        //CARREGAR OS BOTÕES E DEPOIS ENVIÁ-LOS PARA DENTRO DO MENU LATERAL RÁPIDO.
        $sqlb = "SELECT et.ocultar,
                        r.idfluxostatuspessoa, 
                        e.idfluxostatus AS idstatus, 
                        ese.cor AS corstatus, 
                        ese.cortexto AS cortextostatus, 
                        ese.rotulo,
                        esr.idstatus AS idstatusresp, 
                        esr.cor AS corstatusresp, 
                        esr.rotuloresp AS rotuloresp,
                        es.botao, 
                        es.cor, 
                        es.cortexto, 
                        es.idstatus AS idstatusf,
                        e.idevento,
                        et.botaocriador AS botaocriador,
                        et.botaoparticipante AS botaoparticipante,
                        e.criadopor
                        ".$campo."
                   FROM evento e
                    JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                    JOIN fluxostatus ete ON(ete.idfluxostatus = e.idfluxostatus AND ete.idfluxo = ms.idfluxo)
                    JOIN "._DBCARBON."._status ese ON ete.idstatus = ese.idstatus
                    JOIN fluxostatuspessoa r ON e.idevento = r.idmodulo AND r.modulo = 'evento'
                    JOIN "._DBCARBON."._status esr ON ete.idstatus = esr.idstatus
                    JOIN fluxostatus et ON(et.idstatus = ete.idstatus AND et.idfluxo = ms.idfluxo)
                LEFT JOIN "._DBCARBON."._status es ON FIND_IN_SET(es.idstatus, et.fluxo) 
                LEFT JOIN fluxostatus etsf ON(etsf.idstatus = ete.idstatus AND etsf.idfluxo = ms.idfluxo)
            AND NOT EXISTS (SELECT 1 FROM "._DBCARBON."._status ese2
                            WHERE ese2.idstatus = es.idstatus AND FIND_IN_SET(ese2.idstatus, ete.fluxoocultar))
                    WHERE ".$where."
                ORDER BY es.rotuloresp;";
        $resb = d::b()->query($sqlb) or die("[model-evento] - Erro ao buscar status disponiveis no fluxo: " . mysqli_error(d::b())."\n".$sqlb);
        return $resb;
    }

    function getEventoVariaveis()
    {
        /*
         * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
         */
		//Acrescentado o campo dataslaprazo para validar se o prazo é maior que a data de hoje para sla no JavaScript. (LTM - 20-07-2020 - 332052)
        $pagsql = "SELECT e.*, 
                          es.rotulo AS status,
                          DATE_FORMAT(e.inicio, '%d/%m/%Y') AS iniciodata,
                          et.cor,
                          esr.statustipo AS posicao,
                          et.sla,
                          et.prazo AS configprazo,
                          es.rotulo, 
                          ets.ordem, 
                          ets.fluxoocultar,
                          IF(et.prazo = 'Y', 'show', 'hide') AS mostraprazo,
                          IF(et.prazo = 'Y', 'hide', 'show') AS mostradata,
                          DATE_ADD(e.alteradoem, INTERVAL HOUR(es.sla) HOUR) AS dataslaprazo,
                          ROUND(GREATEST(100 - (FN_WORKTIME(e.criadoem,
                            IF(NOW() > CONCAT(e.prazo, ' 17:00:00'),
                                CONCAT(e.prazo, ' 17:00:00'),
                                NOW())) * 100) / FN_WORKTIME(e.criadoem, CONCAT(e.prazo, ' 17:00:00')), 0),
                            0) AS slaprazo,
                          CONCAT(TIMESTAMPDIFF(DAY, NOW(), IF(et.prazo = 'N', 
                                                       CONCAT(e.fim, ' ', e.fimhms),
                                                       CONCAT(e.prazo, ' 17:00:00'))), 'd ',
                                                          MOD(TIMESTAMPDIFF(HOUR, NOW(), 
                                                           IF(et.prazo = 'N', 
                                                       CONCAT(e.fim, ' ', e.fimhms),
                                                       CONCAT(e.prazo, ' 17:00:00'))), 24), 'h ',
                                                          MOD(TIMESTAMPDIFF(MINUTE, NOW(),
                                                           IF(et.prazo = 'N',
                                                       CONCAT(e.fim, ' ', e.fimhms),
                                                       CONCAT(e.prazo, ' 17:00:00'))), 60),
                                'm ') AS prazorestante,
                          TIME_FORMAT(TIMEDIFF(es.sla,
                                TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                                        IF(((SELECT MIN(r1.alteradoem)
                                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                                JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
                                                JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                               WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                            (SELECT MIN(r1.alteradoem)
                                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                                JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
                                                JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                                JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                                WHERE r1.idmodulo = e.idevento), NOW())) * 60)), '%H:%i:%s')), '%H:%i') AS datasla,
                          ROUND(((TIME_TO_SEC(TIME_FORMAT(TIMEDIFF(es.sla,
                          TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                                    IF(((SELECT MIN(r1.alteradoem)
                                            FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                            JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
											JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                            JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                            JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                        WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                        (SELECT MIN(r1.alteradoem)
                                            FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
											JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
											JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                            JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                            JOIN "._DBCARBON."._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                            WHERE r1.idmodulo = e.idevento), NOW())) * 60)), '%H:%i:%s')), '%H:%i:%s')) * 100) / TIME_TO_SEC(es.sla)), 0) AS percentual,
                          et.privado
                     FROM evento e JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
                     JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                LEFT JOIN fluxostatus ets ON ets.idfluxo = ms.idfluxo AND ets.idfluxostatus = e.idfluxostatus
                LEFT JOIN fluxostatuspessoa er ON er.idmodulo = e.idevento AND er.modulo = 'evento' AND er.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"] ." AND er.tipoobjeto = 'pessoa' 
                      AND ets.idfluxostatus = er.idfluxostatus
                LEFT JOIN eventosla es ON (((es.ideventotipo = e.ideventotipo) AND (e.prioridade = es.prioridade) AND (e.servico = es.servico)))
                LEFT JOIN "._DBCARBON."._status es ON es.idstatus = ets.idstatus
                LEFT JOIN "._DBCARBON."._status esr ON esr.idstatus = ets.idstatus
                    WHERE e.idevento = '#pkid'"; 
        return $pagsql;
    }

    //Retorna o Token Inicial do Evento - Nativo
    function getTokenInicial($idevento)
    {
        $sql = "SELECT f.idfluxostatus
                  FROM evento e JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                  JOIN fluxostatus f ON f.idfluxo = ms.idfluxo 
                  JOIN carbonnovo._status s ON s.idstatus = f.idstatus AND s.statustipo = 'INICIO'
                 WHERE e.idevento = '".$idevento."'";
        $res = d::b()->query($sql);
        $r = mysqli_fetch_assoc($res);
        return $r['idfluxostatus'];
    }

    //Retorna o Status Inicial do Usuário no Evento - Nativo
    function getStatusIncialUsuario($_1_u_evento_ideventotipo, $_1_u_evento_idevento)
    {
        $sqk="SELECT s.statustipo AS posicao,
                     ordem,
                     r.idfluxostatuspessoa,
                     er.fluxo,
                     er.idstatus,
                     er.novamensagem,
                     er.idfluxostatus
                FROM eventotipo et JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO' -- AND ms.idobjeto = ".$_1_u_evento_ideventotipo." 
                JOIN fluxostatus er ON er.idfluxo = ms.idfluxo 
                JOIN fluxostatuspessoa r ON r.idfluxostatus = er.idfluxostatus
                LEFT JOIN "._DBCARBON."._status s ON s.idstatus = er.idstatus
               WHERE r.idmodulo = ".$_1_u_evento_idevento." AND r.modulo = 'evento'
               AND r.tipoobjeto='pessoa'
               AND r.idobjeto=".$_SESSION["SESSAO"]["IDPESSOA"];

        $res = d::b()->query($sqk) or die("[model-evento] - Erro ao buscar status inicial do usuario: ". mysqli_error(d::b())."\n".$sqk);        
        return $res;
    }

    //Retorna o Próximo Status após a Leitura - Nativo
    function getStatusLeitura($_1_u_evento_ideventotipo, $ordem)
    {
        $sqk1="SELECT mf.idfluxostatus, mf.ordem
                 FROM fluxo ms JOIN fluxostatus mf ON mf.idfluxo = ms.idfluxo
                 JOIN "._DBCARBON."._status s on s.idstatus = mf.idstatus
                WHERE ms.idobjeto = ".$_1_u_evento_ideventotipo." AND tipoobjeto = 'ideventotipo'
                 AND (s.statustipo  !=  'INICIO' or s.statustipo is null) 
                 AND ordem > ".$ordem." 
            ORDER BY ordem limit 1";
        $res = d::b()->query($sqk1) or die("[model-evento] - Erro ao buscar proximo status apos a leitura: ". mysqli_error(d::b())."\n".$sqk1);
        return $res;
    }

    //Retorna as pessoas para Inserir no Evento - Nativo
    function getPessoas($intipopessoa) 
    {
        global $JSON;
        $sql = "SELECT
                    p.idpessoa,
                    p.nome,
                    p.idtipopessoa
                FROM pessoa p
                WHERE 1
                    ".getidempresa('p.idempresa','evento')."  
             
                    and p.idtipopessoa in (".$intipopessoa.")
                AND p.status ='ATIVO'                
                ORDER BY p.nome";
        $rts = d::b()->query($sql) or die("[model-evento] - Erro ao trazer pessoas getPessoas model/evento: " . mysqli_error(d::b())."\n".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idpessoa"];         
            $arrtmp[$i]["label"] = $r["nome"];
            $i++;
        }
    
        return $JSON->encode($arrtmp);
    }

    //Retorna uma Lista de Setores para inserir no Evento - Nativo
    function getJSetorvinc() 
    {
        global $JSON, $_1_u_evento_idevento;
        $sql = "SELECT idimgrupo, grupo
                  FROM imgrupo g
                 WHERE 1 ".getidempresa('idempresa','evento')."
                   AND status='ATIVO'
                   AND NOT EXISTS(
                       SELECT 1
                         FROM fluxostatuspessoa r
                        WHERE r.idmodulo= '".$_1_u_evento_idevento."' AND r.modulo = 'evento'
                          AND r.tipoobjeto ='imgrupo'
                          AND g.idimgrupo = r.idobjeto)
                ORDER BY grupo ASC";
        $rts = d::b()->query($sql) or die("[model-evento] - getJSetorvinc: ". mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idimgrupo"];
            $arrtmp[$i]["label"] = $r["grupo"];
            $i++;
        }

        return $JSON->encode($arrtmp);
    }

    //Retorna os Campos Visíveis - Nativo
    function getCamposVisiveis($inideventotipoadd)
    {
        $sql = "SELECT distinct(t.col) as col,t.rotulo,t.prompt,t.code,c.datatype,t.ord 
                  FROM eventotipocampos t 
                  JOIN "._DBCARBON."._mtotabcol c ON (c.col=t.col and c.tab= 'eventoobj')
                 WHERE t.ideventotipoadd='".$inideventotipoadd."'
                   AND t.visivel='Y' 
                   AND ord is not null
                   AND rotulo is not null 
              ORDER BY t.ord,t.rotulo";
        $rts = d::b()->query($sql) or die("[model-evento] - getCamposVisiveis: ". mysqli_error(d::b()));

        $arrtmp = array();        

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$r["ord"]]["col"] = $r["col"];
            $arrtmp[$r["ord"]]["rotulo"] = $r["rotulo"];  
            $arrtmp[$r["ord"]]["prompt"] = $r["prompt"];
            $arrtmp[$r["ord"]]["code"] = $r["code"];
            $arrtmp[$r["ord"]]["datatype"] = $r["datatype"];
        }

        return $arrtmp;
    }

    function getCamposVisiveisC($inideventotipo)
    {
        $sql = "SELECT distinct(t.col) as col,t.rotulo,t.prompt,t.code,c.datatype,t.ord,c.dropsql,t.obrigatorio
                  FROM eventotipocampos t JOIN "._DBCARBON."._mtotabcol c ON (c.col=t.col and c.tab='evento')
                 WHERE t.ideventotipo=".$inideventotipo."
                   AND (t.visivel='Y' or t.col in('inicio','prazo'))
                   AND t.ord is not null
                   AND c.rotpsq is not null 
              ORDER BY t.ord,t.rotulo";
        $rts = d::b()->query($sql) or die("[model-evento] - getCamposVisiveisC: ". mysqli_error(d::b())."\n".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["col"] = $r["col"];
            $arrtmp[$i]["rotulo"] = $r["rotulo"];  
            $arrtmp[$i]["prompt"] = $r["prompt"];
            $arrtmp[$i]["code"] = $r["code"];
            $arrtmp[$i]["datatype"] = $r["datatype"];
            $arrtmp[$i]["dropsql"] = $r["dropsql"];
            $arrtmp[$i]["obrigatorio"] = $r["obrigatorio"];
            $i++;
        }

        return $arrtmp;
    }

    function jsonMotivo() 
    {
        $sql = "SELECT idsgdoctipodocumento, tipodocumento
                  FROM sgdoctipodocumento
                 WHERE status='ativo'
                   AND idsgdoctipo='rnc'
              ORDER BY tipodocumento";
        $res = d::b()->query($sql) or die("[model-evento] - jsonMotivo: Erro: ".mysqli_error(d::b())."\n".$sql);

        $arrret = array();
        $i = 0;
        
        while ($r = mysqli_fetch_assoc($res)) {

            $arrtmp[$i]["value"]    =   $r["idsgdoctipodocumento"];
            $arrtmp[$i]["label"]    =   ($r["tipodocumento"]);
            $i++;	
        }
        
        $json = new Services_JSON();
        return $json->encode($arrtmp);
    }

    //Retorna Status do Evento - Nativo
    function getSatusUsario($_1_u_evento_idmodulo)
    {
        $sqlobj = "SELECT eo.idevento
					 FROM eventoobj eo 
					 JOIN fluxostatuspessoa r ON r.idmodulo = eo.idevento AND r.modulo = 'evento'
                      AND r.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"] ." and r.tipoobjeto = 'pessoa'
                    WHERE eo.ideventoobj = '".$_1_u_evento_idmodulo."';";
        $res = d::b()->query($sqlobj) or die("[model-evento] - Erro ao buscar status do usuario no evento: ".mysqli_error(d::b()));
        return $res;
    }

    //Retorna a Permissao do Usuário - Nativo
    function getPermissao($idevento)
    {
        $sqspPermissao = "SELECT * FROM fluxostatuspessoa 
                           WHERE idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"] ." 
                             AND idmodulo = '".$idevento."' AND modulo = 'evento'";
        $res = d::b()->query($sqspPermissao) or die("[model-evento] - Erro ao configuracao de Permissão : " . mysql_error() . "<p>SQL:".$sqspPermissao);
        return $res;
    }

    function getListaSla($ideventotipo, $status = NULL)
    {
        if(!empty($status))
        {
            $where = " AND status='".$status."'";
        }
        $sqsp="SELECT * FROM eventosla 
                WHERE ideventotipo = ".$ideventotipo 
                . $where 
                ." ORDER BY servico, ideventosla";
        $rsp = d::b()->query($sqsp) or die("[model-evento] - Erro ao configuracao de SLA : " . mysql_error() . "<p>SQL:".$sqsp);   
        return $rsp;
    }

    function getSelectSla($campo, $ideventotipo)
    {
        return "SELECT DISTINCT(".$campo.") as id, ".$campo."
                  FROM eventosla 
                 WHERE ideventotipo = ".$ideventotipo."
                   AND status='ATIVO' 
              ORDER BY ".$campo."";
    }

    function getIdeventoFilho($_1_u_evento_idmodulo)
    {
        $sqlobj = "SELECT idevento
					 FROM eventoobj eo 
                    WHERE ideventoobj = '".$_1_u_evento_idmodulo."';";
        $res = d::b()->query($sqlobj) or die("[model-evento] - Erro ao buscar status do usuario no evento: ".mysqli_error(d::b()));
        return $res;
    }

    //Lista as pessoas que foram inseridas no Evento
    function getListaComentariosEvento($_1_u_evento_idevento)
    {
        $sqlc = "SELECT IF(p.nomecurto is null, p.nome, p.nomecurto) AS nomecurto,
                      e.* , 
					  et.anonimo,
                      if(ev.idpessoa = p.idpessoa, 'Y', 'N') as dono
                 FROM modulocom e JOIN pessoa p ON(p.usuario=e.criadopor)
				 JOIN evento ev ON ev.idevento = e.idmodulo AND e.modulo = 'evento'
				 JOIN eventotipo et ON et.ideventotipo = ev.ideventotipo
                WHERE e.idmodulo=".$_1_u_evento_idevento."
                  AND e.status = 'ATIVO' 
             ORDER BY e.criadoem desc";
        $resc = d::b()->query($sqlc) or die("[model-evento] - Erro ao buscar getListaComentariosEvento.".$sqlc);
        return $resc;
    }

    function getEventoTipoAdd($_1_u_evento_ideventotipoadd)
    {
        $sqad="SELECT * 
                 FROM eventotipoadd 
                WHERE ideventotipoadd = ".$_1_u_evento_ideventotipoadd;
        $resc = d::b()->query($sqad) or die("[model-evento] - erro ao buscar os adicionais do evento: ". mysqli_error(d::b())." ".$sqad);
        return $resc;
    }

    function getEventoTipoBloco($_1_u_evento_ideventotipo)
    {
        $sqad="SELECT 
                    ideventotipoadd, titulo,
                    CASE
						WHEN tag = 'Y' THEN 'tag'
						WHEN sgdoc = 'Y' THEN 'sgdoc'
						WHEN pessoa = 'Y' THEN 'pessoa'
						WHEN prodserv = 'Y' THEN 'prodserv'
						WHEN minievento = 'Y' THEN 'minievento'
                        ELSE ''
					END as tipoobjeto
                FROM eventotipoadd 
                WHERE status = 'ATIVO' and ideventotipo = ".$_1_u_evento_ideventotipo;
        $resc = d::b()->query($sqad) or die("[model-evento] - erro ao buscar os adicionais do evento: ". mysqli_error(d::b())." ".$sqad);
        return $resc;
    }

    //Retorna os Dados da Tabela eventoadd - LTM (07/07/2020)
    function getEventoAdd($idevento)
    {
        $sqad = "SELECT * 
                 FROM eventoadd 
                WHERE idevento = ".$idevento."
                ORDER BY ord, ideventoadd";
        $resc = d::b()->query($sqad) or die("[model-evento] - erro ao buscar os adicionais do Mini evento: ". mysqli_error(d::b())." ".$sqad);
        return $resc;
    }

    //Verifica se tem cadastro na tabela eventoadd. Se tiver pegar os dados desta tabela, senão pega do eventotipoadd - LTM (06/07/2020)
    function getMiniEventoAddCount($idevento)
    {
        $resc = $this->getMiniEventoAdd($idevento);
        $total = mysqli_num_rows($resc);
        return $total;
    }

    //Retorna a Quantidade de MiniEventos ligados ao Evento para que não carregue a função sem ter vinculo
    function getMiniEventoCount($idevento, $ideventoadd)
    {
        $sqad="SELECT *
                 FROM eventoobj 
                WHERE idevento = '".$idevento."'
                  AND ideventoadd = '".$ideventoadd."'
                  AND objeto = 'evento'";
        $resc = d::b()->query($sqad) or die("[model-evento] - erro ao buscar os adicionais Contador do MIni evento model/evento.php: ". mysqli_error(d::b())." ".$resc);
        $total = mysqli_num_rows($resc);
        return $total;
    }

    function getRotuloCor($idevento, $ideventoobj)
    {
       $sqlobj = "SELECT e.status,
                          e.idevento,
                          ideventoobj,
                          es.rotulo,
                          es.cor
                     FROM eventoobj eo 
				LEFT JOIN evento e ON e.modulo = 'eventoobj' AND e.idmodulo = eo.ideventoobj
                LEFT JOIN fluxostatus fs ON fs.idfluxostatus = e.idfluxostatus
                LEFT JOIN "._DBCARBON."._status es ON es.idstatus = fs.idstatus
                    WHERE eo.idevento = ".$idevento."
                      AND eo.ideventoobj = '".$ideventoobj."' limit 1;";
        $res = d::b()->query($sqlobj) or die("[model-evento] - Erro ao buscar comentarios getRotuloCor.".$sqlobj);
        return $res;
    }

    //Retorna o Evento Filho
    function getEventoFilho($_1_u_evento_idevento)
    {
        $sqlp = "SELECT e.idevento,
                      e.evento,
                      e.descricao,
                      e.inicio,
                      e.iniciohms,
                      e.fim,
                      e.fimhms,
                      e.prazo,
                      t.tag,
                      t.descricao
                 FROM evento e LEFT JOIN tag t ON(t.idtag=e.idequipamento)
                WHERE e.ideventopai= ".$_1_u_evento_idevento." 
             ORDER BY e.inicio;";
        $res = d::b()->query($sqlp) or die("[model-evento] - Erro ao buscar evento filho: ".mysqli_error(d::b()));
        return $res;
    }

    function getAssinatura($_1_u_evento_idevento)
    {
        $sqla="SELECT * FROM carrimbo 
                WHERE status='PENDENTE' 
                  AND idobjeto = ".$_1_u_evento_idevento." 
                  AND tipoobjeto in ('evento')
                  AND idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
        $res = d::b()->query($sqla) or die("[model-evento] - Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
        return $res;
    }

    //Lista as Pessoas Inseridas no Evento
    function listaPessoaEvento()
    {
        global $_1_u_evento_idevento, $_1_u_evento_idpessoa,$_1_u_evento_modulo,$_1_u_evento_idmodulo, $modelo;
        $s = "SELECT r.idfluxostatuspessoa, 
                     IF(s.nomecurto is null, s.nome, s.nomecurto) AS nomecurto, 
                     s.idpessoa, 
                     r.visualizado, 
                     r.oculto, 
                     r.inseridomanualmente,
                     r.criadopor,
                     r.criadoem,
                     r.status,
                     s.idtipopessoa, 
                     g.grupo, 
                     CASE
                        WHEN ps.tipoobjeto = 'sgsetor' THEN ss.setor
                        WHEN ps.tipoobjeto = 'sgdepartamento' THEN sd.departamento
                        WHEN ps.tipoobjeto = 'sgarea' THEN sa.area
                     END AS 'setor',
                     rg.idfluxostatuspessoa AS idfluxostatuspessoagrupo,
                     es.rotuloresp AS respstatus,
                     es.cor AS respcor,
                     r.assinar, 
                     et.anonimo,
                     if(e.idpessoa = r.idobjeto, 'Y', 'N') AS dono, 
                     g.idimgrupo
                FROM fluxostatuspessoa r
                    JOIN evento e ON r.idmodulo = e.idevento AND r.modulo = 'evento'
                    JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
                    JOIN pessoa s ON s.idpessoa = r.idobjeto AND r.tipoobjeto ='pessoa'
                    LEFT JOIN imgrupo g ON g.idimgrupo = r.idobjetoext 
                    LEFT JOIN pessoaobjeto ps ON ps.idpessoa =  s.idpessoa AND ps.tipoobjeto IN ('sgsetor', 'sgdepartamento', 'sgarea') 
                       -- AND ps.tipoobjeto = 'sgsetor' -- (Retirado pq não aparecia o nome das pessoas que estava em outras áreas - Lidiane - 03-04-2020)
                    LEFT JOIN sgsetor ss ON ss.idsgsetor = ps.idobjeto AND ss.status = 'ATIVO' 
                    LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = ps.idobjeto AND sd.status = 'ATIVO' 
                    LEFT JOIN sgarea sa ON sa.idsgarea = ps.idobjeto AND sa.status = 'ATIVO' 
                    LEFT JOIN fluxostatuspessoa rg ON rg.idobjeto = r.idobjetoext AND rg.idmodulo = r.idmodulo AND rg.modulo = 'evento'
                    LEFT JOIN fluxostatus fs ON fs.idfluxostatus = r.idfluxostatus
                    LEFT JOIN "._DBCARBON."._status es ON(es.idstatus = fs.idstatus)
              WHERE r.idmodulo = '".$_1_u_evento_idevento."'
            GROUP BY s.nome -- (Acrescentado para não repetir os nomes - Lidiane - 03-04-2020)
            ORDER BY g.grupo, s.nome";
    
        $rts = d::b()->query($s) or die("[model-evento] - listaPessoaEvento: ". mysqli_error(d::b()));

        //Pega o status do Módulo atual.
        $tabela = getModuloTab($_1_u_evento_modulo);

        echo "<div class='table-hover table table-striped planilha'>";

        // Verifica se a pessoa pode remover quaquer grupo ou pessoa
        // getModsUsr("MODULOS"): Pega todos os modulos vinculados às minhas LP's
        $eventoMaster = array_key_exists('eventomaster', getModsUsr("MODULOS"));

        while ($r = mysqli_fetch_assoc($rts)) 
        {
            $cor = $r['respcor'];
            $respstatus = $r['respstatus'];
            if(!empty($_1_u_evento_modulo) and !empty($_1_u_evento_idmodulo))
            {
                $versao=0;
                $cassinar='Y';
                if ( $_1_u_evento_modulo == 'documento')
                {
                    //Retorna a Versão do Documento
                    $sqld="SELECT s.versao 
                            FROM sgdoc s
                            WHERE idsgdoc = ".$_1_u_evento_idmodulo;
                    $resd = d::b()->query($sqld) or die("[model-evento] - Erro vesao do documento para assinatura: ".mysqli_error(d::b()));
                    $rowd=mysqli_fetch_assoc($resd);
                    $versao=$rowd['versao'];
                    
                    //Retorna a Assinatura
                    $sqlx = "SELECT c.idcarrimbo,
                                    c.status,
                                    if(s.versao = c.versao, null, s.versao) as versao
                                FROM sgdoc s
                                JOIN carrimbo c on s.idsgdoc = c.idobjeto and (s.versao = c.versao or c.versao = 0)
                                WHERE c.status      in ('PENDENTE', 'ATIVO')
                                AND c.idpessoa    = ".$r['idpessoa']."
                                AND c.idobjeto    = ".$_1_u_evento_idmodulo."
                                AND c.tipoobjeto  = '".$_1_u_evento_modulo."'                                   
                                LIMIT 1";
                    $resx = d::b()->query($sqlx) or die("[model-evento] - Erro versao assinada do documento para assinatura: ".mysqli_error(d::b()));
                    $rowx=mysqli_fetch_assoc($resx);
                    if($rowx['status']=='PENDENTE'){
                        $clbt="warning";
                        $cassinar='N';
                    }elseif($rowx['status']=='ATIVO'){
                        $clbt="success";
                    }else{
                        $clbt="default";
                    }
                }else{
                    $versao=0;
                    $sqlx = "SELECT c.idcarrimbo,
                                    c.status
                            FROM carrimbo c 
                            WHERE c.status IN ('PENDENTE', 'ATIVO')
                                AND c.idpessoa    = ".$r['idpessoa']."
                                AND c.idobjeto    = ".$_1_u_evento_idmodulo."
                                AND c.tipoobjeto  in ('".$_1_u_evento_modulo."', '$tabela') 
                        ORDER BY idcarrimbo desc
                            LIMIT 1"; //die($sqlx);
                    $resx = d::b()->query($sqlx) or die("[model-evento] - Erro versao assinada do anexo para assinatura: ".mysqli_error(d::b()));
                    $rowx=mysqli_fetch_assoc($resx);
                    if($rowx['status']=='PENDENTE'){																			
                        //Alterado: Altera a cor para azul: assinatura (29-01-2020 - Lidiane)
                        //$clbt="warning";							
                        $clbt="success-signature"; 															
                        $cassinar='N';
                        $idcarrimbo = $rowx['idcarrimbo'];
                    }elseif($rowx['status']=='ATIVO'){
                        $clbt="success disabled";
                    }else{
                        $clbt="default";
                    }
                }
                if($rowx['idcarrimbo']){
                    $idcarrimbo = $rowx['idcarrimbo'];
                } else {
                    $idcarrimbo = 0;
                }						                 

                if(!empty($_1_u_evento_modulo) && !empty($_1_u_evento_idmodulo) && !empty($tabela))
                {
                    $idfluxostatus = traduzid($tabela, 'id'.$tabela, 'idfluxostatus', $_1_u_evento_idmodulo);
                    if(is_numeric($idfluxostatus) == true){$idfluxostatus = $idfluxostatus;} else {$idfluxostatus = '';}
                } else {
                    $idfluxostatus = '';
                }

                // GVT - 23/08/2021 - @479450 - Retirar Assinatura dos Eventos
                $inbtstatus="<button onclick=\"criaassinatura(".$r['idpessoa'].",'".$tabela."',".$_1_u_evento_idmodulo.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.",".$idfluxostatus.")\" type='button' class='hidden btn btn-xs btn-".$clbt."  hovercinza pointer floatright ' title='Solicitação de Assinatura' style='margin-right: 8px; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
            }
                                         
            if ($r["idpessoa"] == $_SESSION["SESSAO"]["IDPESSOA"])
            {
                echo '<input id="statusresp" type="hidden" value="'.$r["resptoken"].'" readonly="readonly">';
            }
                        
            $pad = 'padding: 2px 24px;';
            
            if ( $r['oculto'] == '1'){
                $op = 'opacity:0.5;';
            }else{
                $op ='';
            }
            
            if ($grupo != $r["grupo"]){
                if ($grupo != ''){
                    echo '</div></fieldset>';
                }
                $grupo = $r["grupo"];
                if(($_SESSION["SESSAO"]["IDPESSOA"] != $_1_u_evento_idpessoa) && (!$eventoMaster)){
                    echo "<div style='padding:0px 6px;'><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <i class=\"fa fa-ban fa-1x cinzaclaro hovercinza btn-lg pointer ".$cor." ui-droppable\" title='Excluir!'></i><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
                }else{
                    if ($modelo == 'xs'){
                        echo "<div style='padding:0px 6px;'><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
                    }else{
                        echo "<div style='padding:0px 6px;'><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <i class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"retirasgsetor(".$r['idfluxostatuspessoagrupo'].",'".$grupo."')\" title='Excluir!'></i><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
                    }
                }
            }	
    
            if (!empty($r["grupo"])){
                $pad = '';
            }
    
            if($r['idtipopessoa']==1){
                $mod='funcionario';
            }else{
                $mod='pessoa';
            }

            if(($r['idpessoa'] == $_1_u_evento_idpessoa || !$eventoMaster) && ($r['inseridomanualmente']=='N' or $_SESSION["SESSAO"]["IDPESSOA"] != $_1_u_evento_idpessoa)){
                $botao="<i class='fa fa-ban fa-1x cinzaclaro hovercinza btn-lg pointer #f5f5f5 ui-droppable px-0' title='Excluir!'></i>";
            }else{
                $botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' onclick='retirapessoa(".$r["idfluxostatuspessoa"].",\"".$r["nomecurto"]."\")'></i>";
            }
            $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
            if ($r["setor"]){
                $cl = "&nbsp<span style='background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
            }else{
                $cl = '';
            }	
            
            if ($r['oculto']== '1'){
                $vs = "<i class='fa fa-eye-slash' style='font-size: 14px;color:silver'></i>&nbsp";
            }elseif ($r["visualizado"] == '1'){
                $vs = "<i class='fa fa-check' style='font-size: 14px;color:#4FC3F7'></i>&nbsp";
            }else{
                $vs = "<i class='fa fa-check' style='font-size: 14px;color:#fff'></i>&nbsp";
            }
            
            if ($r['aprova'] == 1){
                $va = "<i class='fa fa-edit' style='font-size: 14px;color:#fff'></i>&nbsp";
            }else{
                $va = "<i class='fa fa-edit' style='font-size: 14px;color:#fff'></i>&nbsp";	
            }
            
            
            if ($r["anonimo"] == 'Y' && $r["dono"] == 'Y'){
                $r["nomecurto"] = '<i><b>ANÔNIMO</b></i>';
                $cl = '';
            }
            if ($modelo == 'xs'){
                $md1 = '12';
                $md2 = '12';
    
                echo "<div id=".$r["idfluxostatuspessoa"]." class='".$opacity." col-md-12' style='".$pad."".$op."''>
                        <div class='col-md-".$md1."' style='line-height: 14px; padding: 8px; font-size: 10px;'>
                            <span data-toggle='collapse' href='#".$r['idpessoa']."' title=".$respstatus." onclick='carregaFiltroTipoEvento(".$r['idfluxostatuspessoa'].", ".$r['idpessoa'].", ".$_1_u_evento_idevento.");' class='circle button-".$cor."' style='background:".$cor."; border:none;'></span>&nbsp".$vs."".$r["nomecurto"]." ".$cl."</div>
                            <div class='col-md-".$md2."'><div style='float:right;font-size:9px;cursor:default;' class='btn btn-xs btn-".$clbt."'><i class='fa fa-check'></i>&nbsp;Assinatura</button></div>
                        </div>
                    </div>
                    <div id='collapse-".$r['idpessoa']."'></div>";
            }else{
                $md1 = '8';
                $md2 = '3';
                $md3 = '1';
                echo "<div id='".$r["idfluxostatuspessoa"]."' class='".$opacity." col-md-12' style='".$pad."".$op."''>
                        <div class='col-md-".$md1."' style='line-height: 14px; padding: 8px; font-size: 10px;'>
                            <span data-toggle='collapse' href='#".$r['idpessoa']."' title='".$respstatus."' onclick='carregaFiltroTipoEvento(".$r['idfluxostatuspessoa'].", ".$r['idpessoa'].", ".$_1_u_evento_idevento.");' class='circle button-".$cor."' style='background:".$cor."; border:none;'></span>&nbsp".$vs."".$r["nomecurto"]." ".$cl."
                        </div>
                        <div class='col-md-".$md2."'>".$inbtstatus."</div><div class='col-md-".$md3."'>".$botao."</div> 
                    </div>
                    <div id='collapse-".$r['idpessoa']."'></div>";
            }
        }
        if ($grupo != ''){
            echo '</div></fieldset>';
        }
        echo "</div>";
    }

    //evento - atualiza os participantes definidos no eventotipo
    function atualizaparticipantes($idevento, $tokeninicial, $tipo = NULL, $idobjeto = NULL)
    {
        if($tipo == 'imgrupo')
        {
            $this->insereGrupoFluxoStatusPessoa($idevento, $tokeninicial, $idobjeto);
        }
        //Alterado para chamar a função que está no arquivo [model-evento] - . . Replace para inserir as pessoas do Grupo no Evento
        replaceEvento($idevento, $tokeninicial);					
        
        //Retorna o tipo de Módulo e o Id deste.
        $sql = "SELECT r.idobjeto,
                       r.modulo, 
                       r.idmodulo, 
                       et.assinar 
                  FROM fluxostatuspessoa r
                  JOIN evento e on e.idevento = r.idmodulo AND r.modulo = 'evento' 
                  JOIN eventotipo et on et.ideventotipo = e.ideventotipo
                 WHERE r.tipoobjeto = 'pessoa' and r.idmodulo =".$idevento;
        $res = d::b()->query($sql) or die("[model-evento] - AtualizaParticipantes: ". mysqli_error(d::b()));;
        
        while ($r = mysqli_fetch_assoc($res)) {
            if( $r['assinar'] == 'Y'){
                criaAssinatura($r["idobjeto"], $r["modulo"], $r["idmodulo"]);
            }
        }
    }

    //evento - insere os participantes definidos no eventotipo
    function insereParticipantes($idevento, $ideventotipo, $tokeninicial, $idcriador)
    {
        if(empty($ideventotipo)){
            $ideventotipo= traduzid('evento', 'idevento', 'ideventotipo', $idevento);
        }
            
        $sqlFuncionarios = "SELECT mfo.idobjeto,
                                   mfo.tipoobjeto 
                               FROM fluxo ms JOIN fluxoobjeto mfo ON mfo.idfluxo = ms.idfluxo
                               AND mfo.idobjeto NOT IN (SELECT idobjeto FROM fluxostatuspessoa WHERE idmodulo = ".$idevento." AND modulo = 'evento')
                              WHERE tipo = 'PARTICIPANTE' 
                                AND ms.idobjeto = ".$ideventotipo." AND ms.modulo = 'evento' AND ms.status = 'ATIVO'";

        $resFuncionarios = d::b()->query($sqlFuncionarios) or die("[model-evento] -  Erro carregar participantes: ".mysqli_error(d::b()));			
        $resultsetor = array();
        $resultpessoa = array();
        $i = 0;
        $j = 0;
                
        while($funcionarios = mysqli_fetch_assoc($resFuncionarios)){
            if($funcionarios['tipoobjeto']=='imgrupo'){
                $resultsetor[$i++] = $funcionarios['idobjeto'];
            }elseif($funcionarios['tipoobjeto']=='pessoa'){
                $resultpessoa[$j++] = $funcionarios['idobjeto'];
            }
        }
                
        if ($resultsetor) {
            $resultsetor = implode(",", $resultsetor);
        } else {
            $resultsetor = "''";
        }
        
        if ($resultpessoa) {
            $resultpessoa = implode(",", $resultpessoa);
        } else {
            $resultpessoa = "''";
        }
        
        //Alterado para chamar a função que está no arquivo [model-evento] - . Busca as pessoas Ativas que estão no Evento.
        $sqlPessoa = sqlPessoa($resultpessoa);
        $resPessoa = d::b()->query($sqlPessoa) or die("[model-evento] - Erro ao carregar configuracao de Pessoa: ".mysqli_error(d::b()));
       
        //Alterado para chamar a função que está no arquivo [model-evento] - . Busca os grupos Ativos que estão no Evento.
        $sqlImGrupo = sqlImGrupo($resultsetor);	
        $resImGrupo = d::b()->query($sqlImGrupo) or die("[model-evento] - Erro ao carregar configuracao do Setor: ".mysqli_error(d::b()));
        
        /** 
         * Busca o status do evento no momento
        *
        */
        $sqlFuncEvento = "SELECT idfluxostatus FROM evento WHERE idevento = ".$idevento." ".getidempresa('idempresa','evento')."";
        $resFuncEvento = d::b()->query($sqlFuncEvento) or die("[model-evento] - Erro carregar status do Evento: ".mysqli_error(d::b()));
        $rFuncEvento = mysqli_fetch_assoc($resFuncEvento);	
        $eventoAtual = $rFuncEvento['idfluxostatus'];
        
        $arrDestNotif = [];

        while($r = mysqli_fetch_assoc($resPessoa)) {
            //Alterado para chamar a função que está no arquivo [model-evento] - . Busca inidstatus para validar a inserção no fluxostatuspessoa.
            $sqlFuncEvento = sqlEventoTipoResp($ideventotipo, $r["idpessoa"], $_SESSION["SESSAO"]["IDEMPRESA"]);
            $resFuncEvento = d::b()->query($sqlFuncEvento) or die("[model-evento] - Erro carregar validação status pessoa: ".mysqli_error(d::b()));
            $rFuncEvento = mysqli_fetch_assoc($resFuncEvento);
            if($rFuncEvento['inidstatus'] == $eventoAtual || $rFuncEvento['inidstatus'] == NULL){
                $this->inserefluxostatuspessoa($idevento, $r["idempresa"], $r["idpessoa"], 'pessoa', $tokeninicial, $idcriador, 'insereParticipantes');
                
                if($r["idpessoa"] != $idcriador)
                    $arrDestNotif[] = $r["idpessoa"];
            }
        }

        if(count( $arrDestNotif ) > 0)
            $this->notificacaoEvento($idevento, $arrDestNotif);

        while($r = mysqli_fetch_assoc($resImGrupo)) {
            
            //Alterado para chamar a função que está no arquivo [model-evento] - . Busca inidstatus para validar a inserção no fluxostatuspessoa.
            $sqlFuncEvento = sqlEventoTipoResp($ideventotipo, $r["idimgrupo"], $_SESSION["SESSAO"]["IDEMPRESA"]);
            $resFuncEvento = d::b()->query($sqlFuncEvento) or die("[model-evento] - Erro carregar validação status grupo: ".mysqli_error(d::b()));
            $rFuncEvento = mysqli_fetch_assoc($resFuncEvento);
          
            if($rFuncEvento['inidstatus'] == $eventoAtual || $rFuncEvento['inidstatus'] == NULL)
            {
                $this->inserefluxostatuspessoa($idevento, $r["idempresa"], $r["idimgrupo"], 'imgrupo', $tokeninicial, $idcriador);

                $this->insereGrupoFluxoStatusPessoa($idevento, $tokeninicial, $r["idimgrupo"]);   
            }
        }
        
        //Insere as pessoas que tem que assinar na tabela carrimbo - Lidiane (04/06/2020)
        //Evento de Compras - Guilherme
        $sqlTipoResp = "SELECT mfo.idobjeto 
                          FROM fluxo ms JOIN fluxoobjeto mfo ON mfo.idfluxo = ms.idfluxo 
                         WHERE ms.idobjeto = '".$ideventotipo."' AND assina in ('INDIVIDUAL', 'PARCIAL', 'TODOS') AND  mfo.tipoobjeto = 'pessoa'";
        $resTipoResp = d::b()->query($sqlTipoResp) or die("[model-evento] - Erro ao carregar pessoas eventotipo: ".mysqli_error(d::b()));
        while ($rTipoResp = mysql_fetch_assoc($resTipoResp)) {
            $idobjeto = $rTipoResp['idobjeto'];
            
            $sql = "INSERT INTO carrimbo 
                                (idempresa, 
                                idpessoa,
                                idobjeto, 
                                tipoobjeto, 
                                idobjetoext, 
						        tipoobjetoext,
                                status, 
                                criadopor, 
                                criadoem, 
                                alteradopor, 
                                alteradoem)
                        VALUES        
                                (".$_SESSION['SESSAO']['IDEMPRESA'].",
                                '".$idobjeto."',
                                '".$idevento."',
                                'evento', 
                                '$eventoAtual',
                                'idfluxostatus',
                                'PENDENTE',
                                '".$_SESSION['SESSAO']['USUARIO']."',
                                '".date('Y-m-d H:i:s')."',
                                '".$_SESSION['SESSAO']['USUARIO']."',
                                '".date('Y-m-d H:i:s')."    ');";
                                            
                $res = d::b()->query($sql) or die("[model-evento] - Erro ao inserir carrimbo: ".mysqli_error(d::b()));
                $row = mysqli_fetch_assoc($res);
        }
        
        //Alterado para chamar a função que está no arquivo [model-evento] - . Replace para inserir as pessoas do Grupo no Evento						
        replaceEventoUnion($idevento, $tokeninicial);

    }

    function notificacaoEvento ( $idevento, $arrDestinatarios = [] ) {

        $eventoTipoDescr = $this->getEventoTipoByIdEvento($idevento);
        $eventoTitulo = $this->getEventoTituloByIdEvento($idevento);

        $notif = Notif::ini()
				->canal("browser")
				->conf([
					"mod" => "evento",
                    "modpk" => "idevento",
                    "idmodpk" => $idevento,
                    "title" => "Você foi adicionado em um evento de ".$eventoTipoDescr,
                    "corpo" => $eventoTitulo,
                    "localizacao" => "dashboardsnippet",
                    "url" => "https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=".$idevento
				]);

        foreach($arrDestinatarios as $idpessoa){
            $notif->addDest($idpessoa);
        }
        $notif->send();
    }
	
	function getDadosEventoAdd($idevento)
	{
		$sqlFilhos = "SELECT if(idobjeto IS NULL, ideventoadd, idobjeto) AS idobjeto, titulo, objeto, tipoobjeto FROM eventoadd WHERE idevento = ".$idevento;
		$resFilhos = d::b()->query($sqlFilhos) or die("[saveposchange__evento]-Erro ao buscar objeto dos eventos filhos: ".mysqli_error(d::b()));	
		return $resFilhos;
	}
	
	function criaEvento($idEventoPai, $evento, $dataInicioEvento, $dataFimEvento,$tokeninicial) 
	{
		//VERIFICA SE JÁ EXISTE UM EVENTO FILHO COM A DATA ESPECIFICADA
		$sql = "SELECT idevento from evento e where  ideventopai =  ".$idEventoPai." and inicio = '".$dataInicioEvento."'";  

		$res = d::b()->query($sql);
		$criar = true;
		while ($r = mysqli_fetch_assoc($res)) {
			$criar = false;
			$ideventofilho .= ' '.$r['idevento'];
		}	
		// CASO NEGATIVO, CRIA O EVENTO FILHO PARA A DATA ESPECIFICADA
		if ($criar)
		{
			$sql = "INSERT INTO evento(
							ideventotipo, 
							idempresa,
							idpessoa,
							ideventopai, 
							evento, 
							idfluxostatus,
							idsgdoc,
							idequipamento,
							idpessoaev,
							descricao, 
							inicio,
							iniciohms, 
							fim,
							fimhms,
							prazo, 
							versao, 
							resultado,
							criadopor,
							criadoem, alteradopor,
							alteradoem
				) VALUES (".$evento['ideventotipo'].",
					".$evento['idempresa'].",
					".$_SESSION["SESSAO"]["IDPESSOA"].",
					".$idEventoPai.",
					'".addslashes($evento['evento'])."',
					'".$tokeninicial."',
					'".$evento['idsgdoc']."',
					'".$evento['idequipamento']."',
					'".$evento['idpessoaev']."',
					'".addslashes($evento['descricao'])."',
					'".$dataInicioEvento."',
					'".$evento['iniciohms']."',
					'".$dataFimEvento."',
					'".$evento['fimhms']."',
					'".$dataInicioEvento."',
					'".$evento['versao']."',
					'".$evento['resultado']."',					
					'".$evento['criadopor']."',
					'".$evento['criadoem']."',
					'".$evento['alteradopor']."',
					'".$evento['alteradoem']."');";

			$res = d::b()->query($sql) or die('[model-evento] - Erro ao gerar evento filho sql='.$sql);
     
			$idnovoev= mysqli_insert_id(d::b());
			
			//Select o titulo do Evento Pai (LTM - 10/07/2020)
			$resEventoAdd = $this->getDadosEventoAdd($idEventoPai);
			while($rTitulo = mysqli_fetch_assoc($resEventoAdd))
			{
				$this->insEventoAddNovoBloco($evento['idempresa'], $rTitulo['idobjeto'], $idnovoev, $rTitulo['titulo'], $rTitulo['tipoobjeto'], $rTitulo['objeto']);
			}

			$sqlo="SELECT * FROM eventoobj WHERE idevento=".$idEventoPai;
			
			$reso = d::b()->query($sqlo) or die("[[model-evento]]-Erro ao gerar adicionais dos eventos filhos: ".mysqli_error(d::b()));

			$arrColunas = mysqli_fetch_fields($reso);
			while($robj = mysqli_fetch_assoc($reso)) {
				//print_r($robj); die;
				$this->inseriobj($robj,$arrColunas,$idnovoev, $idEventoPai);                     
			}
			
			$sqlo="SELECT * FROM fluxostatuspessoa WHERE idmodulo = ".$idEventoPai." AND modulo = 'evento'";
			
			$reso = d::b()->query($sqlo) or die("[model-evento]-Erro ao gerar responsaveis dos eventos filhos: ".mysqli_error(d::b()));

			$arrColunas = mysqli_fetch_fields($reso);
			while($robj = mysqli_fetch_assoc($reso)) {
				//print_r($robj); die;
				$this->inserifluxostatuspessoa($robj,$arrColunas,$idnovoev);                     
			}
                
		}else{
			// CASO POSITIVO, ATUALIZA OS DADOS DO EVENTO FILHO
			// @TODO: AJUSTAR PARA ATUALIZAR SOMENTE OS EVENTOS FILHOS NÃO FINALIZADOS
			$ideventofilho = str_replace(' ',',',trim($ideventofilho));
			$sql = "UPDATE evento set 
							evento 		= '".$evento['evento']."',				
							inicio 		= '".$dataInicioEvento."',
							prazo 		= '".$dataInicioEvento."',
							iniciohms 	= '".$evento['iniciohms']."',
							fim 		= '".$dataFimEvento."',
							fimhms 		= '".$evento['fimhms']."',
                            ordem = 99999
						WHERE
						idevento 	in (".$ideventofilho.");";

			$res = d::b()->query($sql);
			
			//atualizar eventoobj nos filhos
			$sqlo="SELECT pr.* 
                     FROM evento f JOIN  eventoobj pr on f.ideventopai = pr.idevento
					WHERE f.idevento = ".$ideventofilho."
					  AND NOT EXISTS(SELECT 1 FROM eventoobj fr 
									  WHERE fr.idevento = f.idevento 
									    AND fr.idobjeto = pr.idobjeto 
									    AND fr.objeto = pr.objeto)";
									
			$reso = d::b()->query($sqlo) or die("[model-evento]-Erro 2 ao gerar adicionais dos eventos filhos: ".mysqli_error(d::b()));

			$arrColunas = mysqli_fetch_fields($reso);
			while($robj = mysqli_fetch_assoc($reso)) {
				//print_r($robj); die;
				$this->inseriobj($robj,$arrColunas,$ideventofilho, $idEventoPai);                     
				 
			}
			
			$sqlo="SELECT pr.* 
                     FROM evento f JOIN  fluxostatuspessoa pr on f.ideventopai = pr.idmodulo AND pr.modulo = 'evento'
                    WHERE f.idevento = ".$ideventofilho."
					  AND NOT EXISTS(SELECT 1 FROM fluxostatuspessoa fr 
                                      WHERE fr.idmodulo=f.idevento 
										AND fr.idobjeto=pr.idobjeto 
										AND fr.tipoobjeto=pr.tipoobjeto)";

			$reso = d::b()->query($sqlo) or die("[model-evento]-Erro ao atualizar responsaveis dos eventos filhos: ".mysqli_error(d::b()));

			$arrColunas = mysqli_fetch_fields($reso);
			while($robj = mysqli_fetch_assoc($reso)) {

			  $this-> inserifluxostatuspessoa($robj,$arrColunas,$ideventofilho);                     
			}
		}

		if (!$res) {
			echo("Erro ao inserir eventoss " . mysqli_error(d::b()) . "<p>SQL: $sql");
		}
	}

    // Retorna areas ativas
    function getAreas($ids)
    {
        global $JSON;        
        $idAreas = str_replace(",", "','", $ids);

        $sql = "SELECT a.idsgarea, CONCAT(e.sigla,'-', a.area) as area
                FROM sgarea a
                JOIN empresa e on(e.idempresa = a.idempresa)
                WHERE a.idsgarea in ('$idAreas')
                and a.status = 'ATIVO';";

        $rts = d::b()->query($sql) or die("getAreas model/evento: " . mysqli_error(d::b())." ".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idsgarea"];
            $arrtmp[$i]["label"] = $r["area"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    //Retorna os Documentos Para inserir no Evento - Nativo
    function getDepartamentos($inidsgdepartamento) 
    {
        global $JSON;        
        $inidsgdepartamento = str_replace(",", "','", $inidsgdepartamento);

        $sql = "SELECT d.idsgdepartamento,
                       CONCAT(e.sigla,'-', d.departamento) AS departamento
                  FROM sgdepartamento d JOIN empresa e ON e.idempresa = d.idempresa
                 WHERE d.idsgdepartamento in ('".$inidsgdepartamento."')
              ORDER BY d.departamento";
        $rts = d::b()->query($sql) or die("getDepartamentos model/evento: " . mysqli_error(d::b())." ".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idsgdepartamento"];
            $arrtmp[$i]["label"] = $r["departamento"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    function getAreasByEmpresaId($ids)
    {
        global $JSON;        
        $ids = str_replace(",", "','", $ids);

        $sql = "SELECT a.idsgarea, CONCAT(e.sigla, ' - ', a.area) AS area
                FROM sgarea a
                JOIN empresa e ON(e.idempresa = a.idempresa)
                WHERE e.idempresa IN('$ids')
                AND a.status = 'ATIVO'
                ORDER BY a.area";
        
        $areas = d::b()->query($sql) or die("getAreasByEmpresaId model/evento: " . mysqli_error(d::b())." ".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($areas)) {
            $arrtmp[$i]["value"] = $r["idsgarea"];
            $arrtmp[$i]["label"] = $r["area"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    function getDepartamentosByEmpresaId($ids)
    {
        global $JSON;        
        $ids = str_replace(",", "','", $ids);

        $sql = "SELECT d.idsgdepartamento, CONCAT(e.sigla, ' - ', d.departamento) AS departamento
                FROM sgdepartamento d
                JOIN empresa e ON(e.idempresa = d.idempresa)
                WHERE e.idempresa IN('$ids')
                AND d.status = 'ATIVO'
                ORDER BY d.departamento";
        
        $departamentos = d::b()->query($sql) or die("getDepartamentosByEmpresaId model/evento: " . mysqli_error(d::b())." ".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($departamentos)) {
            $arrtmp[$i]["value"] = $r["idsgdepartamento"];
            $arrtmp[$i]["label"] = $r["departamento"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    function getSetoresByEmpresaId($ids)
    {
        global $JSON;        
        $ids = str_replace(",", "','", $ids);

        $sql = "SELECT s.idsgsetor, CONCAT(e.sigla, ' - ', s.setor) AS setor
                FROM empresa e
                JOIN sgsetor s ON(s.idempresa = e.idempresa)
                WHERE e.idempresa
                AND s.status = 'ATIVO'
                AND e.idempresa IN('$ids');
        ";

        $setores = d::b()->query($sql) or die("getSetoresByEmpresaId model/evento: " . mysqli_error(d::b())." ".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($setores)) {
            $arrtmp[$i]["value"] = $r["idsgsetor"];
            $arrtmp[$i]["label"] = $r["setor"];
            $i++;
        }

        return $JSON->encode($arrtmp);
    }

    //Retorna os Documentos Para inserir no Evento - Nativo
    function getSetores($inidsgsetor) 
    {
        global $JSON;        
        $inidsgsetor = str_replace(",", "','", $inidsgsetor);

        $sql = "SELECT s.idsgsetor,
                       CONCAT(e.sigla,'-', s.setor) AS setor
                  FROM sgsetor s JOIN empresa e ON e.idempresa = s.idempresa
                 WHERE s.idsgsetor in ('".$inidsgsetor."')
              ORDER BY s.setor";
        $rts = d::b()->query($sql) or die("getSetores model/evento: " . mysqli_error(d::b())." ".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idsgsetor"];
            $arrtmp[$i]["label"] = $r["setor"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }
	
	function inseriobj($robj,$arrColunas,$idnovoev, $idEvento)
	{
		$insevento = new Insert();
		$insevento->setTable("eventoobj");
		
		//Busca o Id do eventoadd correspondente ao novo evento criado (LTM - 10-07-2020)
		$ideventoadd = getInsertIdAdd($idnovoev);
		
		//print_r($arrColunas); die;
		//para cada coluna resultante do select cria-se um item no array
		foreach($arrColunas as $col){ 
		// print_r($col); die;
			if($col->name!='idevento' and $col->name!='ideventoobj' and $col->name!='criadopor' and $col->name!='criadoem' and $col->name!='alteradopor' and $col->name!='alteradoem' and !empty($robj[$col->name])){
				$name=$col->name;
				//Devido a alteração do eventoobj para adicionar os ideventoadd da tabela eventoadd e não mais da tabela eventotipoadd (LTM - 10-07-2020)
				if($name == 'ideventoadd'){
					$insevento->$name = $ideventoadd;
				} else {
					$insevento->$name = $robj[$col->name];
				}
			}
		}

		$insevento->idevento=$idnovoev;
		//print_r($insevento); die;
		$ideventoobj=$insevento->save();
	}

	
	function inserifluxostatuspessoa($robj,$arrColunas,$idnovoev)
	{
		// print_r($robj); die;
		$insevento = new Insert();
		$insevento->setTable("fluxostatuspessoa");

		foreach($arrColunas as $col){ 
			// print_r($col); die;
			if($col->name!='idmodulo' and $col->name!='modulo' and $col->name!='idfluxostatuspessoa'  and $col->name!='criadopor' and $col->name!='criadoem' and $col->name!='alteradopor' and $col->name!='alteradoem' and !empty($robj[$col->name])){
				$name=$col->name;
				$insevento->$name=$robj[$col->name];
			  
			}
		}
						 
		$insevento->idmodulo=$idnovoev;
        $insevento->modulo= 'evento';
		$insevento->save();
	}

    //--------------------------------------- UPDATE --------------------------------------------------
    //Atualiza o Status do Evento para Lido - Nativo
    function upStatusLido()
    {
        global $_1_u_evento_idevento;
        $sup="UPDATE fluxostatuspessoa SET visualizado='1' 
               WHERE idmodulo = ".$_1_u_evento_idevento."  
                 AND modulo = 'evento'
                 AND tipoobjeto='pessoa'
                 AND idobjeto=".$_SESSION["SESSAO"]["IDPESSOA"];
        d::b()->query($sup) or die("Erro ao atualizar evento para lido: ". mysqli_error(d::b()));
    }

    //--------------------------------------- INSERT --------------------------------------------------
    //Insere os Dados dos Campos na Tabela eventotipocampos
    function insEventotipocampos()
    {
        global $_1_u_eventotipo_ideventotipo;
        $sqli="INSERT INTO eventotipocampos
                            (col,
                            rotulo,
                            idempresa,
                            ideventotipo,
                            tabela,
                            criadopor,
                            criadoem,
                            alteradopor,
                            alteradoem)
            (select distinct(mtc.col),
                            mtc.rotpsq,
                            ".$_SESSION["SESSAO"]["IDEMPRESA"].",
                            ".$_1_u_eventotipo_ideventotipo.",
                            'eventotipo',
                            '".$_SESSION["SESSAO"]["USUARIO"]."',
                            sysdate(),
                            '".$_SESSION["SESSAO"]["USUARIO"]."',
                            sysdate()
                       FROM "._DBCARBON."._mtotabcol mtc 
                      WHERE mtc.tab= 'evento'
                        AND mtc.col  in('evento','descricao','idequipamento','idsgdoc','complemento','idpessoaev','idpesssoacli','prazo','inicio','dados1','dados2','texto1','texto2','data1','data2','datahr1','datahr2','nomecompleto','datainicio','datafim','horainicio','horafim','textocurto1','textocurto2','textocurto3','textocurto4','textocurto5','textocurto6'
                        ,'textocurto7','textocurto8','textocurto9','textocurto10','textocurto11','textocurto12','textocurto13','textocurto14','textocurto15','classificacao')
                        AND not exists (select 1 from eventotipocampos c 
                                         where c.ideventotipo=".$_1_u_eventotipo_ideventotipo." 
                                           and c.col=mtc.col)
                )";
            d::b()->query($sqli) or die("[model-evento] - Erro ao inserir campos eventotipo para configuracao: ".mysqli_error()."\n".$sqli);
    }

    //Insere os Dados dos Campos na Tabela eventotipocampos
    function insEventotipoCamposAdd($ideventotipoadd)
    {
        $sqli="INSERT INTO eventotipocampos
                            (col,
                            idempresa,
                            ideventotipoadd,
                            ord,
                            criadopor,
                            criadoem,
                            alteradopor,
                            alteradoem)
            (SELECT distinct(mtc.col),
                            ".$_SESSION["SESSAO"]["IDEMPRESA"].",  
                            ".$ideventotipoadd.",
                            mtc.ordpos,
                            '".$_SESSION["SESSAO"]["USUARIO"]."',
                            sysdate(),
                            '".$_SESSION["SESSAO"]["USUARIO"]."',
                            sysdate()
                       FROM "._DBCARBON."._mtotabcol mtc 
                      WHERE mtc.tab= 'eventoobj'
                        AND mtc.col NOT IN('alteradoem','alteradopor','criadopor','criadoem','idempresa','idevento','ideventoobj','idobjeto','objeto','ideventotipoadd')
                        AND not exists (select 1 from eventotipocampos c 
                                         where c.ideventotipoadd=".$ideventotipoadd." 
                                           and c.col=mtc.col)
                )";
            d::b()->query($sqli) or die("[model-evento] - Erro ao inserir campos para ideventotipoadd: ".mysqli_error()."\n".$sqli);
    }
	
	 //Insere os campos do EventotipoAdd na tabela EventoAdd - LTM (07/07/2020)
    function insereEventoAdd($idEvento, $ideventotipo)
    {
        $sql = "INSERT INTO eventoadd(idobjeto, 
                            idempresa, 
							objeto,
                            idevento, 
                            titulo,
                            tipoobjeto,
                            alteradoem,
                            alteradopor,
                            criadoem,
                            criadopor) 
                    SELECT ideventotipoadd, 
                           idempresa, 
						   'ideventotipoadd',
                           ".$idEvento.", 
                           titulo,
                           CASE WHEN tag = 'Y' THEN 'tag'
                                WHEN sgdoc = 'Y' THEN 'sgdoc'
                                WHEN pessoa = 'Y' THEN 'pessoa'
                                WHEN prodserv = 'Y' THEN 'prodserv'
                                WHEN minievento = 'Y' THEN 'minievento'
                            END AS 'tipoobjeto',
                            now(),
                            '".$_SESSION["SESSAO"]["USUARIO"]."',
                            now(),
                            '".$_SESSION["SESSAO"]["USUARIO"]."'
                      FROM eventotipoadd 
                     WHERE ideventotipo = ".$ideventotipo."
                       AND status='ATIVO'
                  ORDER BY ideventotipoadd";
        $res = d::b()->query($sql) or die("[model-evento] - Erro ao Inserir EventoAdd model/evento: ".mysqli_error(d::b())."\n".$sql);
    }

	//Insere o Evento Novo Bloco e os Eventos criados no Eventotipo - LTM (15-07-2020)
	function insEventoAddNovoBloco($idempresa, $idobjeto, $idevento, $titulo, $tipoobjeto, $objeto, $ord = NULL)
	{
		//Insere o Evento na tabela eventoadd para aparecer os campos no Evento Filho (LTM - 10/07/2020)
		$sqlIins = "INSERT INTO eventoadd (idempresa, idobjeto, objeto, idevento,  titulo, tipoobjeto, criadopor, criadoem, alteradopor, alteradoem) 
					VALUES (".$idempresa.", ".($idobjeto?:"null").", '".$objeto."','".$idevento."', '".$titulo."', '".$tipoobjeto."', '".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now());";
		$res = d::b()->query($sqlIins) or die(" Erro ao inserir eventoadd: " . mysqli_error(d::b())."\n".$sqlIins);
		$idnovoev = mysqli_insert_id(d::b());
		return $idnovoev;
	}

    function insereInicio($idfluxostatuspessoa, $idfluxostatus, $idevento) 
    {
        $sqlins = "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idfluxostatuspessoa, idmodulo, modulo, criadopor, criadoem, alteradopor, alteradoem)
                    VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idfluxostatus.",".$idfluxostatuspessoa.", $idevento, 'evento', '".$_SESSION["SESSAO"]["USUARIO"]."', now(), '".$_SESSION["SESSAO"]["USUARIO"]."', now())";
        d::b()->query($sqlins) or die("[saveposchange__evento]-Falha ao inserir fluxostatushist ".mysqli_error(d::b())."<p>SQL: ".$sqlins);	
    }


    function roundUpToMinuteInterval(\DateTime $dateTime, $minuteInterval = 30) {
        return $dateTime->setTime(
            $dateTime->format('H'),
            ceil($dateTime->format('i') / $minuteInterval) * $minuteInterval, 0
        );
    }

    function getjtime()
    {
        global $JSON;
       $i=0;
        $arrtmp = array();
       for($h=0;$h<25;$h++){
           if($h<10){
               $sh='0'.$h;
           }else{
               $sh=$h;
           }
           $arrtmp[$i]["value"]=$sh.":00";
           $arrtmp[$i]["label"]= $sh.":00";
           $i=$i+1;
           $arrtmp[$i]["value"]=$sh.":15";
           $arrtmp[$i]["label"]= $sh.":15";
           $i=$i+1;
           $arrtmp[$i]["value"]=$sh.":30";
           $arrtmp[$i]["label"]= $sh.":30";
           $i=$i+1;
           $arrtmp[$i]["value"]=$sh.":45";
           $arrtmp[$i]["label"]= $sh.":45";
           $i=$i+1;
       }
        return $arrtmp;
    }
    
    function getjtimeduracao()
    {
        /*
         * Alterado para aparecer até 6 horas. Caso precise de mais, tem a opção de marcar o dia todo.
         * Até 1 hora de 15 em 15 minutos, até as 2 de 30 em 30 e acima de 2, de 1 em 1
         * 30-01-2020 - Lidiane
         */
        global $JSON;
        $i=0;
        $arrtmp = array();
        for($h=0;$h<25;$h++){
            if($h<1){
                $arrtmp[$i]["label"]='0 min';
                $arrtmp[$i]["value"]='0'.$h.":00";
                $i=$i+1;
                $arrtmp[$i]["label"]='15 min';
                $arrtmp[$i]["value"]='0'.$h.":15";
                $i=$i+1;
                $arrtmp[$i]["label"]='30 min';
                $arrtmp[$i]["value"]='0'.$h.":30";
                $i=$i+1;
                $arrtmp[$i]["label"]='45 min';
                $arrtmp[$i]["value"]='0'.$h.":45";
                $i=$i+1;
            }elseif($h<2){
                $arrtmp[$i]["label"]=$h.' h';
                $arrtmp[$i]["value"]='0'.$h.":00";
                $i=$i+1;
                $arrtmp[$i]["label"]=$h.',5 h';
                $arrtmp[$i]["value"]='0'.$h.":30";
                $i=$i+1;
            }elseif($h<6){
                $arrtmp[$i]["label"]=$h.' h';
                $arrtmp[$i]["value"]='0'.$h.":00";
                $i=$i+1;
            }        
           
        }
         return $arrtmp;
    }

    function getJfuncionario() {
        
        global $JSON, $_1_u_evento_idevento;
        
        $sql = "SELECT a.idpessoa ,a.nomecurto    
                  FROM pessoa a
                 WHERE 1 ".getidempresa('a.idempresa','evento')."
                   AND a.status ='ATIVO'
                   AND a.idtipopessoa = 1
                   AND not idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]."
				   AND not exists(SELECT 1
                                    FROM fluxostatuspessoa r
                                   WHERE r.idmodulo = '".$_1_u_evento_idevento."' AND r.modulo = 'evento'
                                     AND r.tipoobjeto ='pessoa'
                                     AND a.idpessoa = r.idobjeto)
            ORDER BY a.nomecurto asc";

        $rts = d::b()->query($sql) or die("[model-evento] - getJfuncionario: ". mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"]=$r["idpessoa"];
            $arrtmp[$i]["label"]= $r["nomecurto"];
            $i++;
        }
        
        return $JSON->encode($arrtmp);    
    }

    //altera o status do evento e altera o evento conforme as configuracoes do status
    function atualizaEventoStatus($_idfluxostatuspessoa, $_idfluxostatus, $inocultar, $var_json = 'true', $voltarStatus = 'false', $fluxounico = 'N', $_idobjeto = NULL)
    {   
        $replace = "";
        if(empty($_idfluxostatuspessoa) or empty($_idfluxostatus) or empty($inocultar)){
            Die("Parametros necessários para alteração não enviados.");
        }

        $sl="select r.* from fluxostatuspessoa r 
            join evento e on e.idevento=r.idmodulo
            join carrimbo c on( c.idobjeto= e.idmodulo and c.tipoobjeto = e.modulo and c.status='PENDENTE' and c.idpessoa=r.idobjeto and r.tipoobjeto='pessoa')
            where assinar='Y' and r.idfluxostatuspessoa = ".$_idfluxostatuspessoa;

        $rl = d::b()->query($sl);
        $qtdpend=mysqli_num_rows($rl);
        if($qtdpend>0){
            //DIE('ASSPENDENTE');
            echo '<script language="javascript">';
            echo 'alert("Assinatura Pendente.")';
            echo '</script>';
        }

        $condicao = ($fluxounico == 'Y')  ? "e.idevento = $_idobjeto" : "r.idfluxostatuspessoa = $_idfluxostatuspessoa AND r.tipoobjeto ='pessoa'";

        //verificar se tem assinatura pendente para todos ou parcial
        //busca status do evento
        $sx="SELECT s.idstatus, 
                    e.ideventotipo, 
                    e.idevento, 
                    e.idpessoa, 
                    e.modulo, 
                    e.idmodulo, 
                    s.statustipo AS posicao
            FROM evento e JOIN fluxostatuspessoa r ON (e.idevento = r.idmodulo)
            JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
            JOIN eventotipo ev ON ev.ideventotipo = e.ideventotipo
            JOIN fluxostatus es ON (es.idfluxostatus = r.idfluxostatus AND es.idfluxo = ms.idfluxo)
            JOIN fluxostatus ese ON (ese.idfluxostatus = r.idfluxostatus AND es.idfluxo = ms.idfluxo)
       LEFT JOIN "._DBCARBON."._status s ON s.idstatus = ese.idstatus 
            WHERE $condicao
        ORDER BY es.ordem DESC
            LIMIT 1";
        $rx = d::b()->query($sx);
        $rwx=mysql_fetch_assoc($rx);
        
        /* 
        * Verifica se o usuário pode ser inserido no evento desde que o status do evento seja igual fluxoobjeto (inidstatus)
        */
        $sqlFuncionarios = "SELECT mfo.idobjeto, mfo.tipoobjeto
                            FROM fluxoobjeto mfo JOIN fluxo ms ON mfo.idfluxo = ms.idfluxo 
                            WHERE mfo.tipo = 'PARTICIPANTE' 
                            AND ms.idobjeto = '".$rwx['ideventotipo']."' AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                            ".getidempresa('idempresa','evento')."";	
        $resFuncionarios = d::b()->query($sqlFuncionarios) or die("Erro carregar participantes: ".mysql_error(d::b()));			
        $resultsetor = array();
        $resultpessoa = array();
        $i = 0;
        $j = 0;

        while($funcionarios = mysql_fetch_assoc($resFuncionarios)){
            if($funcionarios['tipoobjeto']=='imgrupo'){
                $resultsetor[$i++] = $funcionarios['idobjeto'];
            }elseif($funcionarios['tipoobjeto']=='pessoa'){
                $resultpessoa[$j++] = $funcionarios['idobjeto'];
            }
        }
        
        if ($resultsetor) {
            $resultsetor = implode(",", $resultsetor);
        } else {
            $resultsetor = "''";
        }
        
        if ($resultpessoa) {
            $resultpessoa = implode(",", $resultpessoa);
        } else {
            $resultpessoa = "''";
        }
        
        //Pega o status Atual do Evento 
        $sql = "SELECT s.statustipo
                    FROM evento e JOIN fluxostatus mf ON mf.idfluxostatus = e.idfluxostatus 
                    JOIN "._DBCARBON."._status s on s.idstatus = mf.idstatus
                    WHERE e.idevento = '".$rwx['idevento']."'";
        $res = d::b()->query($sql) or die("Erro ao buscar status Evento: ".mysqli_error(d::b()));
        $r = mysqli_fetch_assoc($res);
        $statustipo = $r['statustipo'];

        $nvidevento = traduzid('fluxostatuspessoa', 'idfluxostatuspessoa', 'idmodulo', $_idfluxostatuspessoa);

        $sqlNovoStatus ="SELECT cs.statustipo
                        FROM fluxostatus fs
                        JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
                        WHERE fs.idfluxostatus = $_idfluxostatus;";
        $resultNovoStatus = d::b()->query($sqlNovoStatus) or die("[model-evento] - Select Eventores:  ao verificar nessecidade de enviar uma nova mensagem:" . mysqli_error(d::b()) . "");
        $resultNovoStatusArr =mysqli_fetch_assoc($resultNovoStatus);

        $novoStatusTipo = $resultNovoStatusArr['statustipo'];
        
        //Retorna se o campo 'assinarevento (eventotipo)' ou 'assina (fluxoobjeto)' está setado para que quando clicar no botão setado no eventotipo como ASSINA ou REJEITA (LTM - 17/07/2020)
        $sql = "SELECT es.statustipo AS posicao, 
                       ms.assinar, 
                       mfo.assina
                  FROM eventotipo et JOIN fluxo ms ON et.ideventotipo = ms.idobjeto AND ms.modulo = 'evento' AND ms.status = 'ATIVO' AND et.ideventotipo = '".$rwx['ideventotipo']."' 
                  JOIN fluxostatus ets ON ms.idfluxo = ets.idfluxo 
                  JOIN "._DBCARBON."._status es ON ets.idstatus = es.idstatus	 
                 LEFT JOIN fluxoobjeto mfo ON ms.idfluxo = mfo.idfluxo AND mfo.idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' AND mfo.tipoobjeto = 'pessoa'
                 WHERE ets.idfluxostatus = '".$_idfluxostatus."' AND mfo.tipo = 'PARTICIPANTE';";

        $result = d::b()->query($sql);
        //die($sql);
        while($linha=mysql_fetch_assoc($result)){
            $posicao = $linha['posicao'];
            $assinar = $linha['assinar'];
            $assina = $linha['assina'];
        }

        if(($posicao == 'ASSINA' && $assina != NULL) or ($posicao == 'ASSINA' && $assinar != NULL)){
            $tabela = getModuloTab($rwx['modulo']);
            $su="UPDATE carrimbo SET status = 'ASSINADO', alteradoem = now(), alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."' WHERE idpessoa = '".$_SESSION["SESSAO"]["IDPESSOA"]."' and idobjeto = '".$rwx['idmodulo']."' and tipoobjeto IN ('".$rwx['modulo']."', '$tabela') and status = 'PENDENTE'";
            //die($su);
            $ru=d::b()->query($su) or die("[model-evento] - : erro ao assinar modulo vinculado do evento:" . mysqli_error(d::b()) . "");

            $suEvento="UPDATE carrimbo SET status = 'ASSINADO', alteradoem = now(), alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."' WHERE idpessoa = '".$_SESSION["SESSAO"]["IDPESSOA"]."' and idobjeto = '".$rwx['idevento']."' and tipoobjeto ='evento' and status = 'PENDENTE'";
            //die($su);
            d::b()->query($suEvento) or die("[model-evento] - : erro ao assinar do evento:" . mysqli_error(d::b()) . "");

            echo 'RetiraBotaoAssinar'; //Retorno para retirar o Botão Assinatura (LTM - 15-07-2020)
        }else if(($posicao=='REJEITA' && $assina != NULL) or ($posicao == 'REJEITA' && $assinar != NULL)){
            $su="  UPDATE carrimbo set status = 'CANCELADO' WHERE idpessoa = '".$_SESSION["SESSAO"]["IDPESSOA"]."' and idobjeto = '".$rwx['idmodulo']."' and tipoobjeto ='".$rwx['modulo']."' and status = 'PENDENTE'";
            //die($su);
            $ru=d::b()->query($su) or die("[model-evento] - : erro ao cancelar assinatura do modulo vinculado do evento:" . mysqli_error(d::b()) . "");

            $suEvento="UPDATE carrimbo SET status = 'CANCELADO' WHERE idpessoa = '".$_SESSION["SESSAO"]["IDPESSOA"]."' and idobjeto = '".$rwx['idevento']."' and tipoobjeto ='evento' and status = 'PENDENTE'";
            //die($su);
            d::b()->query($suEvento) or die("[model-evento] - : erro ao cancelar assinatura do evento:" . mysqli_error(d::b()) . "");

            echo 'RetiraBotaoAssinar'; //Retorno para retirar o Botão Assinatura (LTM - 15-07-2020)
        }

        //Busca as pessoas Ativas que estão no Evento.
        $sqlPessoa = sqlPessoa($resultpessoa);	
        $resPessoa = d::b()->query($sqlPessoa) or die("Erro ao carregar configuracao de Pessoa: ".mysqli_error(d::b()));

        //Alterado para chamar a função que está no arquivo [model-evento] - . Busca os grupos Ativos que estão no Evento.
        $sqlImGrupo = sqlImGrupo($resultsetor);
        $resImGrupo = d::b()->query($sqlImGrupo) or die("Erro ao carregar configuracao do Setor: ".mysqli_error(d::b()));

        $arrDestNotif = [];

        while($r = mysql_fetch_assoc($resPessoa)) {
            //Sql para buscar o inidstatus que foi setado no evento para inserir a pessoa a partir dessa permissão
            $sqlFuncEvento = sqlEventoTipoResp($rwx['ideventotipo'], $r["idpessoa"], $_SESSION["SESSAO"]["IDEMPRESA"]);
            $resFuncEvento = d::b()->query($sqlFuncEvento) or die("Erro carregar validação status pessoa: ".mysqli_error(d::b()));
            $rFuncEvento = mysql_fetch_assoc($resFuncEvento);

            if(in_array($_idfluxostatus, explode(',', $rFuncEvento['inidstatus'])) || ($rFuncEvento['inidstatus'] == NULL && ($posicao != 'CANCELADO' && $posicao != 'FIM' && $posicao != 'CONCLUIDO'))){
                //Valida se o usuario já foi inserido
                $sqlQtdePessoa = "SELECT idobjeto AS contador FROM fluxostatuspessoa WHERE idmodulo = ".$rwx['idevento']."  AND modulo = 'evento' AND idobjeto = ".$r['idpessoa']."";
                $resQtdePessoa = d::b()->query($sqlQtdePessoa) or die("Erro carregar validação status pessoa: ".mysqli_error(d::b()));
                $qtdpoessoa = mysql_num_rows($resQtdePessoa);

                //if($qtdpoessoa == 0 && ($retEventoAss == 0 || ($retEventoAss == 1 && $retCarAss == 1))){
                //Retirada a parte em que valida se tem ou não assinatura
                if($qtdpoessoa == 0 and ($rwx['posicao'] != 'CANCELADO' || $rwx['posicao'] != 'FIM' || $rwx['posicao'] != 'CONCLUIDO')){
                    if($novoStatusTipo != 'CANCELADO' && $novoStatusTipo != 'FIM' && $novoStatusTipo != 'CONCLUIDO'){
                        if($rFuncEvento['inidstatus'] == NULL){
                            $idfluxostatus = $this->getTokenInicial($rwx['idevento']);
                        } else {
                            $idfluxostatus = $_idfluxostatus;
                        }
                        $this->inserefluxostatuspessoa($rwx['idevento'], $r["idempresa"], $r["idpessoa"], 'pessoa', $idfluxostatus, $rwx['idpessoa'], 'insereParticipantes');
                        $replace = 'Y';

                        if($r["idpessoa"] != $rwx['idpessoa'])
                            $arrDestNotif[] = $r["idpessoa"];
                    }                    
                }
            }
        }

        if(count( $arrDestNotif ) > 0)
            $this->notificacaoEvento($rwx['idevento'], $arrDestNotif);

        while($r = mysqli_fetch_assoc($resImGrupo)) {
            //Sql para buscar o inidstatus que foi setado no evento para inserir a grupo a partir dessa permissão. Busca inidstatus para validar a inserção no fluxostatuspessoa.
            $sqlFuncEvento = sqlEventoTipoResp($rwx['ideventotipo'], $r["idimgrupo"], $_SESSION["SESSAO"]["IDEMPRESA"]);
            $resFuncEvento = d::b()->query($sqlFuncEvento) or die("Erro carregar validação status grupo: ".mysqli_error(d::b()));
            $rFuncEvento = mysqli_fetch_assoc($resFuncEvento);
            
            if(in_array($_idfluxostatus, explode(',', $rFuncEvento['inidstatus'])) || $rFuncEvento['inidstatus'] == NULL){
                //Valida se o usuario já foi inserido
                $sqlQtdeGrupo = "SELECT idobjeto AS contador FROM fluxostatuspessoa WHERE idmodulo = ".$rwx['idevento']." AND modulo = 'evento' AND idobjeto = ".$r['idimgrupo']."";
                $resQtdeGrupo = d::b()->query($sqlQtdeGrupo) or die("Erro carregar validação status pessoa: ".mysqli_error(d::b()));
                $qtdQtdeGrupo = mysqli_num_rows($resQtdeGrupo);
                if($qtdQtdeGrupo == 0 && $novoStatusTipo != 'CANCELADO' && $novoStatusTipo != 'FIM' && $novoStatusTipo != 'CONCLUIDO'){
                    if($rFuncEvento['inidstatus'] == NULL){
                        $idfluxostatus = $this->getTokenInicial($rwx['idevento']);
                    } else {
                        $idfluxostatus = $_idfluxostatus;
                    }
                    $this->inserefluxostatuspessoa($rwx['idevento'], $r["idempresa"], $r["idimgrupo"], 'imgrupo', $idfluxostatus, $rwx['idpessoa']);
                    $replace = 'Y';

                    //Insere as Pessoas do Grupo para inserir no FluxoStatusHist
                    $this->insereGrupoFluxoStatusPessoa($rwx['idevento'], $_idfluxostatus, $r["idimgrupo"]);                   
                }
            }
        }

        if($replace == 'Y' && $statustipo != 'CANCELADO' && $statustipo != 'FIM' && $statustipo != 'CONCLUIDO')
        {	           
            //Alterado para chamar a função que está no arquivo [model-evento] - . . Replace para inserir as pessoas do Grupo no Evento
            replaceEvento($rwx['idevento'], $_idfluxostatus);         
        }
        /* 
        * Fim Validação Insert Evento.
        */
        
        $condicao = ($fluxounico == 'Y')  ? "e.idevento = ".$rwx['idevento'] : "re.idfluxostatuspessoa = $_idfluxostatuspessoa AND re.tipoobjeto ='pessoa'";
        //buscar configuração tipoevento
        $sql ="SELECT t.ideventotipo, 
                  ms.assinar AS assinarevento,
                  ms.inidstatus,
                  r.inidstatus AS inideventostatustr,
                  r.assina,
                  e.idevento, 
                  e.modulo, 
                  e.idmodulo, 
                  es.statustipo AS modposicao
             FROM fluxostatuspessoa re
             JOIN evento e ON e.idevento = re.idmodulo AND re.modulo = 'evento'
        LEFT JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
        LEFT JOIN fluxoobjeto r ON r.idfluxo = ms.idfluxo AND r.tipo = 'PARTICIPANTE'
             JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
             JOIN fluxostatus fs ON fs.idfluxo = ms.idfluxo AND re.idfluxostatus = fs.idfluxostatus
             JOIN carbonnovo._status es ON es.idstatus = fs.idstatus	
            WHERE $condicao
         GROUP BY e.idevento";
        $res = d::b()->query($sql);

        while($row=mysqli_fetch_assoc($res)){
            $ideventotipo = $row['ideventotipo']; 
            $modevento = $row['modulo']; 
            $idmodevento = $row['idmodulo'];
            $assinar='N';
            $varassina='';
            $arrayStatus=array();
            if(!empty($row['assina']) and !empty($row['inidstatus'])){             
                $arrayStatus = explode(",", $row['inidstatus']);
                $key = array_search($rwx['idstatus'], $arrayStatus);
                //print_r($arrayOcultar);
                if($key!==false){ 
                    $assinar='Y';
                    $varassina=$row['assina'];
                }

            }elseif(!empty($row['assinarevento']) and !empty($row['inidstatus'])){
                $arrayStatus = explode(",", $row['inidstatus']);          
                $key = array_search($rwx['idstatus'], $arrayStatus);
                //print_r($arrayOcultar);
                if($key!==false){
                    $assinar='Y';
                    $varassina=$row['assinarevento'];
                }            
            }

            if($assinar == 'Y' and !empty($varassina)){
                if($varassina == 'TODOS'){
                    $sqls = "SELECT e.modulo, e.idmodulo 
                            FROM evento e 
                            JOIN carrimbo c 
                            on c.idobjeto = e.idmodulo 
                            and e.modulo = c.tipoobjeto                          
                            and c.status = 'PENDENTE' 
                            WHERE idevento = ".$row['idevento'];
                }else{
                    $sqls = "SELECT e.modulo, e.idmodulo 
                            FROM evento e 
                            JOIN carrimbo c 
                            on c.idobjeto = e.idmodulo 
                            and e.modulo = c.tipoobjeto                          
                            and c.status IN ('ATIVO', 'CANCELADO')
                            WHERE idevento = ".$row['idevento'];
                }

                $ress = d::b()->query($sqls);

                $total = mysqli_num_rows($ress); 

                if($total > 0 and $varassina=='TODOS'){
                        die('ERROASSTODOS') ;
                        return;
                }elseif($total < 1 and $varassina=='PARCIAL'){
                        die('ERROASSPARCIAL') ;
                        return;
                }
            }
        }

        if($inocultar=='Y'){
            $oculto = '1';
        }else{
            $oculto = '0';
        }

        if($fluxounico == 'Y'){
            $ocultarFluxo = traduzid("fluxostatus", 'idfluxostatus', 'ocultar', $_idfluxostatus);
            //Altera o status de todas as pessoas do evento
            if($ocultarFluxo == 'N'){
                $sqlu = "UPDATE fluxostatuspessoa 
                            SET idfluxostatus = ".$_idfluxostatus.",
                                alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."',
                                alteradoem = now()
                         WHERE idmodulo = ".$rwx['idevento']." AND modulo = 'evento'";
            } else {
                $sqlu = "UPDATE fluxostatuspessoa 
                            SET oculto = 1,
                                alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."',
                                alteradoem = now()
                         WHERE idfluxostatuspessoa = $_idfluxostatuspessoa";
            }
        } else {
            //altera o status do usuario
            $sqlu="UPDATE fluxostatuspessoa 
                    SET oculto ='".$oculto."',
                        idfluxostatus = ".$_idfluxostatus.",
                        alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."',
                        alteradoem = now()
                WHERE idfluxostatuspessoa = ".$_idfluxostatuspessoa;
        }

        $resu = d::b()->query($sqlu) or die("[model-evento] -  fluxostatuspessoa  : falha ao atualizar o status do usuario:" . mysqli_error(d::b()) . "<br />" .$sqlu);

        $idStatusAtual = traduzid("fluxostatus", 'idfluxostatus', 'idstatus', $_idfluxostatus);
        $descricao = traduzid(_DBCARBON."._status", 'idstatus', 'rotuloresp', $idStatusAtual);
        
        //buscar se o evento e novamensagem, só insere log se for novamensagem =Y
        //coloca a mensagem como não visualizada novamente
        
        $sqm="select novamensagem 
                from fluxo ms JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo
            where ms.idobjeto = ".$rwx['ideventotipo']." AND modulo = 'evento'
            and idfluxostatus=".$_idfluxostatus; //die($sqm);
      
        $resm = d::b()->query($sqm) or die("[model-evento] -  eventonovamensagem: buscar se e novamensagem:" . mysqli_error(d::b()) . "");
        $rowm=mysqli_fetch_assoc($resm);
      
        //if($historico == 'null'){
            //Insere as informações a fim de ter um histórico dos processos para caso necessite voltar um status. Lidiane - 09-03-2020
            $this->insereInicio($_idfluxostatuspessoa, $_idfluxostatus, $rwx['idevento']);
        //}
        
        if($rowm['novamensagem']=='Y'){
            //die($nvidevento);
            // insere a mensagem de alteração de status do usuario
            // GVT - 26/01/2022 - Comentado por apresentar problemas com o CMD quando o módulo não é migrado para C9
            /*$_CMD = new cmd();

            $res = $_CMD->save(array(
                "_modelevento_i_modulocom_idmodulo"         => $nvidevento,
                "_modelevento_i_modulocom_modulo"           => 'evento',
                "_modelevento_i_modulocom_descricao"        => $descricao,
                "_modelevento_i_modulocom_status"           => 'ATIVO',
            ));

            if(!$res){
                die($_CMD->erro);
            }*/

            $intabela= new Insert();
            $intabela->setTable("modulocom");
            $intabela->idempresa    = $_SESSION["SESSAO"]["IDEMPRESA"];
            $intabela->descricao     = $descricao;
            $intabela->idmodulo   = $nvidevento;     
            $intabela->modulo   = 'evento'; 
            $idtabela=$intabela->save();

            
            // coloca como visualizado nao
           $su="UPDATE fluxostatuspessoa r, fluxostatuspessoa r2 SET r2.visualizado = '0'
                  WHERE r.idfluxostatuspessoa = ".$_idfluxostatuspessoa." 
                    and r2.idmodulo = r.idmodulo AND r.modulo = 'evento'
                    and r2.tipoobjeto = 'pessoa'
                    and r2.idfluxostatuspessoa != ".$_idfluxostatuspessoa;
            $ru=d::b()->query($su) or die("[model-evento] - update visualizado fluxostatuspessoa" . mysqli_error(d::b()) . "");
        }
    
        $sqlx="SELECT mf.idfluxostatus,
                      e.idevento,
                      s.statustipo AS posicao,
                      mf.ordem
                 FROM fluxostatuspessoa r JOIN evento e ON e.idevento = r.idmodulo AND r.modulo = 'evento'
                 JOIN fluxo ms JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo AND ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO' AND r.idfluxostatus=mf.idfluxostatus
                 JOIN "._DBCARBON."._status s ON s.idstatus = mf.idstatus
                WHERE r.idmodulo = ".$nvidevento."
                ORDER BY mf.ordem DESC
                LIMIT 1";
        $recx=d::b()->query($sqlx) or die("[model-evento] - SelectaEventores:  ao verificar nessecidade de enviar uma nova mensagem:" . mysqli_error(d::b()) . "");
        $rocx=mysqli_fetch_assoc($recx);

        if(!empty($rocx['idevento']) and !empty($rocx['idfluxostatus'])) 
        {
            //Atualiza o Evento
            if($rocx['posicao'] == 'CANCELADO' || $rocx['posicao'] == 'FIM' || $rocx['posicao'] == 'CONCLUIDO'){ $strst=", status='CONCLUIDO' "; } else { $strst=", status='".$rocx['posicao']."'"; }
            $su = "UPDATE evento SET idfluxostatus = '".$rocx['idfluxostatus']."', ordem = 99999 ".$strst." WHERE idevento = ".$rocx['idevento'];
            d::b()->query($su) or die("[model-evento] - : erro ao atualizar status do evento:" . mysqli_error(d::b()) . "");
            
            //Desativa as etapas a paritr daquela que foi restaurada e insere a etapa restaurada como PENDENTE
            if($voltarStatus == 'true' && $rocx['posicao'] != 'CANCELADO' && $rocx['posicao'] != 'FIM' && $rocx['posicao'] != 'CONCLUIDO'){
                $sqlxHist = "SELECT idfluxostatushist, criadoem
                           FROM fluxostatushist
                          WHERE idfluxostatus = '".$rocx['idfluxostatus']."' AND idmodulo = '".$rocx['idevento']."' AND modulo = 'evento'";

                $recxHist = d::b()->query($sqlxHist) or die("[model-evento] - SelectvoltarStatus: " . mysqli_error(d::b()) . "");
                $rocxHIst = mysqli_fetch_assoc($recxHist);

                $su = "UPDATE fluxostatushist SET status = 'PENDENTE' WHERE idmodulo = '".$rocx['idevento']."' AND modulo = 'evento' AND idfluxostatus = '".$rocx['idfluxostatus']."'";
                d::b()->query($su) or die("[model-evento] - : erro ao atualizar status do evento:" . mysqli_error(d::b()) . "");

               $sqlFluxo = "UPDATE evento t JOIN fluxostatushist fh ON t.idevento = fh.idmodulo AND fh.modulo = 'evento' AND fh.idmodulo = '".$rocx['idevento']."'
                                SET t.idfluxostatus = '".$rocx['idfluxostatus']."', t.alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."', t.alteradoem = sysdate(), fh.alteradoem = sysdate(), fh.status = 'INATIVO'
                              WHERE fh.criadoem > '".$rocxHIst['criadoem']."'";
                d::b()->query($sqlFluxo) or die("[model-evento]: Erro ao alterar Etapas:" . mysqli_error(d::b()) . ""); 
            }            
        }
        
        //CARREGAR OS BOTÕES E DEPOIS ENVIÁ-LOS PARA DENTRO DO MENU LATERAL RÁPIDO.
        $resb = $this->getBotoes($_idfluxostatuspessoa, 'botao_menu_lateral');
        $sep = '';
        $fluxo = '';
        $virgula = '';
        $i = 0;
        while($rowb=mysqli_fetch_assoc($resb))
        { 
            $status = '{"status":{"idevento":"'.$rowb['idevento'].'","idstatus":"'.$rowb['idstatus'].'","corstatus":"'.$rowb['corstatus'].'","cortextostatus":"'.$rowb['cortextostatus'].'","rotulo":"'.$rowb['rotulo'].'","ocultar":"'.$rowb['ocultar'].'"},';
            $statusresp = '"statusresp":{"idstatus":"'.$rowb['idstatusresp'].'","corstatus":"'.$rowb['corstatusresp'].'","rotulo":"'.$rowb['rotuloresp'].'","oculto":"'.$rowb['oculto'].'"},';
            if (($rowb['botaocriador'] == 'Y' and $rowb['criadopor'] == $_SESSION["SESSAO"]["USUARIO"]) OR ($rowb['botaocriador'] != 'Y')){
                $fluxo .= $virgula.'"'.$i.'":{ "botao":"'.$sep.$rowb['botao'].'","cor":"'.$rowb['cor'].'","cortexto":"'.$rowb['cortexto'].'","idstatus":"'.$rowb['idstatusf'].'","idfluxostatuspessoa":"'.$rowb['idfluxostatuspessoa'].'","ocultar":"'.$rowb['ocultar'].'"}';
                $virgula = ',';
                $i++;
            }
        }
        $fluxo .= "}";
        if($var_json!='false'){
            echo $status.$statusresp.'"fluxo":{'.$fluxo.'}';
        }

        //INSERIR ASSINATURAS DO TIPO EVENTO
        //busca status do evento
        $sx="SELECT s.idstatus
            FROM evento e JOIN fluxostatuspessoa r ON e.idevento = r.idmodulo
            JOIN fluxo ms ON e.ideventotipo = ms.idobjeto AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
            JOIN fluxostatus es ON (es.idfluxostatus = r.idfluxostatus AND es.idfluxo = ms.idfluxo)
            JOIN "._DBCARBON."._status s ON s.idstatus = es.idstatus
            WHERE r.idfluxostatuspessoa = ".$_idfluxostatuspessoa." AND r.tipoobjeto = 'pessoa'
        ORDER BY es.ordem DESC
            LIMIT 1";
            $rx = d::b()->query($sx);
            $rwx=mysqli_fetch_assoc($rx );       
                  
            //buscar configuração tipoevento
        $sql ="SELECT ms.assinar AS assinarevento,
                    ms.inidstatus,
                    mfo.inidstatus AS inidstatustr,
                    mfo.assina,
                    e.idevento,
                    re.idobjeto,
                    e.modulo,
                    e.idmodulo,
                    mfo.tipo
                FROM fluxostatuspessoa re
                JOIN evento e ON e.idevento = re.idmodulo AND re.modulo = 'evento'
                JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
           LEFT JOIN fluxoobjeto mfo ON ms.idfluxo = mfo.idfluxo AND mfo.idobjeto = re.idobjeto 
                 AND mfo.tipoobjeto = re.tipoobjeto AND mfo.tipoobjeto = 'pessoa'
                JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
               WHERE re.idfluxostatuspessoa = ".$_idfluxostatuspessoa."
                 AND re.tipoobjeto = 'pessoa'";
        $res = d::b()->query($sql);

        while($row=mysqli_fetch_assoc($res)){
            $assinar='N';
            $varassina='';
            $arrayStatus=array();
    
            if(!empty($row['assina']) and !empty($row['inidstatustr'])){             
                $arrayStatus = explode(",", $row['inidstatustr']);
                $key = array_search($rwx['idstatus'], $arrayStatus);
        
                if($key!==false){ 
    
                    if($row['assina']=='TODOS'){
                        
                        $sql = "SELECT r.idobjeto,
                                        e.modulo, 
                                        e.idmodulo
                                    FROM fluxostatuspessoa r JOIN evento e ON e.idevento = r.idmodulo AND r.modulo = 'evento' 
                                    JOIN eventotipo et on et.ideventotipo = e.ideventotipo
                                    WHERE r.tipoobjeto = 'pessoa' and r.idmodulo =".$row['idevento'];

                        $res = d::b()->query($sql);

                        while ($r = mysqli_fetch_assoc($res)) {                                
                            criaAssinatura($r["idobjeto"], $r["modulo"], $r["idmodulo"]);                             
                        }
                        break;
                    }elseif($row['assina']=='PARCIAL' OR $row['assina']=='INDIVIDUAL'){
                        criaAssinatura($row["idobjeto"], $row["modulo"], $row["idmodulo"]);                          
                    }
                    
                }

            }elseif(!empty($row['assinarevento']) and !empty($row['inidstatus'])){
                $arrayStatus = explode(",", $row['inidstatus']);          
                $key = array_search($rwx['idstatus'], $arrayStatus);

                if($key!==false){ 

                    if($row['assinarevento']=='TODOS'){
                        $sql = "SELECT r.idobjeto,
                                        e.modulo, 
                                        e.idmodulo
                                FROM fluxostatuspessoa r JOIN  evento e on e.idevento = r.idmodulo AND r.modulo = 'evento'
                                JOIN eventotipo et on et.ideventotipo = e.ideventotipo
                                WHERE r.tipoobjeto = 'pessoa' and r.idmodulo =".$row['idevento'];

                        $res = d::b()->query($sql);

                        while ($r = mysqli_fetch_assoc($res)) {                                
                            criaAssinatura($r["idobjeto"], $r["modulo"], $r["idmodulo"]);                             
                        }
                        break;
                        
                    }elseif($row['assinarevento']=='PARCIAL' or $row['assinarevento']=='INDIVIDUAL'){
                        criaAssinatura($row["idobjeto"], $row["modulo"], $row["idmodulo"]);                             
                    }
                }            
            }
        }
    }  

    function inserefluxostatuspessoa($idevento, $idempresa, $idpessoa, $tipo, $tokeninicial, $idcriador, $rec = NULL, $idobjetoext = NULL, $tipodobjetoext = NULL)
    {
        if(!empty($idobjetoext) && !empty($tipodobjetoext)){
            $campo = 'idobjetoext, tipoobjetoext,';
            $inscampo = "$idobjetoext, '$tipodobjetoext',";
        }

        $sql1 = "INSERT INTO fluxostatuspessoa (idmodulo, modulo, idempresa, idobjeto, tipoobjeto, $campo  idfluxostatus, idpessoa, criadopor, criadoem, alteradopor, alteradoem) 
                VALUES (".$idevento.", 'evento', ".$idempresa.", ".$idpessoa.",  '".$tipo."', $inscampo '".$tokeninicial."', '".$idcriador."', '".$_SESSION["SESSAO"]["USUARIO"]."', now(), '".$_SESSION["SESSAO"]["USUARIO"]."', now());";
        d::b()->query($sql1) or die("[model-evento] - inserefluxostatuspessoa:" . mysqli_error(d::b()) . $sql1); 
        
        if($rec == 'insereParticipantes'){
            $idfluxostatuspessoa = mysqli_insert_id(d::b());
            $this->insereInicio($idfluxostatuspessoa, $tokeninicial, $idevento);
        }

        // GVT - 26/01/2022 - Comentado por apresentar problemas com o CMD quando o módulo não é migrado para C9
        /*$_CMD = new cmd();
        $_CMD->disablePrePosChange = true;
        $changeGet = false;

        if(empty($_GET["_modulo"])){
            $_GET["_modulo"] = 'evento';
            $changeGet = true;
        }

        $arrInsertFluxoStatusPessoa = array(
            "_evento_i_fluxostatuspessoa_idmodulo"      => $idevento,
            "_evento_i_fluxostatuspessoa_modulo"        => 'evento',
            "_evento_i_fluxostatuspessoa_idempresa"     => $idempresa,
            "_evento_i_fluxostatuspessoa_idobjeto"      => $idpessoa,
            "_evento_i_fluxostatuspessoa_tipoobjeto"    => $tipo,
            "_evento_i_fluxostatuspessoa_idfluxostatus" => $tokeninicial,
            "_evento_i_fluxostatuspessoa_idpessoa"      => $idcriador
        );
        
        if(!empty($idobjetoext) && !empty($tipodobjetoext)){
            $arrInsertFluxoStatusPessoa["_evento_i_fluxostatuspessoa_idobjetoext"] = $idobjetoext;
            $arrInsertFluxoStatusPessoa["_evento_i_fluxostatuspessoa_tipoobjetoext"] = $tipodobjetoext;
        }

        $res = $_CMD->save($arrInsertFluxoStatusPessoa);
        if(!$res){
            die($_CMD->erro);
        }
        
        if($rec == 'insereParticipantes'){
            $idfluxostatuspessoa = $_CMD->insertid();
            $this->insereInicio($idfluxostatuspessoa, $tokeninicial, $idevento);
        }

        $_CMD = null;

        if($changeGet) unset($_GET["_modulo"]);*/
    }

    function getEventoTipoByIdEvento( $idevento ) {
        if(empty($idevento))
            return '';

        $qr = "SELECT t.eventotipo 
                FROM eventotipo t 
                WHERE 
                    EXISTS (
                        SELECT 1 
                        FROM evento e 
                        WHERE 
                            t.ideventotipo = e.ideventotipo 
                            AND e.idevento = ".$idevento."
                        )";
        $rs = d::b()->query($qr);
        if(!$rs){
            return '';
        }else{
            $rw = mysqli_fetch_assoc($rs);
            return $rw["eventotipo"];
        }
    }

    function getEventoTituloByIdEvento( $idevento ) {
        if(empty($idevento))
            return '';

        $qr = "SELECT 
                e.evento 
            FROM 
                evento e 
            WHERE 
                e.idevento = ".$idevento;
        $rs = d::b()->query($qr);
        if(!$rs){
            return '';
        }else{
            $rw = mysqli_fetch_assoc($rs);
            return $rw["evento"];
        }
    }

    function insereGrupoFluxoStatusPessoa($idevento, $tokeninicial, $idimgrupo)
    {
        //Insere as Pessoas do Grupo para inserir no FluxoStatusHist
        $slqHist = "SELECT gp.idempresa,                
                            gp.idpessoa
                        FROM imgrupopessoa gp LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
                        WHERE gp.idimgrupo = '".$idimgrupo."' 
                        AND gp.idpessoa NOT IN (SELECT idobjeto FROM fluxostatuspessoa 
                                                WHERE idobjeto = gp.idpessoa 
                                                    AND tipoobjeto = 'pessoa' AND idmodulo = '".$idevento."' AND modulo = 'evento')";
        $resHist = d::b()->query($slqHist) or die("Erro carregar validação status grupo: ".mysqli_error(d::b()));
        $arrDestNotif = [];
        while($rowHist = mysqli_fetch_assoc($resHist))
        {
            $this->inserefluxostatuspessoa($idevento, $rowHist["idempresa"], $rowHist["idpessoa"], 'pessoa', $tokeninicial, $_SESSION["SESSAO"]["IDPESSOA"], 'insereParticipantes', $idimgrupo, 'imgrupo');
            
            if($rowHist["idpessoa"] != $_SESSION["SESSAO"]["IDPESSOA"])
                $arrDestNotif[] = $rowHist["idpessoa"];
        }

        if(count( $arrDestNotif ) > 0)
            $this->notificacaoEvento($idevento, $arrDestNotif);
    }
 
}
?>

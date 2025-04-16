<?

class ImMsgConfQuery
{
     public static function buscarImMsgConfComTipoDiferenteDe()
     {
          return "SELECT idimmsgconf,titulo FROM immsgconf c WHERE status='ATIVO' AND tipo NOT IN(?tipo?) ORDER BY titulo";
     }

     public static function atualizarAlertasParaProcessando()
     {
          return "UPDATE
                         immsgconf ic 
                    set 
                         statusprocesso = 'PROCESSANDO', 
                         sessionid = '?sessionid?' 
                    where 
                         tipo not in('E','ET','EP')
                         and statusprocesso = 'ABERTO'
                    --	 and ic.idimmsgconf in (1)
                         and status='ATIVO';";
     }

     public static function atualizarAlertasParaAberto()
     {
          return "UPDATE immsgconf ic
                         set statusprocesso = 'ABERTO'
                    where
                         tipo not in('E','ET','EP')
                         and statusprocesso = 'PROCESSANDO'
                         and status='ATIVO'";
     }

     public static function buscarConfiguracoesDoEnvioDeMensagem()
     {
          return "SELECT 
                    ifnull(if(trim(ic.tabela) = '', null, ic.tabela), m.tab) as tab,
                    if (m.modulo = 'tarefaacumulada', 'pessoa',m.modulo) as modulo,
                    m.rotulomenu,tc.col,ic.idimmsgconf,ic.titulo,ic.tipo,ic.ideventotipo,ic.code,ic.mensagem,ic.titulocurto,ic.apartirde,ic.multiplo,
                    DATE_FORMAT(CASE
                    WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Minute' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MINUTE)
                    WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Hour' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) HOUR)
                    WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Year' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) YEAR)
                    WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Month' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MONTH)
                    WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Day' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) DAY)
                    WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Week' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) WEEK) 
                    END , '%Y-%m-%d') as prazo,
                    et.assinar as assinar,
                    ets.idfluxostatus AS idfluxostatus,
                    ic.idempresa
               FROM immsgconf ic
                    JOIN carbonnovo._modulo m ON m.modulo = ic.modulo
                    JOIN carbonnovo._mtotabcol tc on (tc.tab = m.tab or ic.tabela = tc.tab) and  tc.primkey ='Y'
                    LEFT JOIN eventotipo et on et.ideventotipo = ic.ideventotipo
                    LEFT JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.tipoobjeto = 'ideventotipo'
                    LEFT JOIN fluxostatus ets ON ets.idfluxo = ms.idfluxo 
                    JOIN carbonnovo._status s ON s.idstatus = ets.idstatus and s.statustipo = 'INICIO' 
               WHERE ic.tipo = 'T'
                    and ic.status='ATIVO'
                    and ic.statusprocesso = 'PROCESSANDO'
                    and ic.sessionid = '?sessionid?'
                    and exists (select 1 from immsgconffiltros f where f.valor!=' ' and f.valor is not null and f.idimmsgconf = ic.idimmsgconf)";
     }

     public static function buscarConfiguracoesDaRemocaoDeMensagem()
     {
          return "SELECT 
                         ifnull(if(trim(ic.tabela) = '', null, ic.tabela), m.tab) as tab,
                         if (m.modulo = 'tarefaacumulada', 'pessoa',m.modulo) as modulo,
                         m.rotulomenu,tc.col,ic.idimmsgconf,ic.titulo,ic.tipo,ic.ideventotipo,ic.code,ic.mensagem,ic.titulocurto,ic.apartirde,ic.multiplo,
                         DATE_FORMAT(CASE
                         WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Minute' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MINUTE)
                         WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Hour' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) HOUR)
                         WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Year' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) YEAR)
                         WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Month' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MONTH)
                         WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Day' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) DAY)
                         WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Week' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) WEEK) 
                         END , '%Y-%m-%d') as prazo,
                         ms.assinar as assinar,
                         ets.idfluxostatus as idfluxostatus
                    FROM immsgconf ic
                         JOIN carbonnovo._modulo m ON m.modulo = ic.modulo
                         JOIN carbonnovo._mtotabcol tc on (tc.tab = m.tab or ic.tabela = tc.tab) and  tc.primkey ='Y'
                         JOIN fluxo ms ON ms.idobjeto = ic.ideventotipo AND ms.tipoobjeto = 'ideventotipo'
                         LEFT JOIN fluxostatus ets on ets.idfluxo = ms.idfluxo 
                         JOIN carbonnovo._status s on ets.idstatus = s.idstatus and s.statustipo IN ('FIM', 'CANCELADO', 'CONCLUIDO')
                    where              
                         ic.tipo not in('E','ET','EP')
                    --	and ic.idimmsgconf = 1
                         and ic.status='ATIVO'
                         and exists (select 1 from immsgconffiltros f where f.valor!=' ' and f.valor is not null and f.idimmsgconf = ic.idimmsgconf)
                    group by ic.idimmsgconf	
                    order by ic.idimmsgconf";
     }

     public static function buscarSelectDoAlerta()
     {
          return "SELECT distinct a.?col? AS idpk, 1029 as idpessoa
          FROM ?tab? a 
          WHERE ?clausula?
               AND a.alteradoem >= '?apartirde?'
               AND NOT EXISTS( SELECT 1 
                    FROM immsgconflog l
                    JOIN immsgconf m on m.idimmsgconf = l.idimmsgconf
                    WHERE l.idpk = a.?col?
                    AND l.modulo = '?modulo?'
                    AND l.idimmsgconf = ?idimmsgconf?
                    AND CASE
                           WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Minute' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) MINUTE)
                           WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Hour' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) HOUR)
                           WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Year' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) YEAR)
                           WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Month' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) MONTH)
                           WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Day' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) DAY)
                           WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Week' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) WEEK) 
                         END > NOW())";
     }

     public static function buscarEventoAlerta()
     {
          return "SELECT 
                    e.idevento, 
                    ets.idfluxostatus as idfluxostatus
               FROM 
                    evento e
               JOIN 	
                    eventoobj eo on eo.idevento = e.idevento and eo.objeto = 'immsgconf' and idobjeto = '?idimmsgconf?'
                    LEFT JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo'
                    LEFT JOIN fluxostatus ets ON ets.idfluxo = ms.idfluxo 
                    JOIN carbonnovo._status s ON s.idstatus = ets.idstatus AND s.statustipo = 'INICIO' 
               WHERE 
                    e.ideventotipo = '?ideventotipo?' and 
                    e.modulo = '?modulo?' AND
                    e.idmodulo = '?idpk?' and 
                    e.criadopor = 'immsgconf' 
               order by 
                    e.idevento desc 
               limit 1";
     }

     public static function buscarEventosCriadosPelaConfiguracao()
     {
          return "SELECT distinct 
                         e.idevento as idevento,
                         e.idmodulo,
                         (select idfluxostatus from fluxo ms 
                         JOIN fluxostatus ets1 on ets1.idfluxo = ms.idfluxo 
                         JOIN carbonnovo._status s1 on ets1.idstatus = s1.idstatus  and s1.statustipo IN ('FIM', 'CANCELADO', 'CONCLUIDO')
                    where ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo'  order by ordem limit 1) as idfluxostatus
                    FROM immsgconflog l JOIN evento e on e.idevento =  l.idimmsgbody
                    JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento'
                    JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo' 
                    left JOIN fluxostatus ets on ets.idfluxo = ms.idfluxo and ets.idfluxostatus = r.idfluxostatus
                    JOIN carbonnovo._status s on ets.idstatus = s.idstatus
                    WHERE l.idimmsgconf = ?idimmsgconf? and
                    e.idpessoa = 1029
                    and (s.statustipo is null or s.statustipo = '')";
     }

     public static function filtrarIdsDaBusca()
     {
          return "SELECT distinct a.?col? as idmodulo
                    FROM tab a 
                    WHERE ?clausula?
                    AND a.?col? in (
                     ?ids?
                    );";
     }
}

?>
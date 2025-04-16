<?

class M5StatusQuery
{
    public static function buscarLeituras()
    {
        return "select a.*, ds.desvio,
        CONCAT('?_modulo=menurelatorio&menupai=294&_menu=N&_menulateral=N&_novajanela=Y&_idrep=242&_fds=',DATE_FORMAT(CURRENT_DATE - INTERVAL 2 DAY,'%d/%m/%Y'),'-',DATE_FORMAT(NOW() ,'%d/%m/%Y'),'&iddevice=',a.iddevice) as link
        From (
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            '' as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            '' as taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            '' as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            t.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 7 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                '' as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('t')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                JOIN
            tag t ON t.idtag = d.idtag
                LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = t.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
         
         not d.subtipo is null
         ?todas?
           
        union all
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            '' as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            '' as taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            '' as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            t.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 60 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                '' as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('p')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                JOIN
            tag t ON t.idtag = d.idtag
                LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = t.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
        
         not d.subtipo is null
         ?todas?
              
        
        union all
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            '' as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            '' as taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            '' as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            t.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 60 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                '' as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('u')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                JOIN
            tag t ON t.idtag = d.idtag
                LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = t.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
         
         not d.subtipo is null
         ?todas?
        
         union all
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            '' as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            '' as taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            '' as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            t.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 60 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                '' as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('d')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                JOIN
            tag t ON t.idtag = d.idtag
                LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = t.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
        
         not d.subtipo is null
         ?todas?
        
        
        
        
        
        
        
        union all
        
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            tl.idtag as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            CONCAT(ifnull(tle.sigla,''),'-', ifnull(tl.tag,'')) AS taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            tle.corsistema as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            tl.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 7 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                tl.status as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('t')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                JOIN
            tag t ON t.idtag = d.idtag
             LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                JOIN
            tagreserva tr on tr.idtag = t.idtag 
                JOIN
            tag tl on tl.idtag = tr.idobjeto and tr.objeto = 'tag' 
                LEFT JOIN
            empresa tle on tle.idempresa = tl.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = tl.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
        
         not d.subtipo is null
         ?todas?
           
        union all
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            tl.idtag as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            CONCAT(ifnull(tle.sigla,''),'-', ifnull(tl.tag,'')) AS taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            tle.corsistema as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            tl.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 60 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                tl.status as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('p')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                JOIN
            tag t ON t.idtag = d.idtag
             LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                JOIN
            tagreserva tr on tr.idtag = t.idtag 
                JOIN
            tag tl on tl.idtag = tr.idobjeto and tr.objeto = 'tag' 
                LEFT JOIN
            empresa tle on tle.idempresa = tl.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = tl.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
        
         not d.subtipo is null
         ?todas?
              
        
        union all
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            tl.idtag as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            CONCAT(ifnull(tle.sigla,''),'-', ifnull(tl.tag,'')) AS taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            tle.corsistema as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            tl.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 60 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                tl.status as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('u')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
            JOIN
            tag t ON t.idtag = d.idtag
             LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                JOIN
            tagreserva tr on tr.idtag = t.idtag 
                JOIN
            tag tl on tl.idtag = tr.idobjeto and tr.objeto = 'tag' 
                LEFT JOIN
            empresa tle on tle.idempresa = tl.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = tl.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
        
         not d.subtipo is null
         ?todas?
        
         union all
        SELECT 
            d.ordem,
            d.status,
            dh.iddevicesensorbloco,
            dh.iddevicecicloativ,
            dh.acao,
            dh.tipo,
            d.iddevice,
            d.idempresa as idempresadevice,
            d.ip_hostname,
            d.modelo,
            d.mac_address,
            s.descricao,
            t.idtag,
            tl.idtag as idtaglocada,
            t.idempresa as idempresatag,
            s.idtag as idtagsala,
            ss.idtag as idtagsala2,
            ssb.idtag as idtagsalabloco,
            s.idempresa as idempresasala,
            s.emuso,
            CONCAT(ifnull(te.sigla,''),'-', ifnull(t.tag,'')) AS tag,
            CONCAT(ifnull(tle.sigla,''),'-', ifnull(tl.tag,'')) AS taglocada,
            ifnull(s.descricao,'') AS tagsala,
            ifnull(ss.descricao,'') AS tagsala2,
            CONCAT(ifnull(ssbe.sigla,''),'-', ifnull(ssb.tag,''),' | ',ifnull(ssb.descricao,'')) AS tagsalabloco,
            te.corsistema as corsistema,
            tle.corsistema as corsistemalocada,
            se.corsistema as corsistemasala,
            sse.corsistema as corsistemasala2,
            ssbe.corsistema as corsistemabloco,
            tl.tag AS tagnotificacao,
            ds.nomesensor,
            d.versao,
            dh.registradoem,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), dh.registradoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), dh.registradoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), dh.registradoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), dh.registradoem)),2,'0'),'s') as ultimoregistro,
            dh.valor,
            IF(DATE_ADD(dh.registradoem,
                    INTERVAL 60 MINUTE) < NOW(),
                'danger',
                'success') AS dataatual,
                c.nomeciclo,
               dc.nomeativ,
               dc.var,
               dc.min,
               dc.max,
               dc.alertamin,
               dc.alertamax,
               c.iddeviceciclo,
            CONCAT(
                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s') as uptime,
                d.subtipo,
                if(d.iddeviceref = d.iddevice, null,d.iddeviceref) as iddeviceref,
                tagref.tag as tagref,
                tagref.idtag as idtagref,
                t.status as tagstatus,
                tl.status as taglocadastatus
        FROM
            devicesensorhist dh
                JOIN
            (SELECT 
                MAX(iddevicesensorhist) AS iddevicesensorhist
            FROM
                devicesensorhist dh
            WHERE
                tipo in ('d')
            GROUP BY iddevice) h ON h.iddevicesensorhist = dh.iddevicesensorhist
                JOIN
            device d ON d.iddevice = dh.iddevice
            LEFT join 
            devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
            LEFT join deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
            JOIN
            tag t ON t.idtag = d.idtag
             LEFT JOIN
            empresa te on te.idempresa = t.idempresa
                JOIN
            tagreserva tr on tr.idtag = t.idtag 
                JOIN
            tag tl on tl.idtag = tr.idobjeto and tr.objeto = 'tag' 
                LEFT JOIN
            empresa tle on tle.idempresa = tl.idempresa
                LEFT JOIN
            tagsala ts ON ts.idtag = tl.idtag
                LEFT JOIN
            tag s ON s.idtag = ts.idtagpai
                LEFT JOIN
            empresa se on se.idempresa = s.idempresa
                LEFT JOIN
            tagsala tss ON tss.idtag = s.idtag
                LEFT JOIN
            tag ss ON ss.idtag = tss.idtagpai
                LEFT JOIN
            empresa sse on sse.idempresa = ss.idempresa
                LEFT JOIN
            tagsala tssb ON tssb.idtag = ss.idtag
                LEFT JOIN
            tag ssb ON ssb.idtag = tssb.idtagpai
                LEFT JOIN
            empresa ssbe on ssbe.idempresa = ssb.idempresa
                JOIN
            devicesensor ds ON ds.iddevice = d.iddevice
            left join device dref on dref.iddevice = d.iddeviceref
            left join tag tagref on tagref.idtag = dref.idtag
        WHERE
         not d.subtipo is null
         ?todas?
         ) a  
         left join (select distinct 'Y' as desvio, ds.iddevice from devicesensorhistdesvio ds where (`ds`.`registradoem` BETWEEN (CURDATE() - INTERVAL 7 DAY) AND CURDATE()) ) ds on ds.iddevice = a.iddevice
         where status = 'ATIVO' 
        and ((tagstatus = 'ATIVO') || (tagstatus = 'LOCADO' and taglocadastatus = 'ATIVO'))
        ORDER BY a.subtipo, ifnull(idtagsalabloco, idtagsala2), a.ordem, a.descricao, iddeviceref";
    }
}

?>
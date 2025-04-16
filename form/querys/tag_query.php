<?

require_once(__DIR__ . "/_iquery.php");

class TagQuery implements DefaultQuery
{
    public static $table = 'tag';
    public static $pk = 'idtag';

    public const buscarPorChavePrimariaSQLPadrao = "SELECT t.*, a.caminho
                                                    FROM ?table? t 
                                                    LEFT JOIN arquivo a ON (t.idtag = a.idobjeto AND a.tipoobjeto = 'tagplanta')
                                                    WHERE ?pk? = ?pkval?";

    public static function buscarPorChavePrimariaPadrao()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQLPadrao, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarTagsPorTagTipo()
    {
        return "SELECT CONCAT(e.sigla,'-',t.tag, ' - ', t.descricao) as descr, t.*
                FROM tag t
                JOIN empresa e ON (t.idempresa = e.idempresa)  
                WHERE idtagtipo IN (?idtagtipo?) 
                ?orderby?";
    }


    public static function buscarTagsVinculadasAoResultadoPorTagTipoEPai()
    {
        return "SELECT CONCAT(e.sigla,'-',t.tag, ' - ', t.descricao) as descr, t.*, ov.idobjetovinculo
                FROM tagsala ts
                    JOIN tag t ON(t.idtag = ts.idtag)
                    JOIN empresa e ON (t.idempresa = e.idempresa)
                    LEFT JOIN objetovinculo ov ON (ov.idobjeto = ?idresultado? AND ov.tipoobjeto = 'resultado' AND ov.tipoobjetovinc = 'tag' AND ov.idobjetovinc = t.idtag)
                WHERE t.idtagtipo IN (?idtagtipo?) 
                    AND ts.idtagpai  = ?idtagpai?
                    AND t.status = 'ATIVO'
                ?orderby?";
    }

    public static function buscarTagsPorTagTipoEPai()
    {
        return "SELECT CONCAT(e.sigla,'-',t.tag, ' - ', t.descricao) as descr, t.*
                FROM tagsala ts
                JOIN tag t ON(t.idtag = ts.idtag)
                JOIN empresa e ON (t.idempresa = e.idempresa)
                WHERE t.idtagtipo IN (?idtagtipo?) 
                    AND ts.idtagpai  = ?idtagpai?
                    AND t.status = 'ATIVO'
                ?orderby?";
    }

    public static function buscarTagsQueNaoEstejamLocadasPorIdTagTipo()
    {
        return "SELECT 
                    t.idtag,
                    CONCAT(e.sigla, '-', t.tag, '-', t.descricao) AS descrtag,
                    e.sigla,
                    e.idempresa AS idempp,
                    t.idempresa AS idemptag,
                    CONCAT(e.sigla, '-', t.idtag) AS siglatag
                FROM tag t
                JOIN empresa e ON (t.idempresa = e.idempresa)
                WHERE t.idtagtipo IN (?idtagtipo?)
                AND t.status NOT IN ('INATIVO','LOCADO')
                ?clausula?
                ORDER BY (t.tag * 1);";
    }

    public static function buscarTagPorIdTagClassEIdTagTipo()
    {
        return "SELECT t.idtag, concat(t.tag,' - ',t.descricao) as descr
                FROM tag t
                WHERE idtagclass = ?idtagclass?
                AND idtagtipo = ?idtagtipo?";
    }

    public static function buscarTagPorId()
    {
        return "SELECT t.*, e.sigla
                FROM tag t
                JOIN empresa e ON e.idempresa = t.idempresa
                WHERE t.idtag = ?idtag?
                AND e.idempresa = ?idempresa?
                ";
    }

    public static function atualizarIdUnidadePeloIdTag()
    {
        return "UPDATE tag SET idunidade = ?idunidade? WHERE idtag = ?idtag?";
    }

    public static function atualizarStatusDaTagPeloId()
    {
        return "UPDATE tag
                SET status = '?status?', idfluxostatus = ?idfluxostatus?
                WHERE idtag = ?idtag?";
    }

    public static function atualizarColunasEValoresPorIdTag()
    {
        return "UPDATE tag
                SET ?colunasEValores?
                WHERE idtag = ?idtag?";
    }

    public static function buscarTagLocadaPeloIdEmpresa()
    {
        return "SELECT tr.idtagreserva, tr.idobjeto, t.status, t.tag, t.descricao
                FROM tagreserva tr
                JOIN tag t ON(t.idtag = tr.idobjeto)
                WHERE tr.idtag = ?idtag?
                AND t.idempresa = ?idempresa?
                AND t.status = '?status?'";
    }

    public static function buscarTagsCompartilhadasPeloIdTag()
    {
        return "SELECT GROUP_CONCAT(ovalue) AS idempresas 
                FROM share 
                WHERE sharemetodo = 'compartilharCbUserTAg' 
                AND FIND_IN_SET(?idtag?, REPLACE(JSON_EXTRACT(jclauswhere, '$.idtag'), '\"', '')) > 0;";
    }

    public static function buscarNfPorIdObjetoOrigem()
    {
        return "SELECT nf.nnfe, nf.idnf
                FROM nf nf, nfitem ni, tag t 
                WHERE ni.idnf = nf.idnf 
                AND ni.idnfitem = t.idobjetoorigem 
                AND t.idobjetoorigem = ?idobjetoorigem? limit 1";
    }

    public static function buscarDevice()
    {
        return "SELECT * FROM device WHERE idtag = ?idtag?";
    }

    public static function buscarPeloIdTag()
    {
        return "SELECT t.*, e.sigla
                FROM tag t
                JOIN empresa e ON(e.idempresa = t.idempresa)
                WHERE idtag = ?idtag?";
    }

    public static function buscarSequencia()
    {
        return "SELECT * FROM sequence where sequence = 'tag' and idempresa = ?idempresa?";
    }

    public static function inserir()
    {
        return "INSERT INTO tag
                (
                    idempresa, idunidade, tag, idfluxostatus, descricao, idtagclass, idtagtipo,
                    emuso, local, status, fabricante, modelo, numnfe,
                    nserie, workflow, obs, exatidao, padraotempmin, padraotempmax, linha,
                    coluna, varcarbon, placa, renavam, tara, tpRod, tpCar, uf, criadopor, criadoem,
                    alteradopor, alteradoem, idprateleira, processador, memoria, hd,
                    video, ip, so, nchip, nemei, plano, office,
                    consumo, voltagem, revisado, datacalibracao, dataqualificacao, calibracao,
                    qualificacao, macaddress, temperaturam5, umidadem5, pressaom5, certificado,
                    idpessoa, idobjetoorigem, tipoobjetoorigem, lotacao, multiensaio, tempo,
                    ordem, remoto, linguagem, indpressao, cor
                )
                VALUES
                (
                    ?idempresa?, ?idunidade?, ?tag?, ?idfluxostatus?, '?descricao?', ?idtagclass?, ?idtagtipo?, '?emuso?', '?local?',
                    '?status?', '?fabricante?', '?modelo?', '?numnfe?', '?nserie?', '?workflow?', '?obs?',
                    '?exatidao?', ?padraotempmin?, ?padraotempmax?, ?linha?, ?coluna?, '?varcarbon?', '?placa?',
                    '?renavam?', '?tara?', '?tpRod?', '?tpCar?', '?uf?', '?criadopor?', ?criadoem?, '?alteradopor?', 
                    ?alteradoem?, ?idprateleira?, '?processador?', '?memoria?', '?hd?',
                    '?video?', '?ip?', '?so?', '?nchip?', '?nemei?', '?plano?', '?office?', ?consumo?, '?voltagem?', '?revisado?',
                    ?datacalibracao?, ?dataqualificacao?, '?calibracao?', '?qualificacao?', '?macaddress?', ?temperaturam5?, ?umidadem5?,
                    ?pressaom5?, '?certificado?', ?idpessoa?, ?idobjetoorigem?, '?tipoobjetoorigem?', ?lotacao?, '?multiensaio?',
                    ?tempo?, ?ordem?, '?remoto?', '?linguagem?', '?indpressao?', '?cor?'
                )";
    }

    public static function buscarFilhos()
    {
        return "SELECT t.*
                FROM tagsala ts
                JOIN tag t ON(t.idtag = ts.idtag)
                WHERE ts.idtagpai = ?idtag?";
    }

    public static function buscarPaiPeloIdTag()
    {
        return "SELECT t.*, e.sigla
                FROM tagsala ts
                JOIN tag t ON(t.idtag = ts.idtagpai)
                JOIN empresa e ON(e.idempresa = t.idempresa)
                WHERE ts.idtag = ?idtag?";
    }

    public static function buscarTagsQuePossuemVinculoComTipo()
    {
        return "SELECT idtag, concat(sigla,tag,' - ',t.descricao) as tag 
                FROM tag t
                JOIN empresa e on e.idempresa = t.idempresa
                WHERE t.status IN ('MANUTENCAO','ATIVO')
                AND EXISTS (
                    SELECT 1 
                    FROM objetovinculo ov 
                    JOIN tag t1 on t1.idtag = ?idtag? 
                    AND ov.idobjetovinc = t1.idtagtipo 
                    AND ov.tipoobjetovinc = 'tagtipo' 
                    AND ov.tipoobjeto = 'tagtipo'
                    AND ov.idobjeto = t.idtagtipo
                )
                ORDER BY sigla,tag*1 ;";
    }

    public static function buscarTagsPorIdClassificacao()
    {
        return "SELECT t.idtag, e.sigla, t.descricao, a.caminho
                FROM tag t
                JOIN empresa e ON(e.idempresa = t.idempresa)
                LEFT JOIN arquivo a ON (t.idtag = a.idobjeto AND a.tipoobjeto = 'tagplanta')
                WHERE t.idtagclass = ?idtagclass?
                AND t.status = 'ATIVO'
                ORDER BY t.descricao";
    }

    public static function buscarImpressorasSemModulo()
    {
        return "SELECT CONCAT(e.sigla,'-',t.tag,' ',t.descricao) AS nome,
                        t.idtag,
                        t.fabricante 
                FROM tag t 
                    JOIN tagtipo tp ON (tp.idtagtipo = t.idtagtipo AND tp.tagtipo='IMPRESSORA') 
                    JOIN empresa e ON (e.idempresa=t.idempresa) 
                WHERE t.status not in ('INATIVO','DESAPARECIDO','ESTOQUE','MANUTENCAO')
                    AND NOT EXISTS(
                        SELECT 1 
                        FROM objetovinculo ov 
                        WHERE ov.tipoobjeto='modulo' 
                            AND ov.idobjeto=?idmodulo?
                            AND ov.idobjetovinc=t.idtag 
                            AND ov.tipoobjetovinc='tag')";
    }

    public static function buscarTagPorIdPessoa()
    {
        return "SELECT t.idtag,t.tag,t.descricao, e.sigla
                FROM tag t 
                JOIN empresa e ON(t.idempresa = e.idempresa)
                WHERE t.status='ATIVO' AND t.idpessoa = ?idpessoa?";
    }

    public static function buscarTagPaiComFilhos()
    {
        return "SELECT CONCAT(a.sigla, '-', a.tag) AS tag,
                    CONCAT(a.siglaoriginal, '-', a.tagoriginal) AS tagoriginal,
                    a.idtag,
                    a.ordem 
                FROM (
                    SELECT  e.sigla,
                        t.tag,
                        etri.sigla AS siglaoriginal,
                        tori.tag AS tagoriginal,
                        t.idtag,
                        fs.ordem
                    FROM tag t
                        LEFT JOIN tagtipo tt ON (tt.idtagtipo=t.idtagtipo )
                        LEFT JOIN tagclass tc ON (tc.idtagclass=t.idtagclass AND tc.status = 'ATIVO')
                        JOIN tagsala s ON (s.idtag=t.idtag AND s.idtagpai = ?idtag?)
                        JOIN empresa e ON (e.idempresa = t.idempresa)
                        LEFT JOIN tagreserva tr ON (tr.idobjeto = t.idtag AND tr.objeto = 'tag')
                        LEFT JOIN tag tori ON (tori.idtag = tr.idtag)
                        LEFT JOIN empresa etri ON (etri.idempresa = tori.idempresa)
                        JOIN fluxostatus fs ON fs.idfluxostatus = t.idfluxostatus
                        JOIN " . _DBCARBON . "._status st ON st.idstatus = fs.idstatus
                    WHERE t.status = 'ATIVO'
                    UNION 
                    SELECT 
                        e.sigla,
                        t.tag,
                        etri.sigla AS siglaoriginal,
                        tori.tag AS tagoriginal,
                        t.idtag,
                        fs.ordem
                    FROM tag t
                        LEFT JOIN tagtipo tt ON (tt.idtagtipo=t.idtagtipo )
                        LEFT JOIN tagclass tc ON (tc.idtagclass=t.idtagclass AND tc.status = 'ATIVO')
                        JOIN empresa e ON (e.idempresa = t.idempresa)
                        LEFT JOIN tagreserva tr ON (tr.idobjeto = t.idtag AND tr.objeto = 'tag')
                        LEFT JOIN tag tori ON (tori.idtag = tr.idtag)
                        LEFT JOIN empresa etri ON (etri.idempresa = tori.idempresa)
                        JOIN fluxostatus fs ON fs.idfluxostatus = t.idfluxostatus
                        JOIN " . _DBCARBON . "._status st ON st.idstatus = fs.idstatus
                    WHERE t.idtag = ?idtag?
                ) a
                ORDER BY
                    a.tag DESC";
    }

    public static function buscarTagAtivoEAlocada()
    {
        return "SELECT 
                        CONCAT(e.sigla,'-',t.tag) AS tag,
                        CONCAT(etri.sigla,'-',tori.tag) AS tagoriginal,
                        t.idtag
                    FROM tag t
                        LEFT JOIN tagtipo tt ON (tt.idtagtipo=t.idtagtipo )
                        LEFT JOIN tagclass tc ON (tc.idtagclass=t.idtagclass AND tc.status = 'ATIVO')
                        JOIN empresa e ON (e.idempresa = t.idempresa)
                        LEFT JOIN tagreserva tr ON (tr.idobjeto = t.idtag AND tr.objeto = 'tag')
                        LEFT JOIN tag tori ON (tori.idtag = tr.idtag)
                        LEFT JOIN empresa etri ON (etri.idempresa = tori.idempresa)
                    WHERE t.idtag = ?idtag?";
    }

    public static function buscarTagClass1PorGetIdEmpresaEShare()
    {
        return "SELECT * 
                FROM (
                    SELECT t.idtag, t.descricao, CONCAT(e.sigla,'-',t.tag) AS tag, t.idfluxostatus
                    FROM tag t
                    JOIN tagtipo tt ON (tt.idtagtipo = t.idtagtipo AND tt.calendario = 'Y')
                    JOIN empresa e ON(e.idempresa = t.idempresa)
                    WHERE t.status = 'ATIVO' AND t.idtagclass = ?idtagclass?
                        ?getidempresa?
                    UNION  
                    SELECT t.idtag, t.descricao, CONCAT(e.sigla,'-', t.tag) AS tag, t.idfluxostatus
                    FROM tag t 
                    JOIN empresa e ON(e.idempresa = t.idempresa)
                    JOIN tagtipo tt ON (tt.idtagtipo = t.idtagtipo AND tt.calendario = 'Y')
                    WHERE t.status = 'ATIVO' AND t.idtagclass =?idtagclass?
                    ?share?
                ) AS a
                JOIN fluxostatus fs ON(fs.idfluxostatus = a.idfluxostatus)
                JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
                AND cs.statustipo != 'LOCADO'
                GROUP BY a.idtag
                ORDER BY a.descricao;";
    }

    public static function buscarListaDeTagsPorIdEventoEIdEventoTipoAdd()
    {
        return "SELECT *
                FROM (
                    SELECT concat(t.tag,' - ',t.descricao) as descobj,e.* 
                    FROM eventoobj e 
                    JOIN tag t ON(t.idtag=e.idobjeto)
                    WHERE e.idevento= ?idevento?
                    AND  e.ideventoadd=  ?ideventotipoadd?
                    AND e.objeto='tag' 
                    UNION
                    SELECT concat(t.tag,' - ',t.descricao) as descobj,e.* 
                    FROM eventoobj e JOIN tag t ON(t.idtag=e.idobjeto)
                    WHERE e.idevento= ?idevento?
                    AND  e.ideventoadd=  ?ideventotipoadd?
                    AND e.objeto='tag' 
                    UNION            
                    SELECT concat(t.nome) as descobj,e.* 
                    FROM eventoobj e JOIN pessoa t ON(t.idpessoa=e.idobjeto)
                    WHERE e.idevento= ?idevento?
                    AND e.ideventoadd=  ?ideventotipoadd?
                    AND e.objeto='pessoa'         
                    UNION            
                    SELECT concat(t.titulo) as descobj,e.* 
                    FROM eventoobj e JOIN sgdoc t ON(t.idsgdoc=e.idobjeto)
                    WHERE e.idevento= ?idevento?
                    AND  e.ideventoadd=  ?ideventotipoadd?
                    AND e.objeto='sgdoc' 							
                    UNION            
                    SELECT concat(IFNULL(p.descrcurta,p.descr)) as descobj,e.* 
                    FROM eventoobj e JOIN prodserv p ON(p.idprodserv=e.idobjeto)
                    WHERE e.idevento= ?idevento?
                    AND  e.ideventoadd=  ?ideventotipoadd?
                    AND e.objeto='prodserv'
                ) as qry 							
                ORDER BY qry.ord, qry.alteradoem";
    }

    public static function buscarTagsPaiOuFilhos()
    {
        return "SELECT
                    tp.idtag AS idtagpai,
                    CONCAT(ep.sigla, '-', tp.tag) AS tagpai,
                    tp.cor AS tagpaicor,
                    tp.idunidade AS idunidadepai,
                    ttp.cor AS tagtipopaicor,
                    tp.descricao AS descricaopai,
                    ep.idempresa AS idempresapai,
                    tf.idtag AS idtagfilho, 
                    CONCAT(ef.sigla, '-', tf.tag) AS tagfilho,
                    tf.cor AS tagfilhocor,
                    tf.idunidade AS idunidadefilho,
                    ef.idempresa AS idempresafilho,
                    ttf.cor AS tagtipofilhocor,
                    CONCAT(ef.sigla, '-', tf.tag, '-', tf.descricao) AS descricaofilho, 
                    ttp.idtagtipo AS idtagtipopai,
                    ttp.cssicone AS cssiconepai,
                    ttf.idtagtipo AS idtagtipofilho,
                    ttf.cssicone AS cssiconefilho,
                    d.iddevice,
                    dsb.iddevicesensorbloco,
                    dsb.tipo
                FROM tag tp
                JOIN tagsala tsp ON(tsp.idtagpai = tp.idtag)
                LEFT JOIN empresa ep ON(ep.idempresa = tp.idempresa)
                LEFT JOIN tagtipo ttp ON(ttp.idtagtipo = tp.idtagtipo)
                JOIN tag tf ON(tf.idtag = tsp.idtag)
                LEFT JOIN empresa ef ON(ef.idempresa = tf.idempresa)
                LEFT JOIN tagtipo ttf ON(ttf.idtagtipo = tf.idtagtipo)
                -- Pegar devices vinculados
                LEFT JOIN device d ON(d.idtag = tf.idtag)
                LEFT JOIN devicesensor ds ON(d.iddevice = ds.iddevice)
                LEFT JOIN devicesensorbloco dsb ON(ds.iddevicesensor = dsb.iddevicesensor)
                -- LEFT JOIN devicesensorhist dh ON(dh.iddevice = d.iddevice)
                -- LEFT JOIN devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
                -- LEFT JOIN deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
                ?where?
                AND tf.status = 'ATIVO'";
    }

    public static function buscarFilhosApartirDoBloco()
    {
        return "SELECT
                    -- BLOCO
                    tp.idtag AS idtagbloco,
                    tp.tag AS tagbloco,
                    tp.descricao AS descricaobloco,
                    tp.cor AS corbloco,
                    tp.status AS statusbloco,
                    tp.idunidade AS idunidadebloco,
                    ttp.idtagtipo AS idtagtipobloco,
                    ttp.cssicone AS cssiconebloco,
                    eb.idempresa AS idempresabloco,
                    -- SALAS
                    tf.idtag AS idtagsala,
                    tsp.idtagpai AS idtagpaisala,
                    CONCAT(es.sigla, '-', tf.tag) as tagsala,
                    CONCAT(es.sigla, '-', tf.tag,'-', tf.descricao) AS descricaosala,
                    tf.indpressao AS indpressaosala,
                    tf.cor AS corsala,
                    tf.status AS statussala,
                    tf.idunidade AS idunidadesala,
                    ttf.idtagtipo AS idtagtiposala,
                    ttf.cssicone AS cssiconesala,
                    es.idempresa AS idempresasala,
                    tf.idtagclass AS idtagclasssala,
                    -- EQUIPAMENTOS
                    te.idtag AS idtagequipamento,
                    CONCAT(ee.sigla, '-', te.tag) AS tagequipamento,
                    te.descricao AS descricaoequipamento,
                    te.indpressao AS indpressaoequipamento,
                    te.cor AS corequipamento,
                    te.status AS statusequipamento,
                    te.idunidade AS idunidadeequipamento,
                    tte.idtagtipo AS idtagtipoequipamento,
                    tte.cssicone AS cssiconeequipamento,
                    tte.cor as tipotagcorequipamento,
                    tsf.idtagpai AS idtagpaiequipamento,
                    ee.idempresa AS idempresaequipamento,
                    -- EQUIPAMENTOS DE EQUIPAMENTOS
                    tef.idtag AS idtagequipamentofilho,
                    CONCAT(eef.sigla, '-', tef.tag) AS tagequipamentofilho,
                    tef.descricao AS descricaoequipamentofilho, 
                    tef.status AS statusequipamentofilho,
                    tef.idunidade AS idunidadeequipamentofilho,
                    ttef.idtagtipo AS idtagtipoequipamentofilho,
                    ttef.cssicone AS cssiconeequipamentofilho,
                    ttef.cor AS tipotagcorequipamentofilho,
                    tsef.idtagpai as idtagpaiequipamentofilho,
                    eef.idempresa AS idempresaequipamentofilho,
                    -- DEVICE
                    d.iddevice,
                    dsb.iddevicesensorbloco,
                    dsb.tipo
                FROM tag tp
                JOIN tagsala tsp ON(tsp.idtagpai = tp.idtag)
                LEFT JOIN empresa eb ON(eb.idempresa = tp.idempresa)
                LEFT JOIN tagtipo ttp ON(ttp.idtagtipo = tp.idtagtipo)
                -- Salas
                JOIN tag tf ON(tf.idtag = tsp.idtag)
                LEFT JOIN tagsala tsf ON(tsf.idtagpai = tf.idtag)
                LEFT JOIN empresa es ON(es.idempresa = tf.idempresa)
                LEFT JOIN tagtipo ttf ON(ttf.idtagtipo = tf.idtagtipo)
                -- Equipamentos da sala
                LEFT JOIN tag te ON(te.idtag = tsf.idtag)
                LEFT JOIN tagtipo tte ON(tte.idtagtipo = te.idtagtipo)
                LEFT JOIN empresa ee ON(ee.idempresa = te.idempresa)
                -- Filhos de equipamentos ou salas
                LEFT JOIN tagsala tsef ON(tsef.idtagpai = te.idtag)
                LEFT JOIN tag tef ON(tsef.idtag = tef.idtag)
                LEFT JOIN tagtipo ttef ON(tef.idtagtipo = ttef.idtagtipo)
                LEFT JOIN empresa eef ON(eef.idempresa = tef.idempresa)
                -- Pegar devices vinculados ao equipamento ou sala
                LEFT JOIN device d ON((d.idtag = te.idtag) OR (d.idtag = tef.idtag))
                LEFT JOIN devicesensor ds ON(d.iddevice = ds.iddevice)
                LEFT JOIN devicesensorbloco dsb ON(ds.iddevicesensor = dsb.iddevicesensor)
                -- WHERE tp.idtag = idBloco
                WHERE tp.idtagclass = 13
                -- AND tp.status = 'ATIVO'
                -- AND tf.status = 'ATIVO'
                -- AND te.status != 'ALOCADO'
                -- AND tef.status = 'ATIVO'
                GROUP BY te.idtag, tf.idtag, tef.idtag";
    }

    public static function buscarTagsAtivasOuLocadas()
    {
        return "SELECT t.idtag, CONCAT(e.sigla, '-', t.descricao) as descricao, ts.idtagpai, tt.cssicone, t.tag
                FROM tag t
                JOIN empresa e ON(e.idempresa = t.idempresa)
                LEFT JOIN tagtipo tt ON(tt.idtagtipo = t.idtagtipo)
                LEFT JOIN tagsala ts ON(ts.idtag = t.idtag)
                WHERE t.idtagclass = 1
                AND (t.status = 'ATIVO' OR t.status = 'LOCADO')";
    }

    public static function buscarBlocosDisponiveisParaVinculo()
    {
        return "SELECT
                    t.idtag as id,
                    t.tag,
                    CONCAT(e.sigla, '-', t.tag, '-', t.descricao) as descricao,
                    t.cor,
                    u.idunidade
                FROM tag t
                JOIN empresa e ON(t.idempresa = e.idempresa)
                LEFT JOIN unidade u ON(u.idunidade = t.idunidade)
                LEFT JOIN fluxostatus fs ON(fs.idfluxostatus = t.idfluxostatus)
                LEFT JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
                WHERE t.status = 'ATIVO'
                AND cs.statustipo != 'INATIVO'
                AND t.idtagclass = 13
                ?notin?
                AND EXISTS(
                    SELECT 1
                    FROM tagsala
                    WHERE idtagpai = t.idtag
                )
                GROUP BY t.idtag;";
    }

    public static function buscarTiposAtivosDeEquipamentos()
    {
        return "SELECT tt.idtagtipo, tt.tagtipo
                FROM tag t
                LEFT JOIN tagtipo tt ON(tt.idtagtipo = t.idtagtipo)
                WHERE tt.status = 'ATIVO'
                AND tt.idtagclass = 1 -- IN(1, 4, 5, 8, 12)
                AND t.status = 'ATIVO'
                GROUP BY tt.idtagtipo
                ORDER BY tt.tagtipo;";
    }

    public static function buscarTagsFormatadasPorIdTagTipo()
    {
        return "SELECT t.idtag, LPAD(t.tag, 4, '0') as tag, CONCAT(t.tag, ' - ', t.descricao) as descricao, t.obs, e.sigla
                FROM tag t
                JOIN empresa e on e.idempresa = t.idempresa
                WHERE t.idtagtipo in(?idtagtipo?) 
                AND t.status = 'ATIVO'
                ?idempresa?
                order by trim(t.descricao)";
    }

    public static function buscarTagsFormatadasPorIdTagSalaEIdTagTipo()
    {
        return "SELECT t.idtag, LPAD(t.tag, 4, '0') as tag, CONCAT(t.tag, ' - ', t.descricao) as descricao, t.obs, e.sigla
                FROM tagsala ts
                JOIN tag t on ts.idtag = t.idtag
                JOIN empresa e on e.idempresa = t.idempresa
                WHERE t.idtagtipo in(?idtagtipo?) 
                AND ts.idtagpai = ?idtagpai?
                AND t.status = 'ATIVO'
                order by trim(t.descricao)";
    }

    public static function buscarTagTagdim()
    {
        return "SELECT 
                    p.idtagdim,
                    CONCAT(l.descricao,
                            CONCAT(CASE p.coluna
                                        WHEN 0 THEN '0'
                                        WHEN 1 THEN 'A'
                                        WHEN 2 THEN 'B'
                                        WHEN 3 THEN 'C'
                                        WHEN 4 THEN 'D'
                                        WHEN 5 THEN 'E'
                                        WHEN 6 THEN 'F'
                                        WHEN 7 THEN 'G'
                                        WHEN 8 THEN 'H'
                                        WHEN 9 THEN 'I'
                                        WHEN 10 THEN 'J'
                                        WHEN 11 THEN 'K'
                                        WHEN 12 THEN 'L'
                                        WHEN 13 THEN 'M'
                                        WHEN 14 THEN 'N'
                                        WHEN 15 THEN 'O'
                                        WHEN 16 THEN 'P'
                                        WHEN 17 THEN 'Q'
                                        WHEN 18 THEN 'R'
                                        WHEN 19 THEN 'S'
                                        WHEN 20 THEN 'T'
                                        WHEN 21 THEN 'U'
                                        WHEN 22 THEN 'V'
                                        WHEN 23 THEN 'X'
                                        WHEN 24 THEN 'Z'
                                    END,
                                    p.linha)) AS campo
                FROM
                    tag l,
                    tagdim p
                WHERE
                    p.idtag = l.idtag AND p.idtagdim = ?idobjeto?";
    }

    public static function buscarVarCarbonPorIdTag()
    {
        return "SELECT id,rot from (
                    SELECT 'IMP_DIAGNOSTICO' as id,'Imp Diagnóstico' as rot
                    UNION SELECT 'IMP_DIAGNOSTICO_PROVISORIO' as id, 'Imp Diagnóstico Provisório' as rot 
                    UNION SELECT 'IMPRESSORA_INCUBACAO'  as id,'Imp Setor Incubação' as rot 
                    UNION SELECT 'IMPRESSORA_CQ'  as id,'Imp CQ' as rot 
                    UNION SELECT 'IMPRESSORA_SEMENTES'  as id,'Imp Sementes' as rot
                    UNION SELECT 'IMPRESSORA_ALMOXARIFADO'  as id,'Imp Almoxarifado' as rot
                    UNION SELECT 'IMPRESSORA_ALMOXARIFADO_ZEBRA'  as id,'Imp Almoxarifado Zebra' as rot
                    UNION SELECT 'IMPRESSORA_ALMOXARIFADO_ITEM'  as id,'Imp Almoxarifado Itens' as rot
                    UNION SELECT 'IMPRESSORA_PRODUCAO'  as id,'Imp Produção Zebra' as rot
                    UNION SELECT 'IMPRESSORA_PRODUCAO_2'  as id,'Imp Produção Zebra 2' as rot
                    UNION SELECT 'IMPRESSORA_PRODUCAO_SEM'  as id,'Imp Produção Sementes' as rot
                    UNION SELECT 'IMPRESSORA_CQ_2' as id, 'Imp CQ Sem' as rot
                    UNION SELECT '_IMPRESSORA_LOGISTICA' as id, 'Imp Logistica' as rot
                    UNION SELECT 'IMPRESSORA_MEIOS' as id, 'Imp Meios' as rot
                    UNION SELECT 'IMPRESSORA_PRODUCAO_SEM2'  as id,'Imp Produção Sementes 2' as rot
                    UNION SELECT 'IMPRESSORA_PRODUCAO_SEM3'  as id,'Imp Produção Sementes 3' as rot) as u       
                where not exists (
                    SELECT * from tag t 
                    where t.status='ATIVO' 
                    ?getidempresa?
                    and t.varcarbon = u.id 
                    and t.idtag != ?idtag? 
                )";
    }

    public static function buscarLocalizacoes()
    {
        return "SELECT qry.*
                FROM (
                    SELECT DISTINCT(tp.idtag), CONCAT(tp.descricao,' - ',tt.tagtipo) AS tagdescr
                    FROM tag tp
                    LEFT JOIN tagsala ts ON(tp.idtag = ts.idtagpai)
                    JOIN tagtipo tt ON(tt.idtagtipo = tp.idtagtipo)
                    WHERE tp.status = 'ATIVO'
                    AND tp.idtagclass IN(2,4,11,10,13)
                    GROUP BY tp.idtag
                ) as qry
                ORDER BY qry.tagdescr";
    }

    public static function buscarTagPaiOuFilhoPorIdTag()
    {
        return "SELECT * 
                FROM (
                        SELECT s.idtagsala, t.idtag, t.descricao, concat(sigla,'-',t.tag) as tag, t.tag as tagsemsigla, tc.tagclass, tt.tagtipo,t.status, tt.criadoem, t.idempresa,  'CONTÉM' AS tipo, fs.ordem
                        FROM tag t
                        LEFT JOIN tagtipo tt on tt.idtagtipo=t.idtagtipo 
                        LEFT JOIN tagclass tc on tc.idtagclass=t.idtagclass and tc.status = 'ATIVO'
                        JOIN tagsala s on (s.?colunaparajoincomtag? = t.idtag)
                        JOIN empresa e on (e.idempresa = t.idempresa)
                        JOIN fluxostatus fs ON(fs.idfluxostatus = t.idfluxostatus)
                        JOIN " . _DBCARBON . "._status st ON (st.idstatus = fs.idstatus)
                        WHERE s.?colunapaioufilho? = ?idtag?
                    ) a
                ORDER BY tagsemsigla, ordem, tipo, tagclass, tagtipo";
    }

    public static function buscarTagsParaVincularPorIdTag()
    {
        return "SELECT idtag, concat(sigla,'-', tag,' ',t.descricao) as tag 
                FROM tag t 
                JOIN empresa e ON(e.idempresa = t.idempresa)
                WHERE t.status IN ('MANUTENCAO','ATIVO') 
                ?getidempresa?
                AND NOT EXISTS (
                    SELECT 1 FROM tagsala ts WHERE ts.idtag = t.idtag
                ) 
                AND EXISTS (
                    SELECT 1 
                    FROM objetovinculo ov
                    JOIN tag t1 ON (t1.idtag = ?idtag? and  ov.idobjeto = t1.idtagtipo and ov.tipoobjeto = 'tagtipo' and ov.tipoobjetovinc = 'tagtipo' and ov.idobjetovinc = t.idtagtipo)
                )
                ORDER BY t.descricao, t.tag*1 ;";
    }

    public static function buscarUnidadesPorIdTag()
    {
        return "SELECT idunidade, unidade 
                FROM unidade 
                WHERE status = 'ATIVO' 
                ?getidempresa?
                ?union?";
    }

    public static function buscarTagEmpresa()
    {
        return "SELECT sigla, idtag, tag
                  FROM tag JOIN empresa ON empresa.idempresa = tag.idempresa
                 WHERE idobjetoorigem = ?idobjetoorigem?
                   AND tipoobjetoorigem = '?tipoobjetoorigem?'";
    }

    public static function buscarTagEmpresaPorIdNf()
    {
        return "SELECT e.sigla, t.idtag, t.tag, ni.idnfitem, ni.idnf
                  FROM tag t JOIN empresa e ON e.idempresa = t.idempresa
                  JOIN nfitem ni ON ni.idnfitem = t.idobjetoorigem
                 WHERE idobjetoorigem IS NOT NULL
                   AND tipoobjetoorigem = 'nfitem'
                   AND ni.idnf = '?idnf?'";
    }

    public static function buscarTagsAtivas()
    {
        return "SELECT
                    t.idtag,
                    CONCAT(e.sigla, '-', t.tag, ' ', t.descricao) as descricao,
                    t.tag
                FROM tag t
                JOIN empresa e ON(e.idempresa = t.idempresa)
                WHERE t.status NOT IN('LOCADO', 'INATIVO');";
    }

    public static function buscarBlocoDaTag()
    {
        // Tag ligadas diretamento ao bloco
        return "SELECT
                    tb.idtag as idbloco,
                    CONCAT(eb.sigla, '-', tb.tag, '- ', tb.descricao) as bloco,
                    'SEM SALA' as idsala,
                    'SEM SALA' as sala,
                    te.idtag as idequipamento,
                    CONCAT(ee.sigla, '-', te.tag, '-', te.descricao) as equipamento
                FROM tag te
                JOIN tagsala tsb ON(tsb.idtag = te.idtag)
                JOIN tag tb ON(tb.idtag = tsb.idtagpai)
                JOIN empresa ee ON(ee.idempresa = te.idempresa)
                JOIN empresa eb ON(eb.idempresa = tb.idempresa)
                WHERE tb.idtagclass = 13
                AND te.idtag = ?idtag?
                AND te.status = 'ATIVO'
                UNION
                -- Tag ligadas a sala < bloco
                SELECT
                    tb.idtag as idbloco,
                    CONCAT(eb.sigla, '-', tb.tag, '- ', tb.descricao) as bloco,
                    ts.idtag as idsala,
                    ts.descricao as sala,
                    te.idtag as idequipamento,
                    CONCAT(ee.sigla, '-', te.tag, '-', te.descricao) as equipamento
                FROM tag te
                JOIN tagsala tss ON(tss.idtag = te.idtag)
                JOIN tag ts ON(tss.idtagpai = ts.idtag)
                JOIN tagsala tsb ON(tsb.idtag = ts.idtag)
                JOIN tag tb ON(tb.idtag = tsb.idtagpai)
                JOIN empresa ee ON(ee.idempresa = te.idempresa)
                JOIN empresa eb ON(eb.idempresa = tb.idempresa)
                WHERE tb.idtagclass = 13
                AND te.idtag = ?idtag?
                AND te.status = 'ATIVO'
                UNION
                -- Tag ligada a equipamento do tipo quarto termico < sala < bloco
                SELECT
                    tb.idtag as idbloco,
                    CONCAT(eb.sigla, '-', tb.tag, '- ', tb.descricao) as bloco,
                    tep.idtag as idsala,
                    tep.descricao as sala,
                    te.idtag as idequipamento,
                    CONCAT(ee.sigla, '-', te.tag, '-', te.descricao) as equipamento
                FROM tag te
                JOIN tagsala tse ON(tse.idtag = te.idtag)
                JOIN tag tep ON(tse.idtagpai = tep.idtag)
                LEFT JOIN tagsala tss ON(tss.idtag = tep.idtag)
                LEFT JOIN tag ts ON(tss.idtagpai = ts.idtag)
                LEFT JOIN tagsala tsb ON(tsb.idtag = ts.idtag)
                LEFT JOIN tag tb ON(tb.idtag = tsb.idtagpai)
                LEFT JOIN empresa ee ON(ee.idempresa = te.idempresa)
                LEFT JOIN empresa eb ON(eb.idempresa = tb.idempresa)
                WHERE tb.idtagclass = 13
                AND te.idtag = ?idtag?
                AND te.status = 'ATIVO'";
    }

    public static function buscarTagsEmEstoquePorIdUnidadeIdProdServEIdTagTipo()
    {
        return "SELECT 
                    n.idprodserv,
                    t.idtag,
                    concat(e.sigla,'-',t.tag) as tag,
                    t.idunidade,
                    e.idempresa,
                    tt.tagtipo,
                    t.descricao,
                    modelo,
                    fabricante,
                    tagclass,
                    t.status
                FROM laudo.tag t
                JOIN laudo.tagtipo tt ON t.idtagtipo = tt.idtagtipo
                JOIN tagclass tc ON t.idtagclass = tc.idtagclass
                JOIN empresa e ON t.idempresa = e.idempresa
                JOIN nfitem n ON t.idobjetoorigem = n.idnfitem
                WHERE t.status IN ('ESTOQUE') 
                AND tipoobjetoorigem = 'nfitem'
                ?clausulaunidade?
                AND tt.status!='INATIVO'
                AND (
                    n.idprodserv = ?idprodserv? 
                    OR n.idprodserv IN (
                        SELECT idprodserv 
                        FROM prodserv p 
                        WHERE p.idtagtipo = ?idtagtipo? 
                        AND p.status='ATIVO' 
                        ORDER BY descr
                    )
                )";
    }

    public static function buscarTodasTags()
    {
        return "SELECT  t.idtag, t.descricao FROM tag t";
    }

    public static function buscarTagPorVarCarbonEGetIdEmpresa()
    {
        return "SELECT tag,fabricante,modelo,varcarbon 
                FROM tag 
                WHERE status = 'ATIVO' 
                AND varcarbon IN (?varcarbon?)
                ?getidempresa?
                ORDER BY tag ASC";
    }

    public static function buscarDescricaoDasTags()
    {
        return "SELECT DISTINCT t.descricao, CONCAT(e.sigla, ' - ', t.descricao) as tagdescricao
                FROM tag t
                JOIN empresa e ON(t.idempresa = e.idempresa)
                WHERE t.status = 'ATIVO'";
    }

    public static function buscarGaiolasBioensaio()
    {
        return "SELECT CONCAT(b.descricao,' - ',g.descricao) AS descricao
                from tag g
                    join  tagsala ts on (ts.idtag = g.idtag) 
                    join tag b on(b.idtag=ts.idtagpai)
                where g.idtag = ?idtag?";
    }

    public static function buscarTipoDeSalasDeBioensaioPorUnidade()
    {
        return "SELECT *
                from tagtipo tt
                where ( tt.bioensaio='Y')
                    and exists(select 1 from  tag t join  tagsala ts 
                    join tag t2 on(t2.idtag=ts.idtag and t2.idunidade=  ?idunidadepadrao? and t2.idtagclass = 2  and ts.idtagpai = t.idtag) 
                    join tagtipo tt2 on(tt2.idtagtipo=t2.idtagtipo and tt2.bioensaio='Y')
                    where t.status='ATIVO' 
                    and tt.idtagtipo=t.idtagtipo
                    and t.idtagclass = 2
                    ?getidempresa?
                    and t.idunidade= ?idunidadepadrao? )";
    }

    public static function buscarSalasDeBioensaioPorUnidade()
    {
        return "SELECT 
                    *
                FROM
                    (SELECT 
                        tt.idtagtipo, ts.idtagpai, t.descricao AS descrpai, t2.ordem,t.idempresa,ts.idempresa as empresapai
                    FROM
                        tag t
                    JOIN tagtipo tt ON (tt.idtagtipo = t.idtagtipo
                        AND tt.bioensaio = 'Y')
                    JOIN tagsala ts ON (ts.idtagpai = t.idtag)
                    JOIN tag t2 ON (t2.idtag = ts.idtag
                        AND t2.idtagclass = 2)
                    JOIN tagtipo tt2 ON (tt2.idtagtipo = t2.idtagtipo
                        AND tt2.bioensaio = 'Y')
                    LEFT JOIN localensaio e ON (e.status IN ('AGENDADO' , 'ATIVO')
                        AND e.idtag = t2.idtag)
                    LEFT JOIN vw_reservabioensaio r ON (e.idanalise = r.idanalise
                        AND e.status != 'FINALIZADO')
                    WHERE
                        t.status = 'ATIVO' AND t.idtagclass = 2
                            AND t.idunidade = ?idunidadepadrao?
                    GROUP BY ts.idtagpai UNION SELECT 
                        tt.idtagtipo, ts.idtagpai, t.descricao AS descrpai, t2.ordem,t.idempresa,ts.idempresa as empresapai
                    FROM
                        tag t
                    JOIN tagreserva t3 ON t3.idobjeto = t.idtag
                    JOIN tagtipo tt ON (tt.idtagtipo = t.idtagtipo
                        AND tt.bioensaio = 'Y')
                    JOIN tagsala ts ON (ts.idtagpai = t3.idtag)
                    JOIN tag t2 ON (t2.idtag = ts.idtag
                        AND t2.idtagclass = 2)
                    JOIN tagtipo tt2 ON (tt2.idtagtipo = t2.idtagtipo
                        AND tt2.bioensaio = 'Y')
                    LEFT JOIN localensaio e ON (e.status IN ('AGENDADO' , 'ATIVO')
                        AND e.idtag = t2.idtag)
                    LEFT JOIN vw_reservabioensaio r ON (e.idanalise = r.idanalise
                        AND e.status != 'FINALIZADO')
                    WHERE
                        t.status = 'ATIVO' AND t.idtagclass = 2
                            AND t.idunidade = ?idunidadepadrao?
                    GROUP BY ts.idtagpai) a
                    where a.empresapai = ?idempresa?
                ORDER BY a.idtagpai , a.ordem";
    }

    public static function buscarSalasDeUmaSala()
    {
        return "SELECT t.*
                from tagsala ts
                    join tag t on(ts.idtag=t.idtag and t.idunidade= ?idunidadepadrao?) 
                    join tagtipo tt2 on(tt2.idtagtipo=t.idtagtipo and tt2.bioensaio='Y')
                where ts.idtagpai= ?idtagpai?
                    and  t.idtagclass = 2
                order by ordem";
    }

    public static function buscarTagClassPorIdClassGetIdEmpresaECalendario()
    {
        return "SELECT * 
                FROM (
                    SELECT t.idtag, t.descricao, CONCAT(e.sigla,'-',t.tag) AS tag, t.idfluxostatus
                    FROM tag t
                    JOIN tagtipo tt ON (tt.idtagtipo = t.idtagtipo AND tt.calendario = 'Y')
                    JOIN empresa e ON(e.idempresa = t.idempresa)
                    WHERE t.status = 'ATIVO' AND t.idtagclass = ?idtagclass?
                        ?getidempresa?
                    UNION  
                    SELECT t.idtag, t.descricao, CONCAT(e.sigla,'-', t.tag) AS tag, t.idfluxostatus
                    FROM tag t 
                    JOIN empresa e ON(e.idempresa = t.idempresa)
                    JOIN tagtipo tt ON (tt.idtagtipo = t.idtagtipo AND tt.calendario = 'Y')
                    WHERE t.status = 'ATIVO' AND t.idtagclass =?idtagclass?
                ) AS a
                JOIN tagclass tg on(tg.idtagclass = ?idtagclass?)
                JOIN fluxostatus fs ON(fs.idfluxostatus = a.idfluxostatus)
                JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
                WHERE cs.statustipo != 'LOCADO'
                AND tg.calendario = '?calendario?'
                GROUP BY a.idtag
                ORDER BY a.descricao;";
    }

    public static function buscarTagsPorIdunidadeEIdEmpresa()
    {
        return "SELECT t.idtag, t.idempresa, t.tag, t.descricao, e.sigla
                FROM tag t
                JOIN empresa e ON(e.idempresa = t.idempresa)
                WHERE t.idunidade = ?idunidade?
                AND t.idempresa = ?idempresa?
                ORDER BY t.descricao";
    }

    public static function buscarTagsDisponiveisParaVinculoEmUnidades()
    {
        return "SELECT t.idtag, t.tag, t.idunidade, t.descricao, e.sigla
                FROM tag t
                JOIN empresa e ON(e.idempresa = t.idempresa)
                WHERE (t.idunidade is null OR t.idunidade = '') 
                AND t.status NOT IN('INATIVO', 'LOCADO')
                AND t.idempresa = ?idempresa?
                ORDER BY t.descricao";
    }

    public static function buscarTagPorIdTagClassEIdEmpresa()
    {
        return "SELECT tc.idtagclass, tc.tagclass, t.idtag, t.tag, e.sigla, t.descricao
                FROM tagclass tc
                JOIN tag t ON(tc.idtagclass = t.idtagclass)
                JOIN empresa e ON(e.idempresa = t.idempresa)
                WHERE tc.idtagclass in (?idtagclass?)
                AND t.idunidade = ?idunidade?
                AND t.idempresa = ?idempresa?
                AND t.status != 'INATIVO'";
    }

    public static function inserirTagHistorico()
    {
        return "INSERT INTO taghistorico (idempresa, 
											 idtag, 
											 campo, 
											 campovalue, 
											 datainicio, 
											 datafim, 
											 criadopor, 
											 criadoem,
                                             alteradopor,
                                             alteradoem) 
									VALUES ('?idempresa?', 
											'?idtag?', 
											'?campo?', 
											'?campovalue?', 
											'?datainicio?', 
											'?datafim?', 
											'?usuario?', 
                                            NOW(),
                                            '?usuario?',
											NOW());";
    }

    public static function buscarHistoricoTag()
    {
        return "SELECT idtaghistorico FROM taghistorico WHERE idtag = '?idtag?' AND (datafim IS NULL OR datafim = '0000-00-00 00:00:00')";
    }


    public static function updateHistoricoTag()
    {
        return "UPDATE taghistorico SET datafim =  NOW() WHERE idtaghistorico = ?idtaghistorico?";
    }

    public static function listarHistoricoTagVeiculo()
    {
        return "SELECT th.idtaghistorico,                       
                       th.datainicio,
                       th.datafim,
                       p.idpessoa,
                       IFNULL(p.nomecurto, p.nome) AS nome,
                       t.placa
                FROM taghistorico th JOIN pessoa p ON p.idpessoa = th.campovalue AND campo = 'funcionario'
                JOIN tag t ON t.idtag = th.idtag
                WHERE th.idtag = '?idtag?'";
    }

    public static function buscarTagPorIdTagClass()
    {
        return "SELECT t.idtag,
                       t.descricao
                  FROM tag t 
                  WHERE t.idtagclass = '?idtagclass?'
               ORDER BY t.descricao";
    }

    public static function buscarTagsDisponiveisParaVinculo()
    {
        return "SELECT t.idtag, t.descricao, t.idtagclass
                  FROM tag t
                 WHERE t.status NOT IN ('INATIVO', 'LOCADO')
                   AND t.idtagclass IS NOT NULL
                   AND t.idempresa = ?idempresa?
              ORDER BY t.descricao";
    }

    public static function iniciarViagem()
    {
        return "INSERT INTO controleviagem (kminicial, idtag, status, idempresa, datainicioviagem, criadopor, criadoem)
                VALUES (?kminicial?, ?idtag?, 'Em andamento', ?idempresa?, now(), '?usuario?', now());";
    }

    public static function buscarPrateleiras() {
        return "SELECT t.idtag, CONCAT(e.sigla, '-', t.descricao) as descricao
                from tag t
                join empresa e on e.idempresa = t.idempresa
                join unidade u on u.idunidade = t.idunidade 
                where idtagclass = 4
                and t.idempresa = ?idempresa?
                and t.status = 'ATIVO'";
    }
}

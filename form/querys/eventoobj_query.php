<?

require_once(__DIR__."/_iquery.php");

class EventoObjQuery implements DefaultQuery
{
    public static $table = 'eventoobj';
    public static $pk = 'ideventoobj';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function inserir()
    {
        return "INSERT INTO eventoobj (
                    idevento, ideventoadd, idobjeto, objeto, idempresa, minimo,
                    maximo,  atual,  resultado,  obs,  conclusao,  status, criadopor, criadoem,
                    datainicio, alteradopor, alteradoem, 
                    datafim,  horainicio,  horafim,  ord
                ) values (
                    ?idevento?, ?ideventoadd?, ?idobjeto?, '?objeto?', ?idempresa?, '?minimo?',
                    '?maximo?',  '?atual?',  '?resultado?',  '?obs?',  '?conclusao?',  '?status?', '?criadopor?',
                    '?criadoem?', ?datainicio?, '?alteradopor?', '?alteradoem?',
                    ?datafim?,  ?horainicio?,  ?horafim?,  ?ord?
                );";
    }

    public static function inserirPorSolmat()
    {
        return "INSERT INTO eventoobj (
                    idevento, ideventoadd, idobjeto, objeto, idempresa, minimo,
                    maximo,  atual,  resultado,  obs,  conclusao,  status, criadopor, criadoem,
                    datainicio, alteradopor, alteradoem, 
                    datafim,  horainicio,  horafim,  ord
                )
                select ?idevento?, ?ideventoadd?, ?idobjeto?, '?objeto?', ?idempresa?, '?minimo?',
                    '?maximo?',  '?atual?',  '?resultado?',  '?obs?',  '?conclusao?',  '?status?', '?criadopor?',
                    ?criadoem?, ?datainicio?, '?alteradopor?', ?alteradoem?,
                    ?datafim?,  ?horainicio?,  ?horafim?,  ?ord?
                where not exists (
                    SELECT 1
                    FROM eventoobj eo
                    WHERE eo.idevento = ?idevento?
                    and eo.ideventoadd = ?ideventoadd?
                    and eo.objeto = 'solmat'
                    and eo.idobjeto = ?idobjeto?
                );";
    }
    
    public static function verificarSePsssoaEstaNoEvento()
    {
        return "SELECT eo.idevento
                FROM eventoobj eo 
                JOIN fluxostatuspessoa r ON r.idmodulo = eo.idevento AND r.modulo = 'evento'
                AND r.idobjeto = ?idpessoa? and r.tipoobjeto = 'pessoa'
                WHERE eo.ideventoobj = '?idmodulo?';";
    }

    public static  function buscarEventoFilhoPorIdModulo()
    {
        return "SELECT *
                FROM eventoobj eo 
                WHERE ideventoobj = '?idmodulo?'";
    }

    public static function buscarCorPorIdEventoEIdEventoObj()
    {
        return "SELECT e.status,
                        e.idevento,
                        ideventoobj,
                        es.rotulo,
                        es.cor
                FROM eventoobj eo 
                LEFT JOIN evento e ON e.modulo = 'eventoobj' AND e.idmodulo = eo.ideventoobj
                LEFT JOIN fluxostatus fs ON fs.idfluxostatus = e.idfluxostatus
                LEFT JOIN "._DBCARBON."._status es ON es.idstatus = fs.idstatus
                WHERE eo.idevento = ?idevento?
                AND eo.ideventoobj = '?ideventoobj?'
                limit 1;";
    }

    public static function buscarCamposObjPorIdEventoEIdEventoAdd()
    {
        return "SELECT *
                FROM eventoobj 
                WHERE idevento = '?idevento?'
                AND ideventoadd = '?ideventoadd?'
                AND objeto = '?objeto?'";
    }

    public static function buscarTipoObjetoDasTagsPorIdEventoAddEIdEvento()
    {
        return "SELECT eo.objeto, t.idtag, t.padraotempmin, t.padraotempmax
                FROM eventoobj eo 
                JOIN tag t ON eo.idobjeto = t.idtag and eo.objeto = 'tag'
                WHERE ideventoadd = '?ideventoadd?'
                AND idevento = '?idevento?'";
    }

    public static function buscarStatusDoUsuarioNoEventoPorIdPessoaEIdModulo()
    {
        return "SELECT eo.idevento
                FROM eventoobj eo
                JOIN fluxostatuspessoa r ON r.idmodulo = eo.idevento AND r.modulo = 'evento'
                AND r.idobjeto = ?idpessoa? and r.tipoobjeto = 'pessoa'
                WHERE eo.ideventoobj = '?idmodulo?'";
    }

    public static function buscarEventoObjPorIdEvento()
    {
        return "SELECT * FROM eventoobj WHERE idevento = '?idevento?';";
    }

    public static function deletarEventoObjPorIdEventoObj()
    {
        return "DELETE FROM eventoobj where ideventoobj = ?ideventoobj?";
    }

    public static function deletarEventosPorRangeDeDataEIdEventoPai()
    {
        return "DELETE o.*
                FROM eventoobj o,evento e 
                where e.status is null 
                AND e.idevento = o.idevento 
                AND e.ideventopai = ?ideventopai?
                AND (e.inicio < '?inicio?' or e.fim > '?fim?')";
    }

    public static function deletarEventosForaDoRangeDeDataEIdEventoPai()
    {
        return "DELETE o.*
                FROM eventoobj o,evento e 
                where e.status is null 
                AND e.idevento = o.idevento 
                AND e.ideventopai = ?ideventopai?
                AND (e.inicio > '?inicio?' or e.fim < '?fim?')";
    }

    public static function atualizarValorMinimoMaximoTag()
    {
        return "UPDATE eventoobj 
                SET minimo = '?minimo?', 
                    maximo = '?maximo?'
                WHERE idevento = '?idevento?'
                AND ideventoadd = '?ideventoadd?'
                AND idobjeto = '?idobjeto?'
                AND objeto = 'tag'";
    }

    public static function removerEventoObjVinculadoEvendoAdd()
    {
        return "DELETE FROM eventoobj WHERE (ideventoadd = ?ideventoadd?);";
    }

    public static function buscarSolmatVinculada()
    {
        return "SELECT s.idsolmat, s.status, uo.unidade as unidadeOrigem, ud.unidade as unidadeDestino, p.nomecurto, s.idempresa, s.criadoem, eo.ideventoadd
                FROM eventoobj eo
                JOIN solmat s on s.idsolmat = eo.idobjeto and eo.objeto = 'solmat'
                JOIN unidade uo on uo.idunidade = s.unidade
                JOIN unidade ud on ud.idunidade = s.idunidade
                LEFT JOIN pessoa p on p.usuario = s.criadopor
                WHERE eo.idevento = '?idevento?'
                AND eo.ideventoadd = '?ideventoadd?'";
    }
}

?>
<?
require_once(__DIR__."/_iquery.php");


class _LpobjetoQuery implements DefaultQuery{
    public static $table = _DBCARBON.'_lpobjeto';
    public static $pk = 'idlpobjeto';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarPorEmpresa(){
        return "SELECT 
                    l.idlpobjeto, e.idempresa, e.nomefantasia as empresa
                FROM empresa e
                    LEFT JOIN
                        carbonnovo._lpobjeto l ON (l.idobjeto = e.idempresa AND l.tipoobjeto = 'empresa' AND l.idlp = ?idlp?)
                WHERE
                    e.status = 'ATIVO'
                    and e.idempresa != ?idempresa? order by empresa";
    }

    public static function inserirObjeto(){
        return "INSERT INTO carbonnovo._lpobjeto (idlp, idobjeto, tipoobjeto, criadopor, criadoem, alteradopor, alteradoem)
                VALUES (?idlp?, ?idobjeto?, '?tipoobjeto?', '?criadopor?', '?criadoem?', '?alteradopor?', '?alteradoem?')";
    }

    public static function apagarObjetoVinculadosNaLp(){
        return "DELETE FROM carbonnovo._lpobjeto where tipoobjeto = '?tipoobjeto?' and idlp = ?idlp?";
    }

    public static function buscarIdlpobjetoPorIdobjetoTipoobjetoIdlp(){
        return "SELECT idlpobjeto from carbonnovo._lpobjeto where 
        tipoobjeto = '?tipoobjeto?'
        and idobjeto = '?idobjeto?'
        and idlp = '?idlp?'
        ";
    }
}

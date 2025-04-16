<?
require_once(__DIR__ . "/_iquery.php");

class EntregaEpi implements DefaultQuery
{

    public static $table = 'entregaepi';
    public static $pk = 'identregaepi';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscaDadosColaborador()
    {
        return "SELECT  p.*,
					e.idempresa,
                    e.razaosocial as empresa,
					c.cargo, 
					s.setor,
					po.idobjeto,
					s.setor
			FROM pessoa p
			left JOIN empresa e on p.idempresa = e.idempresa
			left JOIN sgcargo c on p.idsgcargo = c.idsgcargo
			LEFT JOIN pessoaobjeto po ON p.idpessoa = po.idpessoa
			LEFT JOIN sgsetor s ON s.status = 'ATIVO' and s.idsgsetor = po.idobjeto
			where p.idpessoa = ?idpessoa?;";
    }

    public static function buscaSolmatDisponivel()
    {
        return "SELECT s.idsolmat 
                        FROM solmat s
                    JOIN solmatitem i ON s.idsolmat = i.idsolmat
                    JOIN lotecons c ON (
                        c.tipoobjetoconsumoespec = 'solmatitem'
                        AND c.idobjetoconsumoespec = i.idsolmatitem
                        AND c.tipoobjeto = 'lotefracao'
                    )
                    JOIN lote l ON (l.idlote = c.idlote)
                    JOIN lotefracao f ON (f.idlotefracao = c.idobjeto)
                WHERE
                    s.status = 'CONCLUIDO'
                    AND l.certificadoepi > 0 
                    AND s.idempresa = ?idempresa?
                ORDER BY s.idsolmat DESC;";
    }
}

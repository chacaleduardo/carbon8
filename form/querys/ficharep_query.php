<?

require_once(__DIR__."/_iquery.php");

class FicharepQuery implements DefaultQuery {

    public static $table = "ficharep";
	public static $pk = "idficharep";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table'=>self::$table,'pk'=>self::$pk]) ;
	}

    public static function buscarFichaRepELote(){
        return "SELECT f.idficharep,
                        concat(f.idficharep,'-',l.partida,'/',l.exercicio) as ficharep
                from ficharep f,lote l 
                where f.idficharep = ?idficharep?
                    and f.idlote = l.idlote";
    }

    public static function buscarBioensaiosPorFicharep(){
        return "SELECT 
                    e.idbioensaio,
                    e.exercicio,
                    e.idregistro,
                    IFNULL(e.qtd, 0) AS bqtd,
                    e.status,
                    a.idanalise,
                    a.qtd,
                    a.idbioterioanalise,
                    a.datadzero
                FROM bioensaio e
                left join analise a on (a.idobjeto = e.idbioensaio AND a.objeto = 'bioensaio')
                LEFT JOIN bioterioanalise ba ON (ba.idbioterioanalise = a.idbioterioanalise and ba.cria='Y')
                WHERE e.idficharep = ?idficharep?
                ORDER BY e.idregistro";
    }
}
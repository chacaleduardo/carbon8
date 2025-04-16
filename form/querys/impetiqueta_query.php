<?
require_once(__DIR__."/_iquery.php");

class ImpetiquetaQuery implements DefaultQuery{

    public static $table = "impetiqueta";
	public static $pk = "idimpetiqueta";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
	}

    public static function atualizarImpetiqueta(){
        return "INSERT into impetiqueta
        (idempresa,idamostra,idresultado,idprodserv,versao,criadopor,criadoem)
        (SELECT ?idempresa?,?idamostra?,r.idresultado,r.idtipoteste,
            ((SELECT COUNT(*) FROM impetiqueta e WHERE e.idresultado = r.idresultado)+1) AS versao,
                '?user?',sysdate()
        FROM resultado r
        WHERE  r.impetiqueta='Y'
        AND r.idamostra = ?idamostra?)";
    }

    public static function buscarImpetiquetaComCodprodserv(){
        return "SELECT
                    p.codprodserv
                    ,e.*
                from  impetiqueta e 
                left JOIN resultado r on (e.idresultado = r.idresultado)
                left join prodserv p on (p.idprodserv = e.idprodserv)
                where e.status='ATIVO'
                and e.idamostra =  ?idamostra?
                order by e.criadoem desc";
    }
}

?>
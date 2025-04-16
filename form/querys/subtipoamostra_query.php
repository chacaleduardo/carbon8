<?
require_once(__DIR__."/_iquery.php");

class SubtipoAmostraQuery implements DefaultQuery{

	public static $table = "subtipoamostra";
	public static $pk = "idsubtipoamostra";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
	}

	public static function buscarSubtipoamostraPorIdunidade(){
		return "SELECT o.idunidade,
                        s.idsubtipoamostra,
                        s.normativa,
                        s.subtipoamostra as tiposubtipo
                FROM subtipoamostra s 
                    JOIN unidadeobjeto o on (o.tipoobjeto='subtipoamostra' and o.idunidade = ?idunidade? and o.idobjeto = s.idsubtipoamostra)
                WHERE  s.status='ATIVO'
                ORDER BY tiposubtipo";
	}

    public static function buscarSubtipoamostraPorIdEmpresa()
    {
        return "SELECT idsubtipoamostra, subtipoamostra
                  FROM subtipoamostra
                 WHERE 1
                 ?getidempresa?
              ORDER BY subtipoamostra";
    }

    public static function buscarSubtipoamostraEmpresaPorIdEmpresa()
    {
        return "SELECT idsubtipoamostra,
                       CONCAT(e.sigla, ' - ', subtipoamostra) AS subtipoamostra
                  FROM subtipoamostra JOIN empresa e ON e.idempresa = subtipoamostra.idempresa
                 WHERE 1
                 ?getidempresa?
             ORDER BY subtipoamostra";
    }

    public static function buscarSubtipoamostraPorIdPrativ()
    {
        return "SELECT p.*, subtipoamostra
                  FROM prativ p LEFT JOIN subtipoamostra s ON (s.idsubtipoamostra = p.idsubtipoamostra) WHERE idprativ = ?idprativ?";
    }

    public static function buscarExigenciaConferenciaAmostra()
    {
        return "SELECT sa.conferencia
                  FROM subtipoamostra sa
                  JOIN amostra a on (a.idsubtipoamostra = sa.idsubtipoamostra AND a.status in ('ABERTO','PROVISORIO'))
                  WHERE a.dataamostra >'2024-05-07'
                  AND a.idamostra = ?idamostra?";
    }
}

?>
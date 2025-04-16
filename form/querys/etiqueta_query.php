<?
require_once(__DIR__."/_iquery.php");


class EtiquetaQuery implements DefaultQuery{
    public static $table = 'etiqueta';
    public static $pk = 'idetiqueta';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function adicionarEtiquetasAoModulo(){
        return "SELECT e.rotuloetiqueta,
                        e.idetiqueta
                FROM etiqueta e
                WHERE e.status='ATIVO'
                    AND NOT EXISTS (SELECT 1
                                    FROM etiquetaobjeto ov
                                    WHERE ov.tipoobjeto='modulo' 
		                                AND ov.idobjeto=?idmodulo?
                                        AND ov.idetiqueta=e.idetiqueta)";
    }

    public static function buscarEtiquetasVinculadasAoModulo(){
        return "SELECT e.*,
                        ov.idetiquetaobjeto,
                        ov.grupo
                FROM etiquetaobjeto ov
                    JOIN etiqueta e on (e.idetiqueta = ov.idetiqueta)
                WHERE ov.tipoobjeto = 'modulo'
                    AND ov.idobjeto = ?idmodulo?";
    }
}
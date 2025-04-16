<?
require_once(__DIR__."/_iquery.php");

class ControleImpressaoItemQuery implements DefaultQuery
{
    public static $table = 'controleimpressaitem';
    public static $pk = 'idcontroleimpressaitem';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarItensPorControleDeImpressao(){
        return "SELECT c.idcontroleimpressaoitem,
                        a.idregistro,
                        a.exercicio,
                        c.status,
                        c.via,
                        c.oficial,
                        c.criadopor,
                        dmahms(c.criadoem) as criadoem,
                        p.descr
                FROM controleimpressaoitem c,resultado r,amostra a,prodserv p
                where p.idprodserv = r.idtipoteste
                    and a.idamostra = r.idamostra
                    and r.idresultado = c.idresultado
                    and c.idcontroleimpressao =?idcontroleimpressao?
                order by via";
    }
}
?>
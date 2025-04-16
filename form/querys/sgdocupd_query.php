<?
require_once(__DIR__.'/_iquery.php');

class SgdocupdQuery implements DefaultQuery{
    public static $table = 'sgdocupd';
    public static $pk = 'idsgdocupd';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarHistoricoDeVersoes(){
        return "SELECT dmahms(a.alteradoem) AS dmadata,
                        a.idsgdocupd,
                        a.idempresa,
                        a.versao,
                        a.revisao,
                        a.acompversao
                FROM sgdocupd a
                WHERE a.idsgdoc = ?idsgdoc?
                GROUP BY versao
                ORDER BY a.idsgdocupd DESC";
    }

    public static function buscarUltimaVersaoAprovada(){
        return "SELECT p.nome,
                        DATE_FORMAT(sg.alteradoem,'%d/%m/%Y %T') as alteradoem
                FROM sgdocupd sg
                    join pessoa p on(p.usuario = sg.alteradopor)
                WHERE sg.idsgdoc = ?idsgdoc?
                        and sg.versao=?versao?
                        and sg.status = 'APROVADO'
                LIMIT 1";
    }
}
?>
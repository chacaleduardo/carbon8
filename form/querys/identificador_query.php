<?
class IdentificadorQuery {

    public static function buscarIdentificacaoAmostra(){
        return "SELECT 
            id.identificacao, id.ididentificador
        FROM
            amostra AS a
                JOIN
            identificador id ON (id.idobjeto = a.idamostra)
        WHERE
            a.idamostra  = ?idamostra?";
    }

    public static function buscarIdentificadorPorIdobjetoTipoobjeto(){
        return "SELECT *
                FROM identificador
                WHERE  tipoobjeto='?tipoobjeto?'
                AND idobjeto = ?idobjeto?
                ORDER BY ididentificador";
    }

    public static function inserirIdentificador(){
        return "INSERT into identificador (
			idempresa,idobjeto,tipoobjeto,identificacao,criadopor,criadoem,alteradopor,alteradoem
		)values (
			?idempresa?,?idamostra?,'amostra', '?identificacao?','?usuario?',now(),'?usuario?',now()
		)";
    }


}
?>
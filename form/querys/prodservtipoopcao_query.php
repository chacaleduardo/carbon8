<?
class ProdservTipoOpcaoQuery {

    public static function buscarValorProdservTipoOpcao(){
        return "SELECT
                    (valor * 1) AS valor
                FROM
                    prodservtipoopcao
                WHERE
                    idprodserv = ?idtipoteste?
                ORDER BY
                    valor*1, valor";
    }

    public static function buscarValorProdservTipoOpcaoResultado(){
        return "SELECT 
            @i:=@i + 1 AS num, (valor * 1) AS valor
        FROM
            prodservtipoopcao,
            (SELECT @i:=0) AS foo
        WHERE
            idprodserv = ?idtipoteste?
        ORDER BY valor * 1";
    }

    public static function buscarCampoDescritivo(){
        return "SELECT 
            valor AS num, valor AS valor
        FROM
            prodservtipoopcao
        WHERE
            idprodserv = ?idtipoteste?
        ORDER BY 1 * valor";
    }

    public static function buscarValorProdservTipoOpcaoPorValorEIdprodserv(){
        return "SELECT valor as  xresultado
                 FROM prodservtipoopcao
                where valor = '?valor?'
                    and idprodserv = '?idprodserv?'";
    }

    public static function listarProdservTipoOpcaoPorIdprodserv(){
        return "SELECT valor,
                       idprodservtipoopcao,
                       criadopor,
                       criadoem
                 FROM prodservtipoopcao
                WHERE idprodserv = '?idprodserv?'
             ORDER BY valor*1, valor";
    }
}
?>
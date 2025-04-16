<?
class ProdservVinculoQuery {

    public static function buscarProdservVinculada(){
        return "SELECT pv.idobjeto 
                FROM prodservvinculo pv
                WHERE pv.tipoobjeto = 'prodserv' 
                    AND pv.alerta = 'N'
                    AND pv.idprodserv = '?idprodserv?' AND NOT EXISTS (SELECT 1 FROM amostra a JOIN resultado r ON r.idamostra = a.idamostra 
                                                                                    WHERE a.idamostra = '?idamostra?' AND r.idtipoteste = pv.idobjeto);";
    }
}
?>

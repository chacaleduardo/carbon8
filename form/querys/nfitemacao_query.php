<?
class NfItemAcaoQuery
{
    public static function buscarNfItemAcao(){
        return "SELECT * FROM nfitemacao n WHERE n.idnfitem = ?idnfitem?";
    }

    public static function buscarUltimoValor(){
        return "SELECT * FROM nfitemacao WHERE idobjeto = ?idobjeto? AND tipoobjeto = '?tipoobjeto?' ORDER BY idnfitemacao DESC LIMIT 1;";
    }

    public static function inserirNfItemAcao(){
        return "INSERT INTO nfitemacao (idnfitem, 
                                        idobjeto, 
                                        tipoobjeto, 
                                        idempresa, 
                                        categoria, 
                                        kmrodados, 
                                        status, 
                                        criadopor, 
                                        criadoem, 
                                        alteradopor, 
                                        alteradoem) 
                                VALUES ('?idnfitem?', 
                                        '?idobjeto?', 
                                        '?tipoobjeto?', 
                                        '?idempresa?', 
                                        '?categoria?', 
                                        '?kmrodados?', 
                                        '?status?', 
                                        '?usuario?', 
                                        NOW(), 
                                        '?usuario?', 
                                        NOW());";
    }
}
?>
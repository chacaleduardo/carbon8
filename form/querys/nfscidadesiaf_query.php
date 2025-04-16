<?
class NfscidadesiafQuery{
    public static function montarConsultaParaFillselect(){
        return "SELECT cidade,cidade FROM nfscidadesiaf order by cidade;";
    }

    public static function buscarCidadePorEstado(){
        return "SELECT cidade,
                        cidade as Cidade
                FROM nfscidadesiaf 
                where uf ='?uf?'";
    }
}
?>
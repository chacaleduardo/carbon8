<?
class ProdservCustoQuery {
  
    public static function buscarCustosPorIdprodserv(){
        return "SELECT * from prodservcusto where idprodserv = ?idprodserv?";
    }
    
}
?>
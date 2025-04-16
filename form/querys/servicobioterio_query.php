<?
class ServicoBioterioQuery {

    public static function buscarServicosAtivos(){
        return "SELECT idservicobioterio,rotulo FROM servicobioterio where  status ='ATIVO' order by rotulo";
    }
}
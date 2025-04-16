<?
class ControleTituloQuery{
	
    public static function buscarTitulosPorTeste(){
        return "SELECT *
                from controletitulo
                where idcontroleteste = ?idcontroleteste?
                order by idcontroletitulo";
    }
    
}?>
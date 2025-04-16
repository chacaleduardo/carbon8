<?
class ControleTesteQuery{
	
    public static function buscarMediaControles(){
        return "SELECT i.idcontroleteste,
                        (sum(i.titulo)/count(*)) AS media
                from controleteste t,controletitulo i
                where i.idcontroleteste = t.idcontroleteste
                    and t.idtipocontroleteste = 1
                    and t.idcontrole = ?idcontrole?
                group by idcontroleteste";
    }
	
    public static function buscarControlesTeste(){
        return "SELECT c.*,
                        dma(c.data) as dmadata
                from controleteste c
                where c.idtipocontroleteste = ?idtipo?
                        and c.idcontrole = ?idcontrole?
                order by c.idcontroleteste";
    }
    
}?>
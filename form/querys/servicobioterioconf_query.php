<?
class ServicoBioterioConfQuery {

    public static function buscarConfiguracaoDoServico(){
        return "SELECT s.rotulo,
                        a.dia,
                        a.idservicobioterioconf,
                        a.diazero,
                        s.geraamostra,
                        s.idservicobioterio
                FROM servicobioterioconf a
                    left join servicobioterio s on( s.idservicobioterio = a.idservicobioterio)
                where
                    a.idobjeto= ?idbioterioanalise?
                    and a.tipoobjeto='bioterioanalise'
                order by a.dia,s.rotulo";
    }
}
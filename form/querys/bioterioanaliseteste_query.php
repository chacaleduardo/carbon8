<?
class BioterioAnaliseTesteQuery {

    public static function buscarTestesDaConfiguracao(){
        return "SELECT s.descr,
                        a.idbioterioanaliseteste,
                        s.idprodserv
                from bioterioanaliseteste a
                    left join prodserv s on(s.idprodserv = a.idprodserv)
                where a.idservicobioterioconf=?idservicobioterioconf?
                order by s.descr";
    }
}
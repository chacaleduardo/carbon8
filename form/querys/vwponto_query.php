<?
class VwPontoQuery
{
    public static function buscarPontoPorDataEClausula()
    {
        return "select 
                    idpessoa,nome,dataponto,idponto,hora,semana,batida,entsaida,obs,lat,lon
                from vwponto p 
                where data between '?data1?' and '?data2?'
                ?clausula?
                order by nome,hora";
    }

    public static function buscarPontosPendentes()
    {
        return "SELECT DATE_ADD('?data1?', INTERVAL ?intervalo? DAY) as diabusca,
                DATE_FORMAT( DATE_ADD('?data1?', INTERVAL ?intervalo? DAY),'%W') as semana,
                case  when DATE_ADD('?data1?', INTERVAL ?intervalo? DAY) > '?data2?' then 'Y' 
                else 'N' end  as maior";
    }
}
?>
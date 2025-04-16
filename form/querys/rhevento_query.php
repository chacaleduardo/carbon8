<?

class RhEventoQuery
{
    public static function buscarHorasExtrasPendentesAnteriores()
    {
        return "SELECT 
                    ifnull(sum(e.valor),0) as valor,t.evento,SUBSTRING(dma( DATE_SUB('?data?', INTERVAL 6 month)),4,7)  as periodo 
                FROM rhevento e 
                join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                where  e.idrhtipoevento in(6) 
                and e.status='PENDENTE' 
                and e.dataevento < DATE_SUB('?data?', INTERVAL 5 month)
                and e.idpessoa=?idpessoa?
                and e.valor!=0";
    }

    public static function buscarHorasPendentesPorDataEventoEIdPessoaEStatus()
    {
        return "select sum(e.valor) valor,t.evento, MONTH(e.dataevento) as mes 
                from rhevento e 
                join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                where e.idrhtipoevento in(6) 
                and e.status in (?status?)
                and e.dataevento LIKE '?dataevento?%'
                and e.idpessoa=?idpessoa?
                and e.valor!=0 group by mes";
    }

    public static function buscarHorasExtras()
    {
        return "select  sum(e.valor) valor,t.evento, MONTH(e.dataevento) as mes 
                from rhevento e join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
                where  e.idrhtipoevento in(6) 
                and e.status!='INATIVO' 
                and e.dataevento LIKE '?dataevento?%'
                and e.idpessoa=?idpessoa?
                and e.valor!=0 group by mes";
    }
}

?>
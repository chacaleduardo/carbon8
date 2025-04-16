<?

class Vw8LoteConsInsumoQuery
{
    public static function buscarConsumoInsumoPorIdLoteENnfe()
    {
        return "SELECT
                    c.qtdd,
                    c.unpadrao, c.partida, c.descr, t.tipoprodserv,
                    ci.contaitem,'?nnfe?' as nnfe, c.vlrlote,
                    ''  as valor,
                    c.idloteinsumo as idlote, c.idlotecons,p.fabricado, 
                    c.qtdproduzido as qtdprod ,
                    l.unlote,
                    p.idprodserv
                from vw8LoteConsInsumo c
                left join prodserv p on(p.idprodserv=c.idprodserv)
                left join tipoprodserv t on(p.idtipoprodserv = t.idtipoprodserv)
                left join prodservcontaitem pc on(pc.idprodserv = c.idprodserv)
                left join contaitem ci on(ci.idcontaitem =pc.idcontaitem)
                left join lote l on l.idlote = c.idlote
                where l.idlote = ?idlote? 
                order by c.descr";
    }
}

?>
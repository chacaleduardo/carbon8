<? 

class LoteCustoQuery {
    public static function buscarCustoDiretoIndiretoPorIdLote() {
        return "SELECT u.tipocusto,sum(c.valor) as valor, c.idlote
                from lotecusto c 
                join unidade u on(u.idunidade=c.idobjeto and c.tipoobjeto = 'unidade')
                where c.idlote in (?idlote?) 
                group by c.idlote, u.tipocusto
                order by u.tipocusto, c.idlote";
    }
}
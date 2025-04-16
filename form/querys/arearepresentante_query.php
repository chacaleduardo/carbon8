<?

class AreaRepresentanteQuery {
    public static function buscarAreasDisponiveisParaVinculo() {
        return "SELECT ar.*
                from arearepresentante ar
                where ar.status = 'ATIVO'
                and ar.idempresa = ?idempresa?
                and not exists (
                    select 1
                    from pessoaobjeto
                    where idobjeto = ar.idarearepresentante
                    and tipoobjeto = 'arearepresentante'
                    and idpessoa = ?idpessoa?
                );";
    }

    public static function buscarAreasVinculadasPorIdPessoa() {
        return "SELECT po.idpessoaobjeto,ar.idarearepresentante, ar.area
                from arearepresentante ar
                join pessoaobjeto po on po.idobjeto = ar.idarearepresentante and po.tipoobjeto = 'arearepresentante'
                where po.idpessoa = ?idpessoa?";
    }

    public static function buscarGestoresERepresentantes() {
        return "SELECT pc.idpessoacontato, po.idpessoaobjeto, r.idpessoa as idresponsavel, r.nome as responsavel, p.idpessoa as idcliente,p.nome as cliente
                from arearepresentante ar
                join pessoaobjeto po on po.idobjeto = ar.idarearepresentante and po.tipoobjeto = 'arearepresentante'
                join pessoa p on p.idpessoa = po.idpessoa
                left join pessoacontato pc on pc.idpessoa = p.idpessoa
                left join pessoa r on r.idpessoa = pc.idcontato
                where ar.idarearepresentante = ?idarearepresentante?
                and r.idtipopessoa in(12,1)
                and r.idempresa = ?idempresa?
                group by r.idpessoa, p.idpessoa
                order by r.nome";
    }
}

?>
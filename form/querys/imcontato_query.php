<?

class ImContatoQuery
{
    public static function atualizarStatusDeRegistrosQueNaoForamInseridosManualmente()
    {
        return "UPDATE imcontato set status='?status?' where inseridomanualmente='N';";
    }

    public static function atualizarContatoDePessoasQuePossamEstarEmOutraEmpresa()
    {
        return "REPLACE INTO imcontato (idempresa, idimregra, idpessoa, idcontato, objetocontato, inseridomanualmente,criadoem, STATUS, ultimamsg)
                SELECT * FROM (
                    SELECT DISTINCT r.idempresa, 
                        r.idimregra, 
                        gp.idpessoa, 
                        gc.idpessoa AS idcontato, 
                        'pessoa' AS objetocontato, 
                        'N', NOW(), 
                        'A' AS STATUS, 
                        (SELECT ultimamsg FROM imcontato WHERE idpessoa = gp.idpessoa AND idcontato = gc.idpessoa AND objetocontato = 'pessoa' AND gc.idempresa = idempresa) AS ultimamsg
                    FROM imregra r JOIN imgrupopessoa gp ON gp.idimgrupo = r.idobjetoorigem -- TODAS AS PESSOAS
                    JOIN imgrupopessoa gc ON gc.idimgrupo = r.idobjetodestino -- TODAS AS PESSOAS DO GRUPO RELACIONADO
                    WHERE r.status = 'ATIVO' AND r.tiporegra = 'MENSAGEMDIRETA' AND r.tipoobjetoorigem='imgrupo' AND r.tipoobjetodestino = 'imgrupo' 
                    UNION
                    SELECT DISTINCT r.idempresa, 
                        r.idimregra, 
                        gp.idpessoa, 
                        gc.idpessoa AS idcontato, 
                        'pessoa' AS objetocontato, 
                        'N', NOW(), 
                        'A' AS STATUS, 
                        (SELECT ultimamsg FROM imcontato WHERE idpessoa = gp.idpessoa AND idcontato = gc.idpessoa AND objetocontato = 'pessoa' AND gc.idempresa = idempresa) AS ultimamsg
                    FROM imregra r JOIN imgrupopessoa gp ON gp.idimgrupo = r.idobjetodestino -- TODAS AS PESSOAS
                    JOIN imgrupopessoa gc ON gc.idimgrupo = r.idobjetoorigem -- TODAS AS PESSOAS DO GRUPO RELACIONADO
                    WHERE r.status = 'ATIVO' AND r.tiporegra = 'MENSAGEMDIRETA' AND r.tipoobjetoorigem='imgrupo' AND r.tipoobjetodestino = 'imgrupo' 
                ) a
                GROUP BY idpessoa, idcontato";
    }

    public static function inserirOuAtualizarPessoasDoGrupoVinculado()
    {
        return "REPLACE into imcontato (
                    idempresa, idimregra, idpessoa, idcontato, 
                    objetocontato, inseridomanualmente,criadoem,
                    status, ultimamsg
                )
                SELECT r.idempresa, r.idimregra, gp.idpessoa, gc.idimgrupo as idcontato, 'imgrupo' as objetocontato, 'N', now(),if(r.status = 'INATIVO','I', 'A'),
                (
                    SELECT ultimamsg 
                    from imcontato 
                    where idpessoa = gp.idpessoa 
                    and idcontato = gc.idimgrupo 
                    and objetocontato = 'imgrupo' 
                    AND gc.idempresa = idempresa
                ) as ultimamsg
                FROM imregra r
                JOIN imgrupopessoa gp on gp.idimgrupo=r.idobjetoorigem -- TODAS AS PESSOAS
                JOIN imgrupo gc on gc.idimgrupo=r.idobjetodestino -- GRUPOS RELACIONADOS
                where r.status = 'ATIVO' 
                and r.tiporegra='GRUPO' 
                and r.tipoobjetoorigem='imgrupo' 
                and r.tipoobjetodestino = 'imgrupo'";
    }
}

?>
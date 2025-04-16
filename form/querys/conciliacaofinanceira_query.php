<?
class ConciliacaoFinanceiraQuery
{
    public static function buscarLancamentosPorIdConciliacaoFinanceira()
    {
        return "SELECT 
                    i.idconciliacaofinanceiraitem as id,
                    i.idcontapagar,
                    i.descricaofatura as descricaoFatura,
                    i.dataemissaofatura as dataEmissaoFatura,
                    IFNULL(i.totalfatura, 0.0) as totalFatura,
                    IF(
                        i.status = 'APROVADO',
                        i.descricaosistema,
                        IF(
                            cpi.status = 'INATIVO', 
                            null, 
                            IF(
                                cpi.idcontapagaritem is null,
                                null,
                                p.nome
                            )
                        )
                    ) as descricaoSistema, 
                    IF(
                        i.status = 'APROVADO',
                        i.dataemissaosistema,
                        IF(
                            cpi.status = 'INATIVO',
                            null,
                            IF(
                                cpi.idcontapagaritem is null,
                                null,
                                IFNULL(i.dataemissaosistema, nf.dtemissao)
                            )
                        )
                    ) as dataEmissaoSistema,
                    IF(
                        i.status = 'APROVADO',
                        i.totalsistema,
                        IF(
                            cpi.status = 'INATIVO',
                            0.0,
                            IF(
                                cpi.idcontapagaritem is null,
                                0.0,
                                IF(
                                    (cpi.status = 'QUITADO' OR i.status = 'APROVADO'), IFNULL(i.totalsistema, 0.0), IFNULL(cpi.valor, 0.0)
                                )
                            )
                        )
                    ) as totalSistema,
                    i.status,
                    i.indiceorigem as indiceOrigem,
                    i.idpai as idPai,
                    i.idgrupo as idGrupo,
                    i.porcentagem,
                    IF(
						i.status = 'APROVADO',
                        i.match,
                        IF(
							(i.match AND !pai), 
							i.totalFatura = IF(
								cpi.status = 'INATIVO',
								0.00,
								IF(
									(cpi.status = 'QUITADO' OR i.idcontapagaritem is null OR i.status = 'APROVADO'),
									IFNULL(i.totalsistema, 0.0),
									IFNULL(cpi.valor, 0.0)
								)
							), 
							IF(pai, (
								SELECT 
									SUM(IF(i.totalfatura > 0, cff.totalsistema, cff.totalfatura)) = IF(
											cpi.status = 'INATIVO', 0.0, 
											IF(
												(cpi.status = 'QUITADO' OR i.status = 'APROVADO'), IFNULL(IF(i.totalfatura > 0 ,i.totalfatura, i.totalsistema), 0.0), IFNULL(cpi.valor, 0.0)
											)
										)
								FROM conciliacaofinanceiraitem cff
								WHERE cff.idpai = i.idconciliacaofinanceiraitem
							), i.match)
						)
					) as `match`,
                    IF(
						i.status = 'APROVADO',
                        i.idnf,
                        nf.idnf
                    ) as idnf,
                    i.pai,
                    IF(
                        i.status = 'APROVADO',
                        i.idcontapagaritem,
                        IF(
                            cpi.status = 'INATIVO', 
                            null, 
                            cpi.idcontapagaritem
                        )
                    ) as idcontapagaritem,
                    i.mensagem,
                    arq.caminho as anexo
                FROM conciliacaofinanceiraitem i
                LEFT JOIN contapagaritem cpi on cpi.idcontapagaritem = i.idcontapagaritem AND cpi.idcontapagar = i.idcontapagar
                LEFT JOIN nf on nf.idnf = cpi.idobjetoorigem and cpi.tipoobjetoorigem = 'nf'
                LEFT JOIN pessoa p ON p.idpessoa = cpi.idpessoa
                -- No momento a estrutura aceita apenas um anexo
                LEFT JOIN arquivo arq ON arq.idobjeto = nf.idnf AND arq.tipoobjeto = 'nf'
                WHERE i.idconciliacaofinanceira = ?idconciliacaofinanceira?
                AND i.idempresa = ?idempresa?
                GROUP BY i.idconciliacaofinanceiraitem
                ORDER BY i.dataemissaofatura, i.totalfatura, nf.total;";
    }

    public static function removerLancamentos()
    {
        return "DELETE FROM conciliacaofinanceiraitem WHERE idconciliacaofinanceiraitem in (?idconciliacaofinanceiraitem?)";
    }

    public static function removerLancamentosPorIdConciliacaoFinanceira()
    {
        return "DELETE FROM conciliacaofinanceiraitem
        WHERE idconciliacaofinanceira = ?idconciliacaofinanceira?";
    }

    public static function buscarConciliacaoFinananceiraPorIdContaPagar()
    {
        return "SELECT idconciliacaofinanceira, status, idempresa
                FROM conciliacaofinanceira
                WHERE idcontapagar = ?idcontapagar?;";
    }

    public static function adicionarLancamentoPeloComprasApp()
    {
        return "INSERT INTO conciliacaofinanceiraitem(idconciliacaofinanceira, idcontapagaritem, dataemissaosistema, descricaosistema, totalsistema, `status`, idempresa, idcontapagar, criadopor, criadoem, alteradopor, alteradoem)
                SELECT ?idconciliacaofinanceira?, ?idcontapagaritem?, '?dataemissaosistema?', '?descricaosistema?', ?totalsistema?, '?status?', ?idempresa?, ?idcontapagar?, '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
                FROM conciliacaofinanceiraitem
                WHERE idcontapagaritem = ?idcontapagaritem?
                AND idconciliacaofinanceira = ?idconciliacaofinanceira?
                HAVING count(idcontapagaritem) = 0";
    }

    public static function verificarSeExisteConciliacaoPorIdContaItem()
    {
        return "SELECT 1
                FROM conciliacaofinanceira
                WHERE idcontapagar = ?idcontapagar?
                ?clausula?";
    }

    public static function buscarLancamentoPorIdNf()
    {
        return "SELECT cfi.idconciliacaofinanceiraitem, cf.status
                FROM contapagaritem i
                JOIN conciliacaofinanceiraitem cfi on cfi.idcontapagaritem = i.idcontapagaritem
                JOIN conciliacaofinanceira cf on cf.idconciliacaofinanceira = cfi.idconciliacaofinanceira
                WHERE i.idobjetoorigem = ?idnf?
                AND i.tipoobjetoorigem = 'nf'";
    }

    public static function limparMensagemLancamento()
    {
        return "UPDATE conciliacaofinanceiraitem
                SET mensagem = null
                WHERE idconciliacaofinanceiraitem = ?idconciliacaofinanceiraitem?";
    }

    public static function buscarContapagarItemPorIdContaPagarItem()
    {
        return "SELECT ci.idcontapagar, ci.idcontapagaritem, nf.dtemissao, ci.valor, ci.status, p.nome, ci.idempresa
                FROM contapagaritem ci
                JOIN pessoa p ON p.idpessoa = ci.idpessoa
                LEFT JOIN nf on nf.idnf = ci.idobjetoorigem and ci.tipoobjetoorigem = 'nf'
                WHERE ci.idcontapagaritem = ?idcontapagaritem?";
    }
}

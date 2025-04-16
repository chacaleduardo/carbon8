<?
require_once(__DIR__ . "/_iquery.php");

class ResultadoQuery implements DefaultQuery
{

	public static $table = "resultado";
	public static $pk = "idresultado";


	public static function buscarPorChavePrimaria()
	{
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ["table" => self::$table, "pk" => self::$pk]);
	}


	public static function buscarLoteEtiquetaResultado()
	{
		return "SELECT IFNULL(r.loteetiqueta,999999) as loteetiqueta, idamostra
		FROM resultado r
		WHERE 1 ?idempresa?
			AND r.idamostra IN (?idamostra?)
		GROUP BY IFNULL(r.loteetiqueta,999999), idamostra";
	}


	public static function buscarCaminhoArquivoResultadoElisa()
	{
		return "SELECT 
					caminho
				FROM
					arquivo
				WHERE
					idobjeto = ?idresultado?
						AND tipoobjeto = 'resultado'";
	}


	public static function buscarServicosDaAmostra()
	{
		return "SELECT 
					r.idresultado,
					r.idtipoteste,
					t.tipoteste,
					t.sigla,
					r.quantidade quant,
					r.status,
					t.tipogmt,
					r.criadopor,
					DMAHMS(r.criadoem),
					r.alteradopor,
					IF(s.dia IS NULL,
						'',
						CONCAT(' - D', s.dia)) AS rotulo,
					DMAHMS(r.alteradoem) alteradoem,
					se.nome AS secretaria,
					a.idpessoa,
					pv.idprodserv,
					p.codprodserv,
					CASE
						WHEN
							(SELECT 
									COUNT(1)
								FROM
									prodservvinculo pv
								WHERE
									pv.idobjeto = t.idtipoteste) > 0
						THEN
							'VINCULADO'
						WHEN
							(SELECT 
									COUNT(1)
								FROM
									prodservvinculo pv
								WHERE
									pv.idprodserv = t.idtipoteste) > 0
						THEN
							'VINCULO'
						ELSE ''
					END AS 'vinculo',
					IFNULL(pv.idprodserv, t.idtipoteste) AS ordem
				FROM
					resultado r
						JOIN
					vwtipoteste t ON (r.idtipoteste = t.idtipoteste)
						LEFT JOIN
					servicoensaio s ON (r.idservicoensaio = s.idservicoensaio)
						LEFT JOIN
					pessoa se ON (se.idpessoa = r.idsecretaria)
						JOIN
					amostra a ON a.idamostra = r.idamostra
						LEFT JOIN
					prodservvinculo pv ON pv.idobjeto = t.idtipoteste
						AND pv.tipoobjeto = 'prodserv'
						LEFT JOIN
					prodserv p ON p.idprodserv = pv.idprodserv
				WHERE
					r.idamostra = ?idamostra?
						AND r.status not in ('OFFLINE','CANCELADO')
				GROUP BY r.idresultado
				ORDER BY ordem DESC";
	}

	public static function pagSql()
	{
		return "SELECT 
				l.idresultado,
				l.versao,
				l.idfluxostatus,
				l.alerta,
				l.idsecretaria,
				l.tipoalerta,
				l.tipoagente,
				l.idamostra,
				l.idtipoteste,
				l.quantidade,
				l.status,
				l.criadopor,
				DMAHMS(l.criadoem) criadoem,
				l.alteradopor,
				DMAHMS(l.alteradoem) alteradoem,
				l.descritivo,
				l.observacao,
				l.q1,
				l.q2,
				l.q3,
				l.q4,
				l.q5,
				l.q6,
				l.q7,
				l.q8,
				l.q9,
				l.q10,
				l.q11,
				l.q12,
				l.q13,
				l.idt,
				l.gmt,
				l.padrao,
				l.var,
				t.tipoteste,
				t.sigla,
				t.tipogmt,
				t.tipobact,
				l.positividade,
				t.tipoespecial,
				t.tiporelatorio,
				l.idtecnico,
				l.conformidade,
				l.resultadocertanalise,
				l.idservicoensaio,
				p.modelo,
				p.modo,
				p.geraagente,
				p.tipogmt,
				l.tipokit,
				l.custo,
				l.idempresa
			FROM
				resultado l
					JOIN
				vwtipoteste t ON l.idtipoteste = t.idtipoteste
					LEFT JOIN
				prodserv p ON l.idtipoteste = p.idprodserv
			WHERE
				l.idresultado = #pkid
			ORDER BY t.tipoteste";
	}

	public static function buscarJsonConfigJsonResultado()
	{
		return "SELECT 
				jsonconfig, jsonresultado
			FROM
				resultado
			WHERE
				idresultado = ?idresultado?";
	}

	public static function buscarJsonConfigJsonResultadoCongelado()
	{
		return "SELECT 
				jresultado
			FROM
				resultadojson
			WHERE
				idresultado = ?idresultado?";
	}

	public static function buscarJsonConfigJsonResultadoCongeladoVersaoAnterior()
	{
		return "SELECT * From _auditoria where objeto = 'resultadojson' and idobjeto  =  ?idresultado? ";
	}

	public static function buscarNomeArquivoElisaUpload()
	{
		return "SELECT 
			CONCAT(a.idregistro, p.codprodserv) AS nomearqui,
			CONCAT(a.idregistro, p.codprodserv) AS nomearquivortf
		FROM
			resultado r,
			amostra a,
			prodserv p
		WHERE
			p.idprodserv = r.idtipoteste
				AND a.idamostra = r.idamostra
				AND r.idresultado =?idresultado?";
	}

	public static function buscarDataconclusao()
	{
		return "SELECT dataconclusao
				FROM
					resultado
				WHERE
					idresultado = ?idresultado?";
	}

	public static function verificarSeResultadoEInata()
	{
		return "SELECT r.idresultado
				FROM  resultado r
					-- JOIN amostra a ON a.idamostra = r.idamostra 					 
				WHERE r.idempresa = 2
					and r.idresultado = ?idresultado?";
	}

	public static function atualizarInterfrasePorIdresultado()
	{
		return "UPDATE resultado SET interfrase = '?frase?' WHERE idresultado = ?idresultado?";
	}

	public static function atualizarResultadoParaAssinado()
	{
		return "UPDATE resultado SET status = 'ASSINADO' WHERE idresultado = ?idresultado?";
	}

	public static function atualizarEmailsecResultado()
	{
		return "UPDATE resultado SET emailsec = 'A' WHERE idresultado = ?idresultado?";
	}

	public static function atualizarResultadoParaFechado()
	{
		return "UPDATE resultado
				SET 
					status = 'FECHADO',
					idfluxostatus = ?idfluxostatus?,
					alteradoem = now()
				WHERE idresultado = ?idresultado?";
	}

	public static function buscarResultadoAssinado()
	{
		return "SELECT idresultado FROM resultado WHERE idresultado= ?idresultado? and status='ASSINADO'";
	}

	public static function buscarIformacoesProdservPorIdtipoteste()
	{
		return "SELECT
					a.idade,
					p.tipogmt,
					p.modelo,
					p.modo,
					(SELECT g.gmt from gmt g where t.tipoespecial = g.tipogmt AND a.idade = g.idade) as gmt,
					p.idprodserv,
					t.tipoespecial
				FROM
					amostra a
				join
					resultado r on r.idamostra = a.idamostra
				join    
					vwtipoteste t on r.idtipoteste = t.idtipoteste
				join
					prodserv p on p.idprodserv = r.idtipoteste
					WHERE
					r.idresultado = ?idresultado?";
	}

	public static function criarTestes()
	{
		return "INSERT into resultado (
			idempresa,idamostra,idtipoteste,quantidade,idsecretaria,loteetiqueta,npedido,ord,status, idfluxostatus, criadopor,criadoem,alteradopor,alteradoem,cobrar
		)values (
			?idempresa?,?idamostra?,?idtipoteste?,?quantidade?,?idsecretaria?,?loteetiqueta?,'?npedido?',?ord?,'ABERTO', '?idfluxostatus?', '?usuario?',now(),'?usuario?',now(),'?cobrar?'
		)";
	}

	public static function criarTestesBioensaio()
	{
		return "INSERT into resultado (idamostra,idempresa,idtipoteste,idservicoensaio,quantidade,status,idfluxostatus,criadopor,criadoem,alteradopor,alteradoem
		) select ?idamostra?, ?idempresa?,?idprodserv?,?idservicoensaio?,?qtd?,'?status?', '?idfluxostatus?','?usuario',now(),'?usuario?',now()";
	}

	public static function inserirResultadoFormalizacao()
	{
		return "INSERT INTO resultado (idamostra,
									   idtipoteste,
									   idempresa,
									   idfluxostatus,
									   quantidade,
									   status,
									   criadopor,
									   criadoem,
									   alteradopor,
									   alteradoem) 
								VALUE (?idamostra?,
									   ?idtipoteste?,
									   ?idempresa?,
									   ?idfluxostatus?,
									   '?quantidade?',
									   '?status?',
									   '?usuario?',
									   SYSDATE(),
									   '?usuario?',
									   SYSDATE())";
	}

	public static function buscarResultadosAssinadosDaAmostra()
	{
		return "SELECT idresultado,versao from resultado where idamostra= ?idamostra? and status='ASSINADO'";
	}

	public static function fecharResultadosAssinados()
	{
		return "UPDATE resultado set status='FECHADO', idfluxostatus = ?idfluxostatus? where idamostra= ?idamostra? and status='ASSINADO'";
	}

	public static function executaProcessaLote()
	{
		return "SELECT processalote('?idini?','?idfim?', '?idtipoteste?','?status?','?idfluxostatus?','?tipobotao?','?descritivo?','?usuario?') as resultado";
	}

	public static function buscarLotesParaConsumo()
	{
		return "SELECT 
					r.idresultado,(?qtdun?*r.quantidade) as qtdimput,l.idlote
				FROM resultado r,amostra a,prodservformula f, prodservformulains i,lote l
				where r.status in ('ABERTO','PROCESSANDO')
				and a.status not in ('PROVISORIO', 'CANCELADO')
				and r.idtipoteste=?idtipoteste?
				?getidempresa?
				and a.idamostra = r.idamostra
				and a.exercicio = ?exercicio?
				and a.idunidade=1
				and f.ordem = ?ordem?
				and a.idregistro between ?idini? and ?idfim?
				and f.idprodserv = r.idtipoteste
				and i.idprodservformula = f.idprodservformula
				and f.status = 'ATIVO'
				and l.idlote = ?idlote?
				and l.idprodserv = i.idprodserv
				and l.status='APROVADO'
				and i.status='ATIVO'";
	}

	public static function buscarResultadosParaVinculoTag()
	{
		return "SELECT 
					r.idresultado
				FROM resultado r,amostra a
				where r.status in ('ABERTO','PROCESSANDO')
				and a.status not in ('PROVISORIO', 'CANCELADO')
				and r.idtipoteste=?idtipoteste?
				?getidempresa?
				and a.idamostra = r.idamostra
				and a.exercicio = ?exercicio?
				and a.idunidade=1
				and a.idregistro between ?idini? and ?idfim?
				and not exists (select 1 from objetovinculo where idobjeto = r.idresultado and tipoobjeto = 'resultado' and idobjetovinc = ?idtag? and tipoobjetovinc = 'tag')";
	}

	public static function buscarTestesCanceladosPorIdAmostra()
	{
		return "SELECT 
					r.idresultado,p.codprodserv,r.alteradopor,r.alteradoem
				FROM resultado r, amostra a, prodserv p
				where r.status in ('CANCELADO')
					and a.idamostra = r.idamostra
					and r.idtipoteste = p.idprodserv
					and a.idamostra = ?idamostra?";
	}

	public static function buscarResultadosParaConferencia()
	{
		return "SELECT a.*, fh.criadopor as 'fechador'
				FROM vwassinarresultado a 
					LEFT JOIN fluxostatushist fh on (fh.idfluxostatus =a.resultadoidfluxostatus and fh.status = 'PENDENTE' and fh.alteradopor = '?usuario?' and fh.idmodulo = a.idresultado and fh.modulo = 'resultaves')
					 ?clausula?
					and a.conferenciares='Y'
					and a.status = 'FECHADO'
					and not a.resultadoalteradopor = ?idpessoa?
					?getidempresa?
					group by a.idresultado
				ORDER BY exercicio, idregistro, tipoteste";
	}

	public static function buscarFluxoParaResultados()
	{
		return "SELECT fs.idfluxostatus, 
					f.idfluxo,
					(SELECT idfluxostatushist FROM fluxostatushist fh WHERE fh.idmodulo = r.idamostra AND fh.modulo = f.modulo ORDER BY idfluxostatushist DESC LIMIT 1) AS idfluxostatushist,
					fs.ordem,
					s.statustipo,
					s.tipobotao,
					m.modulo,
					r.idamostra
				FROM resultado r
					JOIN amostra a ON (a.idamostra = r.idamostra)
					JOIN unidadeobjeto uo ON (a.idunidade = uo.idunidade AND uo.tipoobjeto = 'modulo')
					JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'resultado' 
					JOIN fluxo f ON (f.modulo = uo.idobjeto AND f.status = 'ATIVO')
					JOIN fluxostatus fs ON (f.idfluxo = fs.idfluxo)
					JOIN carbonnovo._status s ON (fs.idstatus = s.idstatus AND s.statustipo = 'CONFERIDO')
				WHERE r.idresultado = ?idobjeto?";
	}

	public static function atualizarResultadoParaOffline()
	{
		return "UPDATE resultado 
			SET status IN ('OFFLINE','CANCELADO'), 
				idfluxostatus = '?idfluxostatus?' 
			WHERE idresultado = '?idresultado?'";
	}

	public static function buscarResultadosVinculadosAoEnsaio()
	{
		return "SELECT s.idservicoensaio
					,s.dia
					,p.sigla
					,p.tipoteste
					,r.quantidade
					,r.status
					,r.idamostra
					,r.idresultado
					,r.idservicoensaio
					,r.ord
					,r.idtipoteste
					,s.dia
					,a.idunidade
					,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo,
					left(dma(s.data),5) as dataserv
				from resultado r,
					vwtipoteste p,
					servicobioterio sb,
					servicoensaio s,
					amostra a
				where sb.idservicobioterio = s.idservicobioterio
					and r.idamostra = a.idamostra
					and p.idtipoteste  = r.idtipoteste 
					and r.status NOT IN ('OFFLINE','CANCELADO')
					and r.idservicoensaio=s.idservicoensaio
					and s.idservicoensaio= ?idservicoensaio?
				order by s.data,sb.ordem";
	}

	public static function verificarSeExisteResultadoNaAnalise()
	{
		return "SELECT 
					IF((SELECT 
								COUNT(*)
							FROM
								servicoensaio s
									JOIN
								resultado r ON r.idservicoensaio = s.idservicoensaio
									AND r.status NOT IN ('OFFLINE','CANCELADO')
							WHERE
								s.tipoobjeto = 'analise'
									AND s.idobjeto = a.idanalise) > 0,
						1,
						0) AS servicoresultado
				FROM
					analise a
						JOIN
					bioensaio e ON a.idobjeto = e.idbioensaio
						AND a.objeto = 'bioensaio'
						LEFT JOIN
					bioterioanalise b ON (b.idbioterioanalise = a.idbioterioanalise)
				WHERE
					a.idanalise = ?idanalise?";
	}

	public static function apagarResultadosPendentesDaAnalise()
	{
		return "DELETE r.*  
				from servicoensaio s,resultado r
				where s.idobjeto= ?idanalise?
					and s.status = 'PENDENTE'
					and  s.tipoobjeto='analise'
					and r.idservicoensaio = s.idservicoensaio";
	}

	public static function buscarResultadosPorIdServicoEnsaio()
	{
		return "SELECT idresultado FROM resultado WHERE idservicoensaio = ?idservicoensaio?";
	}

	public static function buscarAmostraPorIdResultado()
	{
		return "SELECT a.idamostra,
					   a.idunidade,
					   CONCAT('Registro: ', a.idregistro, '/', a.exercicio) AS descr
				  FROM resultado r INNER JOIN amostra a ON r.idamostra = a.idamostra
				 WHERE r.idresultado = ?idresultado?";
	}

	public static function 	buscarCobrancaResultado()
	{
		return "SELECT 
					c.idnotafiscal, c.status
				FROM
					notafiscalitens i
						JOIN
					notafiscal c ON (c.idnotafiscal = i.idnotafiscal)
				WHERE
					i.idresultado = ?idresultado?";
	}


	public static function buscarResultadosOficiaisParaEnvioDeEmail()
	{
		return "SELECT sb.tipores,
                sb.idpessoa,sb.idsecretaria,sb.idnucleo,sb.exercicio,sb.idempresa,DATE_SUB(NOW(), INTERVAL 30 DAY) as alterado_1,now() as alterado_2
            from 
            (
            select 'TODOS' AS tipores,
                a.idpessoa,s.idpessoa as idsecretaria,a.idnucleo,a.exercicio,a.idempresa
            from    
            (amostra a
            ,resultado r 
            ,pessoa p
            ,pessoa s
            )           
            where p.idpessoa = a.idpessoa 
				and a.idnucleo <> 0
                and s.idpessoa = r.idsecretaria
                and  not exists  (
					select 1 
					from comunicacaoext c
					join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
					where c.tipo = 'EMAILOFICIAL'
					and c.status = 'SUCESSO'
					and i.tipoobjeto = 'resultado'
					and i.idobjeto = r.idresultado
					)  
                and r.status = 'ASSINADO'
                and r.idamostra = a.idamostra 
                and r.idsecretaria != ''
                and (r.alteradoem BETWEEN DATE_SUB(NOW(), INTERVAL 40 DAY) AND NOW()) 
            ) as sb
            
            group by sb.idnucleo,sb.idsecretaria  union all 
            select sb1.tipores, 
            sb1.idpessoa,sb1.idsecretaria,sb1.idnucleo,sb1.exercicio,sb1.idempresa,DATE_SUB(NOW(), INTERVAL 30 DAY) as alterado_1,now() as alterado_2
            from 
            (   
                select 'POS' as tipores,
                a.idpessoa,s.idpessoa as idsecretaria,a.idnucleo,a.exercicio,a.idempresa
                from
                (amostra a
                ,resultado r
                ,pessoa p
                ,pessoa s
                )
                where p.idpessoa = a.idpessoa
				and a.idnucleo <> 0
                and s.idpessoa = r.idsecretaria
				and  not exists  (
					select 1 
					from comunicacaoext c
					join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
					where c.tipo = 'EMAILOFICIALPOS'
					and c.status = 'SUCESSO'
					and i.tipoobjeto = 'resultado'
					and i.idobjeto = r.idresultado
					)         
                and r.status = 'ASSINADO'
                and r.idamostra = a.idamostra             
                and r.alerta = 'Y'
                and r.idsecretaria != ''
                and (r.alteradoem BETWEEN DATE_SUB(NOW(), INTERVAL 40 DAY) AND NOW())
            ) as sb1
            
            group by sb1.idnucleo,sb1.idsecretaria";
	}

	public static function buscarResultadosParaEnvioContatoEmpresa()
	{
		return "SELECT 
                'TODOS' AS tipores, p.idpessoa, a.idnucleo, a.exercicio, a.idempresa, ?alterado_1? as alterado_1, ?alterado_2? as alterado_2, r.idsecretaria
            FROM
                amostra a
                    JOIN
                pessoa p ON (a.idpessoa = p.idpessoa)
                    JOIN
                resultado r ON (a.idamostra = r.idamostra)
            WHERE
                r.status = 'ASSINADO'
                    AND a.idnucleo <> ''
                    AND a.idnucleo <> 0
                    AND (r.alteradoem BETWEEN ?alterado_1? AND ?alterado_2?)
            GROUP BY a.idnucleo 
            UNION ALL
            SELECT 
                'POS' AS tipores, p.idpessoa, a.idnucleo, a.exercicio , a.idempresa, ?alterado_1? as alterado_1, ?alterado_2? as alterado_2, r.idsecretaria
            FROM
                amostra a
                    JOIN
                pessoa p ON (a.idpessoa = p.idpessoa)
                    JOIN
                resultado r ON (a.idamostra = r.idamostra)
            WHERE
                r.alerta = 'Y' AND r.status = 'ASSINADO'
                    AND a.idnucleo <> ''
                    AND a.idnucleo <> 0
                    AND (r.alteradoem BETWEEN ?alterado_1? AND ?alterado_2?)
            GROUP BY a.idnucleo";
	}

	public static function buscarInformacoesResultadoPorIdResultado()
	{
		return "SELECT 
					e.cnpj, 
					e.razaosocial, 
					a.caminho as logo_empresa, 
					e.xlgr as endereco, 
					e.uf, 
					e.xmun as cidade, 
					e.xbairro as bairro,
					e.emailres as email,
					e.TelefonePrestador as telefone,
					e.DDDPrestador as ddd,
					r.jsonresultado,
					IFNULL(r.jsonconfig, ps.jsonconfig) as jsonconfig,
					am.tutor,
					am.paciente,
					CONCAT(pl.plantel, '-',ef.finalidade) as especie,
					am.sexo,
					am.idade,
					am.tipoidade,
					DMA(am.criadoem) as criadoem,
					ps.descr,
					am.responsavel,
					am.idamostra,
					r.idresultado,
					ps.titulotextopadrao,
					ps.textopadrao,
					r.versao,
					rt.idpessoa,
					am.responsavelcolcrmv as crmv,
                    rt.nome as responsaveltecnico,
                    pcrmv.crmv as crmvrt
				from resultado r
				join amostra am on am.idamostra = r.idamostra
                left join carrimbo c on c.idobjeto = r.idresultado and c.tipoobjeto = 'resultado'
                left join pessoa rt on rt.idpessoa = c.idpessoa
                left join pessoacrmv pcrmv on rt.idpessoa = pcrmv.idpessoa
				left join especiefinalidade ef on ef.idespeciefinalidade = am.idespeciefinalidade
                left join plantel pl on pl.idplantel = ef.idplantel
				join empresa e on e.idempresa = r.idempresa
				join arquivo a on a.idempresa = r.idempresa and a.tipoarquivo = 'LOGOSISTEMA'
				join prodserv ps on r.idtipoteste = ps.idprodserv
				where r.idresultado = ?idresultado?
				limit 1";
	}

	public static function buscarPlantelPorIdResultado() {
		return "SELECT idplantel
				from resultado r
				join prodserv p on p.idprodserv = r.idtipoteste
				join plantelobjeto po on po.idobjeto = p.idprodserv and po.tipoobjeto = 'prodserv'
				where r.idresultado = ?idresultado?";
	}


	public static function buscarCustoTeste() {
		return "SELECT 
					ROUND(ifnull(SUM(c.qtdd * l.vlrlote),0), 4) AS custo
				FROM
					lotecons c
						JOIN
					lote l ON (l.idlote = c.idlote AND l.vlrlote > 0)
				WHERE
					c.tipoobjeto = 'resultado'
						AND c.qtdd > 0
						AND c.idobjeto = ?idresultado?";
	}

	public static function atualizarCustoIdresultado()
	{
		return "UPDATE resultado SET custo = '?custo?' WHERE idresultado = ?idresultado?";
	}

	public static function buscarTipoUnidadeAmostra(){

		return "SELECT
					r.idresultado
					FROM
             			resultado r 
					JOIN
						amostra a ON(a.idamostra = r.idamostra)
            		JOIN
						unidade u ON(u.idunidade = a.idunidade AND u.idtipounidade = 7)
            		WHERE
						r.idresultado = '?idresultado?' AND r.idempresa = 2;" ;
	}

	public static function updateStatusResultado(){
		return "UPDATE resultado SET status = 'ASSINADO', idfluxostatus = 845 WHERE idresultado = '?idresultado?'";
	}

	public static function buscaCategoriaIdtipoteste(){
		return "SELECT
					a.idempresa,
					a.idamostra,
					l.idlote,
					la.idloteativ,
					tp.tipoprodserv,
					f.idformalizacao,
					ff.idfluxo AS idfluxoformalizacao,
					fl.idfluxo AS idfluxolote,
					fa.idfluxo AS idfluxoamostra,
					p.fabricado,
					f.status AS statusformalizacao,
					r.status AS statusresultado,
					f.idunidade AS idunidadeformalizacao,
					l.idunidade AS idunidadelote
				FROM
					objetovinculo o
					JOIN
						loteativ la ON (la.idloteativ = o.idobjetovinc)
					JOIN
						resultado r ON(r.idresultado = o.idobjeto AND o.tipoobjeto = 'resultado')
					JOIN
						amostra a ON(a.idamostra = r.idamostra)
					JOIN
						lote l ON(l.idlote = la.idlote)
					JOIN
						formalizacao f ON(f.idlote = l.idlote)
					JOIN
						fluxostatus ff ON(ff.idfluxostatus = f.idfluxostatus)
					JOIN
						fluxostatus fl ON(fl.idfluxostatus = l.idfluxostatus)
					JOIN
						fluxostatus fa ON(fa.idfluxostatus = a.idfluxostatus)
					JOIN
						prodserv p ON(p.idprodserv = l.idprodserv)
					JOIN
						tipoprodserv tp ON(tp.idtipoprodserv = p.idtipoprodserv)
						WHERE
							o.idobjeto = ?idresultado?
						AND
							o.tipoobjetovinc = 'loteativ'";
    }

	public static function buscarConformidadeResultado()
	{
		return "SELECT CASE
							WHEN COUNT(CASE WHEN r.conformidade = 'NAO CONFORME' THEN 1 END) > 0 THEN 'REPROVADO'
							WHEN COUNT(CASE WHEN r.conformidade = 'CONFORME' THEN 1 END) = COUNT(r.idresultado) THEN 'APROVADO'
							ELSE 'PENDENTE'
						END AS status,
						CASE
							WHEN COUNT(CASE WHEN r.status IN('FECHADO' , 'CONFERIDO', 'ASSINADO') THEN 1 END) = COUNT(r.idresultado) THEN 'FECHADO'
							ELSE 'PENDENTE'
						END AS resultamostra
				FROM loteativ la
						JOIN laudo.objetovinculo ov ON (ov.idobjetovinc = la.idloteativ AND tipoobjetovinc = 'loteativ')
						JOIN resultado r ON (r.idresultado = ov.idobjeto)
				WHERE la.nomecurtoativ LIKE '%CONTROLE DE QUALIDADE%'
				AND la.idlote = ?idlote?;";
	}

	public static function buscaModuloPorIdunidadeFormalizacao()
	{
		return "SELECT 
				qry.modulo
				FROM formalizacao f
				JOIN unidade u ON (f.idunidade = u.idunidade)
				join (
					SELECT m.modulo, o.idunidade
					FROM unidadeobjeto o 
					JOIN carbonnovo._modulo m ON (o.tipoobjeto = 'modulo' AND m.modulo = o.idobjeto AND m.modulotipo = '?modulotipo?' AND m.status = 'ATIVO')
				) as qry on qry.idunidade = f.idunidade
				WHERE u.status = 'ATIVO'
				AND f.idformalizacao = ?idtabela?
				AND f.idunidade = ?idunidade?;";
	}

	public static function BuscaModuloPorIdunidadeLote()
	{
		return "SELECT 
				qry.modulo
				FROM lote l 
				join lotefracao f on f.idlote = l.idlote
				JOIN unidade u ON (f.idunidade = u.idunidade)
				join (
					SELECT m.modulo, o.idunidade
					FROM unidadeobjeto o 
					JOIN carbonnovo._modulo m ON (o.tipoobjeto = 'modulo' AND m.modulo = o.idobjeto AND m.modulotipo = '?modulotipo?' AND m.status = 'ATIVO')
				) as qry on qry.idunidade = f.idunidade
				WHERE u.status = 'ATIVO'
				AND l.idlote = ?idtabela?
				AND f.idunidade = ?idunidade?;";
	}
}

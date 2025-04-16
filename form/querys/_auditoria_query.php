<?
require_once(__DIR__ . "/_iquery.php");
class AuditoriaQuery
{
	public static function buscarQuantidadeAuditoriaPorObjetoColunaStatus()
    {
        return "SELECT 1 
				  FROM _auditoria
				 WHERE objeto = '?objeto?'
				   AND idobjeto = '?idobjeto?'
				   ?idempresa?
				   AND coluna = '?coluna?'
				   AND valor = '?valor?'";
    }

	public static function inserirAuditoriaFluxo () {
		return "INSERT INTO _auditoria (idempresa, linha, acao, objeto, idobjeto, coluna, valor, criadoem, criadopor, tela)
			VALUES (?idempresa?, 1, 'u', '?objeto?', ?idobjeto?, 'status', '?valor?', now(), '?criadopor?', '?tela?')";
	}

	public static function inserirRegistroAuditoria()
    {
        return "INSERT INTO `_auditoria`
					(idempresa,
					linha,
					acao,
					objeto,
					idobjeto,
					coluna,
					valor,
					criadoem,
					criadopor,
					tela) 
				values
					(?idempresa?,
					'?linha?',
					'?acao?',
					'?objeto?',
					?idobjeto?,
					'?coluna?',
					'?valor?',
					now(),
					'?usuario?',
					'?HTTP_REFERER?')";
    }
}
?>
<?
require_once(__DIR__.'/_iquery.php');

class EmpresaEmailObjetoQuery
{
    public static function buscarEmpresaEmailObjeto()
    {
        return "SELECT tipoenvio, idobjeto
				  FROM empresaemailobjeto 
				 WHERE tipoobjeto = 'nf' AND idobjeto IN (?idnfs?) 
				 ?idempresa?
			  ORDER BY idempresaemailobjeto DESC";
    }

    public static function buscarEmpresaEmailObjetoOriginalComIdobjeto()
    {
        return "SELECT v.email_original as email
							FROM empresaemailobjeto e
							JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
							WHERE e.tipoenvio = '?tipoenvio?'
								AND e.tipoobjeto = '?tipoobjeto?'
								AND e.idobjeto = ?idobjeto?
								AND e.idempresa = ?idempresa?
								AND v.status = 'ATIVO'
							ORDER BY e.idempresaemailobjeto desc limit 1";
    }
}
?>
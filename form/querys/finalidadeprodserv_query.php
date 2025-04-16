<?
class FinalidadeProdservQuery
{
	public static function buscarFinalidadeProdservPorIdPessoa()
    {
        return "SELECT c.idfinalidadeprodserv, c.finalidadeprodserv, i.idpessoa
				FROM finalidadepessoa i JOIN finalidadeprodserv c ON c.idfinalidadeprodserv = i.idfinalidadeprodserv
				WHERE i.idpessoa IN(?idpessoas?)
				?status?				
				and  exists (select idfinalidadeprodserv from finalidadeempresa fe where fe.idfinalidadeprodserv=c.idfinalidadeprodserv ?idempresa?)
			ORDER BY c.finalidadeprodserv"; 
    }

	public static function buscarFinalidadeProdserv()
    {
        return "SELECT idfinalidadeprodserv, finalidadeprodserv
				  FROM finalidadeprodserv 
				 WHERE status = 'ATIVO'
				 ?idempresa?
			ORDER BY finalidadeprodserv"; 
    }
}

?>
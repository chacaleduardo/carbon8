<?
class NfItemXmlQuery
{
	public static function buscarNfItemXml()
	{
		return "SELECT prodservdescr AS descr,
					   qtd,
					   un,
					   valor,
					   (valor / qtd) AS vlritem,
					   des AS desconto,
					   cst,
					   cfop,
					   aliqicms AS aliq_icms,
					   valicms AS vicms,
					   basecalc,
					   redbc,
					   valipi AS vipi,
					   ipint AS cstipi,
					   bcipi,
					   frete,
					   vst,
					   outro
				  FROM nfitemxml
				 WHERE idnfitemxml = ?idnfitemxml?";
	}
	
	public static function buscarNfItemXmlNfItem()
	{
		return "SELECT x.aliqicms,
					   x.aliqbasecal,
					   x.aliqcofins,
					   x.aliqicmsint,
					   x.aliqpis,
					   x.basecalc,
					   x.cest,
					   x.cfop,
					   x.des,
					   x.frete,
					   x.indiedest,
					   x.aliqipi,
					   x.qtd,
					   x.un,
					   x.prodservdescr,
					   x.cprod,
					   x.ncm,
					   x.modbc,
					   x.confinscst,
					   x.valicms,
					   x.valipi,
					   x.cst,
					   (x.valor / x.qtd) AS vlrliq,
					   x.piscst,
					   x.bccofins,
					   x.bcpis,
					   x.cofins,
					   x.ipint,
					   'V' AS tiponf,
					   '?idnf?' AS idnf,
					   'Y' AS nfe,
					   i.idprodserv,
					   i.idprodservforn,
					   i.idempresa
				  FROM nfitemxml x JOIN nfitem i ON (i.idnfitemxml = x.idnfitemxml)
				 WHERE x.idnfitemxml = ?idnfitemxml?";
	}

	public static function buscarQtdNfItemXml(){
		return "SELECT COUNT(1) as contador FROM nfitemxml WHERE idnf = ?idnf?";
	}

	public static function apagarNfItemXmlPorIdNf()
	{
		return "DELETE FROM nfitemxml WHERE idnf = ?idnf";
	}
}
?>
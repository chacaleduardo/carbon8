<?
require_once(__DIR__."/_iquery.php");

class BioterioAnaliseQuery implements DefaultQuery{

    public static $table = "bioterioanalise";
	public static $pk = "idbioterioanalise";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table'=>self::$table,'pk'=>self::$pk]) ;
	}

	public static function buscarTipoAnalises(){
		return "SELECT idbioterioanalise,
                        tipoanalise
                FROM bioterioanalise
                where
                    status='ATIVO'
                ORDER BY tipoanalise" ;
	}

	public static function buscarTestesDoEnsaio(){
		return "SELECT s.idservicoensaio,
						a.idanalise,
						s.dia,dma(s.data) as dmadata,s.diazero,s.status,sb.rotulo,
						DATEDIFF(s.data,e.nascimento) AS idade,
						DATEDIFF(s.data,a.datadzero) as diabioensaio,
						s.idamostra,ee.idbioensaio,
						sb.idservicobioterio,
						ba.cria
				FROM bioensaio e
					join servicoensaio s
					join servicobioterio sb
					join analise a
					join bioterioanalise ba
					left join analise aa on(aa.idanalise =a.idanalisepai) 
					left join bioensaio ee on(ee.idbioensaio=aa.idobjeto and aa.objeto ='bioensaio')
					left join nucleo n on(n.idnucleo=ee.idnucleo)
				where s.idobjeto=a.idanalise
					and sb.idservicobioterio = s.idservicobioterio 
					and s.tipoobjeto = 'analise'
					and a.idbioterioanalise = ba.idbioterioanalise
					and a.objeto='bioensaio'
					and a.idobjeto =  e.idbioensaio
					and a.idanalise = ?idanalise?
				ORDER BY s.data,sb.rotulo desc" ;
	}

	public static function buscarPorIdEspecieFinalidade() {
		return "SELECT idbioterioanalise, tipoanalise
				from bioterioanalise
				where idespeciefinalidade = ?idespeciefinalidade?
				and idempresa = ?idempresa?
				and status = 'ATIVO'";
	}
}
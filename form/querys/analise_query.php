<?
require_once(__DIR__."/_iquery.php");

class AnaliseQuery implements DefaultQuery{

    public static $table = "analise";
	public static $pk = "idanalise";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table'=>self::$table,'pk'=>self::$pk]) ;
	}

    public static function buscarAnalisesPorIdBioensaio(){
        return "SELECT a.idanalise,
                        a.idbioterioanalise,
                        a.datadzero,
                        e.nascimento,
                        a.idbioensaioctr,
                        a.qtd,
                        a.idanalisepai,
                        b.cria,
                        b.pddose,
                        b.pdvia,
                        b.pdvolume
                FROM analise a
                    join bioensaio e on (a.idobjeto =e.idbioensaio)
                    left join bioterioanalise b on(b.idbioterioanalise=a.idbioterioanalise)
                where a.objeto='bioensaio'
                and a.idobjeto = ?idbioensaio?";
    }

    public static function verificaSeHaAnalisePai(){
        return "SELECT count(*) as qtd
                from analise a,servicoensaio s
                where a.idanalisepai = ?idanalise?
                    and s.idobjeto =a.idanalise
                    and s.tipoobjeto ='analise'";
    }

    public static function buscarBioensaioDaAnalise(){
        return "SELECT concat('B',e.idregistro,'/',e.exercicio,' ',e.idespeciefinalidade) as bioensaio,
                        e.idbioensaio
                from analise a,bioensaio e
                where
                    e.idbioensaio=a.idobjeto
                    and a.objeto ='bioensaio'
                    and a.idanalise = ?idanalise?";
    }

    public static function buscarDatasDosServicos(){
        return "SELECT 
                    DMA(a.datadzero) AS r3dmadata,
                    DMA(a.datadzero) AS r3inicio,
                    DATE_ADD(a.datadzero, INTERVAL 1 DAY) AS r3data,
                    DMA(s.data) AS fdmadata,
                    DMA(s.data) AS finicio,
                    s.data AS fdata,
                    (DATEDIFF(s.data, a.datadzero) + 1) AS diasper,
                    a.qtd
                FROM
                    bioensaio e,
                    servicoensaio s,
                    analise a
                WHERE
                    e.idbioensaio = ?idbioensaio?
                    and a.idanalise = ?idanalise?
                    and s.idobjeto = a.idanalise
                    and s.tipoobjeto = 'analise'
                    order by fdata desc limit 1";
    }

    public static function buscarFimDoEnsaio(){
        return "SELECT  dma(DATE_ADD(datadzero, INTERVAL ?day? DAY)) as datafim
                    from analise 
                where idanalise = ?idanalise?";
    }

    public static function inserirAnalise(){
        return "INSERT INTO analise (idobjeto,objeto,criadopor,criadoem,alteradopor,alteradoem) values (?idobjeto?,'?objeto?','?usuario?',now(),'?usuario?',now())";
    }
    public static function copiarAnalise(){
        return "INSERT INTO analise
        (idempresa,idbioterioanalise,descr,idobjeto,objeto,datadzero,idanalisepai,status,criadopor,criadoem,alteradopor,alteradoem)
        (select idempresa,idbioterioanalise,descr,?idbioensaio?,'bioensaio',datadzero,?idanalise?,status,
            '?usuario?',now(),'?usuario?',now() 
        from analise
        where idanalise = ?idanalise? )";
    }

    public static function atualizarBioterioAnalise(){
        return "UPDATE analise a,analise aa
                        set a.idbioterioanalise= aa.idbioterioanalise,a.datadzero=aa.datadzero 
                WHERE  a.idanalise = ?idanalise?
                        and aa.idanalise=a.idanalisepai";
    }

    public static function apagarAnalise(){
        return "DELETE from analise where  idanalise = ?idanalise?";
    }
}
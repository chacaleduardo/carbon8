<?
require_once(__DIR__."/_iquery.php");

class LocalEnsaioQuery implements DefaultQuery{

    public static $table = "localensaio";
	public static $pk = "idlocalensaio";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table'=>self::$table,'pk'=>self::$pk]) ;
	}

    public static function buscarLocaisEnsaio(){
		return "SELECT idlocalensaio,
                        idlocal,
                        idbioensaio,
                        idanalise,
                        ensaio,
                        idtag
                FROM localensaio le
                WHERE le.idanalise = ?idanalise?" ;
	}

    public static function inserirLocalEnsaio(){
		return "INSERT INTO localensaio (idanalise) values (?idanalise?)" ;
	}

    public static function atualizarTagLocalEnsaio(){
		return "UPDATE localensaio set idtag = ?idtag? where idanalise= ?idanalise?" ;
	}

  public static function finalizaLocalEnsaioPorIdbioensaio(){
		return "UPDATE localensaio e join analise a on(a.idanalise = e.idanalise) join bioensaio b on (a.idobjeto = b.idbioensaio)
                set e.status = 'FINALIZADO'
            where b.idbioensaio = ?idbioensaio?" ;
	}

}
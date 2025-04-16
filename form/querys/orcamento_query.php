<?
require_once(__DIR__ . "/_iquery.php");

class OrcamentoQuery implements DefaultQuery{

    public static $table = "orcamento";
	public static $pk = "idorcamento";

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table' => self::$table,'pk' =>  self::$pk]);
    }

    public static function buscarOrcamentosParaEnvioDeEmail(){
        return "SELECT o.idorcamento,
                        p.nome as cliente,
                        o.email,
                        o.alteradopor,
                        o.formamostra,
                        o.controle,
                        o.alteradopor,
                        o.idempresa
				FROM orcamento o,pessoa p 
				where o.envioemail = 'Y'
				    and o.idpessoa = p.idpessoa";
    }

    public static function atualizarEnvioEmailOrcamento(){
        return "UPDATE orcamento set envioemail = '?envioemail?'  where idorcamento = ?idorcamento?";
    }

    public static function atualizarEnvioEmailOrcamentoComLog(){
        return "UPDATE orcamento set envioemail = 'O', logemail = concat(ifnull(logemail,''),' ?msg? ') where idorcamento = ?idorcamento?";
    }
}
?>

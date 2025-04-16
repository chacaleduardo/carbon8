<?
require_once(__DIR__."/_iquery.php");

class NotaFiscalQuery implements DefaultQuery
{
    public static $table = 'notafiscal';
    public static $pk = 'idnotafiscal';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarNotaFiscalPorNumeroRPS(){
        return "SELECT p.nome,n.nnfe
                from notafiscal n,pessoa p
                where p.idpessoa = n.idpessoa
                and  n.numerorps='?numerorps?'";
    }

    public static function buscarNotaFiscalParaEnvioDetalhe(){
        return "SELECT n.idnotafiscal,p.nome,n.emaildetalhe,n.idpessoa,n.alteradopor,n.idempresa
                FROM notafiscal n,pessoa p
                where p.idpessoa = n.idpessoa
                and n.emaildetalhe is not null
                and n.enviaemaildetalhe='Y'
                and n.status IN ('PENDENTE')";
    }

    public static function atualizarNotafiscal(){
        return "UPDATE notafiscal set ?camposevalores? where idnotafiscal = ?idnotafiscal?";
    }

    public static function buscarNFSParaEnvioDeEmail(){
        return "SELECT n.nnfe
					,n.numerorps,
					dma(n.emissao) as emissao,p.razaosocial,
					n.idnotafiscal,
					n.emailnfe,
					n.idempresa,
					n.enviadetalhenfe,
					n.enviadanfnfe,
					n.emailboleto,
					n.emaildsimplesnac,
					convert( lpad(n.nnfe, '8', '0') using latin1) as numeronfe,
					p.idpessoa,
					left(p.cpfcnpj,8) as ncnpj,
					p.idpessoa,
					n.alteradopor
			FROM notafiscal n,pessoa p
			where p.idpessoa = n.idpessoa
				and (n.enviadetalhenfe = 'Y' or n.enviadanfnfe = 'Y' or emailboleto='Y')
				and n.emailnfe is not null
				and n.enviaemailnfe='Y'
				and n.status = 'CONCLUIDO'";
    }
}
?>
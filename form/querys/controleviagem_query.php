<?

require_once(__DIR__ . "/_iquery.php");

class ControleViagemQuery implements DefaultQuery
{
    public static $table = 'controleviagem';
    public static $pk = 'idcontroleviagem';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarViagem() {
        return "SELECT 
                    v.idcontroleviagem,
                    v.kminicial,
                    v.kmfinal,
                    v.idtag,
                    v.observacao,
                    IFNULL(p.nome, 'Usuário') as condutor,
                    CONCAT(e.sigla, '-', t.tag) as tag, 
                    IFNULL(t.placa, 'Sem placa') as placa,
                    t.descricao
                FROM controleviagem v
                JOIN empresa e ON e.idempresa = v.idempresa
                JOIN tag t on t.idtag = v.idtag
                LEFT JOIN pessoa p on p.usuario = v.criadopor
                WHERE v.idcontroleviagem = ?idcontroleviagem?";
    }

    public static function existeViagemEmAndamentoPorUsuario() {
        return "SELECT 1
                FROM controleviagem
                WHERE status = 'Em andamento'
                AND criadopor = '?usuario?'";
    }
}

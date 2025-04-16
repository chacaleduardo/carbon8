<?
require_once(__DIR__.'/_iquery.php');

class FluxostatushistobsQuery implements DefaultQuery{
    public static $table = 'fluxostatushistobs';
    public static $pk = 'idfluxostatushistobs';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarRestauracoesPorModuloIdmodulo(){
        return "SELECT motivoobs,
                        criadopor,
                        criadoem,
                        versaoorigem,
                        versao 
                FROM fluxostatushistobs 
                WHERE idmodulo = ?idmodulo? 
                    and modulo='?modulo?' ";
    }

    public static function buscarFluxoStatusHistObsPorModulo () {
        return "SELECT fso.idfluxostatushistobs,s.rotulo, fso.motivo, fso.motivoobs, fso.criadoem, p.nome
            FROM fluxostatushistobs fso 
                JOIN fluxostatus fs ON fs.idfluxostatus = fso.idfluxostatus
                JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
                JOIN pessoa p ON p.usuario = fso.criadopor
            WHERE idmodulo = '?idmodulo?'
                AND modulo = '?modulo?'";
    }

    public static function atualizarHistoricoRestaurar () {
        return 'UPDATE fluxostatushistobs set  motivoobs = "?motivoobs?" where idfluxostatushistobs = ?idfluxostatushistobs?';
    }

    public static function inserirFluxoStatusHistObs() {
        return "INSERT INTO `fluxostatushistobs`
            (`idempresa`,
            `idmodulo`,
            `modulo`,
            `motivo`,
            `motivoobs`,
            `status`,
            `idfluxostatus`,
            `criadoem`,
            `criadopor`,
            `alteradoem`,
            `alteradopor`)
            VALUES
            ('?idempresa?',
            '?idmodulo?',
            '?modulo?',
            '?motivo?',
            '?motivoobs?',
            '?status?',
            '?idfluxostatus?',
            sysdate(),
            '?criadopor?',
            sysdate(),
            '?alteradopor?')";
    }

    public static function inserirFluxoStatusHistObsComVersao() {
        return "INSERT INTO `fluxostatushistobs`
            (`idempresa`,
            `idmodulo`,
            `modulo`,
            `motivo`,
            `motivoobs`,
            `status`,
            `idfluxostatus`,
            `versao`,
            `versaoorigem`,
            `criadoem`,
            `criadopor`,
            `alteradoem`,
            `alteradopor`)
            VALUES
            ('?idempresa?',
            '?idmodulo?',
            '?modulo?',
            '?motivo?',
            '?motivoobs?',
            '?status?',
            '?idfluxostatus?',
            '?versao?',
            '?versaoorigem?',
            sysdate(),
            '?criadopor?',
            sysdate(),
            '?alteradopor?')";
    }
}
?>
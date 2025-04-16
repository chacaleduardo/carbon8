<?
class _LpModuloQuery {
    public static function buscarIdLpModuloPorLpeModulo () {
        return "SELECT 
                idlpmodulo
            FROM
                "._DBCARBON."._lpmodulo lm
            WHERE
                lm.idlp = ?idlp?
                    AND lm.modulo = '?modulo?'";
    }

    public static function buscarIdLpModuloPorLpeModuloVinculado () {
        return "SELECT 
                idlpmodulo
            FROM
                "._DBCARBON."._lpmodulo lm
                    JOIN
                "._DBCARBON."._modulo m ON (m.modulo = lm.modulo)
            WHERE
                lm.idlp = ?idlp?
                    AND m.modvinculado = '?modulo?'";
    }

    public static function buscarModulosPorModuloEIdLp()
    {
        return "SELECT * FROM "._DBCARBON."._lpmodulo WHERE modulo = '?modulo?' AND idlp IN (?idlp?)";
    }

    public static function inserirLpModulo () {
        return "INSERT INTO "._DBCARBON."._lpmodulo (idlp,modulo,permissao,solassinatura) VALUES (?idlp?, '?modulo?', '?permissao?', 'X');";
    }

    public static function atualizarPermissaoLpModulo () {
        return "UPDATE "._DBCARBON."._lpmodulo SET permissao = '?permissao?' WHERE idlpmodulo = '?idlpmodulo?'";
    }

    public static function deletarLpModulo () {
        return "DELETE FROM "._DBCARBON."._lpmodulo WHERE idlpmodulo = '?idlpmodulo?'";
    }

    public static function buscarPorModuloEIdLp()
    {
        return "select * from "._DBCARBON."._lpmodulo where modulo ='?modulo?' and idlp in(?idlp?)";
    }
}
?>
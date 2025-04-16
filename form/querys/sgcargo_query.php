<?

class SgcargoQuery
{
    public static function buscarAreasDepsSetoresDisponiveisParaVinculoPorGetIdEmpresa()
    {
        return "SELECT *
            FROM (
            SELECT a.idsgsetor AS id,
                   CONCAT('SETOR - ', a.setor) AS name,
                   'sgsetor' AS tipo
            FROM sgsetor a
            WHERE a.status = 'ATIVO'
            ?getidempresasetor?
            AND NOT EXISTS (
                SELECT 1
                FROM objetovinculo ov
                WHERE ov.idobjetovinc = a.idsgsetor
                AND ov.idobjeto = ?idsgcargo?
                AND ov.tipoobjeto = 'sgcargo'
                AND ov.tipoobjetovinc = 'sgsetor'
            )
            UNION
            SELECT a.idsgarea AS id,
                   CONCAT('AREA - ', a.area) AS name,
                   'sgarea' AS tipo
            FROM sgarea a
            WHERE a.status = 'ATIVO'
            ?getidempresaarea?
            AND NOT EXISTS (
                SELECT 1
                FROM objetovinculo ov
                WHERE ov.idobjetovinc = a.idsgarea
                AND ov.tipoobjeto = 'sgcargo'
                AND ov.idobjeto = ?idsgcargo?
                AND ov.tipoobjetovinc = 'sgarea'
            )
            UNION
            SELECT a.idsgdepartamento AS id,
                   CONCAT('DEPART. - ', a.departamento) AS name,
                   'sgdepartamento' AS tipo
            FROM sgdepartamento a
            WHERE a.status = 'ATIVO'
            ?getidempresadep?
            AND NOT EXISTS (
                SELECT 1
                FROM objetovinculo ov
                WHERE ov.idobjetovinc = a.idsgdepartamento
                AND ov.tipoobjeto = 'sgcargo'
                AND ov.idobjeto = ?idsgcargo?
                AND ov.tipoobjetovinc = 'sgdepartamento'
            )
        ) a
        ORDER BY tipo, name DESC";
    }
}

?>
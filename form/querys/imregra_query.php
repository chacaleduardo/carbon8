<?

class ImRegraQuery
{
    public static function adicionarNovosGruposPorTipoObjeto()
    {
        return "INSERT INTO imregra (
                SELECT 
                    NULL,
                    ?tipoobjeto?.idempresa,
                    'imgrupo',
                    ge.idimgrupo,
                    'imgrupo',
                    imgrupo.idimgrupo,
                    'GRUPO',
                    'ATIVO'
                FROM imgrupo 
                JOIN ?tipoobjeto? ON imgrupo.idobjetoext = ?tipoobjeto?.id?tipoobjeto?
                JOIN imgrupo ge ON ge.idempresa = ?tipoobjeto?.idempresa AND ge.tipoobjetoext = 'tipopessoa' AND ge.idobjetoext = 1
                WHERE imgrupo.tipoobjetoext = '?tipoobjeto?'
                AND imgrupo.status = 'ativo'
                ?isnull?
                AND NOT EXISTS (
                    SELECT 1 
                    FROM imregra 
                    WHERE tiporegra = 'GRUPO' 
                    AND idobjetoorigem =  ge.idimgrupo 
                    AND idobjetodestino = imgrupo.idimgrupo
                )
            )";
    }

    public static function adicionarNovosGruposDaLp()
    {
        return "INSERT INTO imregra (
                    SELECT NULL,
                    imgrupo.idempresa,
                    'imgrupo',
                    ge.idimgrupo,
                    'imgrupo',
                    imgrupo.idimgrupo,
                    'GRUPO',
                    'ATIVO'
                FROM imgrupo 
                JOIN carbonnovo._lp l ON imgrupo.idobjetoext = l.idlp
                JOIN imgrupo ge on ge.idempresa = l.idempresa and ge.tipoobjetoext = 'tipopessoa' and ge.idobjetoext = 1
                WHERE imgrupo.tipoobjetoext = '_lp'
                AND imgrupo.status = 'ativo'
                AND ISNULL(NULLIF(idtipopessoa, ''))
                AND NOT exists (
                    SELECT 1 
                    FROM imregra 
                    WHERE tiporegra = 'GRUPO' 
                    AND idobjetoorigem =  ge.idimgrupo 
                    and idobjetodestino = imgrupo.idimgrupo
                )
            )";
    }

    public static function adicionarNovosGruposManuais()
    {
        return "INSERT INTO imregra (
                    SELECT NULL,
                        imgrupo.idempresa,
                        'imgrupo',
                        ge.idimgrupo,
                        'imgrupo',
                        imgrupo.idimgrupo,
                        'GRUPO',
                        'ATIVO'
                    FROM imgrupo JOIN imgrupo ge on ge.idempresa = imgrupo.idempresa AND ge.tipoobjetoext = 'tipopessoa' AND ge.idobjetoext = 1
                    WHERE imgrupo.tipoobjetoext = 'manual'
                    AND imgrupo.status = 'ativo'
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM imregra 
                        WHERE tiporegra = 'GRUPO' 
                        AND idobjetoorigem =  ge.idimgrupo 
                        AND idobjetodestino = imgrupo.idimgrupo
                    )
                )";
    }
}

?>
<?

class SgcargoFuncao extends Controller
{
    public static function buscarFuncoesPorIdSgcargo()
    {
        return "SELECT 
                    scf.status,
                    scf.idsgcargofuncao,
                    sf.funcao
                FROM sgcargofuncao scf
                JOIN sgfuncao sf ON(sf.idsgfuncao = scf.idsgfuncao)
                WHERE scf.status = 'ATIVO'
                AND scf.idsgcargo = ?idsgcargo?
                ORDER BY sf.funcao";
    }

    public static function listarSetoresDepartamentosAreasVinculadas()
    {
        return "SELECT 
                o.idobjetovinculo,
                s.idsgsetor,
                s.setor AS setor,
                sd.departamento AS departamento,
                sd.idsgdepartamento,
                sa.area AS area,
                sa.idsgarea,
                o.idempresa,
                s.idempresa,
                CONCAT(COALESCE(sa.area, ''), '', COALESCE(sd.departamento, ''), '', COALESCE(s.setor, '')) AS resultado
            FROM
                objetovinculo o
            LEFT JOIN
                sgsetor s ON (s.idsgsetor = o.idobjetovinc AND o.tipoobjetovinc = 'sgsetor')
            LEFT JOIN 
                sgcargo sg ON (sg.idsgcargo = o.idobjeto)
            LEFT JOIN
                sgdepartamento sd ON (sd.idsgdepartamento = o.idobjetovinc AND o.tipoobjetovinc = 'sgdepartamento')
            LEFT JOIN
                sgarea sa ON (sa.idsgarea = o.idobjetovinc AND o.tipoobjetovinc = 'sgarea')
            WHERE
                o.idobjeto = ?idsgcargo?
            AND 
                o.tipoobjeto = 'sgcargo'
            ORDER BY
                resultado ASC";

    }
}


?>
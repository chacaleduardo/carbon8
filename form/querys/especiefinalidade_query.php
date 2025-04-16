<?
class EspecieFinalidadeQuery {

    public static function buscarEspecieAmostra(){
        return "SELECT 
            id.identificacao, p.idplantel
        FROM
            especiefinalidade tef
                LEFT JOIN
            plantel p ON (p.idplantel = tef.idplantel)
                JOIN
            amostra AS a ON (tef.idespeciefinalidade = a.idespeciefinalidade)
                LEFT JOIN
            identificador id ON (id.idobjeto = a.idamostra and id.tipoobjeto='amostra' and id.identificacao is not null)
        WHERE
            tef.status = 'A'
                AND a.idamostra = ?idamostra?
        LIMIT 1";
    }

    public static function buscarEspeciefinalidadeComPlantel(){
        return "SELECT
                    tef.idespeciefinalidade
                    ,p.plantel as especie
                    ,tef.tipoespecie
                    ,tef.finalidade
                    ,tef.calculoidade
                    ,tef.flgcalculo
                    ,tef.rotulo
                FROM especiefinalidade tef
                    LEFT JOIN plantel p on(p.idplantel=tef.idplantel)
                WHERE tef.status='A'
                    and tef.idempresa =?idempresa?
                ORDER BY tef.especie, tef.tipoespecie, tef.finalidade";
    }

    public static function listarEspecieFinalidadePlantelOrdenadoPorPlantel()
    {
        return "SELECT idespeciefinalidade,
                       CONCAT(p.plantel, ' / ', finalidade,  ' / ',  tipoespecie, ' (', 
                                CASE WHEN calculoidade = 'D' THEN 'dias' 
                                     WHEN calculoidade = 'M' THEN 'meses' 
                                     WHEN calculoidade = 'S' THEN 'semanas' 
                                     WHEN calculoidade = 'G' THEN 'semanas'
                                END, ')') AS nome
                  FROM especiefinalidade e LEFT JOIN plantel p ON (p.idplantel = e.idplantel)
                 WHERE e.status = 'A' 
                   AND e.idempresa = ?idempresa?
              ORDER BY p.plantel, e.finalidade, e.tipoespecie";
    }

    public static function listarEspecieFinalidadePorUnidade()
    {
        return "SELECT idespeciefinalidade,
                        concat(p.plantel,'-',e.finalidade) as especiefinalidade 
                FROM especiefinalidade e
                    JOIN unidadeobjeto u
                    JOIN plantel p
                WHERE u.idunidade = ?idunidade?
                        and p.idplantel = e.idplantel
                        and u.tipoobjeto = 'especiefinalidade'
                        and u.idobjeto = e.idespeciefinalidade
                        and e.status ='A'
                ORDER BY especiefinalidade";
    }

    public static function listarEspecieFinalidadePorEmpresa()
    {
        return "SELECT idespeciefinalidade,
                        concat(p.plantel,'-',e.finalidade) as especiefinalidade 
                FROM especiefinalidade e
                        JOIN plantel p
                WHERE
                         p.idplantel = e.idplantel
                        and e.idempresa= ?idempresa?
                        and e.status ='A'
                ORDER BY especiefinalidade";
    }


}
?>
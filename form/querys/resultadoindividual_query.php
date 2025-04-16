<?
class ResultadoindividualQuery {

    public static function buscarIdentificacaoResultado(){
        return "SELECT 
                ri.identificacao, ri.resultado
            FROM
                resultadoindividual ri
                    JOIN
                resultado r ON r.idresultado = ri.idresultado
            WHERE
                ri.idresultado = ?idresultado?
            ORDER BY ri.idresultadoindividual";
    }

    public static function buscarNumeroDelinhasResultadoIndividual(){
        return"SELECT 
                1
            FROM
                resultadoindividual i
            WHERE
                i.idresultado = ?idresultado?";
    }

    public static function inserirResultadoIndividual(){
        return"INSERT INTO resultadoindividual 
        (idempresa, idresultado, pesagem, tipoespecial, identificacao, resultado, valor, criadopor, criadoem, alteradopor, alteradoem, ord, ordem_elisa) 
        VALUES 
        (?idempresa?, ?idresultado?, ?pesagem?, ?tipoespecial?, '?identificacao?', ?resultado?, ?valor?, '?criadopor?', now(), '?alteradopor?', now(), ?ord?, ?ordem_elisa?)
        ";
    }

    public static function deletarResultadoIndividual(){
        return "DELETE FROM resultadoindividual 
            WHERE
                idresultado = ?idresultado? ORDER BY idresultadoindividual DESC LIMIT 1";
    }

    public static function buscarResultadoIndividual(){
        return "SELECT 
            resultado,
            valor,
            identificacao,
            idresultadoindividual
        FROM
            resultadoindividual i
        WHERE
            i.idresultado = ?idresultado?        
        ";
    }

    public static function buscarResultadoIndividualPorIdresultado()
    {
        return "SELECT r.*,        
                       CAST(r.identificacao AS UNSIGNED) AS videntificacao,
                       sbaal.subtipoamostra
                  FROM resultadoindividual r LEFT JOIN resultadoamostralad ral ON ((ral.idresultado = r.idresultado))
             LEFT JOIN amostra aal ON ((ral.idamostra = aal.idamostra))
             LEFT JOIN subtipoamostra sbaal ON ((sbaal.idsubtipoamostra = aal.idsubtipoamostra))
                 WHERE r.idresultado = ?idresultado?
              ORDER BY videntificacao";
    }
}
?>
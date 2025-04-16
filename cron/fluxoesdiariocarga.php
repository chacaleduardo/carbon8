<?
require_once("../inc/php/functions.php");
require_once("../form/controllers/fluxo_controller.php");


$startDate = new DateTime('2024-01-01');
$endDate = new DateTime();


while ($startDate <= $endDate) {
    $currentdate = $startDate->format('Y-m-d'); // Formata a data no formato YYYY-MM-DD
    $sqlf = "
    SELECT 
        f.idfluxo AS idfluxo,
        fs.idfluxostatus AS idfluxostatus,
        f.modulo AS tipo
    FROM
        fluxostatus fs
    JOIN
        fluxo f ON (f.idfluxo = fs.idfluxo)
    WHERE
        f.status = 'ATIVO'
    ORDER BY f.idfluxo;";

    $sqlfl = d::b()->query($sqlf);
    while ($sqlfluxos = mysqli_fetch_assoc($sqlfl)) {
        $idfluxostatus = $sqlfluxos['idfluxostatus'];
        $idfluxo = $sqlfluxos['idfluxo'];

        // ENTRADA
        $sqle = "
            SELECT 
                COUNT(*) AS entrada
            FROM
                fluxostatushist fha
            WHERE
                fha.status NOT IN ('INATIVO')
            AND
                fha.idfluxostatuspessoa IS NULL
            AND
                fha.idfluxostatus = $idfluxostatus
            AND 
                DATE(fha.criadoem) = '$currentdate';"; 

        $sqlen = d::b()->query($sqle);
        $sqlentrada = mysqli_fetch_assoc($sqlen);

        // SAÍDA
        $sqls = "
            SELECT 
                COUNT(*) AS saida
            FROM
                fluxostatushist fhs
            WHERE
                fhs.status NOT IN ('INATIVO')
            AND
                fhs.idfluxostatuspessoa IS NULL
            AND
                fhs.idfluxostatus = $idfluxostatus
            AND
                fhs.alteradoem > fhs.criadoem
            AND
                DATE(fhs.alteradoem) = '$currentdate';";

        $sqlsa = d::b()->query($sqls);
        $sqlsaida = mysqli_fetch_assoc($sqlsa);

       $saldo = $sqlentrada['entrada'] - $sqlsaida['saida'];

        // ATRASO
        $sqlat = "
            SELECT 
                COUNT(*) AS atraso
            FROM
                fluxostatushist fha
            WHERE
                fha.status NOT IN ('INATIVO')
            AND
                DATE(fha.alteradoem) = '$currentdate'
            AND
                fha.atrasodias > 0
            AND
                fha.alteradoem > fha.criadoem
            AND
                fha.idfluxostatus = $idfluxostatus
            AND 
                fha.atrasodias IS NOT NULL;";

        if ($sqlfluxos['tipo'] == 'evento') {
            $sqlat = "
                SELECT 
                    COUNT(*) AS atraso
                FROM
                    fluxostatushist fha
                LEFT JOIN 
                    evento e ON(e.idevento = fha.idmodulo)
                WHERE
                    fha.status NOT IN ('INATIVO')
                AND
                    fha.idfluxostatus = $idfluxostatus
                AND
                    DATE(fha.alteradoem) = '$currentdate'
                AND
                    fha.alteradoem < e.prazo
                AND 
                    fha.alteradoem > fha.criadoem
                AND
                    fha.modulo = 'evento';";
        }

        $sqlatr = d::b()->query($sqlat);
        $sqlatraso = mysqli_fetch_assoc($sqlatr);

        // INSERIR OS DADOS NO BANCO
        FluxoController::InsertFluxoEsDiario($idfluxo, $idfluxostatus, $sqlentrada['entrada'], $sqlsaida['saida'], $saldo, $sqlatraso['atraso'], $currentdate); // Passando $currentdate como parâmetro
    }

    $startDate->modify('+1 day');
}
?>
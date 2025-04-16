<?
require_once "../../inc/php/functions.php"; 
require_once "../../form/controllers/tarifaenergia_controller.php"; 

use \Firebase\JWT\JWT;

$_headers = getallheaders();
$_headers= array_change_key_case($_headers, CASE_LOWER);

$secretkey = "energy-data-timeseries-2025-secret-key!";

if (!$_headers["jwt"]) {
    http_response_code(401);
    die("Token não enviado!");
}

try {
    $decoded = JWT::decode($_headers['jwt'], $secretkey, array('HS256'));
} catch (\Exception $e) {
    $merro = $e->getMessage();
    http_response_code(401);
    die($merro);
}

date_default_timezone_set('America/Sao_Paulo');
$dataAtual = date("Y-m-d H:i:s");

$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

$cobranca = PrecoEnergiaController::BuscarTarifaAtivoParaVinculo();
$valor = $cobranca['valor'];
$idtarifaenergiapadrao = $cobranca['idtarifaenergiapadrao'];

// Formata Valores Uca,Ubc e Uab para 3 casas decimais
$FormatChaves= ['Uca', 'Ubc', 'Uab'];
foreach ($FormatChaves as $chave) {
    if (isset($data[$chave])) {
        $data[$chave] = number_format($data[$chave] / 10, 1, '.', '');
    }
}

$sqli = "INSERT INTO medicaoenergia (medidor, DPTDCT, DPQSING, ua, ub, uc, uab, ubc, uca, ia, ib, ic, pa, pb, pc, ps, qa, qb, qc, qs, pfa, pfb, pfc, pfs, sa, sb, sc, ss, f, wpp, wpn, wqp, wqn, epp, epn, eqp, eqn, idempresa, data, valorcobranca, idtarifaenergiapadrao, nmedidores)
VALUES ('?medidor?', '?DPTDCT?', '?DPQSING?', '?Ua?', '?Ub?', '?Uc?','?Uab?', '?Ubc?', '?Uca?', '?Ia?', '?Ib?', '?Ic?', '?Pa?', '?Pb?', '?Pc?', '?Ps?', '?Qa?', '?Qb?', '?Qc?', '?Qs?', '?PFa?', '?PFb?', '?PFc?', '?PFs?', '?Sa?', '?Sb?', '?Sc?', 
'?Ss?', '?F?', '?WPP?', '?WPN?', '?WQP?', '?WQN?', '?EPP?', '?EPN?', '?EQP?', '?EQN?', '8', '$dataAtual', $valor, $idtarifaenergiapadrao, '?nmedidores?')";

foreach ($data as $chave => $valor) {
    $sqli = preg_replace("/(\?$chave\?)/", $valor, $sqli);
}

if ($sqli == true) {
    echo("Autorizado, dados cadastrados com sucesso.");
}
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
?>

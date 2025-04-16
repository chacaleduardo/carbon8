<?
require_once("../inc/php/functions.php");

// QUERYS
require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/querys/empresa_query.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/unidade_controller.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

$idtag = $_POST['idtag'];

if(!empty($idtag) && $_POST['tipo'] == 'empresa')
{
    $empresas = SQL::ini(EmpresaQuery::buscarEmpresasQueNaoEstejamVinculadasEmUmaTagLocada(), [
        'idempresa' => cb::idempresa(),
        'idtag' => $idtag
    ])::exec();

    if ($empresas->numRows())
    {
        foreach($empresas->data as $empresa)
        {
            $empresasArr[$empresa["idempresa"]]['sigla'] = $empresa['sigla'];
            $empresasArr[$empresa["idempresa"]]['nomefantasia'] = $empresa['nomefantasia'];   
        }

        $json = json_encode($empresasArr);
        cbSetPostHeader('1','buscaempresa');
        echo($json);
    }

} elseif (!empty($idtag) && $_POST['tipo'] == 'unidade')
{
    $unidades = UnidadeController::buscarUnidadesAtivasPorIdEmpresa($_POST['idempresa']);

    echo "<option value='' selected></option>";
    if (count($unidades)) 
    {
        // while($r = mysqli_fetch_array($resinfo)) 
        foreach($unidades as $unidade)
        {
            echo "<option value='".$unidade["idunidade"]."'>".$unidade["unidade"]."</option>";        
        }
    }

} 

?>
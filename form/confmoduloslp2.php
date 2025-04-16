<?
require_once("../inc/php/functions.php");
require_once("controllers/_lp_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "_lp";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idlp" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "SELECT 
            p.*, e.empresa, e.corsistema,o.idobjeto as idlpgrupo
            FROM
            carbonnovo._lp p
                JOIN
            empresa e ON (e.idempresa = p.idempresa)
                JOIN 
            carbonnovo._lpobjeto o on (o.tipoobjeto = 'lpgrupo' AND o.idlp = p.idlp)
            WHERE
            p.idlp = '#pkid'";

/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");
$re = _LpController::buscarSiglaECorsistemaDaEmpresa($_1_u__lp_idempresa);

$_sigla = $re['sigla'];
$_corsistema = $re['corsistema'];

function retArrModSelecionados($inIdLp)
{
    global $_sigla;
    global $_1_u__lp_idempresa;
    return _LpController::buscarModulosSelecionados2($_1_u__lp_idempresa, $inIdLp, $_sigla);
}

function retArrModFilho($inIdLp, $inmod, $inidempLP)
{

    global $_sigla;
    return _LpController::buscarModulosFilhos($inidempLP, $inIdLp, getidempresa('u.idempresa', 'unidade'), $inmod, $_sigla);
}

function retArrModFilho2($inIdLp, $inmod, $inidempLP)
{
    global $_sigla;

    if ($modulovinc = getModReal($inmod)) {
        $inmod = $modulovinc;
    }

    return _LpController::buscarModulosFilhosDosFilhos($inidempLP, $inIdLp, $inmod, $_sigla);
}

function retArrModRep($inIdLp, $inmod)
{
    return _LpController::buscarRepsDoModulo($inIdLp, $inmod);
}

$j = 0;
$modolosPrincipais = retArrModSelecionados($_1_u__lp_idlp);

echo "<h1>SNIPETS</h1>";
foreach ($modolosPrincipais['1'] as $mod => $val) { //modulos principais
    echo '<h1>'.$val['tipo'].' - '.$val['rotulomenu'].' - '.$mod.'</h1>';
    $arrmodfilho = retArrModFilho($_1_u__lp_idlp, $mod, $_1_u__lp_idempresa);
    //echo var_dump($arrmodfilho);
    
    if (count($arrmodfilho) > 0)
    {
        echo '-------- filhos</br>';
        foreach ($arrmodfilho as $modf => $valf) //modulos filhos
        {
            echo var_dump($valf);
            echo '<h3>'.$valf['tipo'].' - '.$valf['rotulomenu'].' - '.$modf.'</h3>';
            $arrmodrep = retArrModRep($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
            $arrmodfilho2 = retArrModFilho2($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
            if (count($arrmodrep) > 0 || count($arrmodfilho2) > 0)
            {
                
                if (count($arrmodfilho2) > 0 ){ //funcionalidades
                    echo '-------- funcionalidades: <b>'.$modf.'</b></br>';
                    foreach ($arrmodfilho2 as $modf2 => $valf2)
                    {   
                        echo '<p>'.$valf2['tipo'].' - '.$valf2['rotulomenu'].' - '.$modf2.'</p>';
                    }                    
                }

                if (count($arrmodrep) > 0 ){ //relatorios
                    echo '-------- relatorios</br>';
                    foreach ($arrmodrep as $modrep => $valrep)
                    {
                        echo '<p>'.$valrep['tipo'].' - '.$valrep['rep'].' - '.$modrep.'</p>';
                    }
                }
            }
        }
    }
} 

echo "<h1>MODULOS</h1>";
foreach ($modolosPrincipais['2'] as $mod => $val) { //modulos principais
    echo '<h1>'.$val['tipo'].' - '.$val['rotulomenu'].' - '.$mod.'</h1>';
    $arrmodfilho = retArrModFilho($_1_u__lp_idlp, $mod, $_1_u__lp_idempresa);
    //echo var_dump($arrmodfilho);
    
    if (count($arrmodfilho) > 0)
    {
        echo '-------- filhos</br>';
        foreach ($arrmodfilho as $modf => $valf) //modulos filhos
        {
            echo var_dump($valf);
            echo '<h3>'.$valf['tipo'].' - '.$valf['rotulomenu'].' - '.$modf.'</h3>';
            $arrmodrep = retArrModRep($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
            $arrmodfilho2 = retArrModFilho2($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
            if (count($arrmodrep) > 0 || count($arrmodfilho2) > 0)
            {                
                if (count($arrmodfilho2) > 0 ){ //funcionalidades
                    echo '-------- funcionalidades: <b>'.$modf.'</b></br>';
                    foreach ($arrmodfilho2 as $modf2 => $valf2)
                    {
                        echo '<p>'.$valf2['tipo'].' - '.$valf2['rotulomenu'].' - '.$modf2.'</p>';
                    }                    
                }

                if (count($arrmodrep) > 0 ){ //relatorios
                    echo '-------- relatorios</br>';
                    foreach ($arrmodrep as $modrep => $valrep)
                    {
                        echo '<p>'.$valrep['tipo'].' - '.$valrep['rep'].' - '.$modrep.'</p>';
                    }
                }
            }
        }
    }
} 
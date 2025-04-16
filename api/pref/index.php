<?
require_once("../../inc/php/validaacesso.php");
$arrModulosUsuario = retArrayModulosUsuario();

if($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1){
    $relfuncionario = '{"endpoint":"/report/relfuncionario.php","icon":"info", "rotulo": "Minhas Informações"},';
} 

$arrayUsuarioAPP = array(120680, 120679, 120678, 120677, 112378);

if(in_array('eventoponto', $arrModulosUsuario["MODULOS"]['eventoponto']) && !in_array($_SESSION["SESSAO"]["IDPESSOA"], $arrayUsuarioAPP)){
    $eventoPonto = '{"endpoint":"?_modulo=eventoponto","icon":"watch_later", "rotulo": "Ponto"},';
}

if(in_array('organograma', $arrModulosUsuario["MODULOS"]['organograma']) && !in_array($_SESSION["SESSAO"]["IDPESSOA"], $arrayUsuarioAPP)){
    $organograma = '{"endpoint":"/report/organograma.php","icon":"lan_sharp", "rotulo": "Organograma"},';
}

if(in_array('notificacoes', $arrModulosUsuario["MODULOS"]['notificacoes'])){
    $notificacoes = '{"endpoint":"?_modulo=notificacoes","icon":"notifications", "rotulo": "Notificações"},';
}

if(in_array('alterasenha', $arrModulosUsuario["MODULOS"]['alterasenha'])){
    $alterasenha = '{"endpoint":"?_modulo=alterasenha","icon":"vpn_key", "rotulo": "Alterar senha"},';
}

switch ($_SESSION["SESSAO"]["IDEMPRESA"]) {
    case 1:
        $empresaPolitica = "laudo-politica-privacidade.html";
    break;
    case 2:
        $empresaPolitica = "inata-politica-privacidade.html";
    break;
    case 4:
        $empresaPolitica = "hubio-politica-privacidade.html";
    break;
    case 8:
        $empresaPolitica = "csc-politica-privacidade.html";
    break;
    case 15:
        $empresaPolitica = "mbiotech-politica-privacidade.html";
    break;
}
//,{"endpoint":"/sobre/","icon":"help", "rotulo": "Sobre o Aplicativo"}

$politicaprivacidade = '{"endpoint":"/inc/politica-privacidade/'.$empresaPolitica.'","icon":"web_asset", "rotulo": "Política Privacidade"},';

$array = "[$relfuncionario $eventoPonto $organograma $notificacoes $alterasenha $politicaprivacidade]";

echo str_replace(',]', ']', $array);
?>

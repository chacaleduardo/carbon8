<?
$iu = $_SESSION['arrpostbuffer']['1']['u']['sgcargo']['idsgcargo'] ? 'u' : 'i';

if($_SESSION['arrpostbuffer']['1'][$iu]['sgcargo']['tipo'] || $_SESSION['arrpostbuffer']['1'][$iu]['sgcargo']['cargo'])
{
    $cargoOriginal = $_SESSION['arrpostbuffer']['1'][$iu]['sgcargo']['cargo'];

    $cargo = $cargoOriginal;
    if($_SESSION['arrpostbuffer']['1'][$iu]['sgcargo']['tipo'])
    {
        $cargo = str_replace("{$_SESSION['arrpostbuffer']['1'][$iu]['sgcargo']['tipo']} ", "", $cargoOriginal);
    }

    $cargo = "{$_SESSION['arrpostbuffer']['1'][$iu]['sgcargo']['tipo']} $cargo";
    
    $_SESSION['arrpostbuffer']['1'][$iu]['sgcargo']['cargo'] = trim($cargo);
}

?>
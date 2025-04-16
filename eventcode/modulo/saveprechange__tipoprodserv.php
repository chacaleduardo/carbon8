<?
//gerar historico e atualizar valor
$gerandohistorico = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistorico)) 
{
    $campo = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'];
    $tabela = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['tipoobjeto'];
    $_id = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];

    $_SESSION['arrpostbuffer']['parc']['u'][$tabela]['id'.$tabela]=$_id;
    $_SESSION['arrpostbuffer']['parc']['u'][$tabela][$campo]=$valor;

    if($campo=='adicional'){

        $arrPL = PlanejamentoProdServController::buscarPlanejamentoProdservAdicional($_id);
        $linha=1;
        foreach ($arrPL as $pl) 
        { $linha++;
            $_SESSION['arrpostbuffer']['parc'.$linha]['u'][$tabela]['id'.$tabela]=$pl['idplanejamentoprodserv'];
            $_SESSION['arrpostbuffer']['parc'.$linha]['u'][$tabela][$campo]=$valor;

        }

    }

    

}

?>
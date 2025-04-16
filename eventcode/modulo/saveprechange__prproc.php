<?
//Seta os status do Fluxo no Atividade
$arrpb = $_SESSION["arrpostbuffer"];
reset($arrpb);
foreach($arrpb as $linha => $arrlinha) 
{
	foreach($arrlinha as $acao => $arracao) 
    {
        foreach($arracao as $tab => $arrtab)
        {
            $idfluxostatus = $arrtab["idfluxostatus"];
            if($idfluxostatus)
            {   
                $rowcFluxo = PrProcController::buscarStatusPorIdFluxoStatus($idfluxostatus)[0];
                if(!empty($rowcFluxo['statustipo']))
                {
                    //Enviar o campo para a pagina de submit
                    $_SESSION["arrpostbuffer"][$linha+1][$acao]["prativ"]["statuspai"] = $rowcFluxo['statustipo'];
                } else {
                    die("Verificar se o Fluxo está configurado corretamente. Status Tipo do ".$rowcFluxo['statustipo']." está vazio.");
                }                
            }                
        }
    }
}

if ($_POST['_statusant_'] == 'APROVADO' AND $_SESSION["arrpostbuffer"]["1"]['u']["prproc"]["status"] == 'APROVADO') 
{
	die("Não é possivel alterar no status aprovado");
}

if ($_POST['_statusant_'] != 'REVISAO' and $_SESSION["arrpostbuffer"]["1"]['u']["prproc"]["status"] == 'REVISAO') 
{
    $_SESSION["arrpostbuffer"]["1"]['u']["prproc"]["versao"] = $_SESSION["arrpostbuffer"]["1"]['u']["prproc"]["versao"] + 1;
    $_SESSION["arrpostbuffer"]["1"]['u']["prproc"]["descr"] = NULL;
}
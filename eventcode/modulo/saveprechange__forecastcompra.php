<?
if ($_POST['_statusant_'] != 'APROVADO' AND $_SESSION["arrpostbuffer"]["1"]['u']["forecastcompra"]["status"] == 'APROVADO')
{
    $_SESSION["arrpostbuffer"]["1"]['u']["forecastcompra"]["versao"] = $_SESSION["arrpostbuffer"]["1"]['u']["forecastcompra"]["versao"] + 1;
}
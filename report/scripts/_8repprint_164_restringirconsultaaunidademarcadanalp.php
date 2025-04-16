<?
if($lpRep)
{
    // while ($r = mysql_fetch_array($rrep)){
    foreach($lpRep as $rep)
    {
        if($rep['flgunidade']=='Y'){
            $_sqlresultado .= " and exists (select 1 from pessoa p where p.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']." and p.idunidade = $_tab.idunidade)";
            break;
        }
    }
}
?>
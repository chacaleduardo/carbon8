<?
// while ($r = mysql_fetch_array($rrep))
foreach($lpRep as $r)
{
    if($r['flgidpessoa']=='Y'){
        $_sqlresultado .= getOrganogramaRep('idpessoafun');
        break;
    }
}
?>
<?
if(in_array('CB-ERROR: Acesso expirado!', headers_list()) == TRUE)
{
    header('Content-type: text/javascript; charset=utf-8');    
    $sqlCotacao = "SELECT idobjetosolipor, nome
                     FROM nf n JOIN pessoa p ON n.idpessoa = p.idpessoa
                    WHERE n.idnf = '".$arr_token['idnf']."';";
    $resCotacao = d::b()->query($sqlCotacao) or die("A Consulta Orçamento falhou:".mysqli_error(d::b())."<br>Sql:".$sqlCotacao); 
    $rowCotacao = mysqli_fetch_assoc($resCotacao)
    ?>
    CB.modal({
        titulo: "AVISO",
        corpo: ["Prezado <?=$rowCotacao['nome']?>,<br><br>"+
    "O prazo de preenchimento do Orçamento <b><?=$rowCotacao['idobjetosolipor']?></b>, Cotação <b><?=$arr_token['idnf']?></b> expirou em <b><?=dma($arr_token['datalimite'])?></b>.<br/><br/>"+
    "Para mais informações, favor nos contatar através do email:<a href='mailto:suprimentos@laudolab.com.br?Subject=AVISO'>suprimentos@laudolab.com.br.</a><br/><br/>Obrigado!<br/><br/>"],
    });
    $("#divSmallBoxes").hide();
    localStorage.setItem("prazoexpirado", "true");
    <?
    die();
}
?>
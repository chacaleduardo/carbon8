//Gerar estatistica para inputs em determinada condicao. Deve ser utilizado padrao de nomeclatura do redis
function _stat(inacao,inform)
{
    inform=(inform)?inform+":":"";
    var frmstat = new FormData();
    $.each($("option[cbstat]"), function(i,e)
    {
        if(e.tagName=="OPTION")
        {
            let selname=$(e).closest("select[name]").attr("name");
            let gcap=selname.match(/_[^_]*_[^_]*_(.*)/);
            //console.log(gcap);
            if(selname)
            {
                frmstat.append(i,"_stat:"+inform+$(e).attr("cbstat")+":"+gcap[1]);
            }
        }
    });
    const request = new XMLHttpRequest();
    request.open("POST", "ajax/_stat.php");
    request.send(frmstat);
}
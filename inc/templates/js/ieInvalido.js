/*
* Detecta versão inválida do IE
* Referência: http://browserhacks.com/
*/
function ieInvalido()
{
    var isIElte8_1 = !+'\v1';
    var isIElte8_2 = '\v'=='v';
    var isIElte8_3 = document.all && !document.addEventListener;
    var isIElte8_4 = document.all && document.querySelector && !document.addEventListener;
    return (isIElte8_1 || isIElte8_2 || isIElte8_3 || isIElte8_4)?true:false;
}

if(ieInvalido()){
    alert("Você está utilizando uma versão inválida do Internet Explorer.\n\nRecomendação: Utilize o Firefox ou Google Chrome!");
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_ieinvalido
<??>
<script >

		
function novavalidacao(inidcontrole,intipo){
    vPost = "";
    vPost = vPost + "&_1_i_controleteste_idcontroleteste=";
    vPost = vPost + "&_1_i_controleteste_idtipocontroleteste="+intipo;
    vPost = vPost + "&_1_i_controleteste_idcontrole="+inidcontrole;

    CB.post({
        objetos: vPost
        ,parcial:true
    }); 
}
function removevalidacao(inidcontroleteste){
    vPost = "";
    vPost = vPost + "&_1_d_controleteste_idcontroleteste="+inidcontroleteste;

    CB.post({
        objetos: vPost
        ,parcial:true
    });
}

function novoteste(inidcontroleteste){
    vPost = "";
    vPost = vPost + "&_1_i_controletitulo_idcontroletitulo=";
    vPost = vPost + "&_1_i_controletitulo_idcontroleteste="+inidcontroleteste;

    CB.post({
        objetos: vPost
        ,parcial:true
    });
}
function removeteste(inidcontroletitulo){
    vPost = "";
    vPost = vPost + "&_1_d_controletitulo_idcontroletitulo="+inidcontroletitulo;

    CB.post({
        objetos: vPost
        ,parcial:true
    });
}
</script>
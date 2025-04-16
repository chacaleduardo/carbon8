<??>
<script>
$("#cbSalvar" ).attr("disabled","disabled");

function inativa(inidcontroleimpressaoitem,inidcontroleimpressao){

	vPost = "";
	vPost = vPost + "&_1_u_controleimpressaoitem_idcontroleimpressaoitem="+inidcontroleimpressaoitem;
	vPost = vPost + "&_1_u_controleimpressaoitem_status=INATIVO";
	vPost = vPost + "&_2_u_controleimpressao_idcontroleimpressao="+inidcontroleimpressao;
	vPost = vPost + "&_2_u_controleimpressao_status=INATIVO";

	//submitajax(vPost,"#reload");
	
	CB.post({
	objetos: vPost		
	,parcial:true
    })

}

function liberartodos(vidcontroleimpressao){
    document.body.style.cursor = 'wait';
		
    $.get("ajax/liberaimpressao.php", 
        { idcontroleimpressao : vidcontroleimpressao}, 
        function(resposta){
                $("#resp").html(resposta);
                if(resposta=="OK"){
                        //$('#frm').submit();
                        document.location.reload(true);
                }else{
                         alert(resposta);

                }					
        }
    );
    document.body.style.cursor = '';		
}

</script>
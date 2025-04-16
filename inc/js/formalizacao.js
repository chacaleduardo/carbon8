/*
 * Esta função recebe uma coleção de objetos (insumos) para loop e extração  das fórmulas existentes em cada um
 */
function getFormulas(inJInsumos){
	jFormulas={};
	$.each(inJInsumos, function(i,o){
		if(o.idprodservformula){
			of = {rotulo:o.rotulo,cor:o.cor}
			jFormulas[o.idprodservformula]=of;
		}else{
			console.warn("getFormulas: o.idprodservformula inválido: "+i);
		}
	});
	return jFormulas;
}

/*
 * Montar html com as possíveis fórmulas para seleção no lote em questão
 * Recebe como parâmetro os insumos/objetos FILHO, que contém as possíveis fórmulas para produção
 * Uma função *externa* de callback será utilizado para o evento de clique/seleção da fórmula
 */
function htmlSeletorFormula(inObj, inNomeFuncaoCallback){

	//debugger;
	
	//Recupera o rotulo de algum objeto para alcançar o pai
	vIdArvoreInsumos=primeiro(inObj);
	vInsumoPai=getInsumoPai(vIdArvoreInsumos,inObj);

	vFormulas=getFormulas(inObj);
	hSelecao="";
	$.each(vFormulas,function(io,o){
		//vIdLoteobj=evalJson(`jLoteObj['prodservformula'][${io}]['idloteobj']`)||"";
		//sChecked=(vIdLoteobj.length>0)?"checked":"";
		hSelecao+=`
		<div class="radio">
			<label class="hoverazul">
				<input type="radio" value="${io}" name="selecaoFormulaFormalizacao" idformula="${io}" title="#${io}" onclick="${inNomeFuncaoCallback}(this)">
				<i class="fa fa-1x fa-circle" style="color:${o.cor};"></i>
				${o.rotulo}
			</label>
		</div>
		`;
	});

	return hSelecao;
}	

/*
 * Recebe como parâmetro os insumos/objetos FILHO, que contém as possíveis fórmulas para produção
 * Uma função *externa* de callback será utilizado para o evento de clique/seleção da fórmula
 */
function seletorFormulaModal(inObj, inNomeFuncaoCallback){
	vObj=inObj||jInsumos;
	hSeletor = htmlSeletorFormula(vObj, inNomeFuncaoCallback);
	
	$("#corpoFormalizacao").html(`<div class="alert alert-warning pointer" role="alert" onclick="seletorFormula()"><i class="fa fa-info-circle hoverazul"></i> Aguardando sele&ccedil;&atilde;o de F&oacute;rmula</a></div>`);
	$("#cbModalTitulo").html("Selecione a f&oacute;rmula para produ&ccedil;&atilde;o:");
	$("#cbModalCorpo").html(hSeletor);
	$('#cbModal').modal('show');

}

/*
 * Informada a chave idprodservformulains, esta função percorre recursivamente a árvore para encontrar o insumo superior
 */
function getInsumoPai(inKeyInsumo,jFilhos, iPai){
	var oRetPai;
	$.each(jFilhos, function(i, o) {
		
		if(i==inKeyInsumo){
			oRetPai = iPai;
			return false;
		}
		if(tamanho(o.insumos)){
			oRetPai = getInsumoPai(inKeyInsumo, o.insumos, i);
		}
	});

	return oRetPai;
}

//Recebe coleção de lotes (objetos) para extração de fórmulas selecionadas
function getFormulasLotes(inLotes){
	aFormulas=[];
	$.each(inLotes, function(i,o){
		if(o.idprodservformula){
			aFormulas.push(o.idprodservformula);
		}
	});
	return aFormulas;
}

//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//#sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>
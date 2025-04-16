<?
/*
 * maf101210: Caso a pagina esteja marcada somente para visualização,
 * neste ponto carrega-se o buffer gerado pelo PHP.
 * Esse buffer é transformado em um objeto DOMDocument.
 * O DOMDocument efetua parse no texto html e cada objeto é trocado por um DIV para se tornar somente leitura
 */

function text2readonly(){
	global $doc,$input,$inputname,$inputvalue,$inputcbvalue;
	
	$novoobj = $doc->createElement('span', $inputvalue);
	$novoobj->setAttribute('class','inputreadonly');
	$novoobj->setAttribute('name',$inputname);
	$novoobj->setAttribute('value', $inputvalue);
	//Nao colocar o atributo cbvalue em qualquer objeto
	if(!empty($inputcbvalue)){
		$novoobj->setAttribute('cbvalue', $inputcbvalue);
	}
	$novoobj->setAttribute('cbtagoriginal', 'text');
	$inputname="";
	return $novoobj;
}

function textarea2readonly(){
	global $doc,$input,$inputname,$inputvalue,$inputcbvalue;
	
	//$objvalue = nl2br($input->nodeValue);
	$objvalue = $input->nodeValue;
	//$objvalue = nl2br($objvalue);
	$novoobj = $doc->createElement('div', $objvalue);
	
	$novoobj->setAttribute('name',$inputname);
	$novoobj->setAttribute('class','textareareadonly');
	$novoobj->setAttribute('cbtagoriginal', 'textarea');
	
	return $novoobj;
}

function select2readonly(){
	global $doc,$input,$inputname,$inputvalue,$inputcbvalue;
	
	//Analisa cada objeto OPTION em busca do selecionado
	$objvalue = "";
	foreach ($input->childNodes as $objoption){

		if($objoption->nodeName=="option"){
			//aqui ocorreria erro (hasAttribute) caso se tentasse obter a propriedade SELECTED de um objeto que nao tivesse essa propriedade. Ex: Comment
			if($objoption->hasAttribute('selected')){
				$objvalue = $objoption->nodeValue;//captura o TEXTO que esta sendo mostrado no <option>
			}	
		}
	}

	$novoobj = $doc->createElement('div', $objvalue);
	if($objvalue!=""){	
		$novoobj->setAttribute('class','selectreadonly');
	}
	$novoobj->setAttribute('name',$inputname);
	$novoobj->setAttribute('cbtagoriginal', 'select');
	
	return $novoobj;
}

if($_pagereadonly==false){
	if(getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"]=="r"){
		$_pagereadonly=true;
	}
}

if($_pagereadonly===true && $_GET["_pagereadonly"]!="N"){

	cbSetCustomHeader("CB-READONLY", "Y");

	$conteudo=ob_get_contents();
	$conteudo=preg_match("//u", $conteudo)?utf8_decode($conteudo):$html; //MAF060519: Converter para ISO8859-1.
	ob_end_clean();

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;
	$doc->loadHTML($conteudo);


	/*
	 * Trata os objetos FORM trocando o endereco de submit para vazio
	 * Caso objeto seja retirado, os descendentes também são excluídos
	 */
	$forms = $doc->getElementsByTagName('form');
	for ($i = $forms->length; --$i >= 0; ) {
		//captura a referencia para o objeto
		$input = $forms->item($i);

		$input->setAttribute('action',$_SERVER["PHP_SELF"]);
	}
	$forms="";
	$input="";
	$novoobj="";
	$objvalue="";
	
	/*
	 * Trata os objetos INPUT
	 */
	$inputs = $doc->getElementsByTagName('input');
	for ($i = $inputs->length; --$i >= 0; ) {
		//captura a referencia para o objeto
		$input = $inputs->item($i);
		$inputname = $input->getAttribute('name');
		$inputvalue = $input->getAttribute('value');
		$inputcbvalue = $input->getAttribute('cbvalue');

		switch ($input->getAttribute('type')) {
			case 'text':
				$input->parentNode->insertBefore(text2readonly($input), $input);
				$input->parentNode->removeChild($input);
				break;
			case 'radio' :
				//recupera o atributo checked
				$objvalue = $input->getAttribute('checked');
				if(!$objvalue){
					$input->setAttribute('disabled', 'disabled');
					$input->setAttribute('style', 'opacity:0.3');
				}
				break;
			case 'password' :
				$input->parentNode->removeChild($input);
				break;
			case 'submit' :
				$input->parentNode->removeChild($input);
				break;
			case 'hidden' :
				$novoobj = text2readonly($input);
				$novoobj->setAttribute("class","hidden");
				$input->parentNode->insertBefore($novoobj, $input);
				$input->parentNode->removeChild($input);
				break;
			default:
				break;
		}
		//caso estas variaveis nao sejam resetadas, o valor e escrito novamente na tela
		$input="";
		$novoobj="";
		$objvalue="";
	}
	$inputs="";

	/*
	 * Trata os objetos BUTTON com propriedade onclick='cbpost();'
	 */
	$inputs = $doc->getElementsByTagName('button');
	for ($i = $inputs->length; --$i >= 0; ) {
		//captura a referencia para o objeto
		$input = $inputs->item($i);

		//recupera o valor da propriedade onclick
		$objclick = $input->getAttribute('onclick');

		//remove o button
		if(preg_match("/cbpost/i", $objclick)){
			$input->parentNode->removeChild($input);					
		}

		//caso estas variaveis nao sejam resetadas, o valor e escrito novamente na tela
		$input="";
		$novoobj="";
		$objvalue="";
	}
	$inputs="";

	/*
	 * Trata os objetos TD com propriedade onclick='cbpost();'
	 */
	$inputs = $doc->getElementsByTagName('td');
	for ($i = $inputs->length; --$i >= 0; ) {
		//captura a referencia para o objeto
		$input = $inputs->item($i);

		//recupera o valor da propriedade onclick
		$objclick = $input->removeAttribute ('onclick');

		//remove a propriedade on click
		if(preg_match("/cbpost/i", $objclick)){
			$input->removeAttribute ('onclick');
		}

		//caso estas variaveis nao sejam resetadas, o valor e escrito novamente na tela
		$input="";
		$novoobj="";
		$objvalue="";
	}
	$inputs="";

	/*
	 * Trata os objetos TEXTAREA
	 */
	$inputs = $doc->getElementsByTagName('textarea');
	for ($i = $inputs->length; --$i >= 0; ) {
		
		//captura a referencia para o objeto
		$input = $inputs->item($i);
		$flag = 0;
		foreach($input->attributes as $attr){
			if($attr->name == 'tinydisabled'){
				$flag = 1;
				$valor = $attr->nodeValue;
			}
		}
		if($flag == 1){
			$input->parentNode->removeChild($input);

			//$input->parentNode->insertBefore(textareareadonly2($input), $input);
			//caso estas variaveis nao sejam resetadas, o valor e escrito novamente na tela
			$input="";
			$novoobj="";
			$objvalue="";
		}else{
		
			//print_r($input);
			//$input->parentNode->appendChild($novoobj);//iteracao regressiva. desta maneira se coloca os objetos em ordem invertida na tela
			$input->parentNode->insertBefore(textarea2readonly($input), $input);
			$input->parentNode->removeChild($input);
		
			//caso estas variaveis nao sejam resetadas, o valor e escrito novamente na tela
			$input="";
			$novoobj="";
			$objvalue="";
		}
		
	}
	$inputs="";

	/*
	 * Trata os objetos SELECT (drop down)
	 */
	$selects = $doc->getElementsByTagName('select');

	for ($i = $selects->length; --$i >= 0; ) {
		//captura a referencia para o objeto
		$input = $selects->item($i);

		$input->parentNode->insertBefore(select2readonly(), $input);
		$input->parentNode->removeChild($input);
		
		//caso estas variaveis nao sejam resetadas, o valor e escrito novamente na tela
		$input="";
		$novoobj="";
		$objvalue="";
	}

	/*
	 * Trata os objetos SPAN com propriedade ROH (readonly hide = true
	 */
	$inputs = $doc->getElementsByTagName('span');
	for ($i = $inputs->length; --$i >= 0; ) {
		//captura a referencia para o objeto
		$input = $inputs->item($i);

		//recupera o valor da propriedade ROH (readonly hide)
		$objroh = $input->getAttribute('roh');
		if($objroh=="true"){
			$novoobj = $doc->createElement('div', $objvalue);
			$novoobj->setAttribute('cbtagoriginal', 'span');
			//$input->parentNode->appendChild($novoobj);//iteracao regressiva. desta maneira se coloca os objetos em ordem invertida na tela
			$input->parentNode->insertBefore($novoobj, $input);
			$input->parentNode->removeChild($input);					
		}

		//caso estas variaveis nao sejam resetadas, o valor e escrito novamente na tela
		$input="";
		$novoobj="";
		$objvalue="";
	}
	$inputs="";
	
	echo $doc->saveHTML();
}

?>
<script>
<?if($_pagereadonly===true && $_GET["_pagereadonly"]!="N"){?>
	$(document).ready(()=>{
		// Disabilita a opção de realizar Upload de arquivos quando a permissão é somente leitura
		//Dropzone.instances.map(i => console.log(i.disable()));
	});
<?}?>
</script>

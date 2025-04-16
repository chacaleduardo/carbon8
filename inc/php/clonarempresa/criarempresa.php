<?

set_time_limit(0);
require_once("../../php/validaacesso.php");

?>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Criar Empresa</title>
	</head>
	<style>
		.breadcrumb-item, input[name=empresa]{
			cursor: pointer;
		}
		.breadcrumb-item:hover{
			color: darkgray;
		}
		.obg{
			color:red;
			font-size: 8pt;;
		}
	</style>
    <body>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
		<script src="https://kit.fontawesome.com/a076d05399.js"></script>
        <div class="container">
			<div class="row mt-3">
				<div class="col-sm-12">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item" value="1">Criar Empresa Clone</li>
							<li class="breadcrumb-item" value="2">Sincronizar</li>
						</ol>
					</nav>
				</div>
			</div>
			<div id="op1" class="row mt-3 options">
				<div class="col-sm-12">
					<div class="card">
						<div class="card-body">
							<form id="formulario">
								<div class="form-row">
									<div class="form-group col-md-6">
										<label for="inputRazaoSocial">Razão Social: <span class="obg">*obrigatório</span></label>
										<input type="text" class="form-control" id="inputRazaoSocial" placeholder="Razão Social">
									</div>
									<div class="form-group col-md-6">
										<label for="inputCnpj">CNPJ: <span class="obg">*obrigatório</span></label>
										<input type="text" class="form-control" id="inputCnpj" placeholder="CNPJ" onkeypress="return isNumberKey(event)">
									</div>
								</div>
								<div class="form-row">
									<div class="form-group col-md-6">
										<label for="inputRadio">Clonar Empresa: <span class="obg">*obrigatório</span></label>
										<div id="inputRadio0">
											<?
											$sql = "SELECT idempresa,ifnull(nomefantasia,razaosocial) as nome FROM empresa WHERE status = 'ATIVO'";
											$res=d::b()->query($sql) or die("Erro ao Buscar Empresas: Erro: ".mysqli_error(d::b())."\n".$sql);
											while($row=mysqli_fetch_assoc($res)){?>
												<div class="form-check">
													<input class="form-check-input" type="radio" name="empresa0" id="radio_<?=$row["idempresa"]?>" value="<?=$row["idempresa"]?>">
													<label class="form-check-label" for="radio_<?=$row["idempresa"]?>"><?=$row["nome"]?></label>
												</div>
											<?}?>
										</div>
									</div>
									<div class="form-group col-md-6">
										<label for="inputRadio">Clonar Empresa: </br><span class="obg">Marcar esse campo fará com que seja criado registros para essa empresa baseada no clone selecionado</span></label>
										<div id="inputRadio1">
											<?
											$sql1 = "SELECT idempresa,ifnull(nomefantasia,razaosocial) as nome FROM empresa WHERE status = 'ATIVO'";
											$res1=d::b()->query($sql1) or die("Erro ao Buscar Empresas: Erro: ".mysqli_error(d::b())."\n".$sql1);
											while($row1=mysqli_fetch_assoc($res1)){?>
												<div class="form-check">
													<input class="form-check-input" type="radio" name="empresa1" id="radio_<?=$row1["idempresa"]?>" value="<?=$row1["idempresa"]?>">
													<label class="form-check-label" for="radio_<?=$row1["idempresa"]?>"><?=$row1["nome"]?></label>
												</div>
											<?}?>
										</div>
									</div>
								</div>
								<!-- Definir como filial -->
								 <div class="form-row">
									<div class="form-group col-xs-4 d-flex align-items-center">
										<input id="is_filial" type="checkbox" />
										<label for="is_filial" class="ml-2 mb-0">Filial</label>
									</div>
								 </div>
								<div class="form-row">
									<div class="form-group col-md-12">
										<input type="color" id="corsistema" name="favcolor" value="#ff0000">
									</div>
								</div>
								<button type="button" class="btn btn-primary" id="buttonCriar">Criar</button>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div id="op2" class="row mt-3 options">
				<div class="col-sm-12">
					<div class="card">
						<div class="card-body">
							Sincronizar
						</div>
					</div>
				</div>
			</div>
		</div>
    </body>
</html>
<script>
	$(document).ready(function(){
		$(".options").hide();
	});

	$(".breadcrumb-item").click(function(){
		var _id = $(this).val();
		if(!$("#op"+_id).is(":visible")){
			$(".options").hide();
			$("#op"+_id).show("1000");
		}
	});

	$("#buttonCriar").click(function(){
		//alert($("#favcolor").val());
		const razao = $("#inputRazaoSocial").val();
		const cnpj = $("#inputCnpj").val();
		const empresa0 = $('input[name=empresa0]:checked').val();
		const empresa1 = $('input[name=empresa1]:checked').val();
		const cor = $("#corsistema").val();
		const filial = $("#is_filial:checked").val();

		if(razao == "" || cnpj == "" || empresa0 == undefined){
			alert("É preciso preencher todos os campos");
		}else{
			if(confirm("Deseja realmente criar uma nova empresa?")){
				$("#buttonCriar").attr("disabled", true);
				$.ajax({
					method: "POST",
					url: "clonaempresa.php",
					data: {
						idempresa: empresa0,
						razao: razao,
						cnpj: cnpj,
						cor: cor,
						_idempresa_: empresa1,
						filial: filial
					}
				}).done(function(data, textStatus, jqXHR) {
					alert(`Empresa ${razao} criada com sucesso`);
					$("#buttonCriar").attr("disabled", false);
					$("#inputRazaoSocial").val("");
					$("#inputCnpj").val("");
				});
			}
		}

	});

	function isNumberKey(evt){
		var e = evt || window.event; //window.event is safer, thanks @ThiefMaster
		var charCode = e.which || e.keyCode;                        
		if (charCode > 31 && (charCode < 47 || charCode > 57))
		return false;
		if (e.shiftKey) return false;
		return true;
	}
</script>
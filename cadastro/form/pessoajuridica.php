<div class="w-full flex flex-col md:flex-row gap-2">
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Razão Social<span class="text-red-600">*</span></span>
		<input id="cliente.razao" name="razao" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a razão social" />
	</div>
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Nome Fantasia <span class="text-red-600">*</span></span>
		<input id="cliente.nome" name="nome" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome fantasia da empresa" />
	</div>
	<div class="w-full flex md:w-4/12 gap-2">
		<div class="w-full sm:w-3/12">
			<span name="telefone" class="text-xs text-[#989898]">DDD <span class="text-red-600">*</span></span>
			<input id="cliente.ddd" name="ddd" class="w-full py-3 ddd required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o DDD" />
		</div>
		<div class="w-full sm:w-9/12">
			<span name="telefone" class="text-xs text-[#989898]">Telefone da empresa <span class="text-red-600">*</span></span>
			<input id="cliente.telefone" name="telefone" class="w-full py-3 telefone-sem-ddd required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o telefone de contato da empresa" />
		</div>
	</div>
</div>
<div class="w-full flex flex-col md:flex-row gap-2">
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Email XML NFe<span class="text-red-600">*</span></span>
		<input id="cliente.emailxmlnfe" name="emailxmlnfe" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="email" placeholder="Informe o email para recebimento da XML NFe" />
	</div>
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Email Material<span class="text-red-600">*</span></span>
		<input id="cliente.email_mat" name="email_mat" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="email" placeholder="Informe o email do Material" />
	</div>
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Email NFS-e<span class="text-red-600">*</span></span>
		<input name="email" class="w-full py-3 bg-white border border-[#DDDDDD] rounded required focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="email" placeholder="Informe o email para recebimento da NFS-e" />
	</div>
</div>
<div class="w-full flex flex-col md:flex-row gap-2">
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Inscrição municipal
			<span class="text-red-600">*</span></span>
		<input id="cliente.insc_m" name="insc_m" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a Inscrição municipal" />
	</div>
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Inscrição Estadual:
		</span>
		<input id="cliente.insc_e" name="insc_e" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a Inscrição estadual" />
	</div>
	<div class="w-full md:w-4/12">
		<span class="text-xs text-[#989898]">Produtor rural? <span class="text-red-600">*</span></span>
		<select id="cliente.produtor_rural" name="produtor_rural" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
			<option value="0" class="font-light" disabled selected>Selecione uma opção</option>
			<option value="S">Sim</option>
			<option value="N">Não</option>
		</select>
	</div>
</div>
<div class="w-full flex flex-col md:flex-row gap-2">
	<div class="w-full md:w-6/12">
		<span class="text-xs text-[#989898]">Consumidor Final<span class="text-red-600">*</span></span>
		<select id="cliente.consumidor_final" name="consumidor_final" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
			<option value="0" class="font-light" disabled selected>Selecione uma opção</option>
			<option value="1">Sim</option>
			<option value="2">Não</option>
		</select>
	</div>
	<div class="w-full md:w-6/12">
		<span class="text-xs text-[#989898]">É um contribuinte do ICMS?<span class="text-red-600">*</span></span>
		<select id="cliente.contribuinte_icms" name="contribuente_icms" class="w-full py-3 required bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
			<option value="0" class="font-light" disabled selected>Selecione uma opção</option>
			<option value="1">Contribuinte ICMS</option>
			<option value="2">Contribuinte isento</option>
			<option value="3">Não Contribuinte</option>
		</select>
	</div>
</div>

<div class="w-full">
	<span class="text-xs text-[#989898]">Observação</span>
	<div>
		<textarea id="cliente.obs" rows="5" name="obs"
			class="block w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]"
			type="text" placeholder="Inserir observação"></textarea>
	</div>
</div>
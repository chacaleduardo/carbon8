<div class="w-full flex flex-wrap rounded-md border border-[#C0C0C0]">
    <span class="w-full text-white py-2 bg-[#178B94] text-center rounded font-bold">Endereço de entrega</span>
    <div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2">
        <!-- CEP -->
        <div class="w-full">
            <span class="text-xs text-[#989898]">CEP <span class="text-red-600">*</span></span>
            <input name="cep" class="w-full cep py-3 bg-white required border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a Inscrição municipal" />
        </div>
        <!-- Endereço e complemento -->
        <div class="w-full flex flex-col md:flex-row gap-2">
            <!-- Endereço -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Endereço <span class="text-red-600">*</span></span>
                <input name="endereco" class="w-full py-3 bg-white border required border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a Inscrição municipal" />
            </div>
            <!-- Complemento -->
            <div class="w-full md:w-6/12">
                <div class="w-full">
                    <span class="text-xs text-[#989898]">Complemento <span class="text-red-600">*</span></span>
                    <input name="complemento" type="text" class="w-full py-[14px] bg-white required border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" />
                </div>
            </div>
        </div>
        <!-- Cidade / UF / Bairro  -->
        <div class="w-full flex flex-col md:flex-row gap-2">
            <!-- Cidade -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Cidade <span class="text-red-600">*</span></span>
                <input name="cidade" class="w-full required py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a razão social" />
            </div>
            <!-- UF -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">UF <span class="text-red-600">*</span></span>
                <select name="uf" class="w-full required py-[14px] bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
                    <option value="">Selecionar</option>
                    <option value="AC">AC</option>
                    <option value="AL">AL</option>
                    <option value="AM">AM</option>
                    <option value="AP">AP</option>
                    <option value="BA">BA</option>
                    <option value="CE">CE</option>
                    <option value="DF">DF</option>
                    <option value="ES">ES</option>
                    <option value="GO">GO</option>
                    <option value="MA">MA</option>
                    <option value="MG">MG</option>
                    <option value="MS">MS</option>
                    <option value="MT">MT</option>
                    <option value="PA">PA</option>
                    <option value="PB">PB</option>
                    <option value="PE">PE</option>
                    <option value="PI">PI</option>
                    <option value="PR">PR</option>
                    <option value="RJ">RJ</option>
                    <option value="RN">RN</option>
                    <option value="RO">RO</option>
                    <option value="RR">RR</option>
                    <option value="RS">RS</option>
                    <option value="SC">SC</option>
                    <option value="SE">SE</option>
                    <option value="SP">SP</option>
                    <option value="TO">TO</option>
                    <option value="EX">EX</option>
                </select>
            </div>
            <!-- Bairro -->
            <div class="w-full md:w-6/12">
                <div class="w-full">
                    <span class="text-xs text-[#989898]">Bairro <span class="text-red-600">*</span></span>
                    <input name="bairro" type="text" class="w-full py-[14px] bg-white border required border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" />
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Referências bancárias -->
<div class="w-full flex flex-wrap rounded-md border border-[#C0C0C0]">
    <!-- Titulo -->
    <span class="w-full text-white py-2 bg-[#178B94] text-center rounded font-bold">Referências
        bancárias</span>
    <div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2">
        <!-- Email para envio do XML e boletos/NF* -->
        <div class="w-full flex flex-col md:flex-row gap-2">
            <div class="w-full">
                <span class="text-xs text-[#989898]">Email para envio do XML e boletos/NF<span class="text-red-600">*</span></span>
                <input name="complemento" class="w-full py-3 bg-white border required border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a Inscrição municipal" />
            </div>
        </div>

        <!-- banco 1 -->
        <!-- banco 1 / Agência 1 / C/C 1 / Contato  -->
        <div class="w-full flex flex-col md:flex-row gap-2">
            <!-- Banco 1-->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Banco</span>
                <input name="banco[0][banco]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome do banco" />
            </div>
            <!-- Agência 1 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Agência</span>
                <input name="banco[0][agencia]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da agência" />
            </div>
            <!-- C/C 1 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">C/C</span>
                <input name="banco[0][conta]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da conta corrente" />
            </div>
            <!-- Contato 1 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Contato</span>
                <input name="banco[0][contato]" class="w-full py-3 bg-white border telefone border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número para contato" />
            </div>
        </div>
        <!-- banco 2 -->
        <!-- banco 2 / Agência 2 / C/C 2 / Contato  -->
        <div class="w-full flex flex-col md:flex-row gap-2">
            <!-- Banco 2 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Banco</span>
                <input name="banco[1][banco]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome do banco" />
            </div>
            <!-- Agência 2 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Agência</span>
                <input name="banco[1][agencia]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da agência" />
            </div>
            <!-- C/C 2 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">C/C</span>
                <input name="banco[1][conta]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da conta corrente" />
            </div>
            <!-- Contato 2 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Contato</span>
                <input name="banco[1][contato]" class="w-full py-3 telefone bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número para contato" />
            </div>
        </div>
        <!-- banco 3 -->
        <!-- banco 3 / Agência 3 / C/C 3 / Contato  -->
        <div class="w-full flex flex-col md:flex-row gap-2">
            <!-- Banco 3 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Banco</span>
                <input name="banco[2][banco]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome do banco" />
            </div>
            <!-- Agência 3 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Agência</span>
                <input name="banco[2][agencia]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da agência" />
            </div>
            <!-- C/C 3 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">C/C</span>
                <input name="banco[2][conta]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da conta corrente" />
            </div>
            <!-- Contato 3 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Contato</span>
                <input name="banco[2][contato]" class="w-full py-3 telefone bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número para contato" />
            </div>
        </div>
        <!-- banco 4 -->
        <!-- banco 4 / Agência 4 / C/C 4 / Contato  -->
        <div class="w-full flex flex-col md:flex-row gap-2">
            <!-- Banco 4 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Banco</span>
                <input name="banco[3][banco]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome do banco" />
            </div>
            <!-- Agência 4 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Agência</span>
                <input name="banco[3][agencia]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da agência" />
            </div>
            <!-- C/C 4 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">C/C</span>
                <input name="banco[3][conta]" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número da conta corrente" />
            </div>
            <!-- Contato 4 -->
            <div class="w-full md:w-6/12">
                <span class="text-xs text-[#989898]">Contato</span>
                <input name="banco[3][contato]" class="w-full py-3 telefone bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o número para contato" />
            </div>
        </div>
    </div>
</div>

<!-- <select name="_1_<? #= $_acao 
                        ?>_endereco_codcidade" id="idcidade" vnulo>
    <? #fillselect( $CodcidadeCidade=EnderecoController::buscarCodcidadeCidade($_1_u_endereco_uf), $_1_u_endereco_codcidade); 
    ?>
</select> -->
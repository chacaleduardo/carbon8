<div class="w-full flex flex-wrap rounded-md border border-[#C0C0C0]">
	<span class="w-full text-white py-2 bg-[#178B94] text-center rounded font-bold">Informações de acesso ao
		SISLAUDO</span>
	<div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2">
		<!-- Usuário -->
		<div class="w-full">
			<span class="text-xs text-[#989898]">Usuário<span class="text-red-600">*</span></span>
			<input name="usuario" class="w-full py-3 bg-white border required border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Nome de usuário" />
		</div>
		<!-- Email -->
		<div class="w-full">
			<span class="text-xs text-[#989898]">Email<span class="text-red-600">*</span></span>
			<input name="email" class="w-full py-3 bg-white border required border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="email" placeholder="Informe de recuperação de senha" />
		</div>
		<!-- Senha -->
		<div class="w-full relative flex flex-col justify-center">
			<span class="text-xs text-[#989898]">Senha<span class="text-red-600">*</span></span>
			<div name="senha" data-id="input-password" class="absolute right-4 h-5 cursor-pointer mt-4 password-icon">
				<svg fill="#5E5E5E" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
					<path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z" />
				</svg>
			</div>
			<input name="senha" id="input-password" type="password" class="w-full py-3 bg-white border required border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" placeholder="Informe sua senha" />
		</div>
		<!-- Confirmar senha -->
		<div class="w-full relative flex flex-col justify-center">
			<span class="password-icon text-xs text-[#989898]">Informe sua senha novamente<span class="text-red-600">*</span></span>
			<div data-id="input-confirm-password" class="absolute right-4 h-5 cursor-pointer mt-4 password-icon">
				<svg fill="#5E5E5E" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
					<path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z" />
				</svg>
			</div>
			<input id="input-confirm-password" type="password" class="w-full py-3 bg-white border required border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" placeholder="Informe sua senha" />
		</div>
	</div>
</div>
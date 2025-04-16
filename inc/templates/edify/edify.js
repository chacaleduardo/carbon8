import { createParser } from 'eventsource-parser';
import { marked } from 'marked';
import { v4 as uuidv4 } from 'uuid';


export class EdifyChat {
	constructor() {
		this.uuid = uuidv4();
		this.sending = false;
		this.username = '';
		this.init();
	}

	init() {
		window.onload = () => {
			setTimeout(() => {
				this.renderTemplate();
				this.cacheElements();
				this.bindEvents();
				this.chatIdle.classList.remove("hidden");
				console.log('Edify starts!');
			}, 2000);
		};
	}
	setUsername(username) {
		this.username = username;
	}
	renderTemplate() {
		document.body.insertAdjacentHTML('beforeend', this.getTemplate());
	}

	getAnswerIaTemplate(msg, id) {
		return `
			<span class="ia" id="answer_${id}">
				<div class="avatar"></div>
				<div class="text">${msg} <img class="loading" src="/inc/img/marcio_avatar_loading.gif"></div>
			</span>
		`;
	}
	/*
	<div id="edify-chat-idle" class="hidden shadow-xl">
	<div class="say-hello">
	Olá! Eu sou Edify.<br>
	Como posso te ajudar?
	</div>
	<div id="say-hello-btn" class="btn "><span class="material-icons text-white">send</span></div>
	</div>
	*/
	getTemplate() {
		return `
			<div id="edify">

				<div id="edify-chat-idle2" class="hidden shadow-xl">
					<div class="avatar"></div>
					<div class="say-hello">
						Posso te ajudar hoje?
					</div>
				</div>
			
				<img class="hidden" src="/inc/img/marcio.png" alt="">
				<div id="edify-chat" class="hidden text-center">
					<div class="edify-chat-body">
						<div class="edify-chat-header">
							<div class="edify-chat-header-title">Olá! Eu sou o Edimar. Como posso te ajudar hoje?</div>
							<div class="actions">
								<i class="button refresh material-icons">refresh</i>
								<i class="button open-full material-icons">open_in_full</i>
								<i class="button close-modal material-icons">close</i>
							</div>
						</div>
						<div class="edify-alert">
							<span>As respostas são meramente informativas. Um veterinário sempre deve ser consultado.</span>
						</div>
						<div class="edify-info hidden">
							<h5 class="bold">Você pode me perguntar coisas do tipo:</h5>
							<ul class="gap-6">
								<li>“Qual a profilaxia da coriza infecciosa das aves?”</li>
								<li>“O que devo fazer após saber o diagnóstico de Mycoplasma para um lote de aves?”</li>
								<li>“Crie uma guia impresso para prevenção de Bronquite infecciosa das aves”</li>
							</ul>
						</div>
						<div class="chat_ hidden"></div>
					</div>
					<form class="edify-chat-input hidden flex-row w-full gap-2" method="">
						<input type="text" placeholder="Digite sua mensagem..." class="sm:w-11/12 w-full flex-col">
						<a class="send button sm:w-1/12 flex-col disabled" href="#"><span class="material-icons text-white">send</span></a>
					</form>
					<h2 class="mt-2 text-white">Em manutenção <i class="fa fa-gear"></i></h2>
				</div>
				<div id="edify-overlay" class="hidden"></div>
			</div>
		`;
	}

	cacheElements() {
		this.form = document.querySelector('.edify-chat-input');
		this.input = document.querySelector('.edify-chat-input input');
		this.btnSend = document.querySelector('.edify-chat-input .send');
		this.chatIdle = document.querySelector('#edify-chat-idle2');
		//this.sayHelloBtn = document.querySelector('#say-hello-btn');
		this.edifyImg = document.querySelector("#edify img");
		this.info = document.querySelector('#edify-chat .edify-chat-body .edify-info');
		this.chat = document.querySelector('#edify-chat .edify-chat-body .chat');
		this.edifyChat = document.querySelector('#edify-chat');
		this.reloadChatBtn = document.querySelector('#edify-chat .edify-chat-header .refresh');
		this.openInFullBtn = document.querySelector('#edify-chat .edify-chat-header .open-full');
		this.closeBtn = document.querySelector('#edify-chat .edify-chat-header .close-modal');
		this.overlay = document.querySelector('#edify-overlay');
	}

	bindEvents() {
		this.input.addEventListener('keyup', this.toggleSendButton.bind(this));
		this.input.addEventListener('keypress', this.handleKeyPress.bind(this));
		this.btnSend.addEventListener('click', this.handleSendButtonClick.bind(this));
		this.form.addEventListener('submit', (e) => e.preventDefault());
		this.edifyImg.addEventListener('click', this.closeChat.bind(this));
		this.chatIdle.addEventListener('click', this.startChat.bind(this));
		this.reloadChatBtn.addEventListener('click', this.reloadChat.bind(this));
		this.openInFullBtn.addEventListener('click', this.openInFullChat.bind(this));
		this.closeBtn.addEventListener('click', this.closeChat.bind(this));
	}

	toggleSendButton() {
		if (this.input.value === "") {
			this.btnSend.classList.add('disabled');
		} else {
			this.btnSend.classList.remove('disabled');
		}
	}

	handleKeyPress(e) {
		if (e.key === 'Enter') {
			this.sendMessage(this.input.value);
		}
	}

	handleSendButtonClick() {
		if (!this.btnSend.classList.contains('disabled')) {
			this.sendMessage(this.input.value);
		}
	}

	reloadChat(){
		this.uuid = uuidv4();
		this.chat.innerHTML = this.getTemplate();
		this.chat.classList.add('hidden');
		this.info.classList.remove('hidden');
		this.input.value = "";
	}

	openInFullChat(){
		if(this.edifyChat.classList.contains("full")){
			this.edifyChat.classList.remove("full");
			this.openInFullBtn.textContent = "open_in_full";
		}else{
			this.edifyChat.classList.add("full");
			this.openInFullBtn.textContent = "close_fullscreen";
		}
	}

	startChat() {
		this.chatIdle.classList.add('hidden');
		this.edifyImg.classList.remove('hidden');
		this.edifyChat.classList.remove('hidden');
		this.overlay.classList.remove('hidden');
	}

	closeChat() {
		this.edifyImg.classList.add('hidden');
		this.edifyChat.classList.add('hidden');
		this.overlay.classList.add('hidden');
		this.chatIdle.classList.remove('hidden');
	}

	async sendMessage(msg) {
		if (this.input.value === "" || this.sending) return;

		this.info.classList.add('hidden');
		this.chat.classList.remove('hidden');
		this.chat.insertAdjacentHTML('beforeend', `<span class="user">${msg}</span>`);
		this.chat.scrollTop = this.chat.scrollHeight;
		this.input.value = '';
		const id = new Date().valueOf();
		this.chat.insertAdjacentHTML('beforeend', this.getAnswerIaTemplate("", id));
		let answer = document.getElementById("answer_" + id).children[1];
		this.chat.scrollTop = this.chat.scrollHeight;

		try { 
			this.sending = true;
			const response = await fetch('https://ai.biofy.tech/stream/', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					"input": {
						"input": msg
					},
					"config": {
						"configurable": {
							"session_id": this.username+'-'+this.uuid
						}
					}
				})
			});

			const reader = response.body.getReader();
			const decoder = new TextDecoder();

			let acumulateAnswer = "";
			let html = "";
			const parser = createParser(event => {
				if (event.type === 'event') {
					let data = JSON.parse(event.data);
					if (data?.answer && data?.answer != "```markdown\n#") {
						acumulateAnswer += data.answer;
						answer.insertAdjacentHTML('beforeend', data.answer);
						this.chat.scrollTop = this.chat.scrollHeight;
						answer.querySelector('.loading')?.remove();
						html = marked(acumulateAnswer);
						answer.innerHTML = html;
					}
				}
			});

			while (true) {
				const { done, value } = await reader.read();
				if (done) break;
				parser.feed(decoder.decode(value, { stream: true }));
			}

			this.sending = false;
			//console.log(acumulateAnswer);
			//console.log(html);
		} catch (err) {
			console.error(err);
			this.sending = false;
			document.querySelector('.edify-chat-body .loading')?.remove();
			this.chat.insertAdjacentHTML('beforeend', this.getAnswerIaTemplate("Erro ao enviar mensagem", 0));
			this.chat.scrollTop = this.chat.scrollHeight;
		}
	}
}

window.EdifyChat = new EdifyChat();
/* import { createApp } from "@vue/runtime-dom";

const app = createApp({
	data() {
		return {
			negocio: 'pet',
			dados:{
			},
			form:0
		}
	},
	methods:{
		avancar(e) {
			e.preventDefault();
			this.form=this.form+1
		},
		voltar(e){
			e.preventDefault();
			this.form=this.form-1
		}
	},
	mounted(){
		console.log('mounted');
	}
});

//Executa evento
CB.on('posLoadUrl',function(){
	debugger
	app.mount('#app-vue');
});
//# sourceURL=cadastro.js */
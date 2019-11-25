<style scoped>
@media (min-width: 0px) {
	.sign-img-btn {
		margin-left: 0px;
	}

	.move-actions {
		margin-top: 20px;
	}
}

@media (min-width: 600px) {
	.sign-img-btn {
		margin-left: 0px;
	}

	.move-actions {
		margin-top: 20px;
	}	
}

@media (min-width: 960px) {

}

</style>

<template>
	<div class="page">
		<v-container >
			<v-card flat>
				<v-card-title>
					<v-container>
						<span class="headline">HANDTEKENING</span>
					</v-container>
				</v-card-title>
				<v-card-text>
					<v-container grid-list-md>
						<v-layout wrap>
							<v-flex xs12 sm12 md12>
								<p style="font-size: 18px; padding: 20px; background-color: #eee;">
									Hierbij machtigt u Beslisapp.nl om de {{this.government_name}} in gebreke te stellen. Als de gemeente {{this.government_name}} niet binnen vijftien dagen een besluit neemt op uw aanvraag of bezwaarschrift is een dwangsom verschuldigd.
									De hoogte van de dwangsom is: 20,- &euro; per dag voor de eerste 14 dagen. 30,- &euro; per dag voor de 14 dagen daarna en 40,- &euro; per dag voor de 14 dagen daarna. De totale dwangsom bedraagt maximaal &euro; 1260,-.<br><br>
									De kosten voor het in gebreke stellen bedragen 30 procent van de uit te betalen dwangsom. De beslisapp incasseert de dwangsom bij de gemeente.
									Zeventig procent van de ontvangen dwangsom wordt binnen twee weken na ontvangst overgemaakt op uw rekeningnummer "{{this.bank_number}}".
								</p>
							</v-flex>
							<v-flex xs12 sm12 md12>
								<v-btn color="success" :outline="sign_mode == 2" @click="selectMode(1)" style="margin-left: 0">HANDTEKENING MET DE MUIS OF VINGER</v-btn>
								<v-btn color="success" :outline="sign_mode == 1" @click="selectMode(2)" :loading="this.image_loading" class="sign-img-btn">UPLOAD EEN AFBEELDING MET UW HANDTEKENING</v-btn>
								<input
									type="file"
									@change="previewMedia"
									name="media"
									id="media"
									ref="media"
									style="display: none;"
								>
							</v-flex>
							<v-flex xs12 sm12 md12 v-if="this.err_msg">
								<label class="error--text">{{this.err_msg}}</label>
							</v-flex>
							<v-flex xs12 sm12 md12>
								<div v-if="sign_mode == 1" style="border: solid 1px gray; height: 300px;">
									<VueSignaturePad
									ref="signaturePad"
									height="300px"
									/>
									<v-btn color="" @click="resetSign()" style="margin-left: 0; margin-top: 28px;">HANDTEKENING OPNIEUW PLAATSEN</v-btn>
								</div>
								<div v-if="sign_mode == 2" style="border: solid 1px gray; height: 300px;">
									<template v-if="sign_mode == 2">
										<!-- <v-img v-if="sign_img != ''" :src="sign_img" class="img-responsive" style="max-width:200px;max-height: 280px;"></v-img> -->
										<img :src="sign_img" class="img-responsive" style="max-width:200px;max-height: 280px;"></img>
									</template>
								</div>
							</v-flex>
						</v-layout>
					</v-container>
				</v-card-text>
				<v-card-actions>
					<v-container>
						<v-layout justify-end class="move-actions">
							<!-- <v-btn>Annuleer</v-btn> -->
							<v-btn dark color="primary" outline @click="onPrev">Terug</v-btn>
							<v-btn dark color="primary" @click="onSave" :loading="this.loading">VERZENDEN</v-btn>
						</v-layout>
					</v-container>
				</v-card-actions>
			</v-card>
		</v-container>
		<v-dialog v-model="show_errMsg" :width="this.errMsg_width" persistent>
			<v-card>
				<v-card-text style="font-size: 20px;padding: 50px 30px;">
				{{errMsg_txt}}
				<v-btn color="primary" @click="onError" :loading="this.errmsg_loading">
					OK
				</v-btn>
				</v-card-text>
			</v-card>
		</v-dialog>
	</div>
</template>

<script>
	export default {
		data () {
			return {
				form_type: '',
				sign_mode: 0,
				sign_img: '',
				bank_number: '',
				government_name: '',
				err_msg: '',
				loading: false,
				image_loading: false,
				show_errMsg: false,
				errMsg_txt: "Er is een fout opgetreden tijdens het verzenden. Neem contact op met: support@beslisapp.nl of 036 525 4774 voor een oplossing.",
				errMsg_width: "600px",
				errmsg_loading: false,
			}
		},

		created(){
			this.init();
		},
		mounted() {
			if(!localStorage.government_name || !localStorage.firstname){
				this.$router.push({
					name: 'form_ingebreke'
				})
			}

			this.form_type = localStorage.form_type;
			
			if(localStorage.bank_number){
				this.bank_number = localStorage.bank_number;
			}
			if(localStorage.government_name)
				this.government_name = localStorage.government_name;
		},
		methods: {
			init(){

			},
			onPrev(){
				this.$router.push({
					name: 'client'
				})
			},
			previewMedia(e) {
				let files = e.target.files || e.dataTransfer.files;
				if (!files.length)
					return;
				let signForm = new FormData();
				signForm.append('mode', 2);
				signForm.append('media', $('#media')[0].files[0]);
				this.image_loading = true;
				axios.post('/api/fax/uploadSign', signForm)
				.then(response =>  {
					this.sign_img = "/assets/signatures/" + response.data.name;
					this.image_loading = false;
				}).catch(error => {
					this.image_loading = false;
				});
			},
			removeImg(){
				this.sign_img = '';
			},
			resetSign(){
				this.$refs.signaturePad.clearSignature();
			},
			selectMode(mode){
				this.err_msg = '';
				this.sign_mode = mode;
				if(mode === 2){
					this.$refs.media.click();
				}
			},
			onSave(){
				var action = '';
				if(this.form_type == 'form_ingebreke')
					action = '/api/fax/publishform_ingebreke';
				if(this.form_type == 'form_bezwaar')
					action = '/api/fax/publishform_bezwaar';
				if(this.form_type == 'form_aanvraag')
					action = '/api/fax/publishform_aanvraag';
				
				if(this.sign_mode == 1){
					const { isEmpty, data } = this.$refs.signaturePad.saveSignature();
					if(isEmpty){
						this.err_msg = 'Please draw your signature.';
						return false;
					}
					let signForm = new FormData();
					signForm.append('mode', this.sign_mode);
					signForm.append('sign', data);
					this.loading = true;
					axios.post(action, signForm)
					.then(response =>  {
						var form_type = localStorage.form_type;
						// localStorage.removeItem('form_type');
						localStorage.removeItem('government_name');
						localStorage.removeItem('firstname');
						localStorage.removeItem('bank_number');
						
						if(response.data.result == "success"){
							this.$emit("changeStep", 4);
							this.$router.push({
								name: 'thanks',
								params: {created: response.data.created}
							})
						} else if(response.data.result == "fail"){
							this.$emit("changeStep", 1);
							this.$router.push({
								name: form_type
							})
						}
						this.loading = false;
					}).catch(error => {
						this.loading = false;
						console.log(error);
						this.show_errMsg = true;
					});
				} else if(this.sign_mode == 2){
					if(this.sign_img == ''){
						this.err_msg = 'image not found.';
						return false;
					}
					let signForm = new FormData();
					signForm.append('mode', 2);
					signForm.append('media', $('#media')[0].files[0]);
					console.log($('#media')[0].files[0]);
					this.loading = true;
					axios.post(action, signForm)
					.then(response =>  {
						localStorage.removeItem('bank_number');
						localStorage.removeItem('government_name');
						if(response.data.result == "success"){
							this.$emit("changeStep", 4);
							this.$router.push({
								name: 'thanks'
							})
						} else if(response.data.result == "fail"){
							this.$emit("changeStep", 1);
							this.$router.push({
								name: 'general'
							})
						}
						this.loading = false;
					}).catch(error => {
						this.loading = false;
						this.show_errMsg = true;
					});
				}
				else{
					this.err_msg = 'Plaats hier uw handtekening.';
				}

			},
			onError() {
				this.errmsg_loading = true;
				this.errMsg_width = "800px";
				axios.post('/api/fax/erroremail').then(response =>  {
					this.errmsg_loading = false;
					this.show_errMsg = false;
					this.$router.push({
						name: localStorage.form_type
					});
				}).catch(error => {
					this.errmsg_loading = false;
					this.show_errMsg = false;
					this.$router.push({
						name: localStorage.form_type
					});
				});
				
			}
		}
	}
</script>

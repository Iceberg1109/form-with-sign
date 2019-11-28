<template>
	<v-app>
		<div class="page">
			<v-container style="height: 100vh; display: flex; align-items: center">
				<v-layout row style="margin-top: -200px;">
						<v-flex xs10 sm10 md4  class="mx-auto text-md-center">
							<v-card flat dark color="#66BB6A">
								<v-card-text>
									<br>
									<br>
									<h2 style="text-align: center;">BEDANKT</h2>
									<p>
										Uw brief wordt nu verzonden en u ontvangt ook een bevestiging per mail.
									</p>
									<br>
									<br>
								</v-card-text>
							</v-card>
							<v-btn v-show="is_loggedin==false" color="info" block @click="onCreateAccount()" :loading="create_loading" style="margin-top: 14px;">MAAK EEN ACCOUNT VOOR ME AAN</v-btn>
							<v-btn v-show="is_loggedin==false" color="info" block @click="onGotoLogin" style="margin-top: 14px;">INLOGGEN</v-btn>
							<v-btn v-show="is_loggedin==true" color="info" block @click="onToUserPanel()" style="margin-top: 14px;">OPEN GEBRUIKERSPANEEL</v-btn>
							<v-btn v-show="is_loggedin==true" color="info" block @click="onLogout()" style="margin-top: 14px;">UITLOGGEN</v-btn>
							<form id="logout-form" action="/logout" method="POST" style="display: none;">
								<input type="hidden" name="_token" :value="csrf_token">
							</form>
						</v-flex>	
				</v-layout>
			</v-container>
			<v-dialog v-model="dialog_show" width="500" >
				<v-card>
					<v-card-title
					class="headline grey lighten-2"
					primary-title
					>
					MAAK EEN ACCOUNT VOOR ME AAN
					</v-card-title>
			
					<v-card-text >
					Er is een account voor u aangemaakt. Uw wachtwoord is per mail naar u verzonden (controleer ook uw spambox).
					</v-card-text>
			
					<v-divider></v-divider>

					<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn color="primary" flat @click="dialog_show = false">
						OK
					</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
		</div>
	</v-app>
</template>

<script>
  	export default {
  		data () {
				return {
					email: '',
					created: false,
					dialog_show: false,
					dialog_msg: '',
					create_loading: false,
					is_loggedin: false,
				}
			},
			methods: {
				init() {
					axios.post('/api/userpane/isloggedin')
					.then(response => {
						if(response.data.result == "success")
							this.is_loggedin = true;
						else
							this.is_loggedin = false;						
					}).catch(error => {
					});
				},
				onCreateAccount() {
					let generalForm = new FormData();
					generalForm.append('email', localStorage.email);
					this.create_loading = true;
					axios.post('/api/userpane/getpassword', generalForm)
					.then(response => {
						this.create_loading = false;
						if (response.data.result == "success") {
							this.dialog_show = true;
						}
					}).catch(error => {
						this.create_loading = false;
					});
				},
				onGotoLogin() {
					this.$router.push({
						name:  localStorage.form_type,
					});
				},
				onToUserPanel() {
					window.location.href = "/userpanel";
				},
				onLogout() {
					document.getElementById('logout-form').submit();
				},
			},
			created() {
				this.created = this.$route.params.created;
				this.init();
			},
			computed: {
				csrf_token() {
	        let token = document.head.querySelector('meta[name="csrf-token"]');
	        return token.content;
	      }
			}
  	}
</script>
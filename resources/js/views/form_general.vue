<style scoped>
	.input-white-outline {
		border: 1px solid white;
    border-radius: 5px;
    padding: 5px;
	}
</style>

<template>
	<div class="page">
		<v-container justify-center>
			<v-form
				ref="form"
				v-model="valid"
				lazy-validation
			>
				<v-card flat>
					<v-card-title>
						<v-container>
							<span class="headline">ALGEMENE INFORMATIE</span>
						</v-container>
					</v-card-title>
					<v-card-text>
						<v-container grid-list-md>
							<v-layout wrap>
								<v-flex xs12 sm12 md12>
									<h3>* Gaat het om een aanvraag of bezwaarschrift?</h3>
									<v-autocomplete
									v-model="generalForm.app_type"
									:items="app_types"
									persistent-hint
									return-object
									:rules="appRules"
									@change="selectAppType()"
									></v-autocomplete>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* Kies hier wat er is aangevraagd?</h3>
									<v-autocomplete
									v-model="generalForm.app_data"
									:items="app_data"
									item-text="value"
									item-value="index"
									persistent-hint
									return-object
									:rules="appDataRules"
									></v-autocomplete>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* {{this.title_request_date}}</h3>
									<v-menu
									lazy
									:close-on-content-click="false"
									v-model="menu_request_date"
									transition="scale-transition"
									offset-y
									:nudge-right="40"
									max-width="290px"
									min-width="290px"
									>
										<v-text-field
										slot="activator"
										v-model="generalForm.request_date"
										prepend-icon="event"
										:rules="dateRules"
										>
										</v-text-field>
										<v-date-picker v-model="date" @input="menu_request_date = false">
										</v-date-picker>
									</v-menu>
									<v-alert
										v-if="this.dateValidationMessage"
										:value="true"
										color="error"
										outline
										style="margin-bottom: 16px;"
										>
										{{this.dateValidationMessage}}
									</v-alert>
									<v-flex xs12 sm12 md12 v-if="this.dateEmailNotification" style="font-size: 14px; padding: 13px 16px; background-color: #2196f3; color: white; margin-bottom: 16px">
										{{this.dateEmailNotification}}
										&nbsp;&nbsp;
										<input class="input-white-outline" v-model="dateNotifyEmail" id="dateNotifyEmail" type="email" placeholder="Uw emailadres">
										<v-btn @click="onEmailSubmit" :loading="this.emailloading" style="font-size: 14px; margin: 0px 10px; width: 200px; height: 30px; color: #2196f3; background-color: white">VERZENDEN</v-btn>
									</v-flex>

								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* Heeft u een brief ontvangen dat er later wordt belist?</h3>
									<v-radio-group
									v-model="generalForm.letter_received"
									row
									:rules="letterRules">
										<v-radio label="Ja" value="yes" @change="showAlert(1)"></v-radio>
										<v-radio label="Nee" value="no" @change="showAlert(0)"></v-radio>
									</v-radio-group>
									<v-flex xs12 sm12 md12 v-if="alertShow == true" style="font-size: 14px; padding: 13px 16px; background-color: #2196f3; color: white; margin-bottom: 16px">
										Vul het aantal weken in dat later wordt beslist (de verdaging) :
										&nbsp;
										<input class="input-white-outline" v-model="weeks" id="weeks" @keypress="changeWeekandDays" style="width: 60px">
										weken.
										&nbsp;&nbsp;&nbsp;&nbsp;
										Of vul het aantal dagen in dat later wordt beslist:
										&nbsp;
										<input class="input-white-outline" id="days" v-model="days" @keypress="changeWeekandDays" style="width: 60px">
										dagen.
									</v-flex>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>Wat is het kenmerk van uw aanvraag / of bezwaarschrift?</h3>
									<v-text-field
									v-model="generalForm.subject"
									counter=35
									@keydown="limitSubjectCount" @paste="limitSubjectPaste"
									>
									</v-text-field>
									<v-alert
										v-show="show_sub_overmsg"
										:value="true"
										color="error"
										outline
										>
										U kunt maximaal 35 karakters gebruiken.
									</v-alert>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* Selecteer hieronder de gemeente:</h3>
									<v-autocomplete
									v-model="generalForm.municipality"
									:items="municipality_items"
									persistent-hint
									:rules="municipalityRules"
									@change="getMunicipality()"
									>
									</v-autocomplete>
								</v-flex>
								<div v-if="municipality">
									Faxnumber: {{ this.municipality.faxnumber }}<br>
									Emailadres: {{ this.municipality.email }}<br>
									Address: {{ this.municipality.address }}, {{ this.municipality.postal }}, {{ this.municipality.city }}
								</div>
							</v-layout>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-container>
							<v-layout justify-end>
								<v-btn dark color="primary" @click="onSave" :loading="this.loading">VOLGENDE</v-btn>
							</v-layout>
						</v-container>
					</v-card-actions>
				</v-card>
			</v-form>
			<v-snackbar v-model="errorSnackbar" color="red lighten-2" :right="true" :timeout="5000" :top="true">
				{{ err_msg }}
				<v-btn color="lightgrey" flat @click="errorSnackbar = false">
					Close
				</v-btn>
			</v-snackbar>
			<v-snackbar v-model="successSnackbar" color="green lighten-2" :right="true" :timeout="5000" :top="true">
				{{ success_msg }}
				<v-btn color="lightgrey" flat @click="successSnackbar = false">
					Close
				</v-btn>
			</v-snackbar>
			<v-dialog v-model="dialog" max-width="290">
				<v-card>
					<v-card-title class="headline">Systeem bericht</v-card-title>
					<v-card-text v-html="err_dialog_content"></v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn color="green darken-1" flat="flat" @click="dialog = false">
							OK
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
		</v-container>
		<v-dialog v-model="login_fail" width="750" >
			<v-card>
				<v-card-text style="font-size: 20px;padding: 50px 30px;">
				Het e-mailadres of wachtwoord van de gebruiker is onjuist!
				<v-btn color="primary" @click="login_fail = false">
					OK
				</v-btn>
				</v-card-text>
		
			</v-card>
		</v-dialog>
		<v-dialog v-model="loggedout_dlg" width="350" >
			<v-card>
				<v-card-text style="font-size: 20px;padding: 50px 30px;">
				U bent uitgelogd.
				<v-btn color="primary" @click="loggedout_dlg = false">
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
				login_fail: false,
				loggedout_dlg: false,
				valid: true,
				alertShow: false,
				date: '',
				menu_request_date: false,
				loading: false,
				emailloading: false,
				weeks: '',
				days: '',
				dateNotifyEmail: '',
				diff_days: 0,
				generalForm: {
					app_type: '',
					app_data: '',
					request_date: '',
					letter_received: '',
					subject: '',
					municipality: ''
				},
				app_types: [
					"Aanvraag",
					"Bezwaarschrift",
					"Bezwaarschift behandeld door een commissie"
				],
				app_data: [
					{
						index: 1,
						period: 56,
						value: "Standaard beslistermijn aanvraag (8 weken)",
					},
					{
						index: 2,
						period: 56,
						value: "Wmo voorziening",
					},
					{
						index: 3,
						period: 56,
						value: "Individuele inkomsentoeslag",
					},
					{
						index: 4,
						period: 56,
						value: "Bijstandsuitkering (Participatiewet)",
					},
					{
						index: 5,
						period: 56,
						value: "Bijzondere bijstand (Participatiewet)",
					},
					{ divider: true },
					{
						index: 6,
						period: 56,
						value: "WIA uitkering",
					},
					{
						index: 7,
						period: 28,
						value: "WW uitkering",
					},
					{
						index: 8,
						period: 28,
						value: "ZW uitkering",
					},
					{ divider: true },
					{
						index: 9,
						period: 35,
						value: "Belastingdienst - Zorgtoeslag",
					},
					{
						index: 10,
						period: 35,
						value: "Belastingdienst - Huurtoeslag",
					},
					{
						index: 11,
						period: 35,
						value: "Belastingdienst - Kinderopvangtoeslag",
					},
					{
						index: 12,
						period: 35,
						value: "Belastingdienst - Kindgebondenbudget",
					},
					{ divider: true },
					{
						index: 13,
						period: 56,
						value: "Omgevingsvergunning (eenvoudige aanvraag)",
					},
					{
						index: 14,
						period: 56,
						value: "Schuldhulpverlening",
					}
				],
				title_request_date: 'Kies de datum van uw aanvraag:',
				municipalities: [],
				municipality: {},
				municipality_items: [],
				errorSnackbar: false,
				successSnackbar: false,
				err_msg : '',
				success_msg: '',
				dialog : false,
				err_dialog_content : '',
				dateValidationMessage: '',
				dateEmailNotification: '',
				show_sub_overmsg: false,
				appRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				appDataRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				municipalityRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				letterRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
			}
		},
		computed: {
			dateRules() {
				const today = new Date();
				const rules = [];

				const rule =
					v => !!v || 'Dit is een verplicht veld.';
				rules.push(rule);
				if(this.generalForm.app_type && Object.keys(this.generalForm.app_data).length){
					var dateOrgString = this.generalForm.request_date;
					var dateString = dateOrgString.substring(6, 10)+'-'+dateOrgString.substring(3, 5)+'-'+dateOrgString.substring(0, 2);
					var selectedDate = new Date(dateString);
					var requiredDiff = 0;
					var dateDiff = 0;

					if(this.weeks){
						requiredDiff = +this.generalForm.app_data.period + +7*this.weeks;
					}
					else if(this.days){
						requiredDiff = +this.generalForm.app_data.period + +this.days;
					}
					else{
						requiredDiff = this.generalForm.app_data.period;
					}

					dateDiff = Math.ceil((today - selectedDate)/(1000 * 3600 * 24));
	
					if(dateDiff < requiredDiff){
						let diff = +requiredDiff - +dateDiff;
						this.diff_days = diff;
						this.dateValidationMessage =`De beslistermijn is nog niet verstreken. Over ${diff} dagen is de beslistermijn wel verstreken en kunt u de gemeente in gebreke stellen.`;
						this.dateEmailNotification = `Vul uw emailadres in als u over ${diff} dagen een herinnering wilt ontvangen: `;
					}
					else{
						this.dateValidationMessage = "";
						this.dateEmailNotification = "";
					}
				}
				return rules;
			}
		},
		created(){
			console.log(localStorage.form_type);
			this.init();
		},
		watch: {
			date: function(val, oldVal){
				this.generalForm.request_date = this.gettanggal(val);
			}
		},
		methods: {
			init(){
				localStorage.setItem('form_type', 'form_ingebreke');
				
				if(this.$route.params.login_failed){
					this.login_fail = true;
				}

				if (this.$route.params.loggedout) {	
					this.loggedout_dlg = true;
				}

				if(this.$route.params.token && this.$route.params.token != 'loggedout'){
					axios.get('/api/fax/general/token/' + this.$route.params.token)
						.then(response => {
							if(response.data.status === 'fail'){
								this.errorSnackbar = true;
								this.err_msg = response.data.message;
							}
							if(response.data.result === "block"){
								this.dialog = true;
								this.err_dialog_content = response.data.message;
							}
							if(response.data.app_type){
								this.generalForm.app_type = response.data.app_type
								this.selectAppType();
							}
							if(response.data.app_data) {
								this.generalForm.app_data = parseInt(response.data.app_data);
								this.setAppData();
							}
							if(response.data.request_date) {
								this.generalForm.request_date = response.data.request_date;
							}
							if(response.data.letter_received)
								this.generalForm.letter_received = response.data.letter_received;
							if (response.data.letter_received == 'yes') {
								this.alertShow = true;
								this.weeks = response.data.letter_weeks;
								this.days = response.data.letter_days;
							}
							if(response.data.subject)
								this.generalForm.subject = response.data.subject;
							
							if(response.data.municipalities.municipalities)
								this.municipalities = response.data.municipalities.municipalities;
							var i;
							for (i = 0; i < this.municipalities.length; i++) {
								this.municipality_items.push(this.municipalities[i].name + ' (' + this.municipalities[i].department + ')');
								if (response.data.municipality == this.municipalities[i].name)
									this.generalForm.municipality = this.municipalities[i].name + ' (' + this.municipalities[i].department + ')';
							}
							this.getMunicipality();
						}).catch(response => {
						console.log("error");
					});
				}
				else{
					axios.get('/api/fax/general/getform_ingebreke')
						.then(response => {
							if(response.data.result === "block"){
								this.dialog = true;
								this.err_dialog_content = response.data.message;
							}
							if(response.data.app_type) {
								this.generalForm.app_type = response.data.app_type
								this.selectAppType();
							}
							if(response.data.app_data) {
								this.generalForm.app_data = parseInt(response.data.app_data);
								this.setAppData();
							}
							if(response.data.request_date){
								this.generalForm.request_date = response.data.request_date;
							}
							if(response.data.letter_received)
								this.generalForm.letter_received = response.data.letter_received;
							if (response.data.letter_received == 'yes') {
								this.alertShow = true;
								this.weeks = response.data.letter_weeks;
								this.days = response.data.letter_days;
							}
							if(response.data.subject)
								this.generalForm.subject = response.data.subject;
								
							if(response.data.municipalities.municipalities)
								this.municipalities = response.data.municipalities.municipalities;
							var i;
							for (i = 0; i < this.municipalities.length; i++) {
								this.municipality_items.push(this.municipalities[i].name + ' (' + this.municipalities[i].department + ')');
								if (response.data.municipality == this.municipalities[i].name)
									this.generalForm.municipality = this.municipalities[i].name + ' (' + this.municipalities[i].department + ')';
							}
							this.getMunicipality();
						}).catch(response => {
						console.log("error", response);
					});
				}
			},
			onSave(){
				// console.log(this.generalForm.app_data.index);
				// return false;
				if (this.$refs.form.validate() && !this.dateValidationMessage && !this.err_dialog_content) {
					let generalForm = new FormData();
					generalForm.append('app_type', this.generalForm.app_type);
					generalForm.append('app_data', this.generalForm.app_data.index);
					generalForm.append('request_date', this.generalForm.request_date);
					generalForm.append('letter_received', this.generalForm.letter_received);
					generalForm.append('letter_weeks', this.weeks);
					generalForm.append('letter_days', this.days);
					generalForm.append('subject', this.generalForm.subject);
					var idx = 0;
					for(idx = 0; idx < this.municipality_items.length; idx ++) {
						if (this.municipality_items[idx] == this.generalForm.municipality)
							break;
					}
					console.log(this.municipalities[idx].name);
					generalForm.append('municipality', this.municipalities[idx].name);
					this.loading = true;
					axios.post('/api/fax/general/saveform_ingebreke', generalForm)
					.then(response =>  {
						this.$emit("changeStep", 2);
						localStorage.setItem('government_name', this.generalForm.municipality);
						
						this.$router.push({
							name: 'client'
						})
						this.loading = false;
					}).catch(error => {
						this.loading = false;
					});
				}
			},
			onEmailSubmit(){
				if(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(this.dateNotifyEmail)){
					this.loading = true;
					let generalForm = new FormData();
					generalForm.append('notify_email', this.dateNotifyEmail);
					generalForm.append('days', this.diff_days);
					console.log(this.diff_days);
					axios.post('/api/fax/general/savenotifyemail', generalForm)
						.then(response =>  {
							this.loading = false;
							this.successSnackbar = true;
							this.success_msg = 'Saved Successfully!';
						}).catch(error => {
						this.loading = false;
						this.errorSnackbar = true;
						this.err_msg = 'An error occurred.';
					});
				}
				else{
					this.errorSnackbar = true;
					this.err_msg = 'Vul hier een geldig emailadres in.';
				}
			},
			showAlert(truthy){
				if(truthy == 1)
					this.alertShow = true;
				else if(truthy == 0)
					this.alertShow = false;
			},
			selectAppType(){
				this.generalForm.app_data = '';
				if(this.generalForm.app_type === 'Aanvraag'){
					this.title_request_date = 'Kies de datum van uw aanvraag:';
					this.app_data = [
						{
							index: 1,
							period: 56,
							value: "Standaard beslistermijn aanvraag (8 weken)",
						},
						{
							index: 2,
							period: 56,
							value: "Wmo voorziening",
						},
						{
							index: 3,
							period: 56,
							value: "Individuele inkomsentoeslag",
						},
						{
							index: 4,
							period: 56,
							value: "Bijstandsuitkering (Participatiewet)",
						},
						{
							index: 5,
							period: 56,
							value: "Bijzondere bijstand (Participatiewet)",
						},
						{ divider: true },
						{
							index: 6,
							period: 56,
							value: "WIA uitkering",
						},
						{
							index: 7,
							period: 28,
							value: "WW uitkering",
						},
						{
							index: 8,
							period: 28,
							value: "ZW uitkering",
						},
						{ divider: true },
						{
							index: 9,
							period: 35,
							value: "Belastingdienst - Zorgtoeslag",
						},
						{
							index: 10,
							period: 35,
							value: "Belastingdienst - Huurtoeslag",
						},
						{
							index: 11,
							period: 35,
							value: "Belastingdienst - Kinderopvangtoeslag",
						},
						{
							index: 12,
							period: 35,
							value: "Belastingdienst - Kindgebondenbudget",
						},
						{ divider: true },
						{
							index: 13,
							period: 56,
							value: "Omgevingsvergunning (eenvoudige aanvraag)",
						},
						{
							index: 14,
							period: 56,
							value: "Schuldhulpverlening",
						}
					];
				}
				else if(this.generalForm.app_type === 'Bezwaarschrift'){
					this.title_request_date = 'Op welke datum is op uw aanvraag beslist:';
					this.app_data = [
						{
							index: 1,
							period: 84,
							value: "Standaard beslistermijn bezwaar (12 weken)",
						},
						{
							index: 2,
							period: 84,
							value: "Wmo voorziening",
						},
						{
							index: 3,
							period: 84,
							value: "Individuele inkomsentoeslag",
						},
						{
							index: 4,
							period: 84,
							value: "Bijstandsuitkering (Participatiewet)",
						},
						{
							index: 5,
							period: 84,
							value: "Bijzondere bijstand (Participatiewet)",
						},
						{ divider: true },
						{
							index: 6,
							period: 84,
							value: "WIA uitkering",
						},
						{
							index: 7,
							period: 91,
							value: "WW uitkering",
						},
						{
							index: 8,
							period: 91,
							value: "ZW bezwaar tegen hoogte dagloon",
						},
						{
							index: 9,
							period: 119,
							value: "ZW met medisch onderzoek",
						},
						{ divider: true },
						{
							index: 10,
							period: 84,
							value: "Belastingdienst - Zorgtoeslag",
						},
						{
							index: 11,
							period: 84,
							value: "Belastingdienst - Huurtoeslag",
						},
						{
							index: 12,
							period: 84,
							value: "Belastingdienst - Kinderopvangtoeslag",
						},
						{
							index: 13,
							period: 84,
							value: "Belastingdienst - Kindgebondenbudget",
						}
					];
				}
				else{
					this.title_request_date = 'Op welke datum is op uw aanvraag beslist:';
					this.app_data = [
						{
							index: 1,
							period: 126,
							value: "Standaard beslistermijn bezwaar commissie (18 weken)",
						}
					];
				}
			},
			setAppData(){
				var data_index = this.generalForm.app_data;
				let arrMatch = this.app_data.filter(function(x){
					return x.index == data_index;
				});
				this.generalForm.app_data = arrMatch[0];
			},
			getMunicipality(){
				var name = this.generalForm.municipality;
				let arrMatch = this.municipalities.filter(function(x){
					return x.name == name;
				});
				this.municipality = arrMatch[0];
			},
			gettanggal(str) {
				if (str != null) {
					return str.substring(8, 10)+'-'+str.substring(5, 7)+'-'+str.substring(0, 4);
				}
				return '';
			},
			changeWeekandDays(e){
				if(e.target.id === 'weeks'){
					if(e.keyCode <= 47 || e.keyCode >= 58){
						e.preventDefault();
					}
					else{
						this.days = '';
					}
				}
				else {
					if(e.keyCode <= 47 || e.keyCode >= 58){
						e.preventDefault();
					}
					else{
						this.weeks = '';
					}
				}
			},
			limitSubjectCount(evt) {
				if (this.generalForm.subject.length >= 35) {
					if ((evt.keyCode >= 48 && evt.keyCode <= 90) 
						|| (evt.keyCode >= 96 && evt.keyCode <= 111)
						|| (evt.keyCode >= 186 && evt.keyCode <= 222) ) {
						evt.preventDefault();
						this.show_sub_overmsg = true;
					}
					else {
						this.show_sub_overmsg = false;
					}
				}
			},
			limitSubjectPaste(evt) {
				var clipdata = evt.clipboardData.getData('text/plain');
				
				if (this.generalForm.subject.length + clipdata.length >= 35) {
					evt.preventDefault();
					var str = this.generalForm.subject + clipdata;
					this.generalForm.subject = str.substr(0, 35);
					this.show_sub_overmsg = true;
				}
				else {
					this.show_sub_overmsg = false;
				}
			}
		}
	}
</script>

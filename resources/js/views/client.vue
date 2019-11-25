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
							<span class="headline">CLIENT INFORMATION</span>
						</v-container>
					</v-card-title>
					<v-card-text>
						<v-container grid-list-md>
							<v-layout wrap>
								<v-flex xs12 sm12 md12>
									<h3>* Gender:</h3>
									<v-radio-group v-model="clientForm.gender" row :rules="genderRules">
										<v-radio label="Meneer" value="meneer"></v-radio>
										<v-radio label="Mevrouw" value="mevrouw"></v-radio>
									</v-radio-group>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* First Name(s):</h3>
									<v-text-field v-model="clientForm.firstname" :rules="firstnameRules"></v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* Last Name:</h3>
									<v-text-field v-model="clientForm.lastname" :rules="lastnameRules"></v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* Email:</h3>
									<v-text-field v-model="clientForm.email" :rules="emailRules"></v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* Postal code:</h3>
									<v-text-field v-model="clientForm.postcode" :rules="postcodeRules" @change="getAddress()"></v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* House Number:</h3>
									<v-text-field v-model="clientForm.housenumber" :rules="housenumberRules" @change="getAddress()"></v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>Phone Number:</h3>
									<v-text-field
									v-model="clientForm.telephone"
									:rules="telephoneRules"
									mask="##########">
									>
									</v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12 v-if="form_type=='form_ingebreke'">
									<h3>* IBAN Bank Number:</h3>
									<v-text-field
									v-model="clientForm.banknumber"
									:rules="banknumberRules"
									mask="aa##aaaa##########"
									></v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* Address:</h3>
									<v-text-field v-model="clientForm.address" :rules="addressRules"></v-text-field>
								</v-flex>
								<v-flex xs12 sm12 md12>
									<h3>* City:</h3>
									<v-text-field v-model="clientForm.city" :rules="cityRules"></v-text-field>
								</v-flex>
							</v-layout>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-container>
							<v-layout justify-end>
								<v-btn dark color="primary" outline @click="onPrev">Prev</v-btn>
								<v-btn dark color="primary" @click="onSave()" :loading="this.loading">Next</v-btn>
							</v-layout>
						</v-container>
					</v-card-actions>
				</v-card>
			</v-form>
		</v-container>
	</div>
</template>

<script>
	export default {
		data () {
			return {
				form_type: "",
				valid: true,
				valid_address: false,

				loading: false,
				government_name: '',
				clientForm: {
					gender: '',
					firstname: '',
					lastname: '',
					housenumber: '',
					postcode: '',
					email: '',
					telephone: '',
					banknumber: '',
					address: '',
					city: '',
				},
				defaultItem: {
					request_date: '',
				},

				genderRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				firstnameRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				lastnameRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				postcodeRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				housenumberRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				telephoneRules: [
					v => /(^$)|^([0-9]{10}$)/.test(v) || 'Voer een telefoonnummer in met tien cijfers.',

				],
				banknumberRules: [
					v => !!v || 'Dit is een verplicht veld.',
					v => /^([a-zA-Z]{2}[0-9]{2}[a-zA-Z]{4}[0-9]{10}$)/.test(v) || 'Voer een geldig bankrekeningnummer in.',
				],
				addressRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				cityRules: [
					v => !!v || 'Dit is een verplicht veld.',
				],
				emailRules: [
					v => !!v || 'Dit is een verplicht veld.',
					v => /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(v) || 'Vul hier een geldig emailadres in.'
				]
			}
		},

		created(){
			this.init();
		},

		methods: {
			init(){
				this.form_type = localStorage.form_type;
				console.log(this.form_type);
				if (this.form_type == undefined)
					this.$router.push({
						name: 'form_ingebreke'
					});
				if(this.$route.params.government_name){
					this.government_name = this.$route.params.government_name;
				}
				axios.get('/api/fax/client/get')
				.then(response => {
					if(response.data.gender)
						this.clientForm.gender = response.data.gender;
					if(response.data.firstname)
						this.clientForm.firstname = response.data.firstname;
					if(response.data.lastname)
						this.clientForm.lastname = response.data.lastname;
					if(response.data.postcode)
						this.clientForm.postcode = response.data.postcode;
					if(response.data.housenumber)
						this.clientForm.housenumber = response.data.housenumber;
					if(response.data.telephone)
						this.clientForm.telephone = response.data.telephone;
					if(response.data.email)
						this.clientForm.email = response.data.email;
					if(response.data.banknumber)
						this.clientForm.banknumber = response.data.banknumber;
					if(response.data.address)
						this.clientForm.address = response.data.address;
					if(response.data.city)
						this.clientForm.city = response.data.city;
				}).catch(response => {
					console.log("error");
				});
			},
			onPrev(){
				this.$router.push({
					name: this.form_type
				})
			},
			onSave(){
				if (this.$refs.form.validate()) {
					let clientForm = new FormData();
					clientForm.append('gender', this.clientForm.gender);
					clientForm.append('firstname', this.clientForm.firstname);
					clientForm.append('lastname', this.clientForm.lastname);
					clientForm.append('postcode', this.clientForm.postcode);
					clientForm.append('housenumber', this.clientForm.housenumber);
					clientForm.append('email', this.clientForm.email);
					clientForm.append('telephone', this.clientForm.telephone);
					clientForm.append('banknumber', this.clientForm.banknumber);
					clientForm.append('address', this.clientForm.address);
					clientForm.append('city', this.clientForm.city);
					this.loading = true;
					axios.post('/api/fax/client/save', clientForm)
					.then(response =>  {
						if(response.data.result === "success") {
							this.$emit("changeStep", 3);
							localStorage.setItem('bank_number', this.clientForm.banknumber);
							localStorage.setItem('firstname', this.clientForm.firstname);
							localStorage.setItem('email', this.clientForm.email);
							this.$router.push({
								name: 'sign'
							})
						}
						this.loading = false;
					}).catch(error => {
						this.loading = false;
						// this.$message({
					 //        type: 'error',
					 //        message: response.data.message
					 //    });
					});
				}
			},
			getAddress(){
				if(this.clientForm.postcode != '' && this.clientForm.housenumber != ''){
					let addressForm = new FormData();
					addressForm.append('postal', this.clientForm.postcode);
					addressForm.append('house', this.clientForm.housenumber);
					axios.post('/api/fax/post', addressForm)
					.then(response =>  {
						if(response.data.status == 1){
							console.log(response.data);
							this.valid_address = true;
							this.clientForm.address = response.data.data.address;
							this.clientForm.city = response.data.data.city;
						}
					}).catch(error => {
						// this.$message({
					 //        type: 'error',
					 //        message: response.data.message
					 //    });
					});
				}
			}
		}
	}
</script>

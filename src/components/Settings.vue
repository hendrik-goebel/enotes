<template>
	<div class="card card-1" v-show="isInitialized">
		<form @submit.prevent="">
			<div class="settings-group">
				<div class="group-title">
					<h3>Email accounts</h3>
					<div>
						Activate Email Acccounts which will be scanned for
						emails which contain ebook reader note attachments
					</div>
				</div>

				<div v-for="account in settings.mailAccountSettings"
					 class="form-check">
					<input
						class="checkbox"
						type="checkbox"
						name="accounts[]"
						v-model="account.active"
						:id="'account-' + account.id"
						@change="change()"
					>
					<label :for="'account-' + account.id">
						{{ account.email }}
					</label>
				</div>
			</div>

			<div class="settings-group">
				<div class="group-title">
					<h3>Email sender addresses</h3>
					<div>
						Emails from this senders will be scanned (comma
						separated list)
					</div>
				</div>
				<div>
					<input type="text"
						   class="settings-text"
						   name="types"
						   v-model="settings.types"
					>
				</div>

			</div>

			<div class="settings-group">
				<button @click="fetch"
						class="primary">
					Fetch notes from emails
				</button>
			</div>
		</form>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import routes from '../routes'

export default {
	name: 'Settings',
	data() {
		return {
			isInitialized: false,
			settings: {}
		}
	},
	mounted() {
		this.get()
	},
	methods: {
		change() {
			this.updateSettings()
		},
		get() {
			const vm = this
			vm.$emit('requestStarted')
			axios
				.get(routes.getSettings)
				.then(function (response) {
					vm.settings = JSON.parse(response.data)
					vm.isInitialized = true
					vm.$emit('requestSucceeded', response)
				})
				.catch(function (response) {
					vm.$emit('requestFailed', response)
				})
		},
		updateSettings() {
			const vm = this
			vm.$emit('requestStarted')
			axios
				.put(routes.updateSettings, {settings: vm.settings})
				.then(function (response) {
					vm.$emit('requestSucceeded', response)
				})
				.catch(function (response) {
					vm.$emit('requestFailed', response)
				})
		},
		fetch() {
			const vm = this
			this.$emit('requestStarted')
			axios
				.get(routes.getFetch)
				.then(function (response) {
					vm.$emit('requestSucceeded', response)
				})
				.catch(function (response) {
					vm.$emit('requestFailed', response)
				})
		},
	},
	watch: {
		'settings.types': function () {
			setTimeout(this.updateSettings, 1000)

		},
	}
}
</script>

<template>
	<div v-show="isInitialized" class="card card-1">
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
					:key="account.id"
					class="form-check">
					<input
						:id="'account-' + account.id"
						v-model="account.active"
						class="checkbox"
						type="checkbox"
						name="accounts[]"
						@change="change()">
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
					<input v-model="settings.types"
						type="text"
						class="settings-text"
						name="types">
				</div>
			</div>

			<div class="settings-group">
				<button class="primary"
					@click="fetch">
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
			settings: {},
		}
	},
	watch: {
		'settings.types'() {
			setTimeout(this.updateSettings, 1000)

		},
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
				.then(function(response) {
					vm.settings = JSON.parse(response.data)
					vm.isInitialized = true
					vm.$emit('requestSucceeded', response)
				})
				.catch(function(response) {
					vm.$emit('requestFailed', response)
				})
		},
		updateSettings() {
			const vm = this
			vm.$emit('requestStarted')
			axios
				.put(routes.updateSettings, { settings: vm.settings })
				.then(function(response) {
					vm.$emit('requestSucceeded', response)
				})
				.catch(function(response) {
					vm.$emit('requestFailed', response)
				})
		},
		fetch() {
			const vm = this
			this.$emit('requestStarted')
			axios
				.get(routes.getFetch)
				.then(function(response) {
					vm.$emit('requestSucceeded', response)
				})
				.catch(function(response) {
					vm.$emit('requestFailed', response)
				})
		},
	},
}
</script>

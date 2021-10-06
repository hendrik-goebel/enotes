<template>
	<div>
		<div v-if="alertMessage" :class="alertClass" @click="clear">
			{{ alertMessage }}
		</div>
	</div>
</template>

<script>

export default {
	name: 'Alert',
	data() {
		return {

		}
	},

	computed: {
		alertMessage() {
			return this.$store.state.alert.message
		},
		alertClass() {
			if (this.$store.state.alert.type === 'error') {
				return 'alert alert-error'
			}
			if (this.$store.state.alert.type === 'success') {
				return 'alert alert-success'
			}
			return ''
		},
	},

	methods: {
		clear() {
			this.$store.state.alert.message = null
		},
	},
}
</script>

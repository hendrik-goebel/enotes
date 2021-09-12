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

	methods: {
		clear() {
			this.$store.state.alert.message = null
		}
	},

	computed: {
		alertMessage() {
			return this.$store.state.alert.message
		},
		alertClass() {
			if (this.$store.state.alert.type == 'error') {
				return 'alert alert-error'
			}
			if (this.$store.state.alert.type == 'success') {
				return 'alert alert-success'
			}
		}
	}
}
</script>

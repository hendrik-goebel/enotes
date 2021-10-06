import Vue from 'vue'
import Vuex from 'vuex'

import { VIEW, ALERT } from './constants'

Vue.use(Vuex)

const store = new Vuex.Store({
	strict: false,
	state: {
		currentView: 'notes',
		alert: {
			type: null,
			message: null,
		},
	},
	mutations: {
		increment(state) {
			state.count++
			console.log(state.count)
		},
		toggleSettingsView(state) {
			state.currentView = state.currentView === VIEW.NOTES ? VIEW.SETTINGS : VIEW.NOTES
		},
		alertError(state, message) {
			state.alert.type = ALERT.ERROR
			state.alert.message = message
		},
		alertSuccess(state, message) {
			state.alert.type = ALERT.SUCCESS
			state.alert.message = message
		},
		clearAlert(state) {
			state.alert.type = null
			state.alert.message = null
		},
	},
})

export default store

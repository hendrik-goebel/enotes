<template>
	<Content app-name="enotes">
		<AppNavigation>
			<template id="app-enotes-navigation" #list>
				<AppNavigationItem v-for="book in books"
					:key="book.id"
					:title="book.title"
					:allow-collapse="false"
					icon="icon-folder"
					@click="selectBook(book),selectView('note')" />

				<AppNavigationItem title="Settings"
					icon="icon-settings-dark"
					:pinned="true"
					@click="toggleSettingsView()" />
			</template>
		</AppNavigation>
		<AppContent>
			<Alert />
			<div v-show="isLoadingIndicator" class="icon-loading" />
			<div id="container">
				<Settings
					v-if="currentView == 'settings'"
					@requestFailed="handleError($event)"
					@requestStarted="onRequestStarted"
					@requestSucceeded="onRequestSucceeded($event)" />

				<div v-if="currentView === 'notes'">
					<Note v-for="{note, index} in book.notes"

						:key="index"
						:note="note"
						:book="book" />
				</div>
			</div>
		</AppContent>
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import axios from '@nextcloud/axios'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import Note from './components/Note'
import Alert from './components/partials/Alert'
import Settings from './components/Settings'
import routes from './routes'

export default {
	name: 'App',
	components: {
		Content,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		Note,
		Settings,
		Alert,
	},
	data() {
		return {
			showLoadingIcon: false,
			show: false,
			starred: false,
			settings: {},
			isLoading: false,
			isLoadingIndicator: false,
			note: {
				type: '',
				content: '',
				location: '',
			},
			book: {
				id: 0,
				title: '',
				notes: [],
			},
			books: [],
		}
	},

	computed: {
		currentView() {
			return this.$store.state.currentView
		},
	},

	mounted() {
		this.getNotes()
	},
	methods: {
		handleError(error) {
			this.hideLoadingIndicator()
			if (error.response) {
				// Request made and server responded
				this.alertError(error.response.data)
			} else if (error.request) {
				// The request was made but no response was received
				console.error(error.request)
			} else {
				// Something happened in setting up the request that triggered an Error
				console.error('Error', error.message)
			}
		},
		onRequestStarted() {
			this.showLoadingIndicator()
		},
		onRequestSucceeded(response) {
			this.hideLoadingIndicator()
			if (response.data.message) {
				this.alertSuccess(response.data.message)
			}
		},
		alertSuccess(message, type) {
			this.$store.commit('alertSuccess', message)
		},
		alertError(message, type) {
			this.$store.commit('alertError', message)
		},
		clearAlert() {
			this.$store.commit('clearAlert')
		},
		showLoadingIndicator() {
			this.isLoading = true

			setTimeout(() => {
				if (this.isLoading) {
					this.isLoadingIndicator = true
				}
			}, 500)
		},
		hideLoadingIndicator() {
			this.isLoading = false
			this.isLoadingIndicator = false
		},
		toggleSettingsView() {
			this.initView()
			this.$store.commit('toggleSettingsView')
		},
		getNotes() {
			const vm = this

			axios
				.get(routes.getNotes)
				.then(function(response) {
					vm.books = JSON.parse(response.data)
					if (typeof vm.books !== 'undefined' && vm.books.length > 0) {
						vm.selectBook(vm.books[0])
					}
				})
				.catch(function(error) {
					vm.handleError(error)
				})
		},

		selectNote(note) {
			this.note = note
			this.book = this.books.filter(function(book) {
				return (book.id === note.bookId)
			}).pop()
		},

		updateSettings() {
			const vm = this
			axios
				.put(this.routes.updateSettings, vm.settings)
				.then(function(response) {
					vm.settings = JSON.parse(response.data)
				})
				.catch(function(error) {
					vm.handleError(error)
				})
		},
		selectBook(book) {
			this.book = book
		},
		initView() {
			this.hideLoadingIndicator()
			this.clearAlert()
		},
	},
}
</script>

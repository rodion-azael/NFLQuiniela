do () ->
	angular
		.module('nfl', [
			'ui.router',
			'ui.bootstrap',
			'ngStorage',
			'datetimepicker',
			'angular-loading-bar'
		])
		.config  ($stateProvider, $urlRouterProvider) ->
			$urlRouterProvider.otherwise "weeks"
			$stateProvider
				.state 'login',
					url: '/login'
					controller: 'loginCtrl'
					controllerAs: 'vm'
					templateUrl: 'partials/login.html'
					data: 
						admin: false
				.state 'main',
					url: '/'
					templateUrl: 'partials/main.html'
					controller: 'nflMainCtrl'
					controllerAs: 'vm'
					data: 
						admin: false
				.state 'main.weeks',
					url: 'weeks'
					controllerAs: 'vm'
					data: 
						admin: false
					views:
						'viewA':
							templateUrl: 'partials/weeks.html'
							controller: 'weeksCtrl'
							controllerAs: 'vm'				
				.state 'main.viewResults',
					url: 'viewResults/:weekID/:week'
					controllerAs: 'vm'
					data: 
						admin: false
					views:
						'viewA':
							templateUrl: 'partials/viewResults.html'
							controller: 'viewResultsCtrl'
							controllerAs: 'vm'				
				.state 'main.globalResults',
					url: 'globalResults'
					controllerAs: 'vm'
					data: 
						admin: false
					views:
						'viewA':
							templateUrl: 'partials/globalResults.html'
							controller: 'globalResultsCtrl'
							controllerAs: 'vm'
				.state 'main.matchs',
					url: 'matchs/:weekID/:week'
					controllerAs: 'vm'
					data: 
						admin: true
					views:
						'viewA':
							templateUrl: 'partials/matchs.html'
							controller: 'matchsCtrl'
							controllerAs: 'vm'
				.state 'main.inputs',
					url: 'inputs/:weekID/:week'
					controllerAs: 'vm'
					data: 
						admin: false
					views:
						'viewA':
							templateUrl: 'partials/userInputs.html'
							controller: 'userInputsCtrl'
							controllerAs: 'vm'
				.state 'main.results',
					url: 'results/:weekID/:week'
					controllerAs: 'vm'
					data: 
						admin: true
					views:
						'viewA':
							templateUrl: 'partials/captureResults.html'
							controller: 'captureResults'
							controllerAs: 'vm'
				.state 'main.profile',
					url: 'profile'
					controllerAs: 'vm'
					data: 
						admin: false
					views:
						'viewA':
							templateUrl: 'partials/profile.html'
							controller: 'profileCtrl'
							controllerAs: 'vm'
			return
		.run ($rootScope, $localStorage, $state) ->
			if !$localStorage.user
				$state.go 'login', {}
			$rootScope.$on '$stateChangeStart', 
				(event, toState, toParams, fromState, fromParams, options) ->
					if !$localStorage.user and toState.name isnt 'login'
						do event.preventDefault
						$state.go 'login', {}
					if toState.data.admin  && $localStorage.user.IsAdmin isnt "1"
						do event.preventDefault
						$state.go 'main.weeks', {}
					return
			return
	return
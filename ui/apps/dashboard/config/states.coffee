angular.module 'gps.dashboard'
.config ($stateProvider, $urlRouterProvider) ->
  
  $stateProvider.state 'dashboard',
    url: '/overview'
    template: "<dashboard-page></dashboard-page>"

  $urlRouterProvider.otherwise ->
    return '/overview'
  
angular.module 'gps.admin'
.directive 'overviewResults', (
  $http,
  modals
) ->
  return {
    restrict: 'E'
    scope:
      results: '='
    templateUrl: '/apps/admin/directives/overview-results/overview-results.html'
    link: (scope, elem, attrs) -> as scope, ->

      @launchProfileModal = (userId) ->
        modals.launch 'candidate-profile', { userId: userId }
  }
  
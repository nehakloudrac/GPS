angular.module 'gps.common.profile-print-view'
.directive 'profilePrintView', (
  $http
) ->
  return {
    restrict: 'E'
    templateUrl: '...'
    scope:
      profile: '='
      user: '='
    link: (scope, elem, attrs) ->
      @foo = 'bar'
  }
  
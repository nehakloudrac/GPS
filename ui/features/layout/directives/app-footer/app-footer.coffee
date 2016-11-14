angular.module 'gps.common.layout'
.directive 'appFooter', ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/features/layout/directives/app-footer/app-footer.html'
  }
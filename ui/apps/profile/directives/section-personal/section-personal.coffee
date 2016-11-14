angular.module 'gps.profile'
.directive 'sectionPersonal', ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/section-personal/section-personal.html'
  }
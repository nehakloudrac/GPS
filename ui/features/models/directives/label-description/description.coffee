angular.module 'gps.common.models'
.directive 'labelDescription', ->
  return {
    restrict: 'E'
    scope:
      text: "@"
    templateUrl: '/features/models/directives/label-description/description.html'
    link: (scope, elem, attrs) -> as scope, ->
      @popover =
        content: @text
  }
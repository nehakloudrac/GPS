angular.module 'gps.admin'
.directive 'adminPage', (layout, $state) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: "/apps/admin/directives/admin-page/admin-page.html"
    link: (scope, elem, attrs) -> as scope, ->
      @layout = layout
      @state = layout.getState()
      
      @$on '$stateChangeSuccess', =>
        @state = layout.getState()
  }

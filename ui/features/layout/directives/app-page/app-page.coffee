angular.module 'gps.common.layout'
.directive 'appPage', ->
  restrict: 'E'
  transclude: true
  scope:
    showMenu: '&'
  templateUrl: '/features/layout/directives/app-page/app-page.html'
  link: (scope, elem, attrs) ->
    scope.showUserMenu = =>
      return if attrs.showMenu? then scope.showMenu() else true
angular.module 'gps.common.layout'
.directive 'minWindowHeight', ($window) ->
  return {
    restrict: 'A'
    link: (scope, elem, attrs) ->
      update = -> elem.css('min-height', $window.innerHeight + 'px')

      update()

      angular.element($window).bind 'resize', update
  }
  

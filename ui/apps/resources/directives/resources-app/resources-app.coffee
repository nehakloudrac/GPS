angular.module 'gps.resources'
.directive 'resourcesApp', (layout, $window) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/resources/directives/resources-app/resources-app.html'
    link: (scope, elem, attrs) -> as scope, ->
      @layout = layout
      
      #unsure if this really needs to be in a timeout, but I did it that way originally
      #for reasons that I don't remember, so leaving it that way
      setTimeout (->
        if $window.twttr
          $window.twttr.widgets.load()
      ), 0
  }
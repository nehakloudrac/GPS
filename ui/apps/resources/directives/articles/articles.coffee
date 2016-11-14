angular.module 'gps.resources'
.directive 'articles', (
  $window,
  newsStories,
  factsAndFigures
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/resources/directives/articles/articles.html'
    link: (scope, elem, attrs) -> as scope, ->
      @factsIndex = 0

      # not sure if this really needs to be in a timeout, but it seems safer that way
      setTimeout (->
        if $window.twttr
          $window.twttr.widgets.load()
      ), 0
      
      factsInterval = setInterval (=>
        @factsIndex += 1
        if @factsIndex >= @factsAndFigures.length
          @factsIndex = 0
        @$digest()
      ), 12000
      
      @newsStories = newsStories[0..3]
      @factsAndFigures = factsAndFigures[0..3]
  }
  
  
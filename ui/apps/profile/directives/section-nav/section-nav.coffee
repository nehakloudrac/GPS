angular.module 'gps.profile'
.directive 'sectionNav', (layout) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/section-nav/section-nav.html'
    scope:
      nextText: '@'
      nextState: '@'
      nextStateArgs: '=?'
      nextAction: '&'
      prevText: '@'
      prevState: '@'
      prevStateArgs: '=?'
      prevAction: '&'
      message: '@'
    link: (scope, elem, attrs) -> as scope, ->
      @prevText = if @prevText? then @prevText else 'Previous'
      @nextText = if @nextText? then @nextText else 'Next'
      
      @showNext = true
      if 'false' == @nextState || 'false' == @nextText
        @showNext = false
      @showPrev = true
      if 'false' == @prevState || 'false' == @prevText
        @showPrev = false
      
      @next = () =>
        if attrs.nextAction?
          @nextAction()
          return
        
        if @nextStateArgs?
          layout.go @nextState, @nextStateArgs
        else
          layout.go @nextState
      
      @prev = () =>
        if attrs.prevAction?
          @prevAction()
          return
          
        if @prevStateArgs?
          layout.go @prevState, @prevStateArgs
        else
          layout.go @prevState
  }

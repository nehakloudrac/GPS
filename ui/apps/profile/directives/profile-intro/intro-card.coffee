angular.module 'gps.profile'
.directive 'introCard', (
  
) ->
  return {
    restrict: 'E'
    scope:
      canSkip: '='
      active: '&'
      onSkip: '&'
      onContinue: '&'
      continueCheck: '&'
    transclude: true
    templateUrl: '/apps/profile/directives/profile-intro/intro-card.html'
    link: (scope, elem, attrs) -> as scope, ->
      @canSkip = if attrs.canSkip? then @canSkip else true

      @isActive = =>
        return if attrs.active? then @active() else false
      
      #check for either a boolean or promise return
      @tryContinue = =>
        if attrs.continueCheck?
          res = @continueCheck()
          if res == true
            @onContinue()
            return
          if res == false
            console.log 'computer says no'
          if res?.then?
            res.then (ok) =>
              @onContinue() if ok
  }

angular.module 'gps.profile'
.directive 'profileCard', () ->
  return {
    restrict: 'E'
    scope:
      showWhen: '&'           #whether or not to show
      enabledWhen: '&'        #navigable
      expandWhen: '&'         #whether or not to expand
      onExpand: '&'           #what to do if clicked
      title: '@'              #title to show
      titleWarningWhen: '&'   #whethor or not to show warning symbol
      titleWarningText: '@'   #text to show in warning
    transclude: true
    templateUrl: '/apps/profile/directives/profile-cards/profile-card.html'
    link: (scope, elem, attrs) -> as scope, ->
      @show = => return if attrs.showWhen? then @showWhen() else true
      @enabled = => return if attrs.enableWhen? then @enabledWhen() else true
      @expanded = => return if attrs.expandWhen? then @expandWhen() else false
      
      @toggleExpand = =>
        return if !@enabled
        @onExpand() if attrs.onExpand?
  }
  
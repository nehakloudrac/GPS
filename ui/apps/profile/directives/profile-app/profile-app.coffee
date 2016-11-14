angular.module 'gps.profile'
.directive 'profileApp', (
  layout
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/profile-app/profile-app.html'
    scope: {}
    link: (scope, elem, attrs) -> as scope, ->
      @layout = layout
      @previousEditState = null

      @edit = =>
        if @previousEditState
          layout.go @previousEditState.name, @previousEditState.args
          return
        layout.go 'profile.edit.background'
      
      @$on '$stateChangeSuccess', (e, toState, toParams, fromState, fromParams) =>
        if layout.includes 'profile.edit'
          @previousEditState =
            name: toState.name
            args: _.cloneDeep toParams
  }
  
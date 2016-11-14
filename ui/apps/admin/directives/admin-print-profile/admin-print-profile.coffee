angular.module 'gps.admin'
.directive 'adminPrintProfile', (
  $http,
  $stateParams,
  $state,
  $window,
  layout
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/admin/directives/admin-print-profile/admin-print-profile.html'
    scope: {}
    link: (scope, elem, attrs) -> as scope, ->
      @error = null
      @promise = null
      @user = null
      @profile = null
      @hideIncomplete = true
      @showContact = true
      @layout = layout
      
      @print = ->
        $window.print()
      
      loadProfile = =>
        @promise = $http.get "/api/admin/users/#{$stateParams.userId}/details"
        .success (res) =>
          @error = null
          
          @user = res.user
          @profile = res.profile
          
        .error (res) =>
          @error = res

      
      loadProfile()
  }
  
angular.module 'gps.profile'
.directive 'viewProfile', (
  appUserService
  appCandidateProfile
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/view-profile/view-profile.html'
    scope: {}
    link: (scope, elem, attrs) -> as scope, ->
      @user = appUserService.getData()
      @profile = null
      @viewAsEmployer = false
      @promise = appCandidateProfile.refresh()
      @promise.then =>
        @profile = appCandidateProfile.getData()
      
      @toggleEmployerView = =>
        @viewAsEmployer = !@viewAsEmployer
  }

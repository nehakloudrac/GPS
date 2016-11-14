angular.module 'gps.profile'
.directive 'formHonorOrg', (
  $rootScope
  profileFormInitializer
  appCandidateProfile
) ->
  return {
    restrict: 'E'
    scope:
      org: '='
    templateUrl: '/apps/profile/directives/edit-honor-orgs/form-honor-org.html'
    link: (scope, elem, attrs) -> as scope, ->
      @form = {}
      isNew = false
      
      onSuccess = (res) =>
        appCandidateProfile.applyAcademicOrg res.organization
        if isNew
          @$emit 'gps.new-org', res.organization
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onError = (res) ->
        console.error 'Error saving academic org field.'
        init()
      
      init = =>
        if !@org.hash?
          isNew = true
        @form = profileFormInitializer.initializeForm @org, "/academic-organizations", onSuccess, onError, [
          'name'
          'duration'
        ]
      
      @$watch 'org.hash', init
      
      init()
}
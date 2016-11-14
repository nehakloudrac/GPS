angular.module 'gps.profile'
.directive 'formCertification', (
  $rootScope
  profileFormInitializer
  appCandidateProfile
) ->
  return {
    restrict: 'E'
    scope:
      cert: '='
    templateUrl: '/apps/profile/directives/edit-certifications/form-certification.html'
    link: (scope, elem, attrs) -> as scope, ->
      @form = {}
      isNew = false
      
      onSuccess = (res) =>
        appCandidateProfile.applyCertification res.certification
        if isNew
          @$emit 'gps.new-cert', res.certification
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onError = (res) ->
        console.error 'Error saving cert field.'
        init()
      
      init = =>
        if !@cert.hash?
          isNew = true
        @form = profileFormInitializer.initializeForm @cert, "/certifications", onSuccess, onError, [
          'name'
          'organization'
          'certId'
          'duration'
        ]
      
      @$watch 'cert.hash', init
      
      init()
}
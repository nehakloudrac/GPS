angular.module 'gps.profile'
.directive 'formMembershipOrg', (
  $rootScope
  profileFormInitializer
  appCandidateProfile
  config
  labeler
) ->
  return {
    restrict: 'E'
    scope:
      org: '='
    templateUrl: '/apps/profile/directives/edit-membership-orgs/form-membership-org.html'
    link: (scope, elem, attrs) -> as scope, ->
      @form = {}
      isNew = false
      
      @organizationMembershipLevels = config.get 'organizationMembershipLevels'
      
      onSuccess = (res) =>
        appCandidateProfile.applyOrganization res.organization
        if isNew
          @$emit 'gps.new-org', res.organization
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onError = (res) ->
        console.error 'Error saving membership org field.'
        init()
      
      init = =>
        if !@org.hash?
          isNew = true
        @form = profileFormInitializer.initializeForm @org, "/organizations", onSuccess, onError, [
          'institution.name'
          'level'
        ]
      
      @$watch 'org.hash', init
      
      init()
}
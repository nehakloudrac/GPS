angular.module 'gps.profile'
.directive 'formAward', (
  $rootScope
  profileFormInitializer
  appCandidateProfile
  config
) ->
  return {
    restrict: 'E'
    scope:
      award: '='
    templateUrl: '/apps/profile/directives/edit-awards/form-award.html'
    link: (scope, elem, attrs) -> as scope, ->
      @form = {}
      @awardsListNames = config.get 'awardsListNames'

      isNew = false
      
      onSuccess = (res) =>
        appCandidateProfile.applyAward res.award
        if isNew
          @$emit 'gps.new-award', res.award
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onError = (res) ->
        console.error 'Error saving award field.'
        init()
      
      init = =>
        if !@award.hash?
          isNew = true
        @form = profileFormInitializer.initializeForm @award, "/awards", onSuccess, onError, [
          'name'
          'date'
        ]
      
      @$watch 'award.hash', init
      
      init()
}

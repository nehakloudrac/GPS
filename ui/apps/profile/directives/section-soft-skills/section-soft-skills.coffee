angular.module 'gps.profile'
.directive 'sectionSoftSkills', (
  $http,
  appCandidateProfile,
  config
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/section-soft-skills/section-soft-skills.html'
    link: (scope, elem, attrs) -> as scope, ->

      @promise = null
      @softSkills = config.get 'softSkills'
      @profile = appCandidateProfile.getData()
      
      @examples =
        promise: null
        items: ['']

      @saveTraits = (traits) =>
        @promise = @save 'softSkills', traits
      
      @saveExamples = =>
        data = _.reject(@examples.items, (elem) -> return elem.length == 0)
        @examples.promise = @save 'softSkillExamples', data
      
      @save = (key, val) =>
        data = {}
        data[key] = val
        
        promise = appCandidateProfile.put "", data
        .success (res) =>
          appCandidateProfile.setData res.profile
          @reset()
        .error (err) =>
          console.error err
          @reset()
        
        return promise


      @reset = =>
        @profile = appCandidateProfile.getData()
        @examples =
          promise: null
          items: if @profile.softSkillExamples then _.cloneDeep @profile.softSkillExamples else ['']

      @reset()
  }
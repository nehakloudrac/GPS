angular.module 'gps.profile'
.directive 'sectionHardSkills', (
  $http,
  appCandidateProfile,
  config
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/section-hard-skills/section-hard-skills.html'
    link: (scope, elem, attrs) -> as scope, ->

      candidateProfile = appCandidateProfile.getData()

      @hardSkills = config.get 'hardSkills'

      @skillLabels = ['N/A','Once or twice','Rarely','Occasionally','Regularly','All the time']

      @promises = {}
      @hardSkills.forEach (item) =>
        @promises[item.key] = null

      #duplicated because when the values are bound to the sliders, it forces nulls to zeros
      @values = {}
      @rawValues = {}

      @saveField = (field) =>
        #noop if value is unchanged
        return if candidateProfile.hardSkills? && @values[field] == candidateProfile.hardSkills[field]

        data = {hardSkills: {}}
        data.hardSkills[field] = @values[field]
        @promises[field] = $http.put "/api/candidate-profiles/#{candidateProfile.id}", data
        .success (res) =>
          candidateProfile.hardSkills = res.profile.hardSkills
          @reset()
        .error (err) =>
          @reset()
          throw new Error err

      @reset = =>
        @values = if candidateProfile.hardSkills? then _.cloneDeep candidateProfile.hardSkills else {}
        @rawValues = if candidateProfile.hardSkills? then _.cloneDeep candidateProfile.hardSkills else {}

      @reset()
  }
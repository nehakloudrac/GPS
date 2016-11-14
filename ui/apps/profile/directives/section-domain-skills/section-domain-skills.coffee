angular.module 'gps.profile'
.directive 'sectionDomainSkills', (
  appCandidateProfile,
  $rootScope
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/section-domain-skills/section-domain-skills.html'
    scope: {}
    link: (scope, elem, attrs) -> as scope, ->

      #note that we only listen to onAdd - update will trigger
      #saving all skill blocks
      @sortableConfig =
        group:
          name: 'skills'
          put: ['examples','skills']
        animation: 150
        ghostClass: 'ghost'
        onAdd: -> update()

      @examplesConfig =
        sort: false
        group:
          name: 'skills'
          pull: 'clone'

      @examples = ["MS Office", "MySQL", "Python", "Angular.js", "Business plan writing", "Strategic planning", "Market research"]

      @skills = {}

      #model used for inputs when adding to skills
      @newSkills =
        proficient: ''
        expert: ''
        advanced: ''

      @promise = null

      @newSkill = ''

      init = =>
        profile = appCandidateProfile.getData()
        skills = if profile.domainSkills? then profile.domainSkills else {}
        skills.expert ?= []
        skills.advanced ?= []
        skills.proficient ?= []

        @skills = _.cloneDeep skills

      @addSkill = (type) =>
        skill = @newSkills[type].trim()
        @newSkills[type] = ''
        return if skill.length == 0
        
        @skills[type] ?= []
        @skills[type].unshift skill
        update()

      sortFn = (a, b) ->
        return -1 if a.toLowerCase() < b.toLowerCase()
        return 1 if a.toLowerCase() > b.toLowerCase()
        return 0

      update = =>

        #enforce proper limits before saving
        if @skills.expert.length > 5
          @skills.expert = @skills.expert[0...5]
        if @skills.advanced.length > 10
          @skills.advanced = @skills.advanced[0...10]
        if @skills.proficient.length > 15
          @skills.proficient = @skills.proficient[0...15]

        #then sort alphabetically
        if @skills.expert?
          @skills.expert.sort sortFn
        if @skills.advanced?
          @skills.advanced.sort sortFn
        if @skills.proficient?
          @skills.proficient.sort sortFn

        save()

      save = =>
        @promise = appCandidateProfile.put "", { domainSkills: _.cloneDeep @skills }
        .success (res) ->
          appCandidateProfile.setData res.profile
          $rootScope.$broadcast 'gps.appCandidateProfile'
        .error (err) ->
          #TODO: toast
          init()

      @removeItem = (group, index) =>
        @skills[group].splice index, 1
        save()

      @$on 'gps.appCandidateProfile', init

      init()
  }

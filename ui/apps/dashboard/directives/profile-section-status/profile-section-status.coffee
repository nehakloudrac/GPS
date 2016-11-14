angular.module 'gps.dashboard'
.directive 'profileSectionStatusLabel', (layout, $timeout) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/dashboard/directives/profile-section-status/label.html'
    scope:
      label: '@'
      check: '&'
      href: '@'
    link: (scope, elem, attrs) -> as scope, ->
      
      # no, I don't know why I have to do this in a timeout...
      $timeout (=>
        @status = @check()
      ), 0

      @click = =>
        if @href
          window.location = layout.uiPath 'candidate', @href
  }
.directive 'profileSectionStatus', (
  profileCompletenessHelper,
  menuCompletenessHelper,
  layout,
  appCandidateProfile,
  $rootScope
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/dashboard/directives/profile-section-status/profile-section-status.html'
    scope:
      profile: '='
    link: (scope) -> as scope, ->
      @helper = profileCompletenessHelper
      @layout = layout
      @menuHelper = menuCompletenessHelper
      
      @skipSection = (name) =>
        data = if @profile.profileStatus.sectionsSkipped? then _.cloneDeep @profile.profileStatus.sectionsSkipped? else []
        data.push name
        appCandidateProfile.put "", {profileStatus:{sectionsSkipped: _.unique data}}
        .success (res) ->
          $rootScope.$broadcast 'gps.appCandidateProfile'
          

      @percentCompleted =
        globalCompetencies: @menuHelper.getGlobalCompetenciesPercentCompleted()
        workAndEducation: @menuHelper.getWorkAndEducationPercentCompleted()
        skills: @menuHelper.getSkillsPercentCompleted()
        persona: @menuHelper.getPersonaPercentCompleted()
        
      # force percent to minimum of 10 for
      # psychological reasons
      ['globalCompetencies','workAndEducation','skills','persona'].forEach (item) =>
        if @percentCompleted[item] < 10
          @percentCompleted[item] = 10
  }

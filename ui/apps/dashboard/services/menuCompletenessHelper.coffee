class MenuCompletenessHelper
  constructor: (@profileService, @helper) ->
    @profile = @profileService.getData()

  isSkipped: (name) =>
    return false if !@profile.profileStatus?.skippedSections?
    return _.includes @profile.profileStatus.skippedSections, name
  
  getGlobalCompetenciesPercentCompleted: =>
    completed = 0
    completed++ if 'complete' == @getExperiencesAbroadStatus()
    completed++ if 'complete' == @getLanguagesStatus()
    return Math.ceil((completed/2) * 100)

  getWorkAndEducationPercentCompleted: =>
    completed = 0
    completed++ if 'complete' == @getProfessionalHistoryStatus()
    completed++ if 'complete' == @getIdealJobStatus()
    completed++ if 'complete' == @getEducationHistoryStatus()
    completed++ if 'complete' == @getAwardsAndHonorsStatus()
    return Math.ceil((completed/4) * 100)

  getSkillsPercentCompleted: =>
    completed = 0
    completed++ if 'complete' == @getSoftSkillsStatus()
    completed++ if 'complete' == @getHardSkillsStatus()
    completed++ if 'complete' == @getDomainSkillsStatus()
    return Math.ceil((completed/3) * 100)

  getPersonaPercentCompleted: =>
    completed = 0
    completed++ if 'complete' == @getInterestsStatus()
    completed++ if 'complete' == @getHobbiesStatus()
    completed++ if 'complete' == @getCharacterStatus()
    return Math.ceil((completed/3) * 100)

  getExperiencesAbroadStatus: =>
    return 'complete' if @isSkipped 'ExperienceAbroad'
    return 'empty' if !@profile.countries? || @profile.countries.length == 0

    if @profile.countries?
      return 'incomplete' for country in @profile.countries when !@helper.isCountryComplete country
      return 'complete'
    return 'empty'

  getLanguagesStatus: =>
    return 'complete' if @isSkipped 'Languages'
    return 'empty' if !@profile.languages? || @profile.languages.length == 0

    if @profile.languages?
      return 'incomplete' for lang in @profile.languages when (!lang.nativeLikeFluency && !@helper.isLanguageComplete lang)
      return 'complete'
    return 'empty'

  getWorkStatus: =>
    return @getProfessionalHistoryStatus()

  getProfessionalHistoryStatus: =>
    return 'complete' if @isSkipped 'WorkHistory'
    return 'empty' if !@profile.timeline? || @profile.timeline.length == 0

    events = _.filterByValues @profile.timeline, 'type', ['job','research','volunteer']
    if events.length > 0
      return 'incomplete' for evt in events when !@helper.isTimelineEventComplete evt
      return 'complete'
    return 'empty'

  getIdealJobStatus: =>
    return 'complete' if @isSkipped 'IdealJob'

    # Not calculating the various "desired" fields at all since they are all optional
    
    return 'incomplete' if !@profile.employerIdeals? || @profile.employerIdeals.length < 3
    
    return 'incomplete' if !@profile.idealJob?.preferences? || Object.keys(@profile.idealJob.preferences).length == 0

    return 'complete'


  getEducationHistoryStatus: =>
    return 'complete' if @isSkipped 'EducationHistory'

    return 'empty' if !@profile.timeline? || @profile.timeline.length == 0

    events = _.filterByValues @profile.timeline, 'type', ['university','study_abroad','language_acquisition']
    if events.length > 0
      return 'incomplete' for evt in events when !@helper.isTimelineEventComplete evt
      return 'complete'
    return 'empty'

  getAwardsAndHonorsStatus: =>
    return 'complete' if @isSkipped 'AwardsAndHonors'

    return 'empty' if !@profile.awards? && !@profile.academicOrganizations?

    if @profile.awards?
      return 'incomplete' for award in @profile.awards when (!award.name? || !award.date?)
    
    if @profile.academicOrganizations?
      return 'incomplete' for org in @profile.academicOrganizations when (!org.duration?.start? || !org.name?)

    return 'complete'

  getSoftSkillsStatus: =>
    return 'complete' if @isSkipped 'SoftSkills'

    return 'empty' if !@profile.softSkills?
    return 'incomplete' if @profile.softSkills? && @profile.softSkills.length > 0 && @profile.softSkills.length < 5
    # return 'incomplete' if !@profile.softSkillExamples? || @profile.softSkillExamples.length == 0
    return 'complete'

  getHardSkillsStatus: =>
    return 'complete' if @isSkipped 'HardSkills'
    return 'empty' if !@profile.hardSkills? || Object.keys(@profile.hardSkills).length == 0
    return 'incomplete' if @profile.hardSkills? && Object.keys(@profile.hardSkills).length > 0 && Object.keys(@profile.hardSkills).length < 5
    return 'complete'

  getDomainSkillsStatus: =>
    return  'complete' if @isSkipped 'DomainSkills'
    
    if @profile.domainSkills?
      return 'complete' if @helper.isDomainSkillsComplete @profile.domainSkills
    return 'empty'

  getInterestsStatus: =>
    return  'complete' if @isSkipped 'Interests'
    return 'empty' if (!@profile.organizations? || @profile.organizations.length == 0) && (!@profile.hobbies || @profile.hobbies.length == 0)
    if @profile.organizations
      return 'incomplete' for org in @profile.organizations when (!org.institution?.name? || !org.level?)
    return 'complete'

  getOrganizationsStatus: =>
    return  'complete' if @isSkipped 'Organizations'
    return 'empty' if !@profile.organizations? || @profile.organizations.length == 0
    if @profile.organizations
      return 'incomplete' for org in @profile.organizations when (!org.institution?.name? || !org.level?)
    return 'complete'

  getHobbiesStatus: =>
    return  'complete' if @isSkipped 'Hobbies'
    return 'empty' if !@profile.hobbies? || @profile.hobbies.length == 0
    return 'complete'
        
  getCharacterStatus: =>
    return  'complete' if @isSkipped 'Character'
    return 'empty' if !@profile.characterTraits? || @profile.characterTraits == 0
    return 'incomplete' if @profile.characterTraits.length < 5
    return 'complete' if @profile.characterTraits.length >= 5

  isComplete: =>
    return false if @getGlobalCompetenciesPercentCompleted() < 100
    return false if @getWorkAndEducationPercentCompleted() < 100
    return false if @getSkillsPercentCompleted() < 100
    return false if @getPersonaPercentCompleted() < 100
    
    return true

angular.module 'gps.dashboard'
.service 'menuCompletenessHelper', ['appCandidateProfile', 'profileCompletenessHelper', MenuCompletenessHelper]

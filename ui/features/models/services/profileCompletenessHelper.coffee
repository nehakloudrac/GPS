###
# Various conditions for checking completeness of specific elements in a candidate profile
###
class ProfileCompletenessHelper
  constructor: (config) ->
    @langCerts = config.get 'languageCertifications'
    @countrySliders = config.get 'countrySliders'
    @hardSkills = config.get 'hardSkills'

  isCountryComplete: (country) ->
    country.businessFamiliarity? &&
    country.cultureFamiliarity?

  # TODO: remove this - sliders don't count for completion
  isCountryMissingSliders: (profile, country) ->
    return false

    return true for slider in @countrySliders when (->
      return true for purpose in slider.purposes when _.includes country.purposes, purpose
    )()

    return false
  
  isLanguageEmpty: (lang) ->
    !lang.code? &&
    !lang.currentUsageSocial? &&
    !lang.currentUsageWork? &&
    !lang.selfCertification?.peakProficiency? &&
    !(->
      for field in ['reading','writing','listening','interacting','social']
        return false if !lang.selfCertification[field]? || !lang.selfCertification[field+'Peak']?
      return true
    )()
  
  isLanguageComplete: (lang) ->
    lang.code? &&
    lang.currentUsageSocial? &&
    lang.currentUsageWork? &&
    lang.selfCertification?.peakProficiency? &&
    (->
      for field in ['reading','writing','listening','interacting','social']
        return true if lang.selfCertification[field]?
      return false
    )()

  isLanguageCertComplete: (cert) =>
    cert.scale? &&
    cert.test? &&
    cert.date? &&
    (=>
      return false for field in _.pluck @langCerts[cert.scale].fields, 'key' when !cert[field]?
      return true
    )()
  
  # right now a lang cert cannot be created without certain fields, so
  # requiring a hash should be enough to determine whether or not it's empty
  isLanguageCertEmpty: (cert) -> return !cert.hash?

  isTimelineEventComplete: (evt) ->
    return false if !(evt.duration?.start?)

    return switch
      when evt.type == 'job' then (evt.description? && evt.positionLevel? && evt.status? && evt.institution?.name? && evt.institution?.industries? && evt.institution?.type?)
      when evt.type == 'research' then (evt.description? && evt.sponsoringProgram? && evt.level? && evt.hoursPerWeek? && evt.subject? && evt.institution?.name?)
      when evt.type == 'volunteer' then (evt.description? && evt.status? && evt.institution?.name? && evt.institution?.industries? && evt.institution?.type?)
      when evt.type == 'university' then (evt.concentrations?[0]?.fieldName && evt.institution?.name?)
      when evt.type == 'study_abroad' then (evt.programName? && evt.institution?.name? && evt.countryRefs?[0]?)
      when evt.type == 'language_acquisition' then (evt.languageRefs?[0]? && evt.source? && evt.hoursPerWeek?)
      when evt.type == 'military' then (evt.description? && evt.branch? && evt.rankType? && evt.rankLevel? && evt.rankValue?)
      when evt.type == 'training' then throw new Error 'not yet implemented'


  # recursively checks for emptiness in an object while ignoring the existance
  # of certain object keys - right now this is primarily used for testing
  # existence of empty timeline events to trigger automatic deletion.  That is
  # an issue that needs to be handled separately anyway, but couldn't at the time
  # due to other reasons
  isTimelineEventEmpty: (event) ->
    
    ignore = ['$$hashKey', 'hash', 'type','formattedAddress', 'serializeCompleteness']
    
    isObjectEmpty = (obj) ->
      if _.isPlainObject obj
        for key in Object.keys obj
          #ignore some fields that don't really affect "completeness"
          continue if _.includes ignore, key
          
          #recursively check nested object
          if _.isPlainObject obj[key]
            return false if !isObjectEmpty obj[key]
            continue
          
          #recursively check nested array of objects
          if _.isArray obj[key]
            for nested,i in obj[key]
              if _.isPlainObject obj[key][i]
                return false if !isObjectEmpty obj[key][i]
                continue
              
              #if the array item wasn't an object, assume not empty
              return false
            continue
          
          #if it wasn't a nested object or array, check that
          #scalar value isn't null
          return false if obj[key]?
      
      #didn't get an object, or finished checking without returning early,
      #so assumed to be empty
      return true
    
    return isObjectEmpty event
  
  isCertificationComplete: (cert) ->
    return true if (cert.name? && cert.duration?.start? && cert.organization?)
    return false
  
  isAwardComplete: (award) ->
    return true if (award.name && award.date)
    return false
  
  isAcademicOrgComplete: (org) ->
    return true if (org.name && org.duration?.start)
    return false
  
  isMembershipOrgComplete: (org) ->
    return true if (org.institution?.name && org.level)
    return false
  
    
  isReferenceComplete: (ref) ->
    ref.firstName? && ref.lastName? && ref.email? && ref.relationship? && ref.phoneNumber?

  isHardSkillsComplete: (skills) ->
    return false if !skills[field]? for field in _.pluck @hardSkills, 'key'
    return true

  #deprecated
  isSkillSetComplete: (set) ->
    (set.expert? && set.expert.length > 0) ||
    (set.advanced? && set.advanced.length > 0) ||
    (set.intermediate? && set.intermediate.length > 0)
  
  isDomainSkillsComplete: (skills) ->
    (skills.expert? && skills.expert.length > 0) ||
    (skills.advanced? && skills.advanced.length > 0) ||
    (skills.proficient? && skills.proficient.length > 0)

  isIdealJobComplete: (idealJob = null) ->
    idealJob? &&
    idealJob.jobTypes? &&
    (idealJob.desiredDate?.start? || idealJob.availableImmediately) &&
    (idealJob.minSalary? || idealJob.minHourlyRate? || idealJob.minMonthlyRate? || idealJob.minWeeklyRate? || idealJob.minDailyRate?)
  
  isExperiencesAbroadSectionComplete: (profile) ->

  isLanguagesSectionComplete: (profile) -> return false

  isProfessionalExperienceSectionComplete: (profile) -> return false

  isAcademicExperienceSectionComplete: (profile) -> return false

  isSkillsSectionComplete: (profile) -> return false

  isCharacterSectionComplete: (profile) -> return false
  
  isProfileComplete: (profile) ->
    #TODO - compare against profile.status settings
    return false

angular.module 'gps.common.models'
.service 'profileCompletenessHelper', ['config', ProfileCompletenessHelper]

angular.module 'gps.common.candidate-profile', ['gps.common.models']
.directive 'candidateProfile', (
  labeler,
  profileImageHelper,
  profileCompletenessHelper,
  layout,
  locationFormatter,
  config
) ->
  
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/templates/candidate-profile.html'
    scope:
      profile: '='
      user: '='
      hideIncomplete: '=?'
      showContact: '=?'
    link: (scope, elem, attrs) -> as scope, ->
      
      @showContact = if attrs.showContact? then @showContact else true
      @hideIncomplete = if attrs.hideIncomplete? then @hideIncomplete else false
      
      #label configs... goodness
      @labels = config.getMap([
        'academicSubjects'
        'characterTraits'
        'countryCodes'
        'countryPurposeSliders'
        'countryPurposes'
        'countrySliders'
        'hardSkills'
        'ideals'
        'institutionIndustries'
        'institutionTypes'
        'jobPreferences'
        'jobTypes'
        'languageCertifications'
        'languageCodes'
        'languageSources'
        'languageTests'
        'organizationMembershipLevels'
        'paymentStatuses'
        'positionLevels'
        'softSkills'
        'studentLevels'
        'timelineTypes'
        'universityDegrees'
        'usTerritories'
        'militaryGeographicSpecialties'
        'militaryRankTypes'
        'militaryServices'
        'locationsAbroadChoices'
        'locationsUSAChoices'
        'usSecurityClearances'
        'willingnessToTravel'
      ])

      @labels.languageCurrentUsage = ['Never', 'Once a year or less', 'A few times a year', 'At least once a month', 'At least once a week', 'Every day', 'My primary language of use']

      # everything else
      @labeler = labeler
      @completenessHelper = profileCompletenessHelper
      @profileImageUrl = profileImageHelper.getProfileImageUrl @user, 150
      
      @getSectionTemplates = ->
        ['professional','education','languages','countries','skills','awards-honors','character','interests','leadership','ideal-job'].map (item) ->
          "/features/candidate-profile/templates/#{item}.html"

      sortEvents = (events) ->
        items = {complete: [], incomplete: []}
        for evt in events
          if profileCompletenessHelper.isTimelineEventComplete evt
            items.complete.push evt
          else
            items.incomplete.push evt
            
        items.complete.sort (e1, e2) ->
          now = Date.now()
          ds1 = new Date(e1.duration.start * 1000)
          ds2 = new Date(e2.duration.start * 1000)
          de1 = if e1.duration.end? then new Date(e1.duration.end * 1000) else now
          de2 = if e2.duration.end? then new Date(e2.duration.end * 1000) else now
          
          # sort by start date if end dates are both "now" (meaning event is "current")
          if de1 == de2
            return 1 if ds1 < ds2
            return -1 if ds1 > ds2
            return 0
          
          #otherwise, sort by end date
          return 1 if de1 < de2
          return -1 if de1 > de2
          return 0
          
        return items
        
      @professionalEvents = sortEvents _.filterByValues(@profile.timeline, 'type', ['job','volunteer','military','research'])
      @academicEvents = sortEvents _.filterByValues(@profile.timeline, 'type', ['university','study_abroad','language_acquisition'])
      
      @leadershipItems = (=>
        items = []

        for evt in @professionalEvents

          #check job positions
          if evt.type == 'job'
            if evt.positionLevel? && evt.institution?.name? && evt.positionLevel >= 3
              items.push {
                location: evt.institution.name
                position: labeler.getLabel @positionLevel, evt.positionLevel
              }

          #check military ranks
          if evt.type == 'military'
            if evt.service? && evt.rankType? && rankLevel?
              if (evt.rankType == 'enlisted' && evt.rank >= 7) || (evt.rankType == 'officer' && evt.rankLevel >= 3)
                items.push {
                  location: labeler.getLabel @militaryServices, evt.service
                  position: "Rank " + if evt.rankType=='enlisted' then 'E' else 'O' + " 10"
                }

        #check membership orgs
        for org in @profile.organizations
          if org.membershipLevel? && org.institution?.name? && org.membershipLevel >= 3
            items.push {
              location: org.institution.name
              position: labeler.getLabel @organizationMembershipLevels, org.membershipLevel
            }

        return items
      )()

      @isEmpty = (val) =>
        return true if val == null || val == undefined || val == ""
        return true if angular.equals {}, val
        return true if angular.equals [], val
        return true if angular.isArray val && val.length == 0
        return false

      @getLabel = (items, key, keyField='key', labelField='label') =>
        return @labeler.getLabel items, key, keyField, labelField

      @getLabels = (items, keys, keyField='key', labelField='label') =>
        return @labeler.getLabels items, keys, keyField, labelField

      @toggleHideIncomplete = =>
        @hideIncomplete = !@hideIncomplete
      @toggleContactInfo = =>
        @showContact = !@showContact

      @getTopHardSkills = (max = 4)=>
        skills = ({key: key, val: val} for key, val of @profile.hardSkills)
        end = if skills.length >= max then max else skills.length
        return _.pluck(_.sortBy(skills, 'val').reverse(), 'key')[0..end-1]

      @getCharacterTraits = (min, max) =>
        return @profile.characterTraits[min..max]

      @getStrongJobPreferences = =>
        prefs = if @profile.idealJob?.preferences? then @profile.idealJob.preferences else []
        labels = []

        for pref in @labels.jobPreferences
          val = if prefs[pref.key]? then prefs[pref.key] else false
          continue if !val
          labels.push pref.lowLabel if val <= 0.25
          labels.push pref.highLabel if val >= 0.75

        return labels

      @getCountryNotableActivitiesLocalLang = (country) =>
        activities = []

        return activities if !country.activities?

        for item in @labels.countryPurposeSliders
          if country.activities["#{item.key}LocalLang"] >= 5
            activities.push item.label

        return activities
      
      @getCountryNotableActivities = (country) =>
        activities = []
        
        return activities if !country.activities?
        
        #only count the activity if it's not already going to be counted
        #in the local language section
        for item in @labels.countryPurposeSliders
          if country.activities[item.key] >= 5 && country.activities["#{item.key}LocalLang"] < 5
            activities.push item.label
        
        return activities

      @getAdvancedOrHigherDomainSkills = =>
        skills = []
        #order skills in array by proficiency
        if @profile.domainSkills?
          skills.push skill for skill in @profile.domainSkills.expert if @profile.domainSkills.expert?
          skills.push skill for skill in @profile.domainSkills.advanced if @profile.domainSkills.advanced?
          skills.push skill for skill in @profile.domainSkills.proficient if @profile.domainSkills.proficient?
        
        #returns first 15... so "proficients" only show up if not enough higher ranked items
        return skills.splice(0,16)

      @hasLangCerts = =>
        return true for lang in @profile.languages when lang.officialCertifications.length > 0
        return false

      @getForeignLanguages = =>
        (lang for lang in @profile.languages when !lang.nativeLikeFluency)
      
      @listNativeLanguages = =>
        return 'N/A' if !@user.languages? || @user.languages.length == 0
        return @labeler.getLabels(@labels.languageCodes, @user.languages, 'code')
      
      @listVisaCountries = =>
        return 'N/A' if !@user.citizenship? || @user.citizenship.length == 0
        return @labeler.getLabels(@labels.countryCodes, @user.citizenship, 'code', 'name')
        
      @listForeignLanguages = =>
        langs = @getForeignLanguages()
        codes = _.pluck langs, 'code'
        return 'N/A' if codes.length == 0
        return @labeler.getLabels @labels.languageCodes, codes, 'code'
      
      
      @calculateLangPercentComplete = (val, scale) =>
        total = @labels.languageCertifications[scale].valueLabels.length - 1
        return Math.round((val / total) * 100)
      
      @showCountriesSection = =>
        return true if !@hideIncomplete
        return false if !@profile.countries?
        return false if @profile.countries.length == 0
        for country in @profile.countries
          return true if profileCompletenessHelper.isCountryComplete country
        return false
      
      @showLanguagesSection = =>
        return true if !@hideIncomplete
        return false if !@profile.languages?
        return false if @profile.languages.length == 0
        for lang in @profile.languages
          return true if profileCompletenessHelper.isLanguageComplete lang
        return false
      
      @showWorkSection = =>
        return true if !@hideIncomplete
        return false if !@profile.timeline?
        return false if @profile.timeline.length == 0
        for evt in @profile.timeline
          if _.includes(['job','volunteer','research','military'], evt.type)
            return true if profileCompletenessHelper.isTimelineEventComplete evt
        return false
      
      @showEducationSection = =>
        return true if !@hideIncomplete
        return false if !@profile.timeline?
        return false if @profile.timeline.length == 0
        for evt in @profile.timeline
          if _.includes(['study_abroad','university','language_acquisition'], evt.type)
            return true if profileCompletenessHelper.isTimelineEventComplete evt
        return false
      
      @showSkillsSection = =>
        return true if !@hideIncomplete
        return true if @showSoftSkillsSection()
        return true if @showHardSkillsSection()
        return true if @showDomainSkillsSection()
        return false
      
      @showSoftSkillsSection = =>
        return true if !@hideIncomplete
        return true if @profile.softSkills? && @profile.softSkills.length > 0
        return false
        
      @showHardSkillsSection = =>
        return true if !@hideIncomplete
        return true if !@isEmpty(@profile.hardSkills)
        return false
        
      @showDomainSkillsSection = =>
        return true if !@hideIncomplete
        skills = @getAdvancedOrHigherDomainSkills()
        return true if skills.length > 0
        return false
      
      @showCertificationsSection = =>
        return true if !@hideIncomplete
        return true if @completeCertifications.length > 0
        return false
      
      @showAwardsHonorsSection = =>
        return true if !@hideIncomplete
        return true if @showAwardsSection() || @showHonorsSection()
        return false
      
      @showAwardsSection = =>
        return true if !@hideIncomplete
        return true if @profile.awards? && @profile.awards.length > 0
        return false
      
      @showHonorsSection = =>
        return true if !@hideIncomplete
        return true if @profile.academicOrganizations? && @profile.academicOrganizations.length > 0
        return false
      
      @showCharacterSection = =>
        return true if !@hideIncomplete
        return true if @profile.characterTraits? && @profile.characterTraits.length > 0
        return false
      
      @showInterestsSection = =>
        return true if !@hideIncomplete
        return true if @showHobbiesSection()
        return true if @showOrganizationsSection()
        return false
      
      @showHobbiesSection = =>
        return true if !@hideIncomplete
        return true if @profile.hobbies && @profile.hobbies.length > 0
        return false

      @showOrganizationsSection = =>
        return true if !@hideIncomplete
        return false if !@profile.organizations || @profile.organizations.length == 0
        for org in @profile.organizations
          return true if (org.institution?.name? && org.level?)
        return false
      
      @showIdealJobSection = =>
        return true if !@hideIncomplete
        return true if @showDesiredSection()
        return true if @showWorkEnvironmentSection()
        return true if @showOrgValuesSection()
      
      @isIdealJobSectionEmpty = =>
        return true if !@profile.idealJob?
        #Note that "willingToTravelOverseas" is not in the list on purpose, whether or not it's complete depends
        #on the field "willingnessToTravel", so we only check for that
        scalars = ['willingnessToTravel','availableImmediately','minSalary','minHourlyRate','minMonthlyRate','minWeeklyRate','minDailyRate']
        arrays = ['jobTypes','locationsUSA','locationsAbroad','industries','employerTypes','availability']

        for scalar in scalars
          return false if @profile.idealJob[scalar]?
        for arr in arrays
          return false if @profile.idealJob[arr]? && @profile.idealJob[arr].length > 0
        
        return true
        
      @showDesiredSection = =>
        return true if !@hideIncomplete
        return false if @isIdealJobSectionEmpty()
        return true

      @showWorkEnvironmentSection = =>
        return true if !@hideIncomplete
        return true if @strongJobPreferences.length > 0
        return false

      @showOrgValuesSection = =>
        return true if !@hideIncomplete
        return true if @profile.employerIdeals? && !@isEmpty(@profile.employerIdeals)
        return false

      # assign some things directly to scope in order to compute only once
      @strongJobPreferences = @getStrongJobPreferences()
      @topHardSkills = @getTopHardSkills()
      @advancedDomainSkills = @getAdvancedOrHigherDomainSkills()
      
      @getFormattedLocation = =>
        return locationFormatter.formatLocation(@user.address)
      
      @getAllIndustries = =>
        inds = if @profile.shortForm?.preferredIndustries? then @profile.shortForm.preferredIndustries else []
        if @profile.timeline?
          for evt in @profile.timeline
            inds.push ind for ind in evt.institution.industries if evt.institution?.industries?
        return if inds.length > 0 then _.uniq(inds).join(', ') else 'N/A'
      
      @getAllDegrees = =>
        degs = if @profile.shortForm?.degrees? then @profile.shortForm.degrees else []
        if @profile.timeline?
          for evt in @profile.timeline when 'university' == evt.type
            degs.push(deg) for deg in evt.degrees if evt.degrees?
        if degs.length > 0
          degs = _.reject(degs, (item) -> 'none' == item)

        return if degs.length > 0 then @getLabels(@labels.universityDegrees, _.uniq(degs)) else 'N/A'
        
      @getAllMajors = =>
        majors = []
        if @profile.timeline?
          for evt in @profile.timeline when 'university' == evt.type
            if evt.concentrations?
              for c in evt.concentrations when 'major' == c.type
                majors.push(c.fieldName) if c.fieldName?
        return if majors.length > 0 then _.uniq(majors).join(', ') else 'N/A'
      
      @getSecurityClearanceLabel = =>
        return if @user.usSecurityClearance then labeler.getLabel(@labels.usSecurityClearances, @user.usSecurityClearance) else 'N/A'
      
      @getCompleteCertifications = =>
        certs = []
        if @profile.certifications?
          for cert in @profile.certifications
            certs.push cert if cert.name? && cert.duration?.start?
        return certs
      
      @getYearsExperience = =>
        timelineYears = 0
        shortFormYears = 0
        
        if @profile.professionalHistoryMonths?
          timelineYears = Math.round(@profile.professionalHistoryMonths / 12)
        
        if @profile.shortForm?.yearsWorkExperience?
          shortFormYears = @profile.shortForm.yearsWorkExperience
        
        finalYears = if timelineYears > shortFormYears then timelineYears else shortFormYears
        if finalYears > 0
          return "#{finalYears} years"
        if @profile.professionalHistoryMonths > 0
          return "#{@profile.professionalHistoryMonths} months"
        
        return "N/A"

      addDuration = (duration) ->
        now = Date.now()
        if duration.start? && duration.end?
          return duration.end - duration.start
        if duration.start? && !duration.end
          return now - duration.start
      
      @allMajors = @getAllMajors()
      @allDegrees = @getAllDegrees()
      @allIndustries = @getAllIndustries()
      @completeCertifications = @getCompleteCertifications()
      @headerIndustries = (=>
        if @profile.timeline? && @profile.timeline.length > 0
          inds = []
          for evt in @profile.timeline
            if evt.institution?.industries? && evt.institution.industries.length > 0
              for ind in evt.institution.industries
                inds.push {
                  name: ind
                  time: if (evt.duration?.start?) then addDuration(evt.duration) else 0
                }
          result = _.sortBy(inds, 'time').reverse()
          
          # map to deduplicate and add total times
          map = {}
          for res in result
            if !map[res.name]?
              map[res.name] = 0
            map[res.name] += res.time
          
          # put back into array to sort/pluck/slice
          parsedInds = []
          parsedInds.push {name: name, time: time} for name,time of map
          sorted = _.sortBy(parsedInds, 'time').reverse()
          res = _.pluck(sorted, 'name')
          return _.slice(res, 0, 7).join(', ')
        
        return if (@profile.shortForm?.preferredIndustries?) then _.slice(@profile.shortForm.preferredIndustries, 0, 7).join(', ') else 'N/A'
      )()
      
      @completeAcademicOrgs = (=>
        orgs = []
        if @profile.academicOrganizations?
          for org in @profile.academicOrganizations
            if org.name? && org.duration.start?
              orgs.push org
          
        return orgs
      )()
      
      @completeAwards = (=>
        awards = []
        if @profile.awards?
          for award in @profile.awards
            if award.name? && award.date?
              awards.push award
        return awards
      )()
  }
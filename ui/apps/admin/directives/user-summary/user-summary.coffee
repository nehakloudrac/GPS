angular.module 'gps.admin'
.directive 'userSummaryItems', (recursionHelper) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/admin/directives/user-summary/user-summary-items.html'
    scope:
      items: '='
    compile: (element) ->
      return recursionHelper.compile element
  }
  
.directive 'userSummary', (
  labeler
  config
) ->
  return {
    restrict: 'E'
    scope:
      user: '='
      profile: '='
    templateUrl: '/apps/admin/directives/user-summary/user-summary.html'
    link: (scope, elem, attrs) -> as scope, ->
      @foo = 'bar'
      
      getTimelineWorkExperience = =>
        total = 0
        now = Date.now() / 1000
        if @profile.timeline?
          for evt in @profile.timeline when _.contains ['job','volunteer','military','research'], evt.type
            
            if evt.duration?.start? && evt.duration?.end?
              total += evt.duration.end - evt.duration.start
            if evt.duration?.start? && !evt.duration?.end?
              total += now - evt.duration.start
        
        return false if total == 0
        
        day = 60*60*24
        days = total / day
        
        years = Math.floor days/365
        remainder = days % 365
        months = Math.floor remainder / 30
        
        return "~ #{years} years, #{months} months"
      
      getAllIndustries = =>
        arrays = []
        if @profile.shortForm.preferredIndustries?
          arrays.push @profile.shortForm.preferredIndustries
        if @profile.idealJob.industries?
          arrays.push @profile.idealJob.industries
        
        (evt.institution.industries for evt in @profile.timeline when evt.institution?.industries?).forEach (arr) -> arrays.push arr
        
        result = _.union arrays...
        return if result.length > 0 then result.join ', ' else false
      
      getForeignLanguages = =>
        codes = (lang.code for lang in @profile.languages when !lang.nativeLikeFluency)
        return if codes.length > 0 then labeler.getLabels config.get('languageCodes'), codes, 'code' else false
      
      getDesiredCompensation = =>
        data = []
        data.push {label: "Salary", value: if @profile.idealJob.minSalary? then "$" + @profile.idealJob.minSalary else false}
        data.push {label: "Hourly", value: if @profile.idealJob.minHourlyRate? then "$" + @profile.idealJob.minHourlyRate else false}
        data.push {label: "Dialy", value: if @profile.idealJob.minDailyRate? then "$" + @profile.idealJob.minDailyRate else false}
        data.push {label: "Weekly", value: if @profile.idealJob.minWeeklyRate? then "$" + @profile.idealJob.minWeeklyRate else false}
        data.push {label: "Monthly", value: if @profile.idealJob.minMonthlyRate? then "$" + @profile.idealJob.minMonthlyRate else false}
        return data
      
      getBioData = =>
        data = []
        data.push { label: 'Current Location', value: formatAddress @user.address }
        data.push { label: 'Work Experience (Short Form)', value: if @profile.shortForm?.yearsWorkExperience? then @profile.shortForm.yearsWorkExperience + " years" else false }
        data.push { label: 'Work Experience (Timeline)', value: getTimelineWorkExperience() }
        data.push { label: 'US Security Clearance', value: if @user.usSecurityClearance? then labeler.getLabel config.get('usSecurityClearances'), @user.usSecurityClearance else false }
        data.push { label: 'US Work Authorization', value: if @user.usWorkAuthorization? then labeler.getLabel config.get('usWorkAuthorizations'), @user.usWorkAuthorization else false }
        data.push { label: 'Industries (All)', value: getAllIndustries() }
        data.push { label: 'Native Languages', value: if @user.languages? then labeler.getLabels config.get('languageCodes'), @user.languages, 'code' else false }
        data.push { label: 'Foreign Languages', value: getForeignLanguages() }
        data.push { label: 'Desired Job Types', value: if @profile.idealJob?.jobTypes? then labeler.getLabels config.get('jobTypes'), @profile.idealJob.jobTypes else false }
        data.push { label: 'Desired Employer Types', value: if @profile.idealJob?.employerTypes? then labeler.getLabels config.get('institutionTypes'), @profile.idealJob.employerTypes else false }
        data.push { label: 'Desired Start Date', value: if @profile.idealJob?.desiredDate?.start? then formatDate @profile.idealJob.desiredDate.start, true else false }
        data.push { label: 'Desired Location (USA)', value: if @profile.idealJob?.locationsUSA? then labeler.getLabels config.get('locationsUSAChoices'), @profile.idealJob.locationsUSA else false }
        data.push { label: 'Desired Location (Abroad)', value: if @profile.idealJob?.locationsAbroad? then labeler.getLabels config.get('locationsAbroadChoices'), @profile.idealJob.locationsAbroad, 'code', 'name' else false }
        data.push { label: 'Desired Compensation', sub: getDesiredCompensation() }
        return data
      
      getCountryData = =>
        data = []
        if @profile.countries?
          for country in @profile.countries
            cdata = []
            cdata.push { label: 'Cultural Familiarity', value: if country.culturalFamiliarity? then country.culturalFamiliarity else false }
            cdata.push { label: 'Business Familiarity', value: if country.businessFamiliarity? then country.businessFamiliarity else false }
            cdata.push { label: 'Total Time', value: if country.approximateNumberMonths? then country.approximateNumberMonths + ' months' else false }
            cdata.push { label: 'Reasons', value: if country.purposes? then labeler.getLabels config.get('countryPurposes'), country.purposes else false }
            
            data.push { label: labeler.getLabel(config.get('countryCodes'), country.code, 'code','name'), sub: cdata }
        return data
        
      getLanguageData = =>
        data = []
        if @profile.languages?
          for lang in @profile.languages when !lang.nativeLikeFluency
            ldata = []
            ldata.push { label: 'Usage (Social)', value: if lang.currentUsageSocial? then lang.currentUsageSocial else false }
            ldata.push { label: 'Usage (Professional)', value: if lang.currentUsageWork? then lang.currentUsageWork else false }
            ldata.push {
              label: 'Current Proficiency', sub: ({ label: field, value: lang.selfCertification[field] } for field in ['listening','reading','writing','interacting','social'])
            }
            
            data.push { label: labeler.getLabel(config.get('languageCodes'), lang.code, 'code'), sub: ldata }
        return data
      
      getWorkData = =>
        data = []
        
        currentEvents = (evt for evt in @profile.timeline when (_.includes(['job','research','volunteer','military'], evt.type) && (evt.duration?.start? && !evt.duration.end?)))
        
        for evt in currentEvents
          edata = []
          
          edata.push { label: 'Type', value: evt.type }
          edata.push { label: 'Institution', value: if evt.institution?.name? then evt.institution.name else false }
          edata.push { label: 'Location', value: if evt.institution?.address? then formatAddress evt.institution.address else false }
          
          if evt.type == 'job'
            edata.push { label: 'Title', value: if evt.title? then evt.title else false }
          
          if evt.type == 'research'
            edata.push { label: 'Program', value: if evt.sponsoringProgram? then evt.sponsoringProgram else false }

          # if evt.type == 'volunteer'
          
          data.push edata
        
        return data
      
      getUniversityData = =>
        data = []
        
        currentEvents = (evt for evt in @profile.timeline when 'university' == evt.type)
        for evt in currentEvents
          udata = []
          udata.push { label: 'School', value: if evt.institution?.name? then evt.institution.name else false }
          udata.push { label: 'End Date', value: if evt.duration?.end? then formatDate evt.duration.end else false }
          udata.push { label: 'Degrees', value: if evt.degrees? then labeler.getLabels config.get('universityDegrees'), evt.degrees else false }
          majors = []
          minors = []
          if evt.concentrations?
            for c in evt.concentrations
              majors.push c.fieldName if c.type == 'major' && c.fieldName?
              minors.push c.fieldName if c.type == 'minor' && c.fieldName?
          udata.push { label: 'Majors', value: if majors.length > 0 then majors.join(', ') else false }
          udata.push { label: 'Minors', value: if minors.length > 0 then minors.join(', ') else false }
          data.push udata
        
        return data
      
      formatDate = (timestamp, days = false) ->
        d = new Date timestamp * 1000
        str = "#{d.getMonth() + 1}/#{d.getFullYear()}"
        if days
          str = "#{d.getDate()}/"+str
        
        return str
      
      formatAddress = (addr) ->
        return false if !addr.city? && !addr.countryCode?

        if addr.countryCode? && addr.countryCode == 'US'
          return "#{addr.city}, #{addr.territory}, United States"
        
        return "#{addr.city}, #{labeler.getLabel(config.get('countryCodes'), addr.countryCode, 'code', 'name')}"
      
      @bioData = getBioData()
      @countryData = getCountryData()
      @languageData = getLanguageData()
      @workData = getWorkData()
      @universityData = getUniversityData()
  }
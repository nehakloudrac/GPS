angular.module 'gps.admin'
.factory 'searchFacetDefinitions', (searchFilterDefinitions) ->
  defs = {}
  for key,item of searchFilterDefinitions
    if item.facet?
      facetKey = if item.facet.field? then item.facet.field else key
      item.facetKey = facetKey
      defs[facetKey] = item
  return defs
  
.factory 'searchFilterDefinitions', (config) ->
  defs =
    #basic text filters:
    'totalSkills':
      name: "Skills"
      desc: "Any skills listed, regardless of level of expertise."
      type: "facet-select"
      multi: true
      facet:
        field: "totalSkills.facet"
        size: 50
        search: true
    'totalUniversities':
      name: "Universities"
      desc: "Any universities listed in education history."
      type: "facet-select"
      multi: false
      facet:
        field: "totalUniversities.facet"
        size: 50
        search: true
    'totalMajors':
      name: "Majors/Minors"
      desc: "Any major/minor listed in education history."
      type: "facet-select"
      multi: true
      facet:
        field: "totalMajors.facet"
        size: 50
        search: true
    'totalEmployers':
      name: "Employers"
      desc: "Any employer names listed in work history."
      type: "facet-select"
      multi: false
      facet:
        field: "totalEmployers.facet"
        size: 50
        search: true
    'clientId':
      name: "Id"
      desc: "Unique database id."
      type: "facet-select"
      multi: false
    'userEmail':
      name: "Email Address"
      type: "facet-select"
      multi: false
    'userName':
      name: "Name"
      desc: "Includes any name from first, last, and preferred name fields."
      type: "facet-select"
      multi: false
    'hobbies':
      name: "Hobbies"
      desc: "Any hobbies listed."
      type: "facet-select"
      multi: true
      facet:
        field: "hobbies.facet"
        size: 50
        search: true
    'desiredIndustries':
      name: "Industries (Desired)"
      desc: "Industries in which the candidate would like to work."
      type: "facet-select"
      multi: true
      facet:
        field: "desiredIndustries.facet"
        size: 50
        search: true
    'totalAwards':
      name: "Awards"
      desc: "Names of any awards mentioned."
      type: "facet-select"
      multi: true
      facet:
        field: "totalAwards.facet"
        size: 50
        search: true
    'totalIndustries':
      name: "Industries (Experience)"
      desc: "Industries in which the candidate has experience."
      type: "facet-select"
      multi: true
      facet:
        field: "totalIndustries.facet"
        size: 50
        search: true
    'totalTitles':
      name: "Job Titles"
      desc: "Names of job titles listed in work history."
      type: "facet-select"
      multi: true
      facet:
        field: "totalTitles.facet"
        size: 50
        search: true
      
    #basic select filters
    'usSecClearance':
      name: "Security Clearance"
      type: "facet-select"
      multi: false
      conf:
        selections: config.get('usSecurityClearances')
      facet: true
    'currentJobStatus':
      name: "Job Status"
      type: "facet-select"
      multi: false
      conf:
        selections: config.get('userJobStatusOptions')
      facet: true
    'usWorkAuthorization':
      name: "US Work Authorization"
      type: "facet-select"
      multi: false
      conf:
        selections: config.get('usWorkAuthorizations')
      facet: true
    'visas':
      name: "Countries (Passports/Visas)"
      type: "facet-select"
      desc: "Countries where the candidate can legally work due to having either a passport or visa."
      multi: true
      conf:
        selections: config.get('countryCodes')
        keyField: 'code'
        labelField: 'name'
      facet:
        search: true
        selectionSearch: true
        size: 0
    'desiredEmployerTypes':
      name: "Desired Employer Type"
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('institutionTypes')
      facet: true
    'desiredJobTypes':
      name: "Desired Job Type"
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('jobTypes')
      facet: true
    'desiredLocationsUSA':
      name: "State (Desired)"
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('locationsUSAChoices')
      facet:
        search: true
        selectionSearch: true
        size: 50
    'desiredLocationsAbroad':
      name: "Countries (Desired)"
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('locationsAbroadChoices')
        keyField: 'code'
        labelField: 'name'
      facet:
        search: true
        selectionSearch: true
        size: 50
    'totalDegrees':
      name: "Degrees"
      desc: "Any degrees listed in either the users short form or education history."
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('universityDegrees')
      facet: true
    'totalCountries':
      name: "Countries (All)"
      desc: "Countries in which candidate has any experience, foreign or passport or visa."
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('countryCodes')
        keyField: 'code'
        labelField: 'name'
      facet:
        search: true
        selectionSearch: true
        size: 0
    'totalLanguages':
      name: "Languages (All)"
      desc: "Any languages referenced by the candidate, foreign or native."
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('languageCodes')
        keyField: 'code'
      facet:
        search: true
        selectionSearch: true
        size: 0
    'totalForeignLanguages':
      name: "Languages (Foreign)"
      desc: "Any non-native language referenced by the candidate."
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('languageCodes')
        keyField: 'code'
      facet:
        search: true
        selectionSearch: true
        size: 0
    'totalNativeLanguages':
      name: "Languages (Native)"
      desc: "Any native language mentioned."
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('languageCodes')
        keyField: 'code'
      facet:
        search: true
        selectionSearch: true
        size: 0
    'totalCertifiedLanguages':
      name: "Languages (Certified)"
      desc: "Any language the users has listed an official certification for."
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('languageCodes')
        keyField: 'code'
      facet:
        search: true
        selectionSearch: true
        size: 0
    'currentLocationCountry':
      name: "Countries (Current)"
      desc: "Which country the candidate is currently in."
      type: "facet-select"
      multi: false
      conf:
        selections: config.get('countryCodes')
        keyField: 'code'
        labelField: 'name'
      facet:
        search: true
        selectionSearch: true
        size: 0
    'currentLocationCity':
      name: "City (Current)"
      desc: "Which city the candidate is currently in."
      type: "facet-select"
      multi: false
      facet:
        field: 'currentLocationCity.facet'
        search: true
        size: 50
    'currentLocationTerritory':
      name: "State (Current)"
      desc: "Which US state the candidete is currently in, if in the US."
      type: "facet-select"
      multi: false
      conf:
        selections: config.get('usTerritories')
      facet:
        search: true
        selectionSearch: true
        size: 50
    'institutionReferrer':
      name: "Referrer"
      desc: "Candidates who registered via a referrer link."
      help: "You must enter the key exactly as it appears in the referrer link, for example 'aatg' or 'npca'."
      type: "facet-select"
      multi: false
      facet: true
    'diversity':
      name: "Diversity"
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('diversityFlags')
      facet: true
    'gender':
      name: "Gender"
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('genderOptions')
      facet: true
    'totalPositionLevels':
      name: "Position Level (All)"
      desc: "Any position levels listed in work history or short form."
      type: "facet-select"
      multi: true
      conf:
        selections: config.get('positionLevelChoices')
      facet: true
    'highestPositionLevel':
      name: "Position Level (Highest)"
      desc: "Highest level attained as mentioned in history or short form."
      type: "facet-select"
      multi: false
      conf:
        selections: config.get('positionLevelChoices')
      facet: true
    'howWillingToTravel':
      name: "Willingness to Travel"
      desc: "How willing the candidate is to travel for work."
      type: "facet-select"
      multi: false
      conf:
        selections: config.get('willingnessToTravel')
      facet: true

    #number filters
    'desiredPaySalary':
      name: "Desired Compensation (Salary)"
      type: "basic-number"
      multi: true
      conf:
        widget: 'text'
    'desiredPayHourly':
      name: "Desired Compensation (Hourly)"
      type: "basic-number"
      multi: true
      conf:
        widget: "text"
    # 'desiredPayDaily':
    #   name: "Desired Compensation (Daily)"
    #   type: "basic-number"
    #   multi: true
    #   conf:
    #     widget: "text"
    # 'desiredPayWeekly':
    #   name: "Desired Compensation (Weekly)"
    #   type: "basic-number"
    #   multi: true
    #   conf:
    #     widget: "text"
    'desiredPayMonthly':
      name: "Desired Compensation (Monthly)"
      type: "basic-number"
      multi: true
      conf:
        widget: "text"
    'desiredHoursPerWeek':
      name: "Desired Hours/Week"
      type: "basic-number"
      multi: true
      conf:
        widget: 'text'
    'totalMonthsExperience':
      name: "Work Experience (full range)"
      desc: "Total monthes of work experience between the earlist job listed, and the most recent job ending."
      type: "basic-number"
      multi: true
      conf:
        widget: 'text'
    'totalMonthsExperienceGaps':
      name: "Work Experience (w/ gaps)"
      desc: "Total months of work experience including gaps in employment."
      type: "basic-number"
      multi: true
      conf:
        widget: 'text'
    # 'dateCreated':
    #   name: "Date Created"
    #   desc: "When the user joined."
    #   type: "basic-number"
    #   multi: true
    #   conf:
    #     widget: 'date'

  
  sorted = []
  for key,def of defs
    def.key = key
    sorted.push def
  sorted = _.sortBy sorted, 'name'
  final = {}
  for item in sorted
    final[item.key] = item

  return final

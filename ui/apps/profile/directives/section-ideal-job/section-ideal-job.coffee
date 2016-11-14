angular.module 'gps.profile'
.directive 'sectionIdealJob', (
  $http
  appCandidateProfile
  config
  labeler
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/section-ideal-job/section-ideal-job.html'
    link: (scope, elem, attrs) -> as scope, ->

      #form config
      @institutionIndustries = config.get 'institutionIndustries'
      @institutionTypes = config.get 'institutionTypes'
      @locationsUSAChoices = config.get 'locationsUSAChoices'
      @locationsAbroadChoices = config.get 'locationsAbroadChoices'
      @jobTypes = config.get 'jobTypes'
      @paymentStatuses = config.get 'paymentStatuses'
      @jobPreferences = config.get 'jobPreferences'
      @willingnessToTravel = config.get 'willingnessToTravel'

      @labeler = labeler

      #form state for editable fields
      @fields = {}                #top-level idealJobs fields
      @availabilities = []        #windows of availability for project-based work (array of objects)
      @preferences = {}           #ideal job preference sliders
      @idealJob = appCandidateProfile.getData().idealJob

      @addAvailability = =>
        @availabilities.push { hash: null, editing: true, fields: {travelInternational: false, travelDomestic: false}, promise: null}

      @clearEmptyAvailabilities = =>
        @availabilities = _.reject @availabilities, { hash: null }

      @saveAvailability = (index) =>
        id = appCandidateProfile.getData().id
        avail = @availabilities[index]
        data = avail.fields
        if avail.hash
          method = "put"
          url = "/api/candidate-profiles/#{id}/project-availability/#{avail.hash}"
        else
          method = "post"
          url = "/api/candidate-profiles/#{id}/project-availability"

        avail.promise = $http[method](url, data)
        .success (res) =>
          appCandidateProfile.applyProjectAvailability res.availability
          rebuildAvailabilities()
        .error (err) =>
          console.error err
          rebuildAvailabilities()

      @cancelAvailability = (index) =>
        rebuildAvailabilities()

      @deleteAvailability = (index) =>
        id = appCandidateProfile.getData().id
        avail = @availabilities[index]
        
        if !avail.hash?
          @availabilities.splice index, 1
          return
        
        avail.promise = $http.delete "/api/candidate-profiles/#{id}/project-availability/#{avail.hash}"
        .success (res) =>
          appCandidateProfile.removeProjectAvailability avail.hash
          rebuildAvailabilities()
        .error (err) =>
          console.error err
          rebuildAvailabilities()

      @hasTypes = (types) =>
        for type in types
          return true if _.includes @fields.jobTypes.value, type
        return false

      @removeType = (type) =>
        console.log 'removed: ', type
        extras = null
        # switch type.key
        #   when '' then extras = null

        @saveField 'jobTypes', extras

      rebuildAvailabilities = =>
        @availabilities = []
        profile = appCandidateProfile.getData()
        profile.idealJob ?= {}
        profile.idealJob.availability ?= []

        for obj in profile.idealJob.availability
          @availabilities.push {
            hash: obj.hash
            editing: false
            promise: null
            fields: _.cloneDeep obj
          }
        
      rebuildFields = =>
        @fields = {}
        profile = appCandidateProfile.getData()
        profile.idealJob ?= {}

        for field in [
          'availableImmediately'
          'willingToTravelOverseas'
          'willingnessToTravel'
          'jobTypes'
          'employerTypes'
          'industries'
          'locationsUSA'
          'locationsAbroad'
          'desiredDate'
          'hoursPerWeek'
          'payStatus'
          'minSalary'
          'minHourlyRate'
          'minDailyRate'
          'minWeeklyRate'
          'minMonthlyRate'
        ]
          @fields[field] = {
            value: if profile.idealJob[field]? then _.cloneDeep profile.idealJob[field] else null
            promise: null
            editing: false
          }

      @saveField = (field, extras = null) =>
        id = appCandidateProfile.getData().id
        data = {idealJob: {}}
        data.idealJob[field] = @fields[field].value

        if extras
          for key, val of extras
            data.idealJob[key] = val

        @fields[field].promise = $http.put "/api/candidate-profiles/#{id}", data
        .success (res) =>
          appCandidateProfile.setData res.profile
          resetField field
        .error (err) =>
          rebuildFields()
          console.error err

      resetField = (key) =>
        profile = appCandidateProfile.getData()
        @idealJob[key] = if profile.idealJob?[key]? then profile.idealJob[key] else null
        @fields[key].value = if profile.idealJob?[key]? then _.cloneDeep profile.idealJob[key] else null

      @savePreference = (key) =>
        profile = appCandidateProfile.getData()
        pref = @preferences[key]
        
        #don't update if the value didn't change
        if profile.idealJob?.preferences?[pref.key]?
          return if profile.idealJob.preferences[pref.key] == pref.value

        data = {
          idealJob: {
            preferences: {}
          }
        }
        data.idealJob.preferences[pref.key] = pref.value

        pref.promise = $http.put "/api/candidate-profiles/#{profile.id}", data
        .success (res) =>
          profile.idealJob.preferences = res.profile.idealJob.preferences
          resetPref pref.key
        .error (err) =>
          console.error err
          rebuildPreferences()

      rebuildPreferences = =>
        @preferences = {}
        profile = appCandidateProfile.getData()
        profile.idealJob ?= {}
        prefs = if profile.idealJob?.preferences? then profile.idealJob.preferences else {}

        for obj in @jobPreferences
          @preferences[obj.key] = {
            promise: null
            key: obj.key
            answered: prefs[obj.key]?
            value: if prefs[obj.key]? then _.clone prefs[obj.key] else 0.5
            lowLabel: obj.lowLabel
            highLabel: obj.highLabel
          }

      resetPref = (key) =>
        profile = appCandidateProfile.getData()

        @preferences[key].answered = profile.idealJob.preferences[key]?
        @preferences[key].value = if profile.idealJob.preferences[key]? then _.clone profile.idealJob.preferences[key] else null

      rebuildFields()
      rebuildAvailabilities()
      rebuildPreferences()
  }
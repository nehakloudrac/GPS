angular.module 'gps.profile'
.directive 'formTimelineEvent', (
  appCandidateProfile
  profileFormInitializer
  $rootScope
  labeler
  config
  $timeout
) ->
  return {
    restrict: 'E'
    scope:
      event: '='
      types: '='
    templateUrl: '/apps/profile/directives/edit-timeline/form-timeline-event.html'
    link: (scope, elem, attrs) -> as scope, ->
      @labeler = labeler
      @timelineTypes = _.filterByValues(config.get('timelineTypes'), 'type', @types)
      
      @form = {}
      isNew = false
         
      onSaveSuccess = (res) =>
        appCandidateProfile.applyTimelineEvent res.event
        if isNew
          @$emit 'gps.new-timeline-event', res.event
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onSaveError = (res) =>
        console.error 'Error saving timeline event field.'
        @init()
      
      @init = (fields = [], config = []) =>
        isNew = true if !@event.hash?

        return if !@event.type?
        
        initFormWithFields fields
        initConfig config
      
      @autocompleteFromList = (list, query) ->
        query = query.trim()
        
        return list if !query.length

        suggestions = []
        list.forEach (item) ->
          suggestions.push item if -1 != item.toLowerCase().indexOf query.toLowerCase()

        return suggestions
      
      #tags-input sets model to array of objects... so convert it before saving
      @saveTaggedField = (name) =>
        @form.fields[name].value = _.pluck @form.fields[name].value, 'text'
        @form.saveField(name)
      
      @isLocationRequired = (addr) ->
        return true if  !addr || addr == null || addr == undefined
        return true if !addr.city || !addr.countryCode || (addr.countryCode == 'US' && !addr.territory)
        return false
      
      #real initialization is triggered by child form, which
      #adds custom fields for type, as well as any other needed logic
      initFormWithFields = (fields) =>
        allFields = [
          'duration'
          'description'
          'activities'
          'languageRefs'
          'countryRefs'
          'institution.name'
          'institution.type'
          'institution.url'
          'institution.industries'
          'institution.address'
        ]
        allFields.push field for field in fields
        
        @form = profileFormInitializer.initializeForm @event, '/timeline', onSaveSuccess, onSaveError, allFields
        @form.saveDataTransformer = (obj) =>
          obj.type = @event.type
          return obj
        @form.saveUrlTransformer = (url, obj, data) ->
          return url+"?type=#{obj.type}" if !obj.hash?
          return url
        
        #force val initialzed to empty array instead of null, otherwise
        #the first save won't trigger properly, also a hack to remove duplicate values, 
        #not sure how they made it in, but will have to deal w/ later
        @form.fields['institution.industries'].value ?= []
        @form.fields['institution.industries'].value = _.unique @form.fields['institution.industries'].value
      
      initConfig = (items = []) =>
        configFields = [
          'institutionTypes'
          'institutionIndustries'
          'countryCodes'
          'languageCodes'
          'usTerritories'
        ]
        configFields.push item for item in items

        @conf = config.getMap configFields
        
      @$watch 'event.hash', => @$broadcast 'event.init'
  }
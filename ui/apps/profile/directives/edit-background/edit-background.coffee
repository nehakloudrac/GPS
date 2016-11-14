angular.module 'gps.profile'
.directive 'editBackground', (
  appCandidateProfile
  appUserService
  labeler
  fieldInitializer
  config
  $rootScope
  $http
  modals
) ->
  return {
    restrict: 'E'
    scope:
      initial: '&'
      showLiImporter: '&'
    templateUrl: '/apps/profile/directives/edit-background/edit-background.html'
    link: (scope, elem, attrs) -> as scope, ->
      @initial = if attrs.initial? then @initial() else false
      @showLiImporter = if attrs.showLiImporter then @showLiImporter() else false
      
      @userForm = {}
      @profileForm = {}
      
      @conf = config.getMap [
        'languageCodes'
        'countryCodes'
        'userJobStatusOptions'
        'institutionIndustries'
        'genderOptions'
        'universityDegrees'
        'positionLevels'
        'usTerritories'
        'usWorkAuthorizations'
        'usSecurityClearances'
        'referralMediums'
        'diversityFlags'
      ]
      
      @labeler = labeler

      @shortForm = {}
      @user = {}

      @appUserId = appUserService.getData().id
      @profileId = appCandidateProfile.getData().id

      @submitting = null

      @fields =
        user: {}
        form: {}

      @saveUserField = (path) =>
        data = @fields.user[path].getSaveData()
        @fields.user[path].promise = $http.put "/api/users/#{@appUserId}", data
        .success (res) =>
          appUserService.setData res.user
          $rootScope.$broadcast 'gps.appUser', res.user
        
      @saveFormField = (path) =>
        data = @fields.form[path].getSaveData()
        @fields.form[path].promise = $http.put "/api/candidate-profiles/#{@profileId}/short-form", data
        .success (res) =>
          appCandidateProfile.setData res.profile
          $rootScope.$broadcast 'gps.shortForm', res.form
      
      @autocompleteFromList = (list, query) ->
        query = query.trim()
        
        return list if !query.length

        suggestions = []
        list.forEach (item) ->
          suggestions.push item if -1 != item.toLowerCase().indexOf query.toLowerCase()

        return suggestions
      
      #tags-input sets model to array of objects... so convert it before saving
      @saveTaggedFormField = (name) =>
        @fields.form[name].value = _.pluck @fields.form[name].value, 'text'
        @saveFormField(name)
          
      @canWorkInUS = =>
        return _.includes @user.citizenship, 'US'
      
      @launchLiImporter = ->
        modals.launch 'alert', { template: '<linkedin-importer></linkedin-importer>' }

      init = =>
        profile = appCandidateProfile.getData()
        profile.shortForm ?= {}
        @shortForm = profile.shortForm
        @user = appUserService.getData()
        
        @fields = {}
        
        @fields.user = fieldInitializer.initializeFields @user, ['preferredName','gender','diversity','languages', 'citizenship','currentJobStatus','usWorkAuthorization','usSecurityClearance','referralMediumChoice','referralMediumOther','address','address.city','address.countryCode','address.territory']
        @fields.form = fieldInitializer.initializeFields @shortForm, ['nativeLanguages','foreignLanguages','countries','preferredIndustries','yearsWorkExperience','lastPositionLevelHeld','lastDegreeEarned', 'degrees']
        
        #this is a bit of a hack to force this field to initialze as an array, otherwise
        #the first save won't trigger properly... also a bit of a hack to remove
        #duplicate values... not sure how they got in there; will have to investigate later
        @fields.form['preferredIndustries'].value ?= []
        @fields.form['preferredIndustries'].value = _.unique @fields.form['preferredIndustries'].value
        
      @$on 'gps.appUser', =>
        @user = appUserService.getData()
      
      @$on 'gps.profile-imported', init

      @$on 'gps.shortForm', (e, form) =>
        @shortForm = form
      
      init()
  }
  
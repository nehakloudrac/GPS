angular.module 'gps.profile'
.directive 'formLangCert', (
  $rootScope
  profileFormInitializer
  appCandidateProfile
  config
  labeler
) ->
  return {
    restrict: 'E'
    scope:
      cert: '='
      language: '='
    templateUrl: '/apps/profile/directives/edit-languages/form-lang-cert.html'
    link: (scope, elem, attrs) -> as scope, ->
      @labeler = labeler
      
      @form = {}
      isNew = false

      onSaveSuccess = (res) =>
        appCandidateProfile.applyLanguageCertification @language.hash, res.certification
        if isNew
          @$emit 'gps.new-lang-cert', res.certification
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onSaveError = (res) ->
        console.error 'Error saving lang cert field.'
        init()
      
      #assign some config to scope for selection menus
      @conf = config.getMap [
        'languageTests'
      ]
      @conf.languageCertifications = _.omit config.get('languageCertifications'), 'gps'
      @conf.langScaleValues = Object.keys @conf.languageCertifications
      
      @onSelectTest = =>
        return if !@cert.test?
        
        #set scale based on test
        if @cert.test? && @cert.test != 'custom'
          item = _.find(@conf.languageTests, {test: @cert.test})
          @cert.scale = item.scale
          @cert.testName = item.label
        
        #if custom, ensure that scale was selected
        return if @cert.test? && !@cert.scale?

        init()
      
      init = =>
        isNew = true if !@cert.hash?
        @form = {}

        return if !@cert.scale? || !@cert.test
        
        #initialize fields based on chosen language rating scale
        fields = [
          'testName'
          'institution'
          'date'
        ]
        fields.push field.key for field in @conf.languageCertifications[@cert.scale].fields
        
        @form = profileFormInitializer.initializeForm @cert, "/languages/#{@language.hash}/certifications", onSaveSuccess, onSaveError, fields
        @form.saveDataTransformer = (obj) =>
          obj.scale = @cert.scale
          obj.test = @cert.test
          if !obj.testName? && !@cert.hash?
            obj.testName = @cert.testName
          return obj
      
      @$watch 'cert.hash', init
      
      init()
  }
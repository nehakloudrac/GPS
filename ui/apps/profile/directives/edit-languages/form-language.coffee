angular.module 'gps.profile'
.directive 'formLanguage', (
  $rootScope
  profileFormInitializer
  appCandidateProfile
  profileCompletenessHelper
  config
  labeler
  modals
) ->
  return {
    restrict: 'E'
    scope:
      language: '='
    templateUrl: '/apps/profile/directives/edit-languages/form-language.html'
    link: (scope, elem, attrs) -> as scope, ->
      @labeler = labeler
      
      #assign some config to scope for selection menus
      @conf = config.getMap [
        'languageCodes'
        'languageCertifications'
      ]
      @conf.usageSliderLabels = ['Never', 'Once a year or less', 'A few times a year', 'At least once a month', 'At least once a week', 'Every day', 'My primary language of use']
      @conf.selfProficiencyTickLabels = ['','Novice','Limited', 'Intermediate','Advanced','Professional','Native/Bilingual']
      @conf.peakProficiency = [
        {k:1,l:'Novice'}
        {k:2,l:'Limited'}
        {k:3,l:'Intermediate'}
        {k:4,l:'Advanced'}
        {k:5,l:'Professional'}
        {k:6,l:'Native/Bilingual'}
      ]
      
      @form = {}
      @currentCert = null
      @deleteCertPromise = null
      isNew = false
      
      @getLanguageLabel = (code) =>
        return @labeler.getLabel(@conf.languageCodes, code, 'code')
      
      @addCert = (cert) => @currentCert = {}

      @editCert = (cert) => @currentCert = cert

      @isCertIncomplete = (cert) ->
        #TODO: really we need testName && date... and all(?) or any(?) rating fields
        return !profileCompletenessHelper.isLanguageCertComplete cert

      @notCurrentlyEditingCert = (cert) =>
        return true if !@currentCert?.hash? || @currentCert.hash != cert.hash
        return false

      @doneEditingCert = =>
        @currentCert = null
        init()

      @deleteCert = (cert) =>
        if !cert.hash?
          @currentCert = null
          return
          
        res = modals.launch 'confirm', { title: "Remove Certification", message: "Are you sure you want to remove this language certification?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deleteCertPromise = appCandidateProfile.delete "/languages/#{@language.hash}/certifications/#{cert.hash}"
            .success (res) =>
              appCandidateProfile.removeLanguageCertification @language.hash, cert.hash
              if @currentCert && @currentCert.hash == cert.hash
                @currentCert = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove language certification'
              init()
      
      onSaveSuccess = (res) =>
        appCandidateProfile.applyLanguage res.language
        if isNew
          @$emit 'gps.new-language', res.language
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onSaveError = (res) ->
        console.error 'Error saving language field.'
        init()
      
      init = =>
        isNew = true if !@language.hash?

        fields = [
          'code'
          'currentUsageWork'
          'currentUsageSocial'
          'selfCertification.peakProficiency'
          'selfCertification.peakProficiencyLevel'
          'selfCertification.interacting'
          'selfCertification.listening'
          'selfCertification.reading'
          'selfCertification.writing'
        ]

        @form = profileFormInitializer.initializeForm @language, "/languages", onSaveSuccess, onSaveError, fields

        #some overrides, mainly for forcing empty sliders to null
        #instead of 0
        [
          'currentUsageWork'
          'currentUsageSocial'
          'selfCertification.interacting'
          'selfCertification.listening'
          'selfCertification.reading'
          'selfCertification.writing'
        ].forEach (key) =>
          @form.fields[key].saveValueTransformer = (val) ->
            return if val == 0 then null else val

        @form.saveDataTransformer = (obj) ->
          #TODO... deprecate the 'nativeLikeFluency' field
          obj.nativeLikeFluency = false
          return obj

        #check for new language cert that we're editing
        selectCurrentCert()
      
      selectCurrentCert = =>
        if @currentCert?.hash?
          for cert in @language.officialCertifications
            if cert.hash == @currentCert.hash
              @editCert cert
      
      @$on 'gps.new-lang-cert', (e, cert) =>
        @currentCert = cert
        init()
      
      #on an update, ensure that if a cert was
      #being edited, it is updated
      @$on 'gps.appCandidateProfile', selectCurrentCert
        
      
      @$watch 'language.hash', =>
        @currentCert = null
        init()
      
      init()
  }
  
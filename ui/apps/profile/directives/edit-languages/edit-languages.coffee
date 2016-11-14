angular.module 'gps.profile'
.directive 'editLanguages', (
  appCandidateProfile
  labeler
  profileCompletenessHelper
  modals
  $rootScope
  config
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/edit-languages/edit-languages.html'
    link: (scope, elem, attrs) -> as scope, ->
      @languages = {}
      @currentLanguage = null
      
      @notCurrentlyEditing = (language) =>
        return true if !@currentLanguage?.hash? || @currentLanguage.hash != language.hash
        return false
      
      @getLanguageLabel = (code) ->
        return labeler.getLabel config.get('languageCodes'), code, 'code'
      
      @isIncomplete = (language) ->
        return true if !profileCompletenessHelper.isLanguageComplete language
        return false
      
      @add = =>
        @currentLanguage = {}
      
      @edit = (language) =>
        @currentLanguage = language
        
      @done = =>
        @currentLanguage = null
        init()

      @delete = (language) =>
        if !language.hash?
          @currentLanguage = null
          return
          
        res = modals.launch 'confirm', { title: "Remove Language", message: "Are you sure you want to remove this language?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deletePromise = appCandidateProfile.delete "/languages/#{language.hash}"
            .success (res) =>
              appCandidateProfile.removeLanguage(language.hash)
              if @currentLanguage && @currentLanguage.hash == language.hash
                @currentLanguage = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove language'
              init()


      init = =>
        @languages = if appCandidateProfile.getData().languages? then appCandidateProfile.getData().languages else []
        
        #check for new language that we're editing
        if @currentLanguage?.hash?
          for language in @languages
            if language.hash == @currentLanguage.hash
              @edit language
        
      
      @$on 'gps.new-language', (e, language) =>
        @currentLanguage = language
        init()
      
      @$on 'gps.appCandidateProfile', init
      
      init()
  }
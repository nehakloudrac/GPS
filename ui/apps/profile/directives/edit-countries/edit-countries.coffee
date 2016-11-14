angular.module 'gps.profile'
.directive 'editCountries', (
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
    templateUrl: '/apps/profile/directives/edit-countries/edit-countries.html'
    link: (scope, elem, attrs) -> as scope, ->
      @countries = {}
      @currentCountry = null
      
      @notCurrentlyEditing = (country) =>
        return true if !@currentCountry?.hash? || @currentCountry.hash != country.hash
        return false
      
      @getCountryLabel = (code) ->
        return labeler.getLabel config.get('countryCodes'), code, 'code', 'name'
      
      @isIncomplete = (country) ->
        return true if !profileCompletenessHelper.isCountryComplete country
        return false
      
      @add = =>
        @currentCountry = {}
      
      @edit = (country) =>
        @currentCountry = country
        
      @done = =>
        @currentCountry = null
        init()

      @delete = (country) =>
        if !country.hash?
          @currentCountry = null
          return
          
        res = modals.launch 'confirm', { title: "Remove Country", message: "Are you sure you want to remove this country?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deletePromise = appCandidateProfile.delete "/countries/#{country.hash}"
            .success (res) =>
              appCandidateProfile.removeCountry(country.hash)
              if @currentCountry && @currentCountry.hash == country.hash
                @currentCountry = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove country'
              init()


      init = =>
        @countries = if appCandidateProfile.getData().countries? then appCandidateProfile.getData().countries else []
        
        #check for new country that we're editing
        if @currentCountry?.hash?
          for country in @countries
            if country.hash == @currentCountry.hash
              @edit country
        
      
      @$on 'gps.new-country', (e, country) =>
        @currentCountry = country
        init()
      
      @$on 'gps.appCandidateProfile', init
      
      init()
  }
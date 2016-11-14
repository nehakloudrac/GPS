angular.module 'gps.profile'
.directive 'formCountry', (
  $rootScope
  profileFormInitializer
  appCandidateProfile
  config
  labeler
) ->
  return {
    restrict: 'E'
    scope:
      country: '='
    templateUrl: '/apps/profile/directives/edit-countries/form-country.html'
    link: (scope, elem, attrs) -> as scope, ->
      @labeler = labeler
      
      #assign some config to scope for selection menus
      @conf = config.getMap [
        'countryCodes'
        'countrySliders'
        'countryPurposes'
        'countryPurposeSliders'
      ]
      @conf['purposeSliderLabels'] = ['Never','Once or twice','Rarely','Occasionally','Regularly','All the time']
      
      @sliderStates = {}
      @form = {}
      isNew = false
      
      @shouldShowSlider = (slider) =>
        return true if slider.purposes == null

        if @country.purposes?
          for purpose in slider.purposes
            return true if _.includes @country.purposes, purpose
        return false
      
      @toggleSliderNA = (sliderKey) =>
        key = 'activities.'+sliderKey
        @form.fields[key].value = 1
        @saveSlider(sliderKey)
      
      @toggleSliderLocalLang = (sliderKey) =>
        key = 'activities.'+sliderKey+'LocalLangBool'
        @form.fields[key].value = !@form.fields[key].value
        @form.saveField(key)
      
      @saveSlider = (sliderKey) =>
        fieldKey = 'activities.'+sliderKey
        return if @country.activities?[sliderKey]? && @country.activities[sliderKey] == @form.fields[fieldKey].value
        @form.saveField(fieldKey).then =>
          @sliderStates[sliderKey].answered = true
      
      onSaveSuccess = (res) =>
        appCandidateProfile.applyCountry res.country
        if isNew
          @$emit 'gps.new-country', res.country
          isNew = false
        $rootScope.$broadcast 'gps.appCandidateProfile'
        
      onSaveError = (res) ->
        console.error 'Error saving country field.'
        init()
      
      convertZeroToNull = (val) ->
        return if val > 0 then val else null
      
      init = =>
        isNew = true if !@country.hash?

        fields = [
          'code'
          'cultureFamiliarity'
          'businessFamiliarity'
          'purposes'
          'approximateNumberMonths'
          'dateLastVisit'
          'cities'
        ]
        
        #cache a check for whether or not the slider has been answered... it is
        #used often in the template
        @conf.countryPurposeSliders.forEach (item) =>
          fields.push 'activities.'+item.key
          fields.push 'activities.'+item.key+'LocalLangBool'
          @sliderStates[item.key] =
            answered: @country.activities?[item.key]? && @country.activities[item.key] > 1
            localLang: if item.localLang? then item.localLang else true
        
        @form = profileFormInitializer.initializeForm @country, "/countries", onSaveSuccess, onSaveError, fields
        
        #override some fields value handling... I know, gross way to do it
        @form.fields['cultureFamiliarity'].saveValueTransformer = convertZeroToNull
        @form.fields['businessFamiliarity'].saveValueTransformer = convertZeroToNull
      
      @$watch 'country.hash', init
      
      init()
  }
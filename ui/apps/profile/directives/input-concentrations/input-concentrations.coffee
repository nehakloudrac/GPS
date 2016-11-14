# TODO: refactor to use ngModel properly
angular.module 'gps.profile'
.directive 'inputConcentrations', (
  config
  $timeout
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/input-concentrations/input-concentrations.html'
    scope:
      model: '='
      onChange: "&"
    link: (scope, elem, attrs) -> as scope, ->
      @concentrationTypes = config.get 'concentrationTypes'
      @academicSubjects = config.get 'academicSubjects'
      @languageCodes = config.get 'languageCodes'
      @countryCodes = config.get 'countryCodes'
      
      @remove = (index) =>
        @model.splice index, 1
        @update()
      
      @add = =>
        if !@model?
          @model = []
        @model.push {type: 'major'}
      
      @setMetaLanguage = (index, item) =>
        name = _.find(@languageCodes, 'code', @model[index].meta.languageCode).label
        @model[index].meta = {languageCode: @model[index].meta.languageCode, languageName: name}
        @update()
      
      @setMetaCountry = (index) =>
        name = _.find(@countryCodes, 'code', @model[index].meta.countryCode).name
        @model[index].meta = {countryCode: @model[index].meta.countryCode, countryName: name}
        @update()
      
      #TODO - refactor to use ng-model properly
      @update = =>
        if attrs.onChange?
          $timeout =>
            @onChange()
            init()
      
      init = =>
        if !@model? || @model.length == 0
          @model = [{type: 'major'}]
      
      init()
  }

angular.module 'gps.common.models'
.directive 'inputLocation', (config) ->
  return {
    restrict: 'E'
    scope: {}
    require: 'ngModel'
    templateUrl: '/features/models/directives/input-location/input-location.html'
    link: (scope, elem, attrs, ngModel) -> as scope, ->
      @model = {}
      @conf = config.getMap ['countryCodes', 'usTerritories']
      
      #handle the incoming value from 'ng-model'
      ngModel.$formatters.push (modelValue) ->
        if modelValue == null
          return {}
        return modelValue
      
      #update the internal model used in the template
      ngModel.$render = =>
        @model = ngModel.$viewValue
      
      #format the value sent back to 'ng-model', remove empty strings
      ngModel.$parsers.push (viewValue) -> viewValue
      
      #if country changes, the territory should be reset
      @updateCountry = =>
        @model.territory = null
        @update()
      
      #update ng-model when the internal model changes
      @update = =>
        ngModel.$setViewValue(_.cloneDeep @model)
        
  }

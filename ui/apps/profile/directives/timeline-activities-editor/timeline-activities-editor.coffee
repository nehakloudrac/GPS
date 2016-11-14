angular.module 'gps.profile'
.directive 'timelineActivitiesEditor', ($timeout) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/timeline-activities-editor/timeline-activities-editor.html'
    require: 'ngModel'
    scope:
      max: '@'
      length: '@'
      instructions: "@"
    link: (scope, elem, attrs, ngModelCtrl) -> as scope, ->

      #handle the incoming value from 'ng-model'
      ngModelCtrl.$formatters.push (modelValue) ->
        if modelValue != null && modelValue.length != 0
          return modelValue
        return ['']
      
      #update the internal model used in the template
      ngModelCtrl.$render = =>
        @model = ngModelCtrl.$viewValue
      
      #format the value sent back to 'ng-model', remove empty strings
      ngModelCtrl.$parsers.push (viewValue) ->
        strings = []
        for str in viewValue
          if str.trim().length > 0
            strings.push str.trim()
        return strings
        
      #update ng-model when the internal model changes
      @update = (index) =>
        ngModelCtrl.$setViewValue(_.cloneDeep @model)
      
      @remove = (index) =>
        val = @model[index]
        @model.splice index, 1
        return if val.trim().length == 0
        @update()
  }

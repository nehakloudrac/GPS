angular.module 'gps.profile'
.directive 'inputDateRange', ->
  return {
    restrict: 'E'
    scope:
      isCurrentMessage: '@'
    require: 'ngModel'
    templateUrl: '/apps/profile/directives/input-date-range/input-date-range.html'
    link: (scope, elem, attrs, ngModelCtrl) -> as scope, ->
      
      justLinked = true
      
      #handle the incoming value from 'ng-model'
      ngModelCtrl.$formatters.push (modelValue) ->
        if modelValue != null && (modelValue.start? || modelValue.end?)
          if modelValue.start? && !modelValue.end? && justLinked
            modelValue.isCurrent = true
          else
            modelValue.isCurrent = false
          return modelValue
        return {start: null, end: null, isCurrent: false}
      
      #update the internal model used in the template
      ngModelCtrl.$render = =>
        @model = ngModelCtrl.$viewValue
      
      #format the value sent back to 'ng-model', remove empty strings
      ngModelCtrl.$parsers.push (viewValue) ->
        newVal = {start: viewValue.start, end: viewValue.end}
        if newVal.start == newVal.end
          start = new Date(newVal.start * 1000)
          end = new Date(newVal.end * 1000)
          end.setMonth start.getMonth() + 1
          end.setDate 0

          newVal.end = end.getTime() / 1000
        
        # wipe out end if start goes away
        if !newVal.start
          newVal.end = null

        return newVal

      #handle the "current date" toggle
      @toggleIsCurrent = =>
        @model.isCurrent = !@model.isCurrent
        if @model.isCurrent
          @model.end = null
        @update()

      @getMaxDate = =>
        return if @model.end? then @model.end * 1000 else null

      @getMinDate = =>
        return if @model.start? then @model.start * 1000 else null

      #update ng-model when the internal model changes
      @update = (index) =>
        justLinked = false
        ngModelCtrl.$setViewValue(_.cloneDeep @model)
  }
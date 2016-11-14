angular.module 'gps.common.models'
.directive 'inputMultiselect', ->
  return {
    restrict: 'E'
    require: 'ngModel'
    #TODO: use function to return separate template for tagging vs no tagging
    templateUrl: '/features/models/directives/input-multiselect/input-multiselect.html'
    scope:
      choices: "="
      idField: "@"
      labelField: "@"
      placeholder: "@"
      suggestionLimit: "@"

    link: (scope, elem, attrs, ngModelCtrl) -> as scope, ->

      #if "tagging" is true, then choices should be a flat array of strings

      @selected = []
      @idField = if attrs.idField? then @idField else 'key'
      @labelField = if attrs.labelField? then @labelField else 'label'
      @matchField = if attrs.tagging? then @labelField else @idField
      @requireAutocompleteChoice = if attrs.tagging? then 'false' else 'true'
      @placeholder = if attrs.placeholder? then @placeholder else "Type to search..."
      @placeholder = if attrs.noPlaceholder? then "\u00A0" else @placeholder
      @placeholderText = @placeholder
      @suggestionLimit = if attrs.suggestionLimit? then @suggestionLimit else @choices.length
      @loadOnFocus = if attrs.loadOnFocus? then 'true' else 'false'
      
      #scan choices for suitable suggestions
      @autocomplete = (query) =>
        query = query.trim()
        
        return @choices if !query.length

        suggestions = []
        @choices.forEach (item) =>
          matchText = item[@labelField].toLowerCase()+' '+item[@idField].toLowerCase()
          suggestions.push item if -1 != matchText.indexOf query.toLowerCase()

        return suggestions
      
      updatePlaceholder = =>
        @placeholderText = if @selected? && @selected.length > 0 then '\u00A0' else @placeholder
      
      #handle the incoming value from 'ng-model'
      ngModelCtrl.$formatters.push (modelValue) ->
        if modelValue != null
          return modelValue
        return []
      
      #update the internal model used in the template
      ngModelCtrl.$render = =>
        @selected = _.filterByValues @choices, @matchField, ngModelCtrl.$viewValue
        updatePlaceholder()
      
      #format the value sent back to 'ng-model', find in choices
      ngModelCtrl.$parsers.push (viewValue) =>
        items = _.filterByValues @choices, @idField, viewValue
        return _.pluck items, @matchField

      #update ng-model when the internal model changes
      @update = =>
        ngModelCtrl.$setViewValue(_.pluck(@selected, @matchField))
        updatePlaceholder()

      init = =>
        updatePlaceholder()

        #if we're tagging, choices/selection must be a flat list of strings, so convert
        #as needed
        if attrs.tagging?
          @selected = _.map @model, (item) -> { label: item }
          @choices = _.map @choices, (item) -> { label: item }
          return

      init()
  }

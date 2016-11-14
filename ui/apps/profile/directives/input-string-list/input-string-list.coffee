# TODO: refactor this to use ngModel properly
angular.module 'gps.profile'
.directive 'inputStringList', (
  $timeout
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/input-string-list/input-string-list.html'
    scope:
      model: '='
      instructions: '@'
      onChange: "&"
    link: (scope, elem, attrs) -> as scope, ->
      @instructions = if attrs.instructions? then @instructions else "Separate entries with a comma..."
      @value = null

      init = =>
        @value = null
        if @model? && @model.length
          @value = @model.join ', '

      @update = =>
        items = []
        @value.split(',').forEach (item) ->
          trimmed = item.trim()
          items.push trimmed if trimmed.length
        @model = items

        #TODO - refactor to use ng-model properly
        if attrs.onChange?
          $timeout =>
            @onChange()
      
      @$watch 'model', init
      
      init()
  }

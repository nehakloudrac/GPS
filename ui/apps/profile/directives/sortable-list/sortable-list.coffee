angular.module 'gps.profile'
.directive 'sortableList', ->
  
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/sortable-list/sortable-list.html'
    scope:
      max: '@'
      directions: '@'
      choices: '='
      model: '='
      busy: '='
      onUpdate: '&'
    link: (scope, elem, attrs) -> as scope, ->
      @directions = if @directions? then @directions else "Sort from the list above by dragging into the area below."
      @model = if @model? then @model else []
      @selectedOptions = []
      
      @reset = =>
        @selectedOptions = []
        for item in @model
          obj = _.find @choices, {key: item}
          @selectedOptions.push obj if obj
      
      @remove = (index) =>
        @selectedOptions.splice index, 1
        @onSort()

      @onSort = (e) =>
        selected = _.uniq @selectedOptions, 'key'
        selected = _.slice selected, 0, parseInt @max
        updated = _.pluck selected, 'key'
        @onUpdate({items: updated})
      
      @excludeSelected = (item) =>
        return !_.includes @model, item.key
      
      @sortableChoicesConfig =
        sort: false
        ghostClass: 'sortable-choice'
        group:
          name: 'choices'
          pull: 'clone'
      
      @sortableSelectionConfig =
        sort: true
        ghostClass: 'sortable-choice-ghost'
        onUpdate: @onSort
        onAdd: @onSort
        group:
          name: 'selections'
          put: ['choices']

      @reset()
            
      @$watch 'model', @reset
  }
  
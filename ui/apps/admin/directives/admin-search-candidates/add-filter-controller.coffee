angular.module 'gps.admin'
.controller 'AddFilterController', (
  $scope,
  $rootScope,
  searchFilterDefinitions
) -> as $scope, ->
  changed = false


  @defs = _.sortBy(_.values(searchFilterDefinitions), 'name')
  existing = _.pluck @filters, 'key'

  @selections = {}
  @selections[def.key] = false for def in @defs
  @selections[key] = true for key in existing
  
  @toggleFilter = (key) =>
    changed = true
    @selections[key] = !@selections[key]
    evt = if @selections[key] then 'filters.add' else 'filters.remove'
    $rootScope.$broadcast evt, key
    
  @$on '$destroy', ->
    $rootScope.$broadcast 'filters.changed' if changed

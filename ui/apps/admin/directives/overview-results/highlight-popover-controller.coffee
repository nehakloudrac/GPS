angular.module 'gps.admin'
.controller 'HighlightPopoverController', (
  $scope,
  searchFilterDefinitions
) -> as $scope, ->
  @defs = searchFilterDefinitions
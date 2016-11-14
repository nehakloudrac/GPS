###
# This extends the uiSelect directive in order to provide "limit" functionality.
#
# By courtesy of: https://github.com/angular-ui/ui-select/pull/348#issuecomment-63736469
###
angular.module 'gps.profile'
.directive 'uiSelect', ->
  return {
    restrict: 'EA',
    require: 'uiSelect',
    link: ($scope, $element, $attributes, ctrl) ->
      $scope.$select.limit = if (angular.isDefined($attributes.limit)) then parseInt($attributes.limit, 10) else undefined
      superSelect = ctrl.select
      ctrl.select = ->
        #return early if over limit
        return if ctrl.multiple && ctrl.limit != undefined && ctrl.selected.length >= ctrl.limit

        superSelect.apply(ctrl, arguments)
  }
  
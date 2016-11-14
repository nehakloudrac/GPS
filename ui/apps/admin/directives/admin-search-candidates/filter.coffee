angular.module 'gps.admin'
.directive 'searchFilter', ->
  return {
    restrict: 'E'
    scope:
      filter: '='
      result: '='
    templateUrl: '/apps/admin/directives/admin-search-candidates/filter.html'
    link: (scope, elem, attrs) -> as scope, ->
      @$watchCollection 'filter.urlVal', (newVal, oldVal) ->
        return if newVal == oldVal
        @dirty = true
  }
  
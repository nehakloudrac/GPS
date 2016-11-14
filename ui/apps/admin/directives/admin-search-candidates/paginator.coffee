angular.module 'gps.admin'
.directive 'searchPaginator', (
  $timeout
) ->
  return {
    restrict: 'E'
    scope:
      total: '='
      limit: '='
      skip: '='
      onPaginate: '&'
    templateUrl: '/apps/admin/directives/admin-search-candidates/paginator.html'
    link: (scope, elem, attrs) -> as scope, ->
      
      @update = =>
        totalPages = Math.ceil @total / @limit
        currentPage = Math.ceil(@skip / @limit) + 1

        @state =
          visible: @total > @limit
          totalPages: totalPages
          currentPage: currentPage
          range: if @total > 0 then new Array(Math.ceil @total / @limit) else []
      
      @goToPage = (num) =>
        page = num
        page = 1 if num < 1
        page = @state.totalPages if num > @state.totalPages
        
        skip = page * @limit - @limit
        @$emit 'pagination.navigate', skip
      
      @update()
      
      @$watch 'limit', @update
      @$watch 'skip', @update
      @$watch 'total', @update
      @$watch 'state.currentPage', (newVal) =>
        @goToPage newVal
  }

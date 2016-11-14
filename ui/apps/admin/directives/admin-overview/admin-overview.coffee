angular.module 'gps.admin'
.directive 'adminOverview', ($http) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/admin/directives/admin-overview/admin-overview.html'
    link: (scope, elem, attrs) -> as scope, ->
      @counts =
        promise: null
        data: null
        error: null

      @distributions =
        promise: null
        error: null
        data: null
      
      @loadCounts = =>
        @counts.promise = $http.get '/api/admin/overview/counts'
        .success (res) =>
          @counts.error = null
          @counts.data = res
        .error (err) =>
          @counts.error = if err.exception.message then err.exception.message else err.response.message

      @loadDistributions = =>
        @distributions.promise = $http.get '/api/admin/overview/distributions'
        .success (res) =>
          @distributions.error = null
          @distributions.data = res.distributions
        .error (err) =>
          @distributions.error = if err.exception.message then err.exception.message else err.response.message
      
      @loadCounts()
      @loadDistributions()
  }
angular.module 'gps.common.layout'

.factory 'sessionExpirationInterceptor', ($window, $q) ->
  return {
    'responseError': (rejection) ->
      if rejection.data?.response?.code? && 401 == rejection.data.response.code
        $window.location.reload()

      return $q.reject rejection
  }

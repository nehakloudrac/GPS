###
# This forces maintenance mode responses to reload the app, which will trigger
# a redirect to the maintenance page
###
angular.module 'gps.common.layout'
.factory 'maintenanceModeInterceptor', ($q, $window) ->
  return {
    'responseError': (res) ->
      if res.data?.response?.code? && res.data.response.code == 503
        $window.location.reload()

      return $q.reject res
  }

angular.module 'gps.common.layout'

.factory 'deployTagHeaderInterceptor', ($window, appDeployedAt) ->
  return {
    'response': (res) ->

      headers = res.headers()

      if headers['x-gps-deployed-at'] && headers['x-gps-deployed-at'] != appDeployedAt
        $window.location.reload()

      return res
  }

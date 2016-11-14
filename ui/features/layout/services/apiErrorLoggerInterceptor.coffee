###
# This forces http errors into the console to be picked up by track.js
###
angular.module 'gps.common.layout'
.factory 'apiErrorLoggerInterceptor', ($q) ->
  return {
    'responseError': (res) ->
      if res.data?.response?.code? && res.data.response.code >= 400 && res.data.response.code != 401
        console.error 'GPS API Error: ', JSON.stringify res

      if !res.data?.response?.code?
        console.error 'GPS UNKNOWN Error: ', JSON.stringify res

      return $q.reject res
  }

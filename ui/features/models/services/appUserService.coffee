class AppUserService
  constructor: (@data = null, @http) ->
  
  refresh: ->
    promise = @http.get "/api/users/#{@data.id}"
    promise.success (res) =>
      @setData res.user

    return promise
  
  getData: ->
    return @data
  
  setData: (data) ->
    @data = data
  
angular.module 'gps.common.models'

.service 'appUserService', ['appUser', '$http', AppUserService]

angular.module 'gps.admin'
.controller 'ProfileModalController', ($scope, $http) -> as $scope, ->
  
  @promise = null
  @user = null
  @profile = null
  @error = null
  
  rawProfile = null
  rawUser = null
  
  @getProfileJson = =>
    return JSON.stringify @profile, null, 4
  
  @getUserJson = =>
    return JSON.stringify @user, null, 4
  
  loadProfile = =>
    @promise = $http.get "/api/admin/users/#{@userId}/details"
    .success (res) =>
      @user = res.user
      @profile = res.profile
      rawUser = _.cloneDeep res.user
      rawProfile = _.cloneDeep res.profile
      
      @error = null
    .error (err) =>
      @error = if err.exception? then err.exception.message else err.response.message
  
  loadProfile()
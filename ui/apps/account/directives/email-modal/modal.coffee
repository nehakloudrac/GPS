angular.module 'gps.account'
.controller 'ChangeEmailModalController', (
  $scope,
  $http,
  appUserService
) -> as $scope, ->
  @promise = null
  @error = null
  @newEmail = null
  @oldEmail = appUserService.getData().email

  @confirm = =>
    @promise = $http.put("/api/users/#{appUserService.getData().id}/email", {email: @newEmail})
    @promise.success (res) =>
      @$close true

    @promise.error (res) =>
      if res.errors?
        @error = res.errors[0].messages[0]
        return
      if res.response.message?
        @error = res.response.message

  @cancel = =>
    @$close false

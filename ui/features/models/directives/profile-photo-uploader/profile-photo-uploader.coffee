angular.module 'gps.common.models'
.directive 'profilePhotoUploader', (appUser, profileImageHelper, Upload, $http, $rootScope) ->
  return {
    restrict: 'E'
    templateUrl: '/features/models/directives/profile-photo-uploader/profile-photo-uploader.html'
    scope:
      instructions: '@'
    link: (scope, elem, attrs) -> as scope, ->

      @profileImageUrl = null
      @fileToUpload = null
      @progress = null
      @promise = null
      @appUser = appUser
      @instructions = if @instructions then @instructions else "To replace your photo, drag a new one into this area, or click the image and select the file you want to upload."

      updateUrl = =>
        @profileImageUrl = profileImageHelper.getProfileImageUrl appUser

      @upload = (files) =>
        if files && files.length
          file = files[0]

          @promise = Upload.upload({
            url: "/api/users/#{@appUser.id}/profile-image"
            fileFormDataName: 'file'
            file: file
          })
          .success (res) =>
            @appUser = appUser = res.user
            $rootScope.$broadcast 'gps.appUser', res.user
            updateUrl()
          .progress (evt) =>
            @progress = parseInt(100.0 * evt.loaded / evt.total)
          .error (err) ->
            console.log 'ERROR: ', err

      @removePhoto = () =>
        @promise = $http.delete "/api/users/#{@appUser.id}/profile-image"
        .success (res) =>
          @appUser = appUser = res.user
          $rootScope.$broadcast 'gps.appUser', res.user
          updateUrl()
        .error (err) ->
          updateUrl()
          console.log 'TODO: handle error'

      @toggleGravatar = () =>
        data = {preferences: {allowGravatar: !appUser.preferences.allowGravatar}}
        @promise = $http.put "/api/users/#{@appUser.id}", data
        .success (res) =>
          @appUser = appUser = res.user
          $rootScope.$broadcast 'gps.appUser', res.user
          updateUrl()
        .error (err) ->
          updateUrl()
          console.log 'TODO: handle error'

      updateUrl()
  }

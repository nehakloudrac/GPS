angular.module 'gps.profile'
.directive 'linkedinImporter', (
  Upload
  appCandidateProfile
  appUserService
  $rootScope
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/linkedin-importer/linkedin-importer.html'
    link: (scope, elem, attrs) -> as scope, ->
      @error = null
      @promise = null
      @progress = null
      @success = false
      
      @upload = (files) =>
        if files && files.length
          file = files[0]

          @promise = Upload.upload({
            url: "/api/candidate-profiles/#{appCandidateProfile.getData().id}/import-profile"
            fileFormDataName: 'file'
            file: file
          })
          .success (res) =>
            @error = null
            appCandidateProfile.setData res.profile
            appUserService.setData res.user
            $rootScope.$broadcast 'gps.appCandidateProfile', res.profile
            $rootScope.$broadcast 'gps.appUser', res.user
            $rootScope.$broadcast 'gps.profile-imported'
            @success = true
          .progress (evt) =>
            @progress = parseInt(100.0 * evt.loaded / evt.total)
          .error (err) =>
            @error = if err.response?.message? then err.response.message else 'We were unable to import your profile.'
  }

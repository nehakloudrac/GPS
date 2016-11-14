angular.module 'gps.profile'
.controller 'CharacterTraitsController', (
  $scope,
  $http,
  appCandidateProfile,
  config
) -> as $scope, ->

  @promise = null
  @characterTraits = config.get 'characterTraits'
  @profile = appCandidateProfile.getData()

  @save = (traits) =>
    data = { characterTraits: traits }
    @promise = appCandidateProfile.put "", data
    .success (res) =>
      appCandidateProfile.setData res.profile
      @reset()
    .error (err) =>
      console.error err
      @reset()

  @reset = =>
    @profile = appCandidateProfile.getData()

  @reset()

angular.module 'gps.profile'
.controller 'EmployerIdealsController', (
  $scope,
  $http,
  appCandidateProfile,
  config
) -> as $scope, ->

  cleanTraits = (traits) ->
    return _.filter traits, ((item) -> return true if item)

  @promise = null
  @ideals = config.get 'ideals'
  @profile = appCandidateProfile.getData()

  @save = (traits) =>
    traits = cleanTraits traits
    data = { employerIdeals: traits }
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

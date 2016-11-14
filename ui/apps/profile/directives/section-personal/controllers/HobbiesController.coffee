angular.module 'gps.profile'
.controller 'HobbiesController', (
  $scope,
  $http,
  appCandidateProfile
) -> as $scope, ->

  @data =
    promise: null
    hobbies: []

  @profile = appCandidateProfile.getData()

  @save = =>
    data = { hobbies: _.reject(@data.hobbies, (elem) -> return elem.length == 0) }

    @data.promise = appCandidateProfile.put "", data
    .success (res) =>
      appCandidateProfile.setData res.profile
      buildHobbies()
    .error (err) =>
      console.error err
      buildHobbies()

  buildHobbies = =>
    @profile = profile = appCandidateProfile.getData()
    @data.hobbies = if profile.hobbies? then _.cloneDeep profile.hobbies else []
    @data.hobbies.push '' if @data.hobbies.length == 0

  buildHobbies()

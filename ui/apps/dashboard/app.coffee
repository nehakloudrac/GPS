#utility to allow easier use of coffeescript "@" this syntax
window.as = (context, fn) -> fn.call(context)

angular.module 'gps.dashboard', [
  'ngSanitize'
  'gps.common.layout'
  'mgcrea.ngStrap.tooltip'
  'mgcrea.ngStrap.select'
  'angular-progress-arc'
]

.run ($window, $state, layout, $rootScope, $urlRouter, appCandidateProfile) ->
  layout.setTitle "Dashboard"
  layout.setBreadcrumb [
      title: 'Dashboard'
  ]

  # If the user hasn't done the profile intro yet, redirect them to their profile
  profile = appCandidateProfile.getData()
  if !profile.profileStatus? || !profile.profileStatus.introCompleted
    $window.location.href = '/candidate/profile'

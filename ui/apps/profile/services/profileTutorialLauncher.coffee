class ProfileTutorialLauncher
  constructor: (@userService, @http, @modals, @tutorial, @rootScope, @layout) ->
  
  launchTutorial: ->
    user = @userService.getData()
    
    promise = @modals.launch 'tutorial', {title: "Profile Tutorial", tutorial: @tutorial, finishedText: "Get Started!"}
    .then (result) =>
      firstTime = false
      if !user.status?.seenProfileViewTutorial? || !user.status.seenProfileViewTutorial
        firstTime = true
        @http.put "/api/users/#{user.id}", { status: { seenProfileViewTutorial: true } }
        .success (res) =>
          @userService.setData res.user
          @rootScope.$broadcast 'gps.appUser', res.user
        .error console.error
      
    return promise
    
angular.module 'gps.profile'
.service 'profileTutorialLauncher', [
  'appUserService',
  '$http',
  'modals',
  'profileViewTutorial',
  '$rootScope',
  'layout',
  ProfileTutorialLauncher
]

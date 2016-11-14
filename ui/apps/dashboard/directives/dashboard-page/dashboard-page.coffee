angular.module 'gps.dashboard'
.directive 'dashboardPage', (
  $http,
  $window,
  $rootScope,
  appUserService,
  appCandidateProfile,
  profileCompletenessHelper,
  profileImageHelper,
  layout,
  modals,
  $timeout,
  dashboardTutorial,
  config,
  labeler,
  newsStories,
  menuCompletenessHelper,
  locationFormatter
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/dashboard/directives/dashboard-page/dashboard-page.html'
    link: (scope, elem, attrs) -> as scope, ->
      @layout = layout
      @profile = appCandidateProfile.getData()
      @user = appUserService.getData()
      @story = newsStories[0]
      
      @viewSampleProfile = ->
        modals.launch 'sampleProfile'
      
      @profileIsComplete = profileCompletenessHelper.isProfileComplete @profile
      
      @getProfileImageUrl = => profileImageHelper.getProfileImageUrl @user, 300
      
      greetings = ["Hello", "Privét", "Jambo", "¡Hola", "Guten Tag", "Nǐ hǎo", "Namaste", "Bonjour"]
      @greetingText = (-> greetings[Math.floor(Math.random() * greetings.length)] )()
      
      @getStatus = =>
        return labeler.getLabel config.get('userJobStatusOptions'), @user.currentJobStatus
      
      @isAdmin = =>
        return _.includes @user.roles, 'ROLE_ADMIN'
      
      @getUserLocation = =>
        return locationFormatter.formatLocation @user.address
      
      @getDashboardBannerMessage = =>
        #unverified email
        if !@user.isVerified
          return "Please verify your email address by clicking the link we sent to your registered account. Once you have verified your email, GPS will begin matching you to job opportunities. Request another <a href='/account/verify' target='_blank'>verification link</a>."
      
        # TODO: this condition may no longer apply...
        if @user.isVerified && !@user.status?.seenProfileViewTutorial
          return "Thank you for verifying your email address. To complete your profile, click “Continue Profile” below."
        
        if menuCompletenessHelper.isComplete()
          return "GPS is working to match you with a job opportunity that leverages your experience and interests."
          
        return "Keep up the momentum! Click any incomplete section below to complete your profile."
      
      @launchDashboardTutorial = =>
        modals.launch 'tutorial', {title: "Dashboard Tutorial", tutorial: dashboardTutorial}
        .then =>
          if !@user.status?.seenDashboardTutorial? || !@user.status.seenDashboardTutorial
            $http.put "/api/users/#{@user.id}", { status: { seenDashboardTutorial: true } }
            .success (res) =>
              appUserService.setData res.user
              @user = res.user
              $rootScope.$broadcast 'gps.appUser', res.user
      
            .error console.error
      
      # automatically launch the dashboard tutorial if they HAVE seen the profile
      # tutorial, but have NOT seen the dashboard tutorial... basically, we don't
      # show the dashboard tutorial immediately, only after they've already been
      # to their profile
      if (
        (!@user.status?.seenDashboardTutorial? || !@user.status.seenDashboardTutorial) &&
        (@user.status?.seenProfileViewTutorial? && @user.status.seenProfileViewTutorial)
      )
        $timeout @launchDashboardTutorial, 3000

      # not sure if this really needs to be in a timeout, but it seems safer that way
      setTimeout (->
        if $window.twttr
          $window.twttr.widgets.load()
      ), 0
      
      @$on 'gps.appUser', (e, user) =>
        @user = appUserService.getData()

  }
  
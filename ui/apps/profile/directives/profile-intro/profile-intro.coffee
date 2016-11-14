angular.module 'gps.profile'
.directive 'profileIntro', (
  appCandidateProfile
  appUserService
  profileCompletenessHelper
  profileTutorialLauncher
  $http
  modals
  $location
  layout
  $analytics
  $window
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/profile-intro/profile-intro.html'
    link: (scope, elem, attrs) -> as scope, ->
      @current = null         #the currently opened card
      @targetSection = null   #the card that needs to be finished
      @currentSectionNumber = 0
      @sectionOrder = [
        'background'
        'professional'
        'education'
        'ideal-job'
        'countries'
        'languages'
        'skills'
        'personal'
      ]
      
      @getProgressWidth = =>
        total = @sectionOrder.length
        current = 0
        for item in @sectionOrder
          current++
          break if item == @targetSection
        
        return Math.ceil(current / total * 100)
      
      sectionStateMap = {}
      
      @showTutorial = ->
        profileTutorialLauncher.launchTutorial()
      
      @saveAndExit = =>
        modalScope =
          title: 'Finish Later?'
          cancelText: 'Continue profile'
          confirmText: 'Exit'
          template: '/apps/profile/directives/profile-intro/exit-modal.html'
          extras:
            verified: true
            promise: appUserService.refresh()
        modalScope.extras.promise.then ->
          modalScope.extras.verified =  appUserService.getData().isVerified
          console.log appUserService.getData()

        res = modals.launch 'confirm', modalScope
        res.then (ok) ->
          if ok
            $window.location.href = '/logout'
      
      @finishAndViewProfile = =>
        appCandidateProfile.put '', {profileStatus:{introCompleted: true}}
        .success (res) =>
          appCandidateProfile.setData res.profile
          layout.go 'profile.view'
      
      @tryNavToState = (name) =>
        return if !@isSectionEnabled(name)
        @navToState(name)
      
      @isSectionEnabled = (name) =>
        return true if name == @targetSection
        for item in @sectionOrder
          return true if sectionStateMap[name]?
        return false
      
      @isCurrent = (name) => name == @current || name == @targetSection
      
      #don't allow closing the target card, but do allow closing
      #previously seen cards by setting the current state to the
      #target state
      @navToState = (name) =>
        #TODO: trigger virtual page view
        if name == @current
          name = @targetSection
        @current = name
        @currentSectionNumber = _.indexOf(@sectionOrder, @targetSection) + 1
        $location.search {section: name}
        $analytics.pageTrack("/candidate/profile/intro/#{name}")

        #scroll to open card, done in timeout... just cause
        setTimeout (->
          targetElem = elem.find('.profile-card.expanded').eq(0)
          if targetElem.length
            $('body').eq(0).animate({scrollTop: targetElem.offset().top - 100}, 'slow')
          ), 100
      
      @skipSection = (name) =>
        sectionsSkipped = if @profile.profileStatus?.introSectionsSkipped? then _.cloneDeep @profile.profileStatus.introSectionsSkipped else []
        sectionsSkipped.push name
        sectionsSkipped = _.uniq sectionsSkipped

        appCandidateProfile.put '', {profileStatus:{introSectionsSkipped: sectionsSkipped}}
        .success (res) =>
          appCandidateProfile.setData res.profile
          init()
        .error console.error
        
      @seenSection = (name) =>
        sectionsSeen = if @profile.profileStatus?.introSectionsSeen? then _.cloneDeep @profile.profileStatus.introSectionsSeen else []
        sectionsSeen.push name
        sectionsSeen = _.uniq sectionsSeen
        
        promise = appCandidateProfile.put '', {profileStatus:{introSectionsSeen: sectionsSeen}}
        .success (res) =>
          appCandidateProfile.setData res.profile
          init()
          
        return promise
      
      @finishBackground = =>
        promise = @seenSection('background')
        promise.then ->
          appCandidateProfile.put '/short-form', {completed: true}
          .success (res) ->
            appCandidateProfile.setData res.profile
            init()
          .error ->
            console.error 'Error finishing short form'
            init()
        
        return promise
      
      @finishPersonal = =>
        sectionsSeen = if @profile.profileStatus?.introSectionsSeen? then _.cloneDeep @profile.profileStatus.introSectionsSeen else []
        sectionsSeen.push 'personal'
        sectionsSeen = _.uniq sectionsSeen
        appCandidateProfile.put '', {profileStatus:{introCompleted: true, introSectionsSeen: sectionsSeen}}
        .success (res) =>
          appCandidateProfile.setData res.profile
          $analytics.pageTrack("/candidate/profile/intro/complete")
          layout.go 'profile.view'
        
      @confirm = (text, title = null) =>
        title = if title then title else "Incomplete information"
        return modals.launch 'confirm', {title: title, message: text}
      
      @checkBackground = =>
        if (
          !@user.currentJobStatus ||
          !@user.address?.countryCode || !@user.address?.city ||
          !@user.languages? || @user.languages.length < 1 ||
          !@user.citizenship? || @user.citizenship.length < 1
        )
          #note, not return the confirm promise on purpose
          @confirm("Some required fields have not been answered.  Ensure that all required fields have been answered to continue to the next section.")
          return false
        return true
      
      @checkProfessional = =>
        prof = true
        certs = true
        if @profile.timeline?
          for evt in @profile.timeline
            if _.includes ['job','volunteer','research','military'], evt.type
              prof = false if !profileCompletenessHelper.isTimelineEventComplete(evt)
        
        if @profile.certifications?
          for cert in @profile.certifications
            certs = false if !profileCompletenessHelper.isCertificationComplete(cert)
        
        return true if certs && prof

        items = []
        items.push("certifications") if !certs
        items.push("professional history") if !prof
        return @confirm("Some items in your #{items.join(' and ')} are missing required information, and may not be displayed on your profile as a result.  Are you sure you would like to continue?")
        
      
      @checkEducation = =>
        timeline = true
        awards = true
        orgs = true
        if @profile.timeline?
          for evt in @profile.timeline
            if _.includes ['university','study_abroad','language_acquisition'], evt.type
              timeline = false if !profileCompletenessHelper.isTimelineEventComplete(evt)
        if @profile.awards?
          for award in @profile.awards
            awards = false if !profileCompletenessHelper.isAwardComplete(award)


        if @profile.academicOrganizations?
          for org in @profile.academicOrganizations
            orgs = false if profileCompletenessHelper.isAcademicOrgComplete(org)
        return true if timeline && awards && orgs
        return @confirm("Some items in this section are missing required information, and may not be displayed on your profile as a result.  Are you sure you would like to continue?")

      
      @checkCountries = =>
        if @profile.countries?
          for country in @profile.countries
            if !profileCompletenessHelper.isCountryComplete country
              return @confirm("Some countries listed are missing required information.  They may not be shown on your profile until they have been completed.  Are you sure you want to continue?")
        return true
      
      @checkLanguages = =>
        if @profile.languages?
          for lang in @profile.languages
            if !profileCompletenessHelper.isLanguageComplete lang
              return @confirm("Some languages listed are missing required information, and may not be shown on your profile as a result.  Are you sure you would like to continue?")
        return true
      
      @checkIdealJob = => true
      
      @checkSkills = => true
      
      @checkPersonal = => true
      
      init = =>
        @profile = appCandidateProfile.getData()
        @user = appUserService.getData()
        sectionStateMap = {}

        #figure out all sections seen
        if @profile.profileStatus?.introSectionsSkipped?
          for item in @profile.profileStatus.introSectionsSkipped
            sectionStateMap[item] = true
        
        if @profile.profileStatus?.introSectionsSeen?
          for item in @profile.profileStatus.introSectionsSeen
            sectionStateMap[item] = true
        
        #nav to first incomplete section
        for item in @sectionOrder
          if sectionStateMap[item] != true
            @targetSection = item
            @navToState(item)
            return
        
      @$on 'gps.appCandidateProfile', =>
        @profile = appCandidateProfile.getData()
      
      @$on 'gps.appUser', =>
        @user = appUserService.getData()
      
      @$on 'form.closed', (e, evt) ->
        targetElem = angular.element(evt.currentTarget).parent().parent()
        $('body').eq(0).animate({scrollTop: targetElem.offset().top - 100}, 'slow')

      init()
      
  }
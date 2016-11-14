class SectionsSeenTracker

  constructor: (@layout, @modals, @profileService, @rootScope) ->
    @sections = [
      'experience-abroad'
      'languages'
      'work-history'
      'ideal-job'
      'education-history'
      'awards-honors'
      'soft-skills'
      'hard-skills'
      'domain-skills'
      'personal'
    ]

  seenSection: (section) ->

    #get seen sections
    p = @profileService.getData()
    hasSeen = if p.profileStatus?.sectionsSeen? then p.profileStatus.sectionsSeen else []
    
    #return early if already seen
    return if _.includes(hasSeen, section) || p.profileStatus.allSectionsSeenNotified == true
    
    hasSeen.push section
    
    @profileService.put '', {profileStatus: {sectionsSeen: hasSeen}}
    .success (res) =>
      @profileService.setData(res.profile)
      
      if res.profile.profileStatus.sectionsSeen.length == @sections.length && !res.profile.profileStatus.allSectionsSeenNotified
        result = @modals.launch 'sectionsSeenModal'
        result.then (action) =>
          @profileService.put '', {profileStatus: {allSectionsSeenNotified: true}}
          switch action
            when 'dashboard' then window.location = "/candidate/dashboard"
            when 'edit' then null
            when 'profile' then @layout.go 'profile.view'
    .error (err) ->
      console.error "failed updating seen sections"

angular.module 'gps.profile'
.service 'sectionsSeenTracker', ['layout', 'modals', 'appCandidateProfile', '$rootScope', SectionsSeenTracker]
.config (modalsProvider) ->
  modalsProvider.defineModal 'sectionsSeenModal', {
    templateUrl: '/apps/profile/templates/sections-seen-modal.html'
    backdrop: 'static'
    animation: 'am-fade'
  }

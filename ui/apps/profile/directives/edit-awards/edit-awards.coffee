angular.module 'gps.profile'
.directive 'editAwards', (
  $rootScope
  appCandidateProfile
  modals
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/edit-awards/edit-awards.html'
    link: (scope, elem, attrs) -> as scope, ->
      @awards = []
      @currentAward = null
      @deletePromise = null
      
      @isIncomplete = (award) ->
        return true if (!award.name || !award.date)
        return false
      
      @notCurrentlyEditing = (award) =>
        return true if !@currentAward
        return false if !award.hash || award.hash == @currentAward.hash
        return true
      
      @add = =>
        @currentAward = {}
        @edit @currentAward
      
      @edit = (award) =>
        @currentAward = award
      
      @done = =>
        @currentAward = null
        init()
        
      @delete = (award) =>
        if !award.hash?
          @currentAward = null
          return
          
        res = modals.launch 'confirm', { title: "Remove Award", message: "Are you sure you want to remove this award?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deletePromise = appCandidateProfile.delete "/awards/#{award.hash}"
            .success (res) =>
              appCandidateProfile.removeAward(award.hash)
              if @currentAward && @currentAward.hash == award.hash
                @currentAward = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove award'
              init()

      init = =>
        @awards = if appCandidateProfile.getData().awards? then appCandidateProfile.getData().awards else []
        
        #if a award is currently being edited, make sure pull it from the array to ensure
        #the form properly updates
        if @currentAward?.hash?
          for award in @awards
            if award.hash == @currentAward.hash
              @edit award
      
      #detect new award and keep editing
      @$on 'gps.new-award', (e, award) =>
        @currentAward = award
        init()
      
      #form edits will trigger this
      @$on 'gps.appCandidateProfile', init
      
      init()
  }
  
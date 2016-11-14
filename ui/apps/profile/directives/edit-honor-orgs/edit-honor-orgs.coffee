angular.module 'gps.profile'
.directive 'editHonorOrgs', (
  $rootScope
  appCandidateProfile
  modals
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/edit-honor-orgs/edit-honor-orgs.html'
    link: (scope, elem, attrs) -> as scope, ->
      @orgs = []
      @currentOrg = null
      @deletePromise = null
      
      @isIncomplete = (org) ->
        return true if (!org.name || !org.duration?.start)
        return false
      
      @notCurrentlyEditing = (org) =>
        return true if !@currentOrg
        return false if !org.hash || org.hash == @currentOrg.hash
        return true
      
      @add = =>
        @currentOrg = {}
        @edit @currentOrg
      
      @edit = (org) =>
        @currentOrg = org
      
      @done = =>
        @currentOrg = null
        init()
        
      @delete = (org) =>
        if !org.hash?
          @currentOrg = null
          return
          
        res = modals.launch 'confirm', { title: "Remove Organization", message: "Are you sure you want to remove this academic organization or honor society?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deletePromise = appCandidateProfile.delete "/academic-organizations/#{org.hash}"
            .success (res) =>
              appCandidateProfile.removeAcademicOrg(org.hash)
              if @currentOrg && @currentOrg.hash == org.hash
                @currentOrg = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove organization'
              init()

      init = =>
        @orgs = if appCandidateProfile.getData().academicOrganizations? then appCandidateProfile.getData().academicOrganizations else []
        
        #if a org is currently being edited, make sure pull it from the array to ensure
        #the form properly updates
        if @currentOrg?.hash?
          for org in @orgs
            if org.hash == @currentOrg.hash
              @edit org
      
      #detect new org and keep editing
      @$on 'gps.new-org', (e, org) =>
        @currentOrg = org
        init()
      
      #form edits will trigger this
      @$on 'gps.appCandidateProfile', init
      
      init()
  }
  
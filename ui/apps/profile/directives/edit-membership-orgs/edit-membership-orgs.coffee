angular.module 'gps.profile'
.directive 'editMembershipOrgs', (
  $rootScope
  appCandidateProfile
  modals
  config
  labeler
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/edit-membership-orgs/edit-membership-orgs.html'
    link: (scope, elem, attrs) -> as scope, ->
      @orgs = []
      @currentOrg = null
      @deletePromise = null

      @isIncomplete = (org) =>
        return true if (!org.institution?.name || !org.level)
        return false
      
      @getLabel = (val) ->
        return labeler.getLabel config.get('organizationMembershipLevels'), val, 'value'
      
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
          
        res = modals.launch 'confirm', { title: "Remove Organization", message: "Are you sure you want to remove this organization?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deletePromise = appCandidateProfile.delete "/organizations/#{org.hash}"
            .success (res) =>
              appCandidateProfile.removeOrganization(org.hash)
              if @currentOrg && @currentOrg.hash == org.hash
                @currentOrg = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove organization'
              init()

      init = =>
        @orgs = if appCandidateProfile.getData().organizations? then appCandidateProfile.getData().organizations else []
        
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
  
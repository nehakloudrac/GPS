angular.module 'gps.profile'
.directive 'editCertifications', (
  $rootScope
  appCandidateProfile
  modals
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/profile/directives/edit-certifications/edit-certifications.html'
    link: (scope, elem, attrs) -> as scope, ->
      @certs = []
      @currentCert = null
      @deletePromise = null
        
      @isIncomplete = (cert) ->
        return true if (!cert.name || !cert.duration?.start? || !cert.organization)
        return false
      
      @notCurrentlyEditing = (cert) =>
        return true if !@currentCert
        return false if !cert.hash || cert.hash == @currentCert.hash
        return true
      
      @add = =>
        @currentCert = {}
        @edit @currentCert
      
      @edit = (cert) =>
        @currentCert = cert
      
      @done = =>
        @currentCert = null
        init()
        
      @delete = (cert) =>
        if !cert.hash?
          @currentCert = null
          return
          
        res = modals.launch 'confirm', { title: "Remove Certification", message: "Are you sure you want to remove this certification?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deletePromise = appCandidateProfile.delete "/certifications/#{cert.hash}"
            .success (res) =>
              appCandidateProfile.removeCertification(cert.hash)
              if @currentCert && @currentCert.hash == cert.hash
                @currentCert = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove cert'
              init()

      init = =>
        @certs = if appCandidateProfile.getData().certifications? then appCandidateProfile.getData().certifications else []
        
        #if a cert is currently being edited, make sure pull it from the array to ensure
        #the form properly updates
        if @currentCert?.hash?
          for cert in @certs
            if cert.hash == @currentCert.hash
              @edit cert
      
      #detect new cert and keep editing
      @$on 'gps.new-cert', (e, cert) =>
        @currentCert = cert
        init()
      
      #form edits will trigger this
      @$on 'gps.appCandidateProfile', init
      
      init()
  }
  
angular.module 'gps.account'
.directive 'accountPrefs', ($rootScope, prefsConfig, $http, appUser) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/account/directives/account-prefs/account-prefs.html'
    link: (scope, elem, attrs) -> as scope, ->
      #template config
      @prefsConfig = prefsConfig
      @prefs = {}
      if !appUser.preferences?
        appUser.preferences = {}
        
      for pref in @prefsConfig
        if appUser.preferences[pref.key]?
          @prefs[pref.key] = appUser.preferences[pref.key]
        else
          @prefs[pref.key] = null

      #saving state
      @saving = {}
      @saving[pref.key] = false for pref in @prefsConfig
      
      #save to server each pref update
      @updatePref = (key, newVal) =>
        #updates to user model
        data = {preferences: {}}
        data.preferences[key] = newVal
        
        #save the data
        @saving[key] = true
        $http.put "/api/users/#{appUser.id}", data
        .success (res) =>
          appUser.preferences = @prefs = res.user.preferences
          $rootScope.$broadcast 'gps.appUser', res.user

          @saving[key] = false
        .error (res) =>
          @saving[key] = false
          throw new Error res

  }
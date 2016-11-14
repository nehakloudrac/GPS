angular.module 'gps.account'
.directive 'accountForm', (
  $rootScope
  $http
  appUser
  appUserService
  profileImageHelper
  labeler
  config
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/account/directives/account-form/account-form.html'
    link: (scope, elem, attrs) -> as scope, ->
      @genderOptions = config.get 'genderOptions'
      @countryCodes = config.get 'countryCodes'
      @languageCodes = config.get 'languageCodes'
      @usTerritories = config.get 'usTerritories'
      @usWorkAuthorizations = config.get 'usWorkAuthorizations'
      @usSecurityClearances = config.get 'usSecurityClearances'
      @userJobStatusOptions = config.get 'userJobStatusOptions'
      @referralMediums = config.get 'referralMediums'
      @diversityFlags = config.get 'diversityFlags'
      @labeler = labeler

      @user = appUserService.getData()

      @profileImageUrl = profileImageHelper.getProfileImageUrl @user, 300
      @models = {}
      
      @couldWorkInUSA = =>
        return true if (
          (@user.citizenship? && _.includes @user.citizenship, 'US') ||
          (@user.address? && @user.address.countryCode == 'US')
        )
        return false
      
      @saveField = (field, path = null) =>
        path = if path then path else "/api/users/#{appUser.id}"

        data = @models[field].getSaveData()

        @models[field].promise = $http.put path, data
        .success (res) =>
          @models[field].error = false
          @models[field].editing = false
          if res.user?
            appUserService.setData res.user
            $rootScope.$broadcast 'gps.appUser', res.user

            @init()
        .error (err) =>
          @init()
      
      @init = =>
        for field in ['address', 'firstName', 'lastName', 'preferredName', 'phone', 'gender', 'diversity', 'email', 'languages', 'citizenship', 'currentJobStatus', 'usWorkAuthorization', 'usSecurityClearance','referralMediumChoice','referralMediumOther']
          do (field) =>
            user = appUserService.getData()

            @models[field] = {
              promise: null
              value: if user[field]? then _.cloneDeep user[field] else null
              getSaveData: (val) ->
                data = {}
                data[field] = @value
                return data
            }

        for key in ['address.city','address.countryCode','address.territory']
          do (key) =>
            [path, name] = key.split('.')
            user = appUserService.getData()
            field = {promise: null}
            field.value = if user[path]?[name]? then _.cloneDeep user[path][name] else null
            field.getSaveData = ->
              data = {address: {}}
              data.address[name] = @value
              return data
            @models[key] = field


      @canWorkInUS = =>
        return _.includes @user.citizenship, 'US'

      @saveEmail = =>
        @saveField 'email', "/api/users/#{appUser.id}/email"

      @savePassword = =>
        throw new Error 'not implemented'

      @$on 'gps.appUser', (e, data) =>
        @user = appUserService.getData()

      @init()
  }
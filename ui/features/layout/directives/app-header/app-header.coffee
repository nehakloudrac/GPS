angular.module 'gps.common.layout'
.directive 'appHeader', (
  layout,
  appUserService,
  profileImageHelper
) ->
  return {
    restrict: 'E'
    templateUrl: '/features/layout/directives/app-header/app-header.html'
    scope:
      showMenu: '&'
    link: (scope, elem, attrs) -> as scope, ->
      @layout = layout
      @user = appUserService.getData()
      @menu = =>
        return if attrs.showMenu? then @showMenu() else true
      
      @getProfileImageUrl = => profileImageHelper.getProfileImageUrl @user, 300

      @isAdmin = =>
        return _.includes @user.roles, 'ROLE_ADMIN'
      
      @$on 'gps.appUser', =>
        @user = appUserService.getData()
  }
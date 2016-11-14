angular.module 'gps.common.layout'
.directive 'appMenu', (appUserService) ->
  return {
    restrict: 'E'
    scope:
      active: '@'
    templateUrl: '/features/layout/directives/app-menu/app-menu.html'
    link: (scope, elem, attrs) -> as scope, ->
      user = appUserService.getData()
      
      isAdmin = ->
        return _.includes user.roles, 'ROLE_ADMIN'
      
      @tabs = [
          key: 'dashboard'
          name: "Dashboard"
          url: "/candidate/dashboard"
          classes: 'g g-dashboard'
          visible: true
        ,
          key: 'profile'
          name: "Profile"
          url: "/candidate/profile"
          classes: 'g g-profile'
          visible: true
        ,
          key: 'resources'
          name: "Resources"
          url: "/candidate/resources"
          classes: 'g g-books'
          visible: true
        ,
          key: 'account'
          name: "Account"
          url: "/candidate/account"
          classes: 'g g-telephone'
          visible: true
        ,
          key: 'admin'
          name: "Admin"
          url: "/admin"
          classes: 'g g-gear'
          visible: isAdmin()
      ]
  }

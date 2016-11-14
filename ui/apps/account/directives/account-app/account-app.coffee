angular.module 'gps.account'
.directive 'accountApp', (
  modals,
  $window
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/account/directives/account-app/account-app.html'
    link: (scope, elem, attrs) -> as scope, ->
      @changeEmail = ->
        promise = modals.launch 'change-email'
        promise.then (res) ->
          
          #this reload will force the user to the login page; upon actually logging in
          #they should end up back at the account page
          if res == true
            $window.location.reload()

  }

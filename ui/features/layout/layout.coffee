angular.module 'gps.common.layout', [
  'ui.router'
  'cgBusy'
  'gps.common.models'
  'ngAnimate'
  'mgcrea.ngStrap.modal'
  'angulartics'
  'angulartics.google.analytics'
]

# a tag used for comparison in API responses; if the response
# contains the "x-gps-deployed-at" header, and does not match
# this value, the page will be refreshed
.value 'appDeployedAt', 0

.value 'cgBusyDefaults', {
  message:'Saving...'
  templateUrl: '/features/layout/templates/saving-overlay.html'
}

.constant 'appAnalyticsEnabled', true

#register http inteceptors here, because order matters
.config ($httpProvider) ->
  $httpProvider.interceptors.push 'deployTagHeaderInterceptor'
  $httpProvider.interceptors.push 'maintenanceModeInterceptor'
  $httpProvider.interceptors.push 'sessionExpirationInterceptor'
  $httpProvider.interceptors.push 'apiErrorLoggerInterceptor'

#configure analytics provider
.config ($analyticsProvider, appAnalyticsEnabled) ->
  $analyticsProvider.withAutoBase true
  
  if !appAnalyticsEnabled
    $analyticsProvider.developerMode true

.config (modalsProvider) ->

  #generic confirm modal, returns true/false
  modalsProvider.defineModal 'confirm', {
    templateUrl: '/features/layout/templates/confirm-modal.html'
    backdrop: 'static'
    animation: 'am-fade'
  }
  
  modalsProvider.defineModal 'alert', {
    templateUrl: '/features/layout/templates/alert-modal.html'
    backdrop: 'static'
    animation: 'am-fade'
  }

  #modal tutorial used for explaining or introducing major features
  modalsProvider.defineModal 'tutorial', {
    templateUrl: '/features/layout/templates/tutorial-modal.html'
    backdrop: 'static'
    animation: 'am-slide-top'
  }

# integrate exception handling with track.js
.config(["$provide", (($provide) ->
  $provide.decorator("$exceptionHandler", ["$delegate", "$window", (($delegate, $window) ->
    return (exception, cause) ->
      if $window.trackJs
        $window.trackJs.track exception

      $delegate exception, cause
  )])
)])

# some things need to be configured at runtime...
.run ($rootScope, $analytics, appUserService) ->
  
  #initialize the help zendesk help button
  helpButton = new GpsHelpButton($('.custom-zd-help-button'))
  
  #identify user with analytics tracking
  $analytics.setUsername appUserService.getData().id

# Don't remove this... the html shell rendered by symfony uses "LayoutController"
# in order to allow the app to control things like page title...
#
# Can probably remove reference to "appUser", but deal w/ it later
.controller 'LayoutController', ($scope, layout, appUserService) -> as $scope, ->
  @layout = layout
  @appUser = appUserService.getData()

  @$on 'gps.appUser', (e, user) =>
    @appUser = appUserService.getData()

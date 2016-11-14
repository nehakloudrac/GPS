#utility to allow easier use of coffeescript "@" this syntax
window.as = (context, fn) -> fn.call(context)

angular.module 'gps.admin', [
  'gps.common.layout'
  'gps.common.models'
  'gps.common.candidate-profile'
  'ngSanitize'
  'ui.select'
  'mgcrea.ngStrap.popover'
  'mgcrea.ngStrap.datepicker'
]
.config (modalsProvider) ->
  modalsProvider.defineModal 'candidate-profile', {
    templateUrl: '/apps/admin/directives/profile-modal/profile-modal.html'
    backdrop: 'static'
    animation: 'am-fade'
  }
  
.run (layout, $rootScope) ->
  layout.setTitle 'GPS Admin'

  #scroll to top when routing state changes
  $rootScope.$on '$stateChangeSuccess', () ->
    $('body').eq(0).animate({scrollTop: 0}, 'fast')

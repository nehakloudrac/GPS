#utility to allow easier use of coffeescript "@" this syntax
window.as = (context, fn) -> fn.call(context)

###
# Defines app for the page, along with some config overrides for dependencies.
#
# Most of the app config is in the config/ directory, as some
# values are particularly large.
###
angular.module 'gps.profile', [
  'ngSanitize'
  'ui.select'
  'ui.bootstrap-slider'
  'ng-sortable'
  'gps.common.layout'
  'gps.common.candidate-profile'
  'ui.mask'   #TODO: is this not used anymore?  don't think it is...
  'mgcrea.ngStrap.helpers.parseOptions'
  'mgcrea.ngStrap.tooltip'
  'mgcrea.ngStrap.popover'
  'mgcrea.ngStrap.typeahead'
  'mgcrea.ngStrap.datepicker'
  'mgcrea.ngStrap.select'
  'mgcrea.ngStrap.affix'
]

.config ($datepickerProvider) ->
  angular.extend $datepickerProvider.defaults,
    dateType: 'unix'
    iconLeft: 'fa fa-arrow-left'
    iconRight: 'fa fa-arrow-right'
    useNative: true
    container: 'body'
    placement: 'auto'

.run (layout, $rootScope, appCandidateProfile) ->
  layout.setTitle "GPS Candidate Profile"
  
  #redirect to intro if they have not completed it.
  profile = appCandidateProfile.getData()
  if !profile.profileStatus?.introCompleted? || profile.profileStatus.introCompleted != true
    layout.go 'intro'
  

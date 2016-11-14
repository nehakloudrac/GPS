#utility to allow easier use of coffeescript "@" this syntax
window.as = (context, fn) -> fn.call(context)

angular.module 'gps.account', [
  'ngSanitize'
  'ui.select'
  'gps.common.layout'
  'mgcrea.ngStrap.tooltip'
]

.config ($stateProvider, $urlRouterProvider) ->
  $stateProvider.state 'account',
    url: '^'
    template: "<account-app></account-app>"
  $urlRouterProvider.otherwise ->
    return '/'

.config (modalsProvider) ->
  modalsProvider.defineModal 'change-email', {
    templateUrl: '/apps/account/directives/email-modal/modal.html'
    backdrop: 'static'
    animation: 'am-fade'
  }

.constant 'prefsConfig', [
    key: "allowGravatar"
    type: "bool"
    label: "Gravatar Profile Image"
    desc: "If you have not uploaded a profile image, allow GPS to use your Gravatar"
  ,
    key: "allowProductFeatureEmails"
    type: "bool"
    label: "Announcements"
    desc: "Receive messages about new features at GPS"
  ,
  #   key: "allowProfileInterestEmails"
  #   type: "bool"
  #   label: "Profile Interest"
  #   desc: "Be notified when your candidate profile matches an employer search"
  # ,
    key: "allowProfileHealthEmails"
    type: "bool"
    label: "Profile Status"
    desc: "Receive messages from GPS about your profile’s status"
  ,
    key: "allowSearchActivityEmails"
    type: "bool"
    label: "Search Activity"
    desc: "Receive updates about how often your profile is being searched."
]

.run (layout) ->
  layout.setTitle "Account"
  layout.setBreadcrumb [
      title: 'Account'
  ]
  
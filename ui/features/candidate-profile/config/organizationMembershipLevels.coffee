angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'organizationMembershipLevels', [
      value: 1
      label: 'General member'
    ,
      value: 2
      label: 'Senior member'
    ,
      value: 3
      label: 'Officer'
    ,
      value: 4
      label: 'Board member'
    ,
      value: 5
      label: 'President or Executive Director'
    ,
      value: 6
      label: 'Founder'
  ]

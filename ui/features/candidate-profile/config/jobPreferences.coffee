angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'jobPreferences', [
      key: 'workWithTeam'
      lowLabel: 'Work alone'
      highLabel: 'Work with a team'
    ,
      key: 'workInField'
      lowLabel: 'Work in an office'
      highLabel: 'Work in the field'
    ,
      key: 'travel'
      lowLabel: 'Stay local'
      highLabel: 'Travel for work'
    ,
      key: 'multiTask'
      lowLabel: 'Work on one task at a time'
      highLabel: 'Work on multiple tasks at the same time'
    ,
      key: 'workWithCustomers'
      lowLabel: 'Work behind the scenes'
      highLabel: 'Interface with customers'
    ,
      key: 'fewerRules'
      lowLabel: 'Work with clearly defined rules'
      highLabel: 'Work with fewer rules'
    ,
      key: 'measureAgainstOthers'
      lowLabel: 'Measure my progress against past performance'
      highLabel: 'Measure my progress against the performance of others'
    ,
      key: 'takeRisks'
      lowLabel: 'Be conservative'
      highLabel: 'Take risks'
    ,
      key: 'socializeOneToOne'
      lowLabel: 'Socialize in group situations'
      highLabel: 'Socialize with people one-on-one'
    ,
      key: 'newEnvironments'
      lowLabel: 'Work in a familiar environment'
      highLabel: 'Work in new environments'
    ,
      key: 'compete'
      lowLabel: 'Work in a collaborative environment'
      highLabel: 'Work in a competitive environment'
    ,
      key: 'commission'
      lowLabel: 'Be compensated 100% by salary'
      highLabel: 'Be compensated 100% by commission'
    ,
      key: 'multicultural'
      lowLabel: 'Work in monocultural environment'
      highLabel: 'Work in multicultural environment'
  ]
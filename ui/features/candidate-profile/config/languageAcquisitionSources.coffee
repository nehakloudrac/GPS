angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'languageAcquisitionSources', [
      key: 'self_study'
      label: 'Self'
    ,
      key: 'training'
      label: 'Immersion program'
    ,
      key: 'school'
      label: 'Formal classroom'
    ,
      key: 'community'
      label: 'Community or heritage'
  ]

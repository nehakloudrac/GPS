angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'studentLevels', [
      key: 'k12'
      label: 'K-12'
    ,
      key: 'university'
      label: 'University'
    ,
      key: 'professional'
      label: 'Professional'
  ]

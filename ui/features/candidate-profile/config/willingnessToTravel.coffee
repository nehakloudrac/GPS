angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'willingnessToTravel', [
      key: 'occaisionally'
      label: 'Occasionally'
    ,
      key: 'up_to_25'
      label: 'Yes, up to 25%'
    ,
      key: 'up_to_50'
      label: 'Yes, up to 50%'
    ,
      key: 'over_50'
      label: 'Yes, more than 50%'
    ,
      key: 'no'
      label: 'No'
  ]

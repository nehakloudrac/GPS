angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'countryPurposes', [
      key: 'work'
      label: 'Work'
    ,
      key: 'study'
      label: 'Study'
    ,
      key: 'volunteer'
      label: 'Volunteer'
    ,
      key: 'military'
      label: 'Military'
    ,
      key: 'dependant'
      label: 'Live with family or friends'
    ,
      key: 'teaching'
      label: 'Teach'
    ,
      key: 'research'
      label: 'Research'
  ]

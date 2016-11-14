angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'genderOptions', [
      key: 'male'
      label: "Male"
    ,
      key: 'female'
      label: "Female"
    ,
      key: 'other'
      label: 'Other'
    ,
      key: 'decline'
      label: "Decline to respond"
  ]

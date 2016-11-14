angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'universityDegrees', [
      key: 'associates'
      label: "Associate's"
    ,
      key: 'bachelors'
      label: "Bachelor's"
    ,
      key: 'masters'
      label: "Master's"
    ,
      key: 'mba'
      label: "MBA"
    ,
      key: 'jd'
      label: "J.D."
    ,
      key: 'phd'
      label: "Ph.D."
    ,
      key: 'md'
      label: "M.D."
    ,
      key: 'edd'
      label: "Ed.D."
    ,
      key: 'none'
      label: 'No degree earned'
  ]

  configProvider.set 'concentrationTypes', [
      key: 'major'
      label: 'Major'
    ,
      key: 'minor'
      label: 'Minor'
  ]

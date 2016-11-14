angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'userJobStatusOptions', [
      key: 'unemployed'
      label: "Unemployed"
    ,
      key: 'looking'
      label: "Actively looking"
    ,
      key: 'open'
      label: "Keeping my options open"
    ,
      key: 'satisfied'
      label: "Open to the right opportunity"
    ,
      key: 'happy'
      label: "Love my job!"
  ]

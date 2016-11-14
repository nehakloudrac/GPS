angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'usSecurityClearances', [
      key: "no"
      label: "No"
    ,
      key: "confidential"
      label: "Confidential"
    ,
      key: "secret"
      label: "Secret"
    ,
      key: "top_secret"
      label: "Top Secret"
    ,
      key: "polygraph"
      label: "Polygraph Clearance"
    ,
      key: "yes"
      label: "Yes (decline to specify)"
  ]

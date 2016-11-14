angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'usWorkAuthorizations', [
      key: "citizen"
      label: "Citizen"
    ,
      key: "green_card"
      label: "Green Card"
    ,
      key: "h1b"
      label: "H1-B Visa"
    ,
      key: "opt",
      label: "OPT"
    ,
      key: "tn"
      label: "TN Permit"
  ]

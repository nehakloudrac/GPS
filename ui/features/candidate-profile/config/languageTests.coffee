angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'languageTests', [
      label: "DLPT"
      test: "dlpt"
      scale: "ilr"
    ,
      label: "ACTFL/AAPPL"
      test: "aappl"
      scale: "actfl"
    ,
      label: "ILR-based OPI"
      test: "ilr-opi"
      scale: "ilr"
    ,
      label: "TELC"
      test: "telc"
      scale: "cefr"
    ,
      label: "STAMP"
      test: "stamp"
      scale: "actfl"
    ,
      label: "TORFL (ТРКИ)"
      test: "torfl"
      scale: "other"
    ,
      label: "TOCFL"
      test: "tocfl"
      scale: "other"
    ,
      label: "Other"
      test: 'custom'
      scale: "other"
  ]

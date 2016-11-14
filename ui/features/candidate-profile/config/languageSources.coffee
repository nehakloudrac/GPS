angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'languageSources', [
      key: "formal_study"
      label: "Formal study in school"
    ,
      key: "in_country_study"
      label: "Study in country"
    ,
      key: "self_study"
      label: "Self study"
    ,
      key: "training_program"
      label: "Training program"
    ,
      key: "community"
      label: "At home with family or in community"
  ]

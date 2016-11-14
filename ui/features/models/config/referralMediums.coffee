angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'referralMediums', [
      key: "search"
      label: "Websearch"
    ,
      key: "linkedin"
      label: "LinkedIn"
    ,
      key: "facebook"
      label: "Facebook"
    ,
      key: "twitter"
      label: "Twitter"
    ,
      key: "career_service"
      label: "Career center"
    ,
      key: "program_lang"
      label: "Language program"
    ,
      key: "program_study_abroad"
      label: "Study abroad program"
    ,
      key: "program_volunteer"
      label: "Volunteer program"
    ,
      key: "other"
      label: "Other"
  ]
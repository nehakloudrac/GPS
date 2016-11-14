angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'timelineTypes', [
      type: 'job'
      label: 'Job'
      iconClass: 'g g-laptop'
      description: "Describe relevant job positions that you have held."
    ,
      type: 'volunteer'
      label: 'Volunteer'
      iconClass: 'g g-hand'
      description: "Describe an extended and significant volunteer experience."
    ,
      type: 'military'
      label: 'Military'
      iconClass: 'fa-fighter-jet'
      description: "List a notable military assignment or deployment."
    ,
      type: 'research'
      label: 'Research'
      iconClass: 'fa-flask'
      description: "Describe an extended and significant research project."
    ,
      type: 'university'
      label: 'University'
      iconClass: 'fa-university'
      description: "List any degrees earned or time spent in a university or college."
    ,
      type: 'study_abroad'
      label: "Study Abroad"
      iconClass: 'fa-globe'
      description: "Tell us about your study abroad experiences."
    ,
      type: 'language_acquisition'
      label: "Other Language Training"
      iconClass: 'fa-comments'
      description: "Describe any significant time you have spent learning a foreign language."
  ]

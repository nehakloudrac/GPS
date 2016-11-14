angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'softSkills', [
      label: "Adaptability"
      key: 'adaptability'
    ,
      label: "Communication"
      key: 'communication'
    ,
      label: "Creativity"
      key: 'creativity'
    ,
      label: "Critical thinking"
      key: 'critical_thinking'
    ,
      label: "Decision-making"
      key: 'decision_making'
    ,
      label: "Intellectual curiosity"
      key: 'curiosity'
    ,
      label: "Leadership"
      key: 'leadership'
    ,
      label: "Problem-solving"
      key: 'problem_solving'
    ,
      label: "Resiliency"
      key: 'resiliency'
    ,
      label: "Self-motivation"
      key: 'motivation'
    ,
      label: "Teamwork"
      key: 'teamwork'
    ,
      label: "Tolerance for ambiguity"
      key: 'tolerate_ambiguity'
  ]

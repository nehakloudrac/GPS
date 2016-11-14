angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'ideals', [
      label: "Accountability"
      key: 'accountability'
    ,
      label: "Creativity/Innovation"
      key: 'creativity'
    ,
      label: "Customer Satisfaction"
      key: 'customer_satisfaction'
    ,
      label: "Diversity"
      key: 'diversity'
    ,
      label: "Integrity"
      key: 'integrity'
    ,
      label: "Meritocracy"
      key: 'meritocracy'
    ,
      label: "Professional growth"
      key: 'professional_growth'
    ,
      label: "Productivity"
      key: 'productivity'
    ,
      label: "Quality"
      key: 'quality'
    ,
      label: "Work/life balance"
      key: 'family'
  ]


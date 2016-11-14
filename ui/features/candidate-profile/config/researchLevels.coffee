angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'researchLevels', [
      key: 'undergrad'
      label: 'Undergraduate'
    ,
      key: 'postgrad',
      label: 'Post Graduate'
    ,
      key: 'grad_student'
      label: 'Graduate Student'
    ,
      key: 'postdoc_fellow'
      label: 'Postdoctoral Fellow'
    ,
      key: 'professional'
      label: 'Professional'
  ]

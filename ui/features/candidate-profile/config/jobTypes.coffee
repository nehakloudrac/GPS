angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'jobTypes', [
      key: 'full_time'
      label: 'Full-time'
    ,
      key: 'part_time'
      label: 'Part-time'
    ,
      key: 'project'
      label: 'Project-based work'
    ,
      key: 'internship'
      label: 'Internship'
  ]

angular.module 'gps.common.candidate-profile'
.config (configProvider, academicSubjects) ->
  configProvider.set 'academicSubjects', academicSubjects

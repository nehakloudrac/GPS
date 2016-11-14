angular.module 'gps.common.models'
.config (configProvider, institutionIndustries) ->
  configProvider.set 'institutionIndustries', institutionIndustries
  
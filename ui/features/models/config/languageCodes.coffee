angular.module 'gps.common.models'
#TODO: this runtime sorting is a terrible idea, it's a short term to solution...
#need to generate the properly sorted lang list differently instead of concatenating
.config (configProvider, languageCodes) ->
  codes = _.sortBy languageCodes, 'label'
  configProvider.set 'languageCodes', codes

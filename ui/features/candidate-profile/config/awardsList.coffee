#TODO: replace this w/ autosuggest
angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'awardsListNames', [
    "Benjamin A. Gilman International Scholarship (Gilman)"
    "Boren Award"
    "Critical Language Enhancement Award (CLEA)"
    "Critical Language Scholarship (CLS)"
    "Foreign Language and Area Studies Fellowship (FLAS)"
    "Fulbright English Teaching Assistantship (ETA)"
    "Fulbright U.S. Scholar Program"
    "Fulbright U.S. Student Program"
    "Language Flagship Fellowship"
    "National Security Language Initiative for Youth Scholarship (NSLI-Y)"
    "Overseas Research Center Fellowship (ORC)"
    "Title VIII Award (Research and Training on Eastern Europe and Eurasia)"
    "Wilson Fellowship"
  ]

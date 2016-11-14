# Thank you, internet: https://gist.github.com/Keeguon/2310008

angular.module 'gps.common.models'
.config (configProvider, countryCodes) ->
  
  configProvider.set 'countryCodes', countryCodes
  
  items = _.cloneDeep countryCodes
  items.unshift { code: 'xx', name: "Anywhere" }
  
  configProvider.set 'locationsAbroadChoices', items


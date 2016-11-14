angular.module 'gps.common.models'

# the extra value here is because on the short form there
# is an extra choice... it should be refactored at some point
.config (configProvider) ->
  
  positionLevels = [
      key:'president_ceo'
      label: "President, CEO, Chairman, Partner"
    ,
      key:'owner_founder'
      label: "Owner, Founder"
    ,
      key:'principal'
      label: "Principal, Managing Director, General Manager"
    ,
      key:'cxo'
      label: "CXO"
    ,
      key:'vp'
      label: "VP, SVP, EVP, etc."
    ,
      key:'director'
      label: "Director"
    ,
      key:'manager'
      label: "Manager/Senior Mgr"
    ,
      key:'advanced'
      label: "Advanced"
    ,
      key:'entry'
      label: "Entry"
    ,
      key:'intern'
      label: "Intern"
  ]

  items = _.cloneDeep positionLevels
  items.unshift { key: 'none', label: 'None' }
  
  configProvider.set 'positionLevels', positionLevels
  configProvider.set 'positionLevelChoices', items


angular.module 'gps.common.models'
.config (configProvider) ->
  usTerritories = [
      key: "AL"
      label: "Alabama"
    ,
      key: "AK"
      label: "Alaska"
    ,
      key: "AS"
      label: "American Samoa"
    ,
      key: "AZ"
      label: "Arizona"
    ,
      key: "AR"
      label: "Arkansas"
    ,
      key: "CA"
      label: "California"
    ,
      key: "CO"
      label: "Colorado"
    ,
      key: "CT"
      label: "Connecticut"
    ,
      key: "DE"
      label: "Delaware"
    ,
      key: "DC"
      label: "District Of Columbia"
    ,
      key: "FL"
      label: "Florida"
    ,
      key: "GA"
      label: "Georgia"
    ,
      key: "HI"
      label: "Hawaii"
    ,
      key: "ID"
      label: "Idaho"
    ,
      key: "IL"
      label: "Illinois"
    ,
      key: "IN"
      label: "Indiana"
    ,
      key: "IA"
      label: "Iowa"
    ,
      key: "KS"
      label: "Kansas"
    ,
      key: "KY"
      label: "Kentucky"
    ,
      key: "LA"
      label: "Louisiana"
    ,
      key: "ME"
      label: "Maine"
    ,
      key: "MD"
      label: "Maryland"
    ,
      key: "MA"
      label: "Massachusetts"
    ,
      key: "MI"
      label: "Michigan"
    ,
      key: "MN"
      label: "Minnesota"
    ,
      key: "MS"
      label: "Mississippi"
    ,
      key: "MO"
      label: "Missouri"
    ,
      key: "MT"
      label: "Montana"
    ,
      key: "NE"
      label: "Nebraska"
    ,
      key: "NV"
      label: "Nevada"
    ,
      key: "NH"
      label: "New Hampshire"
    ,
      key: "NJ"
      label: "New Jersey"
    ,
      key: "NM"
      label: "New Mexico"
    ,
      key: "NY"
      label: "New York"
    ,
      key: "NC"
      label: "North Carolina"
    ,
      key: "ND"
      label: "North Dakota"
    ,
      key: "OH"
      label: "Ohio"
    ,
      key: "OK"
      label: "Oklahoma"
    ,
      key: "OR"
      label: "Oregon"
    ,
      key: "PA"
      label: "Pennsylvania"
    ,
      key: "PR"
      label: "Puerto Rico"
    ,
      key: "RI"
      label: "Rhode Island"
    ,
      key: "SC"
      label: "South Carolina"
    ,
      key: "SD"
      label: "South Dakota"
    ,
      key: "TN"
      label: "Tennessee"
    ,
      key: "TX"
      label: "Texas"
    ,
      key: "UT"
      label: "Utah"
    ,
      key: "VT"
      label: "Vermont"
    ,
      key: "VI"
      label: "Virgin Islands"
    ,
      key: "VA"
      label: "Virginia"
    ,
      key: "WA"
      label: "Washington"
    ,
      key: "WV"
      label: "West Virginia"
    ,
      key: "WI"
      label: "Wisconsin"
    ,
      key: "WY"
      label: "Wyoming"
  ]

  items = _.cloneDeep usTerritories
  items.unshift { key: 'xxx', label: "Anywhere" }
  
  configProvider.set 'usTerritories', usTerritories
  configProvider.set 'locationsUSAChoices', items


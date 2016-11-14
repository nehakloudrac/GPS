angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'militaryGeographicSpecialties', [
    "USAFRICOM"
    "USCENTCOM"
    "USEUCOM"
    "USNORTHCOM"
    "USPACOM"
    "USSOUTHCOM"
  ]

  configProvider.set 'militaryRankTypes', [
      key: 'enlisted'
      label: "Enlisted"
    ,
      key: 'officer'
      label: "Officer"
  ]

  configProvider.set 'militaryServices', [
      key: 'us_navy'
      label: 'US Navy'
    ,
      key: 'us_army'
      label: 'US Army'
    ,
      key: 'us_marines'
      label: 'US Marines'
    ,
      key: 'us_coast_guard'
      label: 'US Coast Guard'
    ,
      key: 'us_air_force'
      label: 'US Air Force'
  ]

  configProvider.set 'militaryRanks', {
    us_navy: configProvider.get 'ranksUsNavy'
    us_army: configProvider.get 'ranksUsArmy'
    us_marines: configProvider.get 'ranksUsMarines'
    us_coast_guard: configProvider.get 'ranksUsCoastGuard'
    us_air_force: configProvider.get 'ranksUsAirForce'
  }

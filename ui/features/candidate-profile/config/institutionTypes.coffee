angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'institutionTypes', [
      key: 'company_private'
      label: 'Private company'
    ,
      key: 'company_public'
      label: 'Public company'
    ,
    #   key: 'gov_enterprise'
    #   label: 'Government-owned enterprise'
    # ,
      key: 'non_profit'
      label: 'Nonprofit'
    ,
      key: 'edu'
      label: "Educational institution"
    ,
      key: 'foundation'
      label: 'Foundation'
    ,
      key: 'org_intl'
      label: 'International organization'
    ,
      key: 'gov_fed'
      label: 'Federal government'
    ,
      key: 'gov_local'
      label: 'State or local government'
    ,
      key: 'other'
      label: 'Other'
  ]

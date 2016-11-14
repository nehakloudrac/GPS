angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'diversityFlags', [
      key: "eth_american_indian"
      label: "American Indian"
    ,
      key: "eth_african_american"
      label: "African American"
    ,
      key: "eth_asn_pacific"
      label: "Asian American or Pacific Islander"
    ,
      key: "eth_hispanic"
      label: "Hispanic"
    ,
      key: "lgbtq"
      label: "LGBTQ"
    ,
      key: "disabled"
      label: "Disability"
    ,
      key: "veteran"
      label: "Veteran"
  ]

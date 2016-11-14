class LocationFormatter
  constructor: (config, @labeler) ->
    @countryCodes = config.get 'countryCodes'
  
  formatLocation: (address = null) ->
    if address?.city? && address?.countryCode?
      if address.territory?
        return "#{address.city}, #{address.territory} <br /> #{@labeler.getLabel(@countryCodes, address.countryCode, 'code','name')}"
      return "#{address.city} <br /> #{@labeler.getLabel(@countryCodes, address.countryCode, 'code','name')}"
    
    return ''

  formatLocationOneLine: (address = null) ->
    if address?.city? && address?.countryCode?
      if address.territory?
        return "#{address.city}, #{address.territory} #{@labeler.getLabel(@countryCodes, address.countryCode, 'code','name')}"
      return "#{address.city}, #{@labeler.getLabel(@countryCodes, address.countryCode, 'code','name')}"
    
    return ''

angular.module 'gps.common.models'
.service 'locationFormatter', ['config','labeler', LocationFormatter]

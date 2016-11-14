#global lodash mixins... wasn't a clear proper place to put them, so I chose here
_.mixin {
  filterByValues: (collection, property, values) ->
    _.filter collection, (item) ->
      _.contains values, item[property]
}

angular.module 'gps.common.models', ['gps.common.models.config']
.constant 'gravatarRoot', "//www.gravatar.com"
.constant 'profileImagesRoot', "/files/profile-images"


angular.module 'gps.common.models.deps', [
  'ngFileUpload'
  'mgcrea.ngStrap.popover'
  'ngTagsInput'
]

angular.module 'gps.common.models.config', ['gps.common.models.deps', 'gps.config']
.config (tagsInputConfigProvider) ->
  #when using tags-input inside a field-widget... causes some problems, so
  #need to do this to force some attributes to be re-evaluated after after
  #the wrapping field-widget initializes
  tagsInputConfigProvider.setActiveInterpolation 'tagsInput', {
    placeholder: true
  }
  tagsInputConfigProvider.setActiveInterpolation 'autoComplete', {
    maxResultsToShow: true
    loadOnFocus: true
    loadOnEmpty: true
  }
  
  
#base config module dependency: will be used in the main
#page template to inject config from the server
#before the bootstrap is initiated
angular.module 'gps.config', ['gps.config.deps']

#a simple key/val store for random things... mostly config for
#drop down menus
angular.module 'gps.config.deps', []
.provider 'config', ->
  conf = {}
  
  @set = (key, val) ->
    conf[key] = val
  
  @get = (key, val) ->
    if !conf[key]?
      throw new Error("Requested unknown config [#{key}]")
    return conf[key]
  
  @$get = ->
    return {
      get: (key, def = null) ->
        return if conf[key]? then conf[key] else def
      
      set: (key, val) ->
        conf[key] = val
      
      getMap: (keys) ->
        map = {}
        for key in keys
          map[key] = @get key
        
        return map
    }
  
  #QUESTION: why...?
  return @

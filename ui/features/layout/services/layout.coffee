angular.module 'gps.common.layout'
.provider 'layout', ->
  
  conf = {}
  
  @set = (key, val) ->
    conf[key] = val
  
  @get = (key, def = null) ->
    return if conf[key]? then conf[key] else def
  
  @$get = ($document, appName, appRoots, $state) ->
    breadcrumb = false
    prevState =
      name: null
      params: null

    return {
      eq: $state.is
      includes: $state.includes
      go: $state.go
      getState: -> $state.current.name
      back: -> $state.go prevState.name, prevState.params

      setBreadcrumb: (b) ->
        breadcrumb = b

      getBreadcrumb: ->
        breadcrumb

      #generate a link to a ui app - if the path is in the current app, the links are relative,
      #otherwise they are absolute
      uiPath: (app, path) ->
        if app == appName
          return "##{path}"

        return "#{appRoots[app]}##{path}"
      
      #set the page title
      setTitle: (t) ->
        $document.prop 'title', t
      
      setPrevState: (name, params) ->
        prevState.name = name
        prevState.params = params
      
      set: (key, val) ->
        conf[key] = val

      get: (key, defaultValue = null) ->
        return if conf[key]? then conf[key] else defaultValue
    }
    
  #QUESTION: why...?
  return @
.run ($rootScope, layout) ->
  
  $rootScope.$on '$stateChangeSuccess', (e, to, toParams, from, fromParams) ->
    layout.setPrevState from, fromParams

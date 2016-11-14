angular.module 'gps.common.layout'
.provider 'modals', ->

  modalDefinitions = {}

  #allow registering named instances of modals with various config
  @defineModal = (name, ops) -> modalDefinitions[name] = ops

  @$get = ($modal, $rootScope, $q) ->

    #launch a modal requires a template, and optional scope variables
    #modal scopes are isolate, and will only contain what is directly passed
    #third param is any other options that would be specified via the angularstrap $modals service
    launchModalInstance = (name, scopeData = {}, instanceOverrides = {}) ->
      modalVars = modalDefinitions[name]
      if !modalVars
        throw new Error 'Attempted to launch undefined modal ['+name+']'

      deferred = $q.defer()

      #create isolated modal scope
      modalScope = $rootScope.$new(true)
      for key, val of scopeData
        modalScope[key] = val

      #inject custom scope method to close and resolve a promise
      #with a return value
      modalScope.$close = (result) ->
        deferred.resolve result
        modalInstance.hide()
        modalScope.$destroy()

      #assemble final modal vars for the angularstrap $modals service
      modalVars.scope = modalScope
      modalVars.show = true
      for key, val of instanceOverrides
        modalVars[key] = val
      
      #instantiate/launch modal instance with the underlying AngularStrap $modal service
      modalInstance = $modal modalVars

      #launching a modal returns a promise that resolves with whatever data is passed from "$close"
      return deferred.promise

    return {
      launch: launchModalInstance
    }

  #QUESTION: WAT?  Why do I have to return here?  The docs suggest that you only define a function for a provider
  return @
  
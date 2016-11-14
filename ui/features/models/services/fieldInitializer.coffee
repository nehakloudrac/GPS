###
# Initializes objects for fields to be used while editing.  A common pattern used
# in many places throughout the UI.  Each field gets an object with a place holder
# for the value, a promise, and a method to return the structure that would be
# submitted via an API call
# 
# TODO: refactor this into the ProfileFormField eventually
###
class FieldInitializer
  constructor: ->
  
  initializeField: (obj, path, extension = null) ->
    buildField = ->
      field =
        path: path
        value: _.cloneDeep _.get obj, path, null
        promise: null
        saveValueTransformer: (val) -> return val
        saveDataTransformer: (obj) -> return obj
        getSaveData: ->
          data = {}
          _.set data, path, @saveValueTransformer(@value)
          return data
      extension(field) if extension
      return field

    field = buildField()
    resetter = ->
      field = buildField()
      field.reset = resetter
    field.reset = resetter

    return field

  initializeFields: (obj, paths) ->
    map = {}
    for path in paths
      do (path) =>
        map[path] = @initializeField obj, path
    return map

angular.module 'gps.common.models'
.service 'fieldInitializer', FieldInitializer
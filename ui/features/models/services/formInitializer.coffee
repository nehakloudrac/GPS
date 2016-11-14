###
# Definition of a field in a form
#
# TODO: formalize and implement to clean up
###
class ProfileFormField
  constructor: ->
    null

###
# Definition of a form that modifies a particular object
#
# TODO: formalize and implement to clean up
###
class ProfileForm
  constructor: (@obj, @baseUrl, @onSuccess, @onError, fieldNames) ->
    @fields = {}

###
# Initializes entire forms with fields, to cut down significantly
# on required directive code for editing objects
#
# TODO: refactor to use ProfileForm and ProfileFormField, and
# maybe rename this to ProfileFormFactory
###
class ProfileFormInitializer
  constructor: (@profile, @fieldInitializer) ->
  
  initializeForm: (obj, baseUrl, onSuccess, onError, fields) ->
    form = {}
    form.fields = @fieldInitializer.initializeFields obj, fields
    form.saveDataTransformer = (obj) -> return obj
    form.saveUrlTransformer = (url, obj, data) -> return url
    form.saveField = (name) =>
      console.error "No field by name:#{name}" if !form.fields[name]?
      
      data = form.fields[name].saveDataTransformer(form.fields[name].getSaveData())
      data = form.saveDataTransformer(data)

      if obj.hash?
        url = baseUrl+"/#{obj.hash}"
        method = "put"
      else
        url = baseUrl
        method = "post"
      
      url = form.saveUrlTransformer(url, obj, data)

      form.fields[name].promise = @profile[method](url, data)
      .success onSuccess
      .error onError
    
    return form


angular.module 'gps.common.models'
.service 'profileFormInitializer', ['appCandidateProfile', 'fieldInitializer', ProfileFormInitializer]
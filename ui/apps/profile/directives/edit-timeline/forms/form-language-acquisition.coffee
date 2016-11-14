angular.module 'gps.profile'
.directive 'formLanguageAcquisition', ->
  return {
    restrict: 'E'
    scope: true
    templateUrl: '/apps/profile/directives/edit-timeline/forms/form-language-acquisition.html'
    link: (scope, elem, attrs) -> as scope, ->
      
      #overloaded the languageRef field, which is really not
      #the best way to do things
      @onSelectLanguage = =>
        @form.fields['languageRefs'].value = [@form.fields['language'].value]
        @form.saveField('languageRefs')

      @showInstitution = =>
        return _.includes ['training','school'], @event.source
      
      fields = ['language','source','hoursPerWeek']
      
      config = ['languageAcquisitionSources']
      
      updateLangValue = =>
        @form.fields['language'].value = if @event.languageRefs?[0]? then @event.languageRefs[0] else null

      init = =>
        @init(fields, config)
        updateLangValue()
            
      @$watchCollection 'event.languageRefs', updateLangValue
      
      @$on 'event.init', init
      
      init()
  }

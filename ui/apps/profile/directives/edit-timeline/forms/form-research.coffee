angular.module 'gps.profile'
.directive 'formResearch', ->
  return {
    restrict: 'E'
    scope: true
    templateUrl: '/apps/profile/directives/edit-timeline/forms/form-research.html'
    link: (scope, elem, attrs) -> as scope, ->

      fields = [
        'sponsoringProgram'
        'level'
        'hoursPerWeek'
        'subject'
        'hostingInstitution.name'
        'hostingInstitution.address'
      ]
      
      config = ['academicSubjects','researchLevels']
      
      @$on 'event.init', => @init(fields, config)
      
      @init(fields, config)
  }

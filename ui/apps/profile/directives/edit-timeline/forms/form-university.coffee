angular.module 'gps.profile'
.directive 'formUniversity', ->
  return {
    restrict: 'E'
    scope: true
    templateUrl: '/apps/profile/directives/edit-timeline/forms/form-university.html'
    link: (scope, elem, attrs) -> as scope, ->

      fields = ['gpa','degrees','concentrations']
      config = ['academicSubjects', 'universityDegrees', 'concentrationTypes']
      
      @$on 'event.init', => @init(fields, config)
      
      @init(fields, config)
  }

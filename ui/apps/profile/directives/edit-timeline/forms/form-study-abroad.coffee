angular.module 'gps.profile'
.directive 'formStudyAbroad', ->
  return {
    restrict: 'E'
    scope: true
    templateUrl: '/apps/profile/directives/edit-timeline/forms/form-study-abroad.html'
    link: (scope, elem, attrs) -> as scope, ->

      fields = ['programName', 'weeklyActivityHours', 'classTimePercentLocalLang', 'hostingInstitution.name']
      
      config = []
      
      @$on 'event.init', => @init(fields, config)
      
      @init(fields, config)
  }

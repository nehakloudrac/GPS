angular.module 'gps.profile'
.directive 'formJob', ->
  return {
    restrict: 'E'
    scope: true
    templateUrl: '/apps/profile/directives/edit-timeline/forms/form-job.html'
    link: (scope, elem, attrs) -> as scope, ->

      fields = ['title','department','positionLevel','salary','hourlyRate','status']
      
      config = ['jobTypes', 'positionLevels']
      
      @$on 'event.init', => @init(fields, config)
      
      @init(fields, config)
  }

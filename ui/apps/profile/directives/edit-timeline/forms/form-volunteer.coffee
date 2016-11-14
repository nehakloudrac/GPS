angular.module 'gps.profile'
.directive 'formVolunteer', ->
  return {
    restrict: 'E'
    scope: true
    templateUrl: '/apps/profile/directives/edit-timeline/forms/form-volunteer.html'
    link: (scope, elem, attrs) -> as scope, ->

      init = =>
        fields = ['status']
        config = ['jobTypes', 'volunteerOrgNames']
      
        @init(fields, config)
        
        @conf.statusTypes = [
            key: 'full_time'
            label: "Full-time"
          ,
            key: 'part_time'
            label: 'Part-time'
        ]

      @$on 'event.init', init
      
      init()
  }

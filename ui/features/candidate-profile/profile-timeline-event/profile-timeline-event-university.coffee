angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEventUniversity', (config) ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event-university.html'
    scope: true
    require: '^profileTimelineEvent'
    link: (scope, elem, attrs, parentScope) -> as scope, ->
      @universityDegrees = config.get 'universityDegrees'
      @academicSubjects = config.get 'academicSubjects'
      @concentrationTypes = config.get 'concentrationTypes'
  }

angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEventResearch', (config) ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event-research.html'
    scope: true
    require: '^profileTimelineEvent'
    link: (scope, elem, attrs, parentScope) -> as scope, ->
      @academicSubjects = config.get 'academicSubjects'
  }

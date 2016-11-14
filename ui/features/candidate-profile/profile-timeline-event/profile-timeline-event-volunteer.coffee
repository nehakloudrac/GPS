angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEventVolunteer', ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event-volunteer.html'
    scope: true
    require: '^profileTimelineEvent'
    link: (scope, elem, attrs, parentScope) -> as scope, ->
      @foo = 'bar'
  }

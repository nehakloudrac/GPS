angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEventJob', ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event-job.html'
    scope: true
    require: '^profileTimelineEvent'
  }

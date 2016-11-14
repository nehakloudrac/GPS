angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEventStudyAbroad', ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event-study-abroad.html'
    scope: true
    require: '^profileTimelineEvent'
  }

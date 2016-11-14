angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEventMilitary', (config) ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event-military.html'
    scope: true
    require: '^profileTimelineEvent'
    link: (scope, elem, attrs, parentScope) -> as scope, ->

      @militaryRankTypes = config.get 'militaryRankTypes'
      @militaryServices = config.get 'militaryServices'
      @ranks = config.get 'militaryRanks'
      
      @getRank = =>
        _.find(@ranks[@event.branch][@event.rankType], 'key', @event.rankValue).label
      
      
  }

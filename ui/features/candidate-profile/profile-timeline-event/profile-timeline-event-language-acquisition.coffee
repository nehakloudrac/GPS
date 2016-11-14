angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEventLanguageAcquisition', (config) ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event-language-acquisition.html'
    scope: true
    require: '^profileTimelineEvent'
    link: (scope, elem, attrs, parentScope) -> as scope, ->

      @languageCodes = config.get 'languageCodes'
      @languageAcquisitionSources = config.get 'languageAcquisitionSources'

  }

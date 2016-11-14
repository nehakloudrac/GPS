angular.module 'gps.common.candidate-profile'
.directive 'profileTimelineEvent', ->
  return {
    restrict: 'E'
    templateUrl: '/features/candidate-profile/profile-timeline-event/profile-timeline-event.html'
    scope:
      event: '='
    controller: (
      $scope,
      $state,
      labeler,
      layout,
      config
      profileCompletenessHelper
    ) -> as $scope, ->
      @countryCodes = config.get 'countryCodes'
      @languageCodes = config.get 'languageCodes'
      @timelineTypes = config.get 'timelineTypes'

      @labeler = labeler

      @getEditUrl = =>
        state = if _.includes ['university','study_abroad', 'language_acquisition'], @event.type then 'profile.edit.education' else 'profile.edit.professional'
        return $state.href state, {hash: @event.hash}

      @isIncomplete = =>
        return !profileCompletenessHelper.isTimelineEventComplete @event

      @getLabel = (items, key, keyField='key', labelField='label') =>
        return @labeler.getLabel items, key, keyField, labelField

      @getLabels = (items, keys, keyField='key', labelField='label') =>
        return @labeler.getLabels items, keys, keyField, labelField
      
      # handle new lines and force to line breaks
      @getDescription = =>
        return if @event?.description? then @event.description.replace(/(\r\n|\n|\r)/gm, "<br>") else ''
  }

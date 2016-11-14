angular.module 'gps.profile'
.directive 'editTimeline', (
  appCandidateProfile
  profileCompletenessHelper
  modals
  $rootScope
  $state
  config
  labeler
) ->
  return {
    restrict: 'E'
    scope:
      types: '='
      typeLabel: '@'
    templateUrl: '/apps/profile/directives/edit-timeline/edit-timeline.html'
    link: (scope, elem, attrs) -> as scope, ->
      @events = []
      @currentEvent = null
      @deletePromise = null
      @state = $state.$current.data
      
      @notCurrentlyEditing = (evt) =>
        return true if !@currentEvent?.hash? || @currentEvent.hash != evt.hash
        return false
      
      @isIncomplete = (evt) =>
        return !profileCompletenessHelper.isTimelineEventComplete(evt)
      
      @getEventLabel = (evt) ->
        return switch evt.type
          when 'job'
            if evt.title? && evt.institution?.name?
              return "#{evt.title} at #{evt.institution.name}"
            return evt.title if evt.title?
            return evt.institution.name if evt.institution?.name?
            return 'Job'
          when 'volunteer'
            if evt.institution?.name? then evt.institution.name else 'Volunteer'
          when 'military'
            if evt.branch? then labeler.getLabel config.get('militaryServices'), evt.branch else 'Military'
          when 'research'
            if evt.institution?.name? then evt.institution.name else 'Research'
          when 'university'
            if evt.institution?.name? then evt.institution.name else 'University'
          when 'study_abroad'
            if evt.programName? then evt.programName else 'Study Abroad'
          when 'language_acquisition'
            if evt.languageRefs?[0]? then "Language Study (#{labeler.getLabel(config.get('languageCodes'), evt.languageRefs[0], 'code') })" else 'Language Study'
      
      m = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]
      @getEventDurationLabel = (evt) ->
        dStart = if evt.duration?.start? then new Date(evt.duration.start * 1000) else null
        dEnd = if evt.duration?.end? then new Date(evt.duration.end * 1000) else null
        
        if !dStart && !dEnd
          return 'n/a'
        if dStart && dEnd
          return "#{m[dStart.getMonth()]} #{dStart.getFullYear()} - #{m[dEnd.getMonth()]} #{dEnd.getFullYear()}"
        if dStart && !dEnd
          return "#{m[dStart.getMonth()]} #{dStart.getFullYear()} - Present"
        return 'n/a'
      
      @add = => @currentEvent = {}

      @edit = (evt) => @currentEvent = evt

      @done = =>
        @currentEvent = null
        init()

      @delete = (evt) =>
        if !evt.hash?
          @currentEvent = null
          return
        
        res = modals.launch 'confirm', { title: "Remove Event", message: "Are you sure you want to remove this event from your history?" }
        res.then (ok) =>
          return if !ok
          if ok
            @deletePromise = appCandidateProfile.delete "/timeline/#{evt.hash}"
            .success (res) =>
              appCandidateProfile.removeTimelineEvent(evt.hash)
              if @currentEvent && @currentEvent.hash == evt.hash
                @currentEvent = null
              $rootScope.$broadcast 'gps.appCandidateProfile'
            .error (err) ->
              console.error 'Failed to remove timeline event'
              init()

      sortEvents = (events) ->
        items = {complete: [], incomplete: []}
        for evt in events
          if profileCompletenessHelper.isTimelineEventComplete evt
            items.complete.push evt
          else
            items.incomplete.push evt
        
        
        items.complete.sort (e1, e2) ->
          now = Date.now()
          ds1 = new Date(e1.duration.start * 1000)
          ds2 = new Date(e2.duration.start * 1000)
          de1 = if e1.duration.end? then new Date(e1.duration.end * 1000) else now
          de2 = if e2.duration.end? then new Date(e2.duration.end * 1000) else now
          
          # sort by start date if end dates are both "now" (meaning event is "current")
          if de1 == de2
            return 1 if ds1 < ds2
            return -1 if ds1 > ds2
            return 0
          
          #otherwise, sort by end date
          return 1 if de1 < de2
          return -1 if de1 > de2
          return 0
          
        return items

      
      init = =>
        evts = _.filterByValues(appCandidateProfile.getData().timeline, 'type', @types)
        sorted = sortEvents evts
        final = []
        final.push evt for evt in sorted.complete
        final.push evt for evt in sorted.incomplete
        
        #TODO: if completely empty, maybe send requests to delete them?

        @events = final
        
        #check for newly created event that we're still editing
        if @currentEvent?.hash?
          for evt in @events
            if evt.hash == @currentEvent.hash
              @edit evt
        
      
      @$on 'gps.new-timeline-event', (e, evt) =>
        @currentEvent = evt
        init()

      @$on 'gps.appCandidateProfile', init
      
      init()
  }
  
###
# Convenience class for managing updates to nested candidate profile data
###
class AppCandidateProfile
  constructor: (@http, @data) ->
  
  refresh: ->
    promise = @http.get "/api/candidate-profiles/#{@data.id}?completeness=true&experience=true"
    promise.success (res) =>
      @setData res.profile
    
    return promise
  
  setData: (data) ->
    @data = data
  getData: ->
    @data

  applyCountry: (obj) ->
    for country,i in @data.countries
      if country.hash == obj.hash
        @data.countries[i] == obj
        return
    @data.countryes.push obj
  
  removeCountry: (hash) ->
    @data.countries = _.reject @data.countries, {hash: hash}

  applyLanguage: (obj) ->
    for lang,i in @data.languages
      if lang.hash == obj.hash
        @data.languages[i] = obj
        return
    @data.languages.push obj
  
  removeLanguage: (hash) ->
    @data.languages = _.reject @data.languages, {hash: hash}

  applyLanguageCertification: (langHash, cert) ->
    for lang in @data.languages
      if lang.hash == langHash
        for langCert, i in lang.officialCertifications
          if langCert.hash == cert.hash
            lang.officialCertifications[i] = cert
            return
        lang.officialCertifications.push cert

  removeLanguageCertification: (langHash, hash) ->
    for lang in @data.languages
      if lang.hash == langHash
        lang.officialCertifications = _.reject lang.officialCertifications, {hash: hash}
        return

  applyTimelineEvent: (obj) ->
    for evt,i in @data.timeline
      if evt.hash == obj.hash
        @data.timeline[i] = obj
        return
    @data.timeline.push obj

  removeTimelineEvent: (hash) ->
    @data.timeline = _.reject @data.timeline, {hash: hash}

  applyOrganization: (org) ->
    for inst,i in @data.organizations
      if inst.hash == org.hash
        @data.organizations[i] = org
        return
    @data.organizations.push org

  removeOrganization: (hash) ->
    @data.organizations = _.reject @data.organizations, {hash: hash}

  applyProjectAvailability: (obj) ->
    for avail, i in @data.idealJob.availability
      if obj.hash == avail.hash
        @data.idealJob.availability[i] = obj
        return
    @data.idealJob.availability.push obj

  removeProjectAvailability: (hash) ->
    @data.idealJob.availability = _.reject @data.idealJob.availability, {hash: hash}

  applyAward: (obj) ->
    for ref, i in @data.awards
      if obj.hash == ref.hash
        @data.awards[i] = obj
        return
    @data.awards.push obj

  removeAward: (hash) ->
    @data.awards = _.reject @data.awards, {hash: hash}
  
  applyCertification: (obj) ->
    for ref, i in @data.certifications
      if obj.hash == ref.hash
        @data.certifications[i] = obj
        return
    @data.certifications.push obj

  removeCertification: (hash) ->
    @data.certifications = _.reject @data.certifications, {hash: hash}

  applyAcademicOrg: (obj) ->
    for ref, i in @data.academicOrganizations
      if obj.hash == ref.hash
        @data.academicOrganizations[i] = obj
        return
    @data.academicOrganizations.push obj

  removeAcademicOrg: (hash) ->
    @data.academicOrganizations = _.reject @data.academicOrganizations, {hash: hash}

  applyCountry: (obj) ->
    for ref, i in @data.countries
      if obj.hash == ref.hash
        @data.countries[i] = obj
        return
    @data.countries.push obj

  removeCountry: (hash) ->
    @data.countries = _.reject @data.countries, {hash: hash}

  applyCountryEvent: (countryHash, obj) ->
    for country in @data.countries
      if countryHash == country.hash
        for event, i in country.events
          if event.hash == obj.hash
            country.events[i] = obj
            return
        country.events.push obj
        return

  removeCountryEvent: (countryHash, eventHash) ->
    for country in @data.countries
      if countryHash == country.hash
        country.events = _.reject country.events, {hash: eventHash}
        return

  getPotentialCountryAssociations: (timelineEvent) ->
    potentials = []
    for country in @data.countries
      item = {country: country, events: []}
      for evt in country.events
        continue if evt.duration?.end? && evt.duration.end < timelineEvent.duration.start
        continue if evt.duration?.start? && timelineEvent.duration.start < evt.duration.start

        item.events.push evt
      potentials.push item if item.events.length > 0
    return potentials

  put: (path, data = null) ->
    promise = @http.put "/api/candidate-profiles/#{@data.id}#{path}", data
    .error (err) ->
      console.log 'TODO: handle error'
    return promise

  post: (path, data = null) ->
    promise = @http.post "/api/candidate-profiles/#{@data.id}#{path}", data
    .error (err) ->
      console.log 'TODO: handle error'
    return promise

  delete: (path) ->
    promise = @http.delete "/api/candidate-profiles/#{@data.id}#{path}"
    .error (err) ->
      console.log 'TODO: handle error'
    return promise

angular.module 'gps.common.models'

.service 'appCandidateProfile', ['$http', 'candidateProfileData', AppCandidateProfile]

.run ($rootScope, appCandidateProfile, $http) ->

  $rootScope.$on 'syncAppCandidateProfile', (e, data) ->
    $http.get "/api/candidate-profiles/#{candidateProfile.id}"
    .success (res) ->
      appCandidateProfile.setData res.profile
      $rootScope.$broadcast 'resetProfile'
    .error (err) ->
      $rootScope.$broadcast 'resetProfile'
      console.log 'TODO: handle error'

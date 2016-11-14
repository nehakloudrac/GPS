angular.module 'gps.admin'
.directive 'adminFacets', (
  $http
  config
  labeler
  layout
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/admin/directives/admin-facets/admin-facets.html'
    link: (scope, elem, attrs) -> as scope, ->
      @facetPromise = null
      @facetResults = null
      @facetValueFilter = null
      @selectedFacetValue = null
      @facetChoices = [
        {key: 'foreign-languages', val: 'Foreign Languages', api: 'profiles'}
        {key: 'native-languages', val: 'Native Languages', api: 'users'}
        {key: 'countries', val: 'Countries (Abroad)', api: 'profiles'}
        {key: 'residence', val: 'Countries (Residence)', api: 'users'}
        {key: 'visas', val: 'Countries (Visas/Passports)', api: 'users'}
        {key: 'countries-ideal', val: 'Countries (Ideal Job)', api: 'profiles'}
        {key: 'industries', val: 'Industries', api: 'profiles'}
        {key: 'degrees', val: 'Degrees', api: 'profiles'}
        {key: 'concentrations', val: 'Majors/Minors', api: 'profiles'}
        {key: 'status', val: 'Current Status', api: 'users'}
        {key: 'referrer', val: 'Referrer Link', api: 'users'}
        {key: 'employers', val: 'Employer Name', api: 'profiles'}
        {key: 'universities', val: 'Universities', api: 'profiles'}
        {key: 'us-security-clearances', val: 'US Security Clearance', api: 'users'}
        {key: 'us-work-authorizations', val: 'US Work Authorization', api: 'users'}
        {key: 'states-residence', val: "State (Residence)", api: "users"}
        {key: 'states-ideal', val: "State (Ideal Job)", api: 'profiles'}
      ]
      @models =
        selectedFacet: @facetChoices[0]

      @profilePromise = null
      @profileResults = null
      
      @error = null
      
      loadFacet = (loadFacetValue = null) =>
        @profileResults = null
        @facetValueFilter = null
        @selectedFacetValue = null
        
        @facetPromise = $http.get "/api/admin/overview/facets/#{@models.selectedFacet.key}"
        .success (res) =>
          @error = null
          res.data.forEach (facet) =>
            facet.translation = @translateFacetValue facet.value
          @facetResults = res.data
          if loadFacetValue != null
            @selectFacetValue loadFacetValue
        .error (err) =>
          @error = if err.response?.message? then err.response.message else "Error loading selected facet."
      
      @selectFacetValue = (facet) =>
        @selectedFacetValue = facet
        layout.set 'admin.selectedFacetValue', facet

        @profilePromise = $http.get "/api/admin/overview/#{@models.selectedFacet.api}?id=#{facet.ids.join(',')}"
        .success (res) =>
          @error = null
          @profileResults = res
        .error (err) =>
          @error = if err.response?.message? then err.response.message else "Error loading #{@models.selectedFacet.api}."
      
      @translateFacetValue = (val) =>
        return 'N/A' if !val
        
        return switch @models.selectedFacet.key
          when 'industries','concentrations','employers','universities','referrer' then val
          when 'countries','visas','residence' then labeler.getLabel config.get('countryCodes'), val, 'code', 'name'
          when 'countries-ideal' then labeler.getLabel config.get('locationsAbroadChoices'), val, 'code', 'name'
          when 'states-residence' then labeler.getLabel config.get('usTerritories'), val
          when 'states-ideal' then labeler.getLabel config.get('locationsUSAChoices'), val
          when 'foreign-languages','native-languages' then labeler.getLabel config.get('languageCodes'), val, 'code'
          when 'status' then labeler.getLabel config.get('userJobStatusOptions'), val
          when 'degrees' then labeler.getLabel config.get('universityDegrees'), val
          when 'us-security-clearances' then labeler.getLabel config.get('usSecurityClearances'), val
          when 'us-work-authorizations' then labeler.getLabel config.get('usWorkAuthorizations'), val
          else '*******'
      
      #check layout for previously selected facets/values
      if layout.get 'admin.selectedFacet', false
        @models.selectedFacet = layout.get 'admin.selectedFacet'
      if layout.get 'admin.selectedFacetValue', false
        @selectedFacetValue = layout.get 'admin.selectedFacetValue'

      #load default facet
      loadFacet(@selectedFacetValue)
      
      @$watch 'models.selectedFacet', (newVal) ->
        layout.set 'admin.selectedFacet', newVal
        loadFacet(null)
  }
  
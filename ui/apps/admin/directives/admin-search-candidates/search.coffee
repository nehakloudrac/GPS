angular.module 'gps.admin'
.config (modalsProvider) ->
  modalsProvider.defineModal 'add-search-filters', {
    templateUrl: '/apps/admin/directives/admin-search-candidates/add-filter-modal.html'
    backdrop: 'static'
    animation: 'am-fade'
  }
.directive 'adminSearchCandidates', (
  $location,
  $http,
  $rootScope,
  searchFilterDefinitions,
  searchFacetDefinitions,
  modals,
  layout
) ->
  return {
    restrict: "E"
    scope: {}
    templateUrl: "/apps/admin/directives/admin-search-candidates/search.html"
    link: (scope, elem, attrs) -> as scope, ->
      
      @filters = []
      
      @quickSearch = ''
      
      #set default pagination values
      @pagination =
        limit: 20
        skip: 0
      
      @result =
        result: {}
        error: null
        promise: null
      
      initFromClientUrl = (searchModel) =>
        #get search model from url
        initFilters(searchModel)
        
        #init pagination
        if searchModel['_limit']?
          @pagination.limit = searchModel['_limit']
        if searchModel['_skip']?
          @pagination.skip = searchModel['_skip']
        if searchModel['_q']?
          @quickSearch = searchModel['_q']
        
        @search()
      
      #checks for fields and facets, and adds corresponding
      #filter if detected
      initFilters = (searchModel) =>
        @filters = []
        initMap = {}
        #check for filters
        for key,val of searchModel
          continue if !searchFilterDefinitions[key]?
          initMap[key] = true
          @filters.push createFilter key, val
        
        #check for facets, but only add if not already added
        if searchModel['_facets']?
          facets = searchModel['_facets'].split(';')
          for facet in facets
            parts = facet.split(':')
            facetKey = parts[0]
            if searchFacetDefinitions[facetKey]?
              filterName = searchFacetDefinitions[facetKey].key
              if !initMap[filterName]
                @filters.push createFilter filterName, null
            
      createFilter = (key, val = null) ->
        if !searchFilterDefinitions[key]?
          throw new Error "Tried to create unknown search filter: #{key} with #{val}"
        def = searchFilterDefinitions[key]
        filter =
          dirty: false
          key: key
          type: def.type
          name: def.name
          multi: def.multi
          def: def
          conf: if def.conf then def.conf else null
          urlVal: val
          internalVal: null
        if def.facet?
          filter.facetState = {}

        return filter
      
      #do search based on state of available filters
      @search = (resetPagination = false) =>
        @pagination.skip = 0 if resetPagination
        
        #build & update url from facets/filters
        queryParams = buildServerSearchQueryParamsString()
        @result.promise = $http.get "/api/admin/search/candidates?#{queryParams}"
        .success (res) =>
          @result.result = res.result
          @result.error = null
          
          # make sure pagination updates if too few results
          if @pagination.skip > @result.result.total
            @pagination.skip = 0
          
          updateClientUrl()
          
          filter.dirty = false for filter in @filters
          
          @$broadcast 'search.result', res
        .error (err) =>
          console.error err
          @result.error = if err.response?.message? then err.response.message else 'Unknown error.'
      
      #rebuild and change current client URL based on
      #status of filters
      updateClientUrl = =>
        searchModel = {}
        
        for filter in @filters
          continue if filter.urlVal == null
          
          if _.isArray filter.urlVal
            searchModel[filter.key] = []
            searchModel[filter.key].push val for val in filter.urlVal
          else
            searchModel[filter.key] = filter.urlVal
        
        #update pagination if they aren't the default values
        if @pagination.limit != 20
          searchModel['_limit'] = @pagination.limit
        if @pagination.skip != 0
          searchModel['_skip'] = @pagination.skip
        
        #assemble facets
        facetVal = createFaceturlVal()
        if facetVal.length > 0
          searchModel['_facets'] = facetVal
        
        if @quickSearch.length > 0
          searchModel['_q'] = @quickSearch
        
        $location.search searchModel
        layout.set('search.params', $location.search())
      
      @reset = ->
        initFromClientUrl({})
      
      createFaceturlVal = =>
        facets = []
        
        for filter in @filters
          continue if !filter.def.facet || !filter.def.facet?
          
          facetName = if filter.def.facet.field? then filter.def.facet.field else filter.key
          facetSize = if filter.def.facet.size? then filter.def.facet.size else 0
          facetSearch = if filter.facetState?.search? then filter.facetState.search else ''
          if facetSearch.trim().length == 0
            facetSearch = false
          
          str = ''
          str += facetName
          str += ':'+facetSize
          str += ':'+facetSearch if facetSearch
          facets.push str
          
        return facets.join(';')
        
      
      #build params for search API call based on
      #status of filters
      buildServerSearchQueryParamsString = =>
        params = []
        
        #assemble filter values, making sure to handle arrays properly
        for filter in @filters
          keystr = if filter.multi then "#{filter.key}[]" else filter.key
          
          if angular.isArray filter.urlVal
            for val in filter.urlVal
              if val != null && val.trim().length > 0
                param = "#{keystr}=#{val}"
                params.push param
          else
            if filter.urlVal != null && filter.urlVal.trim().length > 0
              param = "#{keystr}=#{filter.urlVal}"
              params.push param
        
        #build facets there's a need
        facetVal = createFaceturlVal()
        if facetVal.length > 0
          params.push "_facets=#{facetVal}"
        
        #add pagination
        params.push "_limit=#{@pagination.limit}"
        params.push "_skip=#{@pagination.skip}"
        
        if @quickSearch.length > 0
          params.push "q=#{@quickSearch}"
        else
          params.push "_sort=dateCreated:-"
        
        return params.join '&'
      
      @removeFilter = (index) =>
        @filters.splice index, 1
        @search(true)
      
      @$on 'pagination.navigate', (s, num) =>
        @pagination.skip = num
        @search()
      
      @$on 'filters.add', (e, key) =>
        @filters.push createFilter key
      
      @$on 'filters.remove', (e, key) =>
        i = 0
        for filter in @filters
          if filter.key == key
            @filters.splice(i, 1)
            return
          i++
      
      @$on 'filters.changed', =>
        @search(true)
      
      init = ->
        searchModel = layout.get('search.params', {})
        if _.isEmpty searchModel
          searchModel = $location.search()
        
        initFromClientUrl(searchModel)
      
      init()
  }

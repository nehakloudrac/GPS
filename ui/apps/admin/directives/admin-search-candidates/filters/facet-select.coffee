angular.module 'gps.admin'
.directive 'filterFacetSelect', (
  searchFacetDefinitions,
  labeler
) ->
  return {
    restrict: 'E'
    scope: {
      filter: '='
      result: '='
    }
    templateUrl: '/apps/admin/directives/admin-search-candidates/filters/facet-select.html'
    link: (scope, elem, attrs) -> as scope, ->
      
      @keyField = if @filter.def.conf?.keyField? then @filter.def.conf.keyField else 'key'
      @labelField = if @filter.def.conf?.labelField? then @filter.def.conf.labelField else 'label'
      facetKey = if @filter.def.facet?.field? then @filter.def.facet.field else @filter.key
      @loaded = false
      
      @facetSearchString = ''
      
      reset = =>
        @facetResult = []
        @facetSelections = {}
      
      initFromUrlValue = =>
        @facetSelections = {}
        
        return if null == @filter.urlVal
        
        #TODO: at some point, should get rid of this "multi" nonsense...
        #it's debt from earlier implementations
        #
        #right now, even if there is more than on filter value, only handle
        #the first... could use some sort of mode toggle to specify
        #the difference between and AND or OR, but not yet
        urlVal = if _.isArray(@filter.urlVal) then @filter.urlVal[0] else @filter.urlVal
        
        vals = urlVal.split(',')
        cleaned = (str for str in vals when str.trim().length > 0)
        for val in vals
          @facetSelections[val.trim()] = true
      
      updateUrlValue = =>
        selections = []
        for key,val of @facetSelections
          selections.push key if val
        selections = _.reject selections, (item) -> return item.length == 0
        @filter.urlVal = if @filter.multi then [selections.join(',')] else selections.join(',')
      
      getLabelFromLabeler = (key) =>
        return labeler.getLabel(@filter.def.conf.selections, key, @keyField, @labelField)

      getLabelFromFacet = (key) ->
        return key
      
      #label func depends on whether or not selections are known
      @getLabel = if @filter.def?.conf?.selections? then getLabelFromLabeler else getLabelFromFacet
      
      #check new search results for facets for this field, and sort
      #them according to doc_count
      @$on 'search.result', (e, res) =>
        @loaded = true
        
        if res.result?.facets[facetKey]?
          @facetResult = res.result.facets[facetKey].buckets
          @facetResult = _.sortByOrder(@facetResult, ['doc_count'], ['desc'])
          
        else
          reset()
      
      #do text search on available definitions from confige, then
      #match those with keys from available facets
      #
      #This is generally only used for fields that involve languages or countries, since
      #we store lang/country codes in data, thus the facets contain codes, not
      #lang or country names
      @searchSelections = (search) =>
        matches = []
        
        for str in search.split(',') when str.trim().length > 0
          for item in @filter.def.conf.selections
            if item[@labelField].search(new RegExp(str.trim(), 'i')) != -1
              matches.push item[@keyField]

        @filter.facetState.search = matches.join('|')
        @$emit 'filters.changed'
        
        console.log @filter.facetState
      
      @searchFacet = (search) =>
        vals = []
        for str in search.split(',')
          str = str.trim()
          vals.push str if str.length > 0
        
        @filter.facetState.search = vals.join(',')
        @$emit 'filters.changed'
        console.log @filter.facetState
        
      
      @toggleSelectedValue = (key) ->
        # force init to false of values that aren't already in the map... getting
        # unexpected behaviour by not doing that
        if !@facetSelections[key]?
          @facetSelections[key] = false

        @facetSelections[key] = !@facetSelections[key]
        updateUrlValue()
        @$emit 'filters.changed'
      
      reset()
      initFromUrlValue()
  }

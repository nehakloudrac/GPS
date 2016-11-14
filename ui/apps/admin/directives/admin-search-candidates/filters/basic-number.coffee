angular.module 'gps.admin'
.directive 'filterBasicNumber', ->
  return {
    restrict: 'E'
    scope: {
      filter: '='
    }
    templateUrl: '/apps/admin/directives/admin-search-candidates/filters/basic-number.html'
    link: (scope, elem, attrs) -> as scope, ->
      
      @isRange = false
      @toggleRange = =>
        @isRange = !@isRange
        if @isRange && @filter.internalVal.length <= 1
          num = if @filter.conf.widget == 'text' then null else Math.floor(Date.now() / 1000) - 86400
          @filter.internalVal.push {op: 'gte', num: num}
        if !@isRange && @filter.internalVal.length >= 2
          @filter.internalVal.pop()
          @$emit 'filters.changed'
        
      
      #the internal val should always be an array, for
      #"multi" support, even if not used
      initFromUrlVal = =>
        @filter.internalVal = []
        
        #empty init check (filter just added)
        if @filter.urlVal == null
          num = if @filter.conf.widget == 'text' then null else Math.floor(Date.now() / 1000) - 86400
          @filter.internalVal.push {op: 'gte', num: num}
          return
        
        #maybe got a string instead of array value from url
        if !angular.isArray(@filter.urlVal)
          parts = @filter.urlVal.split(':')
          @filter.internalVal = [{op: parts[0], num: parts[1]}]
        else
          #got an array
          @isRange = true
          for val in @filter.urlVal
            parts = val.split(':')
            @filter.internalVal.push {op: parts[0], num: parts[1]}
      
      updateUrlVal = (newVal) =>
        vals = []
        for val in newVal
          continue if null == val.num
          vals.push "#{val.op}:#{val.num}"
        @filter.urlVal = vals
        @$emit 'filters.changed'
      
      @$watchCollection 'filter.internalVal', (newVal) =>
        @update()
      
      @update = =>
        updateUrlVal(@filter.internalVal)
      
      initFromUrlVal()
  }
  
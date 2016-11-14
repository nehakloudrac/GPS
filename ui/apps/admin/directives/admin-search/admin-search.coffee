angular.module 'gps.admin'
.directive 'adminSearch', (
  $http,
  $location,
  config,
  layout
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: "/apps/admin/directives/admin-search/admin-search.html"
    link: (scope, elem, attrs) -> as scope, ->
      @res = []
      
      @institutionIndustries = config.get('institutionIndustries')
      @countryCodes = config.get('countryCodes')
      @languageCodes = config.get('languageCodes')
      @locationsUSAChoices = config.get('locationsUSAChoices')
      @locationsAbroadChoices = config.get('locationsAbroadChoices')
      
      searchType = null
      @numPerPage = 20
      @page = 1
      @pagination = { visible: false }
      @promise = null
      @userFilters = {}
      @profileFilters = {}
      
      @resetUserFilters = =>
        @userFilters =
          id: null
          shortId: null
          name: null
          email: null
          languages: []
      
      @resetProfileFilters = =>
        @profileFilters =
          countries: []
          languages: []
          industries: []
          universities: []
          locationsUSA: []
          locationsAbroad: []
          skills: []
      
      @searchUsers = (resetPage = true) =>
        @page = 1 if resetPage
        searchType = 'users'
        skip = (@page * @numPerPage) - @numPerPage
        url = "/api/admin/overview/users?limit=#{@numPerPage}&skip=#{skip}"
        
        layout.set 'admin.search', {
          page: @page
          type: 'users'
          form: 'user'
          filters: @userFilters
        }

        #add search filters
        if @userFilters.id?
          url += "&id=#{@userFilters.id}"
        if @userFilters.shortId?
          url += "&shortId=#{@userFilters.shortId}"
        if @userFilters.name?
          url += "&name=#{@userFilters.name}"
        if @userFilters.email?
          url += "&email=#{@userFilters.email}"
        if @userFilters.languages.length
          url += "&languages=#{@userFilters.languages.join(',')}"
        
        doSearch url
      
      @searchProfiles = (resetPage = true) =>
        @page = 1 if resetPage
        searchType = 'profiles'
        skip = (@page * @numPerPage) - @numPerPage
        url = "/api/admin/overview/profiles?limit=#{@numPerPage}&skip=#{skip}"
        
        layout.set 'admin.search', {
          page: @page
          type: 'profiles'
          form: 'profile'
          filters: @profileFilters
        }

        #add search filters
        if @profileFilters.countries.length
          url += "&countries=#{@profileFilters.countries.join(',')}"
        if @profileFilters.languages.length
          url += "&languages=#{@profileFilters.languages.join(',')}"
        if @profileFilters.industries.length
          url += "&industries=#{@profileFilters.industries.join(',')}"
        if @profileFilters.universities.length
          url += "&universities=#{@profileFilters.universities.join(',')}"
        if @profileFilters.locationsUSA.length
          url += "&locationsUSA=#{@profileFilters.locationsUSA.join(',')}"
        if @profileFilters.locationsAbroad.length
          url += "&locationsAbroad=#{@profileFilters.locationsAbroad.join(',')}"
        if @profileFilters.skills.length
          url += "&skills=#{@profileFilters.skills.join(',')}"
        
        doSearch url
      
      doSearch = (url) =>
        @promise = $http.get url
        .success (res) =>
          @res = res
          updatePagination()
        .error console.error
        
      @goToPage = (num) =>
        @page = num
        if searchType == 'profiles' then @searchProfiles(false) else @searchUsers(false)
            
      updatePagination  = =>
        @pagination =
          visible: @res.total > @numPerPage
          totalPages: Math.ceil @res.total / @numPerPage
          currentPage: @page
          range: new Array(Math.ceil @res.total / @numPerPage)
      
      # check for previous filters and restore search
      if layout.get 'admin.search', false
        data = layout.get 'admin.search'
        @page = data.page
        @searchForm = data.form

        if 'users' == data.type
          @userFilters = data.filters
          @resetProfileFilters()
          @searchUsers(false)
        else if 'profiles' == data.type
          @profileFilters = data.filters
          @resetUserFilters()
          @searchProfiles(false)
      else
        @resetUserFilters()
        @resetProfileFilters()
      
  }
  
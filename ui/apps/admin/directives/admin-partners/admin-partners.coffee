angular.module 'gps.admin'
.directive 'adminPartners', (
  $http
  modals
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/admin/directives/admin-partners/admin-partners.html'
    link: (scope, elem, attrs) -> as scope, ->
      @partners = []
      @currentPartner = null
      @promise = null
      @error = null
      
      @add = =>
        @currentPartner = {}
      
      @edit = (index) =>
        @currentPartner = _.cloneDeep @partners[index]
      
      @cancel = =>
        @currentPartner = null
      
      @save = =>
        return if !@currentPartner
        
        if !@currentPartner.id?
          method = 'post'
          url = "/api/partners"
        else
          method = "put"
          url = "/api/partners/#{@currentPartner.id}"
        
        @promise = $http[method](url, @currentPartner)
        .success =>
          @currentPartner = null
          init()
        .error (res) =>
          @error = if res.response?.message? then res.response.message else 'Error saving partner'
          
      @remove = (index) =>
        modals.launch 'confirm', {title: 'Remove Partner?', message: 'Are you sure you want to completely remove this partner?'}
        .then (res) =>
          return if !res
          @promise = $http.delete "/api/partners/#{@partners[index].id}"
          .success init
          .error (res) =>
            @error = if res.response?.message? then res.response.message else 'Error removing partner'
      
      init = =>
        @promise = $http.get '/api/partners'
        .success (res) =>
          @error = null
          @partners = res.partners
        .error (res) =>
          @error = if res.response?.message? then res.response.message else 'Error retrieving partners'

      init()
  }
angular.module 'gps.admin'
.directive 'adminResourceLinks', (
  $http
  modals
) ->
  return {
    restrict: 'E'
    scope: {}
    templateUrl: '/apps/admin/directives/admin-resource-links/resource-links.html'
    link: (scope, elem, attrs) -> as scope, ->
      @items = []
      @currentItem = null
      @promise = null
      @error = null
      
      @add = =>
        @currentItem = {}
      
      @edit = (index) =>
        @currentItem = _.cloneDeep @items[index]
      
      @cancelEdit = =>
        @currentItem = null
      
      @delete = (index) =>
        modals.launch 'confirm', {title: 'Delete Resource?', message: "Are you sure you want to delete this resource link?"}
        .then (res) =>
          return if !res
          @promise = $http.delete "/api/content/links/#{@items[index].id}"
          .success =>
            @error = null
            reload()
          .error (res) =>
            @error = if res.response?.message? then res.response.message else 'Error deleting item.'
      
      @save = =>
        if @currentItem.id?
          method = 'put'
          url = "/api/content/links/#{@currentItem.id}"
          isNew = false
        else
          method = 'post'
          url = "/api/content/links"
          isNew = true
        
        @promise = $http[method](url, @currentItem)
        .success (res) =>
          @error = null
          @currentItem = null
          # TODO: if !isNew, consider direct replace in list instead of a full reload
          reload()
        .error (res) =>
          @error = if res.response?.message? then res.response.message else 'Error saving item.'
      
      reload = =>
        @promise = $http.get '/api/content/links'
        .success (res) =>
          @items = res.links
          @error = null
        .error (res) =>
          @error = if res.response?.message? then res.response.message else 'Error loading items.'
      
      reload()
  }
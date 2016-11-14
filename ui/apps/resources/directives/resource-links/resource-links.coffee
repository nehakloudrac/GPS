angular.module 'gps.resources'
.directive 'resourceLinks', ($http) ->
  return {
    restrict: 'E'
    scope:
      tags: '='
      type: '@'
    templateUrl: '/apps/resources/directives/resource-links/resource-links.html'
    link: (scope, elem, attrs) -> as scope, ->
      @links = []
      @error = null
      @promise = null
      
      @getMediaClass = (index) =>
        switch @links[index].mediaType
          when 'text' then 'fa-newspaper-o'
          when 'file' then 'fa-file-o'
          when 'video' then 'fa-video-camera'
          when 'audio' then 'fa-volume-up'
          else 'fa-external-link'
      
      @reload = =>
        @links = []
        url = "/api/content/links?types=#{@type}"
        url += "&tags=#{@tags.join(',')}" if attrs.tags?
        
        @promise = $http.get url
        .success (res) =>
          @links = res.links
          @error = null
        .error (res) =>
          @error = if res.response?.message? then res.response.message else 'Error loading resources.'
          console.error 'error'
      
      @reload()
  }
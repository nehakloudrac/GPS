angular.module 'gps.admin'
.directive 'resourceForm', (
  $http
  profileFormInitializer
) ->
  return {
    restrict: 'E'
    scope:
      item: '='
    templateUrl: '/apps/admin/directives/admin-resource-links/resource-form.html'
    link: (scope, elem, attrs) -> as scope, ->
      @types =
        "article": "Article"
        "quote": "Quote"
        "program": "Program"
        "public_news": "Public Press Release"
      
      init = =>
        @tags = _.cloneDeep @item.tags
      
      @updateTags = =>
        @item.tags = _.pluck(@tags, 'text')
      
      init()
      
      @$watch 'item.id', init
  }
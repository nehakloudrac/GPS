angular.module 'gps.admin'
.config ($stateProvider, $urlRouterProvider) ->

  createBC = (name) ->
    return [{title: "Admin"}, {title: name}]

  $stateProvider.state 'admin',
    url: "^"
    abstract: true
    template: "<admin-page></admin-page>"
  
  
  $stateProvider.state 'admin.overview',
    url: "/overview"
    template: "<admin-overview></admin-overview>"
    onEnter: (layout) -> layout.setBreadcrumb createBC 'Overview'

  $stateProvider.state 'admin.search-candidates',
    url: "/search/candidates"
    template: "<admin-search-candidates></admin-search-candidates>"
    onEnter: (layout) -> layout.setBreadcrumb createBC "Candidate Search"

  $stateProvider.state 'admin.facets',
    url: "/facets"
    template: "<admin-facets></admin-facets>"
    onEnter: (layout) -> layout.setBreadcrumb createBC 'Categories'

  $stateProvider.state 'admin.search',
    url: "/search"
    template: "<admin-search></admin-search>"
    onEnter: (layout) -> layout.setBreadcrumb createBC 'Search'
  
  $stateProvider.state 'admin.resource-links',
    url: "/resource-links"
    template: "<admin-resource-links></admin-resource-links>"
    onEnter: (layout) -> layout.setBreadcrumb createBC 'Resource Links'

  $stateProvider.state 'admin.partners',
    url: "/partners"
    template: "<admin-partners></admin-partners>"
    onEnter: (layout) -> layout.setBreadcrumb createBC 'Partners'

  $stateProvider.state 'user-print-view',
    url: "/print-user/:userId"
    template: "<admin-print-profile></admin-print-profile>"

  $urlRouterProvider.otherwise '/overview'

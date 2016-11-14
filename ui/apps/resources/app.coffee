#utility to allow easier use of coffeescript "@" this syntax
window.as = (context, fn) -> fn.call(context)

angular.module 'gps.resources', [
  'ngSanitize'
  'markdown'
  'gps.common.layout'
  'mgcrea.ngStrap.tooltip'
]

.config ($stateProvider, $urlRouterProvider) ->
  
  $stateProvider.state 'resources',
    url: '^'
    abstract: true
    template: "<resources-app></resources-app>"
  
  $stateProvider.state 'resources.articles',
    url: '/articles'
    template: "<resource-links type='article'></resource-links>"

  $stateProvider.state 'resources.quotes',
    url: '/quotes'
    template: "<resource-links type='quote'></resource-links>"
  
  $stateProvider.state 'resources.programs',
    url: '/programs'
    template: "<resource-links type='program'></resource-links>"
  
  $stateProvider.state 'resources.news',
    url: '/news'
    template: "<resource-links type='public_news'></resource-links>"

  $urlRouterProvider.otherwise ->
    return '/articles'

.run (layout) ->
  layout.setTitle "Resources"
  layout.setBreadcrumb [
      title: 'Resources'
  ]
  
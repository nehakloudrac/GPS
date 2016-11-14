angular.module 'gps.profile'
.config ($stateProvider, $urlRouterProvider) ->

  createSectionBreadcrumb = (items) ->
    bc = [{title: "Profile", href: '#/profile'}]
    bc.push {title: item} for item in items
    return bc
  
  
  $stateProvider.state 'intro',
    url: '^/intro'
    template: '<profile-intro></profile-intro>'
    onEnter: ($timeout, appUserService, profileTutorialLauncher, layout) ->
      layout.setBreadcrumb [{title: 'Getting Started'}]
      #launch the tutorial on load if they've never seen it
      user = appUserService.getData()
      if !user.status?.seenProfileViewTutorial? || !user.status.seenProfileViewTutorial
        $timeout (-> profileTutorialLauncher.launchTutorial() ), 1000
  
  $stateProvider.state 'profile',
    abstract: true
    url: '^'
    template: '<profile-app></profile-app>'

  $stateProvider.state 'profile.view',
    url: '/profile'
    template: '<view-profile></view-profile>'
    onEnter: (layout) ->
      layout.setBreadcrumb [
          title: "Profile"
      ]
      
  $stateProvider.state 'profile.edit',
    abstract: true
    url: '/edit'
    template: "<ui-view></ui-view>"
    onEnter: (layout) ->
      layout.setBreadcrumb [
          title: "Edit"
      ]
      
      #TODO: move this... isn't having the intended effect here
      $('.profile-card .expanded').eq(0).animate({scrollTop: 0}, 'fast')
  
  $stateProvider.state 'profile.edit.sections',
    url: '/sections'
    template: "<profile-cards></profile-cards>"
  
  #create dedicated state for each card
  [
    'background'
    'professional'
    'education'
    'ideal-job'
    'countries'
    'languages'
    'skills'
    'personal'
  ].forEach (item) ->
    $stateProvider.state "profile.edit.#{item}",
      url: "/#{item}"
      template: "<profile-cards active='#{item}'></profile-cards>"
  
  $urlRouterProvider.otherwise '/profile'
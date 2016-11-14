# out of all scope, initialize the help button immediately
window.helpButton = new GpsHelpButton($('.custom-zd-help-button'))

$(document).ready ->
  moveCircles = ->
    #TODO: can probably delete as part of redesign
    # $(".boxes .grey-circles-outside").css({'height':($(".grey-background-outside").height()+30+'px')})
    # $(".boxes .grey-circles-outside.first .grey-circle-background").css({'height':($(".grey-background-outside").height()/2+15+'px')})
    # $(".boxes .grey-circles-outside.middle .grey-circle-background").css({'height':($(".grey-background-outside").height()+40+'px')})
    # $(".boxes .grey-circles-outside.last .grey-circle-background").css({'height':($(".grey-background-outside").height()/2+15+'px')})
    
    $(".criteria-list .grey-circles-outside").css({'height':($(".grey-background-outside").height()+'px')})
    $(".criteria-list .grey-circles-outside.first .grey-circle-background").css({'height':($(".grey-background-outside").height()/2+'px')})
    $(".criteria-list .grey-circles-outside.middle .grey-circle-background").css({'height':($(".grey-background-outside").height()+'px')})
    $(".criteria-list .grey-circles-outside.last .grey-circle-background").css({'height':($(".grey-background-outside").height()/2+'px')})

  updateSlideHeight = -> $('.home-header').height($(window).height()  - 90  )
  updateEmployerHeight = ->
    # $('.employer-header').height($(window).height() - $('#first-banner').eq(0).height() - 300)
  updateMainMinHeight = ->
    $('.min-window-height').css 'min-height', $(window).height()

  fadeMenuBackgroundOnScroll = ->
    # ALSO - using this as the trigger to hide/show the Zendesk widget (only really true of the homepage)
    top = $(document).scrollTop()
    if top > 80
      $('.faded-header').find($('.header-background')).removeClass 'header-background-transparent'
      helpButton.show()
    else
      $('.faded-header').find($('.header-background')).addClass 'header-background-transparent'


  #on load update various heights
  moveCircles()
  updateSlideHeight()
  updateEmployerHeight()
  updateMainMinHeight()
  fadeMenuBackgroundOnScroll()

  # on window resize also update various heights
  $(window).resize ->
    updateEmployerHeight()
    updateSlideHeight()
    updateMainMinHeight()
    moveCircles()

  # fade in navbar background color when scrolling down
  $(window).scroll fadeMenuBackgroundOnScroll

# Called on the employer contact page
# to enhance up some form elements
initializeEmployerContactForm = ->
  # initialize select 2 fields
  $('.field-country select').select2 { width: "100%", placeholder: 'Country'}
  $('.field-countries select').select2 { width: "100%", placeholder: 'Desired Country Experience'}
  $('.field-languages select').select2 { width: "100%", placeholder: 'Desired Language Experience'}
  $('.field-skills select').select2 { width: "100%", placeholder: 'Desired Skills', tags: true }
  $('.field-industries select').select2 { width: "100%", placeholder: "Industries", tags: true }
  $('.field-status select').select2 {width: "100%", placeholder: "Status"}

onEmployerFormSubmit = ->
  null
  #TODO: prevent 'enter' submissions

# Angular module for controlling the scrolling image header
angular.module 'gps.public', []
.constant 'backgroundImageClasses', ['paris','moscow','new-york','hong-kong','brazil','africa-middle-east']
.constant 'backgroundImageTexts', {
  'candidate': [
    "US firm seeks brand manager to target European market. English, French, German, Spanish required."
    "German company seeks SAP software developer in Moscow. Must be conversant in Russian."
    "New York-based firm seeks financial analyst in emerging markets. Fluent Japanese, experience in Japan required."
    "Mechanical engineer at aerospace company with locations in US and Hong Kong. Advanced Mandarin required."
    "Research assistant to investigate renewable energy in Brazil. Intermediate Portuguese, time in-country preferred."
    "Project manager for international nonprofit operating in MENA. Global mindset and willingness to travel required."
  ]
  'employer': [
    "Wanted: Brand manager in UK to target European market. Spoken and written English, French, German, and Spanish required."
    "Wanted: Commercial real estate asset manager in Chicago with contract experience in Russia and Central Asia. Russian proficiency required."
    "Wanted: Financial analyst to work in emerging markets at your New York-based firm."
    "Wanted: Financial analyst with Southeast Asian market experience. Fluency in Mandarin, Japanese, and Korean required."
    "Wanted: Research assistant in renewable energy in Brazil. Must speak Portuguese. Prior in-country experience a plus."
    "Wanted: Software developer for large online retail provider located in Mumbai. Hindi required, Urdu and Punjabi preferred."
  ]
}
.run ->
  # This was being hidden on the homepage because it conflicted with
  # a design element - but it may not be a concern anymore... remove
  # if so
  helpButton.hide()
.controller 'HeaderController', ($scope, $interval, backgroundImageClasses, backgroundImageTexts) ->
  $scope.selectedIndex = 0
  $scope.backgroundImageClasses = backgroundImageClasses
  $scope.backgroundImageTexts = backgroundImageTexts

  intId = $interval (->
    $scope.selectedIndex += 1
    if $scope.selectedIndex > $scope.backgroundImageClasses.length - 1
      $scope.selectedIndex = 0
  ), 8000

  $scope.$on '$destroy', ->
    $interval.cancel intId

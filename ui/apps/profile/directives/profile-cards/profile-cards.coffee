angular.module 'gps.profile'
.directive 'profileCards', (
  layout
  $location
  $anchorScroll
) ->
  return {
    restrict: 'E'
    scope:
      active: '@'
    templateUrl: '/apps/profile/directives/profile-cards/profile-cards.html'
    link: (scope, elem, attrs) -> as scope, ->
      @current = if attrs.active? then @active else null
      
      #either nav to new card, or close open card
      @navigateTo = (card) =>
        state = if @current != card then 'profile.edit.'+card else 'profile.edit.sections'
        layout.go state
      
      
      #scroll to designated card
      rescroll = =>
        return if @current == null
        
        setTimeout (->
          targetElem = angular.element('body').find(".profile-card.expanded").eq(0)
          if targetElem.length
            $('body').eq(0).animate({scrollTop: targetElem.offset().top - 100}, 'fast')
        ), 100
      
      #this is... hacky, but essentially just listen for form.close
      #events and scroll to the top of the form container when the form
      #is closed... it's fragile, but not sure how else to go about it.
      @$on 'form.closed', (e, evt) ->
        targetElem = angular.element(evt.currentTarget).parent().parent()
        $('body').eq(0).animate({scrollTop: targetElem.offset().top - 100}, 'slow')
      
      rescroll()
  }
  
angular.module 'gps.common.layout'
.controller 'TutorialModalController', ($scope) -> as $scope, ->
  #enforce default values if not set in scope
  @index = if @index then @index else 0
  @showNav = if @showNav? then @showNav else true
  @finishedText = if @finishedText? then @finishedText else "Finished"
  @closeText = if @closeText? then @closeText else "Close"

  # check for explicit step
  if @key
    for step,i in @tutorial
      if @key == step.key
        @index = i
        break

  scroll = ->
    #yes, I'm manipulating the dom from a controller, leave me a alone
    $(".modal").eq(0).animate({scrollTop: 0}, 'fast')
    return null

  @next = =>
    if @index < @tutorial.length - 1
      @index++

    scroll()

  @prev = =>
    if @index > 0
      @index--

    scroll()
  
  @getNextText = =>
    return if @tutorial[@index].nextText? then @tutorial[@index].nextText else 'Next'
  
  @getPrevText = =>
    return if @tutorial[@index].prevText? then @tutorial[@index].prevText else 'Prev'

  @finished = => @$close(true)

  @close = => @$close(false)

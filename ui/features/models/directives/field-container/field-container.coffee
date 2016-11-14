angular.module 'gps.common.models'
.directive 'fieldContainer', (
  $popover
) ->
  return {
    restrict: 'E'
    transclude: true
    templateUrl: "/features/models/directives/field-container/field-container.html"
    scope:
      errorPath: '@'
      promise: '=?'
      errorPlacement: '@'
    controller: ($scope) ->
      @getErrorPath = -> $scope.errorPath
      @getErrors = -> $scope.errors
      @setErrors = (errors) -> $scope.errors = errors
    link: (scope, elem, attrs) -> as scope, ->
      @errors = null

      errorPopover = null
      errorPopoverPlacement = if attrs.errorPlacement? then @errorPlacement else 'top'

      @launchErrorPopover = =>
        #ensure other error popovers on this item are removed first
        #before triggering another one

        @dismissErrorPopover()

        errorPopover = $popover(angular.element(elem[0]), {
          template: '/features/models/directives/field-container/errors-popover.html'
          container: 'body'
          trigger: 'manual'
          placement: errorPopoverPlacement
          content: {errors: @errors}
        })
        errorPopover.$promise.then ->
          errorPopover.show()

      @dismissErrorPopover = =>
        if errorPopover
          errorPopover.destroy()

      @$watch 'promise', =>
        return if !@promise

        @promise.success =>
          @errors = null
          @dismissErrorPopover()
        @promise.error (res, status) =>

          #check for WSB validation errors
          if status == 422 && res?.errors?

            errors = _.indexBy res.errors, 'path'

            if errors[@errorPath]?
              @errors = errors[@errorPath].messages
            else
              @errors = _.union _.pluck(res.errors, 'messages')...
              @errors.unshift 'Other validation errors encountered:'

            @launchErrorPopover()
            return

          #catch other errors
          @errors = if res?.response?.message? then [res.response.message] else ["There was an error saving."]
          @launchErrorPopover()
  }


# http://stackoverflow.com/a/17364716/743296
.directive 'blurOnEnter', ->
  return (scope, elem, attrs) ->
    elem.bind "keyup", (e) ->
      if (e.which == 13)
        angular.element(elem[0]).blur()
        e.preventDefault()

.controller 'FieldContainerErrorController', ($scope) -> as $scope, ->
  console.log 'error controller: ', @content

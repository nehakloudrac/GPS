angular.module 'gps.common.models'
.directive 'fieldLabel', ->
  return {
    restrict: 'E'
    transclude: true
    scope:
      requiredWhen: "&"
    template: '''
      <div class="field-label">
        <p class="form-control-static" ng-class="{required: isRequired()}" ng-transclude></p>
      </div>
    '''
    link: (scope, elem, attrs) -> as scope, ->

      @isRequired = =>
        return true if attrs.required?
        
        return if attrs.requiredWhen? then @requiredWhen() else false
  }
  
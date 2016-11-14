# <p ng-show="isIncomplete()">
#   <span class="text-warning">* &nbsp;</span> Denotes a required field
# </p>

angular.module 'gps.profile'
.directive 'editListForm', ->
  return {
    restrict: 'E'
    transclude: true
    scope:
      showWhen: '&'
      cancelWhen: '&'
      doneWhen: '&'
      incompleteWhen: '&'
      onRemove: '&'
      onCancel: '&'
      onDone: '&'
      doneText: '@'
      removeText: '@'
      cancelText: '@'
      nested: '@'
    template: """
      <div class="edit-list-form" ng-if="showWhen()" ng-class="{'editable-field-block editing': nested}">
        <ng-transclude></ng-transclude>
        <div class="form-controls">
          <a class="btn btn-sm btn-default" ng-click="closeForm('remove', $event)" ng-show="!cancelWhen()">{{getRemoveText()}}</a>
          <a class="btn btn-sm btn-default" ng-click="closeForm('cancel', $event)" ng-show="cancelWhen()">{{getCancelText()}}</a>
          <a class="btn btn-sm btn-info" ng-click="closeForm('done', $event)" ng-show="doneWhen()">{{getDoneText()}}</a>
          <span class="btn btn-sm btn-text" ng-show="isIncomplete()">
            <i class="fa fa-fw fa-asterisk text-warning"></i> Denotes a required field
          </span>
        </div>
      </div>
    """
    link: (scope, elem, attrs) -> as scope, ->
      
      @closeForm = (action, evt) =>
        @onDone() if 'done' == action
        @onCancel() if 'cancel' == action
        @onRemove() if 'remove' == action
        
        @$emit 'form.closed', evt
      
      @isIncomplete = =>
        return if attrs.incompleteWhen? then @incompleteWhen() else false
      
      @getDoneText = =>
        return if attrs.doneText? then @doneText else "Done"
      
      @getCancelText = =>
        return if attrs.cancelText? then @cancelText else "Cancel"
      
      @getRemoveText = =>
        return if attrs.removeText? then @removeText else "Remove"

  }

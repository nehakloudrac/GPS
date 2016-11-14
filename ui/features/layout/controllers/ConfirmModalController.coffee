angular.module 'gps.common.layout'
.controller 'ConfirmModalController', ($scope) -> as $scope, ->
  
  @title = if @title? then @title else "Confirm action"
  
  @confirm = => @$close(true)

  @cancel = => @$close(false)
  
  @getCancelText = => return if @cancelText? then @cancelText else 'Cancel'
  @getConfirmText = => return if @confirmText? then @confirmText else 'Confirm'
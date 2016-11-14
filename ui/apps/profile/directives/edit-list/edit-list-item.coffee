angular.module 'gps.profile'
.directive 'editListItem', ->
  return {
    restrict: 'E'
    scope:
      title: '@'
      subTitle: '@'
      onEdit: '&'
      onRemove: '&'
      incompleteWhen: '&'
    templateUrl: "/apps/profile/directives/edit-list/edit-list-item.html"
    link: (scope, elem, attrs) -> as scope, ->
      @isIncomplete = =>
        return if attrs.incompleteWhen? then @incompleteWhen() else false
      
      @canHaveSubtitle = attrs.subTitle?
      
      @hasTitleText = attrs.title? && @title != null && @title != ''
      @hasSubtitleText = attrs.subTitle? && @subTitle != null && @subTitle != ''
      
      @titleText = if @hasTitleText then @title else 'n/a'
      @subTitleText = if @hasSubtitleText then @subTitle else 'n/a'
      
  }

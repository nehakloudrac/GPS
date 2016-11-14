# approach taken from
# http://stackoverflow.com/a/17426614
# 
# This essentially a directive that allows replacing a dom element with another
# directive.  It's similar to `ng-include`, but rather than requiring a template
# url, you can set the template directly as a string, which will then get
# compiled as expected.
angular.module 'gps.common.layout'
.directive 'bindTemplate', ($compile) ->
  return {
    restrict: 'A'
    link: (scope, elem, attrs) ->
      scope.$watch(
        ((scope)->
          return scope.$eval(attrs.bindTemplate)
        ),
        ((val)->
          elem.html(val)
          $compile(elem.contents())(scope)
        )
      )
  }

# TODO: refactor this to use 'ngModel' properly
angular.module 'gps.profile'
.directive 'inputNumMonths', (
  $timeout
) ->
  return {
    restrict: 'E'
    templateUrl: '/apps/profile/directives/input-num-months/input-num-months.html'
    scope:
      model: '='
      onChange: '&'
    link: (scope, elem, attrs) -> as scope, ->
      @numYears = null
      @numMonths = null

      init = =>
        @numYears = null
        @numMonths = null
        if @model?
          @model = parseInt @model, 10
          numYears = Math.floor @model / 12
          numMonths = @model % 12
          @numYears = if numYears > 0 then numYears else null
          @numMonths = if numMonths > 0 then numMonths else null

      @update = =>
        total = 0

        #isFinite checks to guard against NaN

        months = parseInt(@numMonths, 10)
        if isFinite(months)
          total += months

        years = parseInt(@numYears, 10)
        if isFinite(years)
          total += years * 12

        @model = total

        #TODO - refactor to use ng-model properly
        if attrs.onChange?
          $timeout =>
            @onChange()
      
      @$watch 'model', init
      init()
  }

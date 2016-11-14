angular.module 'gps.profile'
.directive 'formMilitary', ->
  return {
    restrict: 'E'
    scope: true
    templateUrl: '/apps/profile/directives/edit-timeline/forms/form-military.html'
    link: (scope, elem, attrs) -> as scope, ->
      
      fields = ['branch','unit','operation','occupationalSpecialties','geographicSpecialty','rankType','rankValue','rankLevel']      
      config = ['militaryGeographicSpecialties','militaryRankTypes','militaryServices','militaryRanks']
      
      init = =>
        @init(fields, config)
        @form.fields['rankValue'].saveDataTransformer = (obj) =>
          item = _.find(@conf.militaryRanks[@event.branch][@event.rankType], {key: @form.fields['rankValue'].value})
          obj.rankValue = item.key
          obj.rankLevel = item.level
          return obj

      @$on 'event.init', init
      init()
  }

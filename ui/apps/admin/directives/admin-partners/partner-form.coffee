angular.module 'gps.admin'
.directive 'partnerForm', (
  Upload
  $http
  $rootScope
  publicFSBaseUrl
) ->
  return {
    restrict: 'E'
    scope:
      partner: '='
    templateUrl: '/apps/admin/directives/admin-partners/partner-form.html'
    link: (scope, elem, attrs) -> as scope, ->
      @publicFSBaseUrl = publicFSBaseUrl

      @promise = null
      
      imageUpdates = 0

      init = =>
        @tags = _.cloneDeep @partner.tags
      
      @getLogoUrl = =>
        if 0 == imageUpdates
          return publicFSBaseUrl + @partner.logoUrl
        
        return publicFSBaseUrl + @partner.logoUrl + "?" + imageUpdates
      
      @updateTags = =>
        @partner.tags = _.pluck(@tags, 'text')

      @upload = (files) =>
        if files && files.length
          file = files[0]

          @promise = Upload.upload({
            url: "/api/partners/#{@partner.id}/logo"
            fileFormDataName: 'file'
            file: file
          })
          .success (res) ->
            imageUpdates++
            $rootScope.$broadcast 'gps.partner', res.partner
          .progress (evt) =>
            @progress = parseInt(100.0 * evt.loaded / evt.total)
          .error (err) ->
            console.log 'ERROR: ', err
        
      
      init()
      
      @$watch 'partner.id', init
  }
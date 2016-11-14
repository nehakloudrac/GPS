class ProfileImageHelper
  constructor: (@profileImagesRoot, @gravatarRoot) ->

  getProfileImageUrl: (user, size = '200') ->
    return "#{@profileImagesRoot}#{user.avatarUrl}" if user.avatarUrl
    return "#{@gravatarRoot}/avatar/#{user.gravatarHash}?d=mm&s=#{size}" if user.preferences.allowGravatar
    return "#{@gravatarRoot}/avatar/#{user.gravatarHash}?f=y&d=mm&s=#{size}"

angular.module 'gps.common.models'
.service 'profileImageHelper', ['profileImagesRoot', 'gravatarRoot', ProfileImageHelper]

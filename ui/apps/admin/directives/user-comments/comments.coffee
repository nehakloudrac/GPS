angular.module 'gps.admin'
.directive 'userComments', (
  $http
) ->
  return {
    restrict: 'E'
    scope:
      user: "="
    templateUrl: '/apps/admin/directives/user-comments/comments.html'
    link: (scope, elem, attrs) -> as scope, ->
      @loadingPromise = null
      @savingPromise = null
      @error = null
      @newComment = ''
      @comments = []
      
      commentsElem = elem.find('.comments')
      
      scroll = ->
        setTimeout ->
          commentsElems = elem.find('.comment')
          if commentsElems.length > 0
            elem.find('.comments').scrollTop(commentsElems.last().offset().top)
      
      @reload = =>
        @loadingPromise = $http.get "/api/admin/users/#{@user.id}/comments"
        .success (res) =>
          @comments = _.sortBy res.comments, 'dateCreated'
          @error = null
          scroll()
        .error (err) =>
          if err.response?.message?
            @error = err.response.message
      
      @submit = =>
        comment = @newComment.trim()
        
        return if comment.length == 0
        
        @savingPromise = $http.post "/api/admin/users/#{@user.id}/comments", { text: comment }
        .success (res) =>
          @error = null
          @newComment = null
          @reload()
        .error (res) =>
          if err.response?.message?
            @error = err.response.message
      
      @deleteComment = (commentId) =>
        @loadingPromise = $http.delete "/api/admin/users/#{@user.id}/comments/#{commentId}"
        .success =>
          @error = null
          @reload()
        .error (res) =>
          if err.response?.message?
            @error = err.response.message
          
          
      @reload()
  }
  
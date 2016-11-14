gulp          = require "gulp"
gutil         = require "gulp-util"
es            = require "event-stream"
livereload    = require "gulp-livereload"
iconfont      = require "gulp-iconfont"
consolidate   = require "gulp-consolidate"

buildApp      = require "./tasks/buildApp.coffee"
buildPublic   = require "./tasks/buildPublic.coffee"

UI_APPS = [
  'profile'
  'admin'
  'dashboard'
  'resources'
  'account'
]

VENDOR = [
  'node_modules/font-awesome/fonts/*.*'
  'node_modules/bootstrap/fonts/*.*'
]

buildApps = (devMode) ->
  streams = []

  streams.push(buildApp(appName, devMode)) for appName in UI_APPS

  stream = es.merge(streams...)
  stream.on 'end', livereload.reload

  return stream

gulp.task 'icons', ->
  return gulp.src ['icons/*.svg']
    .pipe iconfont({
      fontName: 'gps-icon'
      appendUnicode: true
      formats: ['ttf','eot','woff']
      timestamp: Math.round(Date.now()/1000)
    })
      .on 'glyphs', (glyphs, options) ->
        gulp.src 'icons/gps-icons.css'
          .pipe consolidate('lodash', {
            glyphs: glyphs
            fontName: 'gps-icon'
            fontPath: ''
            className: 'g'
          })
          .pipe gulp.dest 'server/web/icons'
    .pipe gulp.dest 'server/web/icons'

###
# build tasks
###
gulp.task 'build:apps:prod', ->
  buildApps false

gulp.task 'build:apps:dev', ->
  buildApps true

#define individual dev build tasks for each app
(->
  for app in UI_APPS
    do (app) ->
      gulp.task "build:app:dev:#{app}", -> return buildApp(app, true).on('end', livereload.reload)
)()

#define individual watch tasks for each app
(->
  for app in UI_APPS
    do (app) ->
      gulp.task "watch:app:#{app}", ["build:app:dev:#{app}", 'copy'], ->
        livereload.listen()
        gulp.watch ["ui/apps/#{app}/**/*",'ui/features/**/*'], ["build:app:dev:#{app}"]
)()

gulp.task 'build:public:dev', ->
  stream = buildPublic true
  stream.on 'end', livereload.reload
  return stream

gulp.task 'build:public:prod', ->
  buildPublic false

gulp.task 'copy', ->
  gulp.src VENDOR, base: './'
  .pipe gulp.dest 'server/web/vendor/'

###
# Watch tasks
###
gulp.task 'watch:apps', ['build:apps:dev', 'copy'], ->
  livereload.listen()

  gulp.watch ['ui/apps/**/*','ui/features/**/*'], ['build:apps:dev']

gulp.task 'watch:public', ['build:public:dev','copy'], ->
  livereload.listen()

  gulp.watch 'public/**/*', ['build:public:dev', 'copy']

#kill the process with error code if something fails globally
gulp.on 'error', -> process.exit 1

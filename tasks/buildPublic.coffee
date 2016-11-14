###
# This file is for compiling the public assets used for the static public pages.
#
# I went this route instead of using Assetic via Symfony because we're already using gulp for the UI apps.
###

gulp          = require "gulp"
gutil         = require "gulp-util"
sourcemaps    = require "gulp-sourcemaps"
concat        = require "gulp-concat"
less          = require "gulp-less"
coffee        = require "gulp-coffee"
uglify        = require "gulp-uglify"
order         = require "gulp-order"
es            = require "event-stream"

logpaths      = require "./logpaths.coffee"

module.exports = buildPublic = (devMode) ->
  ROOT = __dirname + '/../'

  COPY = [
    'node_modules/font-awesome/fonts/*.*'
    'public/images/**/*'
    'public/fonts/**/*'
  ]

  JS = [
    'node_modules/jquery/dist/jquery.js'
    'node_modules/angular/angular.js'
    'node_modules/bootstrap/dist/js/bootstrap.js'
    'node_modules/select2/dist/js/select2.js'
  ]

  JS_ORDER = [
    'jquery.js'
    'angular.js'
    'bootstrap.js'
    'select2.js'
    'app.js'
  ]

  jsStreams = []
  jsStreams.push((gulp.src JS)
    .pipe if devMode then sourcemaps.init() else gutil.noop()
  )

  jsStreams.push(gulp.src("#{ROOT}/public/**/*.coffee")
    .pipe (if devMode then sourcemaps.init() else gutil.noop())
    .pipe coffee bare: true
      .on 'error', gutil.log
    .pipe concat "app.js"
  )

  jsStream = (es.merge(jsStreams...)
    .pipe order JS_ORDER
    #.pipe logpaths()
    .pipe concat "app.js"
    .pipe (if !devMode then uglify mangle: false else gutil.noop())
    .pipe (if devMode then sourcemaps.write() else gutil.noop())
  )

  cssStream = ((gulp.src "#{ROOT}/public/styles.less")
    .pipe less()
      .on 'error', gutil.log
    #TODO: minify
  )

  copyStream =((gulp.src COPY, base: './')
    .pipe gulp.dest 'server/web/'
  )

  return es.merge(
    copyStream,
    es.merge(jsStream, cssStream).pipe gulp.dest("#{ROOT}/server/web/public")
  )

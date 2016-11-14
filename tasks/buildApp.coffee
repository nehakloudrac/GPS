###
# This task is for building a specific angular app defined in  ui/apps/{appName}
###

gulp          = require "gulp"
gutil         = require "gulp-util"
sourcemaps    = require "gulp-sourcemaps"
concat        = require "gulp-concat"
less          = require "gulp-less"
autoprefixer  = require 'gulp-autoprefixer'
coffee        = require "gulp-coffee"
uglify        = require "gulp-uglify"
order         = require "gulp-order"
templateCache = require "gulp-angular-templatecache"
jade          = require "gulp-jade"
ngAnnotate    = require "gulp-ng-annotate"
es            = require "event-stream"
fs            = require "fs"
path          = require "path"
glob          = require "glob"

logpaths      = require "./logpaths.coffee"


sources = (dirs, pattern) ->
  dirs.map (dir) ->
    dir + pattern

buildApp = (appName, devMode) ->

  buildSettings = JSON.parse fs.readFileSync "#{__dirname}/../ui/apps/#{appName}/build.json"
  destDir = "#{__dirname}/../server/web/apps/#{appName}"
  appSrcDirs = buildSettings.features.map (feature) -> "ui/features/#{feature}"
  appSrcDirs.push "ui/apps/#{appName}"

  jsStreams = []
  taskStreams = []

  #the js streams have a tendency to lose their file order
  appFileOrder = [
    "ui/features/*/*.js"
    "ui/features/*/**/*.js"
    "ui/apps/#{appName}/app.js"
    "ui/apps/#{appName}/**/*.js"
  ]
  jsFileOrder = buildSettings.vendorScripts[..] #copies the array
  jsFileOrder.push 'app.js'
  jsFileOrder.push 'templates.js'

  # get vendor js
  jsStreams.push ((gulp.src buildSettings.vendorScripts, { base: "./" })
    .pipe (if devMode then sourcemaps.init() else gutil.noop())
  )

  #compile/concat all app/feature coffee
  jsStreams.push((gulp.src sources(appSrcDirs, '/**/*.coffee'), { base: "./" })
    .pipe (if devMode then sourcemaps.init() else gutil.noop())
    .pipe coffee bare: true
      .on 'error', gutil.log
    .pipe order appFileOrder
    # .pipe logpaths()
    .pipe concat 'app.js'
  )

  #convert templates to html then js
  templateBase = path.normalize "#{__dirname}/../ui"
  jsStreams.push((gulp.src sources(appSrcDirs, '/**/*.jade'), { base: templateBase })
    .pipe jade pretty: false
      .on 'error', gutil.log
    .pipe templateCache 'templates.js', standalone: true, module: 'gps.templates'
    .pipe (if devMode then sourcemaps.init() else gutil.noop())
  )

  #concat and write all js
  taskStreams.push(es.merge(jsStreams...)
    .pipe order jsFileOrder
    # .pipe logpaths()
    .pipe concat 'app.js'
    .pipe (if !devMode then ngAnnotate() else gutil.noop() )
    .pipe (if devMode then sourcemaps.write() else gutil.noop())
    .pipe (if !devMode then uglify(mangle: true) else gutil.noop())
      .on 'error', gutil.log
    .pipe gulp.dest destDir
  )

  #compile all less styles
  taskStreams.push((gulp.src "./ui/apps/#{appName}/styles.less")
    #TODO: recess lint
    .pipe less()
      .on 'error', gutil.log
    .pipe autoprefixer {browsers: ['last 2 versions'], cascade: false}
    #TODO: minify
    .pipe gulp.dest destDir
  )

  return es.merge(taskStreams...)

module.exports = buildApp

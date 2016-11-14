gutil       = require 'gulp-util'
through     = require 'through2'

#for debugging file order
module.exports = logFilePaths = ->
  log = (file, enc, cb) ->
    gutil.log file.relative
    cb null, file
  through.obj log

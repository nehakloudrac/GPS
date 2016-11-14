###*
 * Convenience methods for getting labels to display from various config objects.
###
class Labeler

  constructor: (config) ->
    @langCodes = config.get 'languageCodes'

  getLabel: (items, key, keyField='key', labelField='label') ->
    return '' if !key
    if !items
      console.error 'no items to search in labeler'
      return ''

    search = {}
    search[keyField] = key

    item = _.find(items, search)
    if !item
      console.error "No key field found searching for #{keyField}:#{key} in:", items
      #returning the key to prevent breaking elsewhere
      return key

    label = if item[labelField]? then item[labelField] else null

    if !label
      console.error "No label field found for #{labelField} with #{keyField}:#{key} in #{items.length} items:"

    return label

  getLabels: (items, keys, keyField='key', labelField='label', asArray = false) ->
    return '' if !keys

    labels = (@getLabel(items, key, keyField, labelField) for key in keys)
    return if asArray then labels else labels.join ', '

  #NOTE: this is legacy stuff... replace calls to this at some point
  getLanguageLabel: (lang) ->
    return _.find(@langCodes, { code: lang.code }).label


angular.module 'gps.common.models'
.service 'labeler', ['config', Labeler]

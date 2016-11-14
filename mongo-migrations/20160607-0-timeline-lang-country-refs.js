/**
 * Update all profiles by initializing new fields for language and country
 * references in timeline events.
 *
 * Note that this does not remove old data.  That should be addressed in a separate migration.
 */

print("Migrating lang country refs.");
var count = 0;
db.candidateProfiles.find({}).forEach(function(doc) {  
  
  if (doc.timeline && doc.timeline.length > 0) {
    var modified = false;
    
    for (var i in doc.timeline) {
      var evt = doc.timeline[i];
      
      // return early if this doc has already been migrated
      if (evt.languageRefs && evt.languageRefs.length > 0) { continue; }
      if (evt.countryRefs && evt.countryRefs.length > 0) { continue; }
      
      // migrate old language ref sub documents
      if (evt.languageReferences && evt.languageReferences.length > 0) {
        var lRefs = [];
        for (var i in evt.languageReferences) {
          lRefs.push(evt.languageReferences[i].code);
        }
        evt.languageRefs = lRefs;
        delete evt.languageReferences;
        modified = true;
      }
      
      // migrate old country ref sub documents
      if (evt.countryReferences && evt.countryReferences.length > 0) {
        var cRefs = [];
        for (var j in evt.countryReferences) {
          cRefs.push(evt.countryReferences[j].code);
        }
        evt.countryRefs = cRefs;
        delete evt.countryReferences;
        modified = true;
      }
    }
   
    if (modified) {
      count++;
      db.candidateProfiles.save(doc);
      print("Updated profile doc ["+doc._id+"]");
    }
  }
});

print("Updated "+count+" doc.");
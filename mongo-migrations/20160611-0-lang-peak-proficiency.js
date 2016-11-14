/**
 * Update documents by setting general peak proficiency value...
 * using first available field from prioritized list as proxy for
 * a general value
 */

print("Migrating lang peak proficiency");
var fields = [
  'interactingPeak',
  'listeningPeak',
  'readingPeak',
  'writingPeak',
  'presentingPeak',
  'socialPeak'
];

var count = 0;

db.candidateProfiles.find({}).forEach(function(doc) {
  
  if (doc.languages && doc.languages.length > 0) {
    var modified = false;
    
    for (var i in doc.languages) {
      var lang = doc.languages[i];
      
      if (lang.selfCertification && !lang.selfCertification.peakProficiencyLevel) {
        var set = false;
        for (var j in fields) {
          if (lang.selfCertification[fields[j]] > 0) {
            lang.selfCertification.peakProficiencyLevel = lang.selfCertification[fields[j]];
            set = true;
            modified = true;
          }
          
          if (set) {
            break;
          }
        }
      }
      
    }
    
    if (modified) {
      count++;
      db.candidateProfiles.save(doc);
    }
  }
});

print("Updated "+count+" docs");
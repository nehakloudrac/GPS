/**
 * Removes 'presenting/presentingPeak' as a field in lang self certifications
 */

var cursor = db.candidateProfiles.find(
  {
    $or: [
      {'languages.selfCertification.presenting': {$exists: true}},
      {'languages.selfCertification.presentingPeak': {$exists: true}}
    ]
  }
);
print(cursor.count(), "PRE-migration docs with language.selfCertification.presenting/presentingPeak");

cursor.forEach(function(doc) {
  
  // remove presenting fields from each
  // nested language where present
  for (var i in doc.languages) {
    var lang = doc.languages[i];

    if (lang.selfCertification && lang.selfCertification.presenting) {
      delete lang.selfCertification.presenting;
    }
    if (lang.selfCertification && lang.selfCertification.presentingPeak) {
      delete lang.selfCertification.presentingPeak;
    }
  }
  
  db.candidateProfiles.save(doc);
  print("Updated doc ["+doc._id+"]");
});

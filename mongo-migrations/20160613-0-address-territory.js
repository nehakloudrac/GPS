/**
 * If address country is not US, territory should be null (for now)
 */

// migrate user addresses

print("migrating address.territory");

var userCount = 0;
db.users.find({}).forEach(function(doc) {
  if (doc.address && doc.address.countryCode != 'US') {
    doc.address.territory = null;
    delete doc.address.territory;
    
    userCount++;
    print('Updated user ['+doc.email+']');
    db.users.save(doc);
  }
});
print("Updated "+userCount+" user docs.");

// migrate institution addresses in timeline

var profCount = 0;
db.candidateProfiles.find({}).forEach(function(doc) {
  var modified = false;

  if (doc.timeline && doc.timeline.length > 0) {
    
    for (var i in doc.timeline) {
      var evt = doc.timeline[i];
      if (evt.institution && evt.institution.address && evt.institution.address.countryCode != 'US') {
        modified = true;

        evt.institution.address.territory = null;
        delete evt.institution.address.territory;
        profCount++;
        print('Doc ['+doc._id+'] evt ['+evt.hash+']');
      }
    }
  }
  
  if (modified) {
    db.candidateProfiles.save(doc);
    print('Updated profile ['+doc._id+']');
  }
});

print("Updated "+profCount+" profile docs.");

/**
 * Migrates `User.usSecurityClearance` field from boolean to 
 * equivalent string value
 */

db.users.find({'usSecurityClearance': {$type: 8}}).forEach(function(doc) {
  
  if (true === doc.usSecurityClearance) {
    doc.usSecurityClearance = String("yes");
    print("Changed doc:", doc._id, doc.usSecurityClearance);
  }
  
  if (false === doc.usSecurityClearance) {
    doc.usSecurityClearance = String("no");
    print("Changed doc:", doc._id, doc.usSecurityClearance);
  }
  
  db.users.save(doc);
});

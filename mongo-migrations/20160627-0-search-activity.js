/**
 * If users prefs don't contain an entry for allowing searchActivityEmails, then
 * add it and initialize it to true
 */
var count = 0;
db.users.find({
  'preferences.allowSearchActivityEmails': {$exists: false}
}).forEach(function (doc) {
  print("Updated "+doc._id);
  db.users.update({_id: doc._id}, {$set:{'preferences.allowSearchActivityEmails': true}});
  count++;
});

print("Updated "+count+" docs.");

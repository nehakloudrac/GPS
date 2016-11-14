/**
 * Migrating from bullet point structure to general string approach for descriptions of timeline events.
 */
var count = 0;

var bulletPoint = String.fromCharCode(parseInt('2022', 16));

var convertBulletPoint = function (string) {
  str = string.trim();
  if (str.length > 0 && str[0] == bulletPoint) {
    return str;
  }
  
  return bulletPoint + " "+ str;
};

db.candidateProfiles.find({}).forEach(function (doc) {
  var modified = false;
  if (doc.timeline && doc.timeline.length > 0) {
    doc.timeline.forEach(function(evt) {
      if (evt.description && evt.description.length > 0) {
        return;
      }

      if (evt.activities && evt.activities.length > 0) {
        modified = true;
        evt.description = evt.activities.map(convertBulletPoint).join("\n");
      }
    });
  }
  
  if (modified) {
    db.candidateProfiles.save(doc);
    print("Updated "+doc._id);
    count++;
  }
});

print("Updated "+count+" docs.");

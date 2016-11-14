/**
 * New process requires that all users see each profile section at least once before continuing on to their dashboard.
 */

var count = 0;

// map users info by profile id
var userProfileMap = {};
db.users.find({},{email:true, status:true, candidateProfile:true}).forEach(function (doc) {
  userProfileMap[doc.candidateProfile] = doc;
});

// get profiles
db.candidateProfiles.find({
  'profileStatus.introCompleted':{$exists:false}
}).forEach(function (profile) {
  var user = userProfileMap[profile._id];
  if (!user) {
    print("User not found for profile: "+profile._id);
    return;
  }
  
  var introCompleted = false;
  
  // didn't finish the short form
  if (!profile.shortForm || !profile.shortForm.completed) {
    introCompleted = false;
  }
  
  // saw the short form, but didn't start the actual profile
  if (
    profile.shortForm && 
    true === profile.shortForm.completed && 
    (!user.status || !user.status.seenProfileViewTutorial)
  ) {
    introCompleted = false;
  }
  
  // saw the short form, and did start the profile at some point
  if (
    profile.shortForm &&
    true === profile.shortForm.completed &&
    user.status &&
    true === user.status.seenProfileViewTutorial
  ) {
    introCompleted = true;
  }
  
  db.candidateProfiles.update({_id: profile._id}, {$set:{'profileStatus.introCompleted': introCompleted}});
  print("Updated doc: "+user.email+": "+introCompleted);
  count++;
});

print("Updated "+count+" profile docs.");

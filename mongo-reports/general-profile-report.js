rs.slaveOk();

/*
# verified
% of completeness of profiles on average
# of searches for candidates or range
* Were any of them in our recent searches?
*/

// arbitrary criteria by which to find profiles
var profileCriteria = {
  'awards.name': {$regex: /gilman/i}
};

// arbitrary criteria by which to find users (will include matched profiles)
var userCriteria = {};

// build a map of profiles
var profileMap = {};
var profilesCursor = db.candidateProfiles.find(profileCriteria);
var profIds = [];
profilesCursor.forEach(function(doc) {
  profIds.push(doc._id);
  profileMap[doc._id.toString()] = doc;
});

print("Num total: "+profilesCursor.count());

var userMap = {};
userCriteria.candidateProfile = {$in: profIds};
var usersCursor = db.users.find(userCriteria);
var countVerified = 0;
var countCompletedShortForm = 0;
var countProfileSearches = 0;


usersCursor.forEach(function(doc) {
  userMap[doc._id.toString()] = doc; 
  // printjson(profileMap[doc.candidateProfile.toString()]);

  if (doc.isVerified) {
    countVerified++;
  }  
  
  if (profileMap[doc.candidateProfile.toString()].shortForm.completed) {
    countCompletedShortForm++;
  }

  if (doc.tracker && doc.tracker.profileSearchHitsTotal && doc.tracker.profileSearchHitsTotal > 0) {
    countProfileSearches += doc.tracker.profileSearchHitsTotal;
  }
});

print("Num verified emails: " + countVerified);
print("Num completed short form: " + countCompletedShortForm);
print("Num avg search hits/user: " + countProfileSearches / usersCursor.count());

// generate completeness map of matched profiles/users
var bounds = [
  [0,5],
  [5,15],
  [15, 25],
  [25,50],
  [50,75],
  [75,90],
  [90,100]
];

for (i in bounds) {
  var low = bounds[i][0];
  var high = bounds[i][1];
  
  var res = db.candidateProfiles.find({
    _id: {$in: profIds},
    "profileStatus.completeness.percentCompleted": {$gte: low, $lt: high}
  });

  print("Completed >= "+low+"% & < "+high+"%: "+res.count());
}


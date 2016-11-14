rs.slaveOk();

var referrer = "aifs";

var profIdsCursor = db.users.find({institutionReferrer: referrer}, {_id:true, candidateProfile:true});
var profIds = [];
profIdsCursor.forEach(function(doc) {
  profIds.push(doc.candidateProfile);
});

print("Total: "+profIdsCursor.count());

var res = db.users.find({
  institutionReferrer: referrer,
  isVerified: true
});
print("Verified: "+res.count());

var res = db.candidateProfiles.find({
  _id: {$in: profIds},
  "shortForm.completed": true
});
print("Submitted short form: "+res.count());

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


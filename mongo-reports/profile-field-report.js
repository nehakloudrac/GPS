// Check fields in profile for response rate
rs.slaveOk();

// check profiles for users matching the following criteria
var search = {
  'shortForm.completed': true
};

// check the following fields
var fields = [
  'hardSkills.accounting',
  'hardSkills.clientManagement',
  'hardSkills.contractNegotiation',
  'hardSkills.eventPlanning',
  'hardSkills.financialAnalysis',
  'hardSkills.fundraising',
  'hardSkills.marketing',
  'hardSkills.projectManagement',
  'hardSkills.reportWriting',
  'hardSkills.publicRelations',
  'hardSkills.publicSpeaking',
  'hardSkills.research',
  'hardSkills.staffManagement',
  'hardSkills.socialMedia',
  'hardSkills.writtenCommunication'
];

// only count number out of people who actually completed short form...
// update this when process changes
var total  = db.candidateProfiles.find({}).count();
var checked = db.candidateProfiles.find(search).count();

var resMap = {};

for (var i in fields) {
  var query = Object.create(search);
  query[fields[i]] = {$exists:true};
  
  resMap[fields[i]] = (db.candidateProfiles.find(query).count()/checked) * 100;
}

print("Checked "+checked+" of "+total);
printjson(resMap);

throw new Error("NOT FINISHED... query is off");

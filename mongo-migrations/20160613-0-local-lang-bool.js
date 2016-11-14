print("migrating country local lang slider to bool");

var fields = [
  'attendClassesLocalLang',
  'modifyCurriculumLocalLang',
  'taughtLocalsLocalLang',
  'commandUnitLocalLang',
  'persuadeLocalsProductLocalLang',
  'relationshipsLocalGovLocalLang',
  'collaborateWithLocalsLocalLang',
  'interfaceWithProfessionalsLocalLang',
  'negotiateContractsLocalLang',
  'manageTeamLocalLang',
  'navLegalReqsLocalLang',
  'navFinancialReqsLocalLang',
  'accommodateValuesLocalLang',
  'professionalInterpretationLocalLang',
  'professionalTranslationLocalLang',
  'localTraditionLocalLang',
  'convinceLocalsLocalLang',
  'talkToMediaLocalLang',
  'publicPresentationsLocalLang',
  'navLocalRegsLocalLang',
  'performanceConsequencesLocalLang',
  'leadProjectLocalLang',
  'socializeWithLocalsLocalLang',
  'useSocialMediaLocalLang',
  'writeReportsLocalLang'
];

var count = 0;
db.candidateProfiles.find({}).forEach(function(doc) {
  var modified = false;
  
  if (doc.countries && doc.countries.length > 0) {
    for (var i in doc.countries) {
      var country = doc.countries[i];
      if (country.activities) {
        fields.forEach(function (name) {
          if (country.activities[name] && country.activities[name] > 1) {
            modified = true;
            country.activities[name+'Bool'] = true;
          }
        });
      }
    }
  }
  
  if (modified) {
    count++;
    db.candidateProfiles.save(doc);
    print('Updated doc '+doc._id);
  }
});

print('Updated '+count+' doc.');

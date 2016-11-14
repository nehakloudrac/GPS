// prefill partners collection with data from old config

var currentDate = new ISODate();

var partners = [
    
  // is referrer, not public partner
  { key: 'aata', name: "American Association of Teachers of Arabic", isReferrerLinkEnabled: true },
  { key: 'aatf', name: "American Association of Teachers of French", isReferrerLinkEnabled: true },
  { key: 'aatj', name: "American Association of Teachers of Japanese", isReferrerLinkEnabled: true },
  { key: 'aatseel', name: "American Association of Teachers of Slavic and East European Languages", isReferrerLinkEnabled: true },
  { key: 'actfl', name: "American Councils of Teachers of Foreign Language", isReferrerLinkEnabled: true },
  { key: 'actr', name: "American Council of Teachers of Russian", isReferrerLinkEnabled: true },
  { key: 'byu', name: "Brigham Young University", isReferrerLinkEnabled: true },
  { key: 'dli', name: "Defense Language Institute", isReferrerLinkEnabled: true },
  { key: 'miguel-marling', name: "Miguel Marling", isReferrerLinkEnabled: true },
  { key: 'flagship-portuguese-uga', name: "Portuguese Flagship Program, UGA", isReferrerLinkEnabled: true },

  // both referrer and public partner
  { key: 'aatg', name: "American Association of Teachers of German", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.aatg.org/' },
  { key: 'aati', name: "American Association of Teachers of Italian", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://aati-online.org/' },
  { key: 'aatsp', name: "American Association of Teachers of Spanish and Portuguese", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.aatsp.org/' },
  { key: 'aifs', name: "American Institute For Foreign Study", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.aifsabroad.com/' },
  { key: 'faoa', name: "Foreign Area Officers Association", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.faoa.org/' },
  { key: 'iie', name: "Institute of International Education", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.iie.org/' },
  { key: 'miis', name: "Middlebury Institute of International Studies at Monterey", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.miis.edu/' },
  { key: 'ncolctl', name: "National Council of Less Commonly Taught Languages", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.ncolctl.org/' },
  { key: 'npca', name: "National Peace Corps Alumni Association", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.peacecorpsconnect.org/' },
  { key: 'ciber', name: "The Centers for International Business Education & Research", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://ciberweb.msu.edu/' },
  { key: 'ur', name: "University of Richmond", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.richmond.edu/' },
  { key: 'nyu', name: "New York University", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.nyu.edu/' },
  { key: 'boren', name: "Boren Forum Inc.", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://www.borenforum.org/' },
  { key: 'geovisions', name: "GeoVisions", isReferrerLinkEnabled: true, isPublicPartner: true, url: 'http://geovisions.org/' },
    
  // is public partner, not referrer
  { key: 'tlc', name: 'Texas Language Center', url: 'http://www.utexas.edu/cola/tlc/', isPublicPartner: true },
  { key: 'iie-gsa', name: 'Generation Study Abroad', url: 'http://www.iie.org/programs/generation-study-abroad', isPublicPartner: true },
  { key: 'flagship', name: 'The Language Flagship', url: 'http://thelanguageflagship.org/', isPublicPartner: true },
  { key: 'eaccny', name: 'European American Chamber of Commerce', url: 'http://www.eaccny.com/', isPublicPartner: true },
  { key: 'jncl', name: 'Joint National Committee for Languages', url: 'http://www.languagepolicy.org/', isPublicPartner: true }
].map(function(item) {
  item.isEnabled = true;

  return item;
});

existingPartners = db.partners.find({}).toArray();

var count = 0;
for (var i in partners) {
  partner = partners[i];
  
  // ensure no duplicate
  var exists = false;
  for (var j in existingPartners) {
    var existing = existingPartners[j];
    if (partner.key == existing.key) {
      exists = true;
    }
  }
  
  if (!exists) {
    db.partners.save(partner);
    count++;
  }
}

print(count + " docs added to partners collection");

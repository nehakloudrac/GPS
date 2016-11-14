/**
 * Converts updates persian lang codes to include macrocode where necessary.
 *
 * Note, to be strict with ISO 693-3, code PER should be converted to PES; however, since the two
 * standards are supposed to be backwards compatible, leaving PER as is, and simply adding the FAS
 * macrocode is enough to resolve the issue without causing new conflicts, or requiring a more
 * difficult migration.
 */

var profileCursor = db.candidateProfiles.find(
  {
    'languages.code': 'per'
  }
);

print(profileCursor.count(), "PRE-migration profile docs with persian language");

profileCursor.forEach(function(doc) {
  
  // TODO: add macrocode "fas" to all items with "per" microcode
  for (var i in doc.languages) {
    var lang = doc.languages[i];

    if ('per' == lang.code) {
      lang.macroCode = 'fas';
    }
  }
  
  db.candidateProfiles.save(doc);

  print("Updated profile doc ["+doc._id+"]");
});

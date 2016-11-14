// NOTE: this migration contains initialization for several fields that
// may not have been initialized in all documents, depending on when they
// were added... it should be safe to run repeatedly

/**
 * Initializes `User.preferences.allowProfileHealthEmails` field to true
 */
db.users.update(
  {'preferences.allowProfileHealthEmails': { $exists: false }},
  {$set: { 'preferences.allowProfileHealthEmails': true }},
  {multi: true}
);

/**
 * Initialize User.status if it doesn't exist yet
 */
 db.users.update(
   {'status': {$exists: false}},
   {$set: {'status': {}}},
   {multi: true}
 );
 
/**
 * Initialize `User.emailHistory` to empty collection if not present
 */
db.users.update(
  {'emailHistory': {$exists: false}},
  {$set: {'emailHistory': []}},
  {multi: true}
);

/**
 * Initialize `Candidate\Profile.profileStatus.completeness` is not present
 */
db.candidateProfiles.update(
  {'profileStatus.completeness': {$exists: false}},
  {$set: {'profileStatus.completeness': {}}},
  {multi: true}
);
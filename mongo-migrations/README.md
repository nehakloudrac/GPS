# Migrations #

This is obviously a temporary solution... but is what it is.

Each migration is a javascript file that executes whatever needs to happen.  Each is 
named in the format `<date>-<index>-<name>`.

The index number is to keep order straight for migrations created on the same day.

Each migration should do one specific thing, and be documented.

To run a migration:

* backup the database first w/ the `aws-prod-backup.yml` playbook.
* lock site in maintenance mode (todo)
* scp migration files onto the primary server
* run the migration file on the primary server: `mongo gps /path/to/migration-file.js`
* unlock site from maintenance mode


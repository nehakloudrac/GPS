<?php

namespace GPS\AppBundle\Event;

/**
 * Declarations for all application-level events fired.  This allows extension points to be added
 * at most important places with little coupling.
 *
 * @package default
 * @author Evan Villemez
 */
final class AppEvents
{
    /**
     * Fires when accounts are created via the registration form.
     *
     * Receives instance of UserEvent.
     */
    const USER_REGISTERED = 'user.registered';

    /**
     * Fires when user has verified their email address.  Note that this
     * happens after user registration, but ALSO when a user changes
     * their email address.
     *
     * Receives instance of UserEvent.
     */
    const USER_EMAIL_VERIFIED = 'user.email.verified';

    /**
     * Fires when any contact related information has changed about a user.  This
     * includes address, phone number, and email.
     *
     * Receives instance of UserEvent.
     */
    const USER_CONTACT_CHANGED = 'user.contact.changed';

    /**
     * Generic event for any time a user is modified.
     *
     * Receives instance of UserEvent.
     */
    const USER_MODIFIED = 'user.modified';

    /**
     * Fires when a user is deleted.  Note that this event fires
     * after the user has been removed from the database.
     *
     * Note that this entails actual removal from the database entirely, not
     * simply marked as removed and de-personalized.
     *
     * Receives instance of UserEvent.
     */
    const USER_DELETED = 'user.deleted';
    
    /**
     * Fires when a user is marked as removed, triggering de-personalization
     * of any identifying information.  We retain some data that we use for
     * analytics, and some references may persist - but the original user
     * should not be personally identifiable at this point.
     *
     * Receives instance of UserEvent.
     */
    const USER_REMOVED = 'user.removed';

    /**
     * Fires when a candidate's short form has been marked as complete.
     *
     * Receives instance of ProfileEvent
     */
    const CANDIDATE_SHORT_FORM_COMPLETED = 'candidate_profile.short_form_completed';
    
    /**
     * Fires when a submission comes in from the employer contact form.
     *
     * Receives isntance of EmployerContactEvent
     */
    const EMPLOYER_CONTACT_CREATED = 'employer.contact_created';
}

<?php

namespace GPS\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AC\WebServicesBundle\Exception\ValidationException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Request;
use GPS\AppBundle\Document;
use GPS\AppBundle\Event\AppEvents;
use GPS\AppBundle\Event\UserEvent;

/**
 * Provides all the user api related actions under /user
 *
 * @package GPS
 * @author Evan Villemez
 *
 * @Route("/api/users")
 */
class UsersController extends AbstractApiController
{
    /**
     * Filter lists of users by criteria.
     *
     * @Route("/", name="api-get-users")
     * @Method("GET")
     */
    public function indexUsersAction(Request $req)
    {
        throw $this->createHttpException(501, "Not yet implemented.");
    }

    /**
     * Create a new user.  This may not ever be enabled, but is here for completeness.
     *
     * @Route("/", name="api-post-users")
     * @Method("POST")
     */
    public function postUsersAction(Request $req)
    {
        throw $this->createHttpException(501, "Not yet implemented.");
    }

    /**
     * Retrieve an individual user.
     *
     * @Route("/{id}", name="api-get-user")
     * @Method("GET")
     */
    public function getUserAction(Request $req, $id)
    {
        $user = $this->getRequestedUser($id, true);

        $groups = ['Default'];
        if ($id == $this->getUser()->getId()) {
            $groups[] = 'User.email';
            $groups[] = 'User.phone';
        }

        $ctx = $this->createSerializationContext()
            ->setGroups($groups);

        return $this->createServiceResponse(['user' => $user], 200, [], $ctx);
    }

    /**
     * Modify user info.
     *
     * @Route("/{id}", name="api-put-user")
     * @Method("PUT")
     */
    public function putUserAction(Request $req, $id)
    {
        $dm = $this->getDocumentManager();
        
        //get the user to modify
        $modifiedUser = $this->getRequestedUser($id);

        $groups = ['Default'];
        if ($id == $this->getUser()->getId()) {
            $groups[] = 'User.email';
            $groups[] = 'User.phone';
        }

        //deserialize the incoming data into the user
        $this->decodeRequest($req, 'GPS\AppBundle\Document\User', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups($groups)
            ->setTarget($modifiedUser)
        );

        //validate it
        $this->validate($modifiedUser);

        //save it
        $dm->flush();

        //notify app of modified user
        $this->get('event_dispatcher')->dispatch(AppEvents::USER_MODIFIED, new UserEvent($modifiedUser));

        //return it
        $ctx = $this->createSerializationContext()
            ->setGroups($groups);
        return $this->createServiceResponse(['user' => $modifiedUser], 200, [], $ctx);
    }

    /**
     * Update the users email address.  Will trigger the email verification process.
     *
     * This route requires JSON input.
     *
     * @Route("/{id}/email", name="api-put-user-email")
     * @Method("PUT")
     */
    public function putUserEmailAction(Request $req, $id)
    {
        $user = $this->getRequestedUser($id);

        $data = $this->decodeRawJsonRequest($req, ['email']);
        $ctx = $this->createSerializationContext()->setGroups(['Default', 'User.email']);

        $newEmail = strtolower(trim($data['email']));
        $oldEmail = strtolower($user->getEmail());
        
        // ensure it's not emtpy
        if ('' === $newEmail) {
            throw $this->createHttpException(422, "Email address cannot be empty.");
        }

        //ensure the address actually changed
        if($newEmail === $oldEmail) {
            throw $this->createHttpException(422, "This is the same email address.");
        }
        
        // ensure it's actually a valid email address string
        $errors = $this->get('validator')->validate($newEmail, new Constraints\Email());
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
        
        // ensure that email is not already in use
        $preExistingUser = $this->getRepository('AppBundle:User')->findBy(['email' => $newEmail]);
        if ($preExistingUser) {
            throw $this->createHttpException(422, "That email address is already in use.");
        }

        //update user and save
        $user->setEmail($newEmail);
        $user->setIsVerified(false);
        $this->getDocumentManager()->flush();
        
        // log this for posterity
        $this->get('monolog.logger.gps_app')->info(sprintf(
            "Changed email address for User(%s) from [%s] to [%s].",
            $user->getId(),
            $oldEmail,
            $user->getEmail()
        ));

        //send email verification email
        $this->get('gps.mailer')->sendNewEmailVerificationEmail($user);

        //notify system that contact info changed
        $this->get('event_dispatcher')->dispatch(AppEvents::USER_CONTACT_CHANGED, new UserEvent($user));

        //user shuold be redirected after this (client-side), so set a flash message
        $this->addFlash('info', "Your email address was changed to \"$newEmail\".  Please log in again with your new email address.");
        $this->get('security.token_storage')->setToken(null);
        
        return $this->createServiceResponse(['user' => $user], 200, [], $ctx);
    }

    /**
     * Updates a user password, encoding it.  This requires the current password, and
     * a double confirmation of the new password.
     *
     * This route requires JSON input.
     *
     * @Route("/{id}/password", name="api-put-user-password")
     * @Method("PUT")
     */
    public function putUserPasswordAction(Request $req, $id)
    {
        $user = $this->getRequestedUser($id);

        //decode json directly, no serializer, this is a special use case
        $data = $this->decodeRawJsonRequest($req, ['newPassword','currentPassword']);

        //create encoded versions of current and new password
        $encoder = $this->get('security.password_encoder');
        $encodedCurrent = $encoder->encode($user, $data['currentPassword']);
        $encodedNew = $encoder->encode($user, $data['newPassword']);

        //ensure current password is correct
        if ($encodedOld !== $user->getPassword()) {
            throw $this->createHttpException(422, "The current password is incorrect.");
        }

        //ensure new password is actually different
        if ($encodedCurrent === $encodedNew) {
            throw $this->createHttpException(422, "You cannot set the new password to be the same as the current password.");
        }

        //save the user
        $user->setPassword($encodedNew);
        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['changed' => true], 200);
    }

    /**
     * Handles upload and resizing of a profile image.
     *
     * @Route("/{id}/profile-image", name="api-put-user-profile-image")
     * @Method("POST")
     */
    public function postUserProfileImageAction(Request $req, $id)
    {
        $user = $this->getRequestedUser($id);

        $uploaded = $req->files->get('file');

        if (!$uploaded->isValid()) {
            throw $this->createHttpException(422, "There was an error while handling the uploaded file.  ERR: ${$file->getError()}");
        }

        //move uploaded file to transcoding location...
        //right now this is kind of unnecessary, it's really an extra file op for no
        //benefit
        $transcodingFS = $this->get('gps.filesystem.transcoding');
        $stream = fopen($uploaded->getRealPath(), 'r+');
        $tmpname = time().'_'.uniqid().'_original.'.$uploaded->getClientOriginalExtension();
        $transcodingFS->writeStream($tmpname, $stream);
        fclose($stream);

        //use thumbnailer to create new files - for now it should only create one,
        //but in the future may do multiple passes.
        //...kinda hacky to get the full file path to hand to the thumbnailer, but it's
        //the quickest workaround for now
        $newlocalpath = $this->container->getParameter('files_transcoding_root')."/".$tmpname;
        $newFileData = $this->get('gps.thumbnailer')->createThumbnails($newlocalpath)[0];

        //figure out final file names
        $finalPath = sprintf(
            "/%s/%s/%s",
            substr($newFileData['basename'], 0, 3),
            substr($newFileData['basename'], 3, 3),
            $newFileData['filename']
        );

        //move new file to profile-images location, cleanup old files
        $profileImagesFS = $this->get('gps.filesystem.profile_images');
        //if user already has an existing profile image - keep track of it, and remove after writing the new one.
        $oldProfileImageUrl = $user->getAvatarUrl();
        $profileImagesFS->write($finalPath, $transcodingFS->readAndDelete($newFileData['filename']));
        if ($oldProfileImageUrl && $profileImagesFS->has($oldProfileImageUrl)) {
            $profileImagesFS->delete($oldProfileImageUrl);
        }
        $transcodingFS->delete($tmpname);

        //update user doc
        $user->setAvatarUrl($finalPath);
        $this->getDocumentManager()->flush();

        #return modified user
        $ctx = $this->createSerializationContext()->setGroups(['Default', 'User.email', 'User.phone']);
        
        return $this->createServiceResponse(['user' => $user], 200, [], $ctx);
    }

    /**
     * Deletes the users profile image
     *
     * @Route("/{id}/profile-image", name="api-delete-user-profile-image")
     * @Method("DELETE")
     */
    public function deleteUserProfileImageAction(Request $req, $id)
    {
        $user = $this->getRequestedUser($id);

        #remove the actual file from whereever
        $this->get('gps.filesystem.profile_images')->delete($user->getAvatarUrl());

        #update and save user doc
        $user->setAvatarUrl(null);
        $this->getDocumentManager()->flush();

        #return modified user
        $ctx = $this->createSerializationContext()->setGroups(['Default', 'User.email', 'User.phone']);
        return $this->createServiceResponse(['user' => $user], 200, [], $ctx);
    }

    /**
     * Change a users roles - generally reserved for super admin users.
     *
     * @Route("/{id}/roles", name="api-put-user-roles")
     * @Method("PUT")
     */
    public function putUserRolesAction(Request $req, $id)
    {
        throw $this->createHttpException(501);
    }

    /**
     * Delete a user.  This may not ever be enabled, but is here for completeness.
     *
     * @Route("/{id}", name="api-delete-user")
     * @Method("DELETE")
     */
    public function deleteUserAction()
    {
        throw $this->createHttpException(501, "May not ever be implemented.... we will see.");
    }
    
    private function getRequestedUser($id, $allowAdmin = false)
    {
        $requestedUser = $this->getRepository('AppBundle:User')->find($id);

        if (!$requestedUser) {
            throw $this->createHttpException(404, "No user by that id.");
        }
        
        $currentUser = $this->getUser();
        
        // if requesting and requested user are the same, it's ok
        if ($currentUser->getId() == $requestedUser->getId()) {
            return $requestedUser;
        }
        
        //simple auth check for now
        if ($allowAdmin) {
            $auth = $this->get('security.authorization_checker');
            if (!$auth->isGranted('ROLE_ADMIN')) {
                throw $this->createHttpException(403, "Users may only access their own information.");
            }
            
            return $requestedUser;
        }
        
        throw new \LogicException('Security check encountered unhandled case.');
    }
}

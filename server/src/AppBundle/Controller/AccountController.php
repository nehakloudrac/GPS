<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints as Assert;
use GPS\AppBundle\Document;
use GPS\AppBundle\Form\RegistrationType;
use GPS\AppBundle\Event\AppEvents;
use GPS\AppBundle\Event\UserEvent;

/**
 * Allows users to create and verify new accounts, as well as reset passwords.
 *
 * @author Evan Villemez
 */
class AccountController extends AbstractController
{
    
    private function log($msg)
    {
        $this->container->get('monolog.logger.gps_user')->log('info', $msg);
    }
    
    /**
     * Allow users to register for account via form.
     *
     * @Route("/account/create", name="create-account", defaults={"maintenance": true})
     */
    public function registrationFormAction(Request $req)
    {
        //create the form
        $form = $this->createForm(new RegistrationType());
        $form->handleRequest($req);
        
        // check for referrer
        $sess = $req->getSession();
        $referrer = $sess->get('gps.referrer', false);

        if ($form->isValid()) {
            $data = $form->getData();

            //check for preexisting user with the given email
            $dm = $this->getDocumentManager();
            $existingUser = $dm->getRepository("AppBundle:User")->findOneBy(['email' => strtolower(trim($data['email']))]);
            if ($existingUser) {
                $this->addFlash('warning', "This email has already been registered.");

                return $this->redirect($this->generateUrl('forgot-password'));
            }

            //bind form data to user
            $user = Document\User::createFromArray([
                'firstName' => trim($data['firstName']),
                'lastName' => trim($data['lastName']),
                'email' => trim(strtolower($data['email']))
            ]);
            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $data['password_confirm']));

            //validate user
            $errors = $this->get('validator')->validate($user);
            if (count($errors) > 0) {
                throw new \RuntimeException("User was invalid: ".(string) $errors);
            }

            // initialize an empty candidate profile
            $profile = new Document\Candidate\Profile();
            $profile->setUser($user);
            $user->setCandidateProfile($profile);
            $errors = $this->get('validator')->validate($user);
            if (count($errors) > 0) {
                throw new \RuntimeException("Profile initialization failed: ".(string) $errors);
            }
            
            // check session for a referrer
            if ($referrer) {
                $user->setInstitutionReferrer($referrer['key']);
            }

            // enble user immediately
            $user->setIsEnabled(true);

            $dm->persist($user);
            $dm->persist($profile);
            $dm->flush();
            $this->log(sprintf("Registered new user [%s]", $user->getEmail()));

            //notify app of registered user
            $this->get('event_dispatcher')->dispatch(AppEvents::USER_REGISTERED, new UserEvent($user));

            //send email
            $this->get('gps.mailer')->sendAccountVerificationEmail($user);
            
            // Log user in immediately, and send to profile
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            return $this->redirect($this->generateUrl('profile-app'));
        }
        
        //otherwise render form
        $template = ($referrer) ? 'public/registration-referrer.html.twig' : 'public/registration.html.twig' ;
        return $this->render($template, [
            'title' => 'Join GPS',
            'form' => $form->createView(),
            'referrer' => $referrer,
        ]);
    }

    /**
     * Static page for having sent password reset email.
     *
     * @Route("/account/password-email-sent", name="password-email-sent")
     */
    public function passwordResetEmailSentAction()
    {
        return $this->render("public/static-message.html.twig", [
            'title' => "Password Reset Email Sent",
            'paragraphs' => [
                "Please check your inbox or junk folder for an email with a link to reset your password. This link is only valid for 24 hours.",
                "If your link has expired, <a href='/account/forgot-password'>request another link</a>."
            ]
        ]);
    }
    
    /**
     * Static page for having re-sent the verification link.
     *
     * @Route("/account/verification-email-sent", name="verification-email-sent")
     */
    public function reverificationEmailSentAction()
    {
        return $this->render("public/static-message.html.twig", [
            'title' => "Verification Email Sent",
            'paragraphs' => [
                "A verification link has been sent to the email address you provided. Please confirm your email address by clicking on the link in the email.",
                "If you do you not receive your verification link within 5 minutes, be sure to check your spam filters and junk mail. If you still do not see our welcome email, please <a href='/account/verify'>request a new verification link</a>."
            ]
        ]);
    }
    
    /**
     * If a user did not get their verification link, or it has expired, they can request another
     * verification email.
     *
     * @Route("/account/verify", name="reverify-account")
     */
    public function reverifyAccountAction(Request $req)
    {
        $form = $this->createFormBuilder()
            ->add('email', 'email', ['constraints' => [new Assert\NotBlank(), new Assert\Email()]])
            ->getForm();

        $form->handleRequest($req);

        if ($form->isValid()) {
            $email = $form->getData()['email'];

            $user = $this->getRepository('AppBundle:User')->findOneBy(['email' => strtolower(trim($email))]);

            if (!$user) {
                $this->addFlash('warning', 'We could not find an account with that email address.');

                return $this->redirect($this->generateUrl("reverify-account"));
            }

            if ($user->isVerified()) {
                $this->addFlash('warning', "The account for the given email address has already been verified.  Did you forget your password?");

                return $this->redirect($this->generateUrl('forgot-password'));
            }

            $this->get('gps.mailer')->sendAccountVerificationEmail($user);
            $this->log(sprintf("User [%s] requested reverification link", $user->getEmail()));

            return $this->redirect($this->generateUrl("verification-email-sent"));
        }

        return $this->render("public/reverify-account.html.twig", [
            'title' => "Resend Account Verification Email",
            'form' => $form->createView()
        ]);
    }

    /**
     * Users click on a link in their email to veify their accounts.
     *
     * @Route("/account/verify/{token}", name="verify-account")
     * @Method("GET")
     */
    public function verifyAccountAction(Request $req, $token)
    {
        $tm = $this->get('gps.token_manager');

        //does the token even exist?
        if (!$tm->hasToken($token)) {
            $this->addFlash('warning', "This link has already been used.  You may need to request a new account verification link.");

            return $this->redirect($this->generateUrl('reverify-account'));
        }

        //use the token
        try {
            $data = $tm->useToken($token);
        } catch (\Exception $e) {
            $this->addFlash('warning', "This link has expired.  You may need to request a new account verification link.");

            return $this->redirect($this->generateUrl('reverify-account'));
        }

        //get the user
        $dm = $this->getDocumentManager();
        $user = $dm->getRepository('AppBundle:User')->findOneBy(['id' => $data['id']]);

        if (!$user) {
            throw new \LogicException("The link was generated for a nonexisting user.");
        }

        // keep track of whether or not they were previously enabled
        $userWasAlreadyEnabled = $user->getIsEnabled();

        //enable and save user
        $user->setIsEnabled(true);
        $user->setIsVerified(true);
        $dm->flush();
        $this->log(sprintf("Verified email for user [%s]", $user->getEmail()));

        //notify system of verified email
        $this->get('event_dispatcher')->dispatch(AppEvents::USER_EMAIL_VERIFIED, new UserEvent($user));

        if (!$userWasAlreadyEnabled) {
            $this->get('gps.mailer')->sendAccountVerifiedEmail($user);
            $this->addFlash('info', "Thank You For Joining GPS!  Your account has been verified.  Login below to get started.");
        } else {
            $this->addFlash('info', "Your email has been verified!");
        }
        
        // redirect to dashboard if logged in already, login
        // otherwise just go to login
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('dashboard-app'));
        }
        
        return $this->redirect($this->generateUrl('login'));
    }

    /**
     * Forgot password route.
     *
     * @Route("/account/forgot-password", name="forgot-password")
     */
    public function forgotPasswordAction(Request $req)
    {
        $form = $this->createFormBuilder()
            ->add('email', 'email', ['constraints' => [new Assert\NotBlank(), new Assert\Email()]])
            ->getForm();

        $form->handleRequest($req);

        if ($form->isValid()) {
            $email = $form->getData()['email'];

            $user = $this->getRepository('AppBundle:User')->findOneBy(['email' => strtolower(trim($email))]);

            if (!$user) {
                $this->addFlash('warning', 'We could not find an account with that email address.');

                return $this->redirect($this->generateUrl("forgot-password"));
            }

            //send the email
            $this->log(sprintf("User [%s] requested password reset link", $user->getEmail()));
            $this->get('gps.mailer')->sendPasswordResetEmail($user);

            return $this->redirect($this->generateUrl("password-email-sent"));
        }

        return $this->render("public/forgot-password.html.twig", [
            'title' => 'Forgot Password',
            'form' => $form->createView()
        ]);
    }

    /**
     * Forgot password route.
     *
     * @Route("/account/password-reset/{token}", name="reset-password-entry")
     */
    public function resetPasswordEntryAction(Request $req, $token)
    {
        $tm = $this->get('gps.token_manager');

        //does the token even exist?
        if (!$tm->hasToken($token)) {
            $this->addFlash('warning', "The reset link was invalid or expired - you may need to request a new password reset link.");

            return $this->redirect($this->generateUrl('forgot-password'));
        }

        //use the token
        try {
            $data = $tm->useToken($token);
        } catch (\Exception $e) {
            $this->addFlash('warning', "The reset link was invalid or expired - you may need to request a new password reset link.");

            return $this->redirect($this->generateUrl('forgot-password'));
        }

        //enable the password reset form in the session
        $req->getSession()->set('pw-reset-ops', [
            'at' => time(),
            'for' => $data['id']
        ]);
        
        $this->log(sprintf("User [%s] used password reset link", $data['id']));        

        return $this->redirect($this->generateUrl('reset-password'));
    }

    /**
     * Reset the password - can only get here by having used a password
     * reset link from email.
     *
     * @Route("/account/reset-password", name="reset-password")
     */
    public function resetPasswordAction(Request $req)
    {
        //get the session ops for password reset
        $session = $req->getSession();
        $ops = $session->get('pw-reset-ops', false);

        if (!$ops) {
            return $this->redirect($this->generateUrl('forgot-password'));
        }

        //validate the time (must complete the reset within 10 minutes of having used the reset token)
        if (time() - $ops['at'] > 600) {
            $this->addFlash('warning', "Time expired while resetting the password.  You must reset your password within 10 minutes of clicking the reset link for security reasons.  You may request another password reset link any time.");
            $session->remove('pw-reset-ops');

            return $this->redirect($this->generateUrl('forgot-password'));
        }

        //get the user
        $dm = $this->getDocumentManager();
        $user = $dm->getRepository('AppBundle:User')->findOneBy(['id' => $ops['for']]);
        if (!$user) {
            $session->remove('pw-reset-ops');

            throw new \LogicException("The token referred to a nonexisting user.");
        }

        //create the reset form
        $form = $this->createFormBuilder()
            ->add('password', 'repeated', [
               'type'        => 'password',
               'first_name'  => 'password',
               'second_name' => 'confirm',
               'invalid_message' => 'The password fields must match.',
               'options' => ['constraints' => [new Assert\NotBlank()]],
               'first_options' => ['label' => "Password"],
               'second_options' => ['label' => "Repeat Password"]
            ])
            ->getForm();

        $form->handleRequest($req);

        if ($form->isValid()) {
            //update password and save user
            $data = $form->getData();

            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $data['password']));
            $dm->flush();
            $this->log(sprintf("User [%s] password was reset", $user->getEmail()));

            $this->addFlash('info', "Your password has been updated!  Please log in with your new password.");
            $session->remove('pw-reset-ops');

            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render("public/reset-password.html.twig", [
            'title' => "Choose a New Password",
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
    
    /**
     * Unsubscribe from an email
     * 
     * @Route("/account/unsubscribe/{token}", name="unsubscribe")
     */
    public function unsubscribeEmailAction(Request $req, $token)
    {
        // decode token
        $decoded = json_decode(base64_decode($token), true);
        if (!isset($decoded['userId']) || !isset($decoded['emailKey'])) {
            throw $this->createHttpException('400', "Invalid unsubscribe token.");
        }
        
        $user = $this->getRepository('AppBundle:User')->find($decoded['userId']);
        if (!$user) {
            throw $this->createHttpException('400', "Invalid unsubscribe token.");
        }
        
        $unsubscribePreferenceMap = [
            'profile-health' => 'allowProfileHealthEmails',
            'product-announcement' => 'allowProductFeatureEmails',
            'search-notifications' => 'allowSearchActivityEmails',
        ];
        if (!isset($unsubscribePreferenceMap[$decoded['emailKey']])) {
            throw $this->createHttpException('400', "Invalid unsubscribe token.");
        }
        
        $preferenceKey = $unsubscribePreferenceMap[$decoded['emailKey']];
        
        // change preference and save
        $user->getPreferences()->{'set'.ucfirst($preferenceKey)}(false);
        $this->getDocumentManager()->flush();
        
        // redirect user
        return $this->redirect($this->generateUrl('unsubscribe-confirmation'));
    }
    
    /**
     * Confirmation of unsubscribe to email
     * 
     * @Route("/account/unsubscribe-confirmation", name="unsubscribe-confirmation")
     */
    public function unsubscribeConfirmationAction()
    {
        return $this->render("public/static-message.html.twig", [
            'title' => "Successfully Unsubscribed",
            'paragraphs' => [
                "You have been unsubscribed from these types of messages.",
                "At any time, you can return to your Dashboard Preferences and manage your email settings."
            ]
        ]);
    }

    public function render($view, array $parameters = [], Response $response = null)
    {
        $parameters['assetCacheInvalidator'] = $this->container->get('gps.local_cache')->fetch('gps.deploy-tag');

        return parent::render($view, $parameters);
    }
}

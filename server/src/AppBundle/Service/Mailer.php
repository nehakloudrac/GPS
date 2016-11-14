<?php

namespace GPS\AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Convenience service for sending emails, since the same email could be triggered
 * from multiple locations.
 */
class Mailer
{
    private $fromAddress;
    private $mailer;
    private $templating;
    private $tokenManager;
    private $routing;
    private $baseUrl;
    private $logger;
    private $enabled = true;

    public function __construct($fromAddress, $mailer, $templating, $tokenManager, $routing, $baseUrl, $logger)
    {
        $this->fromAddress = $fromAddress;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->tokenManager = $tokenManager;
        $this->routing = $routing;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }
    
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * When the user was already registered, but has changed email addresses and needs
     * to verify the new address.
     */
    public function sendNewEmailVerificationEmail($user)
    {
        return $this->sendVerificationEmail($user, "Verify your GPS Email Address", "emails/verify-new-email.md.twig");
    }

    /**
     * Sent immediatly upon registration to verify the users email address.
     */
    public function sendAccountVerificationEmail($user, $subject = null)
    {
        $subject = ($subject) ? $subject : "Verify Your GPS Account";
        
        return $this->sendVerificationEmail($user, $subject, "emails/verify-account.md.twig");
    }
    
    protected function sendVerificationEmail($user, $subject, $template)
    {
        //create verification link
        $url = $this->generateAbsoluteUrl('verify-account', [
            'token' => $this->tokenManager->createToken(['id' => $user->getId()])
        ]);
        
        $newLinkUrl = $this->generateAbsoluteUrl('reverify-account');

        return $this->sendUserEmail(
            $user,
            $subject,
            $template,
            [
                'user' => $user,
                'url' => $url,
                'newLinkUrl' => $newLinkUrl
            ]
        );
    }

    public function sendPasswordResetEmail($user)
    {
        //create verification link
        $url = $this->generateAbsoluteUrl('reset-password-entry', [
            'token' => $this->tokenManager->createToken(['id' => $user->getId()])
        ]);

        return $this->sendUserEmail(
            $user,
            'GPS Password Reset',
            'emails/reset-password.md.twig',
            [
                'user' => $user,
                'url' => $url
            ]
        );
    }

    public function sendAccountVerifiedEmail($user)
    {
        return $this->sendUserEmail(
            $user,
            'Welcome to GPS',
            'emails/account-verified.md.twig',
            ['user' => $user]
        );
    }

    public function sendEmployerContactEmail($contact)
    {
        $name = $contact->getFirstName();
        $to = $contact->getEmail();

        $content = $this->templating->render('emails/employer-contact.md.twig', ['name' => $name]);

        // parse full html body
        $body = $this->templating->render('email.html.twig', [
            'email_assets_base_url' => $this->baseUrl,
            'content' => $content
        ]);

        //send email
        $message = $this->mailer->createMessage()
            ->setSubject("Thanks for contacting GPS!")
            ->setFrom([$this->fromAddress => "GPS Employer Services"])
            ->setTo($contact->getEmail())
            ->setBody($body, 'text/html')
            ->addPart($content, 'text/plain')
        ;

        return $this->sendMessage($message);
    }
    
    public function sendEmployerContactReceivedEmail($recipients = [], $contact)
    {
        $content = $this->templating->render('emails/employer-contact-received.md.twig', [
            'contact' => $contact
        ]);
        
        $body = $this->templating->render('email.html.twig', [
            'email_assets_base_url' => $this->baseUrl,
            'content' => $content
        ]);
        
        //send email
        $message = $this->mailer->createMessage()
            ->setSubject("GPS Employer Contact")
            ->setFrom([$this->fromAddress => "GPS Employer Services"])
            ->setTo($recipients)
            ->setBody($body, 'text/html')
            ->addPart($content, 'text/plain')
        ;
        
        return $this->sendMessage($message);
    }
    
    public function setEnabled($bool)
    {
        $this->enabled = $bool;
    }

    protected function sendUserEmail($user, $title, $templatePath, $templateArgs = [])
    {
        // parse raw text contents in markdown
        $templateArgs['email_assets_base_url'] = $this->baseUrl;
        $content = $this->templating->render($templatePath, $templateArgs);

        // parse full html body
        $body = $this->templating->render('email.html.twig', [
            'email_assets_base_url' => $this->baseUrl,
            'content' => $content
        ]);

        //send email
        $message = $this->mailer->createMessage()
            ->setSubject($title)
            ->setFrom([$this->fromAddress => "Global Professional Search"])
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html')
            ->addPart($content, 'text/plain')
        ;

        return $this->sendMessage($message);
    }

    protected function sendMessage($message)
    {
        if (!$this->enabled) {
            return;
        }
        
        $this->logger->log('info', sprintf(
            "Sending email [%s] from [%s] to [%s]",
            $message->getSubject(),
            json_encode($message->getFrom()),
            json_encode($message->getTo())
        ));

        return $this->mailer->send($message);
    }

    private function generateAbsoluteUrl($name, $params = [])
    {
        // NOTE: you can't rely on generating absolute URLs here, since this is often
        // invoked from the CLI, which means there's no Request present... the base
        // url to use must be configured properly

        return $this->baseUrl.$this->routing->generate($name, $params);
    }
}

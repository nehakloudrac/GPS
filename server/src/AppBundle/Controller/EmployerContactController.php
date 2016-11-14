<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GPS\AppBundle\Document;
use GPS\AppBundle\Form;
use GPS\AppBundle\Event;

/**
 * Allows employers to contact GPS... hopefully this is mostly temporary.
 *
 * @author Evan Villemez
 */
class EmployerContactController extends AbstractController
{

    /**
     * Allow employers to contact GPS via form.
     *
     * @Route("/employers/contact", name="employer-contact", defaults={"maintenance": true})
     */
    public function employerContactFormAction(Request $req)
    {
        //create the form & handle the request
        $contact = new Document\EmployerContact();
        $form = $this->createForm(new Form\EmployerContactType(
            $this->container->getParameter('gps.form.industries'),
            $this->container->getParameter('gps.form.languages'),
            $this->container->getParameter('gps.form.countries')
        ), $contact);

        $form->handleRequest($req);

        // detect added position, debatably hacky... retrieves the model
        // from the form, modifies it, then creates a technically different
        // form with the modified model, returning early... but this is
        // far less work than doing the equivalent in javascript and having to render
        // a prototype template
        if ($form->get('addPosition')->isClicked()) {
            // get model and modify it
            $contact = $form->getData();
            $contact->getPositions()->add(new Document\EmployerContactPosition());

            // create a new form instance w/ the modified model, return early
            $form = $this->createForm(new Form\EmployerContactType(
                $this->container->getParameter('gps.form.industries'),
                $this->container->getParameter('gps.form.languages'),
                $this->container->getParameter('gps.form.countries')
            ), $contact);

            return $this->renderForm($form);
        }

        // check for final submission and validate
        if ($form->get('save')->isClicked() && $form->isValid()) {
            $contact = $form->getData();
            $contact->removeEmptyPositions();

            // save
            $dm = $this->getDocumentManager();
            $dm->persist($contact);
            $dm->flush();

            // notify system
            $this->get('event_dispatcher')->dispatch(Event\AppEvents::EMPLOYER_CONTACT_CREATED, new Event\EmployerContactEvent($contact));

            // notify user
            $this->get('gps.mailer')->sendEmployerContactEmail($contact);
            $this->get('gps.mailer')->sendEmployerContactReceivedEmail([
                'kirsten@globalprofessionalsearch.com',
                'evan@globalprofessionalsearch.com',
                'kirsten@newcitycompanies.com'
            ], $contact);

            return $this->redirect($this->generateUrl('employer-contact-success'));
        }

        //render the form, in whatever state it's in: initial or invalid
        return $this->renderForm($form);
    }

    /**
     * Allow employers to contact GPS via form.
     *
     * @Route("/employers/contact-success", name="employer-contact-success")
     */
    public function employerContactSuccessAction()
    {
        return $this->render("public/static-message.html.twig", [
            'title' => "Request Received",
            'assetCacheInvalidator' => $this->container->get('gps.local_cache')->fetch('gps.deploy-tag'),
            'paragraphs' => [
                "Thank you for contacting GPS. Your global talent needs are important to us. One of our placement consultants will be in touch with you soon.",
                "<a href='/' class='btn btn-sm btn-primary'>Continue Browsing</a>"
            ]
        ]);
    }

    private function renderForm($form)
    {
        return $this->render("public/employer-contact.html.twig", [
            'title' => 'Employer Info',
            'form' => $form->createView(),
            'assetCacheInvalidator' => $this->container->get('gps.local_cache')->fetch('gps.deploy-tag')
        ]);
    }
}

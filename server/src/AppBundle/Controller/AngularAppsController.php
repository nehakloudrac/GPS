<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use GPS\AppBundle\Document;

/**
 * Contains routes that render dynamic angular apps.  Generally speaking, the apps provide various
 * bits of functionalty for authenticated users, and use the GPS API for communication w/ the server.
 *
 * The advantages to having Symfony render the initial page for Angular apps are that:
 *
 *  - Authentication can be checked server side, unauthenticated or invalid users are redirected before the app loads
 *  - Startup info needed by any app can be prefilled, once the Angular app is bootstrapped, it is ready to go
 *
 * @package GPS
 * @author Evan Villemez
 */
class AngularAppsController extends AbstractController
{
    /**
     * Redirect old dashboard location to new.  Location changed after redesign.
     * 
     * @Route("/dashboard", name="old-dashboard-app")
     */
    public function oldDashboardRedirectAction()
    {
        return $this->redirect($this->generateUrl("dashboard-app"));
    }
    
    
    /**
     * Redirect old profile location to new.  Location changed after redesign.
     * 
     * @Route("/dashboard/candidate", name="old-profile-app")
     */
    public function oldProfileRedirectAction()
    {
        return $this->redirect($this->generateUrl("profile-app"));
    }
    
    /**
     * The main dashboard overview app - all users have this, and it provides entry
     * points into the other apps.
     *
     * @Route("/candidate/dashboard", name="dashboard-app", defaults={"maintenance": true})
     */
    public function dashboardAppAction()
    {
        $user = $this->getUser();
        $profile = $user->getCandidateProfile();
        
        try {
            // update completeness calculations when the Dashboard is loaded
            $profile->computeCompleteness();
            $this->getDocumentManager()->flush();
        } catch (\Exception $e) {
        }
        

        return $this->renderAngularApp('dashboard', 'gps.dashboard', [
            'candidateProfileData' => $profile,
            'appUser' => $user
        ]);
    }
    
    /**
     * The candidate resources app
     *
     * @Route("/candidate/resources", name="resources-app", defaults={"maintenance": true})
     */
    public function resourcesAppAction()
    {
        $user = $this->getUser();
        $profile = $user->getCandidateProfile();
        
        return $this->renderAngularApp('resources', 'gps.resources', [
            'candidateProfileData' => $profile,
            'appUser' => $user
        ]);
    }

    /**
     * The candidate account app
     *
     * @Route("/candidate/account", name="account-app", defaults={"maintenance": true})
     */
    public function accountAppAction()
    {
        $user = $this->getUser();
        $profile = $user->getCandidateProfile();

        $c = $this->container;
        return $this->renderAngularApp('account', 'gps.account', [
            'candidateProfileData' => $profile,
            'appUser' => $user,
        ]);
    }

    /**
     * Return the candidate app, which contains the survey and profile mechanisms.
     *
     * @Route("/candidate/profile", name="profile-app", defaults={"maintenance": true})
     */
    public function candidateProfileAppAction()
    {
        $user = $this->getUser();
        $profile = $user->getCandidateProfile();
        
        //TODO: gets lots of survey-related cache values, skill set suggestions
        //for example

        //TODO: create serialization context with all relevant groups (lots in this case)
        
        $c = $this->container;
        return $this->renderAngularApp('profile', 'gps.profile', [
            'candidateProfileData' => $profile,
            'appUser' => $user,
        ]);
    }

    /**
     * Return the admin app, which allows filter/sorting existing users and candidate profiles.
     *
     * @Route("/admin", name="admin-app", defaults={"maintenance": true})
     */
    public function adminAppAction()
    {
        $user = $this->getUser();

        $c = $this->container;
        return $this->renderAngularApp('admin', 'gps.admin', [
            'appUser' => $user,
            // kind of a hack, but the UI will be expecting profile data because
            // of some service dependencies.  One day it will be cleaner.
            // That day is not today.
            'candidateProfileData' => [],
        ]);
    }

    /**
     * Render angular app template, injecting any config that should be available
     * to all angular apps.
     */
    private function renderAngularApp($appName, $appModule, $config = [])
    {
        $c = $this->container;
        $deployTag = $c->get('gps.local_cache')->fetch('gps.deploy-tag');
        
        //inject some meta info that's used by all angular apps
        $config['appName'] = "appName";
        $config['appRoots'] = [
            'admin' => '/admin',
            'dashboard' => '/candidate/dashboard',
            'account' => '/candidate/account',
            'candidate' => '/candidate/profile',
            'resources' => '/candidate/resources'
        ];
        $config['profileImagesRoot'] = $c->getParameter('files_profile_images_base_url');
        $config['candidateDocsRoot'] = $c->getParameter('files_candidate_docs_base_url');
        $config['publicFSBaseUrl'] = $c->getParameter('files_public_base_url');
        $config['appDeployedAt'] = $deployTag;
        
        // inject some config that's required pretty much everywhere... this is a bit wasteful to
        // send down all the time, though :(
        $config['languageCodes'] = $c->getParameter('gps.ui.languages');
        $config['countryCodes'] = $c->getParameter('gps.ui.countries');
        $config['institutionIndustries'] = $c->getParameter('gps.ui.industries');
        $config['academicSubjects'] = $c->getParameter('gps.ui.academic-subjects');

        //TODO: inject session flash messages into config going out

        return $this->render('angular-app-layout.html.twig', [
            'appName' => $appName,
            'angularAppModule' => $appModule,
            'cacheInvalidator' => $deployTag,
            'config' => $this->get('jms_serializer')->serialize($config, 'json')
        ]);
    }
}

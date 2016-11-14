<?php

namespace GPS\AppBundle\Service;

use GPSPDF\PDFProfile;
use GPS\AppBundle\Document as Doc;

/**
 * Use the LI parser to prefill as many
 */
class LinkedInParser
{
    private $liProfilePath;
    private $langCodes;
    private $countryCodes;
    
    public function __construct($liProfilePath, $langCodes, $countryCodes)
    {
        $this->liProfilePath = $liProfilePath;
        $this->langCodes = $langCodes;
        $this->countryCodes = $countryCodes;
    }
    
    /** 
     * Parse file on disk returning nested array of data
     * 
     * @param  string $path Path to file on disk
     * @return array       Structured array of data
     */
    public function parseFile($path)
    {
        $parser = new PDFProfile($this->liProfilePath);

        return $parser->processPDF($path);
    }
    
    /** 
     * Use the LI parser to prefill as many fields in a profile document as possible.
     *
     * Also ensure that multiple importing does not duplicate or override existing data.
     * 
     * @param  string $path    Path to PDF file to parse
     * @param  Doc\Candidate\Profile $profile Candidate Profile doc
     * @return Doc\Candidate\Profile          Modified profile doc
     */
    public function parseFileIntoProfile($path, $profile)
    {
        $data = $this->parseFile($path);
        
        // init short form if it's not there
        $shortForm = $profile->getShortForm() ? $profile->getShortForm() : new Doc\Candidate\ShortForm();
        $profile->setShortForm($shortForm);
        
        // check for native/foreign language data: note that we are only pre-filling
        // the short form field for native languages since the LI importer is intended
        // to run before user begins the profile
        if (isset($data['languages'])) {
            $native = $profile->getUser()->getLanguages() ? $profile->getUser()->getLanguages() : [];
            $foreign = $profile->getShortForm()->getForeignLanguages() ? $profile->getShortForm()->getForeignLanguages() : [];
            foreach ($data['languages'] as $item) {
                // find lang code
                $code = array_search($item['language'], $this->langCodes);
                if (!$code) { continue; }
                
                // is it native?
                if (false !== stripos($item['proficiency'], 'native')) {
                    $native[] = $code;
                } else {
                    $foreign[] = $code;
                }
            }

            $profile->getUser()->setLanguages(array_unique($native));
            $profile->getShortForm()->setForeignLanguages(array_unique($foreign));
        }
        
        // check for work history
        if (isset($data['experience'])) {
            foreach ($data['experience'] as $item) {
                // test to ensure it doesn't already exist
                foreach ($profile->getTimeline() as $evt) {
                    if (
                        'job' == $evt->getType() &&
                        $item['position'] == $evt->getTitle() &&
                        $evt->getInstitution() && $item['company'] == $evt->getInstitution()->getName()
                    ) {
                        continue 2;
                    }
                }
                
                $job = new Doc\Candidate\TimelineJob();
                $job->setInstitution(new Doc\Candidate\InstitutionReference());
                $job->setDuration(new Doc\DateRange());
                $job->setTitle($item['position']);
                $job->getInstitution()->setName($item['company']);
                $job->setDescription($item['summary']);
                if (isset($item['timeframe']['start'])) {
                    $job->getDuration()->setStart(\DateTime::createFromFormat(\DateTime::ISO8601, $item['timeframe']['start']));
                }
                if (isset($item['timeframe']['end'])) {
                    $job->getDuration()->setEnd(\DateTime::createFromFormat(\DateTime::ISO8601, $item['timeframe']['end']));
                }
                
                $profile->getTimeline()->add($job);
            }
        }
        
        // check for education
        if (isset($data['education'])) {
            // note that order matters for these checks
            $degreeTests = [
                'mba' => ["mba","m.b.a.","business administration"],
                'masters' => ["master","m.a.","m.s."],
                'bachelors' => ["b.a.","b.s.","bachelor"],
                'phd' => ["phd","ph.d","doctorate"],
                'jd' => ['jd','j.d.','djur','d.jur','doctor of law','doctorate of law', 'juris doctor','doctor of jurisprudence'],
                'md' => ['md','m.d.','doctor of medicine','doctorate of medicine'],
                'edd' => ['edd','ed.d','doctor of education','doctorate of education', 'doctorate in education'],
                'associates' => ["associate"],
            ];
            
            $getDegree =  function ($string) use ($degreeTests) {
                foreach ($degreeTests as $degree => $tests) {
                    foreach ($tests as $test) {
                        if (false !== stripos($string, $test)) {
                            return $degree;
                        }
                    }
                }
                
                return null;
            };
            
            $totalDegrees = [];
            foreach($data['education'] as $item) {
                // check that item item doesn't already exist based on name and start date
                foreach ($profile->getTimeline() as $evt) {
                    if (
                        'university' == $evt->getType() &&
                        $evt->getInstitution() && $item['provider'] == $evt->getInstitution()->getName() &&
                        $evt->getDuration() &&
                        $evt->getDuration()->getStart()->getTimestamp() == \DateTime::createFromFormat(\DateTime::ISO8601, $item['timeframe']['start'])->getTimestamp()
                    ) {
                        continue 2;
                    }
                }
                
                $uni = new Doc\Candidate\TimelineUniversity();
                $uni->setDuration(new Doc\DateRange());
                $uni->setInstitution(new Doc\Candidate\InstitutionReference());
                $degree = $getDegree($item['degree']);
                if ($degree) {
                    $totalDegrees[] = $degree;
                    $uni->setDegrees([$degree]);                    
                }
                $uni->getInstitution()->setName($item['provider']);
                $uni->getConcentrations()->add(Doc\Candidate\UniversityConcentration::createFromArray([
                    'type' => 'major',
                    'fieldName' => $item['field_of_study']
                ]));
                if (isset($item['timeframe']['start'])) {
                    $uni->getDuration()->setStart(\DateTime::createFromFormat(\DateTime::ISO8601, $item['timeframe']['start']));
                }
                if (isset($item['timeframe']['end'])) {
                    $uni->getDuration()->setEnd(\DateTime::createFromFormat(\DateTime::ISO8601, $item['timeframe']['end']));
                }
                
                $profile->getTimeline()->add($uni);
                
                // also check academic orgs in here
                if ($item['activities_and_studies']) {
                    foreach ($item['activities_and_studies'] as $orgName) {
                        // ensure doesn't already exist
                        foreach ($profile->getAcademicOrganizations() as $academicOrg) {
                            if ($orgName === $academicOrg->getName()) { continue 2; }
                        }
                        
                        $org = new Doc\Candidate\AcademicOrgAffiliation();
                        $org->setName($orgName);
                        $org->setDuration($uni->getDuration());
                        
                        $profile->getAcademicOrganizations()->add($org);
                    }
                }
            }
            
            $profile->getShortForm()->setDegrees(array_unique($totalDegrees));
        }
        
        // check for skills... skills come from linkedin generally in order of highest
        // endorsed: so assume that the higher the skill in the order, the more proficient
        // 
        // this will need to be refactored when we change how we handle skills more broadly
        if (isset($data['skills'])) {
            $skills = $profile->getDomainSkills() ? $profile->getDomainSkills() : new Doc\Candidate\DomainSkills();
            $profile->setDomainSkills($skills);
            
            $exp = ($skills->getExpert()) ? $skills->getExpert() : [];
            $adv = ($skills->getAdvanced()) ? $skills->getAdvanced() : [];
            $prof = ($skills->getProficient()) ? $skills->getProficient() : [];
            
            // combine and unique total skill list to ensure no duplicates
            $total = array_unique($exp + $adv + $prof);
            foreach ($data['skills'] as $skill) {
                $total[] = $skill;
            }
            $total = array_values(array_unique($total));
            
            $exp = [];
            $adv = [];
            $prof = [];
            for ($i = 0; $i < count($total); $i++) {
                if ($i < 5 && count($exp) < 5) {
                    $exp[] = $total[$i];
                }
                
                else if ($i < 15 && count($adv) < 15) {
                    $adv[] = $total[$i];
                }
                
                else if ($i < 30 && count($prof) < 30) {
                    $prof[] = $total[$i];
                }
            }
            
            $skills->setExpert(array_unique($exp));
            $skills->setAdvanced(array_unique($adv));
            $skills->setProficient(array_unique($prof));
        }
        
        return $profile;
    }
}

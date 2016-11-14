<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Overview of activities performed in a specific country.  Not all values are expected to be present.
 *
 * @MongoDB\EmbeddedDocument
 */
class CountryActivities
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */    
    protected $attendClasses;
    
    /**
     * @deprecated
     * 
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $attendClassesLocalLang;

    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $attendClassesLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $modifyCurriculum;
        
    /**
     * @deprecated
     * 
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $modifyCurriculumLocalLang;

    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $modifyCurriculumLocalLangBool;
    
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $taughtLocals;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $taughtLocalsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $taughtLocalsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $commandUnit;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $commandUnitLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $commandUnitLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $persuadeLocalsProduct;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $persuadeLocalsProductLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $persuadeLocalsProductLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $relationshipsLocalGov;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $relationshipsLocalGovLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $relationshipsLocalGovLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $collaborateWithLocals;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $collaborateWithLocalsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $collaborateWithLocalsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $interfaceWithProfessionals;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $interfaceWithProfessionalsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $interfaceWithProfessionalsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $negotiateContracts;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $negotiateContractsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $negotiateContractsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $manageTeam;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $manageTeamLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $manageTeamLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $navLegalReqs;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $navLegalReqsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $navLegalReqsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $navFinancialReqs;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $navFinancialReqsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $navFinancialReqsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $accommodateValues;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $accommodateValuesLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $accommodateValuesLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $professionalInterpretation;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $professionalInterpretationLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $professionalInterpretationLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $professionalTranslation;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $professionalTranslationLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $professionalTranslationLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $localTradition;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $localTraditionLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $localTraditionLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $convinceLocals;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $convinceLocalsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $convinceLocalsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $talkToMedia;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $talkToMediaLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $talkToMediaLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $publicPresentations;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $publicPresentationsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $publicPresentationsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $navLocalRegs;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $navLocalRegsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $navLocalRegsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $performanceConsequences;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $performanceConsequencesLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $performanceConsequencesLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $leadProject;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $leadProjectLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $leadProjectLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $socializeWithLocals;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $socializeWithLocalsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $socializeWithLocalsLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $useSocialMedia;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $useSocialMediaLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $useSocialMediaLocalLangBool;
        
    /**
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $writeReports;
        
    /**
     * @deprecated
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $writeReportsLocalLang;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $writeReportsLocalLangBool;
}

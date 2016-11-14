<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use AC\ModelTraits\AutoGetterSetterTrait;
use GPS\AppBundle\Model\SimpleGetterSetterTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * A reference to an institution of some sort.  This is mostly a placeholder so that in
 * the future we'll have the option of associating our various references to institutions
 * to actual paying institutions we have record of as clients of GPS.
 *
 * @MongoDB\EmbeddedDocument
 */
class InstitutionReference
{
    use AutoGetterSetterTrait, SimpleGetterSetterTrait, PropertyExistanceTrait;
    
    public function __construct()
    {
        // $this->address = new \GPS\AppBundle\Document\Address();
    }

    /**
     * TODO: one day we may be able to associate with paying institutions on file.
     */
    // private $reference;

    /**
     * Name of institution.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=150)
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * Any relevant url for the institution.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=2500)
     * @Serializer\Type("string")
     */
    protected $url;

    /**
     * Type of institution.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Choice(choices={"company_private","company_public","gov_enterprise","non_profit","edu","foundation","org_intl","gov_fed","gov_local","other"})
     * @Serializer\Type("string")
     */
    protected $type;

    /**
     * DEPRECATED - use `industries` instead
     *
     * What industry the institution is in.
     *
     * TODO: consider strictly validating this... but its a large array of values.  May be
     * worth eventually writing a custom array validator service that can accept arguments from
     * the container config.
     *
     * @deprecated
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=50)
     * @Serializer\Type("string")
     */
    protected $industry;

    /**
     * What industries the institution is in.
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $industries;

    /**
     * Address of institution - not always applicable, but we may as well always have a placeholder
     * for it because it can allow us to do geolocation at a later date.
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\Address")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Address")
     */
    protected $address;
}

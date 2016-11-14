<?php

namespace GPS\Tests\AppBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Testing;
use GPS\AppBundle\Document;

class ProfileTest extends Testing\ControllerTest
{
    public function testComputeCompletenessWithFixtures()
    {
        $factory = $this->fetchFixtures();
        foreach ($factory->getPool('Candidate\Profile')->fetchAll() as $profile) {
            $profile->computeCompleteness();
        }
    }
    
    public function testComputeMonthsExperienceWithFixtures()
    {
        $factory = $this->fetchFixtures();
        foreach ($factory->getPool('Candidate\Profile')->fetchAll() as $profile) {
            $totals = $profile->getTimelineProfessionalExperience();
        }
    }
    
    public function testComputeMonthsExperience()
    {
        // bad/useless dates
        $profile = Document\Candidate\Profile::createFromArray([
            'timeline' => new ArrayCollection([
                Document\Candidate\TimelineJob::createFromArray([]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'end' => new \DateTime('now')
                    ])
                ]),
            ])
        ]);

        // shouldn't throw
        $profile->computeProfessionalExperience();

        // empty/ignored date, and a real date
        $profile = Document\Candidate\Profile::createFromArray([
            'timeline' => new ArrayCollection([
                Document\Candidate\TimelineJob::createFromArray([]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 0),
                        'end' => \DateTime::createFromFormat('U', 1000)
                    ])
                ]),
            ])
        ]);
        
        $totals = $profile->getTimelineProfessionalExperience();
        $this->assertSame(1000, $totals['total']);
        $this->assertSame(1000, $totals['gaps']);
        
        // total and gaps should be different
        $profile = Document\Candidate\Profile::createFromArray([
            'timeline' => new ArrayCollection([
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 5000),
                        'end' => \DateTime::createFromFormat('U', 6000)
                    ])
                ]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 0),
                        'end' => \DateTime::createFromFormat('U', 1000)
                    ])
                ]),
            ])
        ]);
        
        $totals = $profile->getTimelineProfessionalExperience();
        $this->assertSame(6000, $totals['total']);
        $this->assertSame(2000, $totals['gaps']);
        
        // overlapping dates should compute as expected
        $profile = Document\Candidate\Profile::createFromArray([
            'timeline' => new ArrayCollection([
                Document\Candidate\TimelineJob::createFromArray([]),
                Document\Candidate\TimelineJob::createFromArray([]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 500),
                        'end' => \DateTime::createFromFormat('U', 1500)
                    ])
                ]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 5000),
                        'end' => \DateTime::createFromFormat('U', 6000)
                    ])
                ]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 0),
                        'end' => \DateTime::createFromFormat('U', 1000)
                    ])
                ]),
            ])
        ]);
        
        $totals = $profile->getTimelineProfessionalExperience();
        $this->assertSame(6000, $totals['total']);
        $this->assertSame(2500, $totals['gaps']);
        
        // dates containing other dates
        $profile = Document\Candidate\Profile::createFromArray([
            'timeline' => new ArrayCollection([
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 0),
                        'end' => \DateTime::createFromFormat('U', 990)
                    ])
                ]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 5000),
                        'end' => \DateTime::createFromFormat('U', 6000)
                    ])
                ]),
                Document\Candidate\TimelineJob::createFromArray([
                    'duration' => Document\DateRange::createFromArray([
                        'start' => \DateTime::createFromFormat('U', 0),
                        'end' => \DateTime::createFromFormat('U', 1000)
                    ])
                ]),
            ])
        ]);
        
        $totals = $profile->getTimelineProfessionalExperience();
        $this->assertSame(6000, $totals['total']);
        $this->assertSame(2000, $totals['gaps']);
    }
}

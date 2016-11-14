<?php

namespace GPS\AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This generates config for both server and ui from some static lists
 * stored in the project as raw text data files.  Depending on where there
 * are used, server vs ui, the lists may be in a different format.
 *
 * This can be streamlined sometime in the future, at least this way that
 * config lives in only one place.
 */
class ListConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $inds = $this->generateIndustriesLists($container);
        $container->setParameter('gps.form.industries', $inds['form']);
        $container->setParameter('gps.ui.industries', $inds['ui']);
        
        $countries = $this->generateCountriesLists($container);
        $container->setParameter('gps.form.countries', $countries['form']);
        $container->setParameter('gps.ui.countries', $countries['ui']);

        $langs = $this->generateLanguageLists($container);
        $container->setParameter('gps.form.languages', $langs['form']);
        $container->setParameter('gps.ui.languages', $langs['ui']);
        
        $container->setParameter('gps.ui.academic-subjects', $this->generateAcademicSubjectsList($container));
    }

    protected function generateAcademicSubjectsList($container)
    {
        $list = [];
        foreach ($this->loadFile($container, '/academic-subjects.txt') as $item) {
            if (!empty($item)) {
                $item = trim($item);
                $list[] = $item;
            }
        }
        
        return $list;
    }

    protected function generateCountriesLists($container)
    {
        $formList = [];
        $uiList = [];
        
        foreach($this->loadFile($container, '/countries.txt') as $line) {
            list($country, $code) = explode(':', $line);
            if ($country && $code) {
                $code = trim($code); 
                $country = trim($country);
                $formList[$code] = $country;
                $uiList[] = ['code' => $code, 'name' => $country];
            }
        }
        
        return [
            'form' => $formList,
            'ui' => $uiList
        ];
    }
    
    protected function generateLanguageLists($container)
    {
        $formList = [];
        $uiList = [];
        
        foreach ($this->loadFile($container, '/languages.txt') as $line) {
            list($code, $label) = explode(':', $line);
            if ($code && $label) {
                $code = trim($code);
                $label = trim($label);
                $formList[$code] = $label;
                $uiList[] = ['label' => $label, 'code' => $code];
            }
        }
        
        foreach ($this->loadFile($container, '/languages-dialects.txt') as $line) {
            list($codes, $label) = explode(':', $line);
            list($micro, $macro) = explode('_', $codes);
            
            if ($micro && $macro && $label) {
                $micro = trim($micro);
                $macro = trim($macro);
                $label = trim($label);

                $formList[trim($micro)] = trim($label);
                $uiList[] = ['label' => $label, 'code' => $micro, 'macroCode' => $macro];
            }
        }
        
        asort($formList);
        
        return [
            'form' => $formList,
            'ui' => $uiList
        ];
    }
    
    protected function generateIndustriesLists($container)
    {
        $formList = [];
        $uiList = [];
        foreach ($this->loadFile($container, '/industries.txt') as $item) {
            if (!empty($item)) {
                $item = trim($item);
                $formList[$item] = $item;
                $uiList[] = $item;
            }
        }
        
        return [
            'form' => $formList,
            'ui' => $uiList
        ];
    }
    
    private function loadFile($container, $filename)
    {
        return file($container->getParameter('kernel.root_dir').'/Resources/data'.$filename);
    }    
}
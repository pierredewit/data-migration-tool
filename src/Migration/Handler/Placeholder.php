<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\Resource\Record;

/**
 * Handler to set constant value to the field
 */
class Placeholder extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Migration\ClassMap
     */
    protected $classMap;

    /**
     * @param \Migration\ClassMap $classMap
     */
    public function __construct(
        \Migration\ClassMap $classMap
    ) {
        $this->classMap = $classMap;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $content = $recordToHandle->getValue($this->field);
        if ($this->hasPlaceholders($content)) {
            $content = $this->processContent($content);
        }
        $recordToHandle->setValue($this->field, $content);
    }

    /**
     * Whether widget content has placeholders
     *
     * @param $content
     * @return int
     */
    protected function hasPlaceholders($content)
    {
        return preg_match('/({{widget|{{block).*}}/mU', $content);
    }

    /**
     * Process widget placeholders content
     *
     * @param $content
     * @return mixed
     */
    protected function processContent($content)
    {
        $classSource = [];
        $classDestination = [];
        foreach ($this->classMap->getMap() as $classOldFashion => $classNewStyle) {
            $classSource[] = sprintf('type="%s"', $classOldFashion);
            $classDestination[] = sprintf('type="%s"', str_replace('\\', '\\\\', $classNewStyle));
        }
        $content = str_replace($classSource, $classDestination, $content);
        // cut off name of a module from template path
        $content = preg_replace('/({{widget|{{block)(.*template=")(.*\/)(.*".*}})/mU', '$1$2$4', $content);
        return $content;
    }
}

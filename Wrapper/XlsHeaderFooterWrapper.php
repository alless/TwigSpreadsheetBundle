<?php

namespace MewesK\TwigExcelBundle\Wrapper;

/**
 * Class XlsHeaderFooterWrapper
 *
 * @package MewesK\TwigExcelBundle\Wrapper
 */
class XlsHeaderFooterWrapper extends AbstractWrapper
{
    /**
     * @var array
     */
    protected $context;
    /**
     * @var XlsSheetWrapper
     */
    protected $sheetWrapper;

    /**
     * @var array
     */
    protected $alignmentAttributes;

    /**
     * @var \PHPExcel_Worksheet_HeaderFooter
     */
    protected $object;
    /**
     * @var array
     */
    protected $attributes;
    /**
     * @var array
     */
    protected $mappings;

    /**
     * @param array $context
     * @param XlsSheetWrapper $sheetWrapper
     */
    public function __construct(array $context, XlsSheetWrapper $sheetWrapper)
    {
        $this->context = $context;
        $this->sheetWrapper = $sheetWrapper;

        $this->alignmentAttributes = [];

        $this->object = null;
        $this->attributes = [];
        $this->mappings = [];

        $this->initializeMappings();
    }

    protected function initializeMappings()
    {
        $wrapper = $this; // PHP 5.3 fix

        $this->mappings['scaleWithDocument'] = function($value) use ($wrapper) { $wrapper->object->setScaleWithDocument($value); };
        $this->mappings['alignWithMargins'] = function($value) use ($wrapper) { $wrapper->object->setAlignWithMargins($value); };
    }

    /**
     * @param string $type
     * @param null|array $properties
     */
    public function start($type, array $properties = null)
    {
        if ($this->sheetWrapper->getObject() === null) {
            throw new \LogicException();
        }
        if (in_array(strtolower($type), ['header', 'oddheader', 'evenheader', 'firstheader', 'footer', 'oddfooter', 'evenfooter', 'firstfooter'], true) === false) {
            throw new \InvalidArgumentException();
        }

        $this->object = $this->sheetWrapper->getObject()->getHeaderFooter();
        $this->attributes['value'] = ['left' => null, 'center' => null, 'right' => null]; // will be generated by the alignment tags
        $this->attributes['type'] = $type;
        $this->attributes['properties'] = $properties ?: [];

        if ($properties !== null) {
            $this->setProperties($properties, $this->mappings);
        }
    }

    public function end()
    {
        $value = implode('', $this->attributes['value']);

        switch (strtolower($this->attributes['type'])) {
            case 'header':
                $this->object->setOddHeader($value);
                $this->object->setEvenHeader($value);
                $this->object->setFirstHeader($value);
                break;
            case 'oddheader':
                $this->object->setDifferentOddEven(true);
                $this->object->setOddHeader($value);
                break;
            case 'evenheader':
                $this->object->setDifferentOddEven(true);
                $this->object->setEvenHeader($value);
                break;
            case 'firstheader':
                $this->object->setDifferentFirst(true);
                $this->object->setFirstHeader($value);
                break;
            case 'footer':
                $this->object->setOddFooter($value);
                $this->object->setEvenFooter($value);
                $this->object->setFirstFooter($value);
                break;
            case 'oddfooter':
                $this->object->setDifferentOddEven(true);
                $this->object->setOddFooter($value);
                break;
            case 'evenfooter':
                $this->object->setDifferentOddEven(true);
                $this->object->setEvenFooter($value);
                break;
            case 'firstfooter':
                $this->object->setDifferentFirst(true);
                $this->object->setFirstFooter($value);
                break;
            default:
                throw new \InvalidArgumentException();
        }

        $this->object = null;
        $this->attributes = [];
    }

    /**
     * @param null|string $type
     * @param null|array $properties
     */
    public function startAlignment($type = null, array $properties = null)
    {
        $this->alignmentAttributes['type'] = $type;
        $this->alignmentAttributes['properties'] = $properties;

        switch (strtolower($this->alignmentAttributes['type'])) {
            case 'left':
                $this->attributes['value']['left'] = '&L';
                break;
            case 'center':
                $this->attributes['value']['center'] = '&C';
                break;
            case 'right':
                $this->attributes['value']['right'] = '&R';
                break;
            default:
                throw new \InvalidArgumentException();
        }
    }

    /**
     * @param null|string $value
     */
    public function endAlignment($value = null)
    {
        switch (strtolower($this->alignmentAttributes['type'])) {
            case 'left':
                if (strpos($this->attributes['value']['left'], '&G') === false) {
                    $this->attributes['value']['left'] .= $value;
                }
                break;
            case 'center':
                if (strpos($this->attributes['value']['center'], '&G') === false) {
                    $this->attributes['value']['center'] .= $value;
                }
                break;
            case 'right':
                if (strpos($this->attributes['value']['right'], '&G') === false) {
                    $this->attributes['value']['right'] .= $value;
                }
                break;
            default:
                throw new \InvalidArgumentException();
        }

        $this->alignmentAttributes = [];
    }

    //
    // Getters/Setters
    //

    /**
     * @return \PHPExcel_Worksheet_HeaderFooter
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param \PHPExcel_Worksheet_HeaderFooter $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * @param array $mappings
     */
    public function setMappings($mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * @return array
     */
    public function getAlignmentAttributes()
    {
        return $this->alignmentAttributes;
    }

    /**
     * @param array $alignmentAttributes
     */
    public function setAlignmentAttributes($alignmentAttributes)
    {
        $this->alignmentAttributes = $alignmentAttributes;
    }
}

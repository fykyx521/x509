<?php

declare(strict_types = 1);

namespace X509\Certificate\Extension\DistributionPoint;

use ASN1\Type\Constructed\Sequence;
use X509\GeneralName\GeneralNames;
use X509\GeneralName\UniformResourceIdentifier;

/**
 * Implements 'fullName' ASN.1 CHOICE type of <i>DistributionPointName</i>
 * used by 'CRL Distribution Points' certificate extension.
 *
 * @link https://tools.ietf.org/html/rfc5280#section-4.2.1.13
 */
class FullName extends DistributionPointName
{
    /**
     * Names.
     *
     * @var GeneralNames $_names
     */
    protected $_names;
    
    /**
     * Constructor.
     *
     * @param GeneralNames $names
     */
    public function __construct(GeneralNames $names)
    {
        $this->_tag = self::TAG_FULL_NAME;
        $this->_names = $names;
    }
    
    /**
     * Initialize with a single URI.
     *
     * @param string $uri
     * @return self
     */
    public static function fromURI(string $uri): self
    {
        return new self(new GeneralNames(new UniformResourceIdentifier($uri)));
    }
    
    /**
     * Get names.
     *
     * @return GeneralNames
     */
    public function names(): GeneralNames
    {
        return $this->_names;
    }
    
    /**
     *
     * {@inheritdoc}
     * @return Sequence
     */
    protected function _valueASN1(): Sequence
    {
        return $this->_names->toASN1();
    }
}

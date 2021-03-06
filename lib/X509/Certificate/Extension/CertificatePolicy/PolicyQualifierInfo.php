<?php

declare(strict_types = 1);

namespace X509\Certificate\Extension\CertificatePolicy;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Constructed\Sequence;
use ASN1\Type\Primitive\ObjectIdentifier;

/**
 * Base class for <i>PolicyQualifierInfo</i> ASN.1 types used by
 * 'Certificate Policies' certificate extension.
 *
 * @link https://tools.ietf.org/html/rfc5280#section-4.2.1.4
 */
abstract class PolicyQualifierInfo
{
    /**
     * OID for the CPS Pointer qualifier.
     *
     * @var string
     */
    const OID_CPS = "1.3.6.1.5.5.7.2.1";
    
    /**
     * OID for the user notice qualifier.
     *
     * @var string
     */
    const OID_UNOTICE = "1.3.6.1.5.5.7.2.2";
    
    /**
     * Qualifier identifier.
     *
     * @var string $_oid
     */
    protected $_oid;
    
    /**
     * Generate ASN.1 for the 'qualifier' field.
     *
     * @return \ASN1\Element
     */
    abstract protected function _qualifierASN1();
    
    /**
     * Initialize from qualifier ASN.1 element.
     *
     * @param UnspecifiedType $el
     * @return self
     */
    public static function fromQualifierASN1(UnspecifiedType $el)
    {
        throw new \BadMethodCallException(
            __FUNCTION__ . " must be implemented in the derived class.");
    }
    
    /**
     * Initialize from ASN.1.
     *
     * @param Sequence $seq
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromASN1(Sequence $seq): self
    {
        $oid = $seq->at(0)
            ->asObjectIdentifier()
            ->oid();
        switch ($oid) {
            case self::OID_CPS:
                return CPSQualifier::fromQualifierASN1($seq->at(1));
            case self::OID_UNOTICE:
                return UserNoticeQualifier::fromQualifierASN1($seq->at(1));
        }
        throw new \UnexpectedValueException("Qualifier $oid not supported.");
    }
    
    /**
     * Get qualifier identifier.
     *
     * @return string
     */
    public function oid(): string
    {
        return $this->_oid;
    }
    
    /**
     * Generate ASN.1 structure.
     *
     * @return Sequence
     */
    public function toASN1(): Sequence
    {
        return new Sequence(new ObjectIdentifier($this->_oid),
            $this->_qualifierASN1());
    }
}

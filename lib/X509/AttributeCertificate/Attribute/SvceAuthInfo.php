<?php

declare(strict_types = 1);

namespace X509\AttributeCertificate\Attribute;

use ASN1\Element;
use ASN1\Type\UnspecifiedType;
use ASN1\Type\Constructed\Sequence;
use ASN1\Type\Primitive\OctetString;
use X501\ASN1\AttributeValue\AttributeValue;
use X501\MatchingRule\BinaryMatch;
use X509\GeneralName\GeneralName;

/**
 * Base class implementing <i>SvceAuthInfo</i> ASN.1 type used by
 * attribute certificate attribute values.
 *
 * @link https://tools.ietf.org/html/rfc5755#section-4.4.1
 */
abstract class SvceAuthInfo extends AttributeValue
{
    /**
     * Service.
     *
     * @var GeneralName $_service
     */
    protected $_service;
    
    /**
     * Ident.
     *
     * @var GeneralName $_ident
     */
    protected $_ident;
    
    /**
     * Auth info.
     *
     * @var string|null $_authInfo
     */
    protected $_authInfo;
    
    /**
     * Constructor.
     *
     * @param GeneralName $service
     * @param GeneralName $ident
     * @param string|null $auth_info
     */
    public function __construct(GeneralName $service, GeneralName $ident,
        $auth_info = null)
    {
        $this->_service = $service;
        $this->_ident = $ident;
        $this->_authInfo = $auth_info;
    }
    
    /**
     *
     * @param UnspecifiedType $el
     * @return self
     */
    public static function fromASN1(UnspecifiedType $el): self
    {
        $seq = $el->asSequence();
        $service = GeneralName::fromASN1($seq->at(0)->asTagged());
        $ident = GeneralName::fromASN1($seq->at(1)->asTagged());
        $auth_info = null;
        if ($seq->has(2, Element::TYPE_OCTET_STRING)) {
            $auth_info = $seq->at(2)
                ->asString()
                ->string();
        }
        return new static($service, $ident, $auth_info);
    }
    
    /**
     * Get service name.
     *
     * @return GeneralName
     */
    public function service(): GeneralName
    {
        return $this->_service;
    }
    
    /**
     * Get ident.
     *
     * @return GeneralName
     */
    public function ident(): GeneralName
    {
        return $this->_ident;
    }
    
    /**
     * Check whether authentication info is present.
     *
     * @return bool
     */
    public function hasAuthInfo(): bool
    {
        return isset($this->_authInfo);
    }
    
    /**
     * Get authentication info.
     *
     * @throws \LogicException
     * @return string
     */
    public function authInfo(): string
    {
        if (!$this->hasAuthInfo()) {
            throw new \LogicException("authInfo not set.");
        }
        return $this->_authInfo;
    }
    
    /**
     *
     * @see \X501\ASN1\AttributeValue\AttributeValue::toASN1()
     * @return Sequence
     */
    public function toASN1(): Sequence
    {
        $elements = array($this->_service->toASN1(), $this->_ident->toASN1());
        if (isset($this->_authInfo)) {
            $elements[] = new OctetString($this->_authInfo);
        }
        return new Sequence(...$elements);
    }
    
    /**
     *
     * @see \X501\ASN1\AttributeValue\AttributeValue::stringValue()
     * @return string
     */
    public function stringValue(): string
    {
        return "#" . bin2hex($this->toASN1()->toDER());
    }
    
    /**
     *
     * @see \X501\ASN1\AttributeValue\AttributeValue::equalityMatchingRule()
     * @return BinaryMatch
     */
    public function equalityMatchingRule(): BinaryMatch
    {
        return new BinaryMatch();
    }
    
    /**
     *
     * @see \X501\ASN1\AttributeValue\AttributeValue::rfc2253String()
     * @return string
     */
    public function rfc2253String(): string
    {
        return $this->stringValue();
    }
    
    /**
     *
     * @see \X501\ASN1\AttributeValue\AttributeValue::_transcodedString()
     * @return string
     */
    protected function _transcodedString(): string
    {
        return $this->stringValue();
    }
}

<?php

declare(strict_types=1);

use Sop\CryptoEncoding\PEM;
use Sop\CryptoEncoding\PEMBundle;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\ECDSAWithSHA256AlgorithmIdentifier;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\AttributeCertificate\AttCertIssuer;
use X509\AttributeCertificate\AttCertValidityPeriod;
use X509\AttributeCertificate\AttributeCertificate;
use X509\AttributeCertificate\AttributeCertificateInfo;
use X509\AttributeCertificate\Attributes;
use X509\AttributeCertificate\Holder;
use X509\AttributeCertificate\Validation\ACValidationConfig;
use X509\AttributeCertificate\Validation\ACValidator;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;
use X509\Certificate\Extension\TargetInformationExtension;
use X509\Certificate\Extension\Target\TargetName;
use X509\CertificationPath\CertificationPath;
use X509\GeneralName\DNSName;

/**
 * @group ac-validation
 */
class PassingACValidationIntegrationTest extends PHPUnit_Framework_TestCase
{
    private static $_holderPath;
    
    private static $_issuerPath;
    
    private static $_ac;
    
    public static function setUpBeforeClass()
    {
        $root_ca = Certificate::fromPEM(
            PEM::fromFile(TEST_ASSETS_DIR . "/certs/acme-ca.pem"));
        $interms = CertificateBundle::fromPEMBundle(
            PEMBundle::fromFile(
                TEST_ASSETS_DIR . "/certs/intermediate-bundle.pem"));
        $holder = Certificate::fromPEM(
            PEM::fromFile(TEST_ASSETS_DIR . "/certs/acme-rsa.pem"));
        $issuer = Certificate::fromPEM(
            PEM::fromFile(TEST_ASSETS_DIR . "/certs/acme-ecdsa.pem"));
        $issuer_pk = PrivateKeyInfo::fromPEM(
            PEM::fromFile(TEST_ASSETS_DIR . "/certs/keys/acme-ec.pem"));
        self::$_holderPath = CertificationPath::fromTrustAnchorToTarget(
            $root_ca, $holder, $interms);
        self::$_issuerPath = CertificationPath::fromTrustAnchorToTarget(
            $root_ca, $issuer, $interms);
        $aci = new AttributeCertificateInfo(Holder::fromPKC($holder),
            AttCertIssuer::fromPKC($issuer),
            AttCertValidityPeriod::fromStrings("now", "now + 1 hour"),
            new Attributes());
        $aci = $aci->withAdditionalExtensions(
            TargetInformationExtension::fromTargets(
                new TargetName(new DNSName("test"))));
        self::$_ac = $aci->sign(new ECDSAWithSHA256AlgorithmIdentifier(),
            $issuer_pk);
    }
    
    public static function tearDownAfterClass()
    {
        self::$_holderPath = null;
        self::$_issuerPath = null;
        self::$_ac = null;
    }
    
    public function testValidate()
    {
        $config = new ACValidationConfig(self::$_holderPath, self::$_issuerPath);
        $config = $config->withTargets(new TargetName(new DNSName("test")));
        $validator = new ACValidator(self::$_ac, $config);
        $this->assertInstanceOf(AttributeCertificate::class,
            $validator->validate());
    }
}

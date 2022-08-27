<?php
namespace Orolyn\Net\Sockets\Options;

use Orolyn\IO\File;

/**
 * @property ?string $peerName
 */
class SecureOptions extends Options
{
    /**
     * @return string|null
     */
    public function getPeerName(): ?string
    {
        return $this->get('peer_name');
    }

    /**
     * @param string|null $peerName
     */
    public function setPeerName(?string $peerName): static
    {
        return $this->set('peer_name', $peerName);
    }

    /**
     * @return bool|null
     */
    public function getVerifyPeer(): ?bool
    {
        return $this->get('verify_peer');
    }

    /**
     * @param bool|null $verifyPeer
     */
    public function setVerifyPeer(?bool $verifyPeer): static
    {
        return $this->set('verify_peer', $verifyPeer);
    }

    /**
     * @return bool|null
     */
    public function getVerifyPeerName(): ?bool
    {
        return $this->get('verify_peer_name');
    }

    /**
     * @param bool|null $verifyPeerName
     */
    public function setVerifyPeerName(?bool $verifyPeerName): static
    {
        return $this->set('verify_peer_name', $verifyPeerName);
    }

    /**
     * @return bool|null
     */
    public function getAllowSelfSigned(): ?bool
    {
        return $this->get('allow_self_signed');
    }

    /**
     * @param bool|null $allowSelfSigned
     */
    public function setAllowSelfSigned(?bool $allowSelfSigned): static
    {
        return $this->set('setAllowSelfSigned', $allowSelfSigned);
    }

    /**
     * @return File|null
     */
    public function getCaFile(): ?File
    {
        return new File($this->get('cafile'));
    }

    /**
     * @param File|null $caFile
     */
    public function setCaFile(?File $caFile): static
    {
        return $this->set('cafile', $caFile->getPath());
    }

    /**
     * @return File|null
     */
    public function getCaPath(): ?File
    {
        return new File($this->get('capath'));
    }

    /**
     * @param File|null $caPath
     */
    public function setCaPath(?File $caPath): static
    {
        return $this->set('capath', $caPath->getPath());
    }

    /**
     * @return File|null
     */
    public function getLocalCert(): ?File
    {
        return new File($this->get('local_cert'));
    }

    /**
     * @param File|null $localCert
     */
    public function setLocalCert(?File $localCert): static
    {
        return $this->set('local_cert', $localCert->getCanonicalPath());
    }

    /**
     * @return File|null
     */
    public function getLocalPk(): ?File
    {
        return new File($this->get('local_pk'));
    }

    /**
     * @param File|null $localPk
     */
    public function setLocalPk(?File $localPk): static
    {
        return $this->set('local_pk', $localPk->getCanonicalPath());
    }

    /**
     * @return string|null
     */
    public function getPassphrase(): ?string
    {
        return $this->get('passphrase');
    }

    /**
     * @param string|null $passphrase
     */
    public function setPassphrase(?string $passphrase): static
    {
        return $this->set('passphrase', $passphrase);
    }

    /**
     * @return int|null
     */
    public function getVerifyDepth(): ?int
    {
        return $this->get('verify_depth');
    }

    /**
     * @param int|null $verifyDepth
     */
    public function setVerifyDepth(?int $verifyDepth): static
    {
        return $this->set('verify_depth', $verifyDepth);
    }

    /**
     * @return bool|null
     */
    public function getCiphers(): ?bool
    {
        return $this->get('ciphers');
    }

    /**
     * @param bool|null $ciphers
     */
    public function setCiphers(?bool $ciphers): static
    {
        return $this->set('ciphers', $ciphers);
    }

    /**
     * @return bool|null
     */
    public function getCapturePeerCert(): ?bool
    {
        return $this->get('capture_peer_cert');
    }

    /**
     * @param bool|null $capturePeerCert
     */
    public function setCapturePeerCert(?bool $capturePeerCert): static
    {
        return $this->set('capture_peer_cert', $capturePeerCert);
    }

    /**
     * @return bool|null
     */
    public function getCapturePeerCertChain(): ?bool
    {
        return $this->get('capture_peer_cert_chain');
    }

    /**
     * @param bool|null $capturePeerCertChain
     */
    public function setCapturePeerCertChain(?bool $capturePeerCertChain): static
    {
        return $this->set('capture_peer_cert_chain', $capturePeerCertChain);
    }

    /**
     * @return bool|null
     */
    public function getSniEnabled(): ?bool
    {
        return $this->get('SNI_enabled');
    }

    /**
     * @param bool|null $sniEnabled
     */
    public function setSniEnabled(?bool $sniEnabled): static
    {
        return $this->set('SNI_enabled', $sniEnabled);
    }

    /**
     * @return bool|null
     */
    public function getDisableCompression(): ?bool
    {
        return $this->get('disable_compression');
    }

    /**
     * @param bool|null $disableCompression
     */
    public function setDisableCompression(?bool $disableCompression): static
    {
        return $this->set('disable_compression', $disableCompression);
    }

    /**
     * @return array|string|null
     */
    public function getPeerFingerprint(): array|string|null
    {
        return $this->get('peer_fingerprint');
    }

    /**
     * @param array|string|null $peerFingerprint
     */
    public function setPeerFingerprint(array|string|null $peerFingerprint): static
    {
        return $this->set('peer_fingerprint', $peerFingerprint);
    }

    /**
     * @return int|null
     */
    public function getSecurityLevel(): ?int
    {
        return $this->get('security_level');
    }

    /**
     * @param int|null $securityLevel
     */
    public function setSecurityLevel(?int $securityLevel): static
    {
        return $this->set('security_level', $securityLevel);
    }
}

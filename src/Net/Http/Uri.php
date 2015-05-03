<?php

namespace Orolyn\Net\Http;

class Uri
{
    private ?string $scheme = null;
    private ?string $user = null;
    private ?string $host = null;
    private ?int $port = null;
    private ?string $path = null;
    private ?string $query = null;
    private ?string $fragment = null;

    /**
     * @param string $value
     * @return Uri
     */
    public static function parseUri(string $value): Uri
    {
        $components = parse_url($value);

        $uri = new Uri();

        $uri->host = $components['host'] ?? null;
        $uri->port = $components['port'] ?? null;
        $uri->path = $components['path'] ?? null;

        $uri->scheme   = $components['scheme']   ?? null;
        $uri->query    = $components['query']    ?? null;
        $uri->fragment = $components['fragment'] ?? null;

        $uri->user = $components['user'] ?? null;

        if ($uri->user) {
            $uri->user .= $components['pass'] ? ':' . $components['user'] : null;
        }

        return $uri;
    }

    /**
     * @return string|null
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * @param string|null $scheme
     * @return void
     */
    public function setScheme(?string $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * @return string|null
     */
    public function getAuthority(): ?string
    {
        if (!$this->host) {
            return null;
        }

        $value = '';

        if ($this->user) {
            $value .= $this->user . '@';
        }

        $value .= $this->host;

        if ($this->port) {
            if (!(
                ('http'  === $this->scheme && 80  === $this->port) ||
                ('https' === $this->scheme && 443 === $this->port)
            )) {
                $value .= ':' . $this->port;
            }
        }

        return $value;
    }

    /**
     * @return string|null
     */
    public function getUserInfo(): ?string
    {
        return $this->user;
    }

    /**
     * @param string|null $user
     * @param string|null $password
     * @return void
     */
    public function setUserInfo(?string $user, ?string $password = null): void
    {
        $this->user = $user;

        if ((null !== $user) && $password) {
            $this->user .= ':' . $password;
        }
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     * @return void
     */
    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     * @return void
     */
    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     * @return void
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @param string|null $query
     * @return void
     */
    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    /**
     * @return string|null
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * @param string|null $fragment
     * @return void
     */
    public function setFragment(?string $fragment): void
    {
        $this->fragment = $fragment;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $value = '';

        if ($this->scheme) {
            $value .= $this->scheme . ':';
        }

        if ($authority = $this->getAuthority()) {
            $value .= '//' . $authority;
        }

        $path = $this->getPath();

        if ($path) {
            if ($authority && '/' !== $path[0]) {
                $path = '/' . $path;
            } else {
                $path = preg_replace('/^\/+/', '/', $path);
            }

            $value .= $path;
        }

        if ($this->query) {
            $value .= '?' . $this->query;
        }

        if ($this->fragment) {
            $value .= '#' . $this->fragment;
        }

        return $value;
    }
}

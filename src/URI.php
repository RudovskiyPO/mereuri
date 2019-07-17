<?php

namespace RudovskiyPO;

class URI
{
    private $link = '';

    public function __construct($link = '')
    {
        if ($link) {
            $this->link = $link;
        } else {
            $origin = self::defineOrigin();
            $path = self::definePath();

            $this->link = $origin . $path;
        }
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getScheme()
    {
        return $this->decompose()['scheme'] ?? '';
    }

    public function getHost()
    {
        return $this->decompose()['host'] ?? '';
    }

    public function getPath()
    {
        return $this->decompose()['path'] ?? '';
    }

    public function getQuery()
    {
        return $this->decompose()['query'] ?? '';
    }

    public function getPort()
    {
        return $this->decompose()['port'] ?? '';
    }

    public function getOrigin()
    {
        $scheme = $this->getScheme() . '://';
        $host = $this->getHost();
        $port = $this->getPort();

        if (empty($port)) {
            $port = '';
        } else {
            $port = ":$port";
        }

        return $scheme . $host . $port;
    }

    public function decompose(): array
    {
        return parse_url($this->link);
    }

    public function getSegments(): array
    {
        return explode('/', trim($this->getPath(), '/'));
    }

    public function getParams(): array
    {
        return explode('&', trim($this->getQuery(), '/'));
    }

    public function getParamsMap(): array
    {
        parse_str($this->getQuery(), $paramsMap);

        return $paramsMap;
    }

    public function __toString()
    {
        return $this->link;
    }

    public function toArray(): array
    {
        return [
            'Link'      => $this->getLink(),
            'Scheme'    => $this->getScheme(),
            'Host'      => $this->getHost(),
            'Path'      => $this->getPath(),
            'Query'     => $this->getQuery(),
            'Segments'  => $this->getSegments(),
            'Params'    => $this->getParams(),
            'ParamsMap' => $this->getParamsMap(),
            'Port'      => $this->getPort(),
            'Origin'    => $this->getOrigin(),
        ];
    }

    public function removeLangSegment(array $langs = [])
    {
        $segments = $this->getSegments();

        if (count($segments)) {
            $segment = array_shift($segments);
            if (in_array($segment, $langs)) {
                $origin = $this->getScheme() . '://' . $this->getHost();
                $path = '/' . implode('/', $segments);
                $query = !empty($this->getQuery()) ? '?' . $this->getQuery() : '';

                $this->setLink($origin . $path . $query);
            }
        }

        return $this;
    }

    /*
     * Static methods
     */
    public static function defineScheme()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ?
            "https://" : "http://";
    }

    public static function defineHost()
    {
        $host = isset($_SERVER['SERVER_NAME']) ?
            $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];

        return empty($host) ? self::defineServerIP() : $host;
    }

    public static function definePath()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public static function defineServerIP()
    {
        return $_SERVER['SERVER_ADDR'];
    }

    public static function defineServerPort()
    {
        return $_SERVER['SERVER_PORT'];
    }

    public static function defineOrigin()
    {
        $scheme = self::defineScheme();
        $host = self::defineHost();
        $port = self::defineServerPort();

        if ($host == 'localhost' && strpos($host, $port) === false) {
            $port = ":$port";
        } else {
            $port = '';
        }

        return $scheme . $host . $port;
    }

    public static function congregateLink($path = '', $query = '', $scheme = null, $host = null, $port = null)
    {
        $link = $path;

        if ($scheme !== null && $host !== null) {
            $origin = $scheme . '://' . $host;

            if ($port !== null) {
                $origin = ':' . $port;
            }

            $link = $origin . '/' . $path;
        }

        if (!empty($query)) {
            $link .= '?' . $query;
        }

        return $link;
    }
}

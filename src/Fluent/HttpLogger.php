<?php
/**
 * Flunet http logger
 */
class FluentHttpLogger
{
    const DEFAULT_HTTP_PORT = 8888;

    protected $tag;
    protected $host;
    protected $port;
    protected $uri;

    /**
     * @param string $tag tag
     * @param string $host host
     * @param integer $port port
     */
    public function __construct($tag, $host, $port = self::DEFAULT_HTTP_PORT)
    {
        $this->tag = $tag;
        $this->host = $host;
        $this->port = $port;
        $this->uri = sprintf('http://%s:%d/%s', $host, $port, $tag);
    }

    public function send($data)
    {
        file_get_contents($this->uri . '?json=' . json_encode($data));
    }
}
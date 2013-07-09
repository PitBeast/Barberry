<?php
namespace Barberry;

class Response {

    /**
     * @var ContentType
     */
    public $contentType;
    public $body;
    public $code;
    public $hasConverted;

    public static function notFound() {
        return new self(ContentType::json(), '{}', 404);
    }

    public static function notImplemented($msg) {
        return new self(
            ContentType::json(),
            json_encode(array('msg' => $msg)),
            501
        );
    }

    public static function serverError() {
        return new self(ContentType::json(), '{}', 500);
    }

    public function __construct(ContentType $contentType, $body, $code = 200, $hasConverted = true) {
        $this->contentType = $contentType;
        $this->body = $body;
        $this->code = $code;
        $this->hasConverted = $hasConverted;
    }

    public function send() {
        header('HTTP/1.1 ' . $this->code);
        header('Content-Type: ' . strval($this->contentType));
        echo $this->body;
    }
}
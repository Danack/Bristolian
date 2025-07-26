<?php

namespace Bristolian\Model;

class WebPushNotification
{
    private string $title;

    private string $body;

    // 'vibrate' => [500,110,500,110,450,110,200,110,170,40,450,110,200,110,170,40,500],
    /**
     * @var null
     * @ var null|int[]
     */
    private $vibrate = null;

    private string $sound = "/sounds/meow.mp3";

    /**
     * @var null
     *  // var null|array<int, mixed>
     */
    private $data = null;
        //'data' => [
        //'url' => '/tools'
        //]
        //];

    public static function create(string $title, string $body): self
    {
        $instance = new self();
        $instance->title = $title;
        $instance->body = $body;

        return $instance;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return null
     */
    public function getVibrate()
    {
        return $this->vibrate;
    }

    /**
     * @return string
     */
    public function getSound(): string
    {
        return $this->sound;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }
}

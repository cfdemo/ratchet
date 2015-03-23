<?php
namespace CurrencyFair;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface
{
    // A lookup of all the topics clients have subscribed to
    protected $subscribedTopics;

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }

    public function onOpen(ConnectionInterface $conn) {
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }

    /**
     * @param string $message JSON string zero mq will pass
     */
    public function onMessageEntry($message)
    {
        $messageData = json_decode($message, true);

        $topicKey = $messageData['topic'];

        // If the lookup topic object isn't set there is no one to publish to
        if (!is_array($this->subscribedTopics) || (is_array($this->subscribedTopics) && !array_key_exists($topicKey, $this->subscribedTopics))) {
            return;
        }

        $topic = $this->subscribedTopics[$topicKey];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($messageData);
    }
}
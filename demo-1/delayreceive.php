<?php

/**
 * PHP amqp(RabbitMQ) Demo-1
 * @author  yuansir <yuansir@live.cn/yuansir-web.com>
 */
date_default_timezone_set('PRC');

$exchangeName = 'activity_user_grade_ex';
$queueName = 'activity_user_grade_q';
$routeKey = 'activity_user_grade_rk';
$exchangeNameDelay = 'activity_user_grade_ex.delay';
$queueNameDelay = 'activity_user_grade_q.delay';


$connection = new AMQPConnection(array('host' => '127.0.0.1', 'port' => '5672', 'vhost' => '/', 'login' => 'guest', 'password' => 'guest'));
$connection->connect() or die("Cannot connect to the broker!\n");
$channel = new AMQPChannel($connection);

//声明正常交换器
$exchange = new AMQPExchange($channel);
$exchange->setName($exchangeName);
$exchange->setType(AMQP_EX_TYPE_DIRECT);
$exchange->setFlags(AMQP_DURABLE);
$exchange->declare();

//声明延迟交换器
$exchangeDelay = new AMQPExchange($channel);
$exchangeDelay->setName($exchangeNameDelay);
$exchangeDelay->setType(AMQP_EX_TYPE_DIRECT);
$exchangeDelay->setFlags(AMQP_DURABLE);
$exchangeDelay->declare();

//声明延迟队列
$queueDelay = new AMQPQueue($channel);
$queueDelay->setName($queueNameDelay);
$queueDelay->setFlags(AMQP_DURABLE);
$queueDelay->declare();
$queueDelay->bind($exchangeNameDelay, $routeKey);

while (TRUE) {
        $queueDelay->consume('callback');
}
$connection->disconnect();

function callback($envelope, $queue) {
        $msg = $envelope->getBody();
        echo '['.date('Y-m-d H:i:s')."] " . $msg.PHP_EOL.PHP_EOL;
        $queue->nack($envelope->getDeliveryTag());
}


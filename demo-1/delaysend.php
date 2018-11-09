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

try {
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
        
        //声明正常的队列
        $queue = new AMQPQueue($channel);
        $queue->setName($queueName);
        $queue->setFlags(AMQP_DURABLE);
        $queue->setArguments(['x-dead-letter-exchange'=>$exchangeNameDelay]);
        $queue->declare();
        $queue->bind($exchangeName, $routeKey);
        
        //声明延迟队列
        $queueDelay = new AMQPQueue($channel);
        $queueDelay->setName($queueNameDelay);
        $queueDelay->setFlags(AMQP_DURABLE);
        $queueDelay->declare();
        $queueDelay->bind($exchangeNameDelay, $routeKey);
        
        //发送消息
        $messages[3] = '['.date('Y-m-d H:i:s').']  我是间隔3分钟的数据';
        $messages[2] = '['.date('Y-m-d H:i:s').']  我是间隔2分钟的数据';
        $messages[4] = '['.date('Y-m-d H:i:s').']  我是间隔4分钟的数据';
        $messages[1] = '['.date('Y-m-d H:i:s').']  我是间隔1分钟的数据';
        $messages[5] = '['.date('Y-m-d H:i:s').']  我是间隔5分钟的数据';
        foreach ($messages as $key => $message) {
            $exchange->publish($message, $routeKey, AMQP_MANDATORY, ['expiration'=>($key * 60000), 'delivery_mode'=>2]);
            echo $message.PHP_EOL;
        }
        
} catch (AMQPConnectionException $e) {
        var_dump($e);
        exit();
}
$connection->disconnect();








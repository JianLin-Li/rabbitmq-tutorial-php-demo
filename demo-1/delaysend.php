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

//优先级最高级别
$priority = 24;

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
        $queue->setArguments(['x-dead-letter-exchange'=>$exchangeNameDelay, 'x-max-priority'=>$priority]);
        $queue->declare();
        $queue->bind($exchangeName, $routeKey);
        
        //声明延迟队列
        $queueDelay = new AMQPQueue($channel);
        $queueDelay->setName($queueNameDelay);
        $queueDelay->setFlags(AMQP_DURABLE);
        $queueDelay->declare();
        $queueDelay->bind($exchangeNameDelay, $routeKey);
        
        //发送消息
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是12点的数据', 'h'=>12];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是12点的数据', 'h'=>12];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是12点的数据', 'h'=>12];
        
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是13点的数据', 'h'=>13];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是13点的数据', 'h'=>13];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是13点的数据', 'h'=>13];
        
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是14点的数据', 'h'=>14];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是14点的数据', 'h'=>14];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是14点的数据', 'h'=>14];
        
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是15点的数据', 'h'=>15];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是15点的数据', 'h'=>15];
        $messages[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是15点的数据', 'h'=>15];
        
        shuffle($messages);
        
        $temparr[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是打头阵的数据', 'h'=>12];
        $temparr[] = ['msg'=>'['.date('Y-m-d H:i:s').'] 我是15点的数据', 'h'=>15];
        $messages = array_merge($temparr, $messages);
        
        
        foreach ($messages as $key => $message) {
            $time = strtotime(date('Y-m-d ').$message['h'].':00:00');
            $pri = $priority - $message['h'];
            $expiration = ($time- time()) * 1000;
            $exchange->publish($message['msg'], $routeKey, AMQP_MANDATORY, ['expiration'=>$expiration, 'delivery_mode'=>2, 'priority'=>$pri]);
            echo $message['msg'].' '.$time.' '.$pri.' '.$expiration.' '.PHP_EOL;
        }
        
} catch (AMQPConnectionException $e) {
        var_dump($e);
        exit();
}
$connection->disconnect();








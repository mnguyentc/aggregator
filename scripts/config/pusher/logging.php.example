<?php

return array(
    'rootLogger' => array(
        'appenders' => array('default', 'console')
    ),
    'appenders' => array(
        'default' => array(
            'class' => 'LoggerAppenderDailyFile',
            'layout' => array(
                'class' => 'LoggerLayoutPattern',
                'params' => array(
                    'conversionPattern' => '%date %-5level %msg%n'
                )
            ),
            'params' => array(
                'datePattern' => 'Y-m-d',
                'file' => APP_PATH . '/log/pusher/pusher-%s.log',
                'append' => true
            )
        ),
        'console' => array(
            'class' => 'LoggerAppenderConsole',
            'layout' => array(
                'class' => 'LoggerLayoutPattern',
                'params' => array(
                    'conversionPattern' => '%msg%n'
                )
            ),
        ),
    )
);

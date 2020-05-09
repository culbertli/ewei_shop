<?php

/**
 * socket server配置文件，重启后生效
 */

// 开发模式开关
define('SOCKET_SERVER_DEBUG', false);

// 设置服务端IP
define('SOCKET_SERVER_IP', 'localhost');

// 设置服务端端口
define('SOCKET_SERVER_PORT', '9501');

// 设置是否启用SSL，如果站点用了https的话，false改成true，并配置下面的key文件和pem文件路径
define('SOCKET_SERVER_SSL', false);

// 设置SSL KEY文件路径
define('SOCKET_SERVER_SSL_KEY_FILE', '');

// 设置SSL CERT文件路径
define('SOCKET_SERVER_SSL_CERT_FILE', '');

// 设置启动的worker进程数
define('SOCKET_SERVER_WORKNUM', 8);

// 设置你的域名，如果用了https，请填写配置了https的那个域名
define('SOCKET_CLIENT_IP', '域名');

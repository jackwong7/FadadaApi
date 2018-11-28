"# FadadaApi" 

安装命令
composer require "jackwong/fadadaapi  ~1.0"


laravel5.5以下的包 需要在app配置文件的providers添加下面服务

JackWong\Fadada\FadadaServiceProvider::class

发布config文件
php artisan vendor:publish --provider="JackWong\Fadada\FadadaServiceProvider"
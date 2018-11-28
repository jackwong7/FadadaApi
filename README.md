"# FadadaApi" 

安装命令
composer require "jackwong/fadadaapi @dev"
laravel5.5以下的包 需要在app配置文件的providers添加下面服务
JackWong\Fadada\FadadaServiceProvider::class


php artisan vendor:publish --provider="JackWong\Fadada\FadadaServiceProvider"
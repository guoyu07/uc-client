Ucenter Client API For Laravel 5

# 安装

    composer install vergil-lai/uc-client
    
安装完成后，在`app/config/app.php`返回的数组的`providers`键加入：

    VergilLai\UcClient\ClientProvider::class,
    
在`aliases`键加入：

    'UcClient'  => VergilLai\UcClient\Facades\UcClient::class,
    
# 配置

运行命令发布配置文件：

    php artisan vendor:publish
    
# 方法：


Ucenter Client API For Laravel 5

# 说明

本类库是用在Laravel5的[UCenter](http://www.comsenz.com/products/ucenter/)客户端

客户端只能使用API的方式与Ucenter通讯

# 安装

    composer install vergil-lai/uc-client
    
安装完成后，在`app/config/app.php`返回的数组的`providers`键加入：

    VergilLai\UcClient\ClientProvider::class,
    
php5.5版本以下写成字符串：

    ‘VergilLai\UcClient\ClientProvider’
    
在`aliases`键加入：

    'UcClient'  => VergilLai\UcClient\Facades\UcClient::class,
    
php5.5版本以下写成字符串：

    'UcClient'  => ‘VergilLai\UcClient\Facades\UcClient’,
    
# 配置

运行命令发布配置文件：

    php artisan vendor:publish
    
## 配置说明

    return [
    
        /*
         * Ucenter的地址
         */
        'api' => env('UC_API'),
    
        /*
         * 通信密钥，必须药与Ucenter保持一致，否则该应用无法与Ucenter正常通信
         */
        'key' => env('UC_KEY'),
    
        /*
         * 应用ID，必须与Ucenter设置的一致
         */
        'appid' => env('UC_APPID'),
    
        /*
         * 处理Ucenter发出的通知的类名，需要实现接口：\VergilLai\UcClient\Contracts\UcenterNoteApi
         */
        'note_handler' => \VergilLai\UcClient\Note::class,
    
        /*
         * 应用接口文件名称，需要与Ucenter设置的一致，默认为uc.php
         */
        'apifilename' => env('UC_API_FILENAME', 'uc.php'),
    
        /*
         * 用户删除 API 接口开关
         */
        'api_deleteuser' => env('UC_API_DELETEUSER', 1),
    
        /*
         * 用户改名 API 接口开关
         */
        'api_renameuser' => env('UC_API_RENAMEUSER', 1),
    
        /*
         * 获取标签 API 接口开关
         */
        'api_gettag' => env('UC_API_GETTAG', 1),
    
        /*
         * 同步登录 API 接口开关
         */
        'api_synlogin' => env('UC_API_SYNLOGIN', 1),
    
        /*
         * 同步登出 API 接口开关
         */
        'api_synlogout' => env('UC_API_SYNLOGOUT', 1),
    
        /*
         * 更改用户密码 开关
         */
        'api_updatepw' => env('UC_API_UPDATEPW', 1),
    
        /*
         * 更新关键字列表 开关
         */
        'api_updatebadword' => env('UC_API_UPDATEBADWORDS', 1),
    
        /*
         * 更新域名解析缓存 开关
         */
        'api_updatehosts' => env('UC_API_UPDATEHOSTS'),
    
        /*
         * 更新应用列表 开关
         */
        'api_updateapps' => env('UC_API_UPDATEAPPS', 1),
    
        /*
         * 更新客户端缓存 开关
         */
        'api_updateclient' => env('UC_API_UPDATECLIENT', 1),
    
        /*
         * 更新用户积分 开关
         */
        'api_updatecredit' => env('UC_API_UPDATECREDIT', 1),
    
        /*
         * 向 UCenter 提供积分设置 开关
         */
        'api_getcreditsettings' => env('UC_API_GETCREDITSETTINGS', 1),
    
        /*
         * 获取用户的某项积分 开关
         */
        'api_getcredit' => env('UC_API_GETCREDIT', 1),
    
        /*
         * 更新应用积分设置 开关
         */
        'api_updatecreditsettings' => env('API_UPDATECREDITSETTINGS', 1),
    
        /*
         * cookie domain
         */
        'cookie_domain' => env('UC_COOKIE_DOMAIN', ''),
    
        /*
         * cookie path
         */
        'cookie_path' => env('UC_COOKIE_PATH', ''),
    ];
    
可添加以下配置到你的`.env`文件

    UC_API=http://your-ucenter-url
    UC_KEY=your-secret-key
    UC_APPID=1
    UC_API_FILENAME=uc.php
    
以下是接收通知开关，可选（1表示打开，0表示关闭）
    
    UC_API_DELETEUSER＝0
    UC_API_RENAMEUSER＝0
    UC_API_GETTAG＝0
    UC_API_SYNLOGIN＝0
    UC_API_SYNLOGOUT＝0
    UC_API_UPDATEPW＝0
    UC_API_UPDATEBADWORDS＝0
    UC_API_UPDATEHOSTS=0
    UC_API_UPDATEAPPS=0
    UC_API_UPDATECLIENT=0
    UC_API_UPDATECREDIT=0
    UC_API_GETCREDITSETTINGS=0
    UC_API_GETCREDIT=0
    UC_API_UPDATECREDITSETTINGS=0
    
# API接口

可以自定义处理接口的类，需要在`config/ucenter.php`的`note_handler`项处修改，需要实现`\VergilLai\UcClient\Contracts\UcenterNoteApi` interface 

具体方法的意义请查阅UCenter手册，或者查看`src/Contracts/UcenterNoteApi.php`的注释
    
# 方法

## 使用示例

    use UcClient;
    
    //使用Facade
    UcClient::getUser('username');

所有方法都是按照UCenter官方的`uc_client`所改写，只是原本失败返回负数值的地方，改为抛出`\VergilLai\UcClient\Exceptions\UcException`异常处理

原本返回枚举数组的地方，改成返回关联数组


### 例子
 
    array UcClient::userLogin(string $username, string $password, int $isuid = 0, int $checkques = 0, int $questionid = '', int $answer = '')
    

###＃ 返回值

成功返回数组，键含义如下所示。失败抛出异常`\VergilLai\UcClient\Exceptions\UcException`

uid: 用户ID

username: 用户名

password: 密码

email: Email

redeclare: 用户名是否重名


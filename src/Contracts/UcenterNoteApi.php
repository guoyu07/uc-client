<?php

namespace VergilLai\UcClient\Contracts;

/**
 * Interface UcenterNoteApi
 *
 * @author Vergil <vergil@vip.163.com>
 * @package VergilLai\UcClient\Contracts
 */
interface UcenterNoteApi
{
    const API_RETURN_SUCCEED = 1;

    const API_RETURN_FAILED = -1;

    const API_RETURN_FORBIDDEN = -2;

    /**
     * 过滤词语缓存key
     */
    const BADWORDS_CACHE_KEY = 'UC_BADWORDS';

    /**
     * 域名解析设置缓存key
     */
    const HOSTS_CACHE_KEY = 'UC_HOSTS';

    /**
     * 应用程序列表缓存key
     */
    const APPS_CACHE_KEY = 'UC_APPS';

    /**
     * UCenter 的基本设置缓存key
     */
    const SETTINGS_CACHE_KEY = 'UC_SETTINGS';

    /**
     * 此接口供仅测试连接。当 UCenter 发起 test 的接口请求时，
     * 如果成功获取到接口返回的 API_RETURN_SUCCEED 值，表示 UCenter 和应用通讯正常。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function test(array $get, array $post);

    /**
     * 当 UCenter 删除一个用户时，会发起 deleteuser 的接口请求，通知所有应用程序删除相应的用户。
     * 输入的参数放在 $get['ids'] 中，值为用逗号分隔的用户 ID。如果删除成功则输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function deleteuser(array $get, array $post);

    /**
     * 当 UCenter 更改一个用户的用户名时，会发起 renameuser 的接口请求，通知所有应用程序改名。
     * 输入的参数 $get['uid'] 表示用户 ID，$get['oldusername'] 表示旧用户名，$get['newusername'] 表示新用户名。
     * 如果修改成功则输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function renameuser(array $get, array $post);

    /**
     * 如果应用程序存在标签功能，可以通过此接口把应用程序的标签数据传递给 UCenter。
     * 输入的参数放在 $get['id'] 中，值为标签名称。输出的数组需经过 uc_serialize 处理。
     *
     * @param array $get
     * @param array $post
     * @return string
     */
    public function gettag(array $get, array $post);

    /**
     * 如果应用程序需要和其他应用程序进行同步登录，此部分代码负责标记指定用户的登录状态。
     * 输入的参数放在 $get['uid'] 中，值为用户 ID。此接口为通知接口，无输出内容。同步登录需使用 P3P 标准。
     *
     * @param array $get
     * @param array $post
     * @return void
     */
    public function synlogin(array $get, array $post);

    /**
     * 如果应用程序需要和其他应用程序进行同步退出登录，此部分代码负责撤销用户的登录的状态。
     * 此接口为通知接口，无输入参数和输出内容。同步退出需使用 P3P 标准。
     *
     * @param array $get
     * @param array $post
     * @return void
     */
    public function synlogout(array $get, array $post);

    /**
     * 当用户更改用户密码时，此接口负责接受 UCenter 发来的新密码。
     * 输入的参数 $get['username'] 表示用户名，$get['password'] 表示新密码。如果修改成功则输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function updatepw(array $get, array $post);

    /**
     * 当 UCenter 的词语过滤设置变更时，此接口负责通知所有应用程序更新后的词语过滤设置内容。
     * 设置内容用 POST 方式提交到接口。接口运行完毕输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function updatebadwords(array $get, array $post);

    /**
     * 当 UCenter 的域名解析设置变更时，此接口负责通知所有应用程序更新后的域名解析设置内容。
     * 设置内容用 POST 方式提交到接口。接口运行完毕输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function updatehosts(array $get, array $post);

    /**
     * 当 UCenter 的应用程序列表变更时，此接口负责通知所有应用程序更新后的应用程序列表。
     * 设置内容用 POST 方式提交到接口。接口运行完毕输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function updateapps(array $get, array $post);

    /**
     * 当 UCenter 的基本设置信息变更时，此接口负责通知所有应用程序更新后的基本设置内容。
     * 设置内容用 POST 方式提交到接口。接口运行完毕输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return mixed
     */
    public function updateclient(array $get, array $post);

    /**
     * 当某应用执行了积分兑换请求的接口函数 uc_credit_exchange_request() 后，此接口负责通知被兑换的目的应用程序所需修改的用户积分值。
     * 输入的参数 $get['credit'] 表示积分编号，$get['amount'] 表示积分的增减值，$get['uid'] 表示用户 ID。
     *
     * @param array $get
     * @param array $post
     * @return void
     */
    public function updatecredit(array $get, array $post);

    /**
     * 此接口用于把应用程序中指定用户的积分传递给 UCenter。
     * 输入的参数 $get['uid'] 为用户 ID，$get['credit'] 为积分编号。接口运行完毕输出积分值。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function getcredit(array $get, array $post);

    /**
     * 此接口负责把应用程序的积分设置传递给 UCenter，以供 UCenter 在积分兑换设置中使用。
     * 此接口无输入参数。输出的数组需经过 uc_serialize 处理。
     *
     * @param array $get
     * @param array $post
     * @return string
     */
    public function getcreditsettings(array $get, array $post);

    /**
     * 此接口负责接收 UCenter 积分兑换设置的参数。
     * 输入的参数放在 $get['credit'] 中，值为设置的参数数组。接口运行完毕输出 API_RETURN_SUCCEED。
     *
     * @param array $get
     * @param array $post
     * @return int
     */
    public function updatecreditsettings(array $get, array $post);

}
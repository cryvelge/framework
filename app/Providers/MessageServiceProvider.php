<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Components\Message\Manager;

class MessageServiceProvider extends ServiceProvider
{
    // 不再延迟，以免出现没有register的情况
    protected $defer = false;

    /**
     * 注册的notifier
     */
    protected $notifier = [
        'App\Components\Message\Notifier\GroupNotifier' => '_group',
        'App\Components\Message\Notifier\WechatNotifier' => 'wechat',
        'App\Components\Message\Notifier\MiniProgramNotifier' => 'mini_program',
        'App\Components\Message\Notifier\NotificationNotifier' => 'notification',
    ];

    /**
     * 注册的reader
     */
    protected $reader = [
        'App\Components\Message\Reader\NotificationReader' => 'notification',
    ];

    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('message', function($app) {
            $manager = Manager::instance();

            // 注册Notifier
            $classes = array_keys($this->notifier);
            for ($i = 0, $len = count($classes); $i < $len; $i++) {
                $class = $classes[$i];
                $topic = $this->notifier[$class];
                $manager->registerNotifier($topic, new $class);
            }

            // 注册Reader
            $classes = array_keys($this->reader);
            for ($i = 0, $len = count($classes); $i < $len; $i++) {
                $class = $classes[$i];
                $topic = $this->reader[$class];
                $manager->registerReader($topic, new $class);
            }

            return $manager;
        });
    }

    public function provides()
    {
        return ['message'];
    }
}

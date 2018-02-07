<?php

Route::group(['prefix' => 'wechat'], function() {
    Route::any('message', 'WeChatController@message');
    Route::any('payment_notify', 'WeChatController@paymentNotify');
});

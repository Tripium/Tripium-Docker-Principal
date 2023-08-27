<?php
//subscriber
use WooKit\Insight\Clicks\Controllers\ClickStatisticAPIController;
use WooKit\Insight\Clicks\Database\ClickStatisticTbl;
use WooKit\Insight\Subscribers\Controllers\SubscriberAPIController;
use WooKit\Insight\Subscribers\Database\SubscriberStatisticTbl;
use WooKit\Insight\Views\Controllers\ViewStatisticAPIController;
use WooKit\Insight\Views\Database\ViewStatisticTbl;

new SubscriberStatisticTbl();
new SubscriberAPIController();
//click Statistic
new ClickStatisticAPIController();
new ClickStatisticTbl();
//view Statistic
new ViewStatisticAPIController();
new ViewStatisticTbl();

<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/24
 * Time: 17:07
 */


return array(
    'tagDict' => array(
        '美食' => array('alias' => 'n_foods', 'manual_score' => 0),
        '旅游' => array('alias' => 'n_travel', 'manual_score' => 0),
        '教育资讯' => array('alias' => 'n_education', 'manual_score' => 0),
        '历史' => array('alias' => 'n_history', 'manual_score' => 0),
        '时尚' => array('alias' => 'n_fashion', 'manual_score' => 0),
        '房产' => array('alias' => 'n_property', 'manual_score' => 0),
        '家居' => array('alias' => 'n_furnishing', 'manual_score' => 0),
        '大陆时事' => array('alias' => 'n_mainland', 'manual_score' => 0),
        '创业' => array('alias' => 'n_startup', 'manual_score' => 0),
        '文化' => array('alias' => 'n_culture', 'manual_score' => 0),
        '星座' => array('alias' => 'n_starsign', 'manual_score' => 0),
        '台湾' => array('alias' => 'n_taiwan', 'manual_score' => 0),
        '汽车' => array('alias' => 'n_vehicle', 'manual_score' => 0),
        '动漫' => array('alias' => 'n_animation', 'manual_score' => 0),
        '数码' => array('alias' => 'n_digital', 'manual_score' => 0),
        '足球' => array('alias' => 'n_football', 'manual_score' => 0),
        '互联网' => array('alias' => 'n_internet', 'manual_score' => 0),
        '运动' => array('alias' => 'n_sports', 'manual_score' => 0),
        '社会资讯' => array('alias' => 'n_society', 'manual_score' => 0),
        '篮球' => array('alias' => 'n_basketball', 'manual_score' => 0),
        '国际' => array('alias' => 'n_international', 'manual_score' => 0),
        '健康' => array('alias' => 'n_fitness', 'manual_score' => 0),
//原有：******************************
//        '财经' => array('alias' => 'n_finance', 'manual_score' => 0),
//        '娱乐' => array('alias' => 'n_entertainment', 'manual_score' => 0),
//        '军事' => array('alias' => 'n_military', 'manual_score' => 0)
    ),
    //原始文件路径
    'raw_path' => '/data1/userclassify/raw',
    //每个用户标签上限
    'oneuser_tagslimit' => 10,
    'mongo_usertags' => array(
        'mongourl' => '127.0.0.1:27017',
        'option' => array()
    ),
    //标签有25个分类
    'tags_class_count' => 25,

    'factor_weight' => array(
        'order_score' => 1.5,
        'manual_score' => 4.5,
        'feedback_score' => 8.5
    )
//原有：**********************
//        'n_finance'
//        'n_entertainment'
//        'n_military'
);
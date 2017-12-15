<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<!--[if IE 7]><html class="no-js ie7 oldie" lang="en-US"> <![endif]-->
<!--[if IE 8]><html class="no-js ie8 oldie" lang="en-US"> <![endif]-->
<!--[if gt IE 8]><!-->
<html lang="en">
    <head>
        <meta charset="utf-8">
        <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Home</title>

        <!-- Font Icon -->
        <link href="/Public/Home/css/plugin/font-awesome.min.css" rel="stylesheet" type="text/css">

        <!-- CSS Global -->
        <link href="/Public/Home/css/plugin/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="/Public/Home/css/plugin/bootstrap-select.min.css" rel="stylesheet" type="text/css">
        <link href="/Public/Home/css/plugin/owl.carousel.css" rel="stylesheet" type="text/css">
        <link href="/Public/Home/css/plugin/animate.css" rel="stylesheet" type="text/css">
        <link href="/Public/Home/css/plugin/subscribe-better.css" rel="stylesheet" type="text/css">

        <!-- Custom CSS -->
        <link href="/Public/Home/css/style.css" rel="stylesheet" type="text/css">

        <link href="/Public/Plugins/layer/skin/layer.css" rel="stylesheet" type="text/css">

        <!-- Color CSS -->

        <!--[if lt IE 9]>
        <script src="/Public/Home/js/plugin/html5shiv.js"></script>
        <script src="/Public/Home/js/plugin/respond.js"></script>
        <![endif]-->
    </head>

    <body id="home" class="wide">



        <div id="loading">
            <div class="loader">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>

        <!-- WRAPPER -->
        <main class="wrapper home-wrap"> 
            <!-- CONTENT AREA -->

            <!-- Main Header Start -->
            <header class="main-header">                  
                <div class="container-fluid rel-div">
    <div class="col-lg-4 col-sm-8">
        <div class="main-logo">
            <a href="index.html"> <strong>naturix <img src="/Public/Home/img/icons/logo-icon.png" alt="" /> </strong> <span class="light-font">farmfood</span>  </a>
            <span class="medium-font">ORGANIC STORE</span>
        </div>
    </div>

    <div class="col-lg-6 responsive-menu">
        <div class="responsive-toggle fa fa-bars"> </div>
        <nav class="fix-navbar" id="primary-navigation">
            <ul class="primary-navbar">
                <li><a href="/Home/Index/index"> Home</a></li>
                <li><a href="/Home/Index/aboutUs">About Us</a></li>
                <li><a href="/Home/Index/shop"> shop</a></li>
                <li><a href="/Home/Index/shopSingle"> shop single </a></li>
                <li><a href="/Home/Index/myAccount"> my account </a></li>
                <li><a href="/Home/Index/contact">Contact Us</a></li>
            </ul>
        </nav>
    </div>

    <div class="col-lg-2 col-sm-4 cart-megamenu">
        <div class="cart-hover">
            <a href="#"> <img alt="" src="/Public/Home/img/icons/cart-icon.png" /></a>
            <span class="cnt crl-bg">2</span> <span class="price"><a href="#">$2.170.00</a></span>
            <ul class="pop-up-box cart-popup">
                <li class="cart-list">
                    <div class="cart-img"> <img src="/Public/Home/img/extra/cart-sm-1.jpg" alt=""> </div>
                    <div class="cart-title">
                        <div class="fsz-16">
                            <a href="#"> <span class="light-font"> organic </span>  <strong>almonds</strong></a>
                            <h6 class="sub-title-1">DRY FRUITS</h6>
                        </div>
                        <div class="price">
                            <strong class="clr-txt">$50.00 </strong> <del class="light-font">$65.00 </del>
                        </div>
                    </div>
                    <div class="close-icon"> <i class="fa fa-close clr-txt"></i> </div>
                </li>

                <li class="cart-list sub-total">
                    <div class="pull-left">
                        <strong>Subtotal</strong>
                    </div>
                    <div class="pull-right">
                        <strong class="clr-txt">$150.00</strong>
                    </div>
                </li>
                <li class="cart-list buttons">
                    <div class="pull-left">
                        <a href="cart.html" class="theme-btn-sm-2">View Cart</a>
                    </div>
                    <div class="pull-right">
                        <a href="checkout.html" class="theme-btn-sm-3"> Checkout </a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="mega-submenu">
            <span class="nav-trigger">
                <a class="menu-toggle" href="#"> <img src="/Public/Home/img/icons/menu.png" alt="" /> </a>
            </span>
            <div class="mega-dropdown-menu">
                <a class="menu-toggle fa fa-close" href="#">  </a>
                <div class="slider-mega-menu">
                    <?php if(is_array($_SESSION['cates'])): $key = 0; $__LIST__ = $_SESSION['cates'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate_one): $mod = ($key % 2 );++$key; if( $cate_one["pid"] == 0 ): ?><div class="menu-block">
                                <div class="menu-caption">
                                    <h2 class="menu-title"> <span class="light-font"> Fresh </span>  <strong><?php echo ($cate_one["cate_name"]); ?></strong> </h2>
                                    <ul class="sub-list">
                                        <?php if(is_array($_SESSION['cates'])): $i = 0; $__LIST__ = $_SESSION['cates'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate_two): $mod = ($i % 2 );++$i; if( $cate_two["pid"] == $cate_one["id"] ): ?><li> <a href="javascript:void(0);"><?php echo ($cate_two["cate_name"]); ?></a> </li>
                                                <ul style="font-size: 14px;display: none;">
                                                    <?php if(is_array($_SESSION['cates'])): $i = 0; $__LIST__ = $_SESSION['cates'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate_three): $mod = ($i % 2 );++$i; if( $cate_three["pid"] == $cate_two["id"] ): ?><li> <a href="#/id/<?php echo ($cate_three["id"]); ?>">&emsp;<?php echo ($cate_three["cate_name"]); ?></a> </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                                </ul><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </ul>
                                    <h2 class="title"> <a href="#" class="clr-txt"> All Fruits </a> </h2>
                                </div>
                                <div class="menu-img">
                                    <img alt="" src="/Public/Home/img/extra/menu-1.png" />
                                </div>
                            </div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
        <div class="responsive-toggle fa fa-bars"> </div>
    </div>

</div>
<div class="layer_login" style="display: none;">
    <div id="login_frame">
        <img src="/Public/Home/img/logo/logo_2x.png" width="106" height="36" data-baiduimageplus-ignore="1" class="logo">

        <div class="sign-up" style="display: none;">
            <div class="holder">
                <div class="with-line">用第三方帐号注册花瓣</div>
                <div class="buttons">
                    <a href="/oauth/weibo/instant_login/?_ref=frame" title="微博帐号登录" rel="nofollow" class="weibo"></a>
                    <a href="/oauth/qzone/instant_login/?_ref=frame" title="QQ帐号登录" rel="nofollow" class="qzone"></a>
                    <a href="/oauth/wechat/instant_login/?_ref=frame" title="微信帐号登录" rel="nofollow" class="wechat"></a>
                    <a href="/oauth/douban/instant_login/?_ref=frame" title="豆瓣帐号登录" rel="nofollow" class="douban"></a>
                </div>
                <a class="switch-email-signup brown-link">使用手机号/邮箱注册</a>
            </div>
            <div class="switch">已有帐号？<a class="brown-link botn-login">登录到花瓣</a></div>
        </div>
        <div style="display: block;" class="login">
            <div class="holder">
                <div class="with-line">使用第三方帐号登录</div>
                <div class="buttons small">
                    <a href="/oauth/weibo/instant_login/?_ref=frame" title="微博帐号登录" rel="nofollow" class="weibo"></a>
                    <a href="/oauth/qzone/instant_login/?_ref=frame" title="QQ帐号登录" rel="nofollow" class="qzone"></a>
                    <a href="/oauth/wechat/instant_login/?_ref=frame" title="微信帐号登录" rel="nofollow" class="wechat"></a>
                    <a href="/oauth/douban/instant_login/?_ref=frame" title="豆瓣帐号登录" rel="nofollow" class="douban"></a>
                    <a href="/oauth/renren/instant_login/?_ref=frame" title="人人帐号登录" rel="nofollow" style="margin-right: 0" class="renren"></a>
                </div>
                <div class="with-line">使用手机号/邮箱登录</div>
                <form action="" method="post" class="mail-login">
                    <input type="hidden" name="_ref" value="frame">
                    <input type="text" name="email" placeholder="输入用户名/手机号/邮箱" class="clear-input">
                    <input name="password" type="password" placeholder="密码" class="clear-input">
                    <a href="javascript:void(0);" class="btn btn18 rbtn"><span class="text"> 登录</span></a>
                </form>
                <a class="reset-password red-link">忘记密码»</a>
                <div class="switch-back">还没有花瓣帐号？
                    <a class="red-link register">点击注册»</a>
                </div>
            </div>
        </div>
        <div style="display: none" class="reset">
            <div class="holder">
                <div class="with-line">找回密码</div>
                <form class="reset-form">
                    <input type="text" name="email" placeholder="输入手机号或者邮箱" class="clear-input">
                    <a href="javascript:void(0);" class="btn btn18 rbtn"><span class="text"> 重置</span></a>
                </form>
                <a class="back red-link">又想起来了»</a>
            </div>
        </div>
        <div class="email-signup">
            <div style="display: none" class="signup-success">
                <div class="with-line">注册成功</div>
                <div class="text">验证邮件已经发送到
                    <span class="email">email</span>，请
                    <a href="" target="_blank" class="check-mail red-link">点击查收邮件</a>激活账号。
                    <br>没有收到激活邮件？请耐心等待，或者
                    <a class="resend red-link disabled">重新发送<span>30</span></a>
                </div>
                <a class="login-link red-link">« 返回登录页</a>
            </div>
            <div style="display: none" class="signup-form">
                <div class="holder">
                    <div class="with-line">使用手机号/邮箱注册</div>
                    <form action="" method="post" class="regi-form">
                        <input type="text" name="email" placeholder="输入手机号或者邮箱" class="clear-input">
                        <div id="captcha" class="captcha"></div>
                        <a href="javascript:void(0);" class="btn btn18 rbtn"><span class="text"> 注册</span></a>
                    </form>
                    <a class="email-signup-back brown-link">« 返回第三方帐号登录</a>
                </div>
            </div>
        </div>
        <div class="close">
            <i></i>
        </div>
    </div>
</div>

            </header>
            <!-- / Main Header Ends -->   

            <!-- Main Slider Start -->
            <section class="main-slide">
                <img alt=".." src="/Public/Home/img/slider/slide-1.jpg" />
                <div class="tbl-wrp slide-1">
                    <div class="text-middle">
                        <div class="tbl-cell">
                            <div class="slide-caption container text-center">
                                <div class="slide-title">
                                    <img src="/Public/Home/img/icons/slide-txt-1.png" alt="" />
                                    <span>100% Guarantee</span>
                                </div>
                                <div class="slide-title2 pb-50">
                                    <h2 class="section-title"> <span class="light-font">Live </span> <strong>organic </strong> <span class="light-font">for live </span> <strong>healthy </strong> </h2>
                                    <h4 class="sub-title"> ORGANIC FRUITS, VEGETABLES, AND LOT MORE TO YOUR DOOR </h4>
                                </div>
                                <div class="slide">
                                    <a href="#" class="slide-btn"> Shop Now</a>  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- / Main Slider Ends -->   

            <!-- Organic All Starts-->
            <section class="organic-all sec-space-bottom">
                <div class="pattern"> 
                    <img alt="" src="/Public/Home/img/icons/white-pattern.png" />
                </div>
                <div class="section-icon"> 
                    <img alt="" src="/Public/Home/img/icons/icon-1.png" />
                </div>
                <div class="container">                    
                    <div class="organic-wrap"> 
                        <img class="logo-img" alt="" src="/Public/Home/img/logo/logo-1.png" />
                        <div class="tabs-box">
                            <ul class="theme-tabs">
                                <?php if(is_array($cate_goods)): $key = 0; $__LIST__ = $cate_goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($key % 2 );++$key;?><li <?php if( $key == 1 ): ?>class="active"<?php else: ?>class=""<?php endif; ?>>
                                    <a href="#product-tab-<?php echo ($key); ?>" data-toggle="tab"> <span class="light-font">organic </span> <strong><?php echo ($cate_goods[$key-1][0]['cn']); ?> </strong> </a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="container-fluid"> 
                    <div class="col-md-12"> 
                        <div class="tab-content organic-content row">
                            <?php if(is_array($cate_goods)): $k = 0; $__LIST__ = $cate_goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?><div id="product-tab-<?php echo ($k); ?>" <?php if( $k == 1 ): ?>class="tab-pane fade active in"<?php else: ?>class="tab-pane fade"<?php endif; ?>>
                                <div class="product-slider-1 dots-1"> 

                                    <?php if(is_array($v)): $i = 0; $__LIST__ = $v;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><div class="item" gid="<?php echo ($val["i"]); ?>">
                                        <div class="product-box"> 
                                            <div class="product-media"> 
                                                <img class="prod-img" alt="" src="<?php echo ($val['bi']); ?>" />
                                                <img class="shape" alt="" src="/Public/Home/img/icons/shap-small.png" />
                                                <?php if( $val['is_act'] == 1 ): ?><span class="prod-tag tag-1">new</span> <span class="prod-tag tag-2">sale</span><?php endif; ?>
                                                <div class="prod-icons"> 
                                                    <a href="javascript:void(0);" class="fa fa-heart"></a>
                                                    <a href="javascript:void(0);" class="fa fa-shopping-basket"></a>
                                                    <a  href="#product-preview" gid="<?php echo ($val['i']); ?>" data-id="<?php echo ($val['i']); ?>" data-toggle="modal" class="fa fa-expand"></a>
                                                </div>
                                            </div>                                           
                                            <div class="product-caption"> 
                                                <h3 class="product-title">
                                                    <a href="#/gid/<?php echo ($val['i']); ?>"> <span class="light-font"> organic </span>  <strong><?php echo ($val['n']); ?></strong></a>
                                                </h3>
                                                <div class="price"> 
                                                    <strong class="clr-txt">$<?php echo ($val['p']); ?> </strong> <del class="light-font">$<?php echo ($val['bp']); ?> </del>
                                                </div>
                                            </div>
                                        </div>
                                    </div><?php endforeach; endif; else: echo "" ;endif; ?>

                                </div>
                            </div><?php endforeach; endif; else: echo "" ;endif; ?>

                        </div>
                    </div>
                </div>
            </section>
            <!-- / Organic All Ends -->

            <!-- Organic Farmfood Starts-->
            <section class="organic-farm sec-space-top light-bg">

                <img alt="" src="/Public/Home/img/extra/sec-img-1.png" class="left-bg-img" />  
                <img alt="" src="/Public/Home/img/extra/sec-img-2.png" class="center-bg-img" />  

                <div class="container rel-div">
                    <div class="title-wrap">
                        <h2 class="section-title"> <span class="light-font">we are </span> <strong>organic farmfood <img src="/Public/Home/img/icons/logo-icon.png" alt="" /> </strong> </h2>
                        <h4 class="sub-title"> <span> ABOUT naturix FARMFOOD </span> </h4>
                        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. </p>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 text-center">
                            <div class="feature-wrap">
                                <img src="/Public/Home/img/extra/feature-1.png" alt="" />
                                <h3 class="title-1 ptb-15"> <span class="light-font">fresh from </span> <strong> naturix farm</strong> </h3>
                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy.  </p>
                                <a href="#" class="sm-bnt-wht">Read More</a>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 text-center">
                            <div class="feature-wrap">
                                <img src="/Public/Home/img/extra/feature-2.png" alt="" />
                                <h3 class="title-1 ptb-15"> <span class="light-font"> 100%</span> <strong> organic goods</strong> </h3>
                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy.  </p>
                                <a href="#" class="sm-bnt-wht">Read More</a>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 text-center">
                            <div class="feature-wrap">
                                <img src="/Public/Home/img/extra/feature-3.png" alt="" />
                                <h3 class="title-1 ptb-15"> <span class="light-font">premium </span> <strong> quality</strong> </h3>
                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy.  </p>
                                <a href="#" class="sm-bnt-wht">Read More</a>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 text-center">
                            <div class="feature-wrap">
                                <img src="/Public/Home/img/extra/feature-4.png" alt="" />
                                <h3 class="title-1 ptb-15"> <span class="light-font">100% </span> <strong>natural</strong> </h3>
                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy.  </p>
                                <a href="#" class="sm-bnt-wht">Read More</a>
                            </div>
                        </div>
                    </div>

                    <div class="rel-div feature-img">
                        <img src="/Public/Home/img/extra/feature.png" alt="" />
                    </div>
                </div>
            </section>
            <!-- / Organic Farmfood Ends -->

            <!-- Our Products Starts-->
            <section class="organic-product sec-space">
                <div class="container"> 
                    <div class="row sec-space-top"> 
                        <div class="col-lg-6 col-sm-12">
                            <div class="row">
                                <?php if(is_array($six_cates)): $i = 0; $__LIST__ = array_slice($six_cates,0,3,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><div class="col-sm-4"> 
                                    <div class="organic-prod">
                                        <img src="<?php echo ($val["img"]); ?>" alt="暂无图片" />
                                        <span class="divider"></span>
                                        <h3 class="title-1"> <a href="#/id/<?php echo ($val["id"]); ?>"> <span class="light-font">organic </span> <strong> <?php echo ($val["cn"]); ?></strong> </a> </h3>
                                        <a class="clr-txt font-2" href="#/id/<?php echo ($val["id"]); ?>"> <i> <?php echo ($val["sum"]); ?> items </i> </a>
                                    </div>
                                </div><?php endforeach; endif; else: echo "" ;endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12"> 
                            <div class="organic-prod-info">
                                <h4 class="sub-title">  FRESH FROM OUR FARM </h4>
                                <h2 class="section-title ptb-15"> <span class="light-font">220+ </span> <strong>fruits, vegetables </strong> <span class="light-font"> & </span> <strong> lot more</strong> </h2>
                                <p class="fsz-16">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. </p>
                            </div>
                        </div>
                    </div>
                    <div class="row sec-space-top"> 
                        <div class="col-lg-6 col-sm-12"> 
                            <div class="organic-prod-info">
                                <h4 class="sub-title">  FRESH FROM OUR FARM </h4>
                                <h2 class="section-title ptb-15"> <span class="light-font">115+ </span> <strong>organic juices </strong> <span class="light-font"> and </span> <strong> organic tea</strong> </h2>
                                <p class="fsz-16">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. </p>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12"> 
                            <div class="row">
                                <?php if(is_array($six_cates)): $i = 0; $__LIST__ = array_slice($six_cates,3,3,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><div class="col-sm-4"> 
                                    <div class="organic-prod">
                                        <img src="<?php echo ($val["img"]); ?>" alt="" />
                                        <span class="divider"></span>
                                        <h3 class="title-1"> <a href="#/id/<?php echo ($val["id"]); ?>"> <span class="light-font">organic </span> <strong> <?php echo ($val["cn"]); ?></strong> </a> </h3>
                                        <a class="clr-txt font-2" href="#/id/<?php echo ($val["id"]); ?>"> <i> <?php echo ($val["sum"]); ?> items </i> </a>
                                    </div>
                                </div><?php endforeach; endif; else: echo "" ;endif; ?>
                            </div>
                        </div>                        
                    </div>
                </div>
            </section>
            <!-- / Our Products Ends -->            

            <!-- naturix Goods Starts-->
            <section class="naturix-goods sec-space-bottom">
                <div class="container"> 
                    <div class="title-wrap">
                        <h4 class="sub-title"> FRESH FROM OUR FARM </h4>
                        <h2 class="section-title"> <span class="round-shape">  <span class="light-font">naturix </span> <strong> organic goods <img src="/Public/Home/img/icons/logo-icon.png" alt="" /></strong> </span> </h2>
                    </div>

                    <div class="tabs-box text-center">
                        <ul class="theme-tabs small">
                            <?php if(is_array($new_shop_goods)): $k = 0; $__LIST__ = $new_shop_goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?><li <?php if( $k == 1 ): ?>class="active"<?php else: ?>class=""<?php endif; ?>>
                                <a href="#naturix-tab-<?php echo ($k); ?>" data-toggle="tab">
                                    <span class="light-font">all </span>
                                    <strong><?php echo ($new_shop_goods[$k-1][0][shop_name]); ?> </strong>
                                </a>
                            </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </div>

                    <div class="tab-content organic-content row">
                        <?php if(is_array($new_shop_goods)): $k = 0; $__LIST__ = $new_shop_goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?><div id="naturix-tab-<?php echo ($k); ?>" <?php if( $k == 1 ): ?>class="tab-pane fade active in" <?php else: ?> class="tab-pane fade"<?php endif; ?>>
                            <div class="naturix-slider-1 dots-1">

                                <?php if(is_array($v)): $key = 0; $__LIST__ = $v;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($key % 2 );++$key;?><div class="item" gid="<?php echo ($val["i"]); ?>">
                                            <div class="product-box">
                                                <div class="product-media">
                                                    <img class="prod-img" alt="" src="<?php echo ($val["bi"]); ?>" />
                                                    <img class="shape" alt="" src="/Public/Home/img/icons/shap-small.png" />
                                                    <?php if( $val['is_act'] == 1 ): ?><span class="prod-tag tag-1">new</span> <span class="prod-tag tag-2">sale</span><?php endif; ?>
                                                    <div class="prod-icons">
                                                        <a href="javascript:void(0);" class="fa fa-heart"></a>
                                                        <a href="javascript:void(0);" class="fa fa-shopping-basket"></a>
                                                        <a  href="#product-preview" data-toggle="modal" class="fa fa-expand"></a>
                                                    </div>
                                                </div>
                                                <div class="product-caption">
                                                    <h3 class="product-title">
                                                        <a href="#/gid/<?php echo ($val["i"]); ?>"> <span class="light-font"> organic </span>  <strong><?php echo ($val["n"]); ?></strong></a>
                                                    </h3>
                                                    <div class="price">
                                                        <strong class="clr-txt">$<?php echo ($val["p"]); ?> </strong> <del class="light-font">$<?php echo ($val["bp"]); ?> </del>
                                                    </div>
                                                </div>
                                            </div>
                                    </div><?php endforeach; endif; else: echo "" ;endif; ?>

                            </div>
                        </div><?php endforeach; endif; else: echo "" ;endif; ?>
                    </div>

                </div>
            </section>
            <!-- / naturix Goods Ends -->

            <!-- Deals Starts-->
            <section class="deals sec-space light-bg">
                <img alt="" src="/Public/Home/img/extra/sec-img-3.png" class="right-bg-img" />  
                <img alt="" src="/Public/Home/img/extra/sec-img-4.png" class="left-bg-img" />  

                <div class="container"> 
                    <div class="row"> 
                        <div class="col-sm-5 text-right"> 
                            <h4 class="sub-title"> naturix DEAL OF THE DAY </h4>
                            <h2 class="section-title"> <span class="light-font">organic goods </span> <strong>50% </strong> <span class="light-font">off</span> </h2>
                        </div>
                        <div class="col-sm-2 text-center no-padding"> 
                            <img alt="" src="/Public/Home/img/extra/deal.png" />
                        </div>
                        <div class="col-sm-5"> 
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. </p>
                        </div>
                    </div>
                    <div class="deal-count">
                        <div class="countdown-wrapper">
                            <div id="defaultCountdown" class="countdown"></div>
                        </div>  
                    </div>
                    <div class="deal-slider dots-2">
                        <?php if(is_array($act_goods)): $i = 0; $__LIST__ = $act_goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><div class="item" gid="<?php echo ($val["i"]); ?>">
                            <div class="deal-item">
                                <div class="deal-icons">                                         
                                    <a href="javascript:void(0);" class="fa fa-heart"></a>
                                    <a href="javascript:void(0);" class="fa fa-shopping-basket"></a>
                                    <a href="#product-preview" data-toggle="modal" class="fa fa-expand"></a>
                                </div>
                                <div class="deal-content">
                                    <div class="text-right">
                                        <span class="prod-tag tag-1">new</span> <span class="prod-tag tag-2">sale</span>
                                    </div>
                                    <div class="deal-text">
                                        <h4 class="sub-title"> ORGANIC FRUITS </h4>
                                        <h2 class="fsz-30 pb-15"> <a href="#/gid/<?php echo ($val["i"]); ?>"> <span class="light-font">fresh </span> <strong><?php echo ($val["n"]); ?> </strong> </a> </h2>
                                        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy...</p>
                                        <div class="price pt-15"> 
                                            <strong class="clr-txt">$<?php echo ($val["p"]); ?> </strong> <del class="light-font">$<?php echo ($val["bp"]); ?> </del>
                                        </div>
                                    </div>
                                    <div class="deal-img"> <img alt="" src="<?php echo ($val["bi"]); ?>" /> </div>
                                </div>
                            </div>
                        </div><?php endforeach; endif; else: echo "" ;endif; ?>
                    </div>
                </div>
            </section>
            <!-- / Deals Ends -->

            <!-- Random Products Starts-->
            <section class=" sec-space-bottom">
                <div class="pattern"> 
                    <img alt="" src="/Public/Home/img/icons/white-pattern.png">
                </div>
                <div class="section-icon"> 
                    <img alt="" src="/Public/Home/img/icons/icon-1.png">
                    <div class="pt-15 icon"> 
                        <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>  
                        <span class="light-font"> a taste of </span> <strong>real food</strong>
                        <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>  
                    </div>
                </div>
                <div class="container"> 
                    <!-- Random Products -->
                    <div class="row">
                        <?php if(is_array($new_three_goods)): $key = 0; $__LIST__ = $new_three_goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($key % 2 );++$key;?><div class="col-md-4 pt-50">
                            <h4 class="sub-title-sm"> NEW FROM THE FARM </h4>
                            <h2 class="fsz-30 pb-15"> <span class="light-font">organic </span> <strong><?php if( $key == 1 ): echo ($val[0]['cn']); elseif( $key == 2 ): echo ($val[1]['cn']); else: echo ($val[2]['cn']); endif; ?> </strong> </h2>
                            <div <?php if( $key == 1 ): ?>id="new-arrivals"<?php elseif( $key == 2 ): ?>id="best-seller"<?php else: ?>id="customer-needs"<?php endif; ?> class="nav-1">
                                <div class="item">
                                    <?php if(is_array($val)): $k = 0; $__LIST__ = $val;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k; if( $k % 2 == 0 ): ?><div class="random-prod"> 
                                        <div class="random-img"> 
                                            <img alt="" src="<?php echo ($v["bi"]); ?>" />
                                        </div>
                                        <div class="random-text"> 
                                            <h3 class="title-1 no-margin"> <a href="#"> <span class="light-font">organic </span> <strong><?php echo ($v["n"]); ?> </strong> </a> </h3>
                                            <span class="divider"></span>
                                            <div class="price"> 
                                                <strong class="clr-txt">$<?php echo ($v["p"]); ?> </strong> <del class="light-font">$<?php echo ($v["bp"]); ?> </del>
                                            </div>
                                        </div>
                                    </div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                </div>
                                <div class="item">
                                    <?php if(is_array($val)): $k = 0; $__LIST__ = $val;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k; if( $k % 2 != 0 ): ?><div class="random-prod">
                                            <div class="random-img">
                                                <img alt="" src="<?php echo ($v["bi"]); ?>" />
                                            </div>
                                            <div class="random-text">
                                                <h3 class="title-1 no-margin"> <a href="#"> <span class="light-font">organic </span> <strong><?php echo ($v["n"]); ?> </strong> </a> </h3>
                                                <span class="divider"></span>
                                                <div class="price">
                                                    <strong class="clr-txt">$<?php echo ($v["p"]); ?> </strong> <del class="light-font">$<?php echo ($v["bp"]); ?> </del>
                                                </div>
                                            </div>
                                        </div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                </div>
                            </div>
                        </div><?php endforeach; endif; else: echo "" ;endif; ?>
                    </div>

                    <!-- Banner -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="prod-banner green-banner">
                                <h4 class="title"> <span class="light-font"> 新鲜来自我们的农庄: </span> <strong> 绿橄榄 </strong> </h4>
                                <div class="banner-box">
                                    <div class="banner-content">
                                        <h3 class="title-sec">Vegetable</h3>
                                        <h2 class="section-title"> <span class="light-font">绿 </span> <strong>橄榄 </strong> </h2>
                                        <h4 class="sub-title"> 35% OFF IN JUNE-JULY </h4>
                                        <a href="#" class="btn"> <span> 查看更多 </span> <i class="fa fa-long-arrow-right"></i> </a>
                                    </div>
                                </div>
                                <img src="/Public/Home/img/extra/banner-1.png" alt=""  class="top-img" />
                                <img src="/Public/Home/img/extra/banner-2.png" alt=""  class="bottom-img" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="prod-banner orange-banner">
                                <h4 class="title"> <span class="light-font"> 盛夏   </span> <strong>果实 </strong> </h4>
                                <div class="banner-box">
                                    <div class="banner-content">
                                        <h3 class="title-sec">Fruits</h3>
                                        <h2 class="section-title"> <span class="light-font">所有  </span> <strong>夏季水果 </strong> </h2>
                                        <h4 class="sub-title"> 35% OFF IN JUNE-JULY </h4>
                                        <a href="#" class="btn"> <span> 查看更多 </span> <i class="fa fa-long-arrow-right"></i> </a>
                                    </div>
                                </div>
                                <img src="/Public/Home/img/extra/banner-3.png" alt=""  class="bottom-img" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Random Products Ends -->

            <!-- Subscribe Newsletter Starts-->
            <section class="subscribe-wrap sec-space light-bg">
                <img alt="" src="/Public/Home/img/extra/sec-img-5.png" class="right-bg-img" />  
                <img alt="" src="/Public/Home/img/extra/sec-img-6.png" class="left-bg-img" />  

                <div class="container"> 
                    <div class="row"> 
                        <div class="col-md-4"> 
                            <h4 class="sub-title"> 分享 我们的 实时讯息 </h4>
                            <h2 class="fsz-35"> <span class="light-font">订阅 </span> <strong> 讯息</strong> </h2>
                        </div>
                        <div class="col-md-8"> 
                            <form class="newsletter-form row">
                                <div class="form-group col-sm-8">
                                    <input class="form-control" placeholder="登记你的电子邮箱" required=""  type="text">
                                </div>
                                <div class="form-group col-sm-4">                                               
                                    <button class="theme-btn btn-block" type="submit"> 订阅 <i class="fa fa-long-arrow-right"></i> </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Subscribe Newsletter Ends -->

            <!-- Testimonials Starts-->
            <section class="">
                <div class="container"> 
                    <div class="testimonials">     
                        <div id="testimonial-slider" class="testimonial-slider nav-1"> 
                            <div class="item"> 
                                <div class="testi-wrap"> 
                                    <div class="testi-img"> 
                                        <a href="#"> <img src="/Public/Home/img/extra/feature-1.png" alt="" /> </a>
                                    </div>
                                    <div class="testi-caption"> 
                                        <p> <i>“双十二折扣大减，买二送二，还有属于你的专属定制礼品.”</i> </p>
                                        <a href="#"> <i class="fa fa-user clr-txt"></i> <strong> MK官方旗舰店--上海宝山区 </strong> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="item"> 
                                <div class="testi-wrap"> 
                                    <div class="testi-img"> 
                                        <a href="#"> <img src="/Public/Home/img/extra/feature-2.png" alt="" /> </a>
                                    </div>
                                    <div class="testi-caption"> 
                                        <p> <i>“雅诗兰黛倾情巨献，折扣立减，小黑瓶8折优惠.”</i> </p>
                                        <a href="#"> <i class="fa fa-user clr-txt"></i> <strong> MK官方旗舰店--武汉洪山区 </strong> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="testi-wrap">
                                    <div class="testi-img">
                                        <a href="#"> <img src="/Public/Home/img/extra/organic-3.png" alt="" /> </a>
                                    </div>
                                    <div class="testi-caption">
                                        <p> <i>“双十二折扣大减，买二送二，还有属于你的专属定制礼品.”</i> </p>
                                        <a href="#"> <i class="fa fa-user clr-txt"></i> <strong> MK官方旗舰店--北京朝阳区 </strong> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="testi-wrap">
                                    <div class="testi-img">
                                        <a href="#"> <img src="/Public/Home/img/extra/organic-4.png" alt="" /> </a>
                                    </div>
                                    <div class="testi-caption">
                                        <p> <i>“雅诗兰黛倾情巨献，折扣立减，小黑瓶8折优惠.”</i> </p>
                                        <a href="#"> <i class="fa fa-user clr-txt"></i> <strong> MK官方旗舰店--天门市 </strong> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Testimonials Ends -->

            <!-- latest news Starts-->
            <section class="sec-space">
                <div class="container"> 
                    <div class="title-wrap">
                        <h4 class="sub-title"> naturix BLOG </h4>
                        <h2 class="section-title"> <span class="light-font">naturix  </span> <strong>最新消息 </strong> </h2>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="latest-news">
                                <div class="news-img">
                                    <img src="/Public/Home/img/blog/blog-sm-1.jpg" alt="" />
                                </div>
                                <div class="news-caption">
                                    <h4 class="sub-title-sm"> 2017-12-12 </h4>
                                    <h2 class="title-2"> <a href="#"> <span class="light-font">5 best foods to make you </span> <strong>新鲜 & 健康</strong> </a> </h2>
                                    <span class="divider-1"></span>
                                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy...</p>
                                    <a href="#" class="fsz-12"> READ ARTICLE <i class="fa fa-long-arrow-right"></i> </a> 
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="latest-news">
                                <div class="news-img">
                                    <img src="/Public/Home/img/blog/blog-sm-2.jpg" alt="" />
                                </div>
                                <div class="news-caption">
                                    <h4 class="sub-title-sm"> 2017-11-11 </h4>
                                    <h2 class="title-2"> <a href="#"> <span class="light-font">5 best foods to make you </span> <strong>fresh & healthy</strong> </a> </h2>
                                    <span class="divider-1"></span>
                                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy...</p>
                                    <a href="#" class="fsz-12"> READ ARTICLE <i class="fa fa-long-arrow-right"></i> </a> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- latest news Ends -->

            <!-- / CONTENT AREA -->

            <!-- FOOTER -->
            <!-- FOOTER -->
            <footer class="page-footer">
                <section class="sec-space light-bg">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-3 col-sm-12 footer-widget">
                                <div class="main-logo">
                                    <a href="index.html"> <strong>naturix <img src="/Public/Home/img/icons/logo-icon.png" alt="" /> </strong> <span class="light-font">farmfood</span>  </a>
                                    <span class="medium-font">ORGANIC STORE</span>
                                </div>
                                <span class="divider-2"></span>
                                <div class="text-widget">
                                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna.</p>
                                    <ul>
                                        <li> <i class="fa fa-map-marker"></i> <span> <strong>100 highland ave,</strong> california, united state </span> </li>
                                        <li> <i class="fa fa-envelope-square"></i> <span><a href="#">email@lingjiuyi.cn</a> </span> </li>
                                        <li> <i class="fa fa-phone-square"></i> <span><a href="#">www.lingjiuyi.cn</a> </span> </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-4 footer-widget">
                                <h2 class="title-1">  <span class="light-font">naturix  </span> <strong>information </strong> </h2>
                                <span class="divider-2"></span>
                                <ul class="list">
                                    <li> <a href="#/aboutOurShop"> about our shop </a> </li>
                                    <li> <a href="#/topSellers"> top sellers </a> </li>
                                    <li> <a href="#/ourBlog"> our blog </a> </li>
                                    <li> <a href="#/newProducts"> new products </a> </li>
                                    <li> <a href="#/secureShopping"> secure shopping </a> </li>
                                </ul>
                            </div>
                            <div class="col-md-3 col-sm-4 footer-widget">
                                <h2 class="title-1">  <span class="light-font">my  </span> <strong>account </strong> </h2>
                                <span class="divider-2"></span>
                                <ul class="list">
                                    <li> <a href="#/myAccount"> my account </a> </li>
                                    <li><a href="#/accountInformation"> Account Information </a></li>
                                    <li><a href="#/addressBook"> Address Books</a></li>
                                    <li><a href="#/orderHistory"> Order History</a></li>
                                    <li><a href="#/reviewRating"> Reviews and Ratings</a></li>
                                    <li><a href="#/return"> Returns Requests</a></li>
                                    <li><a href="#/newsletter"> Newsletter</a></li>
                                </ul>
                            </div>
                            <div class="col-md-3 col-sm-4 footer-widget">
                                <h2 class="title-1">  <span class="light-font">photo  </span> <strong>instagram </strong> </h2>
                                <span class="divider-2"></span>
                                <ul class="instagram-widget">
                                    <li> <a href="#"> <img src="/Public/Home/img/extra/80x80-1.jpg" alt="" /> </a> </li>
                                    <li> <a href="#"> <img src="/Public/Home/img/extra/80x80-2.jpg" alt="" /> </a> </li>
                                    <li> <a href="#"> <img src="/Public/Home/img/extra/80x80-3.jpg" alt="" /> </a> </li>
                                    <li> <a href="#"> <img src="/Public/Home/img/extra/80x80-4.jpg" alt="" /> </a> </li>
                                    <li> <a href="#"> <img src="/Public/Home/img/extra/80x80-5.jpg" alt="" /> </a> </li>
                                    <li> <a href="#"> <img src="/Public/Home/img/extra/80x80-6.jpg" alt="" /> </a> </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="footer-bottom">
                    <div class="pattern">
                        <img alt="" src="/Public/Home/img/icons/white-pattern.png">
                    </div>
                    <div id="to-top" class="to-top"> <i class="fa fa-arrow-circle-o-up"></i> </div>
                    <div class="container ptb-50">
                        <div class="row">
                            <div class="col-md-6 col-sm-5">
                                <!--<p>Copyright &copy; 2017.Company name All rights reserved.<a target="_blank" href="http://sc.naturix.com/moban/">&#x7F51;&#x9875;&#x6A21;&#x677F;</a></p>-->
                                <p> &copy; 2017&emsp;零玖一&emsp;<a target="_blank" href="http://www.miitbeian.gov.cn/">京ICP备17070861号</a></p>

                            </div>
                            <div class="col-md-6 col-sm-7">
                                <ul class="primary-navbar footer-menu">
                                    <li> <a href="#">contact us </a> </li>
                                    <li> <a href="#">term of use  </a> </li>
                                    <li> <a href="#">site map  </a> </li>
                                    <li> <a href="#">privacy policy</a> </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </footer>
            <!-- /FOOTER -->

            <!-- /FOOTER -->
            <div id="to-top-mb" class="to-top mb"> <i class="fa fa-arrow-circle-o-up"></i> </div>
        </main>
        <!-- /WRAPPER -->

        <!-- Product Preview Popup -->
        <section class="modal fade" id="product-preview" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg product-modal">
                <div class="modal-content">
                    <a aria-hidden="true" data-dismiss="modal" class="sb-close-btn close" href="#"> <i class=" fa fa-close"></i> </a>

                    <div class="product-single pb-50 clearfix">
                        <!-- Single Products Slider Starts -->

                        <div class="col-lg-6 col-sm-8 col-sm-offset-2 col-lg-offset-0 pt-50">
                            <div class="prod-slider sync1 ">
                            </div>

                            <div  class="sync2">
                                <div class="item"> <a href="#"> <img src="/Public/Home/img/products/thumb-1.png" alt=""> </a> </div>
                                <div class="item"> <a href="#"> <img src="/Public/Home/img/products/thumb-2.png" alt=""> </a> </div>
                                <div class="item"> <a href="#"> <img src="/Public/Home/img/products/thumb-3.png" alt=""> </a> </div>
                                <div class="item"> <a href="#"> <img src="/Public/Home/img/products/thumb-1.png" alt=""> </a> </div>
                            </div>
                        </div>
                        <!-- Single Products Slider Ends -->

                        <div class="col-lg-6 pt-50">
                            <div class="product-content block-inline">

                                <div class="tag-rate">
                                    <span class="prod-tag tag-1">new</span> <span class="prod-tag tag-2">sale</span>
                                    <div class="rating">
                                        <span class="star active"></span>
                                        <span class="star active"></span>
                                        <span class="star active"></span>
                                        <span class="star active"></span>
                                        <span class="star active"></span>
                                        <span class="fsz-12"> Based on 25 reviews</span>
                                    </div>
                                </div>

                                <div class="single-caption">
                                    <h3 class="section-title">
                                        <a href="#"> <span class="light-font"> organic </span>  <strong>pinapple</strong></a>
                                    </h3>
                                    <span class="divider-2"></span>
                                    <p class="price">
                                        <strong class="clr-txt fsz-20">$50.00 </strong> <del class="light-font">$65.00 </del>
                                    </p>

                                    <div class="fsz-16">
                                        <p>Lorem ipsum dolor sit amet, consectetuer adiping elit food sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. </p>
                                    </div>

                                    <div class="prod-btns">
                                        <div class="quantity">
                                            <button class="btn minus"><i class="fa fa-minus-circle"></i></button>
                                            <input title="Qty" placeholder="1" class="form-control qty" type="text">
                                            <button class="btn plus"><i class="fa fa-plus-circle"></i></button>
                                        </div>
                                        <div class="sort-dropdown">
                                            <div class="search-selectpicker selectpicker-wrapper">
                                                <select class="selectpicker input-price"  data-width="100%"
                                                        data-toggle="tooltip">
                                                    <option>Kilo</option>
                                                    <option>2 Kilo</option>
                                                    <option>3 Kilo</option>
                                                    <option>4 Kilo</option>
                                                    <option>5 Kilo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group"><label class="checkbox-inline"><input value="" type="checkbox"> <span>Ready in stock</span></label> </div>
                                    </div>
                                    <ul class="meta">
                                        <li> <strong> SKU </strong> <span>:  AB2922-B</span> </li>
                                        <li> <strong> CATEGORY </strong> <span>:  Fruits</span> </li>
                                        <li class="tags-widget"> <strong> TAGS </strong> <span>:  <a href="#">fruits</a> <a href="#">vegetables</a> <a href="#">juices</a></span> </li>
                                    </ul>
                                    <div class="divider-full-1"></div>
                                    <div class="add-cart pt-15">
                                        <a href="#" class="theme-btn btn"> <strong> ADD TO CART </strong> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- / Product Preview Popup -->


        <!-- Subscribe Popup-Dark -->
        <section id="subscribe-popups" class="subscribe-me popups-wrap" aria-hidden="true">
            <div class="modal-content">
                <button type="button" class="sb-close-btn close popup-cls"> <i class="fa-times fa clr-txt"></i> </button>
                <div class="subscribe-wrap">
                    <div class="main-logo">
                        <a href="index.html"> <strong>naturix <img src="/Public/Home/img/icons/logo-icon.png" alt="" /> </strong> <span class="light-font">farmfood</span>  </a>
                    </div>

                    <div class="title-wrap">
                        <h2 class="section-title"> <strong>Subscribe Newsletter</strong> </h2>
                        <h4 class="fsz-12"> Join our newsletter for free & get latest news weekly </h4>
                    </div>

                    <form class="newsletter-form">
                        <div class="form-group">
                            <input class="form-control" placeholder="enter your email address" required="" type="text">
                        </div>
                        <div class="form-group">
                            <button class="theme-btn upper-text" type="submit"> <strong> subscribe </strong> </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <!-- / Subscribe Popup-Dark -->

        <!-- JS Global -->
        <script src="/Public/Home/js/plugin/jquery-2.2.4.min.js"></script>
        <script src="/Public/Home/js/plugin/bootstrap.min.js"></script>
        <script src="/Public/Home/js/plugin/bootstrap-select.min.js"></script>
        <script src="/Public/Home/js/plugin/owl.carousel.min.js"></script>
        <script src="/Public/Home/js/plugin/jquery.plugin.min.js"></script>
        <script src="/Public/Home/js/plugin/jquery.countdown.js"></script>
        <script src="/Public/Home/js/plugin/jquery.subscribe-better.min.js"></script>

        <!--自定义js-->
        <script src="/Public/Home/js/index.js"></script>

        <!-- Custom JS -->
        <script src="/Public/Home/js/theme.js"></script>

        <script src="/Public/Plugins/layer/layer.min.js"></script>






    </body>
</html>
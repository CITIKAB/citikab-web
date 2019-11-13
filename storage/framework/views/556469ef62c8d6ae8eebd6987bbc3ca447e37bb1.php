<!DOCTYPE html>
<head><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="<?php echo e($favicon); ?>">

</head>
<html lang="en-IN" xmlns:fb="http://ogp.me/ns/fb#">
<head>
  <title><?php echo e(isset($title) ? $title : Helpers::meta((!isset($exception)) ? Route::current()->uri() : '', 'title')); ?> <?php echo e(isset($additional_title) ? $additional_title : ''); ?></title>

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">


  <meta name="description" content="<?php echo e(Helpers::meta((!isset($exception)) ? Route::current()->uri() : '', 'description')); ?>">
  <meta name="keywords" content="<?php echo e(Helpers::meta((!isset($exception)) ? Route::current()->uri() : '', 'keywords')); ?>">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
  

  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700" rel="stylesheet"> 
  <?php echo Html::style('css/bootstrap.min.css'); ?>

  <?php echo Html::style('css/bootstrap.css'); ?>

  <?php echo Html::style('css/font-awesome.min.css'); ?>

  <?php echo Html::style('css/main.css'); ?>

  <?php echo Html::style('css/common.css'); ?>

  <?php echo Html::style('css/common1.css'); ?>

  <?php echo Html::style('css/styles.css'); ?>

  <?php echo Html::style('css/jquery.bxslider.css'); ?>

  <?php echo Html::style('css/jquery.sliderTabs.min.css'); ?>

  <?php if(Route::current()->uri() != 'driver_payment'): ?>
  <?php echo Html::style('css/jquery-ui.min.css'); ?> 
  <?php endif; ?>

  <link rel="stylesheet" type="text/css" href=" https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body ng-app="App">
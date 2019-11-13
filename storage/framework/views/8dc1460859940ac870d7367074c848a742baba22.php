   
<?php $__env->startSection('main'); ?>
	
<main role="main" id="site-content">
	<div class="page-container-responsive">
	  <div class="row-space-top-6 row-space-16 text-wrap">
	    <?php echo $content; ?>

	  </div>
	</div>
</main>
<?php $__env->startPush('scripts'); ?>
<script type="text/javascript">
$( document ).ready(function() {
 
 var base_url = "<?php echo url('/'); ?>";
 var user_token = '<?php echo Session::get('get_token'); ?>';

 if(user_token!='')
 {

  $('a[href*="'+base_url+'"]').attr('href' , 'javascript:void(0)');
 
 }

});

</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('templatesign', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
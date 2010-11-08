<div class="span-17">
	<div class="ur-formPage">

		<h3 style="color:#db605d;margin:0 0 0 1em;"><span style="color:gray;font-size:small;"><?php __('Forgot your password?');?></span></h3>	
		<h5 style="color:#db605d;margin:0 0 0 1em;"><?php __('Workroll will send password reset instructions to the email address associated with your account.');?></h5>	
		<?php echo $form->create('User', array(
																						'action' => 'reset',        
																					 	'inputDefaults' => array(
            																												'label' => false,
            																												'div' => false
        																														)		
		) ); ?>
		
		<div class="inputFormWrap">

			<div class="formWrapIn">
				<?php echo $form->input('email', array('type' => 'text', 'class' => 'form',  'label' => 'Please type your email address or Twitter username below') );?>	
			</div>
		</div>	
		
	
		<div class="" style="float:left;margin:0 0 1.5em 175px;">			
				<span><?php echo $form->button( __('Send instructions',true), array('type'=>'submit', 'id'=>'resetSubmit') ); ?></span>
		</div>
				
		<?php echo $form->end(); ?>
		
	</div>
</div>




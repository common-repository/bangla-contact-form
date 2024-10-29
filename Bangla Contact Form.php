<?php
/*
Plugin Name: Bangla Contact Form
Plugin URI: http://bdweblab.com/plugins/bangla-contact-form/
Description: Creative Bangla Contact Form with attachment support and also include Empty Form validation.</br>at first go to plugin editor and (Bangla Contact Form.php)findout 'ahrnetwork@gmail.com' then Replace This Email Address.Now [contact_form]  add this code to your new contact us page
Author: Cx Rana
Version: 1.0
Author URI: http://www.bdweblab.com
*/

function contact_form_markup() {

$form_action    = get_permalink();
$author_default = $_COOKIE['comment_author_'.COOKIEHASH];
$email_default  = $_COOKIE['comment_author_email_'.COOKIEHASH];

if ( ($_SESSION['contact_form_success']) ) {
$contact_form_success = '<p style="color: green"><strong>আপনার ই-মেইলের জন্যে ধন্যবাদ,আমরা আপনার সাথে যোগাযোগ করবো.</strong></p>';
unset($_SESSION['contact_form_success']);
}

$markup = <<<EOT

<div id="commentform"><h3>যোগাযোগ পাতা</h3>

	{$contact_form_success}
     
   <form onsubmit="return validateForm(this);" action="{$form_action}" method="post" enctype="multipart/form-data" style="text-align: left">
   
   <p><input type="text" name="author" id="author" value="{$author_default}" size="22" /> <label for="author"><strong>আপনার নাম*</strong></label></p>
   <p><input type="text" name="email" id="email" value="{$email_default}" size="22" /> <label for="email"><strong>ই মেইল *</strong></label></p>
   <p><input type="text" name="subject" id="subject" value="" size="22" /> <label for="subject"><strong>বিষয়*</strong></label></p>
   <p><textarea name="message" id="message" cols="100%" rows="10">এখানে মেসেজ লিখুন...</textarea></p>
   <p><label for="attachment"><strong>ফাইল</strong></label> <input type="file" name="attachment" id="attachment" /></p>
   <p><input name="send" type="submit" id="send" value="পাঠান" /></p>
   
   <input type="hidden" name="contact_form_submitted" value="1">
   
   </form>
   
</div>

EOT;

return $markup;

}

add_shortcode('contact_form', 'contact_form_markup');

function contact_form_process() {

session_start();

 if ( !isset($_POST['contact_form_submitted']) ) return;

 $author  = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : null;
 $email   = ( isset($_POST['email']) )   ? trim(strip_tags($_POST['email'])) : null;
 $subject = ( isset($_POST['subject']) ) ? trim(strip_tags($_POST['subject'])) : null;
 $message = ( isset($_POST['message']) ) ? trim(strip_tags($_POST['message'])) : null;

 if ( $author == '' ) wp_die('ইরর: প্রতিটি বক্সে লিখুন/আপনার নাম (name).'); 
 if ( !is_email($email) ) wp_die('ইরর: সঠিক ইমেইল এড্রেস দিন.');
 if ( $subject == '' ) wp_die('ইরর: কোন বিষয়ে তা লিখুন/বিষয় (subject).');
 
 //we will add e-mail sending support here soon
 
require_once ABSPATH . WPINC . '/class-phpmailer.php';
$mail_to_send = new PHPMailer();

$mail_to_send->FromName = $author;
$mail_to_send->From     = $email;
$mail_to_send->Subject  = $subject;
$mail_to_send->Body     = $message;

$mail_to_send->AddReplyTo($email);
$mail_to_send->AddAddress('ahrnetwork@gmail.com'); //contact form destination e-mail

if ( !$_FILES['attachment']['error'] == 4 ) { //something was send
	
	if ( $_FILES['attachment']['error'] == 0 && is_uploaded_file($_FILES['attachment']['tmp_name']) )
	
		$mail_to_send->AddAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
	
	else 
		
		wp_die('ইরর: নেট সমস্যার কারনে ফাইল আপলোড হচ্ছে না ,আবার ট্রাই করুন.(Please Try again later)');
		
}

if ( !$mail_to_send->Send() ) wp_die('ইরর : মেসেজ পাঠানো যাচ্ছে না - বাংলা কনট্যাক্ট ফর্ম সিস্টেম : ' . $mail_to_send->ErrorInfo);

$_SESSION['contact_form_success'] = 1;

 
 header('Location: ' . $_SERVER['HTTP_REFERER']);
 exit();

} 

add_action('init', 'contact_form_process');

function contact_form_js() { ?>

<script type="text/javascript">
function validateForm(form) {

	var errors = '';
	var regexpEmail = /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/;
		
	if (!form.author.value) errors += "ইরর : নাম লিখুন (name).\n";
	if (!regexpEmail.test(form.email.value)) errors += "ইরর : ইমেইল লিখুন.\n";
	if (!form.subject.value) errors += "ইরর : বিষয় লিখুন(subject).\n";

	if (errors != '') {
		alert(errors);
		return false;
	}
	
return true;
	
}
</script>

<?php }

add_action('wp_head', 'contact_form_js');

?>
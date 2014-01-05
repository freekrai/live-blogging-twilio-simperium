<?php

#error_reporting(E_ALL | E_STRICT);
#ini_set('display_errors', 1);

include("jolt.php");
require 'Services/simperium.php';
require 'Services/Twilio.php';
require 'functions.php';

$app = new Jolt();
$app->option('source', 'config.ini');

$client = new Services_Twilio($app->option('twilio.accountsid'), $app->option('twilio.authtoken') );
$fromNumber = $app->option('twilio.from');

$app->store('client',$client);

$simperium = new Simperium($app->option('simperium.appname'), $app->option('simperium.apikey') );
$simperium->set_token( $app->option('simperium.token') );

$app->store('simperium',$simperium);

$app->post('/listener', function(){
	$app = Jolt::getInstance();

	$bucket = (String)$app->option('simperium.bucket');

//	generate a unique UUID to use as the post id:
	$sms_id = $app->store('simperium')->generate_uuid();

	if ( isset($_POST['NumMedia']) && $_POST['NumMedia'] > 0 ){
		for ($i = 0; $i < $_POST['NumMedia']; $i++){
			if (strripos($_POST['MediaContentType'.$i], 'image') === False){
				continue;
			}
			$file = sha1($_POST['MediaUrl'.$i]).'.jpg';
			file_put_contents( 'images/original/'.$file, file_get_contents($_POST['MediaUrl'.$i]) );
			chmod ('images/original/'.$file, 01777);

			// Edit image
			$in = 'images/original/'.$file;
			$out = 'images/processed/'.$file;
			cropResize($in,$out,250);
			chmod ('images/processed/'.$file, 01777);

			// Remove Original Image
			unlink('images/original/'.$file);

			$app->store('simperium')->$bucket->post( $sms_id,array(
				'text'=>'<img src="'.$app->option('site.url').'/images/processed/'.$file.'" /><br />'.$_POST['Body'],
				'from'=>$_POST['From'],
				'image'=>$file,
				'timeStamp' => time(),
			) );


		}
		$message = $app->store('client')->account->messages->sendMessage(
			$app->option('twilio.from'), // From a valid Twilio number
			$_POST['From'], // Text this number
			"Posted!"
		);
		return true;
	}else{
		//	no image... text post
		$app->store('simperium')->$bucket->post( $sms_id,array(
			'text'=>$_POST['Body'],
			'from'=>$_POST['From'],
			'timeStamp' => time(),
		) );
		$message = $app->store('client')->account->messages->sendMessage(
			$app->option('twilio.from'), // From a valid Twilio number
			$_POST['From'], // Text this number
			"Posted!"
		);
		return true;
	}
});

$app->get('/', function(){
	$app = Jolt::getInstance();

	$app->render( 'home',array(
		'appname'=>$app->option('simperium.appname'), 
		'apikey'=>$app->option('simperium.apikey'),
		'token'=>$app->option('simperium.token'),
		'bucket'=>$app->option('simperium.bucket'),
	));
	exit;
});

$app->listen();
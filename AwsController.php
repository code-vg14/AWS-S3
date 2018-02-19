<?php
namespace App\Controller;

use App\Controller\AppController;
use Aws\CloudFront\CloudFrontClient;
use Aws\CloudFront\Exception\CloudFrontException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * @property \App\Model\Table\
 */
class AwsController extends AppController
{
	public function beforeFilter(Event $event){	
		parent::beforeFilter($event);
	}		
	//** This will get the S3 Object *//
	public function getS3Object(){	
		try {	
			$this->autoRender = false;
			$s3Credentials = Configure::read('S3_CREDENTIALS');
	
			$s3 = S3Client::factory([
					'credentials' => [
							'key' => $s3Credentials['KEY'],
							'secret' => $s3Credentials['SECRET']
					],
					'region' => $s3Credentials['REGION'],
					'version' => $s3Credentials['VERSION'],
						
			]);
				
			return $s3;
	
		}catch (S3Exception $e) {
			echo $e->getMessage() . "\n";
		}
	
	}
	//** Get S3 Preassigned urls for your content**//
	public function getS3Content($path = null){	
		try{
			$s3Credentials = Configure::read('S3_CREDENTIALS');
			$s3 = $this->getS3Object();
			$result = $s3->getObject(array(
					'Bucket' => $s3Credentials['BUCKET'],
					'Key'    => $path,
					'SaveAs' => $temp
			));
			$cmd = $s3->getCommand('GetObject', [
					'Bucket' => $s3Credentials['BUCKET'],
					'Key'    => $path,						
	
			]);					
			$request = $s3->createPresignedRequest($cmd, '+10 seconds');
			$presignedUrl = (string)$request->getUri();
			return $temp;
	
		}catch (S3Exception $e) {
			echo $e->getMessage() . "\n";
		}
			
	}
	
}

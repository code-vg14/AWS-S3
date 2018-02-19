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
 * Courses Controller
 *
 * @property \App\Model\Table\CoursesTable $Courses
 */
class AwsController extends AppController
{
	public function beforeFilter(Event $event){
	
		parent::beforeFilter($event);
		$this->Auth->allow('getCloudFrontContent');
	}
	public function getCloudFrontObject(){
		
		try{
			$this->autoRender = false;
			$cfCredentials = Configure::read('CLOUDFRONT_CREDENTIALS');
			$cloudFront = new CloudFrontClient([
					'region'  => $cfCredentials['REGION'],
					'version' => $cfCredentials['VERSION']
			]);	
				
			return $cloudFront;
			
		}catch (CloudFrontException $e) {
			echo $e->getMessage() . "\n";
		}
	}
	
	public function getCloudFrontContent($path = null){
		
		try{
			$this->autoRender = false;
			if(isset($this->request->data['path'])){
				$path = $this->request->data['path'];			
			}	
		//	$path = '/images/outlook-2016-capacitacion-large.png';		
			$cloudFront = $this->getCloudFrontObject();
			$cfCredentials = Configure::read('CLOUDFRONT_CREDENTIALS');			
			$expires = time() + 10;
			//$privateKey = $this->getS3Content($cfCredentials['PRIVATE_KEY']);			
			// Create a signed URL for the resource using the canned policy
			$signedUrlCannedPolicy = $cloudFront->getSignedUrl([
					'url'         => $cfCredentials['CF_CONTENT_URL'].$path,
					'expires'     => $expires,
					'private_key' => WWW_ROOT.'/'.$cfCredentials['PRIVATE_KEY'],
					'key_pair_id' => $cfCredentials['KEY_PAIR']
			]);			
			if ($this->request->is('ajax') &&  isset($this->request->data['path']))	{
				echo json_encode(['url'=>$signedUrlCannedPolicy]);
			}else{
				
				return $signedUrlCannedPolicy;
			}			
			
		}catch (CloudFrontException $e) {
			echo $e->getMessage() . "\n";
		}
		
	}
	
	
	public function checkFileExistsOnS3($path){
		
		try{			
			$this->autoRender = false;			
			$s3Credentials = Configure::read('S3_CREDENTIALS');
			$s3 = $this->getS3Object();
			$response = $s3->doesObjectExist($s3Credentials['BUCKET'],$path);
			return $response;	
		}catch (S3Exception $e) {
			echo $e->getMessage() . "\n";
		}		
	}
	
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
	
	public function getS3Content($path = null){
	
	
		$filepath = WWW_ROOT.'reference-pdf-files/a2.pdf';
		$root = '/tmp/';
		$prefix = null;
		$temp	= $root . $prefix . mt_rand() . '.pdf';
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
				
	
	
			$request = $s3->createPresignedRequest($cmd, '+20 minutes');
			$presignedUrl = (string)$request->getUri();
			/* 	$stream_wrapper = new StreamWrapper();
				$stream_wrapper->register($s3);
				$s3->registerStreamWrapper(); */
			return $temp;
	
		}catch (S3Exception $e) {
			echo $e->getMessage() . "\n";
		}
			
	}
	
}
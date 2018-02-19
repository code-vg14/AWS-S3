# AWS
Connect to AWS S3 and CloudFront using PHP 3
install aws SDK. You can get installation instructions on this link 
https://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/installation.html

use this method to fetch preassigned urls for your content
getS3Content($path); //** $path is the path to your content.

usage: 

      $s3Content = new AwsController();
			$file = $s3Content->getS3Content($path);

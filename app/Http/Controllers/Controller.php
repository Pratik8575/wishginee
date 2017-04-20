<?php

namespace Wishginee\Http\Controllers;

use Aws\Credentials\Credentials;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Routing\ResponseFactory;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    const PROFILE_PHOTO_URL = "https://s3.ap-south-1.amazonaws.com/wishginee-api/dummy.jpg";

    const COVER_PHOTO_URL = "https://s3.ap-south-1.amazonaws.com/wishginee-api/cover.jpg";

    /**
     * Stores the S3Client Instance
     * @var S3Client
     */
    protected $s3Client;

    /**
     * Stores the AWS Configurations from the config folder
     * @var mixed
     */
    protected $s3Configurations;

    /**
     * Stores the list of buckets of AWS wishginee-api
     * @var mixed|null
     */
    protected $buckets;


    private $guard;

    /**
     * Returns the guard instance
     * @return mixed
     */
    protected function guard()
    {
        if (is_null($this->guard)) {
            $this->guard = auth()->guard();
        }

        return $this->guard;
    }


    /**
     * Setups the AWS S3 Client and AWS Buckets
     * Controller constructor.
     */
    public function __construct()
    {
        $this->s3Configurations = config('aws.config');
        $this->s3Client = new S3Client([
            'credentials' => new Credentials($this->s3Configurations['credentials']['aws_access_key_id'], $this->s3Configurations['credentials']['aws_secret_key']),
            'region' => $this->s3Configurations['region'],
            'version' => $this->s3Configurations['version']
        ]);
        try{
            $this->buckets = $this->s3Client->listBuckets()['Buckets'];
        }catch (S3Exception $e){
            throw new \Exception($e->getMessage(), $e->getStatusCode());
        }
        
    }

    /**
     * Uploads the Photo to AWS Bucket
     * @param $photo
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public function uploadPhotoToAWS($id, $photo, $type = "profile_photo"){
        try{
            $this->deletePhotoFromAWS($id, $type);
            $response = [];
            $response = $this->s3Client->putObject([
                'Bucket' => $this->buckets[$type],
                'Key' => $id,
                'Body' => fopen($photo, 'r+'),
                'ACL' => 'public-read'
            ]);
            if(!is_null($response['ObjectUrl'])){
                return $response['ObjectUrl'];
            }
            return null;    
        }catch (S3Exception $e){
            throw new \Exception($e->getMessage(), $e->getStatusCode());
        }
        
    }


    /**
     * Deletes Photo associated with the photoId from AWS Bucket
     * @param $photoId
     * @param string $type
     * @throws \Exception
     */
    public function deletePhotoFromAWS($photoId, $type = "profile_photo"){
        try{
            if(!is_null($this->s3Client->getObjectUrl($this->buckets[$type], $photoId))) {
                $this->s3Client->deleteObject([$this->buckets[$type], $photoId]);
            }   
        }catch (S3Exception $e){
            throw   new \Exception($e->getMessage(), $e->getStatusCode());
        }
    }

}

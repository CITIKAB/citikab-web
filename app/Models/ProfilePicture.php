<?php

/**
 * Profile Picture Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Profile Picture
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePicture extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'profile_picture';

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    public $appends = ['header_src', 'email_src'];

    // Get picture source URL based on photo_source
    public function getSrcAttribute()
    {

         $url = \App::runningInConsole() ? SITE_URL : url('/');


        if(@$this->attributes['src']=="")
        {
            $src = $url.'/images/user.jpeg';
        }
    	else if($this->attributes['photo_source'] == 'Google')
        {
    		 $src = $this->attributes['src'];
        }     
        else if($this->attributes['photo_source'] == 'Local')
        {
            $picture_details = pathinfo($this->attributes['src']);

                 if($picture_details['filename'] != 'user')
                {
                    $src =  $url.'/images/users/'.$this->attributes['user_id'].'/'.@$picture_details['filename'].'.'.@$picture_details['extension'];  
                }
                else
                {
                   $src =  $url.'/images/users/'.@$picture_details['filename'].'.'.@$picture_details['extension']; 
                }
           
        }
        else
            $src = @$this->attributes['src'];

      
        return $src;
    }

    // Get header picture source URL based on photo_source
    public function getHeaderSrcAttribute()
    {
        if($this->attributes['photo_source'] == 'Facebook')
            $src = str_replace('large', 'small', $this->attributes['src']);
        else
            $src = $this->attributes['src'];

        if($src == '')
            $src = url('images/user.jpeg');
        else if($this->attributes['photo_source'] == 'Local'){
            $picture_details = pathinfo($this->attributes['src']);
            $src = url('images/users/'.$this->attributes['user_id'].'/'.@$picture_details['filename'].'.'.@$picture_details['extension']);        }

        return $src;
    }
    //mobile hearder picture src 
      public function getHeaderSrc510Attribute()
    {
        if($this->attributes['photo_source'] == 'Facebook')
            $src = str_replace('large', 'small', $this->attributes['src']);
        else
            $src = $this->attributes['src'];

        if($src == '')
            $src = url('images/user.jpeg');
        else if($this->attributes['photo_source'] == 'Local'){
            $picture_details = pathinfo($this->attributes['src']);
             $src = url('images/users/'.$this->attributes['user_id'].'/'.@$picture_details['filename'].'.'.@$picture_details['extension']);
        }

        return $src;
    }

    public function getEmailSrcAttribute()
    {
        if($this->attributes['photo_source'] == 'Facebook')
            $src = str_replace('large', 'small', $this->attributes['src']);
        else
            $src = $this->attributes['src'];

        if($src == '')
            $src = url('images/user.jpeg');
        else if($this->attributes['photo_source'] == 'Local'){
            $picture_details = pathinfo($this->attributes['src']);
            $src = url('images/users/'.$this->attributes['user_id'].'/'.@$picture_details['filename'].'.'.@$picture_details['extension']);
        }

        return $src;
    }
}

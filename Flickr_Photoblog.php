<?php // Flickr_Photoblog.php v0.1.0
/**
 * Flickr Photoblog allows developers to easily create photoblog posts or web pages
 * from photos on Flickr.
 *
 * It requires PEAR::Flickr_API which can be found at http://code.iamcal.com/php/flickr/readme.htm
 *
 * @author Dan Bough <daniel.bough at gmail.com> http://www.danielbough.com
 * @copyright Copyright (C) 2010-2013
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 */
include "/usr/share/php/Flickr/API.php";

class Flickr_Photoblog {
    /**
     * Flickr API Key
     * See http://www.flickr.com/services/api/misc.api_keys.html
     * @var string
     */
    var $apiKey;

    /**
     * Set to true to add a small attribution link to the bottom of your blog post.
     * @var bool
     */
    var $attribution = false;

    /**
     * Flickr/API object
     * @var object
     */
    var $flickr;

    /**
     * Add HTML header?
     * @var boolean
     */
    var $fullHtml = false;

    /**
     * Holds html for our blog post
     * @var string
     */
    var $html = '';

    /**
     * Array to hold html data
     * @var array
     */
    var $htmlArray = array();

    /**
     * Add full HTML
     * @var string
     */
    var $htmlHead;

    /**
     * CSS used in $htmlHead if required.
     * @var string
     */
    var $htmlCss;

    /**
     * Set maximum photo size.  For instance, if you choose "Medium" but "Small 320" is the 
     * largest avaiable size, that the photo that will be returned.  Defaults to "Medium"
     *
     * Square          (75x75)
     * Large Square    (150x150)
     * Thumbnail       (100 on longest side)
     * Small           (240 on longest side)
     * Small 320       (320 on longest side)
     * Medium          (500 on longest side)
     * Medium 640      (640 on longest side)
     * Medium 800      (800 on longest side)
     * Large           (1024 on longest side)
     * Large 1600      (1600 on longest side)
     * Large 2048      (2048 on longest side)
     * Original        (Original size)
     * @var string
     */
    var $maxSize = "Medium";

     /**
     * Text to add at the beginning of the post.
     * @var string
     */
    var $postPrefix;

    /**
     * Text to add at the end of the post.
     * @var string
     */
    var $postSuffix;

    /**
     * Title of post (text, H size)
     * Example:  ("This is the title.", "H1");
     * @var array
     */
    var $postTitle = array();

    /**
     * Comma separated tags to search through.
     * @var string
     */
    var $tags;

    /**
     * Total number of photos returned by a search
     * @var string
     */
    var $totalPhotos;

    /**
     * Flickr user ID
     * @var string
     */
    var $userId;

    /**
     * URL for Flickr user profile.
     * @var string
     */
    var $userProfile;

    function __construct($apiKey, $userName, $tags)
    {
        // Build our flickr object
        $this->flickr = new Flickr_API(array(
            'api_key'=>$apiKey
        ));

        // Put our arguments into vars.
        $this->userName = $userName;
        $this->tags = $tags;

        // Set variables the user.
        $this->getSetUserInfo($userName);
    }

    /**
     * Get user id and user profile url then set them to variables.
     * @param string $name
     */
    function getSetUserInfo($name)
    {
        $results = $this->flickr->callMethod('flickr.people.findByUsername', array('username'=>$name));
        if ($results) {
            foreach ($results->children as $child) {
                if ($child->attributes) {
                    $this->userId = $child->attributes['id'];
                    $this->userProfile = $this->getUserProfile();
                }
            }
        }
        else {
            $this->getError($this->flickr);
        }
    }

    /**
     * Get URL of user profile.
     * @return string
     */
    function getUserProfile()
    {
        $results = $this->flickr->callMethod('flickr.urls.getUserProfile', array('user_id'=>$this->userId));
        if ($results) {
            foreach ($results->children as $child) {
                if (isset($child->attributes['url'])) {
                    return $child->attributes['url'];
                }
            }
        }
        else {
            $this->getError($this->flickr);
        }
    }

    /**
     * Main function.  Build HTML
     * @return string
     */
    function getHtml()
    {
        $photos = $this->getPhotos();
        if ($photos) {
            // Main loop
            foreach ($photos as $photo) {
                if ($photo->attributes) {   
                    // Get photo URL and dimensions.
                    $photoAttributes = $this->getPhotoAttributes($photo->attributes['id']);

                    // Get photo title, description and upload date and add it to our attributes array
                    $photoContent = $this->getInfo($photo->attributes['id']);
                    $photoAttributes['title'] = $photoContent['title'];
                    $photoAttributes['description'] = $photoContent['description'];
                    $photoAttributes['date'] = $photoContent['date'];
                    // Add photo attributes to our html array
                    array_push($this->htmlArray, $photoAttributes);
                    $this->totalPhotos--;
                 }
            if ($this->totalPhotos == 0) {
                /*
                    Once we've obtained attributes for all of our photos, 
                    build html for our blog post;
                 */
                if ($this->fullHtml) {
                    $this->buildFullHtml();
                }
                else {
                    $this->buildHtml($this->htmlArray);
                }

                return $this->html;
                }
            }
        }
        else {
            $this->getError($this->flickr);
        }
    }

    /**
     * Basic photo object
     * @return object
     */
    function getPhotos()
    {
        $results =  $this->flickr->callMethod('flickr.photos.search', array(
            'user_id'=>$this->userId,
            'tag_mode'=>'and',  
            'tags'=>$this->tags
        ));

        // Set total number of photos.  Max (for now) is 100
        $this->totalPhotos = ($results->children[1]->attributes['total'] <= 100) ? $results->children[1]->attributes['total'] : 100;

        return $results->children[1]->children;
    }


    /**
     * Get photo URL and dimensions
     * @param  int $id
     * @return array 
     */
    function getPhotoAttributes($id)
    {  
        // Array to hold photos
        $photoArray = array();
        
        // Get avaiable sizes and attributes for the photo.
        $sizes = $this->flickr->callMethod('flickr.photos.getSizes',array('photo_id'=>$id));

        // Build an array of photos / photo attributes.
        if ($sizes) {
            foreach ($sizes->children[1]->children as $child) {
                if ($child->attributes) {
                    array_push($photoArray, $child->attributes);
                }
            }

            // Get photo attiributes that match size requirements (or next available smaller size).
            $limit = count($photoArray);
            return $this->getMatchingPhoto($photoArray, $this->maxSize, $limit);
        }
        else {
            $this->getError($this->flickr);
        }
    }

    /**
     * Get photo URL and dimenions that most closely
     * match the max size.  Called by getPhotoAttributes()
     * @param  array $photoArray
     * @param  string $size
     * @return array
     */
    function getMatchingPhoto($photoArray, $size, $limit) 
    {
        // Array of all possible photo sizes
        $availSizes = array(
            'Square', 'Large Square', 'Thumbnail', 
            'Small', 'Small 320', 'Medium', 'Medium 640', 
            'Medium 800', 'Large', 'Large 1600', 'Large 2048', 'Original');

        // Look through the photos to see if one of them matches our size and return it.
        foreach ($photoArray as $photo) {
            if ($photo['label'] == $size) {
                return $photo;
            }
        }

        // We're limiting our search to the number of photo sizes in the array.
        if ($limit > 1) {
            $currentSize = array_search($size, $availSizes);
            return $this->getMatchingPhoto($photoArray, $availSizes[$currentSize-1], $limit-1);
        }
        else {
            print "[Error]  Unable to get photo size\n";
            exit;
        }
    }

    /**
     * Get description, title and upload date
     * @param  object $flickr
     * @param  int $id
     * @return array
     */
    function getInfo($id)
    {   
        // Initialize vars
        $description = "";
        $title = "";
        $date = "";

        $info = $this->flickr->callMethod('flickr.photos.getInfo', array('photo_id'=>$id));
        if ($info) {
            foreach ($info->children[1]->children as $child) {
                if ($child->content && $child->name == "description") {
                    $description = $child->content; 
                }
                if ($child->content && $child->name == "title") {
                    $title = $child->content;
                } 
                if (array_key_exists('posted', $child->attributes)) {
                    $date = $child->attributes['posted'];
                }
            }
            return array('description'=>$description, 'title'=>$title, 'date'=>$date);
        }
        else {
            $this->getError($this->flickr);
        }
    }

    /**
     * Create blog post HTML
     * @param  array $htmlArray
     * @return string of html
     */
    function buildHtml()
    {   
        // Sort unprocessed html array by date uploaded
        usort($this->htmlArray, array('Flickr_Photoblog','sortByDate'));

        // Add a post title.
        if ($this->postTitle) {
            $this->html .= "<" . $this->postTitle[1] . ">" . $this->postTitle[0] . "</" . $this->postTitle[1] . ">\n";
        }
        // Prepend a paragraph.
        if ($this->postPrefix) {
            $this->html .="<p>" . $this->postPrefix . "</p>\n";
        }

        // Build our HTML
        foreach ($this->htmlArray as $item) {
            $this->html .= "<img src='" . $item['source'] . "' alt='" . $item['title'] . "'" .
                " height='" . $item['height'] . "' width='" . $item['width'] . "' title='" . $item['title'] . "'/>\n" .
                "<p>" . $item['description'] . "</p>\n";
        }

        // Append a paragraph.
        if ($this->postSuffix) {
            $this->html .="<p>" . $this->postSuffix . "</p>\n";
        }

        // If attribution is required, add it.
        if ($this->attribution) {
            $this->html .= "<p style='font-size:80%'>Photos by <a href='" . $this->userProfile . "'>" . $this->userName . "</a></p>\n";
        }

    }

    /**
     * Build full html page.
     */
    function buildFullHtml()
    {   
        // If the user hans't written an html headding, create a basic one.
        if (!$this->htmlHead) {
            $this->html .= "<!DOCTYPE html>\n";
            $this->html .= "<html lang='en'>\n";
            $this->html .= "<head>\n";
            $this->html .= "<meta charset='utf-8' />\n";
            if ($this->postTitle) {
                $this->html .= "<meta name='description' content='" . $this->postTitle[0] . "'>\n";
                $this->html .= "<title>" .$this->postTitle[0] ."</title>\n";
            }    
            if ($this->htmlCss) {
                $this->html .= "<link rel='stylesheet' href='" . $this->htmlCss . "' type='text/css' />\n";
            }
            $this->html .= "</head>\n";
            $this->html .= "<body>\n";
        }
        else {
            $this->html .= $this->htmlHead;
        }
        $this->buildHtml($this->htmlArray);
        $this->html .= "</body>\n";
        $this->html .= "</html>\n";
    }

    /**
     * Print Flickr error id and message
     * @param  object $flickr
     * @return string
     */
    function getError($flickr)
    {
        $code = $flickr->getErrorCode();
        $message = $flickr->getErrorMessage();

        print "[Error]  Code: " . $code . "  Message:  " . $message . "\n";
        exit;
    }

    /**
     * Sort multidimensional array by date
     * @param  string $a
     * @param  string $b
     * @return bool
     */
    static function sortByDate($a, $b) 
    {
        return $a['date'] - $b['date'];
    }
    
}
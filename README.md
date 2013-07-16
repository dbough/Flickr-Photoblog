Flickr-Photoblog v0.1.0
=======================

Flickr Photoblog allows you to easily create a photoblog post or webpage from photos on Flickr.  

Author
------
Dan Bough  
daniel.bough at gmail.com  
http://www.danielbough.com  

License
-------
This software is free to use under the GPLv2 License.  

Overview
--------  
Flickr Photoblog allows you to create a blog post using photos and photo descriptions gathered from Flickr.com.  With as little as three arguments, you can create html for a post that can be dropped into any blog, or, if you prefer, you can create a full, stand alone web page.  

Why?
----
Inspiration for Flickr Photoblog was garnered from DannyChoo.com; a blog about the wonders of Japan.  I had noticed that the photos and text used in some blog posts were identical to their Flickr counterparts.  I figured Danny didn't create both copies by hand and so I started investigating ways to do the same thing programatically.

Requirements
------------
**Flickr API Key**  
http://www.flickr.com/services/api/misc.api_keys.html

**PEAR::Flickr_API**  
http://code.iamcal.com/php/flickr/readme.htm

*To Install*  
 - If you do not have PEAR, install it using Aptitude:  `apt-get install php-pear` or visit http://pear.php.net/ for more info.
 - `pear install -of http://code.iamcal.com/php/flickr/Flickr_API-Latest.tgz`  

Basic Use
----------
**Create html for a basic blog post.**

*Create a Flickr_Photoblog object with three arguments; your Flickr API key, a Flickr username, and a list of comma seperated search terms (tags):*  

    <?php
    include "Flickr_Photoblog.php";

    // Build object with apiKey, userName and tags
    $fb = new Flickr_Photoblog("[YOUR FLICKR API KEY]", "Danny Choo", "akihabarashops");

    // Get photos
    print $fb->getHtml();

Result:  

    <img src='http://farm8.staticflickr.com/7411/9036665485_fe4a78a209.jpg' alt='Akihabara Shops 8' height='281' width='500' title='Akihabara Shops 8'/>
    <p>Akihabara - the worlds largest concentrated area dedicated to anime, games, manga, cosplay, robot, hobby and computing shops. Today we take our regular gander around the otaku haven in this weeks instalment of Akihabara Shops.

    Today we take a lookie at places including Yodobashi Camera, Traders, Dospara, Sofmap, Koubu Inari Shrine [&#232;&#172;&#155;&#230;&#173;&#166;&#231;&#168;&#178;&#232;&#141;&#183;&#231;&#165;&#158;&#231;&#164;&#190;], Akihabara Crane Lab, K-books, Kotobukiya, Animate and other small stores located in the back streets of Akihabara.

    First photo - huge poster for the up n coming eroge title Amairo Islenauts - my team makes the official mobile app that helps you learn Japanese &#94;o&#94;

    View more at &lt;a href=&quot;http://www.dannychoo.com/en/post/26961/Akihabara+Shops+8.html&quot; rel=&quot;nofollow&quot;&gt;www.dannychoo.com/en/post/26961/Akihabara+Shops+8.html&lt;/a&gt;</p>

**Create html for full page.**

    <?php
    include "Flickr_Photoblog.php";

    // Build object with apiKey, userName and tags
    $fb = new Flickr_Photoblog("[YOUR FLICKR API KEY]", "Danny Choo", "akihabarashops");

    // Add HTML header
    $fb->fullHtml = true;

    // Feel free to add a title and give it an H size.
    $fb->postTitle = array("Akihabara Shops", "H1");

    // Get photos
    print $fb->getHtml();

Result:  

    <!DOCTYPE html>
    <html lang='en'>
    <head>
    <meta charset='utf-8' />
    <meta name='description' content='Akihabara Shops'>
    <title>Akihabara Shops</title>
    </head>
    <body>
    <H1>Akihabara Shops</H1>
    <img src='http://farm8.staticflickr.com/7411/9036665485_fe4a78a209.jpg' alt='Akihabara Shops 8' height='281' width='500' title='Akihabara Shops 8'/>
    <p>Akihabara - the worlds largest concentrated area dedicated to anime, games, manga, cosplay, robot, hobby and computing shops. Today we take our regular gander around the otaku haven in this weeks instalment of Akihabara Shops.

    Today we take a lookie at places including Yodobashi Camera, Traders, Dospara, Sofmap, Koubu Inari Shrine [&#232;&#172;&#155;&#230;&#173;&#166;&#231;&#168;&#178;&#232;&#141;&#183;&#231;&#165;&#158;&#231;&#164;&#190;], Akihabara Crane Lab, K-books, Kotobukiya, Animate and other small stores located in the back streets of Akihabara.

    First photo - huge poster for the up n coming eroge title Amairo Islenauts - my team makes the official mobile app that helps you learn Japanese &#94;o&#94;

    View more at &lt;a href=&quot;http://www.dannychoo.com/en/post/26961/Akihabara+Shops+8.html&quot; rel=&quot;nofollow&quot;&gt;www.dannychoo.com/en/post/26961/Akihabara+Shops+8.html&lt;/a&gt;</p>
    </body>
    </html>

Options
-------
- Set maximum photo size.  *This will attempt to get photos of the specified size.  If they don't exist, the next size lower will be attempted (default starts at Medium).*  

    /** Options:
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
     */

    $fb->maxSize = "Large";

- Add attribution at the bottom of a post:  `$fb->attribution = true;`.
- Add CSS file to HTML header (requires `$fb->fullHtml = true;`) `$fb->htmlCss = "/path/to/css";`.
- Create your own HTML header (requires <html><header></header><body> tags): `$fb->htmlHeader = foo;`.
- Add "Intro" our "Outro" paragraphs: `$fb->postPrefix = foo;` & `$fb->postSuffix = bar`.

Notes
-----
- Maximum photos returned = 100.

History
-------  
- v0.1.0 - Initial Release
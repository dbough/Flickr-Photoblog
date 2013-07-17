<?php
/**
 * Flickr Photoblog Example
 *
 * Visit https://github.com/dbough/Flickr-Photoblog for the latest code.
 *
 * @author  Dan Bough <daniel.bough at gmail.com> / http://www.danielbough.com
 * @copyright Copyright (C) 2010-2013
 *
 */

// Change path if needed.
include "Flickr_Photoblog.php";

$apiKey = ($_POST['apiKey']) ? $_POST['apiKey'] : NULL;
$userName = ($_POST['userName']) ? $_POST['userName'] : NULL;
$tags = ($_POST['tags']) ? $_POST['tags'] : NULL;
$title = ($_POST['title']) ? $_POST['title'] : NULL;
$outputPath = ($_POST['outputPath']) ? $_POST['outputPath'] : "/";

if ($apiKey && $userName && $tags & $title) {
    $outputFile = $outputPath . "/photoblog.html";
    $fb = new Flickr_Photoblog($apiKey, $userName, $tags);
    $fb->postTitle = array($title, $_POST['hSize']);
    if ($_POST['attrib']) {
        $fb->attribution = true;
    }
    if ($_POST['fullHtml']) {
        $fb->fullHtml = true;
    }
    if ($_POST['size']) {
        $fb->maxSize = $_POST['size'];
    }
    $html = $fb->getHtml();
    if ($html) {
        $fh = fopen($outputFile, "w");
        fwrite($fh, $html);
        fclose($fh);
    }
    else {
        echo "<pre>";
        echo $fb->error;
        echo "</pre>";
    }
}
else {
    $errorMsg = "<pre>";
    $errorMsg .= "API Key, Username, Tags and Title Required!";
    $errorMsg .= "</pre>";
}
if (file_exists($outputFile)) {
    unlink($outputFile);
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8' />
<title>Flickr Photoblog Example</title>
</head>
<body style="margin:0;padding:0;background:#f5f2f2;">
<div style="max-width:960px;margin:auto;padding:auto;">

    <div style="text-align:center;">
        <h1>Flickr Photoblog Example</h1>
        <p>Flickr Photoblog allows you to easily create a blog post or webpage from photos on Flickr.&nbsp;&nbsp;<a href="https://github.com/dbough/Flickr-Photoblog">Get more info here.</a></p>
    </div>
    <div style="text-align:center;">
        <?php if ($errorMsg) { echo $errorMsg; } ?>
    </div>
        <div style="width:70%;margin:auto;padding:auto;">
            <div style="float:left;">
                 <form method="post" action="example.php" >
                    <p title="Get your Flickr API key at http://www.flickr.com/services/api/misc.api_keys.html" style="cursor:help;">
                        API Key&nbsp;&nbsp;
                        <input type="password" placeholder="Flickr API Key" name="apiKey" size="40">
                    </p>
                    <p>
                        Username&nbsp;&nbsp;
                        <input type="text" placeholder="Flickr Username" name="userName" size="40">
                    </p>
                    <p style="cursor:help;" title="Comma separated list of search terms.">
                        Tags&nbsp;&nbsp;
                        <input type="text" placeholder="Flickr Tags" name="tags" size="40">
                    </p>
                    <p>
                        Post Title&nbsp;&nbsp;
                        <input type="text" placeholder="Post Title" name="title" size="40">
                    </p>
                    <p style="cursor:help;" title="Must be writable by the web server.  Do not include outside slashes.">
                        Output Relative Path&nbsp;&nbsp;
                        <input type="text" placeholder="HTML Output Path" name="outputPath" size="40">
                    </p>
            </div>
            <div>
                    <p>
                        Attribution&nbsp;&nbsp;
                        <input type="checkbox" name="attrib"/>
                    </p>
                    <p>
                        Max Photo Size&nbsp;&nbsp;
                        <select name="size">
                            <option value="Original">Original</option>
                            <option value="Large">Large</option>
                            <option selected value="Medium">Medium</option>
                            <option value="Small">Small</option>
                        </select>
                    </p>
                    <p>
                        Title Size&nbsp;
                        <select name="hSize">
                            <option value="H1">H1</option>
                            <option value="H2">H2</option>
                            <option value="H3">H3</option>
                        </select>
                    </p>
                    <p>
                        Full HTML?&nbsp;
                        <input type="checkbox" name="fullHtml"/>
                    </p>
            </div>
            <p style='margin-top:100px;'>
                <input type="submit" name="submit"/>
            </p>
        </form>
        </div>
    <br/>
    <div style="text-align:center;">
    <?php
        if (file_exists($outputFile) && (filesize($outputFile) > 0)) {
            echo "<a style='font-size:200%' href='" . $outputFile . "'>View your Photoblog!</a>";
        }
    ?>
    </div>
</div>
</body>
</html>

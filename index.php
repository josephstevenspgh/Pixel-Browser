<?php
    /*
    * Pixel Browser - Clean image zooming, and directory browsing with PHP
    *
    * written by Joseph Stevens
    *
    * Last updated Tuesday February 23rd, 2010
    *
    * Version 1.5
    *
    * * * * * * *
    * Changelog *
    * * * * * * *
    *
    * Version 1.6:
    * * Changed the default viewing to thumbnails
    *
    *
    * Version 1.5: 
    * * Fixed bug in counting items per page
    * * Added in anti-image filtering CSS for Firefox and IE
    * * Added "Random Page"
    *
    * Version 1.4:
    * * Created Default settings so that if there are no cookies, things still work properly
    * * Made Page Navigation easier (First,Prev,Next,Last Links)
    *
    * Version 1.3:
    * * Fixed bug: choking on quotes
    * * Fixed bug: choking on spaces
    *
    * Version 1.x:
    * * Didn't keep track!
    *
    * * * * * * * *
    * To-Do List  *
    * * * * * * * *
    *
    * * Make it so right click -> save doesn't name the imaeg "image.php"
    */

    //include functions
    include_once 'Functions.php';

    //load themes
    $handle = opendir("Styles");
    while (false !== ($file = readdir($handle))){
        $fileList[] = trim($file);
    }

    //sort themes
    sort($fileList);
    reset($fileList);

    //place themes in vars
    $i=0;
    while(list($key, $val) = each ($fileList)){
        $i++;
        $Theme[$i]=$val;
    }
    closedir($handle);

    //set up ThemeName variable
    for($i=0;$i<=sizeof($Theme);$i++){
        $ThemeName[$i] = substr($Theme[$i], 0, strlen($Theme[$i])-4);
    }

    //default variables
    $expire         = time()+60*60*24*30;
    $filecount      = "0";
    $ItemsPerPage   = 24;
    $Settings       = 0;
    $page           = 0;
    
    //set $Debug to 0 to hide the "Debug" panel
    $Debug          = 0;

    //get variables from Cookies
    $DefaultZoomLevel   = $_COOKIE["Zoom"];
    $ZoomLevel          = $_COOKIE["Zoom"];
    $thumbnails         = $_COOKIE["Thumbnails"];
    $SelectedTheme      = $_COOKIE["Theme"];

    //If there are no cookies, use these default values
    if(!$DefaultZoomLevel){ $DefaultZoomLevel   = 1;}
    if(!$ZoomLevel){        $ZoomLevel          = $DefaultZoomLevel;}
    if(!$thumbnails){       $thumbnails         = 2;}
    if(!$SelectedTheme){    $SelectedTheme      = $Theme[6];}
    
    
    //if variables are set from the URL, overwrite them.
    if ($_GET['File']){
        $imgname = $_GET['File'];
    }

    if ($_GET['Zoom']){
        $ZoomLevel = $_GET['Zoom'];
    }
    
    if ($_GET['Page']){
        $page = (int)$_GET['Page'];
    }
    
    //thumbnail, and theme, get saved to cookies when they are changed
    //DefaultZoom can be changed as well
    
    //thumbnail toggle
    if ($_GET['Thumbnails']){
        $thumbnails = $_GET['Thumbnails'];
        setcookie("Thumbnails", $thumbnails, $expire);
    }else if($_COOKIE["Thumbnails"]){
        //use thumbnail settings in cookie
        $thumbnails = $_COOKIE["Thumbnails"];
    }else{
        $thumbnails = 2;
    }

    //theme selection
    if ($_GET['Theme']){
        $SelectedTheme = $Theme[$_GET['Theme']];
        setcookie("Theme", $SelectedTheme, $expire);
    }else if($_COOKIE["Theme"]){
        $SelectedTheme = $_COOKIE["Theme"];
    }

    //settings
    if ($_GET['Settings']){
        $Settings = 1;
    }
    
    //default zoom
    if($_GET['DefaultZoom']){
        setcookie("Zoom", $_GET['DefaultZoom'], $expire);
    }
	
	//if an image name was selected: calculate height and width from the zoom level
    if($imgname){
        //strip slashes from imagename
        $imgname = stripslashes($imgname);
	    //calculate image height and width
	    list($imgwidth, $imgheight) = getimagesize("$imgname");

    	$imgheight = $imgheight * $ZoomLevel;
	    $imgwidth = $imgwidth * $ZoomLevel;
    }
    
    //create a list of files in the directory   - only count GIF and PNG files
    $handle = opendir(".");
    while (false !== ($file = readdir($handle))){
        //create temporary variables for cleaner code
        $Filename   = strtolower(trim($file));
        $StartPos   = strlen($Filename) - 3;
        $EndPos     = strlen($Filename);
        if(substr($Filename, $StartPos, $EndPos) == "gif"){
            $fileList[] = trim($file);
        }else if(substr($Filename, $StartPos, $EndPos) == "png"){
            $fileList[] = trim($file);
        }        
    }
    sort ($fileList);
    reset ($fileList);
    
    //Get a filecount
    while (list ($key, $val) = each ($fileList)){
        //only count PNGs or GIFs as images
        if((substr_count(strtolower($val), '.gif')) > 0){
            //increase filecount
            $filecount++;
        }else if((substr_count(strtolower($val),'.png')) > 0){
            $filecount++;
        }
    }
    
    //close and clean shit
    unset($fileList);
    closedir($handle);
    
    //HTML header
    echo("
    <html>
            <head>
                    <title>
                            [pixel browser] v1.5
                    </title>
                    <link rel=\"stylesheet\" type=\"text/css\" href=\"Styles/$SelectedTheme\" />
            </head>
            <body>");


    //Image Panel
    //only display if an image name was specified
    if ($imgname){
        $posPNG = stripos($imgname, ".png");
        //display the image -- currently, only use my special filter if its a PNG
        if($posPNG){
            echo("<div class=\"bodytext\"> $imgname <br/><img src=\"image.php?File=$imgname&Zoom=".$ZoomLevel."\"><br/>");
        }else{
            //if not a PNG: just let the browser handle scaling
            echo("<div class=\"bodytext\"> $imgname <br/><img src=\"$imgname\" width=$imgwidth height=$imgheight><br/>");
        }
        //Zooming options for the user
        $ZoomDownUrl = GenerateURL(($ZoomLevel - 1), $imgname, "First");
        $ResetZoomUrl = GenerateURL(1, $imgname, $page);
        $ZoomInUrl= GenerateURL(($ZoomLevel + 1), $imgname, $page);
        echo("<a href=\"$ZoomDownUrl\">-</a> <a href=\"$ResetZoomUrl\">
                o</a> <a href=\"$ZoomInUrl\">+</a></div>");
    }

    //Directory List Panel
    echo("<div class=\"listing\"><table class=\"TableList\"><tr><td class=\"TableRow0\"");
    //if no thumbnails, make this colspan=2
    if($thumbnails != 2){   echo(" colspan=2 ");}
    echo(">");

    //Create "First" "Previous Page" "Next Page" and "Last" links
    $FirstPage          = "0";
    $PrevPage           = $page-1;
    $NextPage           = $page+1;
    $LastPage           = intval($filecount/$ItemsPerPage);
    $RandPage           = rand(0,$LastPage);
    //checks for Prev/Next
    if($PrevPage < $FirstPage ) { $PrevPage = 0; }
    if($NextPage > $LastPage)   { $NextPage = 0; }
    //create links
    $FirstPageLink      = "?Page=$FirstPage&File=$imgname&Zoom=$ZoomLevel";
    $PreviousPageLink   = "?Page=$PrevPage&File=$imgname&Zoom=$ZoomLevel";
    $NextPageLink       = "?Page=$NextPage&File=$imgname&Zoom=$ZoomLevel";
    $LastPageLink       = "?Page=$LastPage&File=$imgname&Zoom=$ZoomLevel";
    $RandomPageLink     = "?Page=$RandPage&File=$imgname&Zoom=$ZoomLevel";
    
    //print links
    echo("<table border=0 width=100%><tr><td width=20%><p align=center><a href=$FirstPageLink>First Page</a></p></td><td width=20%><p align=center><a href=$PreviousPageLink>Previous Page</a></p></td><td width=20%><p align=center><a href=$RandomPageLink>Random Page</a></p></td><td width=20%><p align=center><a href=$NextPageLink>Next Page</a></p></td><td width=20%><p align=center><a href=$LastPageLink>Last Page</a></p></td></tr></table>");

    //create a list of files in the directory   - only count GIF and PNG files
    $handle = opendir(".");
    while (false !== ($file = readdir($handle))){
        //create temporary variables for cleaner code
        $Filename   = strtolower(trim($file));
        $StartPos   = strlen($Filename) - 3;
        $EndPos     = strlen($Filename);
        if(substr($Filename, $StartPos, $EndPos) == "gif"){
            $fileList[] = trim($file);
        }else if(substr($Filename, $StartPos, $EndPos) == "png"){
            $fileList[] = trim($file);
        }        
    }
    sort ($fileList);
    reset ($fileList);
        
    //draw thumbnails, if they are selected
    if($thumbnails==2){
        while (list ($key, $val) = each ($fileList)){
            //only count PNGs or GIFs as images
            if((substr_count(strtolower($val), '.gif')) > 0){
                if($key>($page*$ItemsPerPage) && $key<=(($page+1)*$ItemsPerPage)){
                    //generate and display URL
                    $ImgUrl = GenerateURLNoZoom($val, $page);
                    echo("<a href=\"$ImgUrl\">");
                    echo("<img class=\"Thumbnail\" src=\"$val\"</a> ");
                }
            }else if((substr_count(strtolower($val),'.png')) > 0){
                if($key>($page*$ItemsPerPage) && $key<=(($page+1)*$ItemsPerPage)){
                    $ImgUrl = GenerateURLNoZoom($val, $page);
                    echo("<a href=\"$ImgUrl\">");
                    echo("<img class=\"Thumbnail\" src=\"$val\"></a> ");
                }
            }
        }
    }
    //no thumbnails: be a little advanced, show more detail
    $row = 0;
    while (list ($key, $val) = each ($fileList)){
        //only count PNG and GIF as images
        if((substr_count(strtolower($val), '.gif')) > 0){
            if($key>($page*$ItemsPerPage) && $key<=(($page+1)*$ItemsPerPage)){
                //display link & info
                echo("<tr class=\"".$currentrow."\"><td><a href=\"index.php?File=$val");
                if($page){
                    echo("&Page=$page");
                }
                echo("\">$val</a>");
                $row++;
                $currentrow = "TableRow".($row % 2);
                $LastMod = date ("F d Y H:i:s", filemtime($val));
                echo("</td><td class=\"TableDate\">");
                echo($LastMod);
                echo("</td></tr>");
            }
        }
        else if((substr_count(strtolower($val),'.png')) > 0){
            if($key>($page*$ItemsPerPage) && $key<=(($page+1)*$ItemsPerPage)){
                echo("<tr class=\"".$currentrow."\"><td><a href=\"index.php?File=$val");
                if($page){
                    echo("&Page=$page");
                }
                echo("\">$val</a>");
                $row++;
                $currentrow = "TableRow".($row % 2);
                $LastMod = date ("F d Y H:i:s", filemtime($val));
                echo("</td><td class=\"TableDate\">");
                echo($LastMod);
                echo("</td></tr>");
            }
        }
    }
    closedir($handle);
    echo("</td></tr></table>");

    //display "Pages: " if needed
    if ($filecount>$ItemsPerPage){
        echo("Pages: ");
        for($i=0;$i<($filecount/$ItemsPerPage);$i++){
            if ($i!=$page){
                echo("<a href=\"index.php?Page=$i");
                if($imgname){
                    echo("&File=$imgname");
                }
                if($ZoomLevel){
                    echo("&Zoom=$ZoomLevel");
                }
                echo("\">$i</a> ");
            }else{
                echo("$i ");
            }
        }
    }
    //display total image count
    echo("<br />Total Image Count: <b>$filecount</b><br></div>");

    //Options panel
    echo("<div class=\"listing\">");
    //display "show" if its hidden
    if($Settings==0){
        echo("<a href=\"?Settings=1\">Show Settings</a>");
    }else if($Settings==1){
        //option for hiding
        //thumbnail toggle
        echo("Thumbnail Settings");

        //highlight selected option
        if($Thumbnails=2){
            $ThumbRow0 = "TableRow1";
            $ThumbRow1 = "TableRow0";
        }else{
            $ThumbRow0 = "TableRow0";
            $ThumbRow1 = "TableRow1";
        }

        echo("<table class=\"TableList\"><tr class=\"".$ThumbRow0."\"><td>");
        echo("<a href=\"?Thumbnails=2\">Enable Thumbnails</a></td></tr>");
        echo("<tr class=\"".$ThumbRow1."\"><td><a href=\"?Thumbnails=1\">Disable Thumbnails</a></td></tr></table>");
        //theme selections
        echo("Theme Selection");
        echo("<table class=\"TableList\">");
        for($i=0;$i<=sizeof($Theme);$i++){
            //highlight selected option
            if($SelectedTheme==$Theme[$i]){
                $TableClass = "TableRow1";
            }else{
                $TableClass = "TableRow0";
            }
            echo("<tr class=\"".$TableClass."\"><td><a href=\"?Theme=".$i."\">".$ThemeName[$i]."</a></td></tr>");
        }
        echo("</table>");
        
        //DefaultZoom Selection
        echo("Default Zoom");
        //table
        echo("<table class=\"TableList\">");
        echo("<tr class=\"TableRow0\"><td><a href=\"?DefaultZoom=1\">1x</a></td></tr>");
        echo("<tr class=\"TableRow1\"><td><a href=\"?DefaultZoom=2\">2x</a></td></tr>");
        echo("<tr class=\"TableRow0\"><td><a href=\"?DefaultZoom=3\">3x</a></td></tr>");
        echo("<tr class=\"TableRow1\"><td><a href=\"?DefaultZoom=4\">4x</a></td></tr>");
        echo("<tr class=\"TableRow0\"><td><a href=\"?DefaultZoom=5\">5x</a></td></tr>");
        echo("</table>");
    }
    echo("</div>");

    //DEBUG panel, disable debug flag when releasing
    if($Debug){
        echo("<div class=\"Listing\">");
            echo("Debug");
            //display a list of variables
            echo("<table class=\"TableList\">");
                echo("<tr><td>");
                echo("\$expire: </td><td>".$expire);
                echo("</td></tr><tr><td>");
                echo("\$ZoomLevel: </td><td>".$ZoomLevel);
                echo("</td></tr><tr><td>");
                echo("\$filecount: </td><td>".$filecount);
                echo("</td></tr><tr><td>");
                echo("\$ItemsPerPage: </td><td>".$ItemsPerPage);
                echo("</td></tr><tr><td>");
                echo("\$SelectedTheme: </td><td>".$SelectedTheme);
                echo("</td></tr><tr><td>");
                echo("\$thumbnails: </td><td>".$thumbnails);
                echo("</td></tr><tr><td>");
                echo("\$Settings: </td><td>".$Settings);
                echo("</td></tr><tr><td>");
                echo("\$page: </td><td>".$page);
                echo("</td></tr><tr><td>");
                echo("\$Debug: </td><td>".$Debug);
                echo("</td></tr><tr><td>");
                echo("\$DefaultZoomLevel: </td><td>".$DefaultZoomLevel);
                echo("</td></tr><tr><td>");
                echo("\$imgname: </td><td>".$imgname);
                echo("</td></tr></table>");
        echo("</div>");
    }

    //Closing
    echo("</body></html>");
?>

<?php
    /*
     * Functions for Pixel Browser
     * 
     * Written by Joseph Stevens
     *
     * Last Updated January 2nd, 2010
     */

    //GenereateURL - Generates a url based off values and returns it as a string
    function GenerateURL($zoom, $filename, $page){
        $NEWURL = "index.php?";
        if($filename){
            $NEWURL = $NEWURL."File=$filename&";
        }if($zoom){
            $NEWURL = $NEWURL."Zoom=$zoom&";
        }if($page){
            $NEWURL = $NEWURL."Page=$page&";
        }
        $NEWURL = substr($NEWURL, 0, (strlen($NEWURL)-1));
        return $NEWURL;
    }
    
    //GenereateURLNoZoom - Generates a url based off values and returns it as a string, without 
    function GenerateURLNoZoom($filename, $page){
        $NEWURL = "index.php?";
        if($filename){
            $NEWURL = $NEWURL."File=$filename&";
        }if($page){
            $NEWURL = $NEWURL."Page=$page&";
        }
        $NEWURL = substr($NEWURL, 0, (strlen($NEWURL)-1));
        return $NEWURL;
    }

?>
